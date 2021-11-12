jQuery(function($) {
    var clinic_btn = '<div class="os-row os-row-btn"><div class="os-col-12"><a href="#" class="latepoint-btn latepoint-skip-datetime-btn" data-pre-last-step-label="Submit" data-label="Next Step"><span>I\'m at the clinic</span></a></div></div>';

    setInterval(function() {
        if($('.latepoint-body .latepoint-footer.request-move').length) {
            $('.latepoint-body .latepoint-footer.request-move').css('display', 'flex').appendTo('.latepoint-form');
            $('.latepoint-body').css('padding-bottom', '15px');
        }


        if($('.step-datepicker-w').length && !$('.step-datepicker-w os-row-btn').length) {
            $('.step-datepicker-w').append(clinic_btn);
        }
    }, 100);

    $('.latepoint-skip-datetime-btn').on('click', function() {
        $('.latepoint_start_date').val('$date');
        $('.latepoint_start_time').val('$time');
        $('.os-row-btn').hide();
        return $(this).closest('.latepoint-form').submit();
    });
});
