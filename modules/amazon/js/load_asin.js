var progressBar = document.getElementById("progress"),
  loadBtn = document.getElementById("button"),
  display = document.getElementById("display");

function upload(data) {
 $('#box_percentuale_asin').show();
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "/moduled/amazon/index.php?action=progress", true);
  if (xhr.upload) {
    xhr.upload.onprogress = function(e) {
      if (e.lengthComputable) {
        progressBar.max = e.total;
        progressBar.value = e.loaded;
        display.innerText = Math.floor((e.loaded / e.total) * 100) + '%';
      }
    }
    xhr.upload.onloadstart = function(e) {
      progressBar.value = 0;
      display.innerText = '0%';
    }
    xhr.upload.onloadend = function(e) {
      progressBar.value = e.loaded;
      loadBtn.disabled = false;
      loadBtn.innerHTML = 'Start uploading';
    }
  }
  xhr.send(data);
}

function buildFormData() {
  var fd = new FormData();

  for (var i = 0; i < 3000; i += 1) {
    fd.append('data[]', Math.floor(Math.random() * 999999));
  }

  return fd;
}

loadBtn.addEventListener("click", function(e) {
  this.disabled = true;
  this.innerHTML = "Uploading...";
  upload(buildFormData());
});