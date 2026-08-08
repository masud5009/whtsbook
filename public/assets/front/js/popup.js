(function ($) {
  "use strict";

  $(window).on("load", function () {
    if ($(".popup-wrapper").length > 0) {
      var $firstPopup = $(".popup-wrapper").eq(0);
      popupAnnouncement($firstPopup);
    }
  });

  function popupAnnouncement($popup) {
    var closedPopups = [];
    if (sessionStorage.getItem("closedPopups")) {
      closedPopups = JSON.parse(sessionStorage.getItem("closedPopups"));
    }

    if (closedPopups.indexOf($popup.data("popup_id")) === -1) {
      $("#" + $popup.attr("id")).show();
      var popupDelay = $popup.data("popup_delay");

      setTimeout(function () {
        $.magnificPopup.open(
          {
            items: { src: "#" + $popup.attr("id") },
            type: "inline",
            callbacks: {
              afterClose: function () {
                closedPopups.push($popup.data("popup_id"));
                sessionStorage.setItem("closedPopups", JSON.stringify(closedPopups));
                if ($popup.next(".popup-wrapper").length > 0) {
                  popupAnnouncement($popup.next(".popup-wrapper"));
                }
              },
            },
          },
          0
        );
      }, popupDelay);
    } else if ($popup.next(".popup-wrapper").length > 0) {
      popupAnnouncement($popup.next(".popup-wrapper"));
    }
  }
})(jQuery);
