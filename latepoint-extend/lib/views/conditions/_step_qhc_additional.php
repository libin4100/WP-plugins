<div class="step-qhc-service-w latepoint-step-content" data-step-name="qhc_additional">
    <div class="os-row">
        <?php
        echo OsFormHelper::text_field('booking[qhc][additional_concern]', 'Please tell us your concern that require care navigation support?', $booking->get_meta_by_key('additinal_concern', ''), ['class' => 'os-form-control', 'placeholder' => 'Please tell us your concern that require care navigation support?'], array('class' => 'os-col-12'));
        echo OsFormHelper::text_field('booking[qhc][additional_waittime]', 'What is your current wait time for the services you need?', $booking->get_meta_by_key('additional_waittime', ''), ['class' => 'os-form-control', 'placeholder' => 'What is your current wait time for the services you need?'], array('class' => 'os-col-12'));
        ?>
        <div class="os-col-12 os-col-sm-12">
            <div class="os-form-group os-form-group-transparent">
                <label for="additional_file_upload">
                    Please upload all the relevent documents for our care navigator to review (i.e. consult notes, imaging reports, blood work, etc.)
                    <div class="btn btn-block latepoint-btn latepoint-btn-secondary">
                        <input type="file" name="booking_file" value="" class="os-form-control" style="display:none" id="additional_file_upload">
                    </div>
                </label>
            </div>
            <div class="latepoint-loading" style="display: none;">
                <div class="lds-dual-ring"></div>
            </div>
            <!--Show the uploaded file name with delete button-->
            <div class="uploaded_file"></div>
        </div>
        <input type="hidden" name="booking[start_date]" value="<?= date('Y-m-d') ?>">
        <input type="hidden" name="booking[start_time]" value="0">
        <input type="hidden" name="booking[qhc][additional_file][]" class="additional_file" value="">
    </div>
    <script>
        jQuery(document).ready(function($) {
            //Upload file when user select file
            $('#additional_file_upload').on('change', function() {
                _this = $(this);
                $(this).prop('disabled', true);
                //Show the loading icon
                $('.latepoint-loading').show();
                var file_data = $('#additional_file_upload').prop('files')[0];
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
                        if (response.status == 'success') {
                            var clone = $('.additional_file').last().clone();
                            $('.additional_file').last().val(response.file).after(clone);
                            $('#additional_file_upload').val('');
                            $('.uploaded_file').append('<div class="uploaded_file_name">' + response.original_name + ' <a href="#" class="delete_file" data-file="' + response.file + '">x</a></div>');
                        }
                    },
                    complete: function() {
                        _this.prop('disabled', false);
                        $('.latepoint-loading').hide();
                    }
                });
            });
            //Delete file when user click on delete button
            $(document).on('click', '.delete_file', function() {
                var file = $(this).data('file');
                $('.additional_file').each(function() {
                    if ($(this).val() == file) {
                        $(this).remove();
                    }
                });
                $(this).parent().remove();
            });
        });
    </script>
    <style>
        label[for="additional_file_upload"] {
            font-size: 14px;
            font-weight: 500;
            opacity: 0.8;
        }

        .lds-dual-ring {
            display: inline-block;
            width: 80px;
            height: 80px;
        }

        .lds-dual-ring:after {
            content: " ";
            display: block;
            width: 64px;
            height: 64px;
            margin: 8px;
            border-radius: 50%;
            border: 6px solid #8e97b3;
            border-color: #8e97b3 transparent #8e97b3 transparent;
            animation: lds-dual-ring 1.2s linear infinite;
        }

        @keyframes lds-dual-ring {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</div>