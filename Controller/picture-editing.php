<?php
	header("Access-Control-Allow-Origin: *"); //Resolves the bug of fetch not working on firefox
	session_start();
	if (!isset($_SESSION['account'])) { header('Location: http://localhost:8000/index.php'); }

	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		$json = file_get_contents('php://input');
		$data = json_decode($json);
		$wd= 320;
		$ht= 240;

		function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    	$cut = imagecreatetruecolor($src_w, $src_h);
    	imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
    	imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
    	imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
		}

		$data->backgroundImageData = substr($data->backgroundImageData, 22); //https://www.php.net/manual/en/function.imagecreatefromstring.php
		$data->backgroundImageData = base64_decode($data->backgroundImageData);
		if (($dest = imagecreatefromstring($data->backgroundImageData)) === false) {
			error_log("Dest image creation from string error");
			exit(1);
		}
		$dest = imagescale($dest, $wd, $ht);
		for ($i = 0; $i < count($data->selectedOverlayImages); $i++) {
			$src = imagecreatefrompng("overlayImages/" . $data->selectedOverlayImages[$i] . ".png");
			$src = imagescale($src, $wd, $ht);
			imagecopymerge_alpha($dest, $src, 0, 0, 0, 0, $wd, $ht, 100);
			imagedestroy($src);
		}
		ob_start();
  	imagepng($dest);
  	$final_image_data = ob_get_contents();
		ob_end_clean();
		$final_image_data_base64 = base64_encode($final_image_data);
		$final_full_image_url = "data:image/png;base64," . $final_image_data_base64;

		require_once(__DIR__ . "/../Model/manageDatabase.php");
		$db = new ManageDatabase;
		$db->createPicture($final_full_image_url, $_SESSION['account']);

		imagedestroy($dest);
		exit();
		//When posting with 'Content-Type: application/x-www-form-urlencoded' the '+' gets transformed into ' ' which we need to revert for the image data to be correct
		// $_POST['imageData'] = str_replace(" ", "+", $_POST['imageData']);
	}
?>

<?php require(__DIR__ . "/../View/header/in-app-header.php"); ?>

<h1 class="pageTitle">Create Picture</h1>
<form method="get">
    <input name="modeOfPicture" type="submit" value="webcam"/>
		<input name="modeOfPicture" type="submit" value="upload"/>
</form>
<div class="wrapperPictureEditing">
<div id="getPicture" class="block1PictureEditing"></div>
<canvas id="takePictureCanvas" style="display:none;" width="320" height="240"></canvas>
<form id="takePictureButton" class="block2PictureEditing">
	<p>Choose Overlay Image:</p>
	<label><input type="checkbox" name="overlay" value="wine"> Wine</label><br>
	<label><input type="checkbox" name="overlay" value="boxes"> Boxes</label><br>
	<label><input type="checkbox" name="overlay" value="plumber"> Plumber</label><br>
	<label><input type="checkbox" name="overlay" value="hotdog"> Hotdog</label><br>
	<label><input type="checkbox" name="overlay" value="shirt"> Shirt</label><br><br>
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
  } else { selectedPictureDisplay.removeAttribute('src'); }
}

