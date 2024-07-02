<div class="step-qha-time-w latepoint-step-content" data-step-name="pharmacy">
    <div class="os-row">
    <?php
        echo OsFormHelper::text_field('booking[qhc][pharmacy_name]', 'Pharmacy Name', $booking->get_meta_by_key('pharmacy_name', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Name'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_phone]', 'Pharmacy Phone', $booking->get_meta_by_key('pharmacy_phone', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Phone'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_fax]', 'Pharmacy Fax', $booking->get_meta_by_key('pharmacy_fax', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Fax'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_province]', 'Province', $booking->get_meta_by_key('pharmacy_province', ''), ['class' => 'os-form-control', 'placeholder' => 'Province'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[custom_fields][first_name]', 'Pharmacist Name', $booking->get_meta_by_key('first_name', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacist Name'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_email]', 'Pharmacist Email', $booking->get_meta_by_key('pharmacy_email', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacist Email'], array('class' => 'os-col-12'));
    ?>
    </div>
</div>