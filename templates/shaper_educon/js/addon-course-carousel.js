/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

//For react template
jQuery(function ($) {
    'use strict';
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            var newNodes = mutation.addedNodes;
            if (newNodes !== null) {
                var $nodes = $(newNodes);

                $nodes.each(function () {
                    var $node = $(this);
                    $node.find('#carousel-courses-layout').each(function () {
                        // Full width Slideshow
                        var $latetCourses = $(this);

                        $latetCourses.owlCarousel({
                            margin: 30,
                            loop: true,
                            autoplay: false,
                            animateIn: 'fadeIn',
                            animateOut: 'fadeOut',
                            responsive: {
                                0: {
                                    items: 1
                                },
                                600: {
                                    items: 2
                                },
                                1000: {
                                    items: 3
                                }
                            },
                            dots: false,
                        });


                        $('.courseCarouselPrev').click(function () {
                            $latetCourses.trigger('prev.owl.carousel', [400]);
                        });

                        $('.courseCarouselNext').click(function () {
                            $latetCourses.trigger('next.owl.carousel', [400]);
                        });

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

