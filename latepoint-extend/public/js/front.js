jQuery(function ($) {
    var clinic_btn = '<div class="os-row os-row-btn"><div class="or"><span>or</span></div><div class="os-col-12"><a href="#" class="latepoint-btn latepoint-skip-datetime-btn" data-pre-last-step-label="Submit" data-label="Next Step"><span>I\'m at the clinic</span></a></div></div>';
    var clinic_notice = '<div class="latepoint-desc-content" id="latepoint-notice">If you are booing for a future time: </div>';
    var clinic_notice2 = '<p>&nbsp;</p><div class="latepoint-desc-content">If you are in clinic already:</div><div class="latepoint-desc-content">Click the button "I\'m at the clinic".</div>';
    var button_click = false;

    var show_notice = '<div class="os-row os-row-div"><div class="os-col-12"><h3>Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time.</h3></div></div>';
    var show_summary = '<div class="os-show-summary os-summary-line os-has-value" style="display: block;flex: 0 0 100%;"><div class="os-summary-value os-summary-value-notice" style="color:red">Please note that your request for the time interval will be processed. DO NOT COME IN, until you receive YOUR SPECIFIC appointment time. </div> </div>';
    first_payment = true;
    var markLastVisibleProgressItem = function () {
        $('.latepoint-w .latepoint-booking-form-element .latepoint-progress ul').each(function () {
            var $items = $(this).children('li');
            $items.removeClass('gtd-last-visible');
            var $visibleItems = $items.filter(function () {
                return $(this).css('display') !== 'none' && $(this).css('visibility') !== 'hidden';
            });
            if ($visibleItems.length) {
                $visibleItems.last().addClass('gtd-last-visible');
            }
        });
    };
    var markRequiredLabels = function () {
        var $customFieldsStep = $('.latepoint-body .step-custom-fields-for-booking-w.latepoint-step-content, .latepoint-body .latepoint-step-content[data-step-name="custom_fields_for_booking"]');
        if (!$customFieldsStep.length) return;

        $customFieldsStep.find('.os-form-group').each(function () {
            var $group = $(this);
            var hasRequiredField = $group.find('.required').filter(function () {
                var $field = $(this);
                if (!$field.is('input,select,textarea')) return false;
                if ($field.is('input[type="hidden"]')) return false;
                return true;
            }).length > 0;

            var $label = $group.children('label').first();
            if (!$label.length) {
                $label = $group.find('label').first();
            }
            if (!$label.length) return;

            if (hasRequiredField) {
                if (!$label.find('.gtd-required-asterisk').length) {
                    $label.append('<span class="gtd-required-asterisk">*</span>');
                }
            } else {
                $label.find('.gtd-required-asterisk').remove();
            }

            $group.find('input[type="text"]').each(function () {
                var $input = $(this);
                var currentPlaceholder = $input.attr('placeholder') || '';
                var basePlaceholder = String(currentPlaceholder || '').replace(/\s*\*+\s*$/, '');
                if ($input.hasClass('required')) {
                    $input.attr('placeholder', basePlaceholder ? (basePlaceholder + ' *') : '*');
                } else if (/\s*\*+\s*$/.test(currentPlaceholder)) {
                    $input.attr('placeholder', basePlaceholder);
                }
            });
        });
    };
    var getSelectedAgentId = function () {
        var agentId = $('input[name="restrictions[selected_agent]"]').val()
            || $('input[name="booking[agent_id]"]').val()
            || $('input[name="presets[selected_agent]"]').val()
            || $('select[name="booking[agent_id]"]').val();
        return parseInt(agentId, 10) || 0;
    };

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
        "ewhb7h3k": { action: 'check_certificate_by', by: 'sp', agent_id: 'input[name="restrictions[selected_agent]"' },
        "ryf56ipw": { action: 'check_certificate_by', by: 'sp', agent_id: 'input[name="restrictions[selected_agent]"' },
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
        markLastVisibleProgressItem();
        markRequiredLabels();
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
            var getRenewalFieldWrapper = function(selector) {
                return $(selector).closest('.os-col-12, .os-col-6');
            };
            var makeTwoColumnRow = function($leftWrapper, $rightWrapper) {
                if (!$leftWrapper.length || !$rightWrapper.length) return;
                $leftWrapper.removeClass('os-col-12').addClass('os-col-6');
                $rightWrapper.removeClass('os-col-12').addClass('os-col-6');
                if (!$leftWrapper.next().is($rightWrapper)) {
                    $rightWrapper.insertAfter($leftWrapper);
                }
            };
            var $otherReasonWrapper = getRenewalFieldWrapper('#customer_custom_fields_cf_nvbyvyyw');
            var $pharmacyNameWrapper = getRenewalFieldWrapper('#customer_custom_fields_cf_cvndxx2e');
            var $pharmacyPhoneWrapper = getRenewalFieldWrapper('#customer_custom_fields_cf_pharmacy_phone');
            var $prescriptionNameWrapper = getRenewalFieldWrapper('#customer_custom_fields_cf_iaooucdc');
            var $dosageWrapper = getRenewalFieldWrapper('#customer_custom_fields_cf_prescription_dosage');

            if ($('#customer_custom_fields_cf_4zkibeey').val() == 'Other') {
                $otherReasonWrapper.show()
            } else {
                $otherReasonWrapper.hide()
            }

            makeTwoColumnRow($pharmacyNameWrapper, $pharmacyPhoneWrapper);
            makeTwoColumnRow($prescriptionNameWrapper, $dosageWrapper);

            if ($('#customer_custom_fields_cf_4zkibeey').val() == 'Prescription renewal') {
                $pharmacyNameWrapper.show();
                $pharmacyPhoneWrapper.show();
                if ($prescriptionNameWrapper.length) {
                    var $row = $prescriptionNameWrapper.parent('.os-row');
                    if ($row.length && !$row.find('.renewal-or-label').length) {
                        $('<div class="os-col-12 renewal-or-label"><p class="or">Or</p></div>').insertBefore($prescriptionNameWrapper);
                    }
                }
                $prescriptionNameWrapper.show();
                $dosageWrapper.show();
            } else {
                $pharmacyNameWrapper.hide();
                $pharmacyPhoneWrapper.hide();
                $prescriptionNameWrapper.hide();
                $dosageWrapper.hide();
                if ($prescriptionNameWrapper.length) {
                    $prescriptionNameWrapper.parent('.os-row').find('.renewal-or-label').remove();
                }
            }
        }
        if ($('#booking_custom_fields_cf_khyzmswi').length) {
            if ($('#booking_custom_fields_cf_khyzmswi').is(':checked')) {
                $('#booking_custom_fields_cf_fpu4ka1m').parents('.os-form-group').parent('div').show();
                $('#booking_custom_fields_cf_pfyxbffm').parents('.os-form-group').parent('div').show();
                $('#booking_custom_fields_cf_pil2uooe').parents('.os-form-group').parent('div').show();
            } else {
                $('#booking_custom_fields_cf_fpu4ka1m').parents('.os-form-group').parent('div').hide();
                $('#booking_custom_fields_cf_pfyxbffm').parents('.os-form-group').parent('div').hide();
                $('#booking_custom_fields_cf_pil2uooe').parents('.os-form-group').parent('div').hide();
            }
        }
        if ($('#booking_custom_fields_cf_quzqwcch').length) {
            var $bookingForWrapper = $('#booking_custom_fields_cf_quzqwcch').closest('.os-col-12, .os-col-6');
            if ($('#booking_custom_fields_cf_quzqwcch').val() == 'Family member') {
                $('#booking_custom_fields_cf_cyhjctjz').parents('.os-form-group').parent('div').show();
                if ($bookingForWrapper.length) {
                    $bookingForWrapper.removeClass('os-col-12').addClass('os-col-6');
                }
            } else {
                $('#booking_custom_fields_cf_cyhjctjz').parents('.os-form-group').parent('div').hide();
                if ($bookingForWrapper.length) {
                    $bookingForWrapper.removeClass('os-col-6').addClass('os-col-12');
                }
            }
        }
        var selectedAgentId = getSelectedAgentId();
        ['#booking_custom_fields_cf_wfhtigvf', '#booking_custom_fields_cf_zoxsdwez'].forEach(function(selector) {
            var $field = $(selector);
            if ($field.length) {
                var $wrapper = $field.closest('.os-col-12, .os-col-6');
                if ($wrapper.length) {
                    if (selectedAgentId === 30) {
                        $wrapper.removeClass('os-col-12').addClass('os-col-6');
                    } else {
                        $wrapper.removeClass('os-col-6').addClass('os-col-12');
                    }
                }
            }
        });
        if ($('#booking_custom_fields_cf_fk9ih4et').length && !$('#booking_custom_fields_cf_fk9ih4et').hasClass('gtd-upload-ready')) {
            var $additionalField = $('#booking_custom_fields_cf_fk9ih4et');
            var $additionalGroup = $additionalField.closest('.os-form-group');
            if ($additionalGroup.length) {
                var uploadHtml = [
                    '<div class="gtd-additional-upload-wrap os-form-group os-form-group-transparent">',
                        '<label for="gtd_additional_file_upload">',
                            'Please upload all the relevent documents for our Employee Family Assistance Program staff to review',
                            '<div class="btn btn-block latepoint-btn latepoint-btn-secondary">',
                                '<strong>Add Files</strong>',
                                '<input type="file" name="booking_file" value="" class="os-form-control" style="display:none" id="gtd_additional_file_upload">',
                            '</div>',
                        '</label>',
                        '<h6 class="gtd-additional-file-help">If the file you need to attach is more than 5 MB, please email it to <a href="mailto:caresupport@gotodoctor.ca">caresupport@gotodoctor.ca</a> and add Employee Family Assistance Program in the subject line.</h6>',
                    '</div>',
                    '<div class="gtd-additional-loading latepoint-loading" style="display:none;">',
                        '<div class="lds-dual-ring"></div>',
                    '</div>',
                    '<div class="gtd-uploaded-file-list"></div>',
                    '<input type="hidden" name="booking[qhc][additional_file][]" class="gtd-additional-file-input" value="">'
                ].join('');

                $additionalGroup.after(uploadHtml);
                $additionalField.addClass('gtd-upload-ready');

                var $wrap = $additionalGroup.parent();
                $wrap.off('change.gtdUpload', '#gtd_additional_file_upload').on('change.gtdUpload', '#gtd_additional_file_upload', function() {
                    var $input = $(this);
                    var file = $input.prop('files')[0];
                    if (!file) return;
                    $input.prop('disabled', true);
                    $wrap.find('.gtd-additional-loading').show();

                    var formData = new FormData();
                    formData.append('additinal_file', file);
                    formData.append('action', 'latepoint_file_upload');
                    formData.append('security', ajax_object.file_upload_nonce || '');

                    $.ajax({
                        url: ajax_object.ajax_url,
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        type: 'post'
                    }).done(function(response) {
                        if (response && response.status === 'success') {
                            var $last = $wrap.find('.gtd-additional-file-input').last();
                            var $clone = $last.clone().val('');
                            $last.val(response.file).after($clone);
                            $input.val('');
                            $wrap.find('.gtd-uploaded-file-list').append(
                                '<div class="uploaded_file_name">' + response.original_name + ' <a href="#" class="gtd-delete-file" data-file="' + response.file + '">x</a></div>'
                            );
                        } else if (response && response.message) {
                            alert(response.message);
                        } else {
                            alert('Error uploading file');
                        }
                    }).fail(function() {
                        alert('Error uploading file');
                    }).always(function() {
                        $input.prop('disabled', false);
                        $wrap.find('.gtd-additional-loading').hide();
                    });
                });

                $wrap.off('click.gtdUpload', '.gtd-delete-file').on('click.gtdUpload', '.gtd-delete-file', function(e) {
                    e.preventDefault();
                    var file = $(this).data('file');
                    $wrap.find('.gtd-additional-file-input').each(function() {
                        if ($(this).val() === file) {
                            $(this).remove();
                        }
                    });
                    $(this).parent().remove();
                    if (!$wrap.find('.gtd-additional-file-input').length) {
                        $wrap.append('<input type="hidden" name="booking[qhc][additional_file][]" class="gtd-additional-file-input" value="">');
                    }
                });
            }
        }
        if ($('#booking_custom_fields_cf_gtd_create_account').length) {
            var $createAccountField = $('#booking_custom_fields_cf_gtd_create_account');
            var wantsAccount = false;
            if ($createAccountField.is(':checkbox')) {
                wantsAccount = $createAccountField.is(':checked');
            } else {
                var createAccountValue = String($createAccountField.val() || '').toLowerCase();
                wantsAccount = createAccountValue === 'yes' || createAccountValue === 'on' || createAccountValue === '1' || createAccountValue === 'true';
            }
            var $usernameWrapper = $('#booking_custom_fields_cf_gtd_username').closest('.os-col-12, .os-col-6');
            if ($usernameWrapper.length) {
                if (wantsAccount) {
                    $usernameWrapper.show();
                } else {
                    $('#booking_custom_fields_cf_gtd_username').val('');
                    $usernameWrapper.hide();
                }
            }
        }
        if ($('#booking_custom_fields_cf_edaxd83r').length && !$('#booking_custom_fields_cf_edaxd83r').hasClass('gtd-services-ready')) {
            var $el = $('#booking_custom_fields_cf_edaxd83r');
            var services = [
                'Addictions', 'Allergy and immunology', 'Audiology', 'Biopsy',
                'Chiropody/foot care', 'Dental care and orthotics', 'Family doctor',
                'Home health and community care', 'Internal medicine', 'Medical aesthetics',
                'Medical diagnostics and imaging', 'Medical marijuana', 'Medical procedure/surgery',
                'Mental health', 'Obstetrics and Gynecology', 'Orthopedic surgery',
                'Paediatric care', 'Pain management', 'Rehab services', 'Respiratory care',
                'Social and community support services', 'Specialist referral', 'Other'
            ];
            if (!$el.is('select')) {
                var $select = $('<select></select>').attr({ id: $el.attr('id'), name: $el.attr('name'), multiple: 'multiple' });
                $el.replaceWith($select);
                $el = $select;
            } else {
                $el.attr('multiple', 'multiple');
            }
            if (!$el.find('option[value!=""]').length) {
                $el.empty();
                services.forEach(function(s) { $el.append($('<option>').val(s).text(s)); });
            }
            var parseSelected = function(value) {
                if (Array.isArray(value)) return value.filter(Boolean);
                if (typeof value === 'string' && value.length) {
                    return value
                        .split(/\s*(?:\||,|\n)\s*/)
                        .map(function(v) { return $.trim(v); })
                        .filter(Boolean);
                }
                return [];
            };

            var hasExplicitSelected = $el.find('option[selected]').length > 0;
            var selectedValues = parseSelected($el.val());
            // Browsers can auto-select the first option for <select multiple> even when no saved data exists.
            if (!hasExplicitSelected && selectedValues.length === 1 && selectedValues[0] === services[0]) {
                selectedValues = [];
                $el.val([]);
            }
            if (selectedValues.length) {
                $el.val(selectedValues);
            }

            var pickerNs = '.gtdServicesPicker' + ($el.attr('id') || 'field');
            var $picker = $('<div class="gtd-services-picker"></div>');
            var $trigger = $('<input type="text" class="gtd-services-trigger os-form-control" readonly placeholder="---Select services---">');
            var $panel = $('<div class="gtd-services-panel" style="display:none;"></div>');
            var $search = $('<input type="text" class="gtd-services-search os-form-control" placeholder="Search services">');
            var $actions = $('<div class="gtd-services-actions"><a href="#" class="gtd-services-select-all">Select all</a><a href="#" class="gtd-services-clear">Clear</a></div>');
            var $options = $('<div class="gtd-services-options"></div>');

            services.forEach(function(service, idx) {
                var key = service.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                var cid = 'gtd-service-' + key + '-' + idx;
                var $item = $('<label class="gtd-service-option" for="' + cid + '"></label>').attr('data-label', service.toLowerCase());
                var $checkbox = $('<input type="checkbox" class="gtd-service-checkbox" id="' + cid + '">').val(service);
                if (selectedValues.includes(service)) {
                    $checkbox.prop('checked', true);
                }
                $item.append($checkbox).append('<span>' + service + '</span>');
                $options.append($item);
            });

            $panel.append($search).append($actions).append($options);
            $picker.append($trigger).append($panel);
            $el
                .after($picker)
                .addClass('gtd-services-ready gtd-services-source')
                .css('display', 'none')
                .attr('aria-hidden', 'true');
            if ($el.next('.select2').length) {
                $el.next('.select2').hide();
            }

            var syncToSelect = function() {
                var selected = [];
                $options.find('.gtd-service-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });
                $el.val(selected).trigger('change');
            };
            var updateTriggerText = function() {
                var selected = parseSelected($el.val());
                if (!selected.length) {
                    $trigger.val('');
                    $trigger.attr('placeholder', '---Select services---');
                } else {
                    var text = selected.join(', ');
                    $trigger.val(text);
                    $trigger.attr('title', text);
                }
            };
            var syncFromSelect = function() {
                var selected = parseSelected($el.val());
                $options.find('.gtd-service-checkbox').each(function() {
                    $(this).prop('checked', selected.includes($(this).val()));
                });
                updateTriggerText();
            };
            var applySearchFilter = function(rawTerm) {
                var term = $.trim(String(rawTerm || '')).toLowerCase();
                $options.find('.gtd-service-option').each(function() {
                    var text = String($(this).attr('data-label') || '').toLowerCase();
                    $(this).toggleClass('gtd-hidden', !!term && text.indexOf(term) === -1);
                });
            };
            var openPanel = function() {
                $picker.addClass('is-open');
                $panel.show();
                setTimeout(function() {
                    $search.trigger('focus');
                }, 0);
            };
            var closePanel = function() {
                $picker.removeClass('is-open');
                $panel.hide();
                $search.val('');
                applySearchFilter('');
            };

            $options.on('change', '.gtd-service-checkbox', function() {
                syncToSelect();
                updateTriggerText();
            });

            $el.on('change.gtdServices', function() {
                syncFromSelect();
            });

            $trigger.on('focus click', function(e) {
                e.preventDefault();
                openPanel();
            });

            $trigger.on('keydown', function(e) {
                if (['Enter', ' ', 'ArrowDown'].includes(e.key)) {
                    e.preventDefault();
                    openPanel();
                }
            });

            $search.on('input keyup change', function() {
                applySearchFilter($(this).val());
            });

            $('body').off('input.gtdServicesSearch keyup.gtdServicesSearch change.gtdServicesSearch', '.gtd-services-picker .gtd-services-search');
            $('body').on('input.gtdServicesSearch keyup.gtdServicesSearch change.gtdServicesSearch', '.gtd-services-picker .gtd-services-search', function() {
                var $currentPicker = $(this).closest('.gtd-services-picker');
                var term = $(this).val();
                $currentPicker.find('.gtd-services-option, .gtd-service-option').each(function() {
                    if (!$(this).hasClass('gtd-service-option')) return;
                    var text = String($(this).attr('data-label') || '').toLowerCase();
                    $(this).toggleClass('gtd-hidden', !!$.trim(String(term || '')).length && text.indexOf($.trim(String(term || '')).toLowerCase()) === -1);
                });
            });

            $actions.on('click', '.gtd-services-select-all', function(e) {
                e.preventDefault();
                $options.find('.gtd-service-option:visible .gtd-service-checkbox').prop('checked', true);
                syncToSelect();
            });

            $actions.on('click', '.gtd-services-clear', function(e) {
                e.preventDefault();
                $options.find('.gtd-service-checkbox').prop('checked', false);
                syncToSelect();
            });

            $(document).off('mousedown' + pickerNs).on('mousedown' + pickerNs, function(e) {
                if (!$(e.target).closest('.gtd-services-picker').length) {
                    closePanel();
                }
            });

            syncFromSelect();
        }
        if ($('.os-col-12 > div > #booking_custom_fields_cf_dq70wnrg').length && $('.os-col-12 > div > #booking_custom_fields_cf_dq70wnrg').parents('.os-col-12').is(':visible')) {
            $('.os-col-12 > div > #booking_custom_fields_cf_dq70wnrg').parents('.os-col-12').hide();
        }
        if ($('#booking_custom_fields_cf_wfhtigvf').length && $('#booking_custom_fields_cf_wfhtigvf').attr('type') == 'text') {
            $('#booking_custom_fields_cf_wfhtigvf').attr('type', 'date');
            $('#booking_custom_fields_cf_wfhtigvf').parent('div.os-form-group').addClass('os-form-select-group').removeClass('os-form-textfield-group')
        }
        if ($('#booking_custom_fields_cf_6nqyulpc').length && $('#booking_custom_fields_cf_6nqyulpc').attr('type') == 'text') {
            $('#booking_custom_fields_cf_6nqyulpc').attr('type', 'date');
            $('#booking_custom_fields_cf_6nqyulpc').parent('div.os-form-group').addClass('os-form-select-group').removeClass('os-form-textfield-group')
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
            "h3 > span.notranslate": {
                "Montreal Gateway Terminal Partnership": "Société Terminaux Montréal Gateway"
            },
            ".step-custom-fields-for-booking-w": {
                "ID d'employé MGT": "Numéro d’employé MGT"
            },
            ".wpcf7-form .wpcf7-response-output": {
                "Thank you for submitting your EAP request. We have sent you an email with simple steps to get started on our digital wellness platform.": "Merci d’avoir soumis votre demande au programme d’aide aux employés (PAE). Nous vous avons envoyé un courriel contenant des étapes simples pour commencer à utiliser notre plateforme de bien-être numérique."
            },
            ".wpcf7-form .error": {
                "This field is required.": "Ce champ est requis."
            },
            ".wpcf7-form .wpcf7-not-valid-tip": {
                "Please fill out this field.": "Veuillez remplir ce champ."
            }
        }
        for (let cls in replaces) {
            if ($('html[lang="fr"] ' + cls + ":not(.replaced)").length) {
                $('html[lang="fr"] ' + cls).each(function () {
                    let el = $(this);
                    let html = el.html();
                    for (let key in replaces[cls]) {
                        if (html.includes(key)) {
                            html = html.replaceAll(key, replaces[cls][key]);
                            el.html(html).addClass("replaced notranslate");
                        }
                    }
                });
            }
        }
        const reverts = {
            "h3 > span.notranslate.replaced": {
                "Société Terminaux Montréal Gateway": "Montreal Gateway Terminal Partnership"
            },
        }
        for (let cls in reverts) {
            if ($('html[lang!="fr"] ' + cls).length) {
                $('html[lang!="fr"] ' + cls).each(function () {
                    let el = $(this);
                    let html = el.html();
                    for (let key in reverts[cls]) {
                        if (html.includes(key)) {
                            html = html.replaceAll(key, reverts[cls][key]);
                            el.html(html).removeClass("replaced");
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
        if ($('.os-months:not(.binded)').length) {
            $('.os-months').each(function () {
                $(this).on('click', '.os-day', function () {
                    if (($('html').attr('lang') == 'fr') && (window.doGTranslate)) window.doGTranslate('en|fr');
                });
                $(this).addClass('binded');
            });
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
        if($('#mbc-cert-hidden').length) $('#mbc-cert-hidden').remove();
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

    $('body').on('change', '#booking_custom_fields_emergency, #booking_custom_fields_cf_ipbmusja', function () {
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
            //$('#mbc-cert-hidden').remove();
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
