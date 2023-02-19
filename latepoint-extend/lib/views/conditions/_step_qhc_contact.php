<div class="step-qhc-service-w latepoint-step-content" data-step-name="qhc_contact">
    <p>Complete if diffirent from client details</p>
    <div class="os-row">
        <?php
        echo OsFormHelper::text_field('booking[qhc][contact_person_name]', 'Contact person name', $booking->get_meta_by_key('contact_person_name', ''), ['class' => 'os-form-control', 'placeholder' => 'Contact person name'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][contact_person_phone]', 'Preferred contact phone number', $booking->get_meta_by_key('contact_person_phone', ''), ['class' => 'os-mask-phone os-form-control', 'placeholder' => 'Preferred contact phone number'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][contact_person_email]', 'Eamil address', $booking->get_meta_by_key('contact_person_email', ''), ['class' => 'os-form-control', 'placeholder' => 'Email address', 'type' => 'email'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][contact_person_additional]', 'Additional notes', $booking->get_meta_by_key('contact_person_additional', ''), ['class' => 'os-form-control', 'placeholder' => 'Additional notes'], array('class' => 'os-col-12'));
        ?>
    </div>
</div>