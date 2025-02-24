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
            'cf_Presc1_0' => ['cf_x18jr0Vf' => 'Yes', 'cf_6A3SfgET' => 'Ontario'], 
            'cf_Presc2_0' => ['cf_Presc1_0' => 'No'],
            'cf_Presc3_0' => ['cf_Presc2_0' => 'No'],
            'cf_Presc3_1' => ['cf_Presc2_0' => 'No'],
            'cf_Presc3_2' => ['cf_Presc2_0' => 'No'],
        ];
    }

    public function validPrescription($customFields, $customFieldsForBookig)
    {
        $rules = $this->prescriptionRules();
        $errors = [];

        foreach ($rules as $field => $rule) {
            $match = true;

            foreach ($rule as $key => $val) {
                if (isset($customFields[$key]) && $customFields[$key] !== $val) {
                    $match = false;
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
        $keys = $this->prescriptionFields(true);
        $ids = array_map(function($key) {
            return 'booking_custom_fields_' . strtolower($key);
        }, $keys);
        $fields = array_combine($keys, $ids);
        $init = array_slice($keys, 0, -2);
        $rules = $this->prescriptionRules();

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

        for (var field in rules[key]) {
            bindRule('#' + fields[key], [field]);
        }
    }

    function toggleFields(list, action) {
        list.forEach(function(field) {
            var f = $('#' + field);
            if (action === 'hide') {
                f.closest('.os-form-group').hide();
                f.prop('required', false);
            } else {
                f.closest('.os-form-group').show();
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
        var rule = rules[field];
        var match = true;
        for (var key in rule) {
            var value = rule[key];
            var f = $('#' + fields[key]);
            if (f.val() !== value) {
                match = false;
                break;
            }
        }
        if (match) {
            toggleFields([fields[field]], 'show');
        } else {
            toggleFields([fields[field]], 'hide');
        }
    }
});
</script>
JS;
    }
}