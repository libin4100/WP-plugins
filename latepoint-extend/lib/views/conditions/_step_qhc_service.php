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

        foreach ($list as $i => $custom_field) {
            $id = 'qhc_service_' . ($custom_field == 'Other' ? $custom_field : $i);
            echo OsFormHelper::checkbox_field('booking[qhc][' . $custom_field . ']', $custom_field, 'on', ($booking->get_meta_by_key($custom_field, 'off') == 'on'), ['id' => $id], ['class' => 'os-col-12']);
            if ($custom_field == 'Other') {
                echo OsFormHelper::text_field('booking[qhc][other_detail]', 'Please specify', $booking->get_meta_by_key('other_detail', ''), ['class' => 'os-form-control', 'placeholder' => 'Please specify', 'id' => 'other_detail'], array('class' => 'os-col-12'));
            }
        }
        ?>
    </div>
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