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
    require_once(dirname(__FILE__) . '/Traits/GtdTrait.php');
    require_once(dirname(__FILE__) . '/Traits/LoadStepTrait.php');
    require_once(dirname(__FILE__) . '/Traits/ProcessStepTrait.php');
    require_once(dirname(__FILE__) . '/Traits/SetFieldTrait.php');
    /**
     * Main Class.
     *
     */
    final class LatePointExt
    {
        use LoadStepTrait, ProcessStepTrait, SetFieldTrait, GtdTrait;

        public $version = '1.4.2';
        public $dbVersion = '1.0.0';
        public $addonName = 'latepoint-extend';

        protected $covid;
        protected $others;
        protected $acorn;
        protected $diff;
        protected $amount = 66;

        protected $cFields = [
            'reason' => 'cf_E6XolZDI'
        ];

        public function __construct()
        {
            $this->version = filemtime(__DIR__ . '/public/js/front.js');
            $this->defines();
            $this->hooks();
        }

        public function defines() {}

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
            add_action('wp_ajax_nopriv_check_certificate_fl', [$this, 'checkCertificateSessionFL']);
            add_action('wp_ajax_check_certificate_fl', [$this, 'checkCertificateFL']);
            add_action('wp_ajax_nopriv_check_certificate_imc', [$this, 'checkCertificateSessionImc']);
            add_action('wp_ajax_check_certificate_imc', [$this, 'checkCertificateImc']);
            add_action('wp_ajax_nopriv_check_certificate_gotohw', [$this, 'checkCertificateSessionGotoHW']);
            add_action('wp_ajax_check_certificate_gotohw', [$this, 'checkCertificateGotoHW']);
            add_action('wp_ajax_nopriv_mbc_certificate', [$this, 'mbcCert']);
            add_action('wp_ajax_mbc_certificate', [$this, 'mbcCert']);
            add_action('wp_ajax_nopriv_cbp_certificate', [$this, 'cbpCert']);
            add_action('wp_ajax_cbp_certificate', [$this, 'cbpCert']);
            add_action('wp_ajax_nopriv_check_certificate_seb', [$this, 'sebCert']);
            add_action('wp_ajax_check_certificate_seb', [$this, 'sebCert']);
            add_action('wp_ajax_nopriv_check_certificate_drug', [$this, 'drugCert']);
            add_action('wp_ajax_check_certificate_drug', [$this, 'drugCert']);
            add_action('wp_ajax_nopriv_check_certificate_ub', [$this, 'unionBenefitsCert']);
            add_action('wp_ajax_check_certificate_ub', [$this, 'unionBenefitsCert']);
            add_action('wp_ajax_nopriv_check_certificate_lg', [$this, 'leslieGroupCert']);
            add_action('wp_ajax_check_certificate_lg', [$this, 'leslieGroupCert']);
            add_action('wp_ajax_nopriv_check_certificate_by', [$this, 'certBy']);
            add_action('wp_ajax_check_certificate_by', [$this, 'certBy']);
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

        public function mbcCert()
        {
            if (!session_id()) {
                session_start();
            }
            $id = trim($_POST['id']);
            if (!$id) {
                $isWildfire = stripos($_SERVER['HTTP_REFERER'] ?? '', 'wildfire') !== false;
                $num = $isWildfire ? 'Use code' : 'Certificate number';
                wp_send_json_error(['message' => "{$num} is required."], 404);
            }
            $care = $this->checkCert($id, 13);
            if ($care) {
                $_SESSION['certCount'] = 0;
                $_SESSION['nonteamster'] = false;
                wp_send_json_success(['care' => 1], 200);
            } else {
                $_SESSION['nonteamster'] = true;
                $this->checkCertificate();
            }
            wp_die();
        }

        public function cbpCert()
        {
            if (!session_id()) {
                session_start();
            }
            $id = trim($_POST['id']);
            if (!$id) {
                wp_send_json_error(['message' => 'Certificate number is required.'], 404);
            }
            $check = $this->checkCertPartner($id, 'cb_providers', 'services');
            if ($check === true) {
                $check = ['care' => true, 'eap' => true, 'op' => true];
            }
            $care = $check['care'] ?? false;
            $eap = $check['eap'] ?? false;
            $op = $check['op'] ?? false;
            if ($care || $eap) {
                $_SESSION['certCount'] = 0;
                wp_send_json_success(['care' => $care, 'eap' => $eap, 'op' => $op], 200);
            } else {
                $this->checkCertificateCBP();
            }
            wp_die();
        }

        public function sebCert()
        {
            if (!session_id()) {
                session_start();
            }
            $id = trim($_POST['id']);
            if (!$id) {
                wp_send_json_error(['message' => 'Certificate number is required.'], 404);
            }
            $care = $this->checkCertPartner($id, 'seb', 13);
            $eap = $this->checkCertPartner($id, 'seb', 15);
            if ($care || $eap) {
                $_SESSION['certCount'] = 0;
                wp_send_json_success(['care' => $care, 'eap' => $eap], 200);
            } else {
                if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
                if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

                if (!$this->checkCertPartner($id, 'seb')) {
                    $_SESSION['certCount'] += 1;
                    if ($_SESSION['certCount'] >= 3)
                        $msg = "We're sorry. The certificate number provided does not match our records. Please contact Smart Employee Benefits at <nobr>1-xxx-xxx-xxxx</nobr> to confirm eligibility. For any technical issues, please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                    else
                        $msg = 'Certificate number does not match our records. Please try again.';

                    wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
                }
            }
            wp_die();
        }

        public function drugCert()
        {
            if (!session_id()) {
                session_start();
            }
            $id = trim($_POST['id']);
            if (!$id) {
                wp_send_json_error(['message' => 'Certificate number is required.'], 404);
            }
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            if (!$this->checkCertPartner($id, 'drug')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The password provided does not match our records.";
                else
                    $msg = 'Password does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function unionBenefitsCert()
        {
            if (!session_id()) {
                session_start();
            }
            $id = trim($_POST['id']);
            if (!$id) {
                wp_send_json_error(['message' => 'Certificate number is required.'], 404);
            }
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            if (!$this->checkCertPartner($id, 'union_benefits')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function certBy()
        {
            if (!session_id()) {
                session_start();
            }
            $id = trim($_POST['id'] ?? '');
            $by = trim($_POST['by'] ?? '');
            $agentId = trim($_POST['agent_id'] ?? '');
            if (!$id && !$agentId) {
                wp_send_json_error(['message' => 'Certificate number is required.'], 404);
            }
            if (!$by) {
                wp_send_json_error(['message' => 'Partner is required.'], 404);
            }
            switch ($by) {
                case 'vpi':
                    $this->vpiCert($id);
                    break;
                case 'cc':
                    $this->ccCert($id);
                    break;
                case 'sp':
                    $this->spCert($id, $agentId);
                    break;
                default:
                    wp_send_json_error(['message' => 'Partner not found.'], 404);
            }
            wp_die();
        }

        public function leslieGroupCert()
        {
            if (!session_id()) {
                session_start();
            }
            $id = trim($_POST['id']);
            if (!$id) {
                wp_send_json_error(['message' => 'Certificate number is required.'], 404);
            }
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            if (!$this->checkCertPartner($id, 'leslie_group')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function vpiCert($id)
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            if (!$this->checkCertPartner($id, 'vpi')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function ccCert($id)
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            if (!$this->checkCertPartner($id, 'cleveland_clinic')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The service key provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Service key does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function spCert($id, $agentId)
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            switch ($agentId) 
            {
                case 22:
                    $partner = 'hunters';
                    $key = 'Email address';
                    break;
                case 23:
                    $partner = 'cpsm';
                    $key = 'Certificate number';
                    $_msg = "We're sorry. The {$key} provided does not match our records. Please contact CPSM at 1-514-723-3594 to confirm eligibility. For any technical issues, Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                    break;
                case 24:
                    $partner = 'asylum';
                    $key = 'Email address';
                    break;
                case 25:
                    $partner = 'local711';
                    $key = 'Email address';
                    break;
                case 28:
                    $partner = 'mgt';
                    $key = 'MGT employee ID';
                    break;
                case 29:
                    $partner = 'seb';
                    $key = 'EEID';
                    $_msg = "We're sorry. The {$key} provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-866-866-8659</nobr> for assistance.";
                    break;
                default:
                    $partner = '';
                    $key = '';
                    break;
            }
            !isset($_msg) && $_msg = "We're sorry. The {$key} provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";

            if (!$id) {
                wp_send_json_error(['message' => $key . ' is required.'], 404);
            }

            if (!$this->checkCertPartner($id, $partner)) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = $_msg;
                else
                    $msg = $key . ' does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
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
            if ($id && !$this->checkCert($id, $_POST['service_id'] ?? null)) {
                $_SESSION['certCount'] += 1;
                $isWildfire = stripos($_SERVER['HTTP_REFERER'] ?? '', 'wildfire') !== false;
                if ($_SESSION['certCount'] >= 3) {
                    $num = $isWildfire ? 'use code' : 'certificate number';
                    $msg = "We're sorry. The {$num} provided does not match our records. Please contact Manitoba Blue Cross at <nobr>1-888-596-1032</nobr> to confirm eligibility. For any technical issues, please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                } else {
                    $num = $isWildfire ? 'Provided code' : 'Certificate number';
                    $msg = "{$num} does not match our records. Please try again.";
                }

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

        public function checkCertificateSessionFL()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateFL();
        }

        public function checkCertificateFL()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertPartner($id, 'fabricland')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function checkCertificateSessionImc()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateImc();
        }

        public function checkCertificateImc()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertPartner($id, 'imperial_capital')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact Imperial Capital Limited at <nobr>437-290-5842</nobr> to confirm eligibility. For any technical issues, please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Certificate number does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function checkCertificateSessionGotoHW()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateGotoHW();
        }

        public function checkCertificateGotoHW()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertPartner($id, 'gothealthwallet')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The employee ID provided does not match our records. Please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
                else
                    $msg = 'Employee ID does not match our records. Please try again.';

                wp_send_json_error(['message' => $msg, 'count' => $_SESSION['certCount']], 404);
            }
            wp_die();
        }

        public function checkCertificateSessionCBP()
        {
            if (!session_id()) {
                session_start();
            }
            $this->checkCertificateCBP();
        }

        public function checkCertificateCBP()
        {
            if (!($_SESSION['certCount'] ?? false)) $_SESSION['certCount'] = 0;
            if ($_SESSION['certCount'] >= 3) $_SESSION['certCount'] = 0;

            $id = trim($_POST['id']);
            if ($id && !$this->checkCertPartner($id, 'cb_providers')) {
                $_SESSION['certCount'] += 1;
                if ($_SESSION['certCount'] >= 3)
                    $msg = "We're sorry. The certificate number provided does not match our records. Please contact CB Providers at <nobr>1-855-944-9166</nobr> to confirm eligibility. For any technical issues, please contact Gotodoctor.ca at <nobr>1-833-820-8800</nobr> for assistance.";
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
            if ($id && !$this->checkCertAAS($id, $_POST['service_id'] ?? null)) {
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
            if (in_array($booking->agent_id, [6, 18])) {
                OsSettingsHelper::$loaded_values['notifications_email'] = 'off';
            }
            if (!in_array($booking->type_id, [5, 11])) {
                OsSettingsHelper::$loaded_values['notification_customer_confirmation'] = 'off';
            }
        }

        public function sidePanel($stepName)
        {
            global $wpdb;

            $bookingObject = OsStepsHelper::get_booking_object();
            $this->_covid($bookingObject);

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
            if (in_array(OsStepsHelper::$booking_object->service_id, [13])) {
                $append = '';
                switch (OsStepsHelper::$booking_object->agent_id) {
                    case '6':
                        if ($_SESSION['nonteamster'] ?? false)
                            $append = '<br><br><strong>Submit your request and get a FREE consultation first</strong><br>Payment required only after you would like to proceed';
                        break;
                    case '11':
                        $desc = 'Thank you for your Gotodoctor Specialist Access Navigation Service request.';
                        break;
                    case '12':
                        $append = '<br><br><strong>Submit your request and get a FREE consultation first</strong><br>Payment required only after you would like to proceed';
                        break;
                    case '18':
                        $desc = 'Thank you for your Gotodoctor Health System Navigator & Second Opinion request.';
                        break;
                    default:
                        $desc = '';
                }
                $desc .= ' You will hear back from us within the next business day. Contact us if you have any questions. Call 911 or visit the nearest hospital for any emergencies.<br><br>* If this is an emergency please go to the nearest hospital or call 911.*';
                echo <<<EOT
<script>
jQuery(function($) {
    $('li[data-step-name="custom_fields_for_booking"] span').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc .latepoint-desc-title').text('Client Details');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text('Request submitted');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').html('{$desc}');
    var cfb = $('.latepoint-side-panel .latepoint-step-desc-w .latepoint-step-desc .latepoint-desc-content').text() == $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="custom_fields_for_booking"] .latepoint-desc-content').text();
    var append = '{$append}';
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="custom_fields_for_booking"] .latepoint-desc-content').append(append);
    if (cfb) {
        $('.latepoint-side-panel .latepoint-step-desc-w .latepoint-step-desc .latepoint-desc-content').append(append);
    }
});
</script>
EOT;
            }
            if (in_array(OsStepsHelper::$booking_object->service_id, [15])) {
                echo <<<EOT
<script>
jQuery(function($) {
    $('li[data-step-name="custom_fields_for_booking"] span').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc .latepoint-desc-title').text('Client Details');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text("Request received. We'll contact you soon");
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').html('Thank you for choosing Gotodoctor.ca. Our team will review your request and contact you within the next 3 business days to collect any additional information required. If you do not hear from us, please call us to confirm your request was received.<br><br>* If this is an emergency please go to the nearest hospital or call 911.*');
});
</script>
EOT;
            }
            if (OsStepsHelper::$booking_object->agent_id == 12) {
                echo <<<EOT
<script>
jQuery(function($) {
    $('li[data-step-name="custom_fields_for_booking"] span').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc .latepoint-desc-title').text('Client Details');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text("Request received. We'll contact you soon");
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').html('Thank you for choosing Gotodoctor.ca. <br>Our team will review your request and contact you within the next 3 business days to collect any additional information required. <br>If you do not hear from us, please call us to confirm your request was received.<br><br>* If this is an emergency please go to the nearest hospital or call 911.*');
    setInterval(function() {
        if ($('.latepoint-form-w .latepoint-heading-w h3.os-heading-text').text() == 'Request submitted') {
            $('.latepoint-form-w .latepoint-heading-w h3.os-heading-text').text("Request received. We'll contact you soon");
        }
    }, 500);
});
</script>
EOT;
            }
            if (OsStepsHelper::$booking_object->service_id == 14) {
                echo <<<EOT
<script>
jQuery(function($) {
    $('li[data-step-name="custom_fields_for_booking"] span').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc .latepoint-desc-title').text('Client Details');
    $('.latepoint-form-w .latepoint-heading-w .os-heading-text').text('Client Details');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-title').text('Request submitted');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').html('Thank you for choosing Gotodoctor.ca. Our team will review your request and contact you within the next 3 business days to collect any additional information required. If you do not hear from us, please call us to confirm your request was received.<br><br>* If this is an emergency please go to the nearest hospital or call 911.*');
    $('.latepoint-footer a.latepoint-btn.latepoint-next-btn').attr('data-os-success-action', 'redirect').attr('data-os-redirect-to', 'https://app2.connectedwellness.com/ui/pub/reg?org=gtd&id=6462769585b0a7766da9ef3b&locale=en').addClass('latepoint-btn-redirection');
    $('.latepoint-footer a.latepoint-btn.latepoint-next-btn').click(function(e) {
        e.preventDefault();
        $('.latepoint-lightbox-close').click();
        window.location.href = 'https://app2.connectedwellness.com/ui/login/main?goto=%2Fui%2Fncw%2FNotifications%2F64cdc95ecf43aa164f824ea4&locale=en&org=gtd';
    });
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
                }
            }
            $lid = intval($bookingObject->location_id ?? 0);
            echo <<<EOT
<script>
jQuery(function($) {
    latepoint_location_id = {$lid};
    if ($('html').attr('lang') == 'fr') {
        $('.latepoint-body > .latepoint-step-content').append('<input type="hidden" name="booking[custom_fields][language]" value="fr" />');
    }
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
    $('.latepoint-w .latepoint-booking-form-element .latepoint-progress ul li[data-step-name="qha_time"]').attr('style', 'display: none !important');
    $('.latepoint-w .latepoint-booking-form-element .latepoint-progress ul li[data-step-name="datepicker2"]').attr('style', 'display: none !important');
    $('.latepoint-w .latepoint-booking-form-element .latepoint-progress ul li[data-step-name="datepicker3"]').attr('style', 'display: none !important');
});
</script>
EOT;
            if (in_array($bookingObject->agent_id, [8, 22, 23, 24, 25, 28]) && $bookingObject->location_id == 4) {
                echo <<<EOT
<script>
jQuery(function($) {
    $('.os-summary-value-location').closest('.os-summary-line').hide().addClass('should-hide hide');
    ele = $('.latepoint-booking-form-element');
    function sprice() {
        if(latepoint_location_id == 4) {
            if($('#booking_custom_fields_cf_6a3sfget').length && $('#booking_custom_fields_cf_6a3sfget').val() && !['Quebec', 'New Brunswick'].includes($('#booking_custom_fields_cf_6a3sfget').val())) {
                var price = ($('#booking_custom_fields_cf_6a3sfget').val() == 'Ontario') ? 210 : 120;
                $('.os-priced-item').attr('data-item-price', price);
                $('.latepoint-priced-component').val(price);
                latepoint_update_summary_field(ele, 'price', '$' + price);

                $('.os-summary-line.should-hide').removeClass('hide').show();
            } else {
                $('.os-priced-item').attr('data-item-price', 0);
                $('.latepoint-priced-component').val(0);
                latepoint_update_summary_field(ele, 'price', 0);

                $('.os-summary-line.should-hide').hide();
            }
        }
    }
    sprice();
    ele.on('change', '#booking_custom_fields_cf_6a3sfget', function() {
        sprice();
    });
});
</script>
EOT;
            }
            if (in_array($bookingObject->agent_id, [11, 15, 16, 19, 20]) && ($bookingObject->location_id != 14)) {
                $location = $bookingObject->location->name ?? '';
                $other = in_array($bookingObject->location_id, [4]) ? true : false;
                $onPrice = $other ? 210 : 105;
                $price = $other ? 120 : 66;
                echo <<<EOT
<script>
jQuery(function($) {
    ele = $('.latepoint-booking-form-element');
    hlocation = '{$location}';
    function sprice() {
        var cLoc = $('#booking_custom_fields_cf_6a3sfget').val();
        if (
            $('#booking_custom_fields_cf_6a3sfget').length
            && cLoc
            && !['Quebec'].includes(cLoc)
            && !hlocation.includes(cLoc)
        ) {
            var price = (cLoc == 'Ontario') ? {$onPrice} : {$price};
            $('.os-priced-item').attr('data-item-price', price);
            $('.latepoint-priced-component').val(price);
            latepoint_update_summary_field(ele, 'price', '$' + price);
        } else {
            $('.os-priced-item').attr('data-item-price', 0);
            $('.latepoint-priced-component').val(0);
            latepoint_update_summary_field(ele, 'price', 0);
        }
    }
    sprice();
    ele.on('change', '#booking_custom_fields_cf_6a3sfget', function() {
        sprice();
    });
});
</script>
EOT;
            }
            if (
                (in_array($bookingObject->agent_id, [2, 7, 9, 10, 14]) && !in_array($bookingObject->location_id, [14]))
                || (in_array($bookingObject->agent_id, [8, 22, 23, 24, 25, 28]) && !in_array($bookingObject->location_id, [4, 14]))
            ) {
                $location = $bookingObject->location->name ?? '';
                $other = in_array($bookingObject->location_id, [4]) ? true : false;
                $biz = in_array($bookingObject->location_id, [12]) ? 1 : 0;
                $onPrice = $other ? 210 : 105;
                $price = $other ? 120 : ($biz ? 60 : 66);
                echo <<<EOT
<script>
jQuery(function($) {
    hlocation = '{$location}';
    ele = $('.latepoint-booking-form-element');
    function sprice() {
            if($('#booking_custom_fields_cf_6a3sfget').length && $('#booking_custom_fields_cf_6a3sfget').val() && !hlocation.includes($('#booking_custom_fields_cf_6a3sfget').val())) {
                var price = ($('#booking_custom_fields_cf_6a3sfget').val() == 'Ontario') && !{$biz} ? {$onPrice} : {$price};
                $('.os-priced-item').attr('data-item-price', price);
                $('.latepoint-priced-component').val(price);
                latepoint_update_summary_field(ele, 'price', '$' + price);
            } else {
                $('.os-priced-item').attr('data-item-price', 0);
                $('.latepoint-priced-component').val(0);
                latepoint_update_summary_field(ele, 'price', 0);
            }
    }
    sprice();
    ele.on('change', '#booking_custom_fields_cf_6a3sfget', function() {
        sprice();
    });
});
</script>
EOT;
            }
            // On returning patient
            if ($this->returningExtra($bookingObject)) {
                echo <<<EOT
<script>
jQuery(function($) {
    var aid = {$bookingObject->agent_id};
    var alist = [2, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 18, 19, 20, 21, 22, 23, 24, 25, 28];
    var observer = new MutationObserver(function(mutations) {
        showhide();
    });
    
    // Start observing the form element once it's available
    var formCheckInterval = setInterval(function() {
        var formElement = $('.latepoint-booking-form-element')[0];
        if (formElement) {
            observer.observe(formElement, { 
                childList: true, 
                subtree: true,
                attributes: true
            });
            clearInterval(formCheckInterval);
            // Initial call to ensure proper state
            showhide();
        }
    }, 200);
    $('body').on('change', '#booking_custom_fields_cf_x18jr0vf', function() {
        showhide();
    });
    function showhide() {
        if($('#booking_custom_fields_cf_wfhtigvf').length && $('#booking_custom_fields_cf_wfhtigvf').attr('type') != 'date') {
            $('#booking_custom_fields_cf_wfhtigvf').attr('type', 'date');
            $('#booking_custom_fields_cf_wfhtigvf').parent('div.os-form-group').addClass('os-form-select-group').removeClass('os-form-textfield-group')
        }
        if($('#booking_custom_fields_cf_wfhtigvf').length && ($('#booking_custom_fields_cf_x18jr0vf').val() != 'Yes' && !alist.includes(aid))) {
            $('#booking_custom_fields_cf_wfhtigvf').closest('.os-form-group').hide();
            $('#booking_custom_fields_cf_zoxsdwez').closest('.os-form-group').hide();
        } else {
            $('#booking_custom_fields_cf_wfhtigvf').closest('.os-form-group').show();
            $('#booking_custom_fields_cf_zoxsdwez').closest('.os-form-group').show();
        }
    }
});
</script>
EOT;
            }
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
                foreach (
                    [
                        'email',
                        'phone',
                        'emergency',
                        'start_date2',
                        'start_time2',
                        'start_date3',
                        'start_time3',
                        'group',
                        'r',
                        'region',
                        'language',
                        'group_id',
                    ] as $key
                ) {
                    if ($data['custom_fields'][$key] ?? false) {
                        $model->custom_fields[$key] = $data['custom_fields'][$key];
                    }
                }
                $booking = OsParamsHelper::get_param('customer');
                if ($booking['custom_fields']['cf_4zkIbeeY'] ?? false) {
                    $model->visit_reason = $booking['custom_fields']['cf_4zkIbeeY'];
                    if ($booking['custom_fields']['cf_4zkIbeeY'] == 'Other' && ($booking['custom_fields']['cf_NVByvyYw'] ?? false)) {
                        $model->visit_reason .= ' (' . $booking['custom_fields']['cf_NVByvyYw'] . ')';
                    }
                    if (($booking['custom_fields']['cf_4zkIbeeY'] == 'Prescription renewal') && ($booking['custom_fields']['cf_cVndXX2e'] ?? false)) {
                        $model->visit_reason .= '||||prescription_renewal_pharmacy:' . $booking['custom_fields']['cf_cVndXX2e']
                            . '||||prescription_renewal_pharmacy_phone:' . $booking['custom_fields']['cf_iAoOucDc'];
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
                foreach (
                    [
                        'start_date2',
                        'start_time2',
                        'start_date3',
                        'start_time3',
                        'r',
                        'region',
                        'language',
                        'group_id',
                    ] as $key
                ) {
                    if ($model->custom_fields[$key] ?? false) {
                        $model->save_meta_by_key($key, $model->custom_fields[$key]);
                    }
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
            if ($booking->service_id == 16)
                echo <<<EOT
<script>
jQuery(function($) {
    $('.latepoint-body .confirmation-app-info ul li:first-child + li').remove();
    $('.latepoint-body .confirmation-app-info h5').text('Submitted Information');
    $('.latepoint-body .confirmation-customer-info h5').text('Customer Information');
    $('.latepoint-side-panel .latepoint-step-desc-w div[data-step-name="confirmation"] .latepoint-desc-content').html('Thank you for choosing Gotodoctor.ca. We are reviewing your request and will contact you within the next 5 business days.<br />*If this is an emergency, please go to the nearest hospital or call 911.*');
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
            if ($this->covid || $this->others || $this->acorn || $booking->service_id == 10) 
                if ($this->others && ($booking->agent_id == 8) && $booking->get_meta_by_key('cf_6A3SfgET') == 'Quebec')
                    return;
            */

            if ($this->needInvoice($booking)) {
                $ref = '';
                $extraClass = '';
                if ($booking->type_id) {
                    $referralType = $wpdb->get_row("SELECT * from wp_referral_type where id = {$booking->type_id}");
                    $ref = $referralType->type_name . '[' . $referralType->type_registration_form_url . ']';
                }
                $agentName = '';
                if ($booking->agent_id) {
                    $agent = new OsAgentModel($booking->agent_id);
                    $agentName = OsAgentHelper::get_full_name($agent);
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
                    'physical_location' => $booking->get_meta_by_key('cf_6A3SfgET', ''),
                    'agent' => $agentName,
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
                if ($this->others || $this->diff) {
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
                $other = in_array($booking->location_id, [4]) ? true : false;
                $onPrice = $other ? 210 : 105;
                $price = $other ? 120 : 66;
                $amount = $booking->location_id == 12 ? 60 : ($booking->get_meta_by_key('cf_6A3SfgET', '') == 'Ontario' ? $onPrice : $price);
                $data = [
                    'method' => 'POST',
                    'body' => [
                        'invoice' => 1,
                        'email' => $booking->customer ? $booking->customer->email : '',
                        'first_name' => $booking->get_meta_by_key('cf_hbCNgimu', ''),
                        'message' => $booking->get_meta_by_key('cf_H7MIk6Kt', null),
                        'invoice_type' => $invoiceType,
                        'amount' => $amount,
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
        protected function needInvoice($booking)
        {
            $ploc = $booking->get_meta_by_key('cf_6A3SfgET', '');
            $loc = $booking->location ? $booking->location->name : '';
            if (in_array($booking->service_id, [13, 15, 16])) {
                return false;
            }
            if (
                ($booking->service_id == 10)
                || $this->acorn
                || $this->covid
                || ($this->others && (!in_array($booking->agent_id, [6, 8, 11, 15, 16, 19, 20, 22, 23, 24, 25, 28])
                    || (in_array($booking->agent_id, [8, 22, 23, 24, 25, 28]) && !in_array($ploc, ['Quebec', 'New Brunswick']))
                    || (in_array($booking->agent_id, [11, 14, 15, 16, 19, 20]) && !in_array($ploc, ['Quebec']))
                ))
                || $this->diff = (
                    (in_array($booking->agent_id, [2, 7, 9, 10, 14]) && (stripos($loc, $ploc) === false))
                    || (in_array($booking->agent_id, [11, 15, 16, 19, 20]) && !$this->others && !in_array($ploc, ['Quebec']) && (stripos($loc, $ploc) === false))
                    || (in_array($booking->agent_id, [8, 22, 23, 24, 25, 28]) && !$this->others && (stripos($loc, $ploc) === false))
                )
            ) {
                return true;
            }
            return false;
        }

        protected function isReturning()
        {
            $booking = OsParamsHelper::get_param('booking');
            $custom_fields_data = $booking['custom_fields'];
            return ($custom_fields_data['cf_x18jr0Vf'] ?? false) == 'Yes';
        }

        protected function returningExtra($booking)
        {
            return (
                (($booking->location_id == 1) && in_array($booking->agent_id, [3, 4]))
                || in_array($booking->agent_id, [2, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 18, 19, 20, 21, 22, 23, 24, 25, 28])
            ) ? true : false;
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
            if ($step == 'pharmacy') {
                $rules['pharmacy'] = true;
            }
            return $rules;
        }

        public function summaryValues($values)
        {
            $bookingObject = OsStepsHelper::get_booking_object();
            unset($values['customer']);
            if ($bookingObject && (in_array($bookingObject->agent_id ?? null, [6, 18])) && (($bookingObject->location_id ?? null) == 4))
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
            if (in_array(OsStepsHelper::$booking_object->service_id, [13, 15])) {
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
            } elseif (in_array(OsStepsHelper::$booking_object->service_id, [16])) {
                $steps['pharmacy'] = [
                    'title' => __('Pharmacy Information', 'latepoint-extand-master'),
                    'order_number' => 1,
                    'sub_title' => __('Pharmacy Information', 'latepoint-extand-master'),
                    'description' => __('Drug and support request', 'latepoint-extand-master'),
                ];
                $steps['pharmacy_additional'] = [
                    'title' => __('Information Request', 'latepoint-extand-master'),
                    'order_number' => 2,
                    'sub_title' => __('Information Request', 'latepoint-extand-master'),
                    'description' => __('Drug and support request', 'latepoint-extand-master'),
                ];
            } else {
                $steps['qha_time'] = [
                    'title' => '',
                    'order_number' => 4,
                    'sub_title' => '',
                    'description' => '',
                ];
            }
            $steps['datepicker2'] = [
                'title' => __('Select Date & Time', 'latepoint'),
                'order_number' => 4,
                'sub_title' => __('Date & Time Selection', 'latepoint'),
                'description' => __('Click on a date to see a timeline of available slots, click on a green time slot to reserve it', 'latepoint')
            ];
            $steps['datepicker3'] = [
                'title' => __('Select Date & Time', 'latepoint'),
                'order_number' => 4,
                'sub_title' => __('Date & Time Selection', 'latepoint'),
                'description' => __('Click on a date to see a timeline of available slots, click on a green time slot to reserve it', 'latepoint')
            ];
            return $steps;
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
            $booking = OsParamsHelper::get_param('booking');
            if (
                OsStepsHelper::$booking_object->service_id == 13
                || ($restrictions['selected_service'] ?? false) == 13
                || OsStepsHelper::$booking_object->service_id == 15
                || ($restrictions['selected_service'] ?? false) == 15
            ) {
                if ($index = array_search('datepicker', $steps)) {
                    array_splice($steps, $index, 2, ['qhc_service', 'qhc_contact', 'qhc_additional']);
                }
            } elseif (OsStepsHelper::$booking_object->service_id == 14 || ($restrictions['selected_service'] ?? false) == 14) {
                $steps = [];
            }
            if (array_search('datepicker2', $steps) === false) {
                if (array_search('datepicker', $steps) !== false) {
                    $index = array_search('datepicker', $steps);
                    array_splice($steps, $index + 1, 0, ['datepicker2', 'datepicker3']);
                    array_splice($steps, $index, 1, ['qha_time', 'datepicker']);
                }
            }
            if (
                OsStepsHelper::$booking_object->service_id == 16
                || ($restrictions['selected_service'] ?? false) == 16
            ) {
                $steps = ['pharmacy', 'pharmacy_additional', 'locations', 'locations', 'locations', 'locations', 'locations', 'locations', 'confirmation'];
                remove_all_actions('latepoint_step_names_in_order');
            }
            return $steps;
        }

        public function beSkipped($skip, $step, $booking_object)
        {
            $params = OsParamsHelper::get_param('booking');
            if (in_array($booking_object->service_id, [13, 15])) {
                if (in_array($step, ['datepicker', 'contact']))
                    $skip = true;
                if (in_array($step, ['qhc_service', 'qhc_contact', 'qhc_additional']))
                    $skip = false;
            } else {
                if (in_array($step, ['qhc_service', 'qhc_contact', 'qhc_additional']))
                    $skip = true;

                if (in_array($step, ['qha_time']))
                    $skip = false;
                if ((($params['qha_time'] ?? false) == 'fastest') && (in_array($step, ['datepicker', 'datepicker2', 'datepicker3'])))
                    $skip = true;
            }
            if ($booking_object->service_id == 14) {
                if (!in_array($step, ['custom_fields_for_booking']))
                    $skip = true;
            }
            if (isset($params['custom_fields']['skip_rest'])) {
                if (in_array($step, ['datepicker2', 'datepicker3']))
                    $skip = true;
            }
            if ($booking_object->service_id == 16) {
                $skip = !in_array($step, ['pharmacy', 'pharmacy_additional', 'confirmation']);
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
            //check file size, max size is 5MB
            $maxFileSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxFileSize) {
                wp_send_json(array('status' => 'error', 'message' => __('File size up to 5MB allowed', 'latepoint')));
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

        public function isGTD()
        {
            return OsParamsHelper::get_param('is_gtd') == '1';
        }

        protected function checkCert($cert, $serviceId = null)
        {
            global $wpdb;
            $check = $this->checkCertTest($cert, 'mbc');
            if (!$check) {
                if ($serviceId == 13) {
                    $row = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}mbc_members where concat('a', certificate) = '%s'", 'a' . $cert));
                    // only group 7245 can book
                    $check = ($row->group ?? false) == 7245;
                } else {
                    $check = $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}mbc_members where concat('a', certificate) = '%s'", 'a' . $cert));
                }
            }

            return $check;
        }

        protected function checkCertSB($cert)
        {
            global $wpdb;
            return  $this->checkCertTest($cert, 'sb') ?: $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}sb_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        protected function checkCertQH($cert)
        {
            global $wpdb;
            return $this->checkCertTest($cert) ?: $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}qh_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        protected function checkCertP($cert)
        {
            global $wpdb;
            return $this->checkCertTest($cert) ?: $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}partners_members where concat('a', certificate) = '%s'", 'a' . $cert));
        }

        protected function checkCertAAS($cert, $serviceId = null)
        {
            global $wpdb;
            $check = $this->checkCertTest($cert);
            if ($check) return $check;

            if ($serviceId == 13) {
                $row = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}aas_members where concat('a', certificate) = '%s'", 'a' . $cert));
                $check = in_array(($row->group ?? false), ['11040001', '10320001', '11020001', '11020000']);
            } else {
                $check = $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}aas_members where concat('a', certificate) = '%s'", 'a' . $cert));
            }
            return $check;
        }

        protected function checkCertPartner($cert, $partner, $serviceId = null)
        {
            global $wpdb;
            $check = $this->checkCertTest($cert, $partner);
            if ($check) return $check;

            if ($serviceId) {
                switch ($partner) {
                    case 'cb_providers':
                        $row = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}partner_members where concat('a', certificate) = '%s' and partner = '%s'", 'a' . $cert, $partner));
                        if ($serviceId == 'services') {
                            $care = stripos($row->service, 'navigation') !== false;
                            $eap = stripos($row->service, 'eap2') !== false;
                            $op = stripos($row->service, '2ndop') !== false;
                            $check = compact('care', 'eap', 'op');
                        } elseif ($serviceId == 13)
                            $check = stripos($row->service, 'navigation') !== false;
                        elseif ($serviceId == 15)
                            $check = stripos($row->service, 'eap2') !== false;

                        break;
                    default:
                        $check = $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}partner_members where concat('a', certificate) = '%s' and partner = '%s'", 'a' . $cert, $partner));
                }
            } else {
                $check = $wpdb->get_var($wpdb->prepare("select id from {$wpdb->prefix}partner_members where concat('a', certificate) = '%s' and partner = '%s'", 'a' . $cert, $partner));
            }
            return $check;
        }

        protected function checkCertTest($cert, $partner = null)
        {
            return ($cert == '123456')
                || ($cert == 'support@teledact.ca')
                || ($cert == 'Testgtd' && in_array($partner, ['cb_providers', 'sb', 'mbc']))
                || (($partner == 'mbc') && ($cert == 'MBCHELPS'))
                || (($partner == 'fabricland') && ($cert == 'FABRICFRIENDS'))
                || (($partner == 'imperial_capital') && ($cert == '1234567890'))
                || (($partner == 'cb_providers') && ($cert == '1234567890'));
        }

        public function onDeactivate() {}

        public function onActivate()
        {
            if (class_exists('OsDatabaseHelper')) OsDatabaseHelper::check_db_version_for_addons();
        }

        protected function _timezone($bookingObject)
        {
            $booking = OsParamsHelper::get_param('booking');
            $timezone = '';
            if (isset($booking['custom_fields']['cf_6A3SfgET'])) {
                unset($_SESSION['earliest']);

                switch ($booking['custom_fields']['cf_6A3SfgET']) {
                    // The time shift, the base is Ontario
                    case 'Alberta':
                    case 'Northwest Territories':
                    case 'Yukon':
                        $_SESSION['earliest'] = -120;
                        break;
                    case 'Newfoundland and Labrador':
                        $_SESSION['earliest'] = 90;
                        break;
                    case 'Nova Scotia':
                    case 'New Brunswick':
                    case 'Prince Edward Island':
                        $_SESSION['earliest'] = 60;
                        break;
                    case 'Quebec':
                    case 'Ontario':
                    case 'Yes':
                        $_SESSION['earliest'] = 0;
                        break;
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