function takePicture(camera) {
	const takePictureButton = document.getElementById('takePictureButton');

	takePictureButton.addEventListener("submit", async (e) => {
		let canvas = document.getElementById('takePictureCanvas');
		var selectedOverlayImages = [];
		for (const selectedOverlayImage of document.querySelectorAll('input[name="overlay"]:checked').values()) {
			selectedOverlayImages.push(selectedOverlayImage.value);
		}

		if (camera) {
			const stream = document.querySelector('video');
			canvas.getContext('2d').drawImage(stream, 0, 0, canvas.width, canvas.height);
		} else {
			const selectedImage = document.getElementById('selectedPictureDisplay');
			canvas.getContext('2d').drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
		}

		function isCanvasBlank(canvas) {
  		const context = canvas.getContext('2d');
  		const pixelBuffer = new Uint32Array(context.getImageData(0, 0, canvas.width, canvas.height).data.buffer);
  		return !pixelBuffer.some(color => color !== 0);
		}

		if (isCanvasBlank(canvas)) {
			camera ? alert("Webcam not ready for taking picture...") : alert("Upload an image before taking picture...");
			return;
		}

		e.preventDefault(); //Resolves the bug of fetch not working on firefox (https://stackoverflow.com/questions/42719041/how-to-resolve-typeerror-networkerror-when-attempting-to-fetch-resource)
		await fetch("http://localhost:8000/picture-editing.php", {
			method: "POST",
			headers: {'Content-Type': 'application/json'},
			body: JSON.stringify({
				backgroundImageData: canvas.toDataURL('image/png'),
				selectedOverlayImages: selectedOverlayImages
			})
		});
		await new Promise(resolve => setTimeout(resolve, 200)); //Wait a little bit before refresh to make sure the picture is ready
		window.location.reload();
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
	var modeOfPicture = (new URLSearchParams(document.location.search)).get('modeOfPicture');

	if (modeOfPicture === null || modeOfPicture === "webcam") {
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
	} else {
		getPictureHTML.insertAdjacentHTML('afterbegin',
			"<label><input type='file' name='selectedPicture' accept='image/*' " +
			"onchange='displaySelectedPicture(this);'><br>Upload image<br></label>" +
			"<br><img id='selectedPictureDisplay' width='320' height='240'/>");
		takePicture(false);
	}
}

init();

//OLD FUNCTION TO OVERLAY IMAGES WRITTEN IN PURE JAVASCRIPT
//FUNCTIONAL ON CHROME BUT NOT FIREFOX
//NOT SERVER SIDE WHICH IS THE MAIN REASON WHY I REWRITE THE FUNCTION IN PHP
/*
takePictureButton.addEventListener("submit", () => {
	let canvas = document.getElementById('takePictureCanvas');
	let canvasContext = canvas.getContext('2d');
	selectedOverlayImages = document.querySelectorAll('input[name="overlay"]:checked');

	if (camera) {
		const stream = document.querySelector('video');
		canvasContext.drawImage(stream, 0, 0, canvas.width, canvas.height);
	} else {
		const selectedImage = document.getElementById('selectedPictureDisplay');
		canvasContext.drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
	}

	if (canvas.toDataURL('image/png') === "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUAAAADwCAYAAABxLb1rAAAAAXNSR0IArs4c6QAABvNJREFUeF7t1AERAAAIAjHpX9ogPxswPHaOAAECUYFFc4tNgACBM4CegACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AgAH0AwQIZAUMYLZ6wQkQMIB+gACBrIABzFYvOAECBtAPECCQFTCA2eoFJ0DAAPoBAgSyAgYwW73gBAgYQD9AgEBWwABmqxecAAED6AcIEMgKGMBs9YITIGAA/QABAlkBA5itXnACBAygHyBAICtgALPVC06AwAMbTgDxXhToPAAAAABJRU5ErkJggg==")
	{
		camera ? alert("Webcam not ready for taking picture...") : alert("Upload an image before taking picture...");
		return;
	}

	var Promises = [];
	for (const selectedOverlayImage of selectedOverlayImages.values()) {
		Promises.push(new Promise((resolve, reject) => {
			const overlayImg = new Image();
			overlayImg.onerror = () => { console.log(`Image ${selectedImage.value} loading error`); reject(); };
			overlayImg.onload = () => {
				canvasContext.drawImage(overlayImg, 0, 0, canvas.width, canvas.height);
				resolve();
			};
			overlayImg.src = `overlayImages/${selectedOverlayImage.value}.png`;
			console.log(selectedOverlayImage.value);
		}));
	}

	var ret = Promise.all(Promises).then(() => {
		let imageData = canvas.toDataURL('image/png');
		fetch("http://localhost:8000/picture-editing.php", { //Call php to create picture in database
			method: "POST",
			headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
					body: `imageData=${imageData}`
		});
		alert('Images successfully loaded!'); //This creates a gain of time before the refresh, allowing the new picture to be present more often after refresh
		window.location.reload();
	}).catch((error) => {console.log(error);});
});
*/
</script>
