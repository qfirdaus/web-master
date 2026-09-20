/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

//For react template
jQuery(function ($) {
    'use strict';

    function setupSliderControls($slider) {
        if ($slider.data('navigation-ready')) {
            return;
        }

        var $addon = $slider.closest('[id^="sppb-addon-"]');
        var $dots = $addon.children('.owl-dots');
        var $slides = $slider.children('.sppb-slideshow-fullwidth-item');

        function showFallbackSlide(index) {
            var slideCount = $slides.length;
            if (!slideCount) {
                return;
            }

            index = (index + slideCount) % slideCount;
            $slider.data('fallback-active-index', index);
            $slides.removeClass('is-fallback-active').eq(index).addClass('is-fallback-active');
            $dots.children('.owl-dot').removeClass('active').eq(index).addClass('active');
        }

        $slider.data('navigation-ready', true);
        $addon.find('.sppbSlidePrev').off('click.bannerNavigation').on('click.bannerNavigation', function (event) {
            event.preventDefault();
            if ($slider.hasClass('owl-loaded')) {
                $slider.trigger('prev.owl.carousel', [400]);
            } else {
                showFallbackSlide(($slider.data('fallback-active-index') || 0) - 1);
            }
        });
        $addon.find('.sppbSlideNext').off('click.bannerNavigation').on('click.bannerNavigation', function (event) {
            event.preventDefault();
            if ($slider.hasClass('owl-loaded')) {
                $slider.trigger('next.owl.carousel', [400]);
            } else {
                showFallbackSlide(($slider.data('fallback-active-index') || 0) + 1);
            }
        });
        $dots.children('.owl-dot').off('click.bannerNavigation').on('click.bannerNavigation', function (event) {
            event.preventDefault();
            var index = $(this).index();
            if ($slider.hasClass('owl-loaded')) {
                $slider.trigger('to.owl.carousel', [index, 400, true]);
            } else {
                showFallbackSlide(index);
            }
        });
        $slider.on('changed.owl.carousel.bannerNavigation', function (event) {
            if (event.page && typeof event.page.index !== 'undefined') {
                $dots.children('.owl-dot').removeClass('active').eq(event.page.index).addClass('active');
            }
        });
    }

    function initializeFallbackSlider($slider) {
        var autoplayEnabled = $slider.attr('data-sppb-slide-ride') === 'true';
        setupSliderControls($slider);

        if ($slider.hasClass('owl-loaded')) {
            if (!$slider.data('rotation-watchdog')) {
                var owlSlideCount = $slider.find('.owl-item:not(.cloned)').length;
                if (autoplayEnabled && owlSlideCount > 1) {
                    $slider.data('rotation-watchdog', true);
                    window.setInterval(function () {
                        $slider.trigger('next.owl.carousel', [600]);
                    }, 5000);
                }
            }
            return;
        }

        if ($slider.data('fallback-slider')) {
            return;
        }

        var $slides = $slider.children('.sppb-slideshow-fullwidth-item');
        if (!$slides.length) {
            return;
        }

        var activeIndex = 0;
        $slider.data('fallback-slider', true);
        $slider.data('fallback-active-index', activeIndex);
        $slider.addClass('has-fallback-slider');
        $slides.removeClass('is-fallback-active').eq(activeIndex).addClass('is-fallback-active');

        if (autoplayEnabled && $slides.length > 1) {
            window.setInterval(function () {
                activeIndex = (($slider.data('fallback-active-index') || 0) + 1) % $slides.length;
                $slider.data('fallback-active-index', activeIndex);
                $slides.removeClass('is-fallback-active').eq(activeIndex).addClass('is-fallback-active');
                $slider.closest('[id^="sppb-addon-"]').children('.owl-dots').children('.owl-dot')
                    .removeClass('active').eq(activeIndex).addClass('active');
            }, 5000);
        }
    }

    // The legacy Owl bundle can fail before adding `owl-loaded`. Run this
    // after its normal document-ready handler and provide a safe fallback.
    window.setTimeout(function () {
        $('.sppb-slider-fullwidth-wrapper #slide-fullwidth').each(function () {
            initializeFallbackSlider($(this));
        });
    }, 250);

    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            var newNodes = mutation.addedNodes;
            if (newNodes !== null) {
                var $nodes = $(newNodes);

                $nodes.each(function () {
                    var $node = $(this);
                    $node.find('#slide-fullwidth').each(function () {
                        var $slideFullwidth = $(this);

                        // Autoplay
                        var $autoplay = $slideFullwidth.attr('data-sppb-slide-ride');
                        if ($autoplay == 'true') {
                            var $autoplay = true;
                        } else {
                            var $autoplay = false
                        }
                        ;

                        // controllers
                        var $controllers = $slideFullwidth.attr('data-sppb-slidefull-controllers');
                        if ($controllers == 'true') {
                            var $controllers = true;
                        } else {
                            var $controllers = false
                        }
                        ;
                        $slideFullwidth.owlCarousel({
                            margin: 0,
                            loop: true,
                            video: true,
                            autoplay: $autoplay,
                            animateIn: 'fadeIn',
                            animateOut: 'fadeOut',
                            autoplayHoverPause: true,
                            autoplaySpeed: 1500,
                            responsive: {
                                0: {
                                    items: 1
                                },
                                600: {
                                    items: 1
                                },
                                1000: {
                                    items: 1
                                }
                            },
                            dots: $controllers,
                        });

                        window.setTimeout(function () {
                            initializeFallbackSlider($slideFullwidth);
                        }, 250);

                    });
                });
            }
        });
    });

    var config = {
        childList: true,
        subtree: true
    };
    // Pass in the target node, as well as the observer options
    observer.observe(document.body, config);
});
