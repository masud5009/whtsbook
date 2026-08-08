(function() {
            // Lightbox
            $('.room-gallery').magnificPopup({
                delegate: 'a.gallery-item',
                type: 'image',
                gallery: {
                    enabled: true
                },
                mainClass: 'mfp-fade'
            });

            // Thumb -> main swap
            $(document).on('click', '.gallery-thumb', function(e) {
                e.preventDefault();
                const src = $(this).data('src');
                const type = $(this).data('type');
                let mainHtml = '';
                if (type === 'video') {
                    mainHtml = `<video class="w-100 room-hero" controls><source src="${src}"></video>`;
                } else {
                    mainHtml =
                        `<a class="gallery-item" href="${src}"><img class="w-100 room-hero" src="${src}" alt="Room"></a>`;
                }
                $('#mainRoomMedia').html(mainHtml);

                $('.gallery-thumb').removeClass('active');
                $(this).addClass('active');
            });
        })();
