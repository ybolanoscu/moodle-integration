<?php
/**
 * Created by PhpStorm.
 */

$devyai_options = get_option('devyai_options', new stdClass());
if (is_string($devyai_options))
    $devyai_options = unserialize($devyai_options);
else
    $devyai_options = new stdClass();
?>
<div class="twothirds vertical-tabs">
    <div class="checklist-wrap" style="position: relative;">
        <?php atlantis_edit_link($devyai_options->template_base); ?>

        <?php
        $overview = get_post($devyai_options->template_overview);
        $courses = get_post($devyai_options->template_courses);
        $admissions = get_post($devyai_options->template_admissions);
        $cost = get_post($devyai_options->template_cost);
        $au = get_post($devyai_options->template_difference);

        $tabsObj = array($overview, $courses, $admissions, $cost, $au);

        $lis = $tabs = "";
        $i = 0;
        foreach ($tabsObj as $item) :
            if (empty($item)) continue;
            ob_start(); ?>
            <li class="<?= !$i ? 'active' : ''; ?>" id="tabbed-<?= $item->ID; ?>" role="tab" aria-controls="tabbed-child-<?= $item->ID; ?>" aria-selected="<?= $i ? 'false' : 'true'; ?>"><?= $item->post_title; ?></li>
            <?php $lis .= ob_get_contents();
            ob_end_clean();

            $current_style_val = get_post_meta($item->ID, 'atlantis_style_page', true);
            $current_script_val = get_post_meta($item->ID, 'atlantis_script_page', true);

            ob_start(); ?>
            <div class="twothirds last box tab<?= $i + 1; ?>-box <?php echo !$i ? 'selected ' : ''; ?>" id="tabbed-child-<?= $item->ID; ?>" role="tabpanel" aria-labelledby="tabbed-<?= $item->ID; ?>">
                <article class="deptBox">
                    <?php atlantis_edit_link($item->ID); ?>
                    <?php echo '<style type="text/css">' . $current_style_val . '</style>' ?>
                    <?php echo apply_filters('the_content', $item->post_content); ?>
                    <?php echo '<script type="text/javascript">' . $current_script_val . '</script>' ?>
                </article>
            </div>
            <?php $tabs .= ob_get_contents();
            ob_end_clean();

            $i++;
        endforeach;
        ?>
        <div class="contextNav onethird">
            <ul class="checklist-select" role="tablist">
                <?= $lis; ?>
            </ul>
        </div>
        <div>
            <?= $tabs; ?>
        </div>
    </div>
</div>
<div class="modal fade" id="course-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        </div>
    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function () {
        var $modal = $('#course-modal');
        document.courses = [];
        $('a.class-button-modal[data-course-id]').click(function (evt) {
            evt.preventDefault();
            var form_data = {};
            form_data['action'] = 'periodic_ajax_modal';
            form_data['path'] = false;
            form_data['course_id'] = $(this).data('course-id');
            $modal.modal('show');
            $modal.find('.modal-content').html('Loading content...');
            $.post('<?php  echo admin_url('admin-ajax.php'); ?>', form_data, function (response) {
                if (response.success) {
                    $modal.find('.modal-content').html(response.html);
                }
            });
        });
    });
</script>
