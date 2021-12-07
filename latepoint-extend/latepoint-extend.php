<?php
/**
 * Plugin Name: Latepoint Addon - Conditions
 * Plugin URI: http://www.mywebsite.com/my-first-plugin
 * Description: Latepoint extension for custom setting
 * Version: 1.0
 * Author: Your Name
 * Author URI: http://www.mywebsite.com
 */

if(!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


if(!class_exists('LatePointExt')):
/**
 * Main Class.
 *
 */
final class LatePointExt {
    public $version = '1.0.0';
    public $dbVersion = '1.0.0';
    public $addonName = 'latepoint-extend';

    protected $covid;
    protected $others;
    protected $acorn;

    public function __construct() {
        $this->defines();
        $this->hooks();
    }

    public function defines() {
    }

    public function hooks() {
        add_action('wp_loaded', [$this, 'route']);
        add_action('latepoint_includes', [$this, 'includes']);
        add_action('latepoint_load_step', [$this, 'loadStep'], 5, 3);
        add_action('latepoint_process_step', [$this, 'processStep'], 5, 2);
        add_action('latepoint_admin_enqueue_scripts', [$this, 'adminScripts']);
        add_action('latepoint_wp_enqueue_scripts', [$this, 'frontScripts']);
        add_action('latepoint_model_save', [$this, 'saveAgent']);
        add_action('latepoint_booking_quick_edit_form_after',[$this, 'outputQuickForm']);
        add_action('latepoint_step_confirmation_head_info_before',[$this, 'confirmationInfoBefore']);
        add_action('latepoint_step_confirmation_before',[$this, 'confirmationInfoAfter']);
        add_action('latepoint_booking_steps_contact_after', [$this, 'contactCovid'], 5);
        add_action('latepoint_booking_created_frontend', [$this, 'bookingCreated']);
        add_action('latepoint_steps_side_panel_after', [$this, 'sidePanel']);

        add_filter('latepoint_installed_addons', [$this, 'registerAddon']);
        add_filter('latepoint_side_menu', [$this, 'addMenu']);
        add_filter('latepoint_step_show_next_btn_rules', [$this, 'addNextBtn'], 10, 2);

        register_activation_hook(__FILE__, [$this, 'onActivate']);
        register_deactivation_hook(__FILE__, [$this, 'onDeactivate']);
    }

    public function route()
    {
        $routeName = OsRouterHelper::get_request_param('route_name', '');
        if($routeName == 'resend_latepoint') {
            $id = OsRouterHelper::get_request_param('id', '');
            if($id) {
                $booking = new OsBookingModel($id);
                OsNotificationsHelper::send_agent_new_appointment_notification($booking);
            }
        }
    }

    public function includes() {
        include_once(dirname( __FILE__ ) . '/lib/controllers/conditions_controller.php');
    }

    public function bookingCreated($booking)
    {
        $this->_covid($booking);

        if($this->covid || $this->others || $this->acorn || $booking->service_id == 10) {
            OsSettingsHelper::$loaded_values['notifications_email'] = 'off';
        }
    }

    public function sidePanel($stepName)
    {
        $this->_covid(OsStepsHelper::$booking_object);

        if($this->covid || $this->others || $this->acorn) {
            $url = site_url('wp-content/uploads/2021/05/icon1x.png');
            echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').text("Please proceed with payment by clicking 'Make Payment' button. Our team will contact you shortly to confirm your appointment. If you do not get a response within 24 hours, please call us to confirm your appointment. *If this is an emergency, please go to the nearest hospital or call 911.*");
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-media').css("background-image", 'url({$url})');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text('Payment');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text-library[data-step-name="confirmation"]').text('Appointment Information');
});
</script>
EOT;
        }
        $str = '';
        if(OsStepsHelper::$booking_object->location_id == 1) {
            $str = <<<EOT
        if($('.os-summary-value-location').length 
            && $('.os-summary-value-location').text().includes('Ontario') 
            && $('#customer_custom_fields_cf_7lkik5fd').length 
            && !$('#customer_custom_fields_cf_7lkik5fd option[value="Pediatrician"]').length
        ) {
            $('#customer_custom_fields_cf_7lkik5fd').append('<option value="Pediatrician">Pediatrician</option>');
        }
EOT;
        }
            echo <<<EOT
