<?php

/**
 * Plugin Name: Latepoint Addon - Conditions
 * Plugin URI: http://www.mywebsite.com/my-first-plugin
 * Description: Latepoint extension for custom setting
 * Version: 1.0
 * Author: Your Name
 * Author URI: http://www.mywebsite.com
 */

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


if (!class_exists('LatePointExt')) :
    /**
     * Main Class.
     *
     */
    final class LatePointExt
    {
        public $version = '1.3.14';
        public $dbVersion = '1.0.0';
        public $addonName = 'latepoint-extend';

        protected $covid;
        protected $others;
        protected $acorn;

        protected $cFields = [
            'reason' => 'cf_E6XolZDI'
        ];

        public function __construct()
        {
            $this->defines();
            $this->hooks();
        }

        public function defines()
        {
        }

        public function hooks()
        {
            add_action('wp_loaded', [$this, 'route']);
            add_action('wp_ajax_nopriv_check_certificate', [$this, 'checkCertificateSession']);
            add_action('wp_ajax_check_certificate', [$this, 'checkCertificate']);
            add_action('wp_ajax_nopriv_check_certificate_sb', [$this, 'checkCertificateSessionSB']);
            add_action('wp_ajax_check_certificate_sb', [$this, 'checkCertificateSB']);
            add_action('wp_ajax_nopriv_check_certificate_qh', [$this, 'checkCertificateSessionQH']);
            add_action('wp_ajax_check_certificate_qh', [$this, 'checkCertificateQH']);
            add_action('wp_ajax_nopriv_check_certificate_p', [$this, 'checkCertificateSessionP']);
            add_action('wp_ajax_check_certificate_p', [$this, 'checkCertificateP']);
            add_action('wp_ajax_nopriv_check_certificate_aas', [$this, 'checkCertificateSessionAAS']);
            add_action('wp_ajax_check_certificate_aas', [$this, 'checkCertificateAAS']);
            add_action('latepoint_includes', [$this, 'includes']);
            add_action('latepoint_load_step', [$this, 'loadStep'], 5, 3);
            add_action('latepoint_process_step', [$this, 'processStep'], 5, 2);
            add_action('latepoint_admin_enqueue_scripts', [$this, 'adminScripts']);
            add_action('wp_enqueue_scripts', [$this, 'frontScripts']);
            add_action('latepoint_model_save', [$this, 'saveModel']);
            add_action('latepoint_booking_quick_edit_form_after', [$this, 'outputQuickForm']);
            add_action('latepoint_step_confirmation_head_info_before', [$this, 'confirmationInfoBefore']);
            add_action('latepoint_step_confirmation_before', [$this, 'confirmationInfoAfter']);
            add_action('latepoint_booking_steps_contact_after', [$this, 'contactAfter'], 5);
            add_action('latepoint_booking_created_frontend', [$this, 'bookingCreated']);
            add_action('latepoint_steps_side_panel_after', [$this, 'sidePanel']);
            add_action('latepoint_model_set_data', [$this, 'setModelData'], 5, 2);
            //Add action to save ajax file upload
            add_action('wp_ajax_nopriv_latepoint_file_upload', [$this, 'fileUpload']);
            add_action('wp_ajax_latepoint_file_upload', [$this, 'fileUpload']);
            //Add action to delete file
            add_action('wp_ajax_nopriv_latepoint_ext_file_delete_unshaken1869', [$this, 'fileDelete']);
            add_action('wp_ajax_latepoint_ext_file_delete_unshaken1869', [$this, 'fileDelete']);

            add_filter('latepoint_installed_addons', [$this, 'registerAddon']);
            add_filter('latepoint_side_menu', [$this, 'addMenu']);
            add_filter('latepoint_step_show_next_btn_rules', [$this, 'addNextBtn'], 10, 2);
            add_filter('latepoint_summary_values', [$this, 'summaryValues']);
            add_filter('latepoint_steps_defaults', [$this, 'steps']);
            add_filter('gettext', [$this, 'gettext'], 10, 3);
            add_filter('latepoint_replace_booking_vars', [$this, 'replace'], 20, 2);
            add_filter('latepoint_customer_model_validations', [$this, 'customerFilter']);
            add_filter('latepoint_step_names_in_order', [$this, 'stepNames'], 2, 2);
            add_filter('latepoint_should_step_be_skipped', [$this, 'beSkipped'], 20, 3);

            register_activation_hook(__FILE__, [$this, 'onActivate']);
            register_deactivation_hook(__FILE__, [$this, 'onDeactivate']);
        }

        public function gettext($translation, $text, $domain)
        {
            if ($domain == 'latepoint') {
                switch ($translation) {
                    case 'Location':
                        $translation = 'Health Card';
                        break;
                    case 'Your Email Address':
                        if ((OsStepsHelper::$booking_object->agent_id ?? null) == 2) {
                            $translation = 'Email Address';
                        } else {
                            $translation = 'Contact Email Address';
                        }
                        break;
                }
            }
            return $translation;
        }

        public function route()
        {
            $routeName = OsRouterHelper::get_request_param('route_name', '');
            switch ($routeName) {
                case 'resend_latepoint':
                    $id = OsRouterHelper::get_request_param('id', '');
                    if ($id) {
                        $booking = new OsBookingModel($id);
                        OsNotificationsHelper::send_agent_new_appointment_notification($booking);
                    }
                    break;
            }
        }

        public function checkCertificateSession()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificate();
        }

        public function checkCertificate()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCert($id)) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Manitoba Blue Cross at <nobr>1-888-596-1032</nobr> to confirm eligibility. For any technical issues, please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function checkCertificateSessionSB()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateSB();
        }

        public function checkCertificateSB()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertSB($id)) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Simply Benefits at <nobr>1-877-815-7751</nobr> or support@simplybenefits.ca to confirm eligibility. For any technical issues, please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function checkCertificateSessionQH()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateQH();
        }

        public function checkCertificateQH()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertQH($id)) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Quick Health Access at <nobr>1-800-789-8036</nobr> ext. 703 or paulina@quickhealthaccess.ca to confirm eligibility. For any technical issues, please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function checkCertificateSessionP()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateP();
        }

        public function checkCertificateP()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertP($id)) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function checkCertificateSessionAAS()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateAAS();
        }

        public function checkCertificateAAS()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertAAS($id)) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function includes()
        {
            include_once(dirname(__FILE__) . '/lib/controllers/conditions_controller.php');
        }

        public function bookingCreated($booking)
        {
            $this->_covid($booking);

            if ($this->covid || $this->others || $this->acorn || $booking->service_id == 10) {
                OsSettingsHelper::$loaded_values['notifications_email'] = 'off';
            }
            if ($booking->agent_id == 6) {
                OsSettingsHelper::$loaded_values['notifications_email'] = 'off';
            }
            if (!in_array($booking->type_id, [5, 11])) {
                OsSettingsHelper::$loaded_values['notification_customer_confirmation'] = 'off';
            }
        }

        public function sidePanel($stepName)
        {
            global $wpdb;

            $this->_covid(OsStepsHelper::$booking_object);

            if ($this->covid || $this->others || $this->acorn) {
                $url = site_url('wp-content/uploads/2021/05/icon1x.png');
                echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').text("Please proceed with payment by clicking 'Make Payment' button. Our team will contact you shortly to confirm your appointment. If you do not get a response within 24 hours, please call us to confirm your appointment. *If this is an emergency, please go to the nearest hospital or call 911.*");
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-media').css("background-image", 'url({$url})');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text('Payment');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text-library[data-step-name="confirmation"]').text('Appointment Information');
    {$style}
});
    delete is_rapid;
