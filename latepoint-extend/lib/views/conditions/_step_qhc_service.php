<div class="step-qhc-service-w latepoint-step-content" data-step-name="qhc_service">
    <p>Please select the service(s) that you need (check all that apply):</p>
    <div class="os-row">
        <?php
        $list = [
            'Allergy and immunology',
            'Addictions',
            'Mental health',
            'Internal medicine',
            'Orthopedic surgery',
            'Audiology',
            'Medical aesthetics',
            'Chiropody/foot care',
            'Medical marijuana',
            'Pain management',
            'Respiratory care',
            'sr' => 'Specialist referral',
            'mdi' => 'Medical diagnostics and imaging',
            'Paediatric care',
            'Medical procedure/surgery',
            'social and community support services',
            'Home health and community care',
            'Rehab services',
            'Dental care and orthotics',
            'Family medicine',
            'biopsy' => 'Biopsy',
            'og' => 'Obstetrics and Gynecology',
            'Other'
        ];
        if ($booking->service_id == 15)
            $list = [
                "Addiction and substance care",
                "Alcohol use",
                "Anger",
                "Anxiety",
                "Child and elder care",
                "Chronic disease management",
                "Depression",
                "Diabetes",
                "Family care information",
                "Financial and legal assistance",
                "Financial goal planning",
                "Gambling",
                "Health and wellness information",
                "Health and Wellness planning Primary care or family physician",
                "Heart disease",
                "High blood pressure",
                "High cholesterol",
                "iCBT programs and tools Mental health support",
                "Individual coaching/counselling session",
                "Individualized health risk assessment",
                "Life transitions",
                "Medical navigation and support",
                "Nutritional and diet ",
                "Physical wellness, energy and resilience",
                "Relationship",
                "Social support services",
                "Specialist care",
                "Stress",
                "Tobacoo use",
                "Weight management",
                "Others - please specify",
            ];
        if ($booking->agent_id == 18) array_unshift($list, 'Medical second opinion');
        $subs = [
            'qhc_service_mdi' => [
                'MDI: OHIP-covered Service' => 'Looking for an OHIP-covered service',
                'MDI: Private Service' => 'Looking for the PRIVATE service',
                'MDI: WISB Coverage' => 'Has WISB coverage'
            ],
            'qhc_service_sr' => [
                'SR: OHIP-covered Appointment' => 'Looking for an OHIP-covered appointment',
                'SR: Private Medical Consultant' => 'Looking for the private medical consultant'
            ],
            'qhc_service_biopsy' => [
                'Biopsy: Thyroid' => 'Thyroid',
            ],
            'qhc_service_og' => [
                'OBGYN: Endometriosis Care' => 'Endometriosis Care',
                'OBGYN: Fibroids Care' => 'Fibroids Care',
                'OBGYN: General Consultation' => 'General Consultation',
                'OBGYN: Colposcopy' => 'Colposcopy',
                'OBGYN: Vulva Care' => 'Vulva Care',
                'OBGYN: Urogynecology' => 'Urogynecology',
            ]
        ];

        foreach ($list as $i => $custom_field) {
            $id = 'qhc_service_' . (stripos($custom_field, 'other') !== false ? 'other' : $i);
            $options = ['id' => $id];
            // if (is_string($i)) $options['class'] = 'has-sub';
            echo OsFormHelper::checkbox_field('booking[qhc][services][' . $custom_field . ']', $custom_field, 'on', ($booking->get_meta_by_key($custom_field, 'off') == 'on'), $options, ['class' => 'os-col-12']);
            if ($id == 'qhc_service_other') {
                echo OsFormHelper::text_field('booking[qhc][services][other_detail]', 'Please specify', $booking->get_meta_by_key('other_detail', ''), ['class' => 'os-form-control', 'placeholder' => 'Please specify', 'id' => 'other_detail'], array('class' => 'os-col-12'));
            }
            /*
            if (isset($subs[$id])) {    
                echo OsFormHelper::select_field("booking[qhc][services][{$i}_detail]", 'Which service do you need?', $subs[$id], $booking->get_meta_by_key('mdi_detail', ''), ['id' => $id . '_detail'], ['class' => 'os-col-12', 'style' => 'display: none;']);
            }
            */
        }
        ?>
    </div>
    <script>
        jQuery(document).ready(function($) {
            var $other = $('#qhc_service_other');
            var $otherDetail = $('#other_detail').parents('.os-col-12');
            $otherDetail.hide();
            $other.on('change', function() {
                if ($other.is(':checked')) {
                    $otherDetail.show();
                    $otherDetail.find('input').focus().prop('required', true);
                } else {
                    $otherDetail.hide();
                    $otherDetail.find('input').prop('required', false);
                }
            });

            $('.has-sub').on('change', function() {
                var detail = $(this).attr('id') + '_detail';
                if ($(this).is(':checked')) {
                    $('#' + detail).parents('.os-col-12').show();
                } else {
                    $('#' + detail).parents('.os-col-12').hide();
                }
            });

            $('body').on('change', 'input.os-form-checkbox[name="booking[qhc][services][Orthopedic surgery]"]', function() {
                if (
                    $(this).is(':checked') &&
                    !$(this).closest('.os-form-group').find('.custom-orthopedic').length &&
                    $('#booking_custom_fields_cf_dq70wnrg').length
                ) {
                    cl = $('#booking_custom_fields_cf_dq70wnrg').closest('.os-form-group').clone();
                    cl.addClass('custom-orthopedic');
                    $(this).closest('.os-form-group').append(cl);
                } else {
                    $(this).closest('.os-form-group').find('.custom-orthopedic').remove();
                }
            });
        });
    </script>
</div>