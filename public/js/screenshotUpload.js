$(function(){
  $("#drop-box").click(function(){
    $("#screenshot").click();
  });

  $(".image-preview").click(function() {
    var id = $(this).attr('ssid');
    $("#image-viewer").show();
    $("#image-view").attr('src', 'http://cdn.blocklandglass.com/screenshots/' + id);
    $("#image-view-ssid").val(id);
  });

  $("#image-viewer").click(function(evt) {
    console.log(evt);
    if(evt.target == this || evt.target.id == 'image-view') {
      $(this).hide();
    }
  })

  // To prevent Browsers from opening the file when its dragged and dropped on to the page
  $(document).on('drop dragover', function (e) {
        e.preventDefault();
    });

  // Add events
  $('input[type=file]').on('change', fileUpload);

  // File uploader function

  function fileUpload(event){
    $("#drop-box").html("<p>Uploading...</p>");
    files = event.target.files;
    var data = new FormData();
    var error = 0;
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        console.log(file.size);
      if(!file.type.match('image.*')) {
          $("#drop-box").html("<p> Images only. Select another file</p>");
          error = 1;
        } else if(file.size > 1048576){
          $("#drop-box").html("<p> Too large Payload. Select another file</p>");
          error = 1;
        }else{
          data.append('image', file, file.name);
        }
    }
    if(!error) {
      var xhr = new XMLHttpRequest();

      xhr.open('POST', '/ajax/uploadScreenshot.php?id=' + addonId, true);
      xhr.send(data);

      $("#screenshot").hide();

      xhr.onprogress = function(evt) {
        if (evt.lengthComputable) {
          var percentComplete = (evt.loaded / evt.total)*100;
          if(percentComplete < 100) {
            $("#drop-box").html('<p><b>Uploading...<b><br />' + percentComplete + '%</p>');
          }
          //console.log(percentComplete);
        }
      };

      xhr.onload = function() {
        try {
          var res = JSON.parse(xhr.response);
        } catch (e) {
          $("#drop-box").html("<p>Server sent invalid response!</p>");
          console.log(xhr.response);
          return;
        }

        if(xhr.status !== 200) {
          $("#drop-box").html("<p>Unable to upload file<br />Status " + xhr.status + "</p>");
          return;
        } else if(res.status !== "success") {
          $("#drop-box").html("<p>Unable to upload file<br />" + res.status + " <b>" + res.error + "</b></p>");
          return;
        }

        //display
        $('#continue').removeClass('red');
        $('#continue').addClass('green');
        $('#continue').html('Continue');
        $('#previews').append('<div class="tile image-preview"><img src="http://cdn.blocklandglass.com/screenshots/thumb/' + res.id + '"></div>');

        //good to go
        $("#drop-box").html("<p><b>Uploaded!</b> Want to upload another?</p>");

        //reset input,

      };
    }
  }
});
