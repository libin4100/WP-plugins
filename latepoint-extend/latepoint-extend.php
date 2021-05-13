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

    public function __construct() {
        $this->defines();
        $this->hooks();
    }

    public function defines() {
    }

    public function hooks() {
        add_action('latepoint_includes', [$this, 'includes']);
        add_action('latepoint_load_step', [$this, 'loadStep'], 5, 3);
        add_action('latepoint_process_step', [$this, 'processStep'], 10, 2);
        add_action('latepoint_admin_enqueue_scripts', [$this, 'adminScripts']);
        add_action('latepoint_wp_enqueue_scripts', [$this, 'frontScripts']);
        add_action('latepoint_model_save', [$this, 'saveAgent']);
        add_action('latepoint_booking_quick_edit_form_after',[$this, 'outputQuickForm']);
        add_action('latepoint_step_confirmation_head_info_before',[$this, 'confirmationInfoBefore']);
        add_action('latepoint_step_confirmation_before',[$this, 'confirmationInfoAfter']);
        add_action('latepoint_booking_steps_contact_after', [$this, 'contactCovid'], 5);

        add_filter('latepoint_installed_addons', [$this, 'registerAddon']);
        add_filter('latepoint_side_menu', [$this, 'addMenu']);
        add_filter('latepoint_step_show_next_btn_rules', [$this, 'addNextBtn'], 10, 2);

        register_activation_hook(__FILE__, [$this, 'onActivate']);
        register_deactivation_hook(__FILE__, [$this, 'onDeactivate']);
    }

    public function includes() {
        include_once(dirname( __FILE__ ) . '/lib/controllers/conditions_controller.php');
    }

    public function contactCovid($customer)
    {
        if(!$this->covid) return;

        $custom_fields_for_customer = OsCustomFieldsHelper::get_custom_fields_arr('customer', 'all');
        if(isset($custom_fields_for_customer) && !empty($custom_fields_for_customer)){
            foreach($custom_fields_for_customer as $custom_field){
                $required_class = ($custom_field['required'] == 'on') ? 'required' : '';
                if($custom_field['label'] == 'Doctor Preference') continue;

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
        //Covid-19
        $sc = new OsServiceCategoryModel(1);
        $services = [];
        if($sc->services) {
            foreach($sc->services as $s) {
                $services[] = $s->id;
            }
        }
        if(in_array($bookingObject->service_id, $services)) {
            $this->covid = true;
        }

        if($stepName == 'contact') {
            if(OsSettingsHelper::get_settings_value('latepoint-disabled_customer_login'))
                OsAuthHelper::logout_customer();
        }
        if($stepName == 'custom_fields_for_booking') {
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
        }
        if($stepName == 'confirmation') {
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
    }

    public function confirmationInfoAfter($booking) {
        $buttons = json_decode(OsSettingsHelper::get_settings_value('latepoint-button_confirmation', '[]'));
        if($buttons && count($buttons)) {
            foreach($buttons as $button) {
                if($button->referer && $button->referer == wp_get_referer()) {
                    $text = (isset($button->text) && $button->text) ? $button->text : __('Next Step', 'latepoint');
                    echo '<div class="latepoint-footer request-move"><a href="' . $button->link . '"' . ((isset($button->target) && $button->target == '_blank') ? ' target="_blank"' : '') . ' class="latepoint-btn latepoint-btn-primary latepoint-next-btn" data-label="' . $text . '"><span>' . $text . '</span> <i class="latepoint-icon-arrow-2-right"></i></a></div>';
                }
            }
        }
        if($this->covid) {
            $db = 'https://dev88.doctorsready.ca:3000/dashboard/';
            $data = [
                'method' => 'POST',
                'body' => [
                    'invoice' => 1,
                    'email' => $booking->customer ? $booking->customer->email : '',
                    'invoice_type' => 'Covid Test',
                    'message' => $booking->service ? $booking->service->name : '',
                    'amount' => $booking->service ? $booking->service->charge_amount : '',
                    'currency' => 'cad',
                    'referral' => 'covid_' . ($booking->id ?: ''),
                ]
            ];
            $payment = wp_remote_post($db . 'api/payment/create', $data);

            if($payment) {
                $res = json_decode(wp_remote_retrieve_body($payment));
                if($res->data ?? false)
                    echo '<div class="latepoint-footer request-move"><a href="' . $db . 'checkout/' . $res->data->id . '" target="_blank" class="latepoint-btn latepoint-btn-primary latepoint-next-btn" data-label="Checkout"><span>Checkout</span> <i class="latepoint-icon-arrow-2-right"></i></a></div>';
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
