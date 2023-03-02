<div class="os-form-sub-header">
    <h3><?php _e('Location Enable/Disable', 'latepoint-extends'); ?></h3>
</div>
<div class="os-conditions-w os-conditions-ordering-w">
    <?php foreach ($settings as $condition) : ?>
        <?php include('_form_location.php'); ?>
    <?php endforeach; ?>
</div>
<div class="os-add-box add-condition-box add-condition-trigger" data-os-action="<?php echo OsRouterHelper::build_route_name('conditions', 'new_location'); ?>" data-os-output-target-do="append" data-os-output-target=".os-conditions-w">
    <div class="add-box-graphic-w">
        <div class="add-box-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
    </div>
    <div class="add-box-label"><?php _e('Add Location Setting', 'latepoint-conditions'); ?></div>
</div>