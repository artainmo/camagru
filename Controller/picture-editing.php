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
?>

<?php require(__DIR__ . "/../View/header/in-app-header.php"); ?>

<h1 class="pageTitle">Create Picture</h1>
<div class="wrapperPictureEditing">
<div id="getPicture" class="block1PictureEditing"></div>
<canvas id="takePictureCanvas" style="display:none;" width="320" height="240"></canvas>
<form id="takePictureButton" class="block2PictureEditing">
	<p>Choose Overlay Image:</p>
	<label><input type="radio" name="overlay" value="wine" required>Wine</label><br>
	<label><input type="radio" name="overlay" value="boxes">Boxes</label><br>
	<label><input type="radio" name="overlay" value="plumber">Plumber</label><br>
	<label><input type="radio" name="overlay" value="hotdog">Hotdog</label><br>
	<label><input type="radio" name="overlay" value="shirt">Shirt</label><br><br>
	<button type="submit">Take photo</button><br>
</form>

<div class="block3PictureEditing">
<h3>Your Pictures</h3>
<?php
    require_once(__DIR__ . "/../Model/manageDatabase.php");
    $db = new ManageDatabase;
    $myPics = $db->getPicturesOfUser($_SESSION['account']);
		$i = 0;
	foreach ($myPics as $pic) {
		$i++;
		echo "<img src='$pic->imageData' width='320' height='240' " .
					"onClick='window.location.href=" .
	        "`/view-picture.php?picId=$pic->storagepath&origin=picture-editing`'>";
		if ($i % 2 === 0) { echo "<br><br>"; } else {
			echo "&emsp;&emsp;&emsp;&emsp;";
		}
	}
?>
</div>
<br><br><br><br>

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

function takePicture(camera) {
	const takePictureButton = document.getElementById('takePictureButton');

	takePictureButton.addEventListener("submit", async () => {
  	let canvas = document.getElementById('takePictureCanvas');
		let canvasContext = canvas.getContext('2d');
		let overlayImg = new Image();
		selectedOverlayImage = document.querySelector('input[name="overlay"]:checked').value;
		overlayImg.src = `overlayImages/${selectedOverlayImage}.png`;

		if (camera) {
			const stream = document.querySelector('video');
	    canvasContext.drawImage(stream, 0, 0, canvas.width, canvas.height);
	  } else {
			const selectedImage = document.getElementById('selectedPictureDisplay');
	    canvasContext.drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
	  }

	  function createImg() {
	    // console.log("IN");
	    canvasContext.drawImage(overlayImg, 0, 0, canvas.width, canvas.height);
	    let imageData = canvas.toDataURL('image/png');
	    //Call php to create picture in database
	    fetch("http://localhost:8000/picture-editing.php", {
	      method: "POST",
	      headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
	          body: `imageData=${imageData}`
	    });
	    alert('Image successfully loaded!'); //This creates a gain of time before the refresh, allowing the new picture to be present more often after refresh
			window.location.reload();
	  }

	  if (overlayImg.complete) { //Certain images are already loaded but still need to go through createImg function
	    // console.log(1);
	    createImg();
	  } else {
	    // console.log(2);
	    await new Promise((resolve) => { overlayImg.onload = resolve; });
	    createImg();
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
      'order to take pictures. Instead you can upload an image.');
	} else if (error.name === "NotFoundError") {
		errorMsg('No camera has been found on this device. ' +
			'Instead you can upload an image.');
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
		getPictureHTML.insertAdjacentHTML('afterbegin',
			"<p id='error' class='error'></p>" +
			"<label><input type='file' name='selectedPicture' accept='image/*' " +
			"onchange='displaySelectedPicture(this);'><br>Upload image<br></label>" +
			"<br><img id='selectedPictureDisplay' width='320' height='240'/>");
		streamError(error);
  	takePicture(false);
  }
}

init();
</script>
