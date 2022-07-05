function imageProcessing(camera, canvas, canvasContext, overlayImg, stream, selectedImage) {
  if (camera) {
    canvasContext.drawImage(stream, 0, 0, canvas.width, canvas.height);
  } else {
    canvasContext.drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
  }

  overlayImg.onload = () => {
    canvasContext.drawImage(overlayImg, 0, 0, canvas.width, canvas.height);
    let imageData = canvas.toDataURL('image/png');
    //Call php to create picture in database
    fetch("http://localhost:8000/picture-editing.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
          body: `imageData=${imageData}`
    });
    window.location.reload();//Refresh the page to see new picture in picture list
  }
}
