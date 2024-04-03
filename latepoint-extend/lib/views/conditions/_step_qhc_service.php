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
            'Specialist referral',
            'Medical diagnostics and imaging',
            'Paediatric care',
            'Medical procedure/surgery',
            'social and community support services',
            'Home health and community care',
            'Rehab services',
            'Dental care and orthotics',
            'Family medicine',
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

        foreach ($list as $i => $custom_field) {
            $id = 'qhc_service_' . (stripos($custom_field, 'other') !== false ? 'other' : $i);
            echo OsFormHelper::checkbox_field('booking[qhc][services][' . $custom_field . ']', $custom_field, 'on', ($booking->get_meta_by_key($custom_field, 'off') == 'on'), ['id' => $id], ['class' => 'os-col-12']);
            if ($id == 'qhc_service_other') {
                echo OsFormHelper::text_field('booking[qhc][services][other_detail]', 'Please specify', $booking->get_meta_by_key('other_detail', ''), ['class' => 'os-form-control', 'placeholder' => 'Please specify', 'id' => 'other_detail'], array('class' => 'os-col-12'));
            }
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
        });
    </script>
</div>