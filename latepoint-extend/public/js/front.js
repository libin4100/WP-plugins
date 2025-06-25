jQuery(function ($) {
    var clinic_btn = '<div class="os-row os-row-btn"><div class="or"><span>or</span></div><div class="os-col-12"><a href="#" class="latepoint-btn latepoint-skip-datetime-btn" data-pre-last-step-label="Submit" data-label="Next Step"><span>I\'m at the clinic</span></a></div></div>';
    var clinic_notice = '<div class="latepoint-desc-content" id="latepoint-notice">If you are booing for a future time: </div>';
    var clinic_notice2 = '<p>&nbsp;</p><div class="latepoint-desc-content">If you are in clinic already:</div><div class="latepoint-desc-content">Click the button "I\'m at the clinic".</div>';
    var button_click = false;

    var show_notice = '<div class="os-row os-row-div"><div class="os-col-12"><h3>Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time.</h3></div></div>';
    var show_summary = '<div class="os-show-summary os-summary-line os-has-value" style="display: block;flex: 0 0 100%;"><div class="os-summary-value os-summary-value-notice" style="color:red">Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time. </div> </div>';
    first_payment = true;

    var fields = {
        "qoqkhbly": { action: 'check_certificate', service_id: $('input[name="restrictions[selected_service]"').val() },
        "pnwpruie": { action: 'check_certificate_fl' },
        "w0izrltg": { action: 'check_certificate_imc' },
        "vin78day": { action: 'check_certificate_sb' },
        "sit7zefp": { action: 'check_certificate_p' },
        "sit7zefo": { action: 'check_certificate_qh' },
        "wzbhg9eb": { action: 'check_certificate_aas', service_id: $('input[name="restrictions[selected_service]"').val() },
        "p56xpuo5": { action: 'check_certificate_gotohw' },
        "4wvf2u9y": {},
        "aku1t075": { action: 'check_certificate_seb', service_id: $('input[name="restrictions[selected_service]"').val() },
        "qblbyjs8": { action: 'check_certificate_ub', service_id: $('input[name="restrictions[selected_service]"').val() },
        "ayvpjhpp": { action: 'check_certificate_lg' },
        "9oadikyh": { action: 'check_certificate_by', by: 'vpi' },
        "yjnziz1d": { action: 'check_certificate_by', by: 'cc' },
        "9e1mhf4v": { action: 'check_certificate_by', by: 'sp', agent_id: 'input[name="restrictions[selected_agent]"' },
        "zdwwtasg": { action: 'check_certificate_by', by: 'sp', agent_id: 'input[name="restrictions[selected_agent]"' },
        "frhzp65m": { action: 'check_certificate_by', by: 'sp', agent_id: 'input[name="restrictions[selected_agent]"' },
        "lbbtei3k": { action: 'check_certificate_by', by: 'sp', agent_id: 'input[name="restrictions[selected_agent]"' },
    }

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
        for (let key in fields) {
            let id = '#booking_custom_fields_cf_' + key;
            if ($(id).length) {
                if (key == "p56xpuo5") {
                    $("#booking_custom_fields_cf_xlaxtiqb")
                    .parents(".os-col-12")
                    .prependTo(
                        ".step-custom-fields-for-booking-w.latepoint-step-content .os-row"
                    );
                }
                if (key == "qblbyjs8" && !$('input[name="booking[custom_fields][group]"]').length) {
                    $(id).after(
                    '<input type="hidden" name="booking[custom_fields][group]" value="' +
                        $("#groups").val() +
                        '">'
                    );
                }
                if (!$(id).parents(".os-col-12").is(":first-child")) {
                    $(id)
                    .parents(".os-col-12")
                    .prependTo(
                        ".step-custom-fields-for-booking-w.latepoint-step-content .os-row"
                    );
                }
                if (["qoqkhbly", "4wvf2u9y"].includes(key)) {
                    $(id).val($("#mbc-cert-hidden").val());
                    $(id).parents(".os-col-12").hide();
                }
            }
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
        if ($('.os-col-12 > div > #booking_custom_fields_cf_dq70wnrg').length && $('.os-col-12 > div > #booking_custom_fields_cf_dq70wnrg').parents('.os-col-12').is(':visible')) {
            $('.os-col-12 > div > #booking_custom_fields_cf_dq70wnrg').parents('.os-col-12').hide();
        }
        if ($('#booking_custom_fields_cf_wfhtigvf').length && $('#booking_custom_fields_cf_wfhtigvf').attr('type') == 'text') {
            $('#booking_custom_fields_cf_wfhtigvf').attr('type', 'date');
        }
        if ($('#booking_custom_fields_cf_9e1mhf4v').length && $('#customer_email').length) {
            $('#customer_email').val($('#booking_custom_fields_cf_9e1mhf4v').val()).closest('.os-col-12').hide();
        }
        if ($('#booking_custom_fields_cf_frhzp65m').length && $('#customer_email').length) {
            $('#customer_email').val($('#booking_custom_fields_cf_frhzp65m').val()).closest('.os-col-12').hide();
        }
        if ($('html[lang="fr"] #customer_custom_fields_cf_7lkik5fd').length && !$('html[lang="fr"] #customer_custom_fields_cf_7lkik5fd').hasClass('replaced')) {
            const replaces = {
                "Male": "homme",
                "Female": "femme",
            }
            for (let key in replaces) {
                $('html[lang="fr"] #customer_custom_fields_cf_7lkik5fd').find('option[value="' + key + '"]').text(replaces[key]);
            }
            $('html[lang="fr"] #customer_custom_fields_cf_7lkik5fd').addClass('replaced');
        }
        const replaces = {
            ".latepoint-prev-btn": {
                "Dos": "Retour",
            },
            ".dp-success-label": {
                "Selected": "Choisi",
            },
            ".current-month > font": {
                "Peut": "Mai",
            },
            ".os-weekdays": {
                "Épouser": "Mer",
                "Assis": "Sam",
                "Soleil": "Dim",
            },
        }
        for (let cls in replaces) {
            if ($('html[lang="fr"] ' + cls + ":not(.replaced)").length) {
                $('html[lang="fr"] ' + cls).each(function () {
                    let el = $(this);
                    let html = el.html();
                    for (let key in replaces[cls]) {
                    if (html.includes(key)) {
                        html = html.replace(key, replaces[cls][key]);
                        el.html(html);
                        el.addClass("replaced notranslate");
                    }
                    }
                });
            }
        }
        if ($('html[lang="fr"] .os-mask-phone:not(.replaced)').length && $('html[lang="fr"] .os-mask-phone[placeholder="Numéro de téléphone portable"]').length) {
            latepoint_mask_phone(jQuery(".os-mask-phone"))
            $('html[lang="fr"] .os-mask-phone').addClass('replaced');
        }
        if ($('.confirmation-app-info:not(.replaced) li > strong').length) {
            $('.confirmation-app-info li > strong').each(function () {
                if (!$(this).text()) {
                    $(this).closest('li').attr('style', 'display:none !important');
                }
            });
            $('.confirmation-app-info').addClass('replaced');
        }
    }, 100);

    $('html[lang="fr"]').on('click', '.os-day', function () {
        if (window.doGTranslate) window.doGTranslate('en|fr');
    });

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
    $('body').on('mouseover', '.cbp-help', function () {
        if (!$('.cbp-image').length)
            $(this).closest('p').after('<img class="cbp-image" src="/wp-content/uploads/2024/03/Print_BenefitCard.jpg" />');
    });
    $('body').on('click', '.cbp-help', function () {
        if ($('.cbp-image').length)
            $('.cbp-image').remove();
        else
            $(this).closest('p').after('<img class="cbp-image" src="/wp-content/uploads/2024/03/Print_BenefitCard.jpg" />');
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

    for (let key in fields) {
        if (fields[key].action) {
            let id = '#booking_custom_fields_cf_' + key;
            $('body').on('blur', id, function () {
                $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
                let data = fields[key];
                data.id = $(id).val();
                if (data.agent_id && data.agent_id.includes('input')) data.agent_id = $(data.agent_id).val();
                $.ajax({
                    method: "POST",
                    url: ajax_object.ajax_url,
                    data: data,
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
        }
    }

    $('body').on('blur', '#booking_qhc_pharmacy_password', function () {
        $('.latepoint-footer .latepoint-next-btn').addClass('os-loading');
        $.ajax({
            method: "POST",
            url: ajax_object.ajax_url,
            data: {
                action: 'check_certificate_drug',
                id: $('#booking_qhc_pharmacy_password').val()
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
            if (msg && msg.data && msg.data.op) {
                $('.with-op').first().find('a').first().click();
            } else if (msg && msg.data && msg.data.care) {
                $('.with-care').first().find('a').first().click();
            } else {
                if ($('.mbc-popup').first().find('a').length) {
                    $('.mbc-popup').first().find('a').first().click();
                } else if ($('.mbc-popup').first().find('div.latepoint-book-button').length) {
                    $('.mbc-popup').first().find('div.latepoint-book-button').first().click();
                }
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

    $('body').on('change', '#booking_custom_fields_emergency', function () {
        if ($(this).val() == 'Yes') {
            $('.latepoint-footer .latepoint-btn').addClass('disabled');
            $('.latepoint-body').empty().prepend('<div id="certificate-error" class="latepoint-message latepoint-message-error"></div>');
            $('.latepoint-body #certificate-error').html('The GotoDoctor Employee Support Program is not intended for emergencies or those requiring immediate medical attention. If this is an emergency or you are in need of rapid medical intervention, please call 911 or visit the nearest hospital.');
        }
            
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

    const notranslates = {
        'British Columbia Health': 'Santé British Columbia',
        'New Brunswick Health': 'Santé New Brunswick',
        'Nova Scotia Health': 'Santé Nova Scotia',
    };
    Object.keys(notranslates).forEach((key) => {
        const value = notranslates[key];
        $('div.latepoint-book-button.os_trigger_booking').each(function () {
            const text = $(this).text();
            if (text.includes(key)) {
                $(this).html('<span class="notranslate"><span class="en">' + key + '</span><span class="fr">' + value + '</span></span>');
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
