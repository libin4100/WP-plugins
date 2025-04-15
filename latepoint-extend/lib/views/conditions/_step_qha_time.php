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
            <div class="os-animated-child os-item os-priced-item with-description" data-time-type="lastest">
                <div class="os-service-selector os-item-i os-animated-self" data-time-type="lastest">
                    <span class="os-item-img-w"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                    <span class="os-item-name-w">
                        <span class="os-item-name">Lastest available appointment date</span>
                        <span class="os-item-desc"></span>
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
                console.log('Target Node (.latepoint-body):', targetNode); // <-- Add this log

                if (targetNode) { // Only proceed if the target node exists
                    const config = {
                        childList: true,
                        subtree: true
                    };
                    const callback = function(mutationsList, observer) {
                        // console.log('Mutations:', mutationsList); // Optional: See all mutations
                        for (let mutation of mutationsList) {
                            console.log('Mutation observed:', mutation); // <-- Log any mutation first
                            if (mutation.type === 'childList') {
                                const stepContent = $(mutation.target).closest('.latepoint-step-content:not(.is-hidden)');
                                const stepName = stepContent.data('step-name');
                                const restrictionDate = $('input[name="restrictions[calendar_start_date]"]').val();
                                console.log('Mutation Target:', mutation.target);
                                console.log('Step Content Found:', stepContent.length > 0);
                                console.log('Step Name:', stepName);
                                console.log('Restriction Date:', restrictionDate);

                                if (restrictionDate && stepName && stepName.includes('datepicker')) {
                                    console.log('Conditions met. Triggering click on date:', restrictionDate);
                                    $('div.os-day[data-date="' + restrictionDate + '"]').trigger('click');
                                } else {
                                    console.log('Conditions not met.');
                                }
                            }
                        }
                    };
                    const observer = new MutationObserver(callback);
                    console.log('Attaching MutationObserver to target node.'); // <-- Add this log
                    observer.observe(targetNode, config);
                } else {
                    console.error('.latepoint-body element not found when initializing MutationObserver.'); // <-- Add error log if target not found
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