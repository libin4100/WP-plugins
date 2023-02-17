<div class="step-qhc-service-w latepoint-step-content" data-step-name="qhc_additional">
    <div class="os-row">
        <?php
        echo OsFormHelper::text_field('booking[qhc][additional_concern]', 'Please tell us your concern that require care navigation support?', $booking->get_meta_by_key('additinal_concern', ''), ['class' => 'os-form-control', 'placeholder' => 'Please tell us your concern that require care navigation support?'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][additional_waittime]', 'What is your current wait time for the services you need?', $booking->get_meta_by_key('additional_waittime', ''), ['class' => 'os-form-control', 'placeholder' => 'What is your current wait time for the services you need?'], array('class' => 'os-col-12'));
        ?>
        <div class="os-col-12 os-col-sm-12">
            <div class="os-form-group os-form-group-transparent os-form-textfield-group"><label for="additional_file">Please upload all the relevent documents for our care navigator to review (i.e. consult notes, imaging reports, blood work, etc.)</label><input type="file" placeholder="Please upload all the relevent documents for our care navigator to review (i.e. consult notes, imaging reports, blood work, etc.)" name="booking[qhc][additional_file]" value="" class="os-form-control" id="additional_file"></div>
        </div>
        <input type="hidden" name="booking[start_date]" value="<?= date('Y-m-d') ?>">
        <input type="hidden" name="booking[start_time]" value="0">
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        //Upload file when user select file
        $('#additional_file').on('change', function() {
            var file_data = $('#additional_file').prop('files')[0];
            var form_data = new FormData();
            form_data.append('additinal_file', file_data);
            form_data.append('action', 'latepoint_file_upload');
            form_data.append('security', '<?= wp_create_nonce('latepoint_file_upload') ?>');
            $.ajax({
                url: '<?= admin_url('admin-ajax.php') ?>',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(response) {
                    if (response.success) {
                        $('#additional_file').val(response.data);
                    }
                }
            });
        });
    });
</script>