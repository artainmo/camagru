function imageProcessing(camera, canvas, canvasContext, selectedOverlayImage, stream, selectedImage) {
  let loaded = false;
  let overlayImg = new Image();

  if (camera) {
    canvasContext.drawImage(stream, 0, 0, canvas.width, canvas.height);
  } else {
    canvasContext.drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
  }

  function createImg() {
    if (loaded) { return ; } else { loaded = true; }
    canvasContext.drawImage(overlayImg, 0, 0, canvas.width, canvas.height);
    let imageData = canvas.toDataURL('image/png');
    //Call php to create picture in database
    fetch("http://localhost:8000/picture-editing.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
          body: `imageData=${imageData}`
    });
  }

  overlayImg.addEventListener('load', createImg);
  overlayImg.src = `overlayImages/${selectedOverlayImage}.png`;
  if (overlayImg.complete) { createImg(); } //Certain images are already loaded but still need to go through createImg function
}
