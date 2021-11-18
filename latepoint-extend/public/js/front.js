jQuery(function($) {
    var clinic_btn = '<div class="os-row os-row-btn"><div class="or"><span>or</span></div><div class="os-col-12"><a href="#" class="latepoint-btn latepoint-skip-datetime-btn" data-pre-last-step-label="Submit" data-label="Next Step"><span>I\'m at the clinic</span></a></div></div>';
    var clinic_notice = '<div class="latepoint-desc-content">If you are booing for a future time: </div>';
    var clinic_notice2 = '<p>&nbsp;</p><div class="latepoint-desc-content">If you are in clinic already:</div><div class="latepoint-desc-content">Click the button "I\'m at the clinic".</div>';

    setInterval(function() {
        if($('.latepoint-body .latepoint-footer.request-move').length) {
            $('.latepoint-body .latepoint-footer.request-move').css('display', 'flex').appendTo('.latepoint-form');
            $('.latepoint-body').css('padding-bottom', '15px');
        }


        if((typeof(show_btn) != 'undefined') && $('.step-datepicker-w').length && !$('.step-datepicker-w .os-row-btn').length) {
            $('.step-datepicker-w').append(clinic_btn);
            $('.latepoint-step-desc .latepoint-desc-media').after(clinic_notice);
            $('.latepoint-step-desc').append(clinic_notice2);
        }
    }, 100);

    $('body').on('click', '.latepoint-body .latepoint-skip-datetime-btn', function() {
        $('.latepoint-body .latepoint_start_date').val(start_date);
        $('.latepoint-body .latepoint_start_time').val(start_time);
        return $('.latepoint-form').submit();
    });
});
start_date = '';
start_time = '';
function set_start(date, time) {
    start_date = date;
    start_time = time;
}
