jQuery(function ($) {
    var clinic_btn = '<div class="os-row os-row-btn"><div class="or"><span>or</span></div><div class="os-col-12"><a href="#" class="latepoint-btn latepoint-skip-datetime-btn" data-pre-last-step-label="Submit" data-label="Next Step"><span>I\'m at the clinic</span></a></div></div>';
    var clinic_notice = '<div class="latepoint-desc-content" id="latepoint-notice">If you are booing for a future time: </div>';
    var clinic_notice2 = '<p>&nbsp;</p><div class="latepoint-desc-content">If you are in clinic already:</div><div class="latepoint-desc-content">Click the button "I\'m at the clinic".</div>';
    var button_click = false;

    var show_notice = '<div class="os-row os-row-div"><div class="os-col-12"><h3>Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time.</h3></div></div>';
    var show_summary = '<div class="os-show-summary os-summary-line os-has-value" style="display: block;flex: 0 0 100%;"><div class="os-summary-value os-summary-value-notice" style="color:red">Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time. </div> </div>';
    first_payment = true;

    setInterval(function () {
        if ($('.latepoint-body .latepoint-footer.request-move').length) {
            $('.latepoint-body .latepoint-footer.request-move').css('display', 'flex').appendTo('.latepoint-form');
            $('.latepoint-body').css('padding-bottom', '15px');
        }

        if ($('.os-today').data('total-work-minutes')
            && ($('.os-today').data('work-start-time') <= start_time)
            && ($('.os-today').data('work-end-time') > start_time)
            && (typeof (show_btn) != 'undefined')
            && $('.step-datepicker-w').length
            && !$('.step-datepicker-w .os-row-btn').length
        ) {
            $('.step-datepicker-w').append(clinic_btn);
        }
        if ($('.latepoint-body .latepoint-skip-datetime-btn').length && $('.latepoint-body .latepoint-skip-datetime-btn').is(':visible')) {
            if (!$('#latepoint-notice').length) {
                $('.latepoint-step-desc .latepoint-desc-media').after(clinic_notice);
                $('.latepoint-step-desc').append(clinic_notice2);
            }
            //if($('#at_clinic').length) $('#at_clinic').remove();
        }
        if (typeof (is_rapid) != 'undefined' && is_rapid) {
            if ($('.dp-timeslot.with-tick.selected').length && !showed) {
                $('.step-datepicker-w').append(show_notice);
                $('.os-summary-lines').append(show_summary);
                showed = true;
            }
            if (!$('.dp-timeslot.with-tick.selected').length && showed) {
                $('.os-row-div').remove();
                $('.os-show-summary').remove();
                showed = false;
            }
        }
        if ($('.latepoint-payment').length && first_payment) {
            $('.latepoint-lightbox-close').hide();
            $('.latepoint-form-w .latepoint-heading-w .os-heading-text-library[data-step-name="confirmation"]').text('Appointment Request');
            $('.confirmation-app-info ul li').eq(1).html($('.confirmation-app-info ul li').eq(1).html().replace('Time', 'Requested Time'))
            first_payment = false;
        }

        if ($('.latepoint-prev-btn').length) {
            $('.latepoint-prev-btn').bind('click', function () {
                $('.latepoint-footer .latepoint-next-btn span').text($('.latepoint-footer .latepoint-next-btn').data('label'));
            });
        }
        if ($('.latepoint-step-desc-library[data-step-name!="services"] .latepoint-desc-title').length) {
            $('.latepoint-step-desc-library[data-step-name!="services"][data-step-name!="confirmation"] .latepoint-desc-title').text('');
        }
        if ($('#booking_custom_fields_cf_qoqkhbly').length && !$('#booking_custom_fields_cf_qoqkhbly').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_qoqkhbly').val($('#mbc-cert-hidden').val());
            $('#booking_custom_fields_cf_qoqkhbly').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row').hide();
        }
        if ($('#booking_custom_fields_cf_pnwpruie').length && !$('#booking_custom_fields_cf_pnwpruie').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_pnwpruie').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
        }
        if ($('#booking_custom_fields_cf_w0izrltg').length && !$('#booking_custom_fields_cf_w0izrltg').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_w0izrltg').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
        }
        if ($('#booking_custom_fields_cf_vin78day').length && !$('#booking_custom_fields_cf_vin78day').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_vin78day').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
        }
        if ($('#booking_custom_fields_cf_sit7zefo').length && !$('#booking_custom_fields_cf_sit7zefo').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_sit7zefo').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
        }
        if ($('#booking_custom_fields_cf_sit7zefp').length && !$('#booking_custom_fields_cf_sit7zefp').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_sit7zefp').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
        }
        if ($('#booking_custom_fields_cf_wzbhg9eb').length && !$('#booking_custom_fields_cf_wzbhg9eb').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_wzbhg9eb').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
        }
        if ($('#booking_custom_fields_cf_p56xpuo5').length && !$('#booking_custom_fields_cf_p56xpuo5').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_xlaxtiqb').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
            $('#booking_custom_fields_cf_p56xpuo5').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row')
        }
        if ($('#booking_custom_fields_cf_4wvf2u9y').length && !$('#booking_custom_fields_cf_4wvf2u9y').parents('.os-col-12').is(':first-child')) {
            $('#booking_custom_fields_cf_4wvf2u9y').val($('#mbc-cert-hidden').val());
            $('#booking_custom_fields_cf_4wvf2u9y').parents('.os-col-12').prependTo('.step-custom-fields-for-booking-w.latepoint-step-content .os-row').hide();
        }
        if ($('#customer_custom_fields_cf_4zkibeey').length) {
            if ($('#customer_custom_fields_cf_4zkibeey').val() == 'Other') {
                $('#customer_custom_fields_cf_nvbyvyyw').parents('.os-col-12').show()
            } else {
                $('#customer_custom_fields_cf_nvbyvyyw').parents('.os-col-12').hide()
            }
            if ($('#customer_custom_fields_cf_4zkibeey').val() == 'Prescription renewal') {
                $('#customer_custom_fields_cf_cvndxx2e').parents('.os-col-12').show()
                $('#customer_custom_fields_cf_iaooucdc').parents('.os-col-12').show()
            } else {
                $('#customer_custom_fields_cf_cvndxx2e').parents('.os-col-12').hide()
                $('#customer_custom_fields_cf_iaooucdc').parents('.os-col-12').hide()
            }
        }
    }, 100);

    $('body').on('click', '.latepoint-body .latepoint-skip-datetime-btn', function () {
        $('.latepoint-body .latepoint_start_date').val(start_date);
        $('.latepoint-body .latepoint_start_time').val(start_time);
        if (!$('#at_clinic').length)
            $('.latepoint-form').append('<input type="hidden" name="booking[at_clinic]" value="1" id="at_clinic">');
        return $('.latepoint-form').submit();
    });

    $('body').on('mouseover', '.mbc-help', function () {
        if (!$('.mbc-image').length)
            $(this).closest('p').after('<img class="mbc-image" src="/wp-content/uploads/2022/03/mbc-1.png" />');
    });
    $('body').on('click', '.mbc-help', function () {
        if ($('.mbc-image').length)
            $('.mbc-image').remove();
        else
            $(this).closest('p').after('<img class="mbc-image" src="/wp-content/uploads/2022/03/mbc-1.png" />');
    });
    $('body').on('mouseover', '.latepoint-body .sb-help', function () {
        $('.latepoint-summary-w').append('<img class="sb-image" src="/wp-content/uploads/2022/11/tempsnip.png" />');
    });
    $('body').on('mouseover', '.latepoint-body .ic-help', function () {
        $('.latepoint-summary-w').append('<img class="ic-image" src="/wp-content/uploads/2024/01/imperial-cert.jpg" />');
    });
    $('body').on('mouseover', '.latepoint-body .fabricland-help', function () {
        if (!$('.latepoint-body .fabricland-image').length) {
            if ($('.latepoint-summary-w .os-summary-line:visible').length)
                $('.latepoint-summary-w').append('<img class="fabricland-image" src="/wp-content/uploads/2023/07/fabricland.png" />');
            else
                $('.latepoint-body .step-custom-fields-for-booking-w').append('<img class="fabricland-image" src="/wp-content/uploads/2023/07/fabricland.png" />');
        }
    });
    $('body').on('click', '.latepoint-body .fabricland-help', function () {
        if ($('.latepoint-body .fabricland-image').length)
            $('.latepoint-body .fabricland-image').remove();
        else
            $('.latepoint-body .step-custom-fields-for-booking-w').append('<img class="fabricland-image" src="/wp-content/uploads/2023/07/fabricland.png" />');
    });
    $('body').on('mouseout', '.latepoint-body .fabricland-help', function () {
        $('.latepoint-summary-w .fabricland-image').remove();
    });
    $('body').on('click', '.latepoint-body .sb-help', function () {
        if ($('.latepoint-body .sb-image').length)
            $('.latepoint-body .sb-image').remove();
        else
            $('.latepoint-body .step-custom-fields-for-booking-w').append('<img class="sb-image" src="/wp-content/uploads/2022/11/tempsnip.png" />');
    });
    $('body').on('click', '.latepoint-body .ic-help', function () {
        if ($('.latepoint-body .ic-image').length)
            $('.latepoint-body .ic-image').remove();
        else
            $('.latepoint-body .step-custom-fields-for-booking-w').append('<img class="ic-image" src="/wp-content/uploads/2024/01/imperial-cert.jpg" />');
    });
    $('body').on('mouseout', '.latepoint-body .sb-help', function () {
        $('.latepoint-summary-w .sb-image').remove();
    });
    $('body').on('mouseout', '.latepoint-body .ic-help', function () {
        $('.latepoint-summary-w .ic-image').remove();
    });
    $('body').on('click', '.latepoint-btn', function () {
        if ($('.latepoint-body .mbc-image').length) $('.latepoint-body .mbc-image').remove();
        if ($('.latepoint-body .sb-image').length) $('.latepoint-body .sb-image').remove();
    })

    $('body').on('blur', '#booking_custom_fields_cf_qoqkhbly', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate',
                id: $('#booking_custom_fields_cf_qoqkhbly').val(),
                service_id: $('input[name="restrictions[selected_service]"').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });
    $('body').on('blur', '#booking_custom_fields_cf_pnwpruie', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_fl',
                id: $('#booking_custom_fields_cf_pnwpruie').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });
    $('body').on('blur', '#booking_custom_fields_cf_w0izrltg', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_imc',
                id: $('#booking_custom_fields_cf_w0izrltg').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });

    $('body').on('blur', '#booking_custom_fields_cf_vin78day', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_sb',
                id: $('#booking_custom_fields_cf_vin78day').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });

    $('body').on('blur', '#booking_custom_fields_cf_sit7zefo', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_qh',
                id: $('#booking_custom_fields_cf_sit7zefo').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });

    $('body').on('blur', '#booking_custom_fields_cf_sit7zefp', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_p',
                id: $('#booking_custom_fields_cf_sit7zefp').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });

    $('body').on('blur', '#booking_custom_fields_cf_wzbhg9eb', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_aas',
                id: $('#booking_custom_fields_cf_wzbhg9eb').val(),
                service_id: $('input[name="restrictions[selected_service]"').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });

    $('body').on('blur', '#booking_custom_fields_cf_p56xpuo5', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_gotohw',
                id: $('#booking_custom_fields_cf_p56xpuo5').val()
            },
        }).done(function () {
            $('.latepoint-body #certificate-error').remove();
        }).always(function () {
            $('.latepoint-footer .latepoint-next-btn').removeClass('os-loading');
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $('.latepoint-footer .latepoint-btn').addClass('disabled');
                    $('.latepoint-body').empty();
                }

                if (!$('.latepoint-body #certificate-error').length)
                    $('.latepoint-body').prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                $('.latepoint-body #certificate-error').html(xhr.responseJSON.data.message)
            }
        });
    });

    $('body').on('keypress', '.mbc-cert', function (e) {
        if (e.which == 13) {
            $(this).siblings('.check-mbc-cert').trigger('click');
        }
    })
    $('body').on('click', '.check-mbc-cert', function () {
        $(this).addClass('os-loading').prop('disabled', true);
        var pform = $(this).closest('.form');
        var cert = pform.find('.mbc-cert').val();
        var action = 'mbc_certificate';
        if (pform.find('.mbc-cert').data('action'))
            action = pform.find('.mbc-cert').data('action');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: action,
                id: cert
            },
        }).done(function (msg) {
            Custombox.modal.close()
            if (msg && msg.data && msg.data.care) {
                $('.with-care').first().find('a').first().click();
            } else {
                $('.mbc-popup').first().find('a').first().click();
            }
            if(!$('#mbc-cert-hidden').length)
                $('body').append('<input type="hidden" id="mbc-cert-hidden" name="mbc_cert" value="">');

            $('#mbc-cert-hidden').val(cert);
        }).always(function () {
            pform.find('.os-loading').removeClass('os-loading').prop('disabled', false);
        }).fail(function (xhr) {
            if (xhr.status == 404) {
                if (xhr.responseJSON.data.count >= 3) {
                    $(this).addClass('disabled');
                    pform.find('input').val('').prop('disabled', true);
                }

                if (!pform.find('#certificate-error').length)
                    pform.prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
                pform.find('#certificate-error').html(xhr.responseJSON.data.message);
            }
        });
    });

    document.addEventListener('custombox:overlay:close', function() {
        if (!$('.custombox-open').length) {
            $('.mbc-wrapper .mbc-popup').addClass('hidden');
            $('.mbc-wrapper .mbc-popup .care').addClass('hidden');
            $('.mbc-wrapper .mbc-cert').val('');
            $('.mbc-wrapper .os-loading').removeClass('os-loading');
            $('.mbc-wrapper .form').show();
            $('.mbc-wrapper .form').find('#certificate-error').remove();
            $('#mbc-cert-hidden').remove();
            $('.mbc-image').remove();
        }
    })
});
start_date = '';
start_time = '';
function set_start(date, time) {
    start_date = date;
    start_time = time;
}
