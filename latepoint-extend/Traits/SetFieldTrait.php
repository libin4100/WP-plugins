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
                $fields = $this->_fields('sp');
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

    protected function _fields($type = false, $reset = false)
    {
        static $cfBooking;
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
                    ],
                    'merge' => [
                        'cf_x18jr0Vf' => [
                            'label' => __('Has the patient used or registered with GotoDoctor or Enhanced Care Clinic before?', 'latepoint'),
                        ],
                    ]
                ],
                'mbcc' => [
                    'show' => ['cf_qOqKhbly', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                            'label' => __('Has the patient used or registered with GotoDoctor or Enhanced Care Clinic before?', 'latepoint'),
                        ],
                        'cf_6A3SfgET' => [
                            'label' => __('Where are you or the client currently located?', 'latepoint'),
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
                'gotohealthwallet' => [
                    'show' => ['cf_P56xPUO5', 'cf_XlAxtIqB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'gc' => [
                    'show' => ['cf_P56xPUO5', 'cf_XlAxtIqB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'aasc' => [
                    'show' => ['cf_WzbhG9eB', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'fabricland' => [
                    'show' => ['cf_pnWPrUIe', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'fc' => [
                    'show' => ['cf_pnWPrUIe', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'imc' => [
                    'show' => ['cf_W0iZRLtG', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'imcc' => [
                    'show' => ['cf_W0iZRLtG', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'ic' => [
                    'show' => ['cf_6A3SfgET', 'cf_sBJs0cqR', 'cf_zZbexFje', 'cf_DQ70wnRG'],
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
                'cbp' => [
                    'show' => ['cf_4wVF2U9Y', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'cbpc' => [
                    'show' => ['cf_4wVF2U9Y', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'cbpe' => [
                    'show' => ['cf_4wVF2U9Y', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                    'hide' => [
                        'cf_hbCNgimu',
                        'cf_zDS7LUjv',
                        'cf_H7MIk6Kt',
                        'cf_nxwjDAcZ',
                    ],
                    'add' => [
                        'emergency' => [
                            'label' => __('Are you experiencing a life-threatening emergency or require immediate medical attention?', 'latepoint'),
                            'placeholder' => __('---Please Select---', 'latepoint'),
                            'type' => 'select',
                            'width' => 'os-col-12',
                            'visibility' => 'public',
                            'options' => "Yes\nNo",
                            'required' => 'on',
                            'id' => 'emergency',
                        ],
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
                'seb' => [
                    'show' => ['cf_aku1T075', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'sebc' => [
                    'show' => ['cf_aku1T075', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'ub' => [
                    'show' => ['cf_QBLBYjS8', 'cf_6A3SfgET', 'cf_dREtrHWr', 'cf_Yf3KvptS'],
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
                'ubc' => [
                    'show' => ['cf_QBLBYjS8', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'lg' => [
                    'show' => ['cf_AYVpjhpP', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'vpi' => [
                    'show' => ['cf_9OaDIkYh', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'cc' => [
                    'show' => ['cf_yjnZIZ1D', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
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
                'sp' => [
                    'show' => ['cf_9e1mhF4v', 'cf_6A3SfgET', 'cf_sBJs0cqR'],
                    'hide' => [
                        'email',
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
                'returning' => ['show' => ['cf_WFHtiGvf', 'cf_ZoXsdwEZ', 'cf_NeRenew0', 'cf_NeRenew1', 'cf_NeRenew2', 'cf_NeRenew3', 'cf_NeRenew4', 'cf_NeRenew5', 'cf_NeRenew6']],
                'returningOnly' => ['show' => ['cf_DrKevcqV', 'cf_4zkIbeeY', 'cf_NVByvyYw', 'cf_cVndXX2e', 'cf_iAoOucDc']],
                'careServices' => ['show' => ['cf_DQ70wnRG']],
                'isGTD' => ['show' => ['cf_Presc1_0', 'cf_Presc2_0', 'cf_Presc3_0', 'cf_Presc3_1', 'cf_Presc3_2']],
            ];
            $hideField = ($onSave ?? false) ? 'public' : 'hidden';
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
}
