<?php
trait LoadStepTrait
{
    public function loadStep($stepName, $bookingObject, $format = 'json')
    {
        global $wpdb;
        $this->_covid($bookingObject);

        $this->setField($bookingObject);

        switch ($stepName) {
            case 'services':
                $locationSettings = (new OsConditionsController)->getLocationSettings();
                if ($locationSettings) {
                    foreach ($locationSettings as $locationSetting) {
                        if (
                            $bookingObject->location_id == $locationSetting['location_id'] &&
                            in_array($bookingObject->agent_id, $locationSetting['agents'])
                        ) {
                            $show = true;
                            if ($locationSetting['referrals']) {
                                $type_id = 0;
                                $referral_tracking_value = $_COOKIE['referral_tracking'];
                                $check_url_type = $wpdb->get_results("SELECT * from wp_referral_info  WHERE `page_opened_session`='" . $referral_tracking_value . "' order by info_id asc limit 1");
                                foreach ($check_url_type as $type_values) {
                                    $type_id = $type_values->type_id;
                                }
                                if ($type_id && !in_array($type_id, $locationSetting['referrals'])) {
                                    $show = false;
                                }
                            }
                            if ($show) {
                                echo '<div class="latepoint-desc-content" style="padding:0">' . nl2br($locationSetting['message']) . '</div>';
                                remove_all_actions('latepoint_load_step');
                            }
                        }
                    }
                }
                if ($bookingObject->location_id == 1) {
                    $type_id = 0;
                    /*
                        if ($bookingObject->agent_id == 2) {
                            $referral_tracking_value = $_COOKIE['referral_tracking'];
                            $check_url_type = $wpdb->get_results("SELECT * from wp_referral_info  WHERE `page_opened_session`='" . $referral_tracking_value . "' order by info_id asc limit 1");
                            foreach ($check_url_type as $type_values) {
                                $type_id = $type_values->type_id;
                            }
                        }
                        */

                    //$tmpDisabled = (rtrim(wp_get_referer(), '/') == get_home_url()) ? true : false;
                    if ($type_id == 5) {
                        echo '<div class="step-datepicker-w latepoint-step-content" data-step-name="datepicker">
                        <div class="latepoint-desc-content" style="padding:0">Due to the recent government cut back, we are experiencing an overwhelming volume of requests.<br /><br />We stop accepting new requests temporarily. Please come back later and check again if service is resumed.<br /><br />Thank you for your support.<br />If this is an emergency, please go to the nearest hospital.</div>
                        </div>';
                        remove_all_actions('latepoint_load_step');
                    }
                }
                break;
            case 'contact':
                if (OsSettingsHelper::get_settings_value('latepoint-disabled_customer_login'))
                    OsAuthHelper::logout_customer();
                if ($this->covid || $bookingObject->service_id == 10) {
                    $customFields = OsSettingsHelper::get_settings_value('custom_fields_for_customer', false);
                    $values = json_decode($customFields, true);
                    if ($values) {
                        foreach ($values as $id => $val) {
                            if (($val['visibility'] ?? false) != 'public')
                                $values[$id]['visibility'] = 'public';
                            if ($val['label'] == 'Doctor Preference' || $val['label'] == "Reason for today's visit ( required )")
                                unset($values[$id]);
                        }
                        OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
                    }
                }
                $booking = OsParamsHelper::get_param('booking');
                if (($booking['custom_fields']['language'] ?? false) == 'fr') {
                    $customFields = OsSettingsHelper::get_settings_value('custom_fields_for_customer', false);
                    $values = json_decode($customFields, true);
                    if ($values && isset($values['cf_language'])) {
                        $values['cf_language']['options'] = "French\r\nEnglish";
                        OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
                    }
                }
                break;
            case 'custom_fields_for_booking':
                $_SESSION['certCount'] = 0;

                if (OsSettingsHelper::get_settings_value('latepoint-disabled_customer_login'))
                    OsAuthHelper::logout_customer();
                if (
                    ($allowShortcode = OsSettingsHelper::get_settings_value('latepoint-allow_shortcode_custom_fields')) 
                    || $this->isGTD()
                    || (in_array($bookingObject->service_id ?? 0, [2, 3, 7, 8]))
                ) {
                    if ($allowShortcode) {
                        $customFields = OsSettingsHelper::get_settings_value('custom_fields_for_booking', false);
                        $fields = [];
                        if ($customFields) {
                            $values = json_decode(do_shortcode($customFields), true);
                            if ($values) {
                                foreach ($values as $id => $val) {
                                    if (!isset($val['visibility']) || $val['visibility'] == 'public') $fields[$id] = $val;
                                }
                            }
                        }
                    } else {
                        $fields = OsCustomFieldsHelper::get_custom_fields_arr('booking', 'customer');
                    }

                    $custom_fields_controller = new OsCustomFieldsController();
                    $custom_fields_controller->vars['custom_fields_for_booking'] = $fields;
                    $custom_fields_controller->vars['booking'] = $bookingObject;
                    $custom_fields_controller->vars['current_step'] = $stepName;
                    $custom_fields_controller->set_layout('none');
                    $custom_fields_controller->set_return_format($format);
                    $extra = [
                        'step_name'         => $stepName,
                        'show_next_btn'     => OsStepsHelper::can_step_show_next_btn($stepName),
                        'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                        'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                        'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                        'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                    ];
                    $html = $custom_fields_controller->render($custom_fields_controller->get_view_uri('_step_custom_fields_for_booking', false), 'none', []);
                    $html = substr($html, 0, -6) . $this->needRenewJs() . '</div>';
                    if ($this->isGTD()) {
                        $html = substr($html, 0, -6) . $this->prescriptionJs() . '</div>';
                    }
                    wp_send_json(array_merge(
                        ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                        $extra
                    ));
                }
                break;
            case 'datepicker':
                if (OsSettingsHelper::get_settings_value('latepoint-disabled_customer_login'))
                    OsAuthHelper::logout_customer();
                if ($format == 'json' && $bookingObject->service_id == 10) {
                    $controller = new OsStepsController();
                    $controller->vars = $controller->vars_for_view;
                    $controller->vars['booking'] = $bookingObject;
                    $controller->vars['current_step'] = $stepName;
                    $controller->set_layout('none');
                    $controller->set_return_format($format);
                    $date = date('Y-m-d');
                    $time = date('H') * 60;
                    $css = <<<EOT
<style>
.os-row-btn { margin: 30px -30px 0; padding-top: 30px; position: relative; border-top: 1px solid #DDDDDD; }
.os-row-div { margin: 30px 0; position: relative; }
.os-row-btn .or { position: absolute; top: -15px; width: 100%; text-align:center; }
.os-row-btn .or span { background-color: #fff; padding-left: 10px; padding-right: 10px; font-size: 22px; }
.os-row-btn .os-col-12 { text-align: center; }
.os-row-btn .latepoint-btn.latepoint-skip-datetime-btn, .os-row-btn .latepoint-btn.latepoint-skip-datetime-btn:hover, .os-row-btn .latepoint-btn.latepoint-skip-datetime-btn:focus { background-color: #215681; }
.latepoint-payment { font-size: 17px !important }
</style>
<script>
jQuery(function($) {
    is_rapid = true;
    showed = false;
    var current = $time;
    var ava = String($('.os-today').data('available-minutes')).split(',');
    for(let k in ava) {
        if(ava[k] <= (current + 120))
            ava.splice(k, 1);
    }
    $('.os-today').data('available-minutes', ava.join(','))
    {$style}
});
//set_start('$date', '$time');
//show_btn=true;
</script>
EOT;

                    $html = $css
                        . $controller->render($controller->get_view_uri("_{$stepName}", false), 'none', []);
                    wp_send_json(array_merge(
                        ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                        [
                            'step_name'         => $stepName,
                            'show_next_btn'     => OsStepsHelper::can_step_show_next_btn($stepName),
                            'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                            'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                            'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                            'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                        ]
                    ));
                }
                if ($bookingObject->location_id == 1 && $bookingObject->agent_id == 2) {
                    $html = '<div class="step-datepicker-w latepoint-step-content" data-step-name="datepicker">
<div class="latepoint-desc-content" style="padding:0">Due to the recent billing changes, many virtual care providers have stopped services. We have experienced a significant increase in our volume. Please reach out to us again if you do not hear back after 1 business day. If this is an emergency, pls go to the nearest hospital.<br /><br />
<font style="font-weight: 700">We will schedule you for the next available appointment.</font> Please watch out for our email on appointment date and time.</div>
' . OsFormHelper::hidden_field('booking[start_date]', date('Y-m-d'), ['class' => 'latepoint_start_date', 'skip_id' => true]) . '
' . OsFormHelper::hidden_field('booking[start_time]', 0, ['class' => 'latepoint_start_time', 'skip_id' => true]) . '
                    </div>';
                    wp_send_json(array_merge(
                        ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                        [
                            'step_name'         => $stepName,
                            'show_next_btn'     => true,
                            'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                            'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                            'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                            'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                        ]
                    ));
                }
                break;
            case 'confirmation':
                $booking = OsParamsHelper::get_param('booking');
                if (($booking['qha_time'] ?? false) == 'fatest') {
                    $bookingObject->start_date = date('Y-m-d');
                    $bookingObject->start_time = date('H') * 60 + date('i');
                }
                $defaultAgents = OsAgentHelper::get_agents_for_service_and_location($bookingObject->service_id, $bookingObject->location_id);
                $availableAgents = [];
                if ($bookingObject->start_date && $bookingObject->start_time) {
                    foreach ($defaultAgents as $agent) {
                        if (OsAgentHelper::is_agent_available_on(
                            $agent,
                            $bookingObject->start_date,
                            $bookingObject->start_time,
                            $bookingObject->get_total_duration(),
                            $bookingObject->service_id,
                            $bookingObject->location_id,
                            $bookingObject->total_attendies
                        )) {

                            $availableAgents[] = $agent;
                        }
                    }
                }
                $availableAgents = $availableAgents ?: $defaultAgents;

                $agents = [];
                $setting = OsSettingsHelper::get_settings_value('latepoint-conditions', false);
                if ($setting) {
                    $conditions = json_decode($setting);
                    if ($conditions) {
                        foreach ($conditions as $condition) {
                            if ($condition->operator == 'and') {
                                $check = true;
                                foreach ($condition->custom_fields as $key => $cf) {
                                    if ($bookingObject->custom_fields[$key] != $cf) $check = false;
                                }
                            } elseif ($condition->operator == 'or') {
                                $check = false;
                                foreach ($condition->custom_fields as $key => $cf) {
                                    if ($bookingObject->custom_fields[$key] == $cf) $check = true;
                                }
                            }
                            if ($check == true) {
                                $agents += $condition->agents;
                            }
                        }
                    }
                }
                if ($availableAgents)
                    $agents = array_intersect($agents, $availableAgents);

                if ($agents) {
                    $bookingObject->agent_id = array_shift($agents);
                    if ($agents) $bookingObject->agents = json_encode($agents);
                }
                break;
            //Steps for QHA appointment request
            case 'qha_time':
                if (!in_array($bookingObject->service_id, [13, 15])) {
                    $controller = new OsConditionsController();
                    $html = $controller->render($controller->get_view_uri('_step_qha_time'), 'none', [
                        'booking' => $bookingObject,
                        'current_step' => $stepName
                    ]);
                    wp_send_json(array_merge(
                        ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                        [
                            'step_name'         => 'qha_time',
                            'show_next_btn'     => true,
                            'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                            'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                            'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                            'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                        ]
                    ));
                }

                //Steps for QHA Care Navigation
            case 'qhc_service':
                if (in_array($bookingObject->service_id, [13, 15])) {
                    $controller = new OsConditionsController();
                    $html = $controller->render($controller->get_view_uri('_step_qhc_service'), 'none', [
                        'booking' => $bookingObject,
                        'current_step' => $stepName
                    ]);
                    wp_send_json(array_merge(
                        ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                        [
                            'step_name'         => $stepName,
                            'show_next_btn'     => true,
                            'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                            'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                            'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                            'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                        ]
                    ));
                }
                break;
            case 'qhc_contact':
                $controller = new OsConditionsController();
                $html = $controller->render($controller->get_view_uri('_step_qhc_contact'), 'none', [
                    'booking' => $bookingObject,
                    'current_step' => $stepName
                ]);
                wp_send_json(array_merge(
                    ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                    [
                        'step_name'         => $stepName,
                        'show_next_btn'     => true,
                        'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                        'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                        'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                        'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                    ]
                ));
            case 'qhc_additional':
                $controller = new OsConditionsController();
                $html = $controller->render($controller->get_view_uri('_step_qhc_additional'), 'none', [
                    'booking' => $bookingObject,
                    'current_step' => $stepName
                ]);
                wp_send_json(array_merge(
                    ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                    [
                        'step_name'         => $stepName,
                        'show_next_btn'     => true,
                        'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                        'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                        'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                        'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                    ]
                ));
                break;
            case 'pharmacy':
                $controller = new OsConditionsController();
                echo $controller->render($controller->get_view_uri('_step_pharmacy'), 'none', [
                    'booking' => $bookingObject,
                    'current_step' => $stepName
                ]);
                break;
            case 'pharmacy_additional':
                $controller = new OsConditionsController();
                $html = $controller->render($controller->get_view_uri('_step_pharmacy_additional'), 'none', [
                    'booking' => $bookingObject,
                    'current_step' => $stepName
                ]);
                wp_send_json(array_merge(
                    ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                    [
                        'step_name'         => $stepName,
                        'show_next_btn'     => true,
                        'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                        'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                        'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                        'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                    ]
                ));
                break;
            case 'datepicker2':
            case 'datepicker3':
                $controller = new OsConditionsController();
                $html = $controller->render($controller->get_view_uri('_step_' . $stepName), 'none', [
                    'booking' => $bookingObject,
                    'params' => OsParamsHelper::get_param('booking'),
                    'current_step' => $stepName
                ]);
                wp_send_json(array_merge(
                    ['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html],
                    [
                        'step_name'         => $stepName,
                        'show_next_btn'     => false,
                        'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName),
                        'is_first_step'     => OsStepsHelper::is_first_step($stepName),
                        'is_last_step'      => OsStepsHelper::is_last_step($stepName),
                        'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                    ]
                ));
                break;
        }
    }
}
