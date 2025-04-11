<?php
trait SetFieldTrait
{
    public function setField($bookingObject)
    {
        switch (true) {
            case $this->covid || $bookingObject->service_id == 10:
                $this->_fields('covid');
                break;
            case $bookingObject->agent_id == 6:
                //MB Blue Cross
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('mbcc');
                else
                    $fields = $this->_fields('mbc');
                break;
            case $bookingObject->agent_id == 7:
                //Simply Benefits
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('sbc');
                else
                    $fields = $this->_fields('sb');
                break;
            case $bookingObject->agent_id == 8:
                //Quick health access
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('qhc');
                else
                    $fields = $this->_fields('qh');
                break;
            case $bookingObject->agent_id == 9:
                //AAS
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('aasc');
                else
                    $fields = $this->_fields('aas');
                break;
            case $bookingObject->agent_id == 10:
                //Partners
                if ($bookingObject->service_id == 13 || $bookingObject->service_id == 14)
                    $fields = $this->_fields('pc');
                else
                    $fields = $this->_fields('p');
                break;
            case $bookingObject->agent_id == 11:
                //Fabricland
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('fc');
                else
                    $fields = $this->_fields('fabricland');
                break;
            case $bookingObject->agent_id == 12:
                //Individual Navigation
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('ic');
                break;
            case $bookingObject->agent_id == 13:
                //Goto Health Wallet
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('gc');
                else
                    $fields = $this->_fields('gotohealthwallet');
                break;
            case $bookingObject->agent_id == 14:
                //Imperial Capital
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('imcc');
                else
                    $fields = $this->_fields('imc');
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
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('sebc');
                else
                    $fields = $this->_fields('seb');
                break;
            case $bookingObject->agent_id == 18:
                //Union Benefits
                if ($bookingObject->service_id == 13)
                    $fields = $this->_fields('ubc');
                else
                    $fields = $this->_fields('ub');
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
                $fields = $this->_fields('sp', false, compact('certKey'));
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
        return [
            'mbc' => $this->getMbcFields(),
            'mbcc' => $this->getMbccFields(),
            'sb' => $this->getSbFields(),
            'sbc' => $this->getSbFields(),
            'qh' => $this->getQhFields(),
            'qhc' => $this->getQhcFields(),
            'p' => $this->getPFields(),
            'pc' => $this->getPcFields(),
            'gotohealthwallet' => $this->getGotohealthwalletFields(),
            'gc' => $this->getGcFields(),
            'aas' => $this->getAasFields(),
            'aasc' => $this->getAascFields(),
            'fabricland' => $this->getFabriclandFields(),
            'fc' => $this->getFcFields(),
            'imc' => $this->getImcFields(),
            'imcc' => $this->getImccFields(),
            'ic' => $this->getIcFields(),
            'cbp' => $this->getCbpFields(),
            'cbpc' => $this->getCbpcFields(),
            'cbpe' => $this->getCbpeFields(),
            'seb' => $this->getSebFields(),
            'sebc' => $this->getSebcFields(),
            'ub' => $this->getUbFields(), 
            'ubc' => $this->getUbcFields(),
            'lg' => $this->getLgFields(),
            'vpi' => $this->getVpiFields(),
            'cc' => $this->getCcFields(),
            'sp' => $this->getSpFields($options),
            'located' => ['show' => ['cf_6A3SfgET']],
            'locatedOther' => ['show' => ['cf_6A3SfgET']],
            'needRenew' => ['show' => ['cf_NeRenew0', 'cf_NeRenew1', 'cf_NeRenew2', 'cf_NeRenew3', 'cf_NeRenew4', 'cf_NeRenew5', 'cf_NeRenew6']],
            'covid' => ['show' => ['cf_GiVH6tot', 'cf_7MZNhPC6', 'cf_4aFGjt5V', 'cf_E6XolZDI']],
            'returning' => ['show' => ['cf_WFHtiGvf', 'cf_ZoXsdwEZ']],
            'returningOnly' => ['show' => ['cf_DrKevcqV', 'cf_4zkIbeeY', 'cf_NVByvyYw', 'cf_cVndXX2e', 'cf_iAoOucDc']],
            'careServices' => ['show' => ['cf_DQ70wnRG']],
            'isGTD' => ['show' => ['cf_Presc1_0', 'cf_Presc2_0', 'cf_Presc3_0', 'cf_Presc3_1', 'cf_Presc3_2']],
        ];
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
     * Get MBC field definitions
     * 
     * @return array Field definitions
     */
    protected function getMbcFields()
    {
        return [
            'show' => ['cf_qOqKhbly', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields(),
            'merge' => [
                'cf_x18jr0Vf' => [
                    'label' => __('Has the patient used or registered with GotoDoctor or Enhanced Care Clinic before?', 'latepoint'),
                ],
            ]
        ];
    }

    /**
     * Get MBCC field definitions
     * 
     * @return array Field definitions
     */
    protected function getMbccFields()
    {
        return [
            'show' => ['cf_qOqKhbly', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => [
                'cf_x18jr0Vf' => [
                    'label' => __('Has the patient used or registered with GotoDoctor or Enhanced Care Clinic before?', 'latepoint'),
                ],
                'cf_6A3SfgET' => [
                    'label' => __('Where are you or the client currently located?', 'latepoint'),
                ],
            ]
        ];
    }

    /**
     * Get Simply Benefits field definitions
     * 
     * @return array Field definitions
     */
    protected function getSbFields()
    {
        return [
            'show' => ['cf_Vin78Day', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Simply Benefits Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getSbcFields()
    {
        return [
            'show' => ['cf_Vin78Day', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get Quick Health Access field definitions
     * 
     * @return array Field definitions
     */
    protected function getQhFields()
    {
        return [
            'show' => ['cf_SIt7Zefo', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Quick Health Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getQhcFields()
    {
        return [
            'show' => ['cf_SIt7Zefo', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get Partners field definitions
     * 
     * @return array Field definitions
     */
    protected function getPFields()
    {
        return [
            'show' => ['cf_SIt7Zefp', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Partners Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getPcFields()
    {
        return [
            'show' => ['cf_SIt7Zefp', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get GotoHealthWallet field definitions
     * 
     * @return array Field definitions
     */
    protected function getGotohealthwalletFields()
    {
        return [
            'show' => ['cf_P56xPUO5', 'cf_XlAxtIqB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Goto Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getGcFields()
    {
        return [
            'show' => ['cf_P56xPUO5', 'cf_XlAxtIqB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get AAS field definitions
     * 
     * @return array Field definitions
     */
    protected function getAasFields()
    {
        return [
            'show' => ['cf_WzbhG9eB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get AASC field definitions
     * 
     * @return array Field definitions
     */
    protected function getAascFields()
    {
        return [
            'show' => ['cf_WzbhG9eB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get Fabricland field definitions
     * 
     * @return array Field definitions
     */
    protected function getFabriclandFields()
    {
        return [
            'show' => ['cf_pnWPrUIe', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Fabricland Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getFcFields()
    {
        return [
            'show' => ['cf_pnWPrUIe', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get Imperial Capital field definitions
     * 
     * @return array Field definitions
     */
    protected function getImcFields()
    {
        return [
            'show' => ['cf_W0iZRLtG', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Imperial Capital Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getImccFields()
    {
        return [
            'show' => ['cf_W0iZRLtG', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get Individual Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getIcFields()
    {
        return [
            'show' => ['cf_6A3SfgET', 'cf_sBJs0cqR', 'cf_zZbexFje', 'cf_DQ70wnRG'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get CB Providers field definitions
     * 
     * @return array Field definitions
     */
    protected function getCbpFields()
    {
        return [
            'show' => ['cf_4wVF2U9Y', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get CB Providers Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getCbpcFields()
    {
        return [
            'show' => ['cf_4wVF2U9Y', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get CB Providers Emergency field definitions
     * 
     * @return array Field definitions
     */
    protected function getCbpeFields()
    {
        $fields = $this->getCbpcFields();
        
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
     * Get SEB field definitions
     * 
     * @return array Field definitions
     */
    protected function getSebFields()
    {
        return [
            'show' => ['cf_aku1T075', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get SEB Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getSebcFields()
    {
        return [
            'show' => ['cf_aku1T075', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get Union Benefits field definitions
     * 
     * @return array Field definitions
     */
    protected function getUbFields()
    {
        return [
            'show' => ['cf_QBLBYjS8', 'cf_6A3SfgET', 'cf_dREtrHWr', 'cf_Yf3KvptS'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Union Benefits Care field definitions
     * 
     * @return array Field definitions
     */
    protected function getUbcFields()
    {
        return [
            'show' => ['cf_QBLBYjS8', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(true),
            'add' => array_merge(
                $this->getStandardPersonFields(true),
                $this->getClientContactFields()
            ),
            'merge' => $this->getStandardClientMerges()
        ];
    }

    /**
     * Get Leslie Group field definitions
     * 
     * @return array Field definitions
     */
    protected function getLgFields()
    {
        return [
            'show' => ['cf_AYVpjhpP', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get VPI field definitions
     * 
     * @return array Field definitions
     */
    protected function getVpiFields()
    {
        return [
            'show' => ['cf_9OaDIkYh', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get Cleveland Clinic field definitions
     * 
     * @return array Field definitions
     */
    protected function getCcFields()
    {
        return [
            'show' => ['cf_yjnZIZ1D', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => $this->getStandardHideFields(),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Get SP field definitions
     * 
     * @param array $options Options with certKey
     * @return array Field definitions
     */
    protected function getSpFields($options)
    {
        return [
            'show' => [$options['certKey'] ?? false, 'cf_6A3SfgET', 'cf_sBJs0cqR'],
            'hide' => array_merge(['email'], $this->getStandardHideFields()),
            'add' => $this->getStandardPersonFields()
        ];
    }

    /**
     * Generate JavaScript for rules
     *
     * @param string $funcFields Function name for fields
     * @param string $funcRules Function name for rules
     * @return string JavaScript code
     */
    public function rulesJs($funcFields = 'prescriptionFields', $funcRules = 'prescriptionRules')
    {
        static $jsNamespaceIncluded = false;
        
        $keys = $this->$funcFields(true);
        $ids = array_map(function ($key) {
            return 'booking_custom_fields_' . strtolower($key);
        }, $keys);
        $fields = array_combine($keys, $ids);
        $init = array_slice($keys, 0, -2);
        $rules = $this->$funcRules();

        $fieldsJs = json_encode($fields);
        $initJs = json_encode(array_values($init));
        $rulesJs = json_encode($rules);
        
        // Generate a unique ID for this instance
        $instanceId = md5($funcFields . $funcRules);
        
        $namespaceJs = '';
        if (!$jsNamespaceIncluded) {
            // Only include the namespace definition once
            $namespaceJs = <<<JS
    // Define functions only once using a namespace approach
    if (typeof window.GtdTraitHelpers === 'undefined') {
        window.GtdTraitHelpers = {
            toggleFields: function(list, action, fields) {
                list.forEach(function(field) {
                    var f = $('#' + fields[field] || field);
                    if (action === 'hide') {
                        f.closest('.os-form-group').hide();
                        f.closest('.os-form-group').siblings('#preferred_pharamcy_label').hide();
                        f.prop('required', false);
                    } else {
                        f.closest('.os-form-group').show();
                        f.closest('.os-form-group').siblings('#preferred_pharamcy_label').show();
                        f.prop('required', true);
                    }
                });
            },
            
            bindRule: function(selector, list, checkRuleFn) {
                // Remove any existing event handler first
                $('body').off('change.gtdtrait', selector);
                $('body').on('change.gtdtrait', selector, function() {
                    list.forEach(function(field) {
                        checkRuleFn(field);
                    });
                });
            },
            
            createCheckRuleFn: function(rules, fields) {
                return function(field) {
                    var ruleSets = rules[field];
                    var match = false;

                    ruleSets.forEach(function(rule) {
                        var ruleMatch = true;
                        for (var key in rule) {
                            var value = rule[key];
                            var f = $('#' + fields[key]);
                            if (value.startsWith('!=')) {
                                value = value.substring(2);
                                if (!f.val() || (f.val() === value)) {
                                    ruleMatch = false;
                                    break;
                                }
                            } else {
                                if (f.val() !== value) {
                                    ruleMatch = false;
                                    break;
                                }
                            }
                        }
                        if (ruleMatch) {
                            match = true;
                        }
                    });

                    if (match) {
                        window.GtdTraitHelpers.toggleFields([fields[field]], 'show', fields);
                    } else {
                        window.GtdTraitHelpers.toggleFields([fields[field]], 'hide', fields);
                    }
                };
            }
        };
    }
JS;
            $jsNamespaceIncluded = true;
        }
        
        return <<<JS
<script>
jQuery(document).ready(function($) {
$namespaceJs
    
    // Each call to rulesJs creates its own instance
    var instanceId = '$instanceId';
    var hiddenFields = $initJs;
    var fields = $fieldsJs;
    var rules = $rulesJs;
    
    // Use the singleton functions from our namespace
    window.GtdTraitHelpers.toggleFields(hiddenFields, 'hide', fields);
    
    var checkRule = window.GtdTraitHelpers.createCheckRuleFn(rules, fields);
    
    for (var key in rules) {
        checkRule(key);

        rules[key].forEach(function(rule) {
            for (var field in rule) {
                window.GtdTraitHelpers.bindRule('#' + fields[field], [key], checkRule);
            }
        });
    }

    // Only add this once
    if (!$('#preferred_pharamcy_label').length) {
        $('#booking_custom_fields_cf_presc3_0').closest('.os-form-group').before('<div id="preferred_pharamcy_label" class="os-form-group os-form-select-group os-form-group-transparent" style="margin-bottom: 0 !important;"><label>Preferred pharmacy</label></div>');
    }
});
</script>
JS;
    }
}
