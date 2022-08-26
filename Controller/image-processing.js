async function imageProcessing(camera, canvas, canvasContext, overlayImg, stream, selectedImage) {
  if (camera) {
    await canvasContext.drawImage(stream, 0, 0, canvas.width, canvas.height);
  } else {
    await canvasContext.drawImage(selectedImage, 0, 0, canvas.width, canvas.height);
  }

  overlayImg.onload = async () => {
    await canvasContext.drawImage(overlayImg, 0, 0, canvas.width, canvas.height);
    let imageData = await canvas.toDataURL('image/png');
    //Call php to create picture in database
    await fetch("http://localhost:8000/picture-editing.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
          body: `imageData=${imageData}`
    });
    await window.location.reload();//Refresh the page to see new picture in picture list
  }
}
