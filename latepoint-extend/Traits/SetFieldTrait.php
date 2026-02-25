<?php
trait SetFieldTrait
{
    public function setField($bookingObject)
    {
        // Original switch case for service providers
        switch (true) {
            case $this->covid || $bookingObject->service_id == 10:
                $this->_fields('covid');
                break;
            case $bookingObject->agent_id == 6:
                //MB Blue Cross
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'mbcc' : 'mbc');
                break;
            case $bookingObject->agent_id == 7:
                //Simply Benefits
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'sbc' : 'sb');
                break;
            case $bookingObject->agent_id == 8:
                //Quick health access
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'qhc' : 'qh');
                break;
            case $bookingObject->agent_id == 9:
                //AAS
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'aasc' : 'aas');
                break;
            case $bookingObject->agent_id == 10:
                //Partners
                $fields = $this->_fields(($bookingObject->service_id == 13 || $bookingObject->service_id == 14) ? 'pc' : 'p');
                break;
            case $bookingObject->agent_id == 11:
                //Fabricland
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'fc' : 'fabricland');
                break;
            case $bookingObject->agent_id == 12:
                //Individual Navigation
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('ic');
                break;
            case $bookingObject->agent_id == 13:
                //Goto Health Wallet
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'gc' : 'gotohealthwallet');
                break;
            case $bookingObject->agent_id == 14:
                //Imperial Capital
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'imcc' : 'imc');
                break;
            case $bookingObject->agent_id == 15:
                //CB Providers
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('cbpc');
                elseif ($bookingObject->service_id == 15)
                    $fields = $this->_fields('cbpe');
                else
                    $fields = $this->_fields('cbp');
                break;
            case $bookingObject->agent_id == 16:
                //SEB
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'sebc' : 'seb');
                break;
            case $bookingObject->agent_id == 18:
                //Union Benefits
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'ubc' : 'ub');
                break;
            case $bookingObject->agent_id == 19:
                // Leslie Group
                $fields = $this->_fields('lg');
                break;
            case $bookingObject->agent_id == 20:
                // VPI
                $fields = $this->_fields('vpi');
                break;
            case $bookingObject->agent_id == 21:
                // Cleveland Clinic
                $fields = $this->_fields('cc');
                break;
            case $bookingObject->agent_id == 22:
                // Hunters
                $certKey = 'cf_9e1mhF4v';
                $fields = $this->_fields('sp', false, compact('certKey'));
                break;
            case $bookingObject->agent_id == 23:
                // CPSM
                $certKey = 'cf_zdwWTAsg';
                $care = $bookingObject->service_id == 13;
                $fields = $this->_fields('sp', false, compact('certKey', 'care'));
                break;
            case $bookingObject->agent_id == 24:
                // Asylum
                $certKey = 'cf_FRhzp65m';
                $care = $bookingObject->service_id == 13;
                $fields = $this->_fields('sp', false, compact('certKey', 'care'));
                break;
            case $bookingObject->agent_id == 25:
                // LOGO 711
                $certKey = 'cf_lbBtEi3k';
                $care = $bookingObject->service_id == 13;
                $fields = $this->_fields('sp', false, compact('certKey', 'care'));
                break;
            case $bookingObject->agent_id == 28:
                // MGT
                $certKey = 'cf_EwHB7H3K';
                $care = $bookingObject->service_id == 13;
                $fields = $this->_fields('sp', false, compact('certKey', 'care'));
                break;
            case $bookingObject->agent_id == 29:
                // SEB/BestBuy
                $fields = $this->_fields($bookingObject->service_id == 13 ? 'bestbuyc' : 'bestbuy');
                break;
            case $bookingObject->agent_id == 30:
                // GTD dedicated flow
                $this->setAgent30Fields($bookingObject);
                return;
            case in_array($bookingObject->service_id, [2, 3]):
                $this->_fields('located');
                break;
            case in_array($bookingObject->service_id, [7, 8]):
                $this->_fields('locatedOther');
                break;
            default:
                $fields = $this->_fields('', true);
        }

        // Additional field processing
        if (in_array($bookingObject->service_id, [2, 3, 7, 8])) $this->_fields('needRenew');

        if ($this->returningExtra($bookingObject)) {
            $this->_fields('returning');
        }
        if ($bookingObject->service_id == 13) {
            $this->_fields('careServices');
        }
        if (($bookingObject->service_id != 13) && $this->isReturning()) {
            $this->_fields('returningOnly');
        }
        if ($bookingObject->service_id == 16) {
            $customFields = OsSettingsHelper::$loaded_values['custom_fields_for_booking'];
            $values = is_array($customFields) ? $customFields : json_decode($customFields, true);
            foreach ($values as $id => $val) {
                $values[$id]['visibility'] = 'hidden';
            }
            OsSettingsHelper::$loaded_values['custom_fields_for_booking'] = json_encode($values);

            $customFields = OsSettingsHelper::$loaded_values['custom_fields_for_customer'];
            $values = is_array($customFields) ? $customFields : json_decode($customFields, true);
            foreach ($values as $id => $val) {
                $values[$id]['visibility'] = 'hidden';
            }
            OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
        }
        if ($this->isGTD()) {
            $this->_fields('isGTD');
        }
    }

    protected function _fields($type = false, $reset = false, $options = [])
    {
        static $cfBooking;
        static $jsIncluded = false; // Track if JavaScript has been included
        
        $setting = new OsSettingsModel();
        if (!$cfBooking) {
            $cfBooking = $setting->where(['name' => 'custom_fields_for_booking'])->set_limit(1)->get_results_as_models();
            $cfCustomer = $setting->where(['name' => 'custom_fields_for_customer'])->set_limit(1)->get_results_as_models();
            if ($cfBooking)
                $customFields = $cfBooking->value;
        } else {
            $customFields = OsSettingsHelper::$loaded_values['custom_fields_for_booking'];
            $cfCustomer = new OsSettingsModel();
            $cfCustomer->value = OsSettingsHelper::$loaded_values['custom_fields_for_customer'];
        }

        if ($reset) {
            OsSettingsHelper::$loaded_values['custom_fields_for_booking'] = $cfBooking->value;
            OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = $cfCustomer->value;
        } else {
            $fields = $this->getFieldDefinitions($options);
            $hideField = ($onSave ?? false) ? 'public' : 'hidden';
            
            $values = is_array($customFields) ? $customFields : json_decode($customFields, true);
            if ($values && isset($fields[$type])) {
                foreach ($values as $id => $val) {
                    if (in_array($id ?? false, ($fields[$type]['hide'] ?? [])))
                        $values[$id]['visibility'] = $hideField;
                    if (in_array($id ?? false, ($fields[$type]['show'] ?? [])))
                        $values[$id]['visibility'] = 'public';

                    if (isset($fields[$type]['merge'][$id]))
                        $values[$id] = array_merge($values[$id], $fields[$type]['merge'][$id]);
                }
                $values = ($fields[$type]['add'] ?? []) + $values;
                OsSettingsHelper::$loaded_values['custom_fields_for_booking'] = json_encode($values);
            }
            
            if ($cfCustomer) {
                $values = is_array($cfCustomer->value) ? $cfCustomer->value : json_decode($cfCustomer->value, true);
                if ($values && isset($fields[$type])) {
                    foreach ($values as $id => $val) {
                        if (in_array($id ?? false, ($fields[$type]['hide'] ?? [])))
                            $values[$id]['visibility'] = $hideField;
                        if (in_array($id ?? false, ($fields[$type]['show'] ?? [])))
                            $values[$id]['visibility'] = 'public';

                        if (isset($fields[$type]['merge'][$id]))
                            $values[$id] = array_merge($values[$id], $fields[$type]['merge'][$id]);
                    }
                    $values = ($fields[$type]['addCustomer'] ?? []) + $values;
                    OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
                }
            }
        }
    }

    /**
     * Replace booking custom fields for agent 30 with a strict ordered list.
     */
    protected function setAgent30Fields($bookingObject = null)
    {
        // Start from original configured booking fields before any runtime mutations.
        $this->_fields('', true);

        $customFields = OsSettingsHelper::$loaded_values['custom_fields_for_booking'] ?? false;
        $values = is_array($customFields) ? $customFields : json_decode($customFields, true);
        if (!is_array($values)) {
            return;
        }

        $isLoginPath = $this->isAgent30LoginPath($bookingObject);
        $allowAccountCreation = method_exists($this, 'isAgent30CreateAccountEnabled')
            ? $this->isAgent30CreateAccountEnabled()
            : true;

        // flow.md:
        // - Login flow: show short list (emergency, request type, location, services).
        // - Not login: show full EAP field list.
        $agent30Fields = $isLoginPath
            ? [
                'cf_ipbMUSJA' => 'Are you experiencing a life-threatening emergency or require immediate medical attention?',
                'cf_UCfp8qZF' => 'Request Type',
                'email' => 'Email',
                'phone' => 'Phone',
                'cf_6A3SfgET' => 'Where are you or patient currently located?',
                'cf_eDaxd83r' => 'Type to select an option or enter a new value',
            ]
            : [
                'cf_ipbMUSJA' => 'Are you experiencing a life-threatening emergency or require immediate medical attention?',
                'cf_UCfp8qZF' => 'Request Type',
                'cf_pv1s5ZMZ' => 'Employee ID',
                'cf_QuZqWccH' => 'Who is this booking for?',
                'cf_cYhjctjz' => 'Select relation',
                'cf_SdFSk6Tv' => 'Patient First Name',
                'cf_blm6LCcz' => 'Patient Last Name',
                'cf_WFHtiGvf' => 'Date of Birth',
                'cf_ZoXsdwEZ' => 'HIN',
                'email' => 'Email',
                'phone' => 'Phone',
                'cf_6A3SfgET' => 'Where are you or patient currently located?',
                'cf_eDaxd83r' => 'Type to select an option or enter a new value',
                'cf_khYzMsWi' => 'Alternate Contact Information (if different from above)',
                'cf_fPU4Ka1m' => 'Contact person name',
                'cf_PfyXBFfM' => 'Preferred contact phone number',
                'cf_PIl2UOoe' => 'Email address',
                'cf_Fk9ih4Et' => 'Additional Info on your request.',
                'cf_gtd_create_account' => 'Do you want to create an account?',
                'cf_gtd_username' => 'Username',
            ];

        if (!$allowAccountCreation) {
            unset($agent30Fields['cf_gtd_create_account'], $agent30Fields['cf_gtd_username']);
        }

        $ordered = [];
        foreach ($agent30Fields as $fieldId => $label) {
            if (!isset($values[$fieldId]) || !is_array($values[$fieldId])) {
                if (in_array($fieldId, ['email', 'phone', 'cf_gtd_create_account', 'cf_gtd_username'], true)) {
                    $fieldType = 'text';
                    $fieldOptions = '';
                    $required = 'on';
                    $width = 'os-col-6';
                    if ($fieldId === 'cf_gtd_create_account') {
                        $fieldType = 'checkbox';
                        $fieldOptions = '';
                        $required = 'off';
                        $width = 'os-col-12';
                    } elseif ($fieldId === 'cf_gtd_username') {
                        $required = 'off';
                        $width = 'os-col-12';
                    }
                    $values[$fieldId] = [
                        'id' => $fieldId,
                        'type' => $fieldType,
                        'width' => $width,
                        'options' => $fieldOptions,
                        'placeholder' => __($label, 'latepoint'),
                        'required' => $required,
                        'visibility' => 'public',
                    ];
                } else {
                    continue;
                }
            }

            $extra = [];
            if ($fieldId === 'cf_ipbMUSJA') {
                $extra = [
                    'type' => 'select',
                    'options' => "Yes\nNo",
                    'placeholder' => __('---Please Select---', 'latepoint'),
                    'required' => 'on',
                ];
            } elseif ($fieldId === 'cf_UCfp8qZF') {
                $extra = [
                    'type' => 'select',
                    'options' => "New Request\nFollowup",
                    'placeholder' => __('---Please Select---', 'latepoint'),
                    'required' => 'on',
                ];
            } elseif ($fieldId === 'cf_QuZqWccH') {
                $extra = [
                    'type' => 'select',
                    'options' => "Myself\nFamily member",
                    'placeholder' => __('---Please Select---', 'latepoint'),
                    'required' => 'on',
                ];
            } elseif ($fieldId === 'cf_cYhjctjz') {
                $extra = [
                    'type' => 'select',
                    'placeholder' => __('---Please Select---', 'latepoint'),
                ];
            } elseif ($fieldId === 'cf_6A3SfgET') {
                $extra = [
                    'required' => 'on',
                ];
            } elseif ($fieldId === 'email') {
                $extra = [
                    'type' => 'text',
                    'required' => 'on',
                    'placeholder' => __('Email', 'latepoint'),
                ];
            } elseif ($fieldId === 'phone') {
                $extra = [
                    'type' => 'text',
                    'required' => 'on',
                    'placeholder' => __('Phone', 'latepoint'),
                ];
            } elseif ($fieldId === 'cf_gtd_create_account') {
                $extra = [
                    'type' => 'checkbox',
                    'required' => 'off',
                ];
            } elseif ($fieldId === 'cf_gtd_username') {
                $extra = [
                    'type' => 'text',
                    'required' => 'off',
                    'placeholder' => __('Username', 'latepoint'),
                ];
            } elseif ($fieldId === 'cf_eDaxd83r') {
                $extra = [
                    'type' => 'select',
                    'options' => implode("\n", $this->getServiceList()),
                    'placeholder' => __('---Select services---', 'latepoint'),
                    'required' => 'on',
                ];
            }

            $ordered[$fieldId] = array_merge(
                $values[$fieldId],
                [
                    'id' => $fieldId,
                    'label' => __($label, 'latepoint'),
                    'visibility' => 'public',
                ],
                $extra
            );
        }

        OsSettingsHelper::$loaded_values['custom_fields_for_booking'] = json_encode($ordered);
    }

    protected function isAgent30LoginPath($bookingObject = null)
    {
        if (method_exists($this, 'isAgent30LoginPageEnabled') && !$this->isAgent30LoginPageEnabled()) {
            return false;
        }
        $booking = OsParamsHelper::get_param('booking');
        $loginStatus = trim((string)($booking['custom_fields']['gtd_login_status'] ?? ''));
        if ($loginStatus === '' && $bookingObject && isset($bookingObject->custom_fields['gtd_login_status'])) {
            $loginStatus = trim((string)$bookingObject->custom_fields['gtd_login_status']);
        }

        return in_array(strtolower($loginStatus), ['login', 'logged_in', 'yes', 'true', '1'], true);
    }

    /**
     * Normal service list used by agent 30 and QHC service step.
     */
    protected function getServiceList()
    {
        return [
            "Abuse",
            "Addiction - Sex",
            "Addiction - Alcohol / Drugs",
            "Addiction - Gambling",
            "Anger management",
            "Anxiety",
            "Autism Spectrum Disorder / Developmental Concerns",
            "Attachment",
            "Behaviour management",
            "Brain injuries",
            "Bullying",
            "Burnout",
            "Communication concerns",
            "Complex trauma",
            "Conflict resolution",
            "Co-parenting",
            "Cultural concerns",
            "Depression",
            "Disability Management and understanding new diagnosis",
            "Domestic violence",
            "Elderly care",
            "Eating disorders",
            "Emotional regulation",
            "Finances",
            "Grief and loss",
            "Guilt / Shame",
            "Gender",
            "General Support",
            "Health issues",
            "Residential School survivors",
            "Intergenerational trauma",
            "Immigration and refugee",
            "Mediation / Divorce",
            "Medical Diagnosis",
            "Men's issues",
            "Mental health diagnosis incl. bipolar, schizophrenia, OCD, ODD, BPD",
            "Pain management",
            "Panic attacks",
            "Parent/ teen/ child conflict",
            "Parenting",
            "Phobias",
            "Post partem",
            "Problem solving",
            "PTSD",
            "Post traumatic stress disorder",
            "Religion / spiritual",
            "Relationships",
            "Self-esteem",
            "Suicide",
            "Self Harm",
            "Sexuality",
            "Sexual abuse",
            "Stress",
            "Sport and performance",
            "Women's issues (incl. menopause)",
            "Work life balance",
            "Work related issues",
        ];
    }

    /**
     * Get field definitions for all field types
     * 
     * @param array $options Additional options
     * @return array Field definitions
     */
    protected function getFieldDefinitions($options = [])
    {
        $specialFieldDefs = [
            'located' => ['show' => ['cf_6A3SfgET']],
            'locatedOther' => ['show' => ['cf_6A3SfgET']],
            'needRenew' => ['show' => ['cf_NeRenew0', 'cf_NeRenew1', 'cf_NeRenew2', 'cf_NeRenew3']],
            'covid' => ['show' => ['cf_GiVH6tot', 'cf_7MZNhPC6', 'cf_4aFGjt5V', 'cf_E6XolZDI']],
            'returning' => ['show' => ['cf_WFHtiGvf', 'cf_ZoXsdwEZ']],
            'returningOnly' => [
                'show' => [
                    'cf_DrKevcqV',
                    'cf_4zkIbeeY',
                    'cf_NVByvyYw',
                    'cf_cVndXX2e',
                    'cf_pharmacy_phone',
                    'cf_iAoOucDc',
                    'cf_prescription_dosage',
                ],
                'add' => $this->getPrescriptionRenewalSplitFields(),
                'addCustomer' => $this->getPrescriptionRenewalSplitFields(),
                'merge' => [
                    'cf_cVndXX2e' => [
                        'label' => __('Enter your pharmacy name', 'latepoint'),
                        'placeholder' => __('Enter your pharmacy name', 'latepoint'),
                        'width' => 'os-col-6',
                        'required' => 'off',
                    ],
                    'cf_iAoOucDc' => [
                        'label' => __('Prescription name', 'latepoint'),
                        'placeholder' => __('Prescription name', 'latepoint'),
                        'width' => 'os-col-6',
                        'required' => 'off',
                    ],
                ],
            ],
            'careServices' => ['show' => ['cf_DQ70wnRG']],
            'isGTD' => ['show' => ['cf_Presc1_0', 'cf_Presc2_0', 'cf_Presc3_0', 'cf_Presc3_1', 'cf_Presc3_2']],
            'sp' => $this->getSpFields($options),
        ];

        // Provider field definitions using a factory method
        $providerFieldDefs = [
            'mbc' => $this->createProviderField('cf_qOqKhbly', false, ['cf_x18jr0Vf' => ['label' => __('Has the patient used or registered with GotoDoctor or Enhanced Care Clinic before?', 'latepoint')]]),
            'mbcc' => $this->createCareProviderField('cf_qOqKhbly', ['cf_x18jr0Vf' => ['label' => __('Has the patient used or registered with GotoDoctor or Enhanced Care Clinic before?', 'latepoint')], 'cf_6A3SfgET' => ['label' => __('Where are you or the client currently located?', 'latepoint')]]),
            'sb' => $this->createProviderField('cf_Vin78Day'),
            'sbc' => $this->createCareProviderField('cf_Vin78Day'),
            'qh' => $this->createProviderField('cf_SIt7Zefo'),
            'qhc' => $this->createCareProviderField('cf_SIt7Zefo'),
            'p' => $this->createProviderField('cf_SIt7Zefp'),
            'pc' => $this->createCareProviderField('cf_SIt7Zefp'),
            'gotohealthwallet' => $this->createProviderField(['cf_P56xPUO5', 'cf_XlAxtIqB']),
            'gc' => $this->createCareProviderField(['cf_P56xPUO5', 'cf_XlAxtIqB']),
            'aas' => $this->createProviderField('cf_WzbhG9eB'),
            'aasc' => $this->createCareProviderField('cf_WzbhG9eB'),
            'fabricland' => $this->createProviderField('cf_pnWPrUIe'),
            'fc' => $this->createCareProviderField('cf_pnWPrUIe'),
            'imc' => $this->createProviderField('cf_W0iZRLtG'),
            'imcc' => $this->createCareProviderField('cf_W0iZRLtG'),
            'ic' => $this->createCareProviderField(['cf_6A3SfgET', 'cf_sBJs0cqR', 'cf_zZbexFje', 'cf_DQ70wnRG']),
            'cbp' => $this->createProviderField('cf_4wVF2U9Y'),
            'cbpc' => $this->createCareProviderField('cf_4wVF2U9Y'),
            'cbpe' => $this->createEmergencyProviderField('cf_4wVF2U9Y'),
            'seb' => $this->createProviderField('cf_aku1T075'),
            'sebc' => $this->createCareProviderField('cf_aku1T075'),
            'ub' => $this->createProviderField(['cf_QBLBYjS8', 'cf_6A3SfgET', 'cf_dREtrHWr', 'cf_Yf3KvptS']),
            'ubc' => $this->createCareProviderField('cf_QBLBYjS8'),
            'lg' => $this->createProviderField('cf_AYVpjhpP'),
            'vpi' => $this->createProviderField('cf_9OaDIkYh'),
            'cc' => $this->createProviderField(['cf_yjnZIZ1D', 'cf_sx8M50Pw', 'cf_VTXfH4Wq', 'cf_ZmLsfxFI', 'cf_fH4hcx29', 'cf_B7rj01VE', 'cf_nmfpde3f', 'cf_6NqyuLpc'], false, ['cf_6A3SfgET' => ['label' => __('Are you located in Quebec?', 'latepoint'), 'options' => "Yes\nNo"]]),
            'bestbuy' => $this->createProviderField('cf_ryf56IpW'),
            'bestbuyc' => $this->createCareProviderField('cf_ryf56IpW'),
        ];

        return array_merge($providerFieldDefs, $specialFieldDefs);
    }

    /**
     * Factory method to create a standard provider field definition
     * 
     * @param string|array $uniqueFields Field(s) unique to this provider
     * @param bool $includeExtra Whether to include extra fields
     * @param array $customMerge Any custom merge fields
     * @return array Provider field definition
     */
    protected function createProviderField($uniqueFields, $includeExtra = false, $customMerge = [])
    {
        if (!is_array($uniqueFields)) {
            $uniqueFields = [$uniqueFields];
        }
        
        $standardFields = ['cf_6A3SfgET', 'cf_sBJs0cqR'];
        $showFields = array_merge($uniqueFields, $standardFields);
        
        return [
            'show' => $showFields,
            'hide' => $this->getStandardHideFields($includeExtra),
            'add' => $this->getStandardPersonFields(),
            'merge' => $customMerge
        ];
    }

    /**
     * Factory method to create a care provider field definition
     * 
     * @param string|array $uniqueFields Field(s) unique to this provider
     * @param array $customMerge Any custom merge fields
     * @return array Care provider field definition
     */
    protected function createCareProviderField($uniqueFields, $customMerge = [])
    {
        $fields = $this->createProviderField($uniqueFields, true);
        
        $fields['add'] = array_merge(
            $this->getStandardPersonFields(true),
            $this->getClientContactFields()
        );
        
        $fields['merge'] = array_merge($this->getStandardClientMerges(), $customMerge);
        
        return $fields;
    }
    
    /**
     * Factory method to create an emergency provider field definition
     * 
     * @param string|array $uniqueFields Field(s) unique to this provider
     * @return array Emergency provider field definition 
     */
    protected function createEmergencyProviderField($uniqueFields)
    {
        $fields = $this->createCareProviderField($uniqueFields);
        
        // Add emergency field
        $emergencyField = [
            'emergency' => [
                'label' => __('Are you experiencing a life-threatening emergency or require immediate medical attention?', 'latepoint'),
                'placeholder' => __('---Please Select---', 'latepoint'),
                'type' => 'select',
                'width' => 'os-col-12',
                'visibility' => 'public',
                'options' => "Yes\nNo",
                'required' => 'on',
                'id' => 'emergency',
            ]
        ];
        
        $fields['add'] = $emergencyField + $fields['add'];
        
        return $fields;
    }

    /**
     * Creates standard field definitions for first and last name
     * 
     * @param bool $isClientFields Whether these are client fields or regular fields
     * @return array Field definitions
     */
    protected function getStandardPersonFields($isClientFields = false)
    {
        $prefix = $isClientFields ? 'Client ' : '';
        
        return [
            'first_name' => [
                'label' => __($prefix . 'First Name', 'latepoint'),
                'placeholder' => __($prefix . 'First Name', 'latepoint'),
                'type' => 'text',
                'width' => 'os-col-12',
                'visibility' => 'public',
                'options' => '',
                'required' => 'on',
                'id' => 'first_name'
            ],
            'last_name' => [
                'label' => __($prefix . 'Last Name', 'latepoint'),
                'placeholder' => __($prefix . 'Last Name', 'latepoint'),
                'type' => 'text',
                'width' => 'os-col-12',
                'visibility' => 'public',
                'options' => '',
                'required' => 'on',
                'id' => 'last_name'
            ]
        ];
    }

    /**
     * Creates client contact fields (phone, email)
     * 
     * @return array Field definitions
     */
    protected function getClientContactFields()
    {
        return [
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
            ]
        ];
    }

    /**
     * Get standard field merges for client-related fields
     * 
     * @return array Field merges
     */
    protected function getStandardClientMerges()
    {
        return [
            'cf_x18jr0Vf' => [
                'label' => __('Have you or client used GotoDoctor before?', 'latepoint'),
            ],
            'cf_6A3SfgET' => [
                'label' => __('Where are you or the client currently located?', 'latepoint'),
            ],
        ];
    }

    /**
     * Get standard hide fields
     * 
     * @param bool $includeExtra Whether to include extra hidden fields
     * @return array Hide fields
     */
    protected function getStandardHideFields($includeExtra = false)
    {
        $fields = [
            'cf_hbCNgimu',
            'cf_zDS7LUjv',
            'cf_H7MIk6Kt',
        ];
        
        if ($includeExtra) {
            $fields[] = 'cf_nxwjDAcZ';
        }
        
        return $fields;
    }

    /**
     * Get SP field definitions
     * 
     * @param array $options Options with certKey
     * @return array Field definitions
     */
    protected function getSpFields($options)
    {
        if ($options['care'] ?? false) {
            return $this->createCareProviderField($options['certKey'] ?? '');
        } else {
            return $this->createProviderField($options['certKey'] ?? '');
        }
    }

    /**
     * Split prescription renewal fields for pharmacy and prescription details.
     *
     * @return array Field definitions
     */
    protected function getPrescriptionRenewalSplitFields()
    {
        return [
            'cf_pharmacy_phone' => [
                'label' => __('Phone Number', 'latepoint'),
                'placeholder' => __('Phone Number', 'latepoint'),
                'type' => 'text',
                'width' => 'os-col-6',
                'visibility' => 'public',
                'options' => '',
                'required' => 'off',
                'id' => 'cf_pharmacy_phone',
            ],
            'cf_prescription_dosage' => [
                'label' => __('Dosage', 'latepoint'),
                'placeholder' => __('Dosage', 'latepoint'),
                'type' => 'text',
                'width' => 'os-col-6',
                'visibility' => 'public',
                'options' => '',
                'required' => 'off',
                'id' => 'cf_prescription_dosage',
            ],
        ];
    }
}
