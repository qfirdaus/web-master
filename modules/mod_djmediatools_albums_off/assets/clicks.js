
    jQuery(document).ready(function () {

        jQuery('.dj-slide a[data-id]').click(function () {
            if (!jQuery(this).attr('clicked')) {
                var item_id = jQuery(this).data('id').split(":");
                if (item_id[0]) {
                    jQuery(this).attr('clicked', true);
                    jQuery.ajax({
                        url: "index.php?option=com_ajax&module=djmediatools_albums&method=incClicks&format=json",
                        type: "POST",
                        data: {item_id: item_id[0]}
                    });
                }
            }
            return true;
        });


    });