</script>
EOT;
            }
            if (OsStepsHelper::$booking_object->service_id == 10) {
                $desc = __('Thank you for choosing Gotodoctor as your Virtual Healthcare provider. Please proceed to make payment and check your email for further instructions. *If this is an emergency, go to the nearest hospital or call 911.*<br /><br /><font color="red">DO NOT COME IN, until you receive YOUR SPECIFIC appointment time.</font>', 'latepoint-extand-master');
                $title = __('Your appointment request was received', 'latepoint-extand-master');
                $head = __('Appointment Request', 'latepoint-extand-master');
                echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').html('{$desc}');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text('{$title}');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text-library[data-step-name="confirmation"]').text('{$head}');
});
</script>
EOT;
            }
            if (OsStepsHelper::$booking_object->service_id == 13) {
                echo <<<EOT
<script>
jQuery(function($) {
    $('li[data-step-name="custom_fields_for_booking"] span').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc .latepoint-desc-title').text('Client Details');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text('Request submitted');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').html('Thank you for choosing Gotodoctor.ca. Our team will review your request and contact you within the next 3 business days to collect any additional information required. If you do not hear from us please call us to confirm your request was received.<br><br>* If this is an emergency please go to the nearest hospital or call 911.*');
});
</script>
EOT;
            }
            $str = '';
            $locationSettings = (new OsConditionsController)->getLocationSettings();
            if ($locationSettings) {
                foreach ($locationSettings as $locationSetting) {
                    if (
                        OsStepsHelper::$booking_object->location_id == $locationSetting['location_id'] &&
                        in_array(OsStepsHelper::$booking_object->agent_id, $locationSetting['agents'])
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
                        if ($show)
                            echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-side-panel, .latepoint-summary-w').remove();
    $('.latepoint-lightbox-w .latepoint-lightbox-i').css('width', '540px');
    $('.latepoint-lightbox-w .latepoint-lightbox-i .os-heading-text').text('{$locationSetting['label']}');
});
</script>
EOT;
                    }
                }
            }
            if (OsStepsHelper::$booking_object->location_id == 1) {
                $type_id = 0;
                /*
                if (OsStepsHelper::$booking_object->agent_id == 2) {
                    $referral_tracking_value = $_COOKIE['referral_tracking'];
                    $check_url_type = $wpdb->get_results("SELECT * from wp_referral_info  WHERE `page_opened_session`='" . $referral_tracking_value . "' order by info_id asc limit 1");
                    foreach ($check_url_type as $type_values) {
                        $type_id = $type_values->type_id;
                    }
                }
                */

                //$tmpDisabled = (rtrim(wp_get_referer(), '/') == get_home_url()) ? true : false;
                if ($type_id == 5) {
                    echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-side-panel, .latepoint-summary-w').remove();
    $('.latepoint-lightbox-w .latepoint-lightbox-i').css('width', '540px');
    $('.latepoint-lightbox-w .latepoint-lightbox-i .os-heading-text').text('Update about our services in Ontario');
});
</script>
EOT;
                } else {
                    echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="datepicker"] .latepoint-desc-content').html('');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text('');
});
</script>
EOT;
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
            }
            if ($this->others && OsStepsHelper::$booking_object->agent_id == 8) {
                $str = <<<EOT
ele = $('.latepoint-booking-form-element');
if(!$('#booking_custom_fields_cf_6a3sfget').length || ($('#booking_custom_fields_cf_6a3sfget').val() == 'Quebec')) {
    latepoint_update_summary_field(t, 'price', '0');
} else {
    latepoint_update_summary_field(t, 'price', '$66');
}
EOT;
            }
            echo <<<EOT
