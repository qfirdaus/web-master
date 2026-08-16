
    function isVisible(row, container) {

        var elementTop = jQuery(row).offset().top,
            elementHeight = jQuery(row).height(),
            containerTop = container.scrollTop(),
            containerHeight = container.height();

        return ((((elementTop - containerTop) + elementHeight) > 0) && ((elementTop - containerTop) < containerHeight));
    }

    jQuery(document).ready(function () {

        jQuery(window).scroll(function () {
            jQuery('.dj-slide').each(function () {
                var slide_item = jQuery(this);
                var slide_visible = slide_item.css('visibility');
                if (isVisible(slide_item, jQuery(window)) && slide_visible == 'visible' && slide_item.attr('displayed') == null) {
                    var item_link = slide_item.find('a[data-id]');
                    if (typeof item_link !== 'undefined') {
                        var item_id = item_link.data('id').split(":");
                        if (item_id[0]) {
                            slide_item.attr('displayed', true);
                            jQuery.ajax({
                                url: "index.php?option=com_ajax&module=djmediatools_albums&method=incViews&format=json",
                                type: "POST",
                                data: {item_id: item_id[0]}
                            });
                        }
                    }
                }
                ;
            });
        });


    });