<script>
jQuery(function($) {
    first = true;
    setInterval(function() {
        $str
        if($('#customer_custom_fields_cf_eh0zhq9s').length && first) {
            $('#customer_phone').parents('.os-col-sm-12').after($('#customer_custom_fields_cf_eh0zhq9s').parents('.os-col-12'))
            first = false;
        }
    }, 500);
});
</script>
EOT;
    }

    public function contactCovid($customer)
    {
        $custom_fields_for_customer = OsCustomFieldsHelper::get_custom_fields_arr('customer', 'customer');
        if(isset($custom_fields_for_customer) && !empty($custom_fields_for_customer)) {
            foreach($custom_fields_for_customer as $custom_field) {
                $required_class = ($custom_field['required'] == 'on') ? 'required' : '';
                if($this->covid || $this->acorn) {
                    if($custom_field['label'] == 'Doctor Preference') continue;
                }
                if($custom_field['label'] == 'Home Phone Number (Optional)') $required_class .= ' os-mask-phone';

                switch ($custom_field['type']) {
                case 'text':
                    echo OsFormHelper::text_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
                    break;
                case 'textarea':
                    echo OsFormHelper::textarea_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
                    break;
                case 'select':
                    echo OsFormHelper::select_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], OsFormHelper::generate_select_options_from_custom_field($custom_field['options']), $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
                    break;
                case 'checkbox':
                    echo OsFormHelper::checkbox_field('customer[custom_fields]['.$custom_field['id'].']', $custom_field['label'], 'on', ($customer->get_meta_by_key($custom_field['id'], 'off') == 'on') , ['class' => $required_class], array('class' => $custom_field['width']));
                    break;
                }
            }
        }
        remove_all_actions('latepoint_booking_steps_contact_after');
    }

    public function loadStep($stepName, $bookingObject, $format = 'json') {
        $this->_covid($bookingObject);

        switch($stepName) {
        case 'contact':
            if(OsSettingsHelper::get_settings_value('latepoint-disabled_customer_login'))
                OsAuthHelper::logout_customer();
            if($bookingObject->service_id == 10) {
                $customFields = OsSettingsHelper::get_settings_value('custom_fields_for_customer', false);
                $values = json_decode($customFields, true);
                if($values) {
                    foreach($values as $id => $val) {
                        if(($val['visibility'] ?? false) != 'public')
                            $values[$id]['visibility'] = 'public';
                    }
                    OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
                }
            }
            break;
        case 'custom_fields_for_booking':
            if($bookingObject->service_id == 10) {
                $customFields = OsSettingsHelper::get_settings_value('custom_fields_for_booking', false);
                $values = json_decode($customFields, true);
                if($values) {
                    foreach($values as $id => $val) {
                        if(($val['visibility'] ?? false) == 'hidden')
                            $values[$id]['visibility'] = 'public';
                    }
                    OsSettingsHelper::$loaded_values['custom_fields_for_booking'] = json_encode($values);
                }
            }
            if(OsSettingsHelper::get_settings_value('latepoint-allow_shortcode_custom_fields')) {
                $customFields = OsSettingsHelper::get_settings_value('custom_fields_for_booking', false);
                $fields = [];
                if($customFields) {
                    $values = json_decode(do_shortcode($customFields), true);
                    if($values) {
                        foreach($values as $id => $val) {
                            if(!isset($val['visibility']) || $val['visibility'] == 'public') $fields[$id] = $val;
                        }
                    }
                }

                $custom_fields_controller = new OsCustomFieldsController();
                $custom_fields_controller->vars['custom_fields_for_booking'] = $fields;
                $custom_fields_controller->vars['booking'] = $bookingObject;
                $custom_fields_controller->vars['current_step'] = $stepName;
                $custom_fields_controller->set_layout('none');
                $custom_fields_controller->set_return_format($format);
                $custom_fields_controller->format_render('_step_custom_fields_for_booking', [], [
                    'step_name'         => $stepName, 
                    'show_next_btn'     => OsStepsHelper::can_step_show_next_btn($stepName), 
                    'show_prev_btn'     => OsStepsHelper::can_step_show_prev_btn($stepName), 
                    'is_first_step'     => OsStepsHelper::is_first_step($stepName), 
                    'is_last_step'      => OsStepsHelper::is_last_step($stepName), 
                    'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)]);
            }
            break;
        case 'datepicker':
            if($format == 'json' && $bookingObject->service_id == 10) {
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
.os-row-btn .or { position: absolute; top: -15px; width: 100%; text-align:center; }
.os-row-btn .or span { background-color: #fff; padding-left: 10px; padding-right: 10px; font-size: 22px; }
.os-row-btn .os-col-12 { text-align: center; }
.os-row-btn .latepoint-btn.latepoint-skip-datetime-btn, .os-row-btn .latepoint-btn.latepoint-skip-datetime-btn:hover, .os-row-btn .latepoint-btn.latepoint-skip-datetime-btn:focus { background-color: #215681; }
</style>
EOT;

                $html = "<script>set_start('$date', '$time'); show_btn=true;</script>" 
                    . $css
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
            break;
        case 'confirmation':
            $defaultAgents = OsAgentHelper::get_agents_for_service_and_location($bookingObject->service_id, $bookingObject->location_id); $availableAgents = [];
            if($bookingObject->start_date && $bookingObject->start_time) {
                foreach($defaultAgents as $agent) {
                    if(OsAgentHelper::is_agent_available_on($agent,
                        $bookingObject->start_date,
                        $bookingObject->start_time,
                        $bookingObject->get_total_duration(),
                        $bookingObject->service_id,
                        $bookingObject->location_id,
                        $bookingObject->total_attendies)) {

                        $availableAgents[] = $agent;
                    }
                }
            }
            $availableAgents = $availableAgents ?: $defaultAgents;

            $agents = [];
            $setting = OsSettingsHelper::get_settings_value('latepoint-conditions', false);
            if($setting) {
                $conditions = json_decode($setting);
                if($conditions) {
                    foreach($conditions as $condition) {
                        if($condition->operator == 'and') {
                            $check = true;
                            foreach($condition->custom_fields as $key => $cf) {
                                if($bookingObject->custom_fields[$key] != $cf) $check = false;
                            }
                        } elseif($condition->operator == 'or') {
                            $check = false;
                            foreach($condition->custom_fields as $key => $cf) {
                                if($bookingObject->custom_fields[$key] == $cf) $check = true;
                            }
                        }
                        if($check == true) {
                            $agents += $condition->agents;
                        }
                    }
                }
            }
            if($availableAgents)
                $agents = array_intersect($agents, $availableAgents);

            if($agents) {
                $bookingObject->agent_id = array_shift($agents);
                if($agents) $bookingObject->agents = json_encode($agents);
            }
            break;
        }
    }

    public function processStep($stepName, $bookingObject)
    {
        if($stepName == 'custom_fields_for_booking') {
            $booking = OsParamsHelper::get_param('booking');
            $custom_fields_data = $booking['custom_fields'];
            $custom_fields_for_booking = OsCustomFieldsHelper::get_custom_fields_arr('booking', 'all');

            $is_valid = true;
            $fields = [
                'Are you experiencing any COVID-19 symptoms?',
                'Have you been in close physical contact with someone who currently has COVID-19?',
                'Are you part of a specific outbreak investigation?'
            ];
            $errors = [];
            foreach($custom_fields_for_booking as $k => $f) {
                if($bookingObject->service_id == 10 && in_array(trim($f['label']), $fields) && (strtolower($custom_fields_data[$k]) != 'no')) {
                    $errors[] = ['type' => 'validation', 'message' => 'You must be asymptomatic to proceed with the booking.'];
                    break;
                }
            }
            $error_messages = [];
            if($errors){
                $is_valid = false;
                foreach($errors as $error){
                    $error_messages[] = $error['message'];
                }
            }
            if(!$is_valid){
                remove_all_actions('latepoint_process_step');
                wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => $error_messages));
                return;
            }
        }
        if($stepName == 'contact' && $bookingObject->service_id == 10) {
            $booking = OsParamsHelper::get_param('customer');
            $data = $booking['custom_fields']['cf_DV0y9heS'] ?? false;
            if(!$data || $data != 'on') {
                remove_all_actions('latepoint_process_step');
                wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['You need to read and accepte the consent acknowledgment to book the appointment.']));
                return;
            }
        }
    }

    protected function _covid($booking)
    {
        //Covid-19
        $sc = new OsServiceCategoryModel(1);
        $services = [];
        if($sc->id && $sc->services) {
            foreach($sc->services as $s) {
                $services[] = $s->id;
            }
        }
        if(in_array($booking->service_id, $services)) {
            $this->covid = true;
        }

        //Others
        $sc2 = new OsServiceCategoryModel(2);
        $services = [];
        if($sc2->id && $sc2->services) {
            foreach($sc2->services as $s) {
                $services[] = $s->id;
            }
        }
        if(in_array($booking->service_id, $services)) {
            $this->others = true;
        }

        //Acorn
        $sc3 = new OsServiceCategoryModel(3);
        $services = [];
        if($sc3->id && $sc3->services) {
            foreach($sc3->services as $s) {
                $services[] = $s->id;
            }
        }
        if(in_array($booking->service_id, $services)) {
            $this->acorn = true;
        }
    }

    public function saveAgent($model) {
        if($model->is_new_record()) return;

        if($model instanceof OsBookingModel) {
            if($model->agents) {
                $model->save_meta_by_key('extra_agents', $model->agents);

                foreach($model->agents as $id) {
                    $agent = new OsAgentModel($id);

                    if((OsSettingsHelper::get_settings_value('notifications_email') == 'on') &&
                        (OsSettingsHelper::get_settings_value('notification_agent_confirmation') == 'on')) {

                        $agentMailer = new OsAgentMailer();
                        $agentMailer->new_booking_notification($agent, $model);
                    }
                    if(OsSettingsHelper::is_sms_notifications_enabled() &&
                        (OsSettingsHelper::get_settings_value('notification_sms_agent_confirmation') == 'on')) {

                        $agentSmser = new OsAgentSmser();
                        $agentSmser->new_booking_notification($agent, $model);
                    }
                }
            }
        }
    }

    public function outputQuickForm($booking) {
        echo '<div class="os-row">';
        $agents = $booking->get_meta_by_key('extra_agents');
        if($agents) {
            echo '<div class="os-col-12"><h3>' . __('Extra Agents', 'latepoint-conditions') . '</h3></div>';
            $agents = json_decode($agents);
            foreach($agents as $id) {
                $agent = new OsAgentModel($id);
                echo '<div class="os-col-12">' . OsAgentHelper::get_full_name($agent) . '</div>';
            }
        }
        echo '</div>';
    }

    public function confirmationInfoBefore($booking) {
        if($this->covid || $this->others || $this->acorn) 
            echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-body .confirmation-head-info').hide();
});
</script>
EOT;
    }

    public function confirmationInfoAfter($booking) {
        global $wpdb;
        $buttons = json_decode(OsSettingsHelper::get_settings_value('latepoint-button_confirmation', '[]'));
        if($buttons && count($buttons)) {
            foreach($buttons as $button) {
                if($button->referer && $button->referer == wp_get_referer()) {
                    $text = (isset($button->text) && $button->text) ? $button->text : __('Next Step', 'latepoint');
                    echo '<div class="latepoint-footer request-move"><a href="' . $button->link . '"' . ((isset($button->target) && $button->target == '_blank') ? ' target="_blank"' : '') . ' class="latepoint-btn latepoint-btn-primary latepoint-next-btn" data-label="' . $text . '"><span>' . $text . '</span> <i class="latepoint-icon-arrow-2-right"></i></a></div>';
                }
            }
        }
        $db = 'https://dev88.doctorsready.ca:3000/dashboard/';
        if($booking->customer->phone ?? false) {
            $sms = wp_remote_post($db . 'api/gtd/sms', ['method' => 'POST', 'body' => ['phone' => $booking->customer->phone]]);
        }
        if($this->covid || $this->others || $this->acorn || $booking->service_id == 10) {
            $ref = '';
            if($booking->type_id) {
                $referralType = $wpdb->get_row("SELECT * from wp_referral_type where id = {$booking->type_id}");
                $ref = $referralType->type_name . '[' . $referralType->type_registration_form_url . ']';
            }

            $extra = [
                'pname' => $booking->get_meta_by_key('cf_zDS7LUjv', ''),
                'registered' => $booking->get_meta_by_key('cf_x18jr0Vf', ''),
                'datetime' => "{$booking->nice_start_time} - {$booking->nice_end_time} ({$booking->nice_start_date})",
                'phone' => $booking->customer ? $booking->customer->phone : '',
                'home_phone' => $booking->customer->get_meta_by_key('cf_eh0ZhQ9s', ''),
                'type' => $booking->service ? $booking->service->name : '',
                'reply_by' => $booking->customer ? $booking->customer->get_meta_by_key('cf_nxwjDAcZ', '') : '',
                'doctor_preference' => $booking->customer ? $booking->customer->get_meta_by_key('cf_7Lkik5fd', '') : '',
                'referral' => $ref,
            ];
            $invoiceType = 'Appointment';
            $bodyExtra = $merge = [];
            if($this->covid) {
                $returnUrl = site_url('thank-you-covid-19-testing');
                $merge = [
                    'location' => $booking->customer ? $booking->customer->get_meta_by_key('cf_DWcgeHQB', '') : '',
                    'redirect_paid' => site_url('thank-you-covid-19-testing-payment-made'),
                ];
                $invoiceType = 'Covid Test';
            }
            if($this->others) {
                $returnUrl = site_url('thank-you-booking-a-virtual-healthcare-appointment');
                $merge = [
                    'type' => 'Private Pay - Virtual Healthcare Appointment',
                    'location' => 'Others',
                    'redirect_paid' => site_url('thank-you-booking-a-virtual-healthcare-appointment-and-payment-has-already-been-made'),
                ];
            }
            if($this->acorn) {
                $returnUrl = site_url('thank-you/?t=' . ($booking->service ? $booking->service->name : ''));
                $invoiceType = 'Acorn';
                $merge = [
                    'type' => 'Acorn Live Cell Banking',
                    'location' => $booking->customer ? $booking->customer->get_meta_by_key('cf_DWcgeHQB', '') : '',
                    'redirect_paid' => site_url('thank-you-booking-a-virtual-healthcare-appointment-and-payment-has-already-been-made'),
                    'tax_name' => 'HST',
                    'tax_country' => 'CA',
                    'tax_state' => 'ON',
                ];
                $bodyExtra = ['need_tax' => 1];
            }
            if($booking->service_id == 10) {
                $service = ($booking->service ? $booking->service->name : '');
                $returnUrl = site_url('thank-you/?t=' . $service);
                $invoiceType = $service;
                $merge = [
                    'type' => $service,
                    'location' => $booking->customer ? $booking->customer->get_meta_by_key('cf_DWcgeHQB', '') : '',
                    'redirect_paid' => site_url('thank-you-payment-has-already-been-made/?t=' . $service),
                ];
            }
            if($merge) {
                $extra = array_merge($extra, $merge);
            }
            $data = [
                'method' => 'POST',
                'body' => [
                    'invoice' => 1,
                    'email' => $booking->customer ? $booking->customer->email : '',
                    'first_name' => $booking->get_meta_by_key('cf_hbCNgimu', ''),
                    'message' => $booking->get_meta_by_key('cf_H7MIk6Kt', null),
                    'invoice_type' => $invoiceType,
                    'amount' => $booking->service ? $booking->service->charge_amount : '',
                    'currency' => 'cad',
                    'referral' => 'latepoint_' . ($booking->id ?: ''),
                    'return_url' => $returnUrl,
                    'extra' => $extra,
                ] + $bodyExtra
            ];
            $payment = wp_remote_post($db . 'api/payment/create', $data);

            if($payment) {
                $res = json_decode(wp_remote_retrieve_body($payment));
                if($res->data ?? false)
                    echo '<div class="latepoint-footer request-move"><a href="' . $res->data->payment_link . '" class="latepoint-btn latepoint-btn-primary latepoint-next-btn" data-label="Make Payment" style="width: auto"><span>Make Payment</span> <i class="latepoint-icon-arrow-2-right"></i></a></div>';
            }
        }
    }

    public function adminScripts() {
        $jsFolder = plugin_dir_url( __FILE__ ) . 'public/js/';
        wp_enqueue_script('latepoint-conditions',  $jsFolder . 'admin.js', array('jquery'), $this->version);
    }

    public function frontScripts() {
        $jsFolder = plugin_dir_url( __FILE__ ) . 'public/js/';
        wp_enqueue_script('latepoint-conditions',  $jsFolder . 'front.js', array('jquery'), $this->version);
    }

    public function registerAddon($installedAddons) {
        $installedAddons[] = ['name' => $this->addonName, 'db_version' => $this->dbVersion, 'version' => $this->version];
        return $installedAddons;
    }

    public function addMenu($menus) {
        if(!OsAuthHelper::is_admin_logged_in()) return $menus;
        $menus[] = ['id' => 'condition_filter', 'label' => __('Conditions', 'latepoint-extend'), 'icon' => 'latepoint-icon latepoint-icon-layers', 'link' => OsRouterHelper::build_link(['conditions', 'index'])];
        return $menus;
    }

    public function addNextBtn($rules, $step) {
        $buttons = json_decode(OsSettingsHelper::get_settings_value('latepoint-button_confirmation', '[]'));
        foreach($buttons as $button) {
            if($button->referer && $button->referer == wp_get_referer()) {
                $rules['confirmation'] = true;
            }
        }
        return $rules;
    }

    public function onDeactivate() {
    }

    public function onActivate() {
        if(class_exists('OsDatabaseHelper')) OsDatabaseHelper::check_db_version_for_addons();
    }
}
endif;

if(in_array('latepoint/latepoint.php', get_option('active_plugins', [])) || array_key_exists('latepoint/latepoint.php', get_site_option('active_sitewide_plugins', []))) {
    $LATEPOINTEXT = new LatePointExt();
}
