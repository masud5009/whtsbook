
(function ($) {
    "use strict";

    /*============================================
    // Preloader
    ============================================*/
    if ($('.preloader').length > 0) {
        window.onload = function () {
            const preloader = document.querySelector('.preloader');
            preloader.classList.add('hidden');
        };
    }

    // header-next
    var getHeaderHeight = function () {
        var headerNext = $(".header-next");
        var header = $(".header-area");
        var headerHeight = header.height();
        headerNext.css({
            "margin-top": headerHeight + "px"
        });
    }
    getHeaderHeight();

    $(window).on('resize', function () {
        getHeaderHeight();
    });


    /*---------------------------
        Magic Cursor
    /----------------------------*/
    if ($('.magic-cursor').length > 0) {

        const cursor = document.querySelector('.magic-cursor');
        const cursorInner = document.querySelector('.magic-cursor-inner');
        const hoverTargets = document.querySelectorAll('a, button, .cursor-hover-target');

        let mouseX = 0, mouseY = 0;
        let cursorX = 0, cursorY = 0;
        let innerX = 0, innerY = 0;

        document.addEventListener('mousemove', e => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        function animateCursor() {
            // Smooth interpolation
            cursorX += (mouseX - cursorX) * 0.15;
            cursorY += (mouseY - cursorY) * 0.15;
            innerX += (mouseX - innerX) * 0.25;
            innerY += (mouseY - innerY) * 0.25;

            cursor.style.left = `${cursorX}px`;
            cursor.style.top = `${cursorY}px`;

            cursorInner.style.left = `${innerX}px`;
            cursorInner.style.top = `${innerY}px`;

            requestAnimationFrame(animateCursor);
        }

        animateCursor();

        // Link hover effects
        hoverTargets.forEach(el => {
            el.addEventListener('mouseenter', () => {
                cursor.classList.add('cursor-hover');
                cursorInner.classList.add('cursor-inner-hover');
            });
            el.addEventListener('mouseleave', () => {
                cursor.classList.remove('cursor-hover');
                cursorInner.classList.remove('cursor-inner-hover');
            });
        });
    }

    /*---------------------------
        Animate Button  
    /----------------------------*/
    document.querySelectorAll(".anim-btn").forEach(button => {
        // Create a span element dynamically if it doesn't exist
        let span = button.querySelector("span");
        if (!span) {
            span = document.createElement("span");
            button.appendChild(span);
        }

        // Add the mousemove event listener
        button.addEventListener("mousemove", e => {
            const rect = button.getBoundingClientRect();
            span.style.position = "absolute"; // Ensure the span is positioned correctly
            span.style.top = `${e.clientY - rect.top}px`;
            span.style.left = `${e.clientX - rect.left}px`;
        });
    });

    /*============================================
    nice select
    ============================================*/
    $(document).ready(function () {
        $('.nice-select').niceSelect();
    });

    /*============================================
    Select2
    ============================================*/
    $('.select2').select2();

    /*============================================
        Youtube popup
    ============================================*/
    $(".youtube-popup").magnificPopup({
        disableOn: 300,
        type: "iframe",
        mainClass: "mfp-fade",
        removalDelay: 160,
        preloader: false,
        fixedContentPos: false
    })

    /*============================================
    AOS js init
    ============================================*/

    document.addEventListener("DOMContentLoaded", function () {
        AOS.init({
            easing: "ease",
            duration: 1200,
            once: true,
            offset: 0,
            disable: "mobile"
        });
    });

    /*============================================
    wow js init
    ============================================*/
    new WOW().init();

    // =============  Dynamic Year ========= 
    if ($('.dynamic-year').length > 0) {
        const yearElement = document.querySelector('.dynamic-year');
        const currentYear = new Date().getFullYear();
        yearElement.innerHTML = currentYear;
    }

    /******************************
    Tol Tip
    ********************************/
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    /*============================================
    Toggle List
    ============================================*/
    $("[data-toggle-list]").each(function () {

        var show_more = "Show More +";
        var show_less = "Show Less -";

        var list = $(this).children();
        var listShow = $(this).data("toggle-show");
        var listShowBtn = $(this).next("[data-toggle-btn]");

        var showMoreText = show_more + '';
        var showLessText = show_less + '';

        if (list.length > listShow) {
            listShowBtn.show();
            list.slice(listShow).hide();
            listShowBtn.on("click", function () {
                var isExpanded = listShowBtn.text() === showLessText;
                list.slice(listShow).slideToggle(300);
                listShowBtn.text(isExpanded ? showMoreText : showLessText);
            });
        } else {
            listShowBtn.hide();
        }
    });

    /*--------------------------------------------------------
    /  04. password Toggle
    /--------------------------------------------------------*/
    $(".show-password-field").on("click", function () {
        var showIcon = $(this).children(".show-icon");
        var passwordField = $(this).prev("input");
        showIcon.toggleClass("show");
        if (passwordField.attr("type") == "password") {
            passwordField.attr("type", "text")
        } else {
            passwordField.attr("type", "password");
        }
    })

    /*============================================
        data att background image
    ============================================*/
    function lazyLoadBackground() {
        $(".bg-img").each(function () {
            var el = $(this);
            if (el.attr("data-bg-image") && el.is(":visible") && el.offset().top < $(window).scrollTop() + $(window).height()) {
                var src = el.attr("data-bg-image");
                el.css({
                    "background-image": "url(" + src + ")",
                }).removeAttr("data-bg-image");
            }
        });
    }
    lazyLoadBackground();
    $(window).on("scroll", lazyLoadBackground);


    /*============================================
    Image to background image
    ============================================*/
    $(".img-to-bg.blur-up").parent().addClass('blur-up lazyload');

    $(".img-to-bg").each(function () {
        var el = $(this), src = el.attr("src"), parent = el.parent();

        parent.css({
            "background-image": "url(" + src + ")",
            "background-size": "cover",
            "background-position": "center",
            "display": "block"
        });

        el.hide();
    });

    /*============================================
        Lazyload image
    ============================================*/
    var lazyLoad = function () {
        window.lazySizesConfig = window.lazySizesConfig || {};
        window.lazySizesConfig.loadMode = 2;
        lazySizesConfig.preloadAfterLoad = true;

        var lazyContainer = $(".lazy-container");

        if (lazyContainer.children(".lazyloaded")) {
            lazyContainer.addClass("lazy-active")
        } else {
            lazyContainer.removeClass("lazy-active")
        }
    }

    $(document).ready(function () {
        lazyLoad();
    })

    // Elements to inject
    var mySVGsToInject = document.querySelectorAll('img.img-to-svg');

    // Do the injection
    SVGInjector(mySVGsToInject);

    /*============================================
        default Slider
    ============================================*/
    $(".default-slider").each(function () {
        var web_slider = $(this);
        var id = web_slider.attr("id");
        var sliderId = "#" + id;

        var isCentered = web_slider.data("center");
        var isloop = web_slider.data("loop");

        var swiper = new Swiper(sliderId, {
            spaceBetween: web_slider.data("slidespace"),
            centeredSlides: isCentered === true || isCentered === "true" ? true : false,
            loop: isloop === true || isloop === "true" ? true : false,
            speed: 1000,
            rtl: $('html').attr('dir') === 'rtl',
            pagination: {
                el: sliderId + "-pagination",
                clickable: true,
            },

            navigation: {
                nextEl: sliderId + "-next",
                prevEl: sliderId + "-prev",
            },

            breakpoints: {
                0: {
                    slidesPerView: web_slider.data("xsmview"),
                },
                420: {
                    slidesPerView: web_slider.data("smview"),
                },
                768: {
                    slidesPerView: web_slider.data("mdview"),
                },
                992: {
                    slidesPerView: web_slider.data("lgview"),
                },
                1199: {
                    slidesPerView: web_slider.data("xlview"),
                }
            },
        });
    });


    /**--- hero Slider -----**/
    var swiper = new Swiper(".hero-slider-thumb", {
        spaceBetween: 10,
        slidesPerView: 2,
        direction: "vertical",
        watchSlidesProgress: true,
        breakpoints: {
            0: {
                direction: "horizontal"
            },
            992: {
                direction: "vertical"
            }
        }
    });
    var swiper2 = new Swiper(".hero-slider-main", {
        spaceBetween: 10,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        thumbs: {
            swiper: swiper,
        },

        on: {
            slideChangeTransitionStart: function () {
                document
                    .querySelectorAll('.swiper-slide [data-aos]')
                    .forEach(el => {
                        el.classList.remove('aos-animate');
                    });

                document
                    .querySelectorAll('.swiper-slide-active [data-aos]')
                    .forEach(el => {
                        el.classList.add('aos-animate');
                    });
            }
        }
    });


    /** Brand Slider **/
    if ($('.brand-slider').length > 0) {
        var swiper = new Swiper(".brand-slider", {
            slidesPerView: 6,
            spaceBetween: 30,
            speed: 6000,
            loop: true,
            freeMode: true,
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            breakpoints: {
                0: {
                    slidesPerView: 2,
                    spaceBetween: 15,
                },
                320: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                480: {
                    slidesPerView: 2,
                },
                575: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 3,
                },
                992: {
                    slidesPerView: 4,
                },
                1200: {
                    slidesPerView: 5,
                },
                1400: {
                    slidesPerView: 6,
                }
            }
        });
    }





    // pricing-accordion
    $(document).ready(function () {
        $('.faqAccordion .accordion-collapse').on('show.bs.collapse', function () {
            //  faqAccordion active toggle
            $(this).closest('.faqAccordion').find('.accordion-item').removeClass('active');
            $(this).closest('.accordion-item').addClass('active');
        });

        $('.faqAccordion .accordion-collapse').on('hide.bs.collapse', function () {
            $(this).closest('.accordion-item').removeClass('active');
        });
    });

    // about-accordion
    $(document).ready(function () {
        $('.about-accordion .accordion-collapse').on('show.bs.collapse', function () {
            // about-accordion active toggle
            $(this).closest('.about-accordion').find('.accordion-item').removeClass('active');
            $(this).closest('.accordion-item').addClass('active');
        });

        $('.about-accordion .accordion-collapse').on('hide.bs.collapse', function () {
            $(this).closest('.accordion-item').removeClass('active');
        });
    });



})(jQuery);


