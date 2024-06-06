<div class="step-datepicker-w latepoint-step-content" data-step-name="datepicker3">
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
  echo OsFormHelper::hidden_field('booking[start_date]', $booking->start_date, ['skip_id' => true]);
  echo OsFormHelper::hidden_field('booking[start_time]', $booking->start_time, ['skip_id' => true]);
  echo OsFormHelper::hidden_field('booking[custom_fields][start_date2]', $params['custom_fields']['start_date2'] ?? '', ['skip_id' => true]);
  echo OsFormHelper::hidden_field('booking[custom_fields][start_time2]', $params['custom_fields']['start_time2'] ?? '', ['skip_id' => true]);
  echo OsFormHelper::hidden_field('booking[custom_fields][start_date3]', $booking->get_meta_by_key('start_date3', ''), [ 'class' => 'latepoint_start_date', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('booking[custom_fields][start_time3]', $booking->get_meta_by_key('start_time3', ''), [ 'class' => 'latepoint_start_time', 'skip_id' => true]);
  ?>
</div>
<script>
  jQuery(document).ready(function($) {
      sday = $('div[data-step-name="datepicker3"] div.os-day[data-date="<?= $booking->start_date ?>"]');
      remove_booked(sday, '<?= $booking->start_time ?>');
      sday = $('div[data-step-name="datepicker3"] div.os-day[data-date="<?= $params['custom_fields']['start_date2'] ?? '' ?>"]');
      remove_booked(sday, '<?= $params['custom_fields']['start_time2'] ?? '' ?>');
  });
</script>