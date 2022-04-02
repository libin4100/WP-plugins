jQuery(function($) {
    var clinic_btn = '<div class="os-row os-row-btn"><div class="or"><span>or</span></div><div class="os-col-12"><a href="#" class="latepoint-btn latepoint-skip-datetime-btn" data-pre-last-step-label="Submit" data-label="Next Step"><span>I\'m at the clinic</span></a></div></div>';
    var clinic_notice = '<div class="latepoint-desc-content" id="latepoint-notice">If you are booing for a future time: </div>';
    var clinic_notice2 = '<p>&nbsp;</p><div class="latepoint-desc-content">If you are in clinic already:</div><div class="latepoint-desc-content">Click the button "I\'m at the clinic".</div>';
    var button_click = false;

    var show_notice = '<div class="os-row os-row-div"><div class="os-col-12"><h3>Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time.</h3></div></div>';
    var show_summary = '<div class="os-show-summary os-summary-line os-has-value" style="display: block;flex: 0 0 100%;"><div class="os-summary-value os-summary-value-notice" style="color:red">Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time. </div> </div>';
    first_payment = true;

    setInterval(function() {
        if($('.latepoint-body .latepoint-footer.request-move').length) {
            $('.latepoint-body .latepoint-footer.request-move').css('display', 'flex').appendTo('.latepoint-form');
            $('.latepoint-body').css('padding-bottom', '15px');
        }

        if($('.os-today').data('total-work-minutes')
            && ($('.os-today').data('work-start-time') <= start_time)
            && ($('.os-today').data('work-end-time') > start_time)
            && (typeof(show_btn) != 'undefined')
            && $('.step-datepicker-w').length
            && !$('.step-datepicker-w .os-row-btn').length
        ) {
            $('.step-datepicker-w').append(clinic_btn);
        }
        if($('.latepoint-body .latepoint-skip-datetime-btn').length && $('.latepoint-body .latepoint-skip-datetime-btn').is(':visible')) {
            if(!$('#latepoint-notice').length) {
                $('.latepoint-step-desc .latepoint-desc-media').after(clinic_notice);
                $('.latepoint-step-desc').append(clinic_notice2);
            }
            //if($('#at_clinic').length) $('#at_clinic').remove();
        }
        if(typeof(is_rapid) != 'undefined' && is_rapid) {
            if($('.dp-timeslot.with-tick.selected').length && !showed) {
                $('.step-datepicker-w').append(show_notice);
                $('.os-summary-lines').append(show_summary);
                showed = true;
            }
            if(!$('.dp-timeslot.with-tick.selected').length && showed) {
                $('.os-row-div').remove();
                $('.os-show-summary').remove();
                showed = false;
            }
        }
        if($('.latepoint-payment').length && first_payment) {
            $('.latepoint-lightbox-close').hide();
            $('.latepoint-form-w .latepoint-heading-w .os-heading-text-library[data-step-name="confirmation"]').text('Appointment Request');
            $('.confirmation-app-info ul li').eq(1).html($('.confirmation-app-info ul li').eq(1).html().replace('Time', 'Requested Time'))
            first_payment = false;
        }

        if($('.latepoint-prev-btn').length) {
            $('.latepoint-prev-btn').bind('click', function() {
                $('.latepoint-footer .latepoint-next-btn span').text($('.latepoint-footer .latepoint-next-btn').data('label'));
            });
        }
    }, 100);

    $('body').on('click', '.latepoint-body .latepoint-skip-datetime-btn', function() {
        $('.latepoint-body .latepoint_start_date').val(start_date);
        $('.latepoint-body .latepoint_start_time').val(start_time);
        if(!$('#at_clinic').length)
            $('.latepoint-form').append('<input type="hidden" name="booking[at_clinic]" value="1" id="at_clinic">');
        return $('.latepoint-form').submit();
    });

    $('body').on('mouseover', '.latepoint-body .mbc-help', function() {
        $('.latepoint-summary-w').append('<img class="mbc-image" src="/wp-content/uploads/2022/04/mbc-2.png" />');
    });
    $('body').on('click', '.latepoint-body .mbc-help', function() {
        if($('.latepoint-body .mbc-image').length)
            $('.latepoint-body .mbc-image').remove();
        else
            $('.latepoint-body .step-custom-fields-for-booking-w').append('<img class="mbc-image" src="/wp-content/uploads/2022/03/mbc-1.png" />');
    });
    $('body').on('mouseout', '.latepoint-body .mbc-help', function() {
        $('.latepoint-summary-w .mbc-image').remove();
    });
    $('body').on('click', '.latepoint-btn', function() {
        if($('.latepoint-body .mbc-image').length) $('.latepoint-body .mbc-image').remove();
    })

    $('body').on('blur', '#booking_custom_fields_cf_qoqkhbly', function() {
        $('.latepoint-footer .latepoint-next-btn').addClass('disabled');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate',
                id: $('#booking_custom_fields_cf_qoqkhbly').val()
            },
        }).done(function() {
            $('.latepoint-footer .latepoint-next-btn').removeClass('disabled');
            $('.latepoint-body #certificate-error').remove();
        }).fail(function(xhr) {
            if(xhr.status == 404) {
                if(xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if(!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').text(xhr.responseJSON.data.message)
            }
        });
    });
});
start_date = '';
start_time = '';
function set_start(date, time) {
    start_date = date;
    start_time = time;
}
