<form data-os-custom-field-id="<?php echo $condition['id']; ?>" data-os-action="<?php echo OsRouterHelper::build_route_name('conditions', 'save_location'); ?>" data-os-after-call="latepoint_condition_saved" class="os-custom-field-form">
    <div class="os-custom-field-form-i">
        <div class="os-custom-field-form-info">
            <div class="os-custom-field-drag"></div>
            <div class="os-custom-field-name"><?php echo !empty($condition['label']) ? $condition['label'] : __('New Location Setting', 'latepoint-conditions'); ?></div>
            <div class="os-custom-field-edit-btn"><i class="latepoint-icon latepoint-icon-edit-3"></i></div>
        </div>
        <div class="os-custom-field-form-params">
            <div class="os-row">
                <div class="os-col-12">
                    <?php echo OsFormHelper::text_field('condition[label]', __('Name', 'latepoint-conditions'), $condition['label'], ['class' => 'os-custom-field-name-input', 'id' => 'label_' . $condition['id']]); ?>
                </div>
                <div class="os-col-12">
                    <?php echo OsFormHelper::textarea_field('condition[message]', __('Message', 'latepoint-conditions'), $condition['message'], ['id' => 'message_' . $condition['id'], 'rows' => '10']); ?>
                </div>
                <div class="os-col-6 os-form-w">
                    <div class="white-box">
                        <div class="white-box-header">
                            <div class="os-form-sub-header">
                                <h3><?php _e('Location', 'latepoint-conditions'); ?></h3>
                                <div class="os-form-sub-header-actions" ]>
                                </div>
                            </div>
                        </div>
                        <div class="white-box-content">
                            <?php echo OsFormHelper::select_field('condition[location_id]', '', $locations, $condition['location_id'], ['id' => 'location_' . $condition['id']]); ?>
                            <div class="os-complex-agents-selector">
                                <?php if ($referralTypes) {
                                    foreach ($referralTypes as $referralType) {
                                        $is_active_referral = in_array($referralType->id, $condition['referrals']);
                                        $is_active_referral_value = $is_active_referral ? 'yes' : 'no';
                                        $active_class = $is_active_referral ? 'active' : '';
                                ?>
                                        <div class="agent <?php echo $active_class; ?>">
                                            <div class="agent-i selector-trigger">
                                                <?php echo OsFormHelper::hidden_field('condition[referrals][' . $referralType->id . ']', $is_active_referral_value, ['class' => 'agent-service-connection', 'id' => 'referrals_' . $referralType->id . $condition['id']]); ?>
                                                <h3 class="referral-name"><?php echo $referralType->type_name; ?></h3>
                                            </div>
                                        </div>
                                <?php }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="os-col-6 os-form-w">
                    <div class="white-box">
                        <div class="white-box-header">
                            <div class="os-form-sub-header">
                                <h3><?php _e('Agents', 'latepoint'); ?></h3>
                            </div>
                        </div>
                        <div class="white-box-content">
                            <div class="os-complex-agents-selector">
                                <?php if ($agents) {
                                    foreach ($agents as $agent) {
                                        $is_active_agent = in_array($agent->id, $condition['agents']);
                                        $is_active_agent_value = $is_active_agent ? 'yes' : 'no';
                                        $active_class = $is_active_agent ? 'active' : '';
                                ?>
                                        <div class="agent <?php echo $active_class; ?>">
                                            <div class="agent-i selector-trigger">
                                                <?php echo OsFormHelper::hidden_field('condition[agents][' . $agent->id . ']', $is_active_agent_value, ['class' => 'agent-service-connection', 'id' => 'agents_' . $agent->id . $condition['id']]); ?>
                                                <div class="agent-avatar"><img src="<?php echo $agent->get_avatar_url(); ?>" /></div>
                                                <h3 class="agent-name"><?php echo $agent->full_name; ?></h3>
                                            </div>
                                        </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="os-custom-field-save-btn latepoint-btn latepoint-btn-primary"><span><?php _e('Save', 'latepoint-conditions'); ?></span></button>
        </div>
    </div>
    <?php echo OsFormHelper::hidden_field('condition[id]', $condition['id'], ['class' => 'os-custom-field-id', 'id' => 'id_' . $condition['id']]); ?>
    <a href="#" data-os-prompt="<?php _e('Are you sure you want to remove this field?', 'latepoint-conditions'); ?>" data-os-after-call="latepoint_custom_field_removed" data-os-pass-this="yes" data-os-action="<?php echo OsRouterHelper::build_route_name('conditions', 'delete_location'); ?>" data-os-params="<?php echo OsUtilHelper::build_os_params(['id' => $condition['id']]) ?>" class="os-remove-custom-field"><i class="latepoint-icon latepoint-icon-cross"></i></a>
</form>