Dropzone.options.sliderDropzone = {
  acceptedFiles: '.png, .jpg, .jpeg, .mp4, .webm, .ogg, .mov',
  paramName: 'slider_images',
  url: uploadSliderImage,
  method: 'post',
  success: function (file, response) {
    $('#slider-image-id').append(`<input type="hidden" id="img-${response.file_id}" name="slider_images[]" value="${response.file_id}">`);

    // create remove button
    const rmvBtn = Dropzone.createElement("<button class='rmv-btn'><i class='fa fa-times'></i></button>");

    // capture the dropzone instance as closure
    let _this = this;

    // bind an event to the remove button
    rmvBtn.addEventListener('click', function (event) {

      // make sure the button click event doesn't submit the form
      event.preventDefault();
      event.stopPropagation();

      // remove image from dropzone preview
      _this.removeFile(file);
      // remove image from storage
      rmvImg(response.file_id);
    });

    // add the remove button to the file preview element
    file.previewElement.appendChild(rmvBtn);
  },
  error: function (file, message) {
    $('#err_slider_image').text(message.error.slider_images[0]);

    // create remove button
    const rmvBtn = Dropzone.createElement("<button class='rmv-btn'><i class='fa fa-times'></i></button>");

    // capture the dropzone instance as closure
    let _this = this;

    // bind an event to the remove button
    rmvBtn.addEventListener('click', function (event) {
      // make sure the button click event doesn't submit the form
      event.preventDefault();
      event.stopPropagation();

      // remove video from dropzone preview
      _this.removeFile(file);
    });

    // add the remove button to the file preview element
    file.previewElement.appendChild(rmvBtn);
  }
};

function rmvImg(unqName) {
  $(".request-loader").addClass("show");
  $.ajax({
    url: imgRmvUrl,
    type: 'POST',
    data: { 'imageName': unqName },
    success: function (response) {
      const image = $(`#img-${unqName}`);
      image.remove();
    },
    error: function (response) {
    }
  }).always(function (param) {
    $(".request-loader").removeClass("show");
  });
}

function rmvStoredImg(id, key) {
  $(".request-loader").addClass("show");
  $.ajax({
    url: imgDetachUrl,
    type: 'POST',
    data: { 'id': id, 'key': key },
    success: function (response) {
      $(`#slider-image-${key}`).remove();
      let content = {};

      content.message = response.message;
      content.title = 'Success';
      content.icon = 'fas fa-check-circle';

      $.notify(content, {
        type: 'success',
        placement: {
          from: 'top',
          align: 'right'
        },
        showProgressbar: true,
        time: 1000,
        delay: 4000
      });

      $('#reload-slider-div').load(location.href + ' #reload-slider-div');
    },
    error: function (response) {
      let content = {};

      content.message = response.responseJSON.message;
      content.title = 'Error';
      content.icon = 'fas fa-times-circle';

      $.notify(content, {
        type: 'danger',
        placement: {
          from: 'top',
          align: 'right'
        },
        showProgressbar: true,
        time: 1000,
        delay: 4000
      });
    }
  }).always(function (param) {
    $(".request-loader").removeClass("show");
  });
}
