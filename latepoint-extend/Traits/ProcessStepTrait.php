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
                    if (method_exists($this, 'isAgent30LoginPageEnabled') && !$this->isAgent30LoginPageEnabled()) {
                        if (!isset($_POST['booking']) || !is_array($_POST['booking'])) {
                            $_POST['booking'] = [];
                        }
                        if (!isset($_POST['booking']['custom_fields']) || !is_array($_POST['booking']['custom_fields'])) {
                            $_POST['booking']['custom_fields'] = [];
                        }
                        $_POST['booking']['custom_fields']['gtd_login_status'] = 'not_login';
                        break;
                    }
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
                    if ($status === 'login') {
                        $username = trim((string)($booking['gtd_login']['username'] ?? ''));
                        $password = (string)($booking['gtd_login']['password'] ?? '');
                        if ($username === '' || $password === '') {
                            remove_all_actions('latepoint_process_step');
                            wp_send_json([
                                'status' => LATEPOINT_STATUS_ERROR,
                                'message' => ['Please enter username and password.']
                            ]);
                            return;
                        }

                        $appUser = null;
                        $authError = $this->validateAgent30AppUserCredentials($username, $password, $appUser);
                        if ($authError) {
                            remove_all_actions('latepoint_process_step');
                            wp_send_json([
                                'status' => LATEPOINT_STATUS_ERROR,
                                'message' => [$authError]
                            ]);
                            return;
                        }

                        if (!isset($_POST['booking']) || !is_array($_POST['booking'])) {
                            $_POST['booking'] = [];
                        }
                        if (!isset($_POST['booking']['custom_fields']) || !is_array($_POST['booking']['custom_fields'])) {
                            $_POST['booking']['custom_fields'] = [];
                        }
                        if (!empty($appUser['email']) && empty($_POST['booking']['custom_fields']['email'])) {
                            $_POST['booking']['custom_fields']['email'] = (string)$appUser['email'];
                        }
                        if (!empty($appUser['phone']) && empty($_POST['booking']['custom_fields']['phone'])) {
                            $_POST['booking']['custom_fields']['phone'] = (string)$appUser['phone'];
                        }
                    }

                    // Never keep raw credentials in request payload after step validation.
                    if (isset($_POST['booking']['gtd_login']['password'])) {
                        unset($_POST['booking']['gtd_login']['password']);
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
                $shouldAuthorizeAgent30 = $this->shouldAuthorizeAgent30WithoutLogin($bookingObject, $booking);
                if (intval($bookingObject->agent_id ?? 0) === 30) {
                    error_log(sprintf(
                        '[latepoint-extend] Agent30 custom_fields auth check. should_authorize=%d booking_customer_id=%d logged_in_customer_id=%d',
                        $shouldAuthorizeAgent30 ? 1 : 0,
                        intval($bookingObject->customer_id ?? 0),
                        intval(OsAuthHelper::get_logged_in_customer_id() ?: 0)
                    ));
                }
                if ($shouldAuthorizeAgent30) {
                    $customerAuthError = $this->authorizeAgent30CustomerFromCustomFields($bookingObject, $custom_fields_data);
                    if ($customerAuthError) {
                        error_log(sprintf(
                            '[latepoint-extend] Agent30 custom_fields authorize failed. error=%s booking_customer_id=%d logged_in_customer_id=%d',
                            (string)$customerAuthError,
                            intval($bookingObject->customer_id ?? 0),
                            intval(OsAuthHelper::get_logged_in_customer_id() ?: 0)
                        ));
                        remove_all_actions('latepoint_process_step');
                        wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => [$customerAuthError]));
                        return;
                    }
                }
                $accountCreationError = $this->maybeCreateAgent30AppUser($bookingObject, $booking, $custom_fields_data);
                if ($accountCreationError) {
                    remove_all_actions('latepoint_process_step');
                    wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => [$accountCreationError]));
                    return;
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
                $hasPharmacyDetails = ($booking['custom_fields']['cf_cVndXX2e'] ?? false) || ($booking['custom_fields']['cf_pharmacy_phone'] ?? false);
                $hasPrescriptionDetails = ($booking['custom_fields']['cf_iAoOucDc'] ?? false) || ($booking['custom_fields']['cf_prescription_dosage'] ?? false);
                if ((($booking['custom_fields']['cf_4zkIbeeY'] ?? false) == 'Prescription renewal') && !($hasPharmacyDetails || $hasPrescriptionDetails)) {
                    remove_all_actions('latepoint_process_step');
                    wp_send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => ['At least one of the fields (pharmacy name, phone number, prescription name, or dosage) is required for Prescription renewal.']));
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

    protected function validateAgent30AppUserCredentials($username, $password, &$appUser = null)
    {
        $appUser = null;
        $username = trim((string)$username);
        $password = (string)$password;
        if ($username === '' || $password === '') {
            return 'Please enter username and password.';
        }

        $tableName = $this->resolveAgent30AppUsersTable();
        if ($tableName === '') {
            return 'Login service is temporarily unavailable. Please request without login.';
        }

        $user = $this->findAgent30AppUserByUsername($tableName, $username);
        if (!$user) {
            return 'Invalid username or password.';
        }
        if (!$this->isAgent30AppUserActive($user)) {
            return 'Your account is inactive. Please request without login.';
        }

        $storedPassword = '';
        if (isset($user['password_hash'])) {
            $storedPassword = (string)$user['password_hash'];
        } elseif (isset($user['password'])) {
            $storedPassword = (string)$user['password'];
        }
        if ($storedPassword === '') {
            return 'Invalid username or password.';
        }
        if (!$this->verifyAgent30AppUserPassword($password, $storedPassword)) {
            return 'Invalid username or password.';
        }

        $appUser = $user;
        $this->touchAgent30AppUserLastLogin($tableName, $user);
        return null;
    }

    protected function maybeCreateAgent30AppUser($bookingObject, $booking, $customFieldsData)
    {
        if (intval($bookingObject->agent_id ?? 0) !== 30) {
            return null;
        }
        if (method_exists($this, 'isAgent30CreateAccountEnabled') && !$this->isAgent30CreateAccountEnabled()) {
            return null;
        }

        $status = '';
        if (is_array($booking)) {
            $status = trim(strtolower((string)($booking['custom_fields']['gtd_login_status'] ?? '')));
        } elseif (is_object($booking) && isset($booking->custom_fields) && is_array($booking->custom_fields)) {
            $status = trim(strtolower((string)($booking->custom_fields['gtd_login_status'] ?? '')));
        }
        if ($status === '') {
            $status = 'not_login';
        }
        if ($status !== 'not_login') {
            return null;
        }

        $username = trim((string)($customFieldsData['cf_gtd_username'] ?? ''));
        if ($username === '') {
            return null;
        }
        if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $username)) {
            return 'Username must be 3-100 characters and can include letters, numbers, ".", "_" or "-".';
        }

        $tableName = $this->resolveAgent30AppUsersTable();
        if ($tableName === '') {
            return 'Account service is temporarily unavailable. Please continue without account creation.';
        }
        if ($this->findAgent30AppUserByUsername($tableName, $username)) {
            return 'Username is already in use. Please choose another username.';
        }

        $email = trim((string)($customFieldsData['email'] ?? ''));
        $phone = trim((string)($customFieldsData['phone'] ?? ''));
        if ($email === '') {
            return 'Email is required to create an account.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Email is not valid.';
        }

        $firstName = trim((string)($customFieldsData['cf_SdFSk6Tv'] ?? $customFieldsData['first_name'] ?? ''));
        $lastName = trim((string)($customFieldsData['cf_blm6LCcz'] ?? $customFieldsData['last_name'] ?? ''));
        $displayName = trim($firstName . ' ' . $lastName);
        if ($displayName === '') {
            $displayName = $username;
        }

        $plainPassword = wp_generate_password(12, true, false);
        $passwordHash = wp_hash_password($plainPassword);

        $insertData = $this->buildAgent30AppUserInsertData($tableName, [
            'name' => $displayName,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $passwordHash,
        ]);
        if (is_string($insertData)) {
            return $insertData;
        }

        global $wpdb;
        $inserted = $wpdb->insert($tableName, $insertData['data'], $insertData['formats']);
        if (!$inserted) {
            return 'Unable to create account. Please continue without login for now.';
        }

        $subject = 'Your Gotodoctor account has been created';
        $message = "Hello {$displayName},\n\n"
            . "Your account for gotodoctor.ca/cosefap has been created.\n\n"
            . "Username: {$username}\n"
            . "Temporary password: {$plainPassword}\n\n"
            . "Please sign in and change your password after login.\n\n"
            . "Need help? Call 1-833-820-8800.\n";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        $mailSent = wp_mail($email, $subject, $message, $headers);
        if (!$mailSent) {
            error_log(sprintf(
                '[latepoint-extend] Failed to send app_users password email. username=%s email=%s',
                $username,
                $email
            ));
        }

        return null;
    }

    protected function resolveAgent30AppUsersTable()
    {
        global $wpdb;

        $candidates = ['app_users'];
        if (!empty($wpdb->prefix)) {
            $candidates[] = $wpdb->prefix . 'app_users';
        }
        $candidates = array_values(array_unique(array_filter($candidates)));

        foreach ($candidates as $tableName) {
            if ($this->agent30AppUsersTableExists($tableName)) {
                return $tableName;
            }
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[latepoint-extend] resolveAgent30AppUsersTable failed. candidates=%s last_error=%s',
                json_encode($candidates),
                (string)$wpdb->last_error
            ));
        }

        return '';
    }

    protected function agent30AppUsersTableExists($tableName)
    {
        global $wpdb;

        if (!is_string($tableName) || $tableName === '') {
            return false;
        }

        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $tableName));
        if ($exists === $tableName) {
            return true;
        }

        $columns = $wpdb->get_results("SHOW COLUMNS FROM `{$tableName}`", ARRAY_A);
        if (is_array($columns) && !empty($columns)) {
            return true;
        }

        return false;
    }

    protected function findAgent30AppUserByUsername($tableName, $username)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM `{$tableName}` WHERE `username` = %s LIMIT 1", $username);
        $row = $wpdb->get_row($sql, ARRAY_A);
        if (!is_array($row) || empty($row)) {
            return null;
        }
        return $row;
    }

    protected function isAgent30AppUserActive($user)
    {
        if (!is_array($user)) {
            return false;
        }

        if (array_key_exists('deleted_at', $user) && trim((string)$user['deleted_at']) !== '') {
            return false;
        }

        if (array_key_exists('status', $user)) {
            return intval($user['status']) === 1;
        }

        if (array_key_exists('is_active', $user)) {
            $value = strtolower(trim((string)$user['is_active']));
            return !in_array($value, ['0', 'false', 'off', 'no'], true);
        }

        return true;
    }

    protected function verifyAgent30AppUserPassword($plainPassword, $storedPassword)
    {
        if ($storedPassword === '') {
            return false;
        }

        if (function_exists('password_verify') && password_verify($plainPassword, $storedPassword)) {
            return true;
        }

        if (function_exists('wp_check_password') && wp_check_password($plainPassword, $storedPassword)) {
            return true;
        }

        return hash_equals((string)$storedPassword, (string)$plainPassword);
    }

    protected function touchAgent30AppUserLastLogin($tableName, $user)
    {
        global $wpdb;

        if (!is_array($user) || empty($user['id'])) {
            return;
        }
        if (!array_key_exists('last_login_at', $user)) {
            return;
        }

        $wpdb->update(
            $tableName,
            ['last_login_at' => current_time('mysql')],
            ['id' => intval($user['id'])],
            ['%s'],
            ['%d']
        );
    }

    protected function buildAgent30AppUserInsertData($tableName, $values)
    {
        global $wpdb;

        $columns = $wpdb->get_results("SHOW COLUMNS FROM `{$tableName}`", ARRAY_A);
        if (!is_array($columns) || empty($columns)) {
            return 'Account table is not available.';
        }

        $columnMap = [];
        foreach ($columns as $column) {
            if (!isset($column['Field'])) continue;
            $columnMap[$column['Field']] = $column;
        }

        $data = [];
        $formats = [];
        $set = function ($field, $value, $format = '%s') use (&$data, &$formats, $columnMap) {
            if (!isset($columnMap[$field])) return;
            $data[$field] = $value;
            $formats[] = $format;
        };

        $set('name', (string)$values['name']);
        $set('username', (string)$values['username']);
        $set('email', (string)$values['email']);
        if (isset($columnMap['phone'])) {
            $set('phone', (string)$values['phone']);
        }
        if (isset($columnMap['password_hash'])) {
            $set('password_hash', (string)$values['password_hash']);
        } elseif (isset($columnMap['password'])) {
            $set('password', (string)$values['password_hash']);
        } else {
            return 'Account password column is missing.';
        }

        if (isset($columnMap['status'])) {
            $set('status', 1, '%d');
        } elseif (isset($columnMap['is_active'])) {
            $set('is_active', 1, '%d');
        }
        if (isset($columnMap['created_at'])) {
            $set('created_at', current_time('mysql'));
        }
        if (isset($columnMap['updated_at'])) {
            $set('updated_at', current_time('mysql'));
        }

        foreach (['project_id', 'partner_id'] as $foreignKey) {
            if (!isset($columnMap[$foreignKey])) continue;
            if (isset($data[$foreignKey])) continue;
            $columnInfo = $columnMap[$foreignKey];
            $nullable = strtoupper((string)($columnInfo['Null'] ?? 'NO')) === 'YES';
            $default = $columnInfo['Default'] ?? null;
            if ($default !== null && $default !== '') {
                $set($foreignKey, intval($default), '%d');
                continue;
            }
            if ($nullable) {
                continue;
            }

            $resolvedId = $this->resolveAgent30ForeignIdForAppUser($foreignKey);
            if (!$resolvedId) {
                return sprintf('Cannot resolve %s for app user creation.', $foreignKey);
            }
            $set($foreignKey, intval($resolvedId), '%d');
        }

        return ['data' => $data, 'formats' => $formats];
    }

    protected function resolveAgent30ForeignIdForAppUser($foreignKey)
    {
        global $wpdb;

        $targets = [];
        if ($foreignKey === 'partner_id') {
            $targets = ['app_partners', $wpdb->prefix . 'app_partners'];
        } elseif ($foreignKey === 'project_id') {
            $targets = ['projects', $wpdb->prefix . 'projects'];
        } else {
            return 0;
        }

        foreach ($targets as $tableName) {
            $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $tableName));
            if ($exists !== $tableName) continue;
            $id = intval($wpdb->get_var("SELECT id FROM `{$tableName}` ORDER BY id ASC LIMIT 1"));
            if ($id > 0) {
                return $id;
            }
            if ($foreignKey === 'partner_id') {
                $bootstrapId = $this->bootstrapAgent30DefaultPartner($tableName);
                if ($bootstrapId > 0) {
                    return $bootstrapId;
                }
            }
        }

        return 0;
    }

    protected function bootstrapAgent30DefaultPartner($tableName)
    {
        global $wpdb;

        if (!is_string($tableName) || $tableName === '') {
            return 0;
        }

        $columns = $wpdb->get_results("SHOW COLUMNS FROM `{$tableName}`", ARRAY_A);
        if (!is_array($columns) || empty($columns)) {
            return 0;
        }

        $columnMap = [];
        foreach ($columns as $column) {
            if (!isset($column['Field'])) continue;
            $columnMap[$column['Field']] = $column;
        }

        if (!isset($columnMap['name']) || !isset($columnMap['code'])) {
            return 0;
        }

        $data = [
            'name' => 'Default Partner',
            'code' => 'default-' . strtolower(wp_generate_password(8, false, false)),
        ];
        $formats = ['%s', '%s'];

        if (isset($columnMap['is_active'])) {
            $data['is_active'] = 1;
            $formats[] = '%d';
        }
        if (isset($columnMap['created_at'])) {
            $data['created_at'] = current_time('mysql');
            $formats[] = '%s';
        }
        if (isset($columnMap['updated_at'])) {
            $data['updated_at'] = current_time('mysql');
            $formats[] = '%s';
        }

        $inserted = $wpdb->insert($tableName, $data, $formats);
        if (!$inserted) {
            return 0;
        }

        return intval($wpdb->insert_id);
    }

    protected function shouldAuthorizeAgent30WithoutLogin($bookingObject, $booking)
    {
        if (intval($bookingObject->agent_id ?? 0) !== 30) {
            return false;
        }
        $loggedInCustomerId = intval(OsAuthHelper::get_logged_in_customer_id() ?: 0);
        $bookingCustomerId = intval($bookingObject->customer_id ?? 0);
        $loggedInCustomerExists = $this->agent30CustomerExists($loggedInCustomerId);
        $bookingCustomerExists = $this->agent30CustomerExists($bookingCustomerId);

        // Session can hold stale customer ids after DB restore/import.
        // If either context points to a missing customer record, rebuild customer auth from submitted fields.
        if (($loggedInCustomerId > 0 && !$loggedInCustomerExists) || ($bookingCustomerId > 0 && !$bookingCustomerExists)) {
            return true;
        }
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

    protected function agent30CustomerExists($customerId)
    {
        $customerId = intval($customerId);
        if ($customerId <= 0) {
            return false;
        }
        $customer = new OsCustomerModel($customerId);
        return intval($customer->id ?? 0) === $customerId;
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
        if ($email === '') {
            $email = trim((string)($customFieldsData['cf_PIl2UOoe'] ?? ''));
        }

        $phone = trim((string)($customFieldsData['phone'] ?? ''));
        if ($phone === '') {
            $phone = trim((string)$readValue($bookingParams, 'phone'));
        }
        if ($phone === '') {
            $phone = trim((string)$readValue($customerInput, 'phone'));
        }
        if ($phone === '') {
            $phone = trim((string)($customFieldsData['cf_PfyXBFfM'] ?? ''));
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }

        if (!$email && !$phone) {
            // Do not hard-fail booking flow on missing contact fields.
            // Generate a unique placeholder email for customer record creation.
            $email = 'agent30-' . time() . '-' . strtolower(wp_generate_password(6, false, false)) . '@example.com';
            error_log('[latepoint-extend] Agent30 customer fallback: generated placeholder email because no email/phone was provided.');
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
        if (intval($bookingObject->agent_id ?? 0) === 30) {
            // Agent30 flow does not collect customer custom fields.
            // Disable addon-level model validate hooks for this request to avoid false failures.
            remove_all_actions('latepoint_model_validate');
        }
        $customer->set_data($customerPayload);
        $saved = $customer->save();

        if (!$saved || !($customer->id ?? false)) {
            global $wpdb;
            $customerErrors = $customer->get_error_messages();
            $messages = [];
            if (is_array($customerErrors) && !empty($customerErrors)) {
                $messages = array_values(array_filter(array_map(function ($message) {
                    return is_scalar($message) ? trim((string)$message) : '';
                }, $customerErrors)));
            } elseif (is_string($customerErrors) && trim($customerErrors) !== '') {
                $messages = [trim($customerErrors)];
            }
            $dbError = trim((string)($wpdb->last_error ?? ''));

            error_log(sprintf(
                '[latepoint-extend] Agent30 customer save failed. saved=%d errors=%s db_error=%s payload=%s',
                $saved ? 1 : 0,
                wp_json_encode($messages),
                $dbError,
                wp_json_encode($customerPayload)
            ));

            if (!empty($messages)) {
                return implode(' ', $messages);
            }
            if ($dbError !== '') {
                return 'Unable to create customer record. ' . $dbError;
            }
            return 'Unable to create customer record. Please verify contact information and try again.';
        }

        OsAuthHelper::authorize_customer($customer->id);
        OsStepsHelper::$booking_object->customer_id = $customer->id;
        $bookingObject->customer_id = $customer->id;
        $_POST['booking']['customer_id'] = $customer->id;
        $_POST['customer_id'] = $customer->id;

        if (intval($bookingObject->agent_id ?? 0) === 30) {
            error_log(sprintf(
                '[latepoint-extend] Agent30 customer authorized from custom_fields. customer_id=%d',
                intval($customer->id)
            ));
        }

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
