<div class="step-datepicker-w latepoint-step-content" data-step-name="datepicker2">
  <div class="os-dates-w">
    <?php OsBookingHelper::generate_monthly_calendar($calendar_start_date, ['total_attendies' => $booking->total_attendies, 'timeshift_minutes' => OsTimeHelper::get_timezone_shift_in_minutes(OsTimeHelper::get_timezone_name_from_session()), 'service_id' => $booking->service_id, 'agent_id' => $booking->agent_id, 'location_id' => $booking->location_id, 'duration' => $booking->get_total_duration()]); ?>
  </div>
  <div class="time-selector-w">
    <div class="times-header"><?php _e('Pick Appointment Time For', 'latepoint'); ?> <span></span></div>
    <div class="os-times-w">
      <div class="timeslots"></div>
    </div>
  </div>
  <?php
  echo OsFormHelper::hidden_field('booking[start_date]', $booking->start_date, ['skip_id' => true]);
  echo OsFormHelper::hidden_field('booking[start_time]', $booking->start_time, ['skip_id' => true]);
  echo OsFormHelper::hidden_field('booking[custom_fields][start_date2]', $booking->get_meta_by_key('start_date2', ''), ['class' => 'latepoint_start_date', 'skip_id' => true]);
  echo OsFormHelper::hidden_field('booking[custom_fields][start_time2]', $booking->get_meta_by_key('start_time2', ''), ['class' => 'latepoint_start_time', 'skip_id' => true]);
  ?>
  <script>
    jQuery(document).ready(function($) {
      if (!$('.latepoint-footer a.skip-rest').length) {
        $('.latepoint-footer a.latepoint-next-btn').before('<a href="#" class="skip-rest latepoint-btn latepoint-btn-grey" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">Skip</a>');
      }

      $('.latepoint-footer').on('click', 'a.skip-rest', function(e) {
        e.preventDefault();
        if (!$('.hidden-skip-rest').length)
          $('.latepoint-footer .latepoint-booking-params-w').append('<input type="hidden" class="hidden-skip-rest" name="booking[custom_fields][skip_rest]" value="1">');
        $('.latepoint_start_date').val('');
        $('.latepoint_start_time').val('');
        $('.latepoint-next-btn').removeClass('disabled').click();
        $('a.skip-rest').hide();
      });

      $('.latepoint-next-btn').on('click', function(e) {
        if ($('a.skip-rest').length) {
          if (['datepicker', 'datepicker2'].includes($('.latepoint-footer .latepoint-booking-params-w .latepoint_current_step').val())) {
            $('a.skip-rest').show();
          } else {
            $('a.skip-rest').hide();
          }
        }
        if (e.which && $('.hidden-skip-rest').length) {
          $('.hidden-skip-rest').remove();
        }
      });

      $('.latepoint-prev-btn').on('click', function(e) {
        if ($('a.skip-rest').length) {
          var prevStep = $(this).closest(".latepoint-form").find(".latepoint-step-content.is-hidden").last().data('step-name');
          console.log(prevStep);
          if (['datepicker2', 'datepicker3'].includes(prevStep)) {
            $('a.skip-rest').show();
          } else {
            $('a.skip-rest').hide();
          }
        }
        if ($('.hidden-skip-rest').length) {
          $('.hidden-skip-rest').remove();
        }
      });

      sday = $('div[data-step-name="datepicker2"] div.os-day[data-date="<?= $booking->start_date ?>"]');
      remove_booked(sday, '<?= $booking->start_time ?>');
    });

    function remove_booked(obj, start_time) {
      if (obj.length) {
        minutes = obj.data('available-minutes');
        if (minutes) {
          minutes = minutes.split(',');
          minutes = minutes.filter(function(value, index, arr) {
            return value != start_time;
          });
          obj.data('available-minutes', minutes.join(','));
        }
      }
    }
  </script>
</div>