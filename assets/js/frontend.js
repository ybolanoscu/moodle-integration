$(function ($) {
    "use strict";

    $('button[id=apply_now]').click(function () {
        $.post(WPURLS.siteurl + '/wp-admin/admin-ajax.php', {
            action: 'periodic_ajax_apply_now',
            category_id: $(this).data('category-id'),
            class_id: $(this).data('class-id')
        }, function (response) {
            if (response.success)
                window.location = response.route;
        });
    });

    $(window).resize(function () {
        if ($('.vertical-tabs').innerWidth() > 608) {
            if ($('div.selected').length) {
            } else {
                $('div.box:first').addClass('selected');
            }
        }
    });

    $('ul.checklist-select li').click(function () {
        var selectID = $(this).attr('id');
        $('ul.checklist-select li').removeClass('active');
        $(this).addClass('active');
        $('div.box').removeClass('selected');
        $('.' + selectID + '-box').addClass('selected');
    });
});
