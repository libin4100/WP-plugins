<div class="step-qha-time-w latepoint-step-content" data-step-name="pharmacy">
    <div class="os-row">
    <?php
        echo OsFormHelper::text_field('booking[qhc][pharmacy_name]', 'Pharmacy Name', $booking->get_meta_by_key('pharmacy_name', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Name'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_address]', 'Pharmacy Address', $booking->get_meta_by_key('pharmacy_address', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Address'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_phone]', 'Pharmacy Phone', $booking->get_meta_by_key('pharmacy_phone', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Phone'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_fax]', 'Pharmacy Fax', $booking->get_meta_by_key('pharmacy_fax', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Fax'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][pharmacy_email]', 'Pharmacy Email', $booking->get_meta_by_key('pharmacy_email', ''), ['class' => 'os-form-control', 'placeholder' => 'Pharmacy Email'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][your_name]', 'Your Name', $booking->get_meta_by_key('your_name', ''), ['class' => 'os-form-control', 'placeholder' => 'Your Name'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][your_phone]', 'Number to contact (if different from above)', $booking->get_meta_by_key('your_phone', ''), ['class' => 'os-form-control', 'placeholder' => 'Number to contact (if different from above)'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][your_email]', 'Email to contact (if different from above)', $booking->get_meta_by_key('your_email', ''), ['class' => 'os-form-control', 'placeholder' => 'Email to contact (if different from above)'], array('class' => 'os-col-12'));
    ?>
    </div>
</div>