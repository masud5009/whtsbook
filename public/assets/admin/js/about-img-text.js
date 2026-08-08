"use strict";

(function ($) {
  const $dropzone = $("#aboutGalleryDropzone");
  if ($dropzone.length === 0 || typeof Dropzone === "undefined") {
    return;
  }

  const uploadUrl = $dropzone.data("upload-url");
  const deleteRouteTemplate = $dropzone.data("delete-route");
  const deleteText = $dropzone.data("delete-text") || "Delete";
  const errorText = $dropzone.data("error-text") || "Something went wrong!";
  const successTitle = $dropzone.data("success-title") || "Success";

  Dropzone.autoDiscover = false;

  new Dropzone("#aboutGalleryDropzone", {
    url: uploadUrl,
    method: "post",
    paramName: "image",
    acceptedFiles: ".jpg,.jpeg,.png",
    addRemoveLinks: true,
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
    success: function (file, response) {
      $("#errabout_gallery_images").text("");
      this.removeFile(file);

      const deleteUrl = deleteRouteTemplate.replace("__id__", response.id);
      const galleryCard = `
        <div class="col-md-4 mb-3" id="about-gallery-item-${response.id}">
          <div class="card">
            <div class="card-body p-2">
              <img src="${response.image_url}" alt="gallery-image" class="img-thumbnail w-100 mb-2">
              <button type="button" class="btn btn-danger btn-sm btn-block delete-about-gallery"
                data-url="${deleteUrl}" data-id="${response.id}">
                ${deleteText}
              </button>
            </div>
          </div>
        </div>
      `;

      $("#aboutGalleryList").prepend(galleryCard);
    },
    error: function (file, response) {
      this.removeFile(file);

      if (response && response.errors && response.errors.image && response.errors.image[0]) {
        $("#errabout_gallery_images").text(response.errors.image[0]);
      } else {
        $("#errabout_gallery_images").text(errorText);
      }
    },
  });

  $(document).on("click", ".delete-about-gallery", function (e) {
    e.preventDefault();

    const $btn = $(this);
    const url = $btn.data("url");
    const id = $btn.data("id");

    $(".request-loader").addClass("show");

    $.ajax({
      url: url,
      method: "POST",
      data: {
        _token: $('meta[name="csrf-token"]').attr("content"),
      },
      success: function (response) {
        $("#about-gallery-item-" + id).remove();

        const content = {
          message: response.message,
          title: successTitle,
          icon: "fa fa-bell",
        };

        $.notify(content, {
          type: "success",
          placement: {
            from: "top",
            align: "right",
          },
          showProgressbar: true,
          time: 1000,
          delay: 4000,
        });
      },
    }).always(function () {
      $(".request-loader").removeClass("show");
    });
  });
})(jQuery);

