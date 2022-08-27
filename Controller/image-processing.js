async function imageProcessing(camera, canvas, canvasContext, overlayImg, stream, selectedImage) {
  if (camera) {
    canvasContext.drawImage(stream, 0, 0, canvas.width, canvas.height);
  } else {
    canvasContext.drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
  }

  function createImg() {
    console.log("IN");
    canvasContext.drawImage(overlayImg, 0, 0, canvas.width, canvas.height);
    let imageData = canvas.toDataURL('image/png');
    //Call php to create picture in database
    fetch("http://localhost:8000/picture-editing.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
          body: `imageData=${imageData}`
    });
    alert('Image loaded!');
  }

  if (overlayImg.complete) { //Certain images are already loaded but still need to go through createImg function
    console.log(1);
    createImg();
  } else {
    console.log(2);
    await new Promise((resolve) => { overlayImg.onload = resolve; });
    createImg();
  }
}
