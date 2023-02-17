<div class="step-qhc-service-w latepoint-step-content" data-step-name="qhc_additional">
    <div class="os-row">
    <?php
    echo OsFormHelper::text_field('booking[custom_fields][additional_concern]', 'Please tell us your concern that require care navigation support?', $booking->get_meta_by_key('additinal_concern', ''), ['class' => 'os-form-control', 'placeholder' => 'Please tell us your concern that require care navigation support?'], array('class' => 'os-col-12'));
    echo OsFormHelper::text_field('booking[custom_fields][additional_waittime]', 'What is your current wait time for the services you need?', $booking->get_meta_by_key('additional_waittime', ''), ['class' => 'os-form-control', 'placeholder' => 'What is your current wait time for the services you need?'], array('class' => 'os-col-12'));
    ?>
    <div class="os-col-12 os-col-sm-12"><div class="os-form-group os-form-group-transparent os-form-textfield-group"><label for="additional_file">Please upload all the relevent documents for our care navigator to review (i.e. consult notes, imaging reports, blood work, etc.)</label><input type="file" placeholder="Please upload all the relevent documents for our care navigator to review (i.e. consult notes, imaging reports, blood work, etc.)" name="booking[custom_fields][additional_file]" value="" class="os-form-control" id="additional_file"></div></div>
    </div>
</div>
