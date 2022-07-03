<?php
	session_start();
	if (!isset($_SESSION['account'])) { header('Location: http://localhost:8000/index.php'); }
	if ($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST['imageData'])) {
		require_once(__DIR__ . "/../Model/manageDatabase.php");
		$db = new ManageDatabase;
		//When posting with 'Content-Type: application/x-www-form-urlencoded' the '+' gets transformed into ' ' which we need to revert for the image data to be correct
		$_POST['imageData'] = str_replace(" ", "+", $_POST['imageData']); 
		$db->createPicture($_POST['imageData'], $_SESSION['account']);
	}
	if ($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST['storagePath'])) {
		require_once(__DIR__ . "/../Model/manageDatabase.php");
		$db = new ManageDatabase;
		$db->deletePicture($_POST['storagePath']);
	}	
?>

<?php require(__DIR__ . "/../View/header/in-app-header.php"); ?>

<h3>Take Picture</h3>
<div id="getPicture"></div>
<canvas id="takePictureCanvas" style="display:none;" width="320" height="240"></canvas>
<form id="takePictureButton">
	<p>Choose Overlay Image:</p>
	<label><input type="radio" name="overlay" value="wine" required>Wine</label><br>
	<label><input type="radio" name="overlay" value="christmas">Christmas</label><br>
	<label><input type="radio" name="overlay" value="shovel">Shovel</label><br>
	<label><input type="radio" name="overlay" value="hotdog">Hotdog</label><br>
	<label><input type="radio" name="overlay" value="tree">Tree</label><br><br>
	<button type="submit">Take photo</button><br>
</form>

<h3>Your Pictures</h3>
<ul>
<?php
    require_once(__DIR__ . "/../Model/manageDatabase.php");
    $db = new ManageDatabase;
    $myPics = $db->getPicturesOfUser($_SESSION['account']);
	foreach ($myPics as $pic) {
		echo "<li><img src='$pic->imageData' width='320' height='240'>
			<button onClick='deletePicture(`$pic->storagepath`)'>Delete</button></li>";
	}	
?>
</ul>

<?php require(__DIR__ . "/../View/footer/footer.html"); ?>

<script>
function displaySelectedPicture(input) {
  var selectedPictureDisplay = document.getElementById('selectedPictureDisplay');

  if (input.files && input.files[0]) {
  	var reader = new FileReader();

    reader.onload = function (e) {
		selectedPictureDisplay.src = e.target.result;
    };

    reader.readAsDataURL(input.files[0]);
  } else { selectedPictureDisplay.removeAttribute('src') }
}

function deletePicture(storagePath) {
	//Call php to delete picture
	fetch("http://localhost:8000/picture-editing.php", {
		method: "POST", //This should be delete but we use post instead because php can easily parse it
		headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
     	body: `storagePath=${storagePath}`
    });
	window.location.reload();//Refresh the page to see new picture in picture list
}

function takePicture(camera) {
	const takePictureButton = document.getElementById('takePictureButton');	

	takePictureButton.addEventListener("submit", () => {
  	 	let canvas = document.getElementById('takePictureCanvas');
		let canvasContext = canvas.getContext('2d');
		let overlayImg = new Image();
		selectedOverlayImage = document.querySelector('input[name="overlay"]:checked').value;
		overlayImg.src = `overlayImages/${selectedOverlayImage}.png`

		console.log('before');
		if (camera) {
			console.log('NOT IN');
			const stream = document.querySelector('video');
			canvasContext.drawImage(stream, 0, 0, canvas.width, canvas.height);
		} else {
			console.log('IN');
			const selectedImage = document.getElementById('selectedPictureDisplay');	
			canvasContext.drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
		}
		
		overlayImg.onload = () => {
			console.log('1');
			canvasContext.drawImage(overlayImg, 0, 0, canvas.width, canvas.height);	
			console.log('2');
			let imageData = canvas.toDataURL('image/png');
			console.log('3');
			//Call php to create picture in database
			console.log(imageData);
			fetch("http://localhost:8000/picture-editing.php", {
				method: "POST",
				headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
        		body: `imageData=${imageData}`
			});
			console.log('4');
			window.location.reload();//Refresh the page to see new picture in picture list
		}
	});
}

function setupVideoStream(stream) {
  const video = document.querySelector('video');
  video.srcObject = stream;
  video.play();
}

function errorMsg(msg, error) {
  const errorElement = document.getElementById('error');
  errorElement.innerHTML += `${msg}`;
  if (typeof error !== 'undefined') {
    console.error(error);
  }
}

function streamError(error) {
  if (error.name === 'NotAllowedError') {
    errorMsg('Permissions have not been granted to use your camera' +
      ', you need to allow the page access to your camera in ' +
      'order to take pictures.');
  } else {
  	errorMsg(`getUserMedia error: ${error.name}`, error);
  }
}

async function init() {
  var getPictureHTML = document.getElementById('getPicture');

  try {
    const stream = await navigator.mediaDevices.getUserMedia({video: true});
  	getPictureHTML.insertAdjacentHTML('afterbegin', 
		"<video width='320' height='240' autoplay></video><br>");
	setupVideoStream(stream);
	takePicture(true);
  } catch (error) {
	getPictureHTML.insertAdjacentHTML('afterbegin', "<p id='error'></p>" + 
	"<input type='file' name='selectedPicture' accept='image/*' " + 
	"onchange='displaySelectedPicture(this);'>" + 
	"<br><img id='selectedPictureDisplay' width='320' height='240'/>");
	streamError(error);
  	takePicture(false);
  }
}

init();
</script>