<script>
jQuery(function($) {
    setInterval(function() {
        $str
        if($('#customer_custom_fields_cf_eh0zhq9s.init').length) {
            $('#customer_custom_fields_cf_eh0zhq9s').removeClass('init')
            $('#customer_custom_fields_cf_rglgzjat').removeClass('init')
            $('#customer_phone').parents('.os-col-sm-12').after($('#customer_custom_fields_cf_rglgzjat').parents('.os-col-12'))
            $('#customer_phone').parents('.os-col-sm-12').after($('#customer_custom_fields_cf_eh0zhq9s').parents('.os-col-12'))
        }

        $('#customer_phone').change(function() {
            $('#customer_custom_fields_cf_rglgzjat').val($('#customer_phone').val()).parent('div.os-form-group').addClass('has-value');
        });
    }, 500);
});
</script>
EOT;
        }

        public function contactAfter($customer)
        {
            $custom_fields_for_customer = OsCustomFieldsHelper::get_custom_fields_arr('customer', 'customer');
            if (isset($custom_fields_for_customer) && !empty($custom_fields_for_customer)) {
                foreach ($custom_fields_for_customer as $custom_field) {
                    $required_class = ($custom_field['required'] == 'on') ? 'required' : '';
                    if ($this->covid || $this->acorn) {
                        if ($custom_field['id'] == 'cf_7Lkik5fd') continue;
                    }
                    if (in_array($custom_field['id'], ['cf_eh0ZhQ9s', 'cf_rgLGzjat'])) $required_class .= ' os-mask-phone init';

                    switch ($custom_field['type']) {
                        case 'text':
                            echo OsFormHelper::text_field('customer[custom_fields][' . $custom_field['id'] . ']', $custom_field['label'], $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
                            break;
                        case 'textarea':
                            echo OsFormHelper::textarea_field('customer[custom_fields][' . $custom_field['id'] . ']', $custom_field['label'], $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
                            break;
                        case 'select':
                            echo OsFormHelper::select_field('customer[custom_fields][' . $custom_field['id'] . ']', $custom_field['label'], OsFormHelper::generate_select_options_from_custom_field($custom_field['options']), $customer->get_meta_by_key($custom_field['id'], ''), ['class' => $required_class, 'placeholder' => $custom_field['placeholder']], array('class' => $custom_field['width']));
                            break;
                        case 'checkbox':
                            echo OsFormHelper::checkbox_field('customer[custom_fields][' . $custom_field['id'] . ']', $custom_field['label'], __('on', 'latepoint-extand-master'), ($customer->get_meta_by_key($custom_field['id'], __('off', 'latepoint-extand-master')) == __('on', 'latepoint-extand-master')), ['class' => $required_class], array('class' => $custom_field['width']), __('off', 'latepoint-extand-master'));
                            break;
                    }
                }
            }

            $booking = OsParamsHelper::get_param('booking');

            remove_all_actions('latepoint_booking_steps_contact_after');
        }

        public function loadStep($stepName, $bookingObject, $format = 'json')
        {
            global $wpdb;
            $this->_covid($bookingObject);
            if ($this->covid || $bookingObject->service_id == 10)
                $this->_fields('covid');
            elseif ($bookingObject->agent_id == 6) {
                //MB Blue Cross
                $fields = $this->_fields('mbc');
            } elseif ($bookingObject->agent_id == 7) {
                //Simply Benefits
                $fields = $this->_fields('sb');
            } elseif ($bookingObject->agent_id == 8) {
                //Quick health access
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('qhc');
                else
                    $fields = $this->_fields('qh');
            } elseif ($bookingObject->agent_id == 9) {
                //AAS
                $fields = $this->_fields('aas');
            } elseif ($bookingObject->agent_id == 10) {
                //Partners
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('pc');
                else
                    $fields = $this->_fields('p');
            } elseif (in_array($bookingObject->service_id, [2, 3]))
                $this->_fields('located');
            elseif (in_array($bookingObject->service_id, [7, 8]))
                $this->_fields('locatedOther');
            else
                $fields = $this->_fields('', true);

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
                    //returning patient
                    $booking = OsParamsHelper::get_param('booking');
                    $custom_fields_data = $booking['custom_fields'];
                    if (($custom_fields_data['cf_x18jr0Vf'] ?? false) == 'Yes') {
                        $customFields = OsSettingsHelper::get_settings_value('custom_fields_for_customer', false);
                        $values = json_decode($customFields, true);
                        if ($values) {
                            foreach ($values as $id => $val) {
                                if (in_array($val['label'], ["Reason for today's visit ( required )", "Other Reason ( required )"]))
                                    $values[$id]['visibility'] = 'public';
                            }
                            OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
                        }
                    }
                    break;
                case 'custom_fields_for_booking':
                    $_SESSION['certCount'] = 0;

                    if (OsSettingsHelper::get_settings_value('latepoint-disabled_customer_login'))
                        OsAuthHelper::logout_customer();
                    if (OsSettingsHelper::get_settings_value('latepoint-allow_shortcode_custom_fields')) {
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
                            'is_pre_last_step'  => OsStepsHelper::is_pre_last_step($stepName)
                        ]);
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

                    //Steps for QHA Care Navigation
                case 'qhc_service':
                    if ($bookingObject->service_id == 13) {
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
            }
        }

        public function processStep($stepName, $bookingObject)
        {
            $this->_covid($bookingObject);
            if ($this->covid || $bookingObject->service_id == 10)
                $this->_fields('covid');
            elseif ($bookingObject->agent_id == 6) {
                //MB Blue Cross
                $fields = $this->_fields('mbc');
            } elseif ($bookingObject->agent_id == 7) {
                //Simply Benefits
                $fields = $this->_fields('sb');
            } elseif ($bookingObject->agent_id == 8) {
                //Quick health access
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('qhc');
                else
                    $fields = $this->_fields('qh');
            } elseif ($bookingObject->agent_id == 9) {
                //AAS
                $fields = $this->_fields('aas');
            } elseif ($bookingObject->agent_id == 10) {
                //Partners
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('pc');
                else
                    $fields = $this->_fields('p');
            } elseif (in_array($bookingObject->service_id, [2, 3]))
                $this->_fields('located');
            elseif (in_array($bookingObject->service_id, [7, 8]))
                $this->_fields('locatedOther');
            else
                $fields = $this->_fields('', true);

            switch ($stepName) {
                case 'custom_fields_for_booking':
                    $this->_timezone($bookingObject);

                    $booking = OsParamsHelper::get_param('booking');
                    $custom_fields_data = $booking['custom_fields'];
                    $custom_fields_for_booking = OsCustomFieldsHelper::get_custom_fields_arr('booking', 'customer');

                    $is_valid = true;
                    $fields = ['cf_GiVH6tot', 'cf_7MZNhPC6', 'cf_4aFGjt5V'];
                    $errors = [];
                    $first = true;
                    $errors = OsCustomFieldsHelper::validate_fields($custom_fields_data, $custom_fields_for_booking);
                    foreach ($custom_fields_for_booking as $k => $f) {
                        if ($this->covid || $bookingObject->service_id == 10) {
                            if (in_array(trim($f['id']), $fields) && (strtolower($custom_fields_data[$k]) != 'no')) {
                                if ($first) {
                                    $errors[] = ['type' => 'validation', 'message' => 'You do not pass the screening and cannot proceed with the booking.'];
                                    $first = false;
                                }
                            } elseif ($f['visibility'] == 'public' && $f['required'] == 'on' && !(trim($custom_fields_data[$k]))) {
                                $errors[] = ['type' => 'validation', 'message' => 'You do not pass the screening and cannot proceed with the booking.'];
                            }
                        }
                        if ($bookingObject->agent_id == 6 && $k == 'cf_qOqKhbly') {
                            if (!$this->checkCert($custom_fields_data[$k] ?? '')) {
                                $msg = 'Certificate number does not match our records. Please try again.';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                        if ($bookingObject->agent_id == 7 && $k == 'cf_Vin78Day') {
                            if (!$this->checkCertSB($custom_fields_data[$k] ?? '')) {
                                $msg = 'Certificate number does not match our records. Please try again.';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                        if ($bookingObject->agent_id == 8 && $k == 'cf_SIt7Zefo') {
                            if (!$this->checkCertQH($custom_fields_data[$k] ?? '')) {
                                $msg = 'Certificate number does not match our records. Please try again.';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                        if ($bookingObject->agent_id == 10 && $k == 'cf_SIt7Zefp') {
                            if (!$this->checkCertP($custom_fields_data[$k] ?? '')) {
                                $msg = 'Certificate number does not match our records. Please try again.';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                        if ($bookingObject->agent_id == 9 && $k == 'cf_WzbhG9eB') {
                            if (!$this->checkCertAAS($custom_fields_data[$k] ?? '')) {
                                $msg = 'Certificate number does not match our records. Please try again.';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                    }
                    $error_messages = [];
                    if ($errors) {
                        $is_valid = false;
                        $err = '';
                        foreach ($errors as $error) {
                            $err .= $error['message'] . '.<br>';
                        }
                        $error_messages[] = substr($err, 0, -4);
                    }
                    if (!$is_valid) {
                        remove_all_actions('latepoint_process_step');
                        wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => $error_messages));
                        return;
                    }

                    break;
                case 'contact':
                    if ($bookingObject->service_id == 10) {
                        $booking = OsParamsHelper::get_param('customer');
                        $data = $booking['custom_fields']['cf_DV0y9heS'] ?? false;
                        if (!$data || $data != 'on') {
                            remove_all_actions('latepoint_process_step');
                            wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['You need to read and accepte the consent acknowledgment to book the appointment.']));
                            return;
                        }
                    }

                    if ($bookingObject->location_id == 1 && $bookingObject->agent_id == 2) {
                        $booking = OsParamsHelper::get_param('customer');
                        if ((($booking['custom_fields']['cf_4zkIbeeY'] ?? false) == 'Other') && !($booking['custom_fields']['cf_NVByvyYw'] ?? false)) {
                            remove_all_actions('latepoint_process_step');
                            wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['Other Reason ( required ) can not be blank']));
                            return;
                        }
                    }
                    break;

                case 'qhc_service':
                    break;
                case 'qhc_contact':
                    break;
                case 'qhc_additional':
                    $booking = OsParamsHelper::get_param('booking');
                    $qhc = $booking['custom_fields'];
                    $customer_params = [
                        'first_name' => $qhc['first_name'],
                        'last_name' => $qhc['last_name'],
                        'email' => $qhc['email'],
                        'phone' => $qhc['phone'],
                    ];
                    $customer = new OsCustomerModel();
                    $check = $customer->where(['email' => $customer_params['email']])->get_results_as_models();
                    if ($check) {
                        $customer = $check[0];
                    }
                    $customer->set_data($customer_params);
                    if ($customer->save()) {
                        OsAuthHelper::authorize_customer($customer->id);
                        OsStepsHelper::$booking_object->customer_id = $customer->id;
                    }
                    break;
            }
        }

        protected function _covid($booking)
        {
            //Covid-19
            $sc = new OsServiceCategoryModel(1);
            $services = [];
            if ($sc->id && $sc->services) {
                foreach ($sc->services as $s) {
                    $services[] = $s->id;
                }
            }
            if (in_array($booking->service_id, $services)) {
                $this->covid = true;
            }

            //Others
            $sc2 = new OsServiceCategoryModel(2);
            $services = [];
            if ($sc2->id && $sc2->services) {
                foreach ($sc2->services as $s) {
                    $services[] = $s->id;
                }
            }
            if (in_array($booking->service_id, $services)) {
                $this->others = true;
            }

            //Acorn
            $sc3 = new OsServiceCategoryModel(3);
            $services = [];
            if ($sc3->id && $sc3->services) {
                foreach ($sc3->services as $s) {
                    $services[] = $s->id;
                }
            }
            if (in_array($booking->service_id, $services)) {
                $this->acorn = true;
            }
        }

        public function setModelData($model, $data = [])
        {
            if ($data && is_array($data)) {
                if ($data['at_clinic'] ?? false) {
                    $model->at_clinic = 1;
                }

                if ($data['custom_fields'][$this->cFields['reason']] ?? false) {
                    $model->cf_reason = $data['custom_fields'][$this->cFields['reason']];
                }
            }
            if (($model instanceof OsBookingModel)) {
                $custom_fields_structure = OsCustomFieldsHelper::get_custom_fields_arr('booking', 'all');
                if (!isset($model->custom_fields)) $model->custom_fields = [];
                foreach (($data['custom_fields'] ?? []) as $key => $custom_field) {
                    if ($custom_fields_structure[$key] ?? false)
                        $model->custom_fields[$key] = $custom_field;
                }
                if ($data['custom_fields']['first_name'] ?? false) {
                    $model->custom_fields['first_name'] = $data['custom_fields']['first_name'];
                    $model->custom_fields['last_name'] = $data['custom_fields']['last_name'];
                    $model->cname = $data['custom_fields']['first_name'] . ' ' . ($data['custom_fields']['last_name'] ?? '');
                    $model->pname = '';
                }
                if ($data['custom_fields']['email'] ?? false) {
                    $model->custom_fields['email'] = $data['custom_fields']['email'];
                }
                if ($data['custom_fields']['phone'] ?? false) {
                    $model->custom_fields['phone'] = $data['custom_fields']['phone'];
                }
                $booking = OsParamsHelper::get_param('customer');
                if ($booking['custom_fields']['cf_4zkIbeeY'] ?? false) {
                    $model->visit_reason = $booking['custom_fields']['cf_4zkIbeeY'];
                    if ($booking['custom_fields']['cf_4zkIbeeY'] == 'Other' && ($booking['custom_fields']['cf_NVByvyYw'] ?? false)) {
                        $model->visit_reason .= ' (' . $booking['custom_fields']['cf_NVByvyYw'] . ')';
                    }
                }

                if ($qhc = $data['qhc'] ?? false) {
                    $model->qhc = json_encode($qhc);
                }
            }
        }

        public function saveModel($model)
        {
            if ($model->is_new_record()) return;

            if ($model->visit_reason ?? false) {
                $model->save_meta_by_key('cf_4zkIbeeY', $model->visit_reason);
            }
            if ($model instanceof OsBookingModel) {
                if ($model->at_clinic) {
                    $model->save_meta_by_key('at_clinic', 1);
                }
                if ($model->cf_reason) {
                    $model->save_meta_by_key($this->cFields['reason'], $model->cf_reason);
                }
                if ($model->cname) {
                    $model->save_meta_by_key('cf_hbCNgimu', $model->cname);
                }
                if ($model->pname !== null) {
                    $model->save_meta_by_key('cf_zDS7LUjv', $model->pname);
                }
                if (defined('WPLANG')) {
                    $model->save_meta_by_key('language', WPLANG);
                }
                if ($model->agents) {
                    $model->save_meta_by_key('extra_agents', $model->agents);

                    foreach ($model->agents as $id) {
                        $agent = new OsAgentModel($id);

                        if ((OsSettingsHelper::get_settings_value('notifications_email') == 'on') &&
                            (OsSettingsHelper::get_settings_value('notification_agent_confirmation') == 'on')
                        ) {

                            $agentMailer = new OsAgentMailer();
                            $agentMailer->new_booking_notification($agent, $model);
                        }
                        if (
                            OsSettingsHelper::is_sms_notifications_enabled() &&
                            (OsSettingsHelper::get_settings_value('notification_sms_agent_confirmation') == 'on')
                        ) {

                            $agentSmser = new OsAgentSmser();
                            $agentSmser->new_booking_notification($agent, $model);
                        }
                    }
                }

                if ($model->qhc) {
                    $model->save_meta_by_key('qhc', $model->qhc);
                }
            }
        }

        public function outputQuickForm($booking)
        {
            echo '<div class="os-row">';
            $agents = $booking->get_meta_by_key('extra_agents');
            if ($agents) {
                echo '<div class="os-col-12"><h3>' . __('Extra Agents', 'latepoint-extand-master') . '</h3></div>';
                $agents = json_decode($agents);
                foreach ($agents as $id) {
                    $agent = new OsAgentModel($id);
                    echo '<div class="os-col-12">' . OsAgentHelper::get_full_name($agent) . '</div>';
                }
            }
            echo '</div>';
        }

        public function confirmationInfoBefore($booking)
        {
            if ($this->covid || $this->others || $this->acorn)
                echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-body .confirmation-head-info').hide();
});
</script>
EOT;
            if ($booking->location_id == 1 || $booking->service_id == 13)
                echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-body .confirmation-app-info ul li:first-child + li').remove();
    $('.latepoint-body .confirmation-app-info ul li:first-child').remove();
});
</script>
EOT;
        }

        public function confirmationInfoAfter($booking)
        {
            global $wpdb;
            $buttons = json_decode(OsSettingsHelper::get_settings_value('latepoint-button_confirmation', '[]'));
            if ($buttons && count($buttons)) {
                foreach ($buttons as $button) {
                    if ($button->referer && $button->referer == wp_get_referer()) {
                        $text = (isset($button->text) && $button->text) ? $button->text : __('Next Step', 'latepoint');
                        echo '<div class="latepoint-footer request-move"><a href="' . $button->link . '"' . ((isset($button->target) && $button->target == '_blank') ? ' target="_blank"' : '') . ' class="latepoint-btn latepoint-btn-primary latepoint-next-btn" data-label="' . $text . '"><span>' . $text . '</span> <i class="latepoint-icon-arrow-2-right"></i></a></div>';
                    }
                }
            }
            if (file_exists(__DIR__ . '/config.php')) require_once(__DIR__ . '/config.php');
            !isset($db) && $db = 'https://teledact.ca/';
            /*
            if ((!in_array($booking->service_id, [10, 13])) && ($booking->customer->phone ?? false)) {
                $body = ['phone' => $booking->customer->phone];
                if (defined('WPLANG')) $body['lang'] = WPLANG;
                $sms = wp_remote_post($db . 'api/gtd/sms', ['method' => 'POST', 'body' => $body]);
            }
            */
            if ($this->covid || $this->others || $this->acorn || $booking->service_id == 10) {
                if ($this->others && ($booking->agent_id == 8) && $booking->get_meta_by_key('cf_6A3SfgET') == 'Quebec')
                    return;

                $ref = '';
                $extraClass = '';
                if ($booking->type_id) {
                    $referralType = $wpdb->get_row("SELECT * from wp_referral_type where id = {$booking->type_id}");
                    $ref = $referralType->type_name . '[' . $referralType->type_registration_form_url . ']';
                }

                $extra = [
                    'pname' => $booking->get_meta_by_key('cf_zDS7LUjv', ''),
                    'registered' => $booking->get_meta_by_key('cf_x18jr0Vf', ''),
                    'datetime' => "{$booking->nice_start_time} - {$booking->nice_end_time} ({$booking->nice_start_date})",
                    'phone' => $booking->customer ? $booking->customer->phone : '',
                    'home_phone' => $booking->customer->get_meta_by_key('cf_eh0ZhQ9s', ''),
                    'preferred_phone' => $booking->customer->get_meta_by_key('cf_rgLGzjat', ''),
                    'type' => $booking->service ? $booking->service->name : '',
                    'reply_by' => $booking->customer ? $booking->customer->get_meta_by_key('cf_nxwjDAcZ', '') : '',
                    'doctor_preference' => $booking->customer ? $booking->customer->get_meta_by_key('cf_7Lkik5fd', '') : '',
                    'referral' => $ref,
                    'lang' => (defined('WPLANG') ? WPLANG : ''),
                ];
                $invoiceType = 'Appointment';
                $bodyExtra = $merge = [];
                if ($this->covid) {
                    $returnUrl = function_exists('pll_get_post') ? get_the_permalink(pll_get_post(get_page_by_path('thank-you-covid-19-testing')->ID)) : site_url('thank-you-covid-19-testing');
                    $merge = [
                        'location' => $booking->customer ? $booking->customer->get_meta_by_key('cf_DWcgeHQB', '') : '',
                        'redirect_paid' => function_exists('pll_get_post') ? get_the_permalink(pll_get_post(get_page_by_path('thank-you-covid-19-testing-payment-made')->ID)) : site_url('thank-you-covid-19-testing-payment-made'),
                    ];
                    $invoiceType = 'Covid Test';
                }
                if ($this->others) {
                    $returnUrl = function_exists('pll_get_post') ? get_the_permalink(pll_get_post(get_page_by_path('thank-you-booking-a-virtual-healthcare-appointment')->ID)) : site_url('thank-you-booking-a-virtual-healthcare-appointment');
                    $merge = [
                        'type' => __('Private Pay - Virtual Healthcare Appointment', 'latepoint-extand-master'),
                        'location' => __('Private Pay', 'latepoint-extand-master'),
                        'current_location' => $booking->get_meta_by_key('cf_6A3SfgET', ''),
                        'redirect_paid' => function_exists('pll_get_post') ? get_the_permalink(pll_get_post(get_page_by_path('thank-you-booking-a-virtual-healthcare-appointment-and-payment-has-already-been-made')->ID)) : site_url('thank-you-booking-a-virtual-healthcare-appointment-and-payment-has-already-been-made'),
                    ];
                    if ($booking->agent_id == 6) {
                        $noteOnly = true;
                        $bodyExtra['note_only'] = $noteOnly;
                    }
                }
                if ($this->acorn) {
                    $returnUrl = function_exists('pll_get_post') ? get_the_permalink(pll_get_post(get_page_by_path('thank-you')->ID)) . '?t=' . ($booking->service ? $booking->service->name : '') : site_url('thank-you/?t=' . ($booking->service ? $booking->service->name : ''));
                    $invoiceType = 'Acorn';
                    $merge = [
                        'type' => __('Acorn Live Cell Banking', 'latepoint-extand-master'),
                        'location' => $booking->location ? $booking->location->name : '',
                        'redirect_paid' => function_exists('pll_get_post') ? get_the_permalink(pll_get_post(get_page_by_path('thank-you-booking-a-virtual-healthcare-appointment-and-payment-has-already-been-made')->ID)) : site_url('thank-you-booking-a-virtual-healthcare-appointment-and-payment-has-already-been-made'),
                        'tax_name' => 'HST',
                        'tax_country' => 'CA',
                        'tax_state' => 'ON',
                    ];
                    $bodyExtra = ['need_tax' => 1];
                }
                if ($booking->service_id == 10) {
                    $service = ($booking->service ? $booking->service->name : '');
                    $new = ($booking->get_meta_by_key('cf_x18jr0Vf', '') == 'No') ? true : false;
                    $returnUrl = function_exists('pll_get_post') ? get_the_permalink(pll_get_post(get_page_by_path('thank-you')->ID)) . '?t=' . $service . ($new ? '&n=1' : '') : site_url('thank-you/?t=' . $service . ($new ? '&n=1' : ''));
                    $invoiceType = $service;
                    $merge = [
                        'type' => $service,
                        'location' => $booking->location ? $booking->location->name : '',
                        'new' => $new,
                        'reason' => $booking->get_meta_by_key($this->cFields['reason'], null),
                        'redirect_paid' => site_url('thank-you-payment-has-already-been-made/?t=' . $service),
                    ];
                }
                if ($merge) {
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

                if ($payment && !($noteOnly ?? false)) {
                    $extraClass = ' latepoint-payment';
                    $res = json_decode(wp_remote_retrieve_body($payment));
                    if ($res->data ?? false)
                        echo '<div class="latepoint-footer request-move"><a href="' . $res->data->payment_link . '" class="latepoint-btn latepoint-btn-primary latepoint-next-btn' . $extraClass . '" data-label="' . __('Make Payment', 'latepoint-extand-master') . '" style="width: auto"><span>' . __('Make Payment', 'latepoint-extand-master') . '</span> <i class="latepoint-icon-arrow-2-right"></i></a></div>';
                }
            }
        }

        public function adminScripts()
        {
            $jsFolder = plugin_dir_url(__FILE__) . 'public/js/';
            wp_enqueue_script('latepoint-conditions',  $jsFolder . 'admin.js', array('jquery'), $this->version);
        }

        public function frontScripts()
        {
            $jsFolder = plugin_dir_url(__FILE__) . 'public/js/';
            wp_enqueue_script('ajax-script',  $jsFolder . 'front.js', array('jquery'), $this->version);
            wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_enqueue_style('latepoint-conditions',  plugin_dir_url(__FILE__) . 'public/css/' . 'front.css', false, $this->version);
        }

        public function registerAddon($installedAddons)
        {
            $installedAddons[] = ['name' => $this->addonName, 'db_version' => $this->dbVersion, 'version' => $this->version];
            return $installedAddons;
        }

        public function addMenu($menus)
        {
            if (!OsAuthHelper::is_admin_logged_in()) return $menus;
            $menus[] = ['id' => 'location_switch', 'label' => __('Location Setting', 'latepoint-extand-master'), 'icon' => 'latepoint-icon latepoint-icon-layers', 'link' => OsRouterHelper::build_link(['conditions', 'location'])];
            $menus[] = ['id' => 'condition_filter', 'label' => __('Conditions', 'latepoint-extand-master'), 'icon' => 'latepoint-icon latepoint-icon-layers', 'link' => OsRouterHelper::build_link(['conditions', 'index'])];
            return $menus;
        }

        public function addNextBtn($rules, $step)
        {
            $buttons = json_decode(OsSettingsHelper::get_settings_value('latepoint-button_confirmation', '[]'));
            foreach ($buttons as $button) {
                if ($button->referer && $button->referer == wp_get_referer()) {
                    $rules['confirmation'] = true;
                }
            }
            return $rules;
        }

        public function summaryValues($values)
        {
            $bookingObject = OsStepsHelper::get_booking_object();
            unset($values['customer']);
            if ($bookingObject && (($bookingObject->agent_id ?? null) == 6) && (($bookingObject->location_id ?? null) == 4))
                unset($values['location']);

            if ($values['time'] ?? false)
                $values['time'] = ['label' => __('Requested Time', 'latepoint-extand-master'), 'value' => ''];

            return $values;
        }

        public function steps($steps)
        {
            if (OsStepsHelper::$booking_object->service_id == 10) {
                $steps['confirmation'] = [
                    'title' => __('Your appointment request was received', 'latepoint-extand-master'),
                    'order_number' => 8,
                    'sub_title' => __('Appointment Request', 'latepoint-extand-master'),
                    'description' => __('Thank you for choosing Gotodoctor as your Virtual Healthcare provider. Please proceed to make payment and check your email for further instructions. *If this is an emergency, go to the nearest hospital or call 911.*<br /><strong>DO NOT COME IN, until you receive YOUR SPECIFIC appointment time.</strong>', 'latepoint-extand-master'),
                ];
            }
            if (OsStepsHelper::$booking_object->service_id == 13) {
                $steps['qhc_service'] = [
                    'title' => __('Services Required', 'latepoint-extand-master'),
                    'order_number' => 4,
                    'sub_title' => __('Services Required', 'latepoint-extand-master'),
                    'description' => '',
                ];
                $steps['qhc_contact'] = [
                    'title' => __('Contact Person Details', 'latepoint-extand-master'),
                    'order_number' => 5,
                    'sub_title' => __('Contact Person Details', 'latepoint-extand-master'),
                    'description' => '',
                ];
                $steps['qhc_additional'] = [
                    'title' => __('Additional Information', 'latepoint-extand-master'),
                    'order_number' => 6,
                    'sub_title' => __('Additional Information', 'latepoint-extand-master'),
                    'description' => '',
                ];
            }
            return $steps;
        }

        private function _fields($type = false, $reset = false)
        {
            $setting = new OsSettingsModel();
            $cfBooking = $setting->where(['name' => 'custom_fields_for_booking'])->set_limit(1)->get_results_as_models();
            $cfCustomer = $setting->where(['name' => 'custom_fields_for_customer'])->set_limit(1)->get_results_as_models();
            if ($cfBooking)
                $customFields = $cfBooking->value;
            if ($reset) {
                OsSettingsHelper::$loaded_values['custom_fields_for_booking'] = $cfBooking->value;
                OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = $cfCustomer->value;
            } else {
                $fields = [
                    'mbc' => [
                        'show' => ['cf_qOqKhbly', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                        'hide' => [
                            'cf_hbCNgimu',
                            'cf_zDS7LUjv',
                            'cf_H7MIk6Kt',
                        ],
                        'add' => [
                            'first_name' => [
                                'label' => __('First Name', 'latepoint'),
                                'placeholder' => __('First Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'first_name'
                            ],
                            'last_name' => [
                                'label' => __('Last Name', 'latepoint'),
                                'placeholder' => __('Last Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'last_name'
                            ],
                        ]
                    ],
                    'sb' => [
                        'show' => ['cf_Vin78Day', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                        'hide' => [
                            'cf_hbCNgimu',
                            'cf_zDS7LUjv',
                            'cf_H7MIk6Kt',
                        ],
                        'add' => [
                            'first_name' => [
                                'label' => __('First Name', 'latepoint'),
                                'placeholder' => __('First Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'first_name'
                            ],
                            'last_name' => [
                                'label' => __('Last Name', 'latepoint'),
                                'placeholder' => __('Last Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'last_name'
                            ],
                        ]
                    ],
                    'qh' => [
                        'show' => ['cf_SIt7Zefo', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                        'hide' => [
                            'cf_hbCNgimu',
                            'cf_zDS7LUjv',
                            'cf_H7MIk6Kt',
                        ],
                        'add' => [
                            'first_name' => [
                                'label' => __('First Name', 'latepoint'),
                                'placeholder' => __('First Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'first_name'
                            ],
                            'last_name' => [
                                'label' => __('Last Name', 'latepoint'),
                                'placeholder' => __('Last Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'last_name'
                            ],
                        ]
                    ],
                    'qhc' => [
                        'show' => ['cf_SIt7Zefo', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                        'hide' => [
                            'cf_hbCNgimu',
                            'cf_zDS7LUjv',
                            'cf_H7MIk6Kt',
                            'cf_nxwjDAcZ',
                        ],
                        'add' => [
                            'first_name' => [
                                'label' => __('Client First Name', 'latepoint'),
                                'placeholder' => __('Client First Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'first_name'
                            ],
                            'last_name' => [
                                'label' => __('Client Last Name', 'latepoint'),
                                'placeholder' => __('Client Last Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'last_name'
                            ],
                            'phone' => [
                                'label' => __('Client Contact Number', 'latepoint'),
                                'placeholder' => __('Client Contact Number', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'phone'
                            ],
                            'email' => [
                                'label' => __('Client Email', 'latepoint'),
                                'placeholder' => __('Client Email', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'email'
                            ],
                        ],
                        'merge' => [
                            'cf_x18jr0Vf' => [
                                'label' => __('Have you or client used GotoDoctor before?', 'latepoint'),
                            ],
                            'cf_6A3SfgET' => [
                                'label' => __('Where are you or the client currently located?', 'latepoint'),
                            ],
                        ]
                    ],
                    'p' => [
                        'show' => ['cf_SIt7Zefp', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                        'hide' => [
                            'cf_hbCNgimu',
                            'cf_zDS7LUjv',
                            'cf_H7MIk6Kt',
                        ],
                        'add' => [
                            'first_name' => [
                                'label' => __('First Name', 'latepoint'),
                                'placeholder' => __('First Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'first_name'
                            ],
                            'last_name' => [
                                'label' => __('Last Name', 'latepoint'),
                                'placeholder' => __('Last Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'last_name'
                            ],
                        ]
                    ],
                    'pc' => [
                        'show' => ['cf_SIt7Zefp', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                        'hide' => [
                            'cf_hbCNgimu',
                            'cf_zDS7LUjv',
                            'cf_H7MIk6Kt',
                            'cf_nxwjDAcZ',
                        ],
                        'add' => [
                            'first_name' => [
                                'label' => __('Client First Name', 'latepoint'),
                                'placeholder' => __('Client First Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'first_name'
                            ],
                            'last_name' => [
                                'label' => __('Client Last Name', 'latepoint'),
                                'placeholder' => __('Client Last Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'last_name'
                            ],
                            'phone' => [
                                'label' => __('Client Contact Number', 'latepoint'),
                                'placeholder' => __('Client Contact Number', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'phone'
                            ],
                            'email' => [
                                'label' => __('Client Email', 'latepoint'),
                                'placeholder' => __('Client Email', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'email'
                            ],
                        ],
                        'merge' => [
                            'cf_x18jr0Vf' => [
                                'label' => __('Have you or client used GotoDoctor before?', 'latepoint'),
                            ],
                            'cf_6A3SfgET' => [
                                'label' => __('Where are you or the client currently located?', 'latepoint'),
                            ],
                        ]
                    ],
                    'aas' => [
                        'show' => ['cf_WzbhG9eB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                        'hide' => [
                            'cf_hbCNgimu',
                            'cf_zDS7LUjv',
                            'cf_H7MIk6Kt',
                        ],
                        'add' => [
                            'first_name' => [
                                'label' => __('First Name', 'latepoint'),
                                'placeholder' => __('First Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'first_name'
                            ],
                            'last_name' => [
                                'label' => __('Last Name', 'latepoint'),
                                'placeholder' => __('Last Name', 'latepoint'),
                                'type' => 'text',
                                'width' => 'os-col-12',
                                'visibility' => 'public',
                                'options' => '',
                                'required' => 'on',
                                'id' => 'last_name'
                            ],
                        ]
                    ],
                    //'located' => ['show' => ['cf_6A3SfgET', 'cf_YXtUB2Jc']],
                    'located' => ['show' => ['cf_6A3SfgET']],
                    'locatedOther' => ['show' => ['cf_6A3SfgET']],
                    'covid' => ['show' => ['cf_GiVH6tot', 'cf_7MZNhPC6', 'cf_4aFGjt5V', 'cf_E6XolZDI']],
                ];
                $hideField = $onSave ? 'public' : 'hidden';
                $values = is_array($customFields) ? $customFields : json_decode($customFields, true);
                if ($values && $fields[$type]) {
                    foreach ($values as $id => $val) {
                        if (in_array($id ?? false, ($fields[$type]['hide'] ?? [])))
                            $values[$id]['visibility'] = $hideField;
                        if (in_array($id ?? false, ($fields[$type]['show'] ?? [])))
                            $values[$id]['visibility'] = 'public';

                        if ($fields[$type]['merge'][$id] ?? false)
                            $values[$id] = array_merge($values[$id], $fields[$type]['merge'][$id]);
                    }
                    $values = ($fields[$type]['add'] ?? []) + $values;
                    OsSettingsHelper::$loaded_values['custom_fields_for_booking'] = json_encode($values);
                }
                if ($cfCustomer) {
                    $values = is_array($cfCustomer->value) ? $cfCustomer->value : json_decode($cfCustomer->value, true);
                    if ($values && $fields[$type]) {
                        foreach ($values as $id => $val) {
                            if (in_array($id ?? false, ($fields[$type]['hide'] ?? [])))
                                $values[$id]['visibility'] = $hideField;
                            if (in_array($id ?? false, ($fields[$type]['show'] ?? [])))
                                $values[$id]['visibility'] = 'public';

                            if ($fields[$type]['merge'][$id] ?? false)
                                $values[$id] = array_merge($values[$id], $fields[$type]['merge'][$id]);
                        }
                        //$values = ($fields[$type]['add'] ?? []) + $values;
                        OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
                    }
                }
            }
        }

        public function replace($text, $booking)
        {
            if ($booking) {
                $custom_fields_for_booking = OsCustomFieldsHelper::get_custom_fields_arr('booking', 'all');
                if (!empty($custom_fields_for_booking)) {
                    $needles = [];
                    $replacements = [];
                    foreach ($custom_fields_for_booking as $custom_field) {
                        $needles[] = '{' . $custom_field['id'] . '}';
                        $replacements[] = $booking->get_meta_by_key($custom_field['id'], '');
                    }
                    $text = str_replace($needles, $replacements, $text);
                }
            }
            return $text;
        }

        public function customerFilter($validations)
        {
            $validations['email'] = array('presence', 'email');
            return $validations;
        }

        public function stepNames($steps, $show_all_steps)
        {
            $restrictions = OsParamsHelper::get_param('restrictions');
            if (OsStepsHelper::$booking_object->service_id == 13 || ($restrictions['selected_service'] ?? false) == 13) {
                if ($index = array_search('datepicker', $steps)) {
                    array_splice($steps, $index, 2, ['qhc_service', 'qhc_contact', 'qhc_additional']);
                }
            }
            return $steps;
        }

        public function beSkipped($skip, $step, $booking_object)
        {
            if ($booking_object->service_id == 13) {
                if (in_array($step, ['datepicker', 'contact']))
                    $skip = true;
                if (in_array($step, ['qhc_service', 'qhc_contact', 'qhc_additional']))
                    $skip = false;
            } else {
                if (in_array($step, ['qhc_service', 'qhc_contact', 'qhc_additional']))
                    $skip = true;
            }
            return $skip;
        }

        /**
         * File upload handler
         */
        public function fileUpload()
        {
            check_ajax_referer('latepoint_file_upload', 'security');
            $file = $_FILES['additinal_file'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $saveName = uniqid() . '-' . $file_name;
            //check file type by mime, allowed types are: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, txt, csv
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain', 'text/csv'];
            if (!in_array($file['type'], $allowedMimes)) {
                wp_send_json(array('status' => 'error', 'message' => __('File type not allowed', 'latepoint')));
            }
            //check file size, max size is 10MB
            if ($file['size'] > 10485760) {
                wp_send_json(array('status' => 'error', 'message' => __('File size too big', 'latepoint')));
            }

            if ($r = wp_upload_bits($saveName, null, file_get_contents($file_tmp))) {
                //return the file url
                $response = array('status' => 'success', 'file' => $r['url'], 'original_name' => $file_name);
            } else {
                $response = array('status' => 'error', 'message' => __('Error uploading file', 'latepoint'));
            }
            wp_send_json($response);
        }

        /**
         * File delete handler
         */
        public function fileDelete()
        {
            //Convert the url to the absolute path
            foreach (($_POST['file'] ?? []) as $file) {
                if (strpos($file, wp_upload_dir()['baseurl']) === 0)
                    wp_delete_file(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file));
            }
            wp_send_json(['status' => 'success']);
        }

        protected function checkCert($cert)
        {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}mbc_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        protected function checkCertSB($cert)
        {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}sb_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        protected function checkCertQH($cert)
        {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}qh_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        protected function checkCertP($cert)
        {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}partners_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        protected function checkCertAAS($cert)
        {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}aas_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        public function onDeactivate()
        {
        }

        public function onActivate()
        {
            if (class_exists('OsDatabaseHelper')) OsDatabaseHelper::check_db_version_for_addons();
        }

        protected function _timezone($bookingObject)
        {
            $booking = OsParamsHelper::get_param('booking');
            $custom_fields_data = $booking['custom_fields'];
            $timezone = '';
            if (isset($booking['custom_fields']['cf_6A3SfgET'])) {
                unset($_SESSION['earliest']);

                switch ($booking['custom_fields']['cf_6A3SfgET']) {
                    case 'British Columbia':
                        $_SESSION['earliest'] = -180;
                        break;
                    case 'Manitoba':
                    case 'Saskatchewan':
                        $_SESSION['earliest'] = -60;
                        break;
                }
            }
            if ($timezone) {
                OsTimeHelper::set_timezone_name_in_session($timezone);
            }
        }
    }
endif;

if (in_array('latepoint/latepoint.php', get_option('active_plugins', [])) || array_key_exists('latepoint/latepoint.php', get_site_option('active_sitewide_plugins', []))) {
    $LATEPOINTEXT = new LatePointExt();
}
