<?php
trait GtdTrait
{
    public function prescriptionFields($prepend = false)
    {
        return array_merge([
            'cf_Presc1_0',
            'cf_Presc2_0',
            'cf_Presc3_0',
            'cf_Presc3_1',
            'cf_Presc3_2',
        ], $prepend ? ['cf_x18jr0Vf', 'cf_6A3SfgET'] : []);
    }

    public function prescriptionRules()
    {
        return [
            /**
             * Has the patient used Gotodoctor or Enhanced Care Clinic before? - Yes
             * Where is the patient currently located? - Ontario
             */
            'cf_Presc1_0' => [['cf_x18jr0Vf' => 'Yes', 'cf_6A3SfgET' => 'Ontario']],
            'cf_Presc2_0' => [['cf_Presc1_0' => 'No']],
            'cf_Presc3_0' => [
                ['cf_Presc2_0' => 'No'],
                ['cf_x18jr0Vf' => 'Yes', 'cf_6A3SfgET' => '!=Ontario']
            ],
            'cf_Presc3_1' => [
                ['cf_Presc2_0' => 'No'],
                ['cf_x18jr0Vf' => 'Yes', 'cf_6A3SfgET' => '!=Ontario']
            ],
            'cf_Presc3_2' => [
                ['cf_Presc2_0' => 'No'],
                ['cf_x18jr0Vf' => 'Yes', 'cf_6A3SfgET' => '!=Ontario']
            ],
        ];
    }

    public function validPrescription($customFields, $customFieldsForBookig)
    {
        return $this->validRules($customFields, $customFieldsForBookig, 'prescriptionRules');
    }

    public function validRules($customFields, $customFieldsForBookig, $funcRules = 'prescriptionRules')
    {
        $rules = $this->$funcRules();
        $errors = [];

        foreach ($rules as $field => $ruleSets) {
            $match = false;

            foreach ($ruleSets as $rule) {
                $ruleMatch = true;

                foreach ($rule as $key => $val) {
                    if (strpos($val, '!=') === 0) {
                        $val = substr($val, 2);
                        if (isset($customFields[$key]) && $customFields[$key] === $val) {
                            $ruleMatch = false;
                            break;
                        }
                    } else {
                        if (isset($customFields[$key]) && $customFields[$key] !== $val) {
                            $ruleMatch = false;
                            break;
                        }
                    }
                }

                if ($ruleMatch) {
                    $match = true;
                    break;
                }
            }

            if ($match && empty($customFields[$field])) {
                $msg = $customFieldsForBookig[$field]['label'] . ' is required';
                $errors[] = ['type' => 'validation', 'message' => $msg];
            }
        }

        return $errors;
    }

    public function prescriptionJs()
    {
        return $this->rulesJs('prescriptionFields', 'prescriptionRules');
    }

    public function needRenewFields($prepend = false)
    {
        return array_merge([
            'cf_NeRenew0',
            'cf_NeRenew1',
            'cf_NeRenew2',
            'cf_NeRenew3',
            'cf_NeRenew4',
            'cf_NeRenew5',
            'cf_NeRenew6',
        ], $prepend ? ['cf_x18jr0Vf'] : []);
    }

    public function needRenewRules()
    {
        return [
            /**
             * Has the patient used Gotodoctor or Enhanced Care Clinic before? - Yes
             */
            'cf_NeRenew0' => [['cf_x18jr0Vf' => 'Yes']],
            'cf_NeRenew1' => [['cf_x18jr0Vf' => 'Yes', 'cf_NeRenew0' => 'No']],
            'cf_NeRenew2' => [['cf_x18jr0Vf' => 'Yes', 'cf_NeRenew0' => 'No']],
            'cf_NeRenew3' => [['cf_x18jr0Vf' => 'Yes', 'cf_NeRenew0' => 'No']],
            'cf_NeRenew4' => [['cf_x18jr0Vf' => 'Yes', 'cf_NeRenew0' => 'No']],
            'cf_NeRenew5' => [['cf_x18jr0Vf' => 'Yes', 'cf_NeRenew0' => 'No']],
            'cf_NeRenew6' => [['cf_x18jr0Vf' => 'Yes', 'cf_NeRenew5' => 'Enter Medication List Below']],
        ];
    }

    public function validNeedRenew($customFields, $customFieldsForBookig)
    {
        return $this->validRules($customFields, $customFieldsForBookig, 'needRenewRules');
    }

    public function needRenewJs()
    {
        return $this->rulesJs('needRenewFields', 'needRenewRules');
    }

    public function rulesJs($funcFields = 'prescriptionFields', $funcRules = 'prescriptionRules')
    {
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
        return <<<JS
<script>
jQuery(document).ready(function($) {
    var hiddenFields = {$initJs};
    var fields = {$fieldsJs};
    var rules = {$rulesJs};
    toggleFields(hiddenFields, 'hide');

    for (var key in rules) {
        checkRuel(key);

        rules[key].forEach(function(rule) {
            for (var field in rule) {
                bindRule('#' + fields[field], [key]);
            }
        });
    }

    function toggleFields(list, action) {
        list.forEach(function(field) {
            var f = $('#' + field);
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
    }

    function bindRule(selector, list) {
        $('body').on('change', selector, function() {
            list.forEach(function(field) {
                checkRuel(field);
            });
        });
    }

    function checkRuel(field) {
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
            toggleFields([fields[field]], 'show');
        } else {
            toggleFields([fields[field]], 'hide');
        }
    }

    $('#booking_custom_fields_cf_presc3_0').closest('.os-form-group').before('<div id="preferred_pharamcy_label" class="os-form-group os-form-select-group os-form-group-transparent" style="margin-bottom: 0 !important;"><label>Preferred pharmacy</label></div>');
});
</script>
JS;
    }
}
