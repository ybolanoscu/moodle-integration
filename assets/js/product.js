(function ($) {
    $('input[name^="_product_availability_"]').datepicker({
        defaultDate: '',
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: true,
    });
})(jQuery);