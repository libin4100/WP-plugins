<div class="step-datepicker-w latepoint-step-content" data-step-name="datepicker">
  <div class="os-dates-w">
    <?php OsBookingHelper::generate_monthly_calendar($calendar_start_date, ['total_attendies' => $booking->total_attendies, 'timeshift_minutes' => OsTimeHelper::get_timezone_shift_in_minutes(OsTimeHelper::get_timezone_name_from_session()),'service_id' => $booking->service_id, 'agent_id' => $booking->agent_id, 'location_id' => $booking->location_id, 'duration' => $booking->get_total_duration()]); ?>
  </div>
  <div class="time-selector-w">
    <div class="times-header"><?php _e('Pick Appointment Time For', 'latepoint'); ?> <span></span></div>
    <div class="os-times-w">
      <div class="timeslots"></div>
    </div>
  </div>
  <?php
  echo OsFormHelper::hidden_field('booking[custom_fields][start_date2]', $booking->get_meta_by_key('start_date2', ''), [ 'class' => 'latepoint_start_date', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('booking[custom_fields][start_time2]', $booking->get_meta_by_key('start_time2', ''), [ 'class' => 'latepoint_start_time', 'skip_id' => true]);
  ?>
  <script>
    jQuery(document).ready(function($) {
      if (!$('.latepoint-footer a.skip-rest').length) {
        $('.latepoint-footer a.latepoint-next-btn').before('<a href="#" class="skip-rest latepoint-btn latepoint-btn-grey" style="margin: 0 auto">Skip</a>');
      }

      $('.latepoint-footer').on('click', 'a.skip-rest', function(e) {
        e.preventDefault();
        $('.latepoint-footer .latepoint-booking-params-w').append('<input type="hidden" class="hidden-skip-rest" name="booking[custom_fields][skip_rest]" value="1">');
        $(this).remove();
        $('.latepoint-next-btn').click();
      });
      $('.latepoint-footer').on('click', 'a.latepoint-next-btn, a.latepoint-prev-btn', function(e) {
        if ($('a.skip-rest').length) {
          $('a.skip-rest').remove();
        }
        if ($('.hidden-skip-rest').length) {
          $('.hidden-skip-rest').remove();
        }
      });
    });
  </script>
</div>