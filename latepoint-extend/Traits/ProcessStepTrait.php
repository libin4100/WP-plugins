<?php
trait ProcessStepTrait
{
    public function processStep($stepName, $bookingObject)
    {
        $this->_covid($bookingObject);

        $this->setField($bookingObject);

        switch ($stepName) {
            case 'login':
                if (intval($bookingObject->agent_id ?? 0) === 30) {
                    $booking = OsParamsHelper::get_param('booking');
                    $status = trim((string)($booking['custom_fields']['gtd_login_status'] ?? ''));
                    if ($status === '') {
                        $_POST['booking']['custom_fields']['gtd_login_status'] = 'not_login';
                        $status = 'not_login';
                    }
                    if (!in_array($status, ['login', 'not_login'], true)) {
                        remove_all_actions('latepoint_process_step');
                        wp_send_json([
                            'status' => LATEPOINT_STATUS_ERROR,
                            'message' => ['Invalid login status. Please choose login or request without login.']
                        ]);
                        return;
                    }
                }
                break;
            case 'custom_fields_for_booking':
                if ($bookingObject->service_id == 14) {
                    remove_all_actions('latepoint_process_step');
                    wp_send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => ''));
                    return;
                }

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
                    /*
                        MBC care navigation no need to check certificate
                        if ($bookingObject->agent_id == 6 && $k == 'cf_qOqKhbly') {
                            if (!$this->checkCert($custom_fields_data[$k] ?? '', $bookingObject->service_id)) {
                                $msg = 'Certificate number does not match our records. Please try again.';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                        */
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
                        if (!$this->checkCertAAS($custom_fields_data[$k] ?? '',  $bookingObject->service_id)) {
                            $msg = 'Certificate number does not match our records. Please try again.';
                            $errors[] = ['type' => 'validation', 'message' => $msg];
                        }
                    }
                    $lists = [
                        'fabricland' => ['agent_id' => 11, 'field' => 'cf_pnWPrUIe'],
                        'gotohealthwallet' => ['agent_id' => 13, 'field' => 'cf_P56xPUO5'],
                        'imperial_capital' => ['agent_id' => 14, 'field' => 'cf_W0iZRLtG'],
                        'cb_providers' => ['agent_id' => 15, 'field' => 'cf_4wVF2U9Y'],
                        'seb' => ['agent_id' => 16, 'field' => 'cf_aku1T075'],
                        'union_benefits' => ['agent_id' => 18, 'field' => 'cf_qblbyjs8'],
                        'leslie_group' => ['agent_id' => 19, 'field' => 'cf_AYVpjhpP'],
                        'vpi' => ['agent_id' => 20, 'field' => 'cf_9OaDIkYh'],
                        'cleveland_clinic' => ['agent_id' => 21, 'field' => 'cf_yjnZIZ1D'],
                        'hunters' => ['agent_id' => 22, 'field' => 'cf_9e1mhF4v'],
                        'bestbuy' => ['agent_id' => 29, 'field' => 'cf_ryf56IpW'],
                    ];
                    foreach ($lists as $key => $list) {
                        if ($bookingObject->agent_id == $list['agent_id'] && $k == $list['field']) {
                            if (!$this->checkCertPartner($custom_fields_data[$k] ?? '', $key, $bookingObject->service_id)) {
                                $name = $f['label'] ?? 'Certificate number';
                                $msg = $name . ' does not match our records. Please try again.';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                    }
                    if ($this->returningExtra($bookingObject) && in_array($k, ['cf_WFHtiGvf'])) {
                        if (($custom_fields_data['cf_x18jr0Vf'] ?? '') == 'Yes' || $bookingObject->agent_id == 2) {
                            if (!($custom_fields_data[$k] ?? '')) {
                                $msg = $f['label'] . ' is required';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            } elseif (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $custom_fields_data[$k]) != 1) {
                                //If the value is not a valid date, return error
                                $msg = $f['label'] . ' is not a valid date';
                                $errors[] = ['type' => 'validation', 'message' => $msg];
                            }
                        }
                    }
                }
                if (in_array($bookingObject->service_id, [2, 3, 7, 8])) {
                    if ($_errors = $this->validNeedRenew($custom_fields_data, $custom_fields_for_booking)) {
                        $errors = array_merge($errors, $_errors);
                    }
                }
                if ($this->isGTD()) {
                    $_errors = $this->validPrescription($custom_fields_data, $custom_fields_for_booking);
                    if ($_errors) {
                        $errors = array_merge($errors, $_errors);
                    }
                }
                // Cleveland Clinic (agent_id == 21) wifi validation
                if ($bookingObject->agent_id == 21) {
                    $isTytoHome = ($custom_fields_data['cf_sx8M50Pw'] ?? '') === 'Tyto Home';

                    // Calculate age from date of birth
                    $isUnder18 = false;
                    $dob = $custom_fields_data['cf_WFHtiGvf'] ?? '';
                    if ($dob && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                        $birthDate = new DateTime($dob);
                        $today = new DateTime();
                        $isUnder18 = $today->diff($birthDate)->y < 18;
                    }

                    // Guardian fields required when Tyto Home AND under 18
                    if ($isTytoHome && $isUnder18) {
                        foreach (['cf_fH4hcx29', 'cf_B7rj01VE'] as $field) {
                            if (empty($custom_fields_data[$field])) {
                                $label = $custom_fields_for_booking[$field]['label'] ?? $field;
                                $errors[] = ['type' => 'validation', 'message' => $label . ' is required'];
                            }
                        }
                    }

                    // Tyto Home required fields
                    if ($isTytoHome) {
                        foreach (['cf_VTXfH4Wq', 'cf_ZmLsfxFI'] as $field) {
                            if (empty($custom_fields_data[$field])) {
                                $label = $custom_fields_for_booking[$field]['label'] ?? $field;
                                $errors[] = ['type' => 'validation', 'message' => $label . ' is required'];
                            }
                        }
                    }

                    // cf_6NqyuLpc required if either guardian field has value
                    if (!empty($custom_fields_data['cf_fH4hcx29']) || !empty($custom_fields_data['cf_B7rj01VE'])) {
                        if (empty($custom_fields_data['cf_6NqyuLpc'])) {
                            $label = $custom_fields_for_booking['cf_6NqyuLpc']['label'] ?? 'cf_6NqyuLpc';
                            $errors[] = ['type' => 'validation', 'message' => $label . ' is required'];
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
                if ($this->shouldAuthorizeAgent30WithoutLogin($bookingObject, $booking)) {
                    $customerAuthError = $this->authorizeAgent30CustomerFromCustomFields($bookingObject, $custom_fields_data);
                    if ($customerAuthError) {
                        remove_all_actions('latepoint_process_step');
                        wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => [$customerAuthError]));
                        return;
                    }
                }
                $this->ensureAgent30DefaultDateTime($bookingObject);

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

                $booking = OsParamsHelper::get_param('customer');
                if ((($booking['custom_fields']['cf_4zkIbeeY'] ?? false) == 'Other') && !($booking['custom_fields']['cf_NVByvyYw'] ?? false)) {
                    remove_all_actions('latepoint_process_step');
                    wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['Other Reason ( required ) can not be blank']));
                    return;
                }
                if ((($booking['custom_fields']['cf_4zkIbeeY'] ?? false) == 'Prescription renewal') && !(($booking['custom_fields']['cf_cVndXX2e'] ?? false) && ($booking['custom_fields']['cf_iAoOucDc'] ?? false))) {
                    remove_all_actions('latepoint_process_step');
                    wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['Pharmacy can not be blank']));
                    return;
                }
                break;

            case 'qha_time':
                break;

            case 'qhc_service':
                $booking = OsParamsHelper::get_param('booking');
                if ($bookingObject->service_id == 15) {
                    $_err = true;
                    if (!isset($booking['qhc']['services']) || !is_array($booking['qhc']['services'])) {
                    } else {
                        foreach ($booking['qhc']['services'] as $field => $val) {
                            if (strtolower($val) == 'on') {
                                $_err = false;
                                break;
                            }
                        }
                    }
                    if ($_err) {
                        remove_all_actions('latepoint_process_step');
                        wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['You need to select at least one service.']));
                        return;
                    }
                }

                // Orthopedic surgery
                /*
                    if (($booking['qhc']['services']['Orthopedic surgery'] ?? false) == 'on') {
                        $select = trim($booking['custom_fields']['cf_DQ70wnRG'] ?? '');
                        if (!$select) {
                            remove_all_actions('latepoint_process_step');
                            wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['Please select the service of orthopedic surgery.']));
                            return;
                        }
                    }
                    */
                break;
            case 'qhc_contact':
                break;
            case 'qhc_additional':
            case 'pharmacy_additional':
                /*
                    if ($bookingObject->service_id == 15) {
                        $booking = OsParamsHelper::get_param('booking');
                        if (
                            !isset($booking['qhc']['additional_file'])
                            || !is_array($booking['qhc']['additional_file'])
                            || empty(array_filter($booking['qhc']['additional_file']))
                        ) {
                            remove_all_actions('latepoint_process_step');
                            wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['You need to upload at least one file.']));
                            return;
                        }
                    }
                    */
                $booking = OsParamsHelper::get_param('booking');
                $qhc = $booking['custom_fields'];
                if (isset($qhc['first_name']) && !isset($qhc['last_name'])) {
                    $arr = explode(' ', $qhc['first_name']);
                    $qhc['last_name'] = (count($arr) > 1) ? array_pop($arr) : '';
                    $qhc['first_name'] = implode(' ', $arr);
                }
                $email = trim($qhc['email'] ?: $booking['qhc']['pharmacy_email'] ?? '');
                if ($email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        remove_all_actions('latepoint_process_step');
                        wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['Email is not valid.']));
                        return;
                    }
                }
                $customer_params = [
                    'first_name' => $qhc['first_name'],
                    'last_name' => $qhc['last_name'],
                    'email' => $email,
                    'phone' => $qhc['phone'] ?: $booking['qhc']['pharmacy_phone'] ?? '',
                ];
                $customer = new OsCustomerModel();
                $check = $customer->where(['email' => $customer_params['email']])->get_results_as_models();
                if ($check) {
                    $customer = $check[0];
                } else {
                    remove_all_actions('latepoint_model_validate');
                }
                if (!$customer_params['email']) {
                    add_filter('latepoint_customer_model_validations', function ($validations) {
                        unset($validations['email']);
                    });
                }
                $customer->set_data($customer_params);
                $customer->save();
                if ($customer->id ?? false) {
                    OsAuthHelper::authorize_customer($customer->id);
                    OsStepsHelper::$booking_object->customer_id = $customer->id;
                } else {
                    try {
                        error_log("Latepoint error to get customer: " . json_encode(OsParamsHelper::get_params()));
                    } catch (\Exception $e) {
                    }
                }
                break;
            case 'datepicker2':
            case 'datepicker3':
                break;
        }
    }

    protected function shouldAuthorizeAgent30WithoutLogin($bookingObject, $booking)
    {
        if (intval($bookingObject->agent_id ?? 0) !== 30) {
            return false;
        }
        $loggedInCustomerId = intval(OsAuthHelper::get_logged_in_customer_id() ?: 0);
        $bookingCustomerId = intval($bookingObject->customer_id ?? 0);
        if ($loggedInCustomerId && $bookingCustomerId && ($loggedInCustomerId === $bookingCustomerId)) {
            return false;
        }

        $status = '';
        if (is_array($booking)) {
            $status = trim(strtolower((string)($booking['custom_fields']['gtd_login_status'] ?? '')));
        } elseif (is_object($booking) && isset($booking->custom_fields) && is_array($booking->custom_fields)) {
            $status = trim(strtolower((string)($booking->custom_fields['gtd_login_status'] ?? '')));
        }
        if ($status === '' && isset($bookingObject->custom_fields['gtd_login_status'])) {
            $status = trim(strtolower((string)$bookingObject->custom_fields['gtd_login_status']));
        }
        if ($status === '') {
            $status = 'not_login';
        }

        // For agent 30, always recover customer session from submitted custom fields
        // when current request does not have a valid logged-in customer context.
        if (!$loggedInCustomerId || !$bookingCustomerId || ($loggedInCustomerId !== $bookingCustomerId)) {
            return true;
        }

        return $status === 'not_login';
    }

    protected function authorizeAgent30CustomerFromCustomFields($bookingObject, $customFieldsData)
    {
        $bookingParams = OsParamsHelper::get_param('booking');
        $customerInput = OsParamsHelper::get_param('customer');
        $readValue = function ($payload, $key) {
            if (is_array($payload)) {
                return $payload[$key] ?? '';
            }
            if (is_object($payload)) {
                return $payload->$key ?? '';
            }
            return '';
        };

        $email = trim((string)($customFieldsData['email'] ?? ''));
        if ($email === '') {
            $email = trim((string)$readValue($bookingParams, 'email'));
        }
        if ($email === '') {
            $email = trim((string)$readValue($customerInput, 'email'));
        }

        $phone = trim((string)($customFieldsData['phone'] ?? ''));
        if ($phone === '') {
            $phone = trim((string)$readValue($bookingParams, 'phone'));
        }
        if ($phone === '') {
            $phone = trim((string)$readValue($customerInput, 'phone'));
        }
        if (!$email && !$phone) {
            return 'Email or phone is required to continue.';
        }

        $firstName = trim((string)($customFieldsData['cf_SdFSk6Tv'] ?? $customFieldsData['first_name'] ?? ''));
        if ($firstName === '') {
            $firstName = trim((string)$readValue($bookingParams, 'first_name'));
        }
        if ($firstName === '') {
            $firstName = trim((string)$readValue($customerInput, 'first_name'));
        }

        $lastName = trim((string)($customFieldsData['cf_blm6LCcz'] ?? $customFieldsData['last_name'] ?? ''));
        if ($lastName === '') {
            $lastName = trim((string)$readValue($bookingParams, 'last_name'));
        }
        if ($lastName === '') {
            $lastName = trim((string)$readValue($customerInput, 'last_name'));
        }
        if (!$firstName) {
            $firstName = 'Client';
        }
        if (!$lastName) {
            $lastName = 'EAP';
        }

        $customer = null;
        if ($email) {
            $exists = (new OsCustomerModel())->where(['email' => $email])->set_limit(1)->get_results_as_models();
            if ($exists) {
                if (is_array($exists)) {
                    $customer = $exists[0] ?? null;
                } elseif (is_object($exists)) {
                    $customer = $exists;
                }
            }
        }
        if (!$customer && $phone) {
            $exists = (new OsCustomerModel())->where(['phone' => $phone])->set_limit(1)->get_results_as_models();
            if ($exists) {
                if (is_array($exists)) {
                    $customer = $exists[0] ?? null;
                } elseif (is_object($exists)) {
                    $customer = $exists;
                }
            }
        }
        if (!$customer) {
            $customer = new OsCustomerModel();
        }

        $customerPayload = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
        ];
        if (!$email) {
            add_filter('latepoint_customer_model_validations', function ($validations) {
                unset($validations['email']);
                return $validations;
            });
        }
        $customer->set_data($customerPayload);
        $customer->save();

        if (!($customer->id ?? false)) {
            return 'Unable to create customer record. Please verify contact information and try again.';
        }

        OsAuthHelper::authorize_customer($customer->id);
        OsStepsHelper::$booking_object->customer_id = $customer->id;
        $bookingObject->customer_id = $customer->id;
        $_POST['booking']['customer_id'] = $customer->id;
        $_POST['customer_id'] = $customer->id;

        return null;
    }

    protected function ensureAgent30DefaultDateTime($bookingObject)
    {
        if (intval($bookingObject->agent_id ?? 0) !== 30) {
            return;
        }

        $today = OsTimeHelper::today_date('Y-m-d');

        if (empty($bookingObject->start_date)) {
            $bookingObject->start_date = $today;
        }
        if (!isset($bookingObject->start_time) || $bookingObject->start_time === '' || $bookingObject->start_time === null) {
            $bookingObject->start_time = 0;
        }
        if (empty($bookingObject->end_date)) {
            $bookingObject->end_date = $bookingObject->start_date;
        }
        if (!isset($bookingObject->end_time) || $bookingObject->end_time === '' || $bookingObject->end_time === null) {
            $bookingObject->end_time = 0;
        }

        if (!isset($_POST['booking']) || !is_array($_POST['booking'])) {
            $_POST['booking'] = [];
        }
        if (empty($_POST['booking']['start_date'])) {
            $_POST['booking']['start_date'] = $bookingObject->start_date;
        }
        if (!isset($_POST['booking']['start_time']) || $_POST['booking']['start_time'] === '') {
            $_POST['booking']['start_time'] = (string)$bookingObject->start_time;
        }
        if (empty($_POST['booking']['end_date'])) {
            $_POST['booking']['end_date'] = $bookingObject->end_date;
        }
        if (!isset($_POST['booking']['end_time']) || $_POST['booking']['end_time'] === '') {
            $_POST['booking']['end_time'] = (string)$bookingObject->end_time;
        }
    }
}
