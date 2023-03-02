<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('OsConditionsController')) :
    class OsConditionsController extends OsController
    {
        public function __construct()
        {
            parent::__construct();

            $this->views_folder = plugin_dir_path(__FILE__) . '../views/conditions/';
        }

        public function index()
        {
            $conditions = $this->_getFromSettings();
            $buttons = $this->_getFromButtonSettings();

            $this->_vars();
            $this->vars['page_header'] = 'Conditions';
            $this->vars['conditions'] = $conditions;
            $this->vars['disabledCustomer'] = OsSettingsHelper::get_settings_value('latepoint-disabled_customer_login', 0);
            $this->vars['allowShortcode'] = OsSettingsHelper::get_settings_value('latepoint-allow_shortcode_custom_fields', 0);
            $this->vars['buttonConfirms'] = $buttons;
            $this->format_render(__FUNCTION__);
        }

        public function new_form()
        {
            $this->_vars();
            $this->vars['condition'] = [
                'id' => $this->_genId(),
                'label' => '',
                'type' => '',
                'placeholder' => '',
                'agents' => []
            ];
            $this->set_layout('none');
            $this->format_render(__FUNCTION__);
        }

        public function new_form_button()
        {
            $this->vars['buttonConfirm'] = [
                'id' => $this->_genId($this->_getFromButtonSettings),
                'label' => '',
                'link' => '',
                'target' => '',
                'referer' => ''
            ];
            $this->set_layout('none');
            $this->format_render(__FUNCTION__);
        }

        public function location()
        {
            $this->_location_vars();
            $setting = $this->getLocationSettings();
            $this->vars['settings'] = $setting;
            $this->format_render(__FUNCTION__);
        }

        public function new_location()
        {
            $this->_location_vars();
            $this->vars['condition'] = [
                'id' => $this->_genId(),
                'label' => '',
                'type' => '',
                'placeholder' => '',
                'agents' => [],
                'referrals' => [],
                'location_id' => '',
            ];
            $this->set_layout('none');
            $this->format_render(__FUNCTION__);
        }

        public function save_location()
        {
            if ($condition = $this->params['condition']) {
                if (!$condition['location_id']) {
                    $status = LATEPOINT_STATUS_ERROR;
                    $response = __('Invalid Location', 'latepoint-conditions');
                } else {
                    $agents = $referrals = [];
                    foreach ($condition['agents'] as $agentId => $v) {
                        if ($v == 'yes') $agents[] = $agentId;
                    }
                    if (!$agents) {
                        $status = LATEPOINT_STATUS_ERROR;
                        $response = __('Invalid Agents', 'latepoint-conditions');
                    } else {
                        $condition['agents'] = $agents;

                        foreach ($condition['referrals'] as $referralId => $v) {
                            if ($v == 'yes') $referrals[] = $referralId;
                        }
                        $condition['referrals'] = $referrals;

                        $conditions = $this->getLocationSettings();
                        if (!isset($condition['id']) || !$condition['id']) {
                            $condition['id'] = $this->_genId($conditions);
                        }

                        $conditions[$condition['id']] = $condition;

                        if (OsSettingsHelper::save_setting_by_name('latepoint-location_switch', json_encode($conditions))) {
                            $status = LATEPOINT_STATUS_SUCCESS;
                            $response = __('Location Setting Saved', 'latepoint-conditions');
                        } else {
                            $status = LATEPOINT_STATUS_ERROR;
                            $response = __('Error Saving Setting', 'latepoint-conditions');
                        }
                    }
                }
            } else {
                $status = LATEPOINT_STATUS_ERROR;
                $response = __('Invalid Params', 'latepoint-conditions');
            }
            if ($this->get_return_format() == 'json') {
                $this->send_json(['status' => $status, 'message' => $response]);
            }
        }

        public function save()
        {
            if ($condition = $this->params['condition']) {
                $condition['custom_fields'] = array_filter($condition['custom_fields']);
                if (!$condition['custom_fields']) {
                    $status = LATEPOINT_STATUS_ERROR;
                    $response = __('Invalid Custom Fields', 'latepoint-conditions');
                } else {
                    $agents = [];
                    foreach ($condition['agents'] as $agentId => $v) {
                        if ($v == 'yes') $agents[] = $agentId;
                    }
                    if (!$agents) {
                        $status = LATEPOINT_STATUS_ERROR;
                        $response = __('Invalid Agents', 'latepoint-conditions');
                    } else {
                        $condition['agents'] = $agents;

                        $conditions = $this->_getFromSettings();
                        if (!isset($condition['id']) || !$condition['id']) {
                            $condition['id'] = $this->_genId($conditions);
                        }

                        $conditions[$condition['id']] = $condition;

                        if (OsSettingsHelper::save_setting_by_name('latepoint-conditions', json_encode($conditions))) {
                            $status = LATEPOINT_STATUS_SUCCESS;
                            $response = __('Condition Saved', 'latepoint-conditions');
                        } else {
                            $status = LATEPOINT_STATUS_ERROR;
                            $response = __('Error Saving Condition', 'latepoint-conditions');
                        }
                    }
                }
            } else {
                $status = LATEPOINT_STATUS_ERROR;
                $response = __('Invalid Params', 'latepoint-conditions');
            }
            if ($this->get_return_format() == 'json') {
                $this->send_json(['status' => $status, 'message' => $response]);
            }
        }

        public function confirmation()
        {
            if ($buttonConfirm = $this->params['button']) {
                $buttons = $this->_getFromButtonSettings();
                foreach ($buttons as $i => $button) {
                    if (!isset($button['id'])) unset($buttons[$i]);
                }
                if (!isset($buttonConfirm['id']) || !$buttonConfirm['id'])
                    $buttonConfirm['id'] = $this->_genId($buttons);

                $buttons[$buttonConfirm['id']] = $buttonConfirm;

                if (OsSettingsHelper::save_setting_by_name('latepoint-button_confirmation', json_encode($buttons))) {
                    $status = LATEPOINT_STATUS_SUCCESS;
                    $response = __('Saved', 'latepoint-conditions');
                } else {
                    $status = LATEPOINT_STATUS_ERROR;
                    $response = __('Error', 'latepoint-conditions');
                }
            } else {
                $status = LATEPOINT_STATUS_ERROR;
                $response = __('Invalid Params', 'latepoint-conditions');
            }
            if ($this->get_return_format() == 'json') {
                $this->send_json(['status' => $status, 'message' => $response]);
            }
        }

        public function delete()
        {
            if (isset($this->params['id']) && !empty($this->params['id'])) {
                $conditions = $this->_getFromSettings();
                if (isset($conditions[$this->params['id']])) {
                    unset($conditions[$this->params['id']]);

                    if (OsSettingsHelper::save_setting_by_name('latepoint-conditions', json_encode($conditions))) {
                        $status = LATEPOINT_STATUS_SUCCESS;
                        $response = __('Condition Removed', 'latepoint-conditions');
                    } else {
                        $status = LATEPOINT_STATUS_ERROR;
                        $response = __('Error Removing Condition', 'latepoint-conditions');
                    }
                } else {
                    $status = LATEPOINT_STATUS_ERROR;
                    $response = __('Invalid Field ID', 'latepoint-conditions');
                }
            } else {
                $status = LATEPOINT_STATUS_ERROR;
                $response = __('Invalid Field ID', 'latepoint-conditions');
            }
            if ($this->get_return_format() == 'json') {
                $this->send_json(['status' => $status, 'message' => $response]);
            }
        }

        public function delete_button()
        {
            if (isset($this->params['id']) && !empty($this->params['id'])) {
                $conditions = $this->_getFromButtonSettings();
                if (isset($conditions[$this->params['id']])) {
                    unset($conditions[$this->params['id']]);

                    if (OsSettingsHelper::save_setting_by_name('latepoint-button_confirmation', json_encode($conditions))) {
                        $status = LATEPOINT_STATUS_SUCCESS;
                        $response = __('Button Removed', 'latepoint-conditions');
                    } else {
                        $status = LATEPOINT_STATUS_ERROR;
                        $response = __('Error Removing Button', 'latepoint-conditions');
                    }
                } else {
                    $status = LATEPOINT_STATUS_ERROR;
                    $response = __('Invalid Field ID', 'latepoint-conditions');
                }
            } else {
                $status = LATEPOINT_STATUS_ERROR;
                $response = __('Invalid Field ID', 'latepoint-conditions');
            }
            if ($this->get_return_format() == 'json') {
                $this->send_json(['status' => $status, 'message' => $response]);
            }
        }

        public function delete_location()
        {
            if (isset($this->params['id']) && !empty($this->params['id'])) {
                $conditions = $this->getLocationSettings();
                if (isset($conditions[$this->params['id']])) {
                    unset($conditions[$this->params['id']]);

                    if (OsSettingsHelper::save_setting_by_name('latepoint-location_switch', json_encode($conditions))) {
                        $status = LATEPOINT_STATUS_SUCCESS;
                        $response = __('Location Setting Removed', 'latepoint-conditions');
                    } else {
                        $status = LATEPOINT_STATUS_ERROR;
                        $response = __('Error Removing Location Setting', 'latepoint-conditions');
                    }
                } else {
                    $status = LATEPOINT_STATUS_ERROR;
                    $response = __('Invalid Field ID', 'latepoint-conditions');
                }
            } else {
                $status = LATEPOINT_STATUS_ERROR;
                $response = __('Invalid Field ID', 'latepoint-conditions');
            }
            if ($this->get_return_format() == 'json') {
                $this->send_json(['status' => $status, 'message' => $response]);
            }
        }

        public function settings()
        {
            if ($this->params && count($this->params)) {
                foreach ($this->params as $k => $v) {
                    if (OsSettingsHelper::save_setting_by_name('latepoint-' . $k, $v)) {
                        $status = LATEPOINT_STATUS_SUCCESS;
                        $response = __('Saved', 'latepoint-conditions');
                    } else {
                        $status = LATEPOINT_STATUS_ERROR;
                        $response = __('Error', 'latepoint-conditions');
                    }
                }
            } else {
                $status = LATEPOINT_STATUS_ERROR;
                $response = __('Invalid Params', 'latepoint-conditions');
            }
            if ($this->get_return_format() == 'json') {
                $this->send_json(['status' => $status, 'message' => $response]);
            }
        }

        protected function _vars()
        {
            $this->vars['custom_fields_for_booking'] = OsCustomFieldsHelper::get_custom_fields_arr('booking', 'customer');

            $agents = new OsAgentModel();
            $this->vars['agents'] = $agents->get_results_as_models();
        }

        protected function _location_vars()
        {
            global $wpdb;
            $agents = new OsAgentModel();
            $locs = new OsLocationModel();
            $allLocs = $locs->get_results_as_models();
            $locations = [];
            foreach ($allLocs as $loc) {
                $locations[] = [
                    'value' => $loc->id,
                    'label' => $loc->name
                ];
            }
            $referralTypes = $wpdb->get_results("SELECT * FROM wp_referral_type");

            $this->vars['agents'] = $agents->get_results_as_models();
            $this->vars['locations'] = $locations;
            $this->vars['referralTypes'] = $referralTypes;
        }

        protected function _getFromSettings()
        {
            $conditions = [];
            $setting = OsSettingsHelper::get_settings_value('latepoint-conditions', false);
            if ($setting) {
                $conditions = json_decode($setting, true);
            }
            return $conditions;
        }

        protected function _getFromButtonSettings()
        {
            $conditions = [];
            $setting = OsSettingsHelper::get_settings_value('latepoint-button_confirmation', false);
            if ($setting) {
                $conditions = json_decode($setting, true);
            }
            return $conditions;
        }

        public function getLocationSettings()
        {
            $conditions = [];
            $setting = OsSettingsHelper::get_settings_value('latepoint-location_switch', false);
            if ($setting) {
                $conditions = json_decode($setting, true);
            }
            return $conditions;
        }

        protected function _genId($conditions = null)
        {
            !$conditions && $conditions = $this->_getFromSettings();
            do {
                $id = OsCustomFieldsHelper::generate_custom_field_id();
            } while (isset($conditions[$id]));

            return $id;
        }
    }
endif;
