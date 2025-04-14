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
                    OsSettingsHelper::$loaded_values['custom_fields_for_customer'] = json_encode($values);
                }
            }
        }
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
            'needRenew' => ['show' => ['cf_NeRenew0', 'cf_NeRenew1', 'cf_NeRenew2', 'cf_NeRenew3', 'cf_NeRenew4', 'cf_NeRenew5', 'cf_NeRenew6']],
            'covid' => ['show' => ['cf_GiVH6tot', 'cf_7MZNhPC6', 'cf_4aFGjt5V', 'cf_E6XolZDI']],
            'returning' => ['show' => ['cf_WFHtiGvf', 'cf_ZoXsdwEZ']],
            'returningOnly' => ['show' => ['cf_DrKevcqV', 'cf_4zkIbeeY', 'cf_NVByvyYw', 'cf_cVndXX2e', 'cf_iAoOucDc']],
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
            'cc' => $this->createProviderField('cf_yjnZIZ1D'),
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
}
