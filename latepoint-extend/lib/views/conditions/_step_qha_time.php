<div class="step-qha-time-w latepoint-step-content" data-step-name="qha_time">
    <div class="os-animated-parent os-items os-as-rows">
        <div class="os-animated-child os-item os-priced-item with-description" data-time-type="fastest">
            <div class="os-service-selector os-item-i os-animated-self" data-time-type="fastest">
                <span class="os-item-img-w"></span>
                <span class="os-item-name-w">
                    <span class="os-item-name">Next available appointment</span>
                    <span class="os-item-desc"></span>
                </span>
            </div>
        </div>
        <div class="os-animated-child os-item os-priced-item with-description" data-time-type="future">
            <div class="os-service-selector os-item-i os-animated-self" data-time-type="future">
                <span class="os-item-img-w"></span>
                <span class="os-item-name-w">
                    <span class="os-item-name">Preferred date/time</span>
                    <span class="os-item-desc"></span>
                </span>
            </div>
        </div>
    </div>
    <?= OsFormHelper::hidden_field('booking[qha_time]', '') ?>
    <div id="time-type"></div>
    <div id="time-type-css"></div>
    <script>
        jQuery(document).ready(function($) {
            var date = new Date();
            var $timeType = $('.step-qha-time-w').find('[name="booking[qha_time]"]');
            var $timeTypeItems = $('.step-qha-time-w').find('.os-item');
            $timeTypeItems.on('click', function() {
                $timeType.val($(this).data('time-type'));
                $timeTypeItems.removeClass('selected');
                $(this).addClass('selected');
                $('.latepoint-next-btn').click();
                $('.os-summary-value-date').text('');
                $('.os-summary-value-time').text('');
                $('.os-summary-value-date').parents('.os-summary-line').removeClass('os-has-show').hide();
                $('.os-summary-value-time').parents('.os-summary-line').removeClass('os-has-show').hide();
                if ($timeType.val() == 'fastest') {
                    $('.os-summary-value-time').text('Next available appointment');
                    $('.os-summary-value-time').parents('.os-summary-line').addClass('os-has-show').show();
                    $('#time-type').append('<input type="hidden" name="booking[start_date]" value="<?= date('Y-m-d') ?>"><input type="hidden" name="booking[start_time]" value="0">');
                    setInterval(function() {
                        if ($('.confirmation-info-w .confirmation-app-info ul li').length) {
                            $('.confirmation-info-w .confirmation-app-info ul li').eq(0).html('Requested time: <strong>Next available appointment</strong>');
                        }
                    }, 100);
                    $('#time-type-css').append('<style>.confirmation-info-w .confirmation-app-info ul li:nth-child(2) { display: none !important; }</style>');
                } else {
                    $('#time-type').empty();
                }
            });
        });
    </script>
</div>