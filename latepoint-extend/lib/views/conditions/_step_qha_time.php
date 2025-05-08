<div class="step-qha-time-w latepoint-step-content" data-step-name="qha_time">
    <div class="os-animated-parent os-items os-as-rows">
        <?php if ($today): ?>
            <div class="os-animated-child os-item os-priced-item with-description" data-time-type="fastest">
                <div class="os-service-selector os-item-i os-animated-self" data-time-type="fastest">
                    <span class="os-item-img-w"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                    <span class="os-item-name-w">
                        <span class="os-item-name">Next available appointment</span>
                        <span class="os-item-desc"></span>
                    </span>
                </div>
            </div>
        <?php else: ?>
            <div class="os-animated-child os-item os-priced-item with-description">
                <div class="os-service-selector os-item-i os-animated-self" style="cursor: not-allowed;">
                    <span class="os-item-img-w"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                    <span class="os-item-name-w">
                        <span class="os-item-name">Next available appointment</span>
                        <span class="os-item-desc">We're sorry. There is no upcoming appointment available. Please select the option below.</span>
                    </span>
                </div>
            </div>
        <?php endif; ?>
        <div class="os-animated-child os-item os-priced-item with-description" data-time-type="future">
            <div class="os-service-selector os-item-i os-animated-self" data-time-type="future">
                <span class="os-item-img-w"><i class="fa fa-calendar" aria-hidden="true"></i></span>
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
            if ($('.latepoint-heading-w .os-heading-text-library[data-step-name="datepicker"]').length) {
                let clone = $('.latepoint-heading-w .os-heading-text-library[data-step-name="datepicker"]').clone();
                clone.attr('data-step-name', 'qha_time');
                $('.latepoint-heading-w .os-heading-text-library[data-step-name="datepicker"]').after(clone);
            }
            if ($('.latepoint-step-desc .latepoint-step-desc-library[data-step-name="datepicker"]').length) {
                let clone = $('.latepoint-step-desc .latepoint-step-desc-library[data-step-name="datepicker"]').clone();
                clone.attr('data-step-name', 'qha_time');
                $('.latepoint-step-desc .latepoint-step-desc-library[data-step-name="datepicker"]').after(clone);
            }
            var date = new Date();
            var $timeType = $('.step-qha-time-w').find('[name="booking[qha_time]"]');
            var $timeTypeItems = $('.step-qha-time-w').find('.os-item');
            $timeTypeItems.on('click', function() {
                if (!$(this).data('time-type')) {
                    return;
                }
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
                        if ($('.confirmation-info-w .confirmation-app-info ul li').length && !$('.confirmation-info-w .confirmation-app-info ul li.fastest').length) {
                            $('.confirmation-info-w .confirmation-app-info ul li').eq(0).addClass('fastest').html('Requested time: <strong>Next available appointment</strong>');
                        }
                    }, 100);
                    $('#time-type-css').append('<style>.confirmation-info-w .confirmation-app-info ul li:nth-child(2) { display: none !important; }</style>');
                } else {
                    $('#time-type').empty();
                }
            });
            <?php if ($next_date): ?>
                $('input[name="restrictions[calendar_start_date]"]').val('<?= $next_date ?>');

                // Add dom change observer on .latepoint-body
                const targetNode = document.querySelector('.latepoint-body');

                if (targetNode) {
                    const config = {
                        childList: true,
                    };
                    const callback = function(mutationsList, observer) {
                        for (let mutation of mutationsList) {
                            if (mutation.type === 'childList' && mutation.target === targetNode) {
                                const stepContent = $('.latepoint-body').find('.latepoint-step-content:not(.is-hidden)').first();
                                const stepName = stepContent.data('step-name');
                                const restrictionDate = $('input[name="restrictions[calendar_start_date]"]').val();

                                if (restrictionDate && stepName && stepName.includes('datepicker')) {
                                    $('div.os-day[data-date="' + restrictionDate + '"]').trigger('click').addClass('selected');
                                } else {
                                    console.log('Conditions not met.');
                                }
                            }
                        }
                    };
                    const observer = new MutationObserver(callback);
                    observer.observe(targetNode, config);
                }
            <?php endif; ?>
        });
    </script>
    <style>
        .step-qha-time-w .os-item-name-w i {
            font-size: 2.5em !important;
            color: #205681 !important
        }
    </style>
</div>