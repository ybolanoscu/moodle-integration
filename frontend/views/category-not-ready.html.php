<?php require_once __DIR__ . '/../CourseTerm.php'; ?>
<h3 class="bg-primary-default"><?php echo __('Category Status', 'devyai'); ?></h3>
<div class="container">
    <div class="table-responsive">
        <table class="shop_table shop_table_responsive">
            <thead>
            <tr style="background: #6d89af;">
                <th>#</th>
                <th><?php echo __('Publish', 'devyai') ?></th>
                <th><?php echo __('Category', 'devyai') ?></th>
                <th><?php echo __('Menu', 'devyai') ?></th>
                <th style="white-space: nowrap;"><?php echo __('Language', 'devyai') ?></th>
                <th><?php echo __('Price', 'devyai') ?></th>
                <th style="white-space: nowrap;"><?php echo __('Start Date', 'devyai') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            foreach ($categories as $category):
                $category->saved = get_term_meta($category->term_id, '_saved', true);
                $category->price = get_term_meta($category->term_id, '_devyai-price', true);
                $category->publish = get_term_meta($category->term_id, '_devyai-publish', true);
                $category->start_date = get_term_meta($category->term_id, '_devyai-start-date', true);
                $category->publish = get_term_meta($category->term_id, '_devyai-publish', true);
                $category->menu = get_term_meta($category->term_id, '_devyai-menu', true);
                $category->lang = get_term_meta($category->term_id, '_devyai-lang', true);

                $price = CourseTerm::getInstance()->calculateCategoryPrice($category->term_id);
                ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?php if ($category->publish == 'true'): ?><i class="fa fa-eye" aria-hidden="true"></i><?php endif; ?></td>
                    <td><?= $category->name; ?></td>
                    <td style="font-style: italic;"><?= @$mapMenu['m' . $category->menu] ?></td>
                    <td style="font-weight: bold; text-align: center;"><?= @$category->lang ?></td>
                    <td>
                        <?php if ($category->saved): ?>
                            <span class="badge badge-success"><?= ($category->price == 0) ? "FREE" : '$' . $category->price; ?></span>
                        <?php else: $category->price = $price; ?>
                            <span class="badge badge-danger">Price <?= $price ? ': $ ' . $price : '' ?></span>
                        <?php endif;
                        if ((double)$price != (double)$category->price && $price > 0): ?>
                            <span class="badge badge-info">New: <?= $price ? '$ ' . $price : '' ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$category->start_date): ?>
                            <span class="badge badge-danger"><?php echo __('Start Date', 'devyai'); ?></span>
                        <?php else: ?>
                            <span class="badge badge-success"><?= $category->start_date->format('d/m/Y'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" style="padding: 5px;min-width: 30px;" class="btn btn-xs btn-dark edit_course" data-toggle="modal" data-target="#modal_edit_course"
                                data-id="<?= @$category->term_id ?>"><i class="fa fa-pencil"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="modal_edit_course" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form_course" action="#" autocomplete="off" novalidate>
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle"><?= __('Edit Category', 'devyai') ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color: #000;">&times;</span></button>
                </div>
                <div class="modal-body text-left">
                    <div class="alert alert-danger font-weight-bold alert-dismissible" role="alert" id="msg" style="display: none;">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span>&times;</span>
                        </button>
                        <span id="msg_content"><?= __('Please check the values', 'devyai') ?></span>
                    </div>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general-content" role="tab" aria-controls="general-content" aria-selected="true">General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-nav-description-overview" data-toggle="tab" href="#tab-description-overview" role="tab" aria-controls="tab-description-overview" aria-selected="false">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-nav-description-course-competences" data-toggle="tab" href="#tab-description-course-competences" role="tab" aria-controls="tab-description-course-competences" aria-selected="false">Course</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-nav-description-admission" data-toggle="tab" href="#tab-description-admission" role="tab" aria-controls="tab-description-admission" aria-selected="false">Admission</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-nav-description-cost-time" data-toggle="tab" href="#tab-description-cost-time" role="tab" aria-controls="tab-description-cost-time" aria-selected="false">Cost & Time</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-nav-description-difference" data-toggle="tab" href="#tab-description-difference" role="tab" aria-controls="tab-description-difference" aria-selected="false">AU Difference</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="tabs-contents">
                        <div class="tab-pane fade show active pt-2" id="general-content" role="tabpanel" aria-labelledby="general-tab">
                            <div class="form-group">
                                <label for="price"><?= __('Price', 'devyai') ?></label>
                                <input type="text" class="form-control" id="price-s" placeholder="<?= __('Price', 'devyai') ?>" disabled>
                                <input type="hidden" name="price" id="price">
                            </div>
                            <div class="form-group">
                                <label for="start_date"><?= __('Start Date', 'devyai') ?></label>
                                <input type="text" class="form-control date-picker" name="start_date" id="start_date" placeholder="<?= __('Start Date', 'devyai') ?>">
                            </div>
                            <div class="form-group">
                                <label for="menu">Menu</label>
                                <select class="form-control" name="menu" id="menu">
                                    <option></option>
                                    <?php foreach ($menus as $menu) : ?>
                                        <?php if (!$menu->menu_item_parent) : ?>
                                            <option value="<?= $menu->ID ?>"><?= $menu->title; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="lang">Language</label>
                                <select class="form-control" name="lang" id="lang">
                                    <?php foreach ($languages as $lang) : ?>
                                        <option value="<?= $lang['code'] ?>"><?= $lang['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="publish" id="publish"> <?= __('Publish', 'devyai') ?>
                            </label>
                        </div>
                        <div class="tab-pane fade pt-2" id="tab-description-overview" role="tabpanel" aria-labelledby="tab-nav-description-overview">
                            <?php wp_editor('', 'description-overview'); ?>
                        </div>

                        <div class="tab-pane fade pt-2" id="tab-description-course-competences" role="tabpanel" aria-labelledby="tab-nav-description-course-competences">
                            <?php wp_editor('', 'description-course_competences'); ?>
                        </div>

                        <div class="tab-pane fade pt-2" id="tab-description-admission" role="tabpanel" aria-labelledby="tab-nav-description-admission">
                            <?php wp_editor('', 'description-admission'); ?>
                        </div>

                        <div class="tab-pane fade pt-2" id="tab-description-cost-time" role="tabpanel" aria-labelledby="tab-nav-description-cost-time">
                            <?php wp_editor('', 'description-cost_time'); ?>
                        </div>

                        <div class="tab-pane fade pt-2" id="tab-description-difference" role="tabpanel" aria-labelledby="tab-nav-description-difference">
                            <?php wp_editor('', 'description-difference'); ?>
                        </div>
                    </div>
                    <div class="status_loading text-center p-4" id="tabs-contents-loading">
                        <i class="fa fa-spinner fa-spin fa-2x" aria-hidden="true"></i>
                    </div>
                    <input type="hidden" class="form-control" name="course_id" id="course_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= __('Close', 'devyai') ?></button>
                    <button type="button" class="btn btn-success btn-submit"><?= __('Save', 'devyai') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        const ajaxurl = '<?= admin_url('admin-ajax.php') ?>';
        const $form = $('#form_course'), $modal = $('#modal_edit_course'), $msg = $('#msg'), $submit = $form.find('.btn-submit');
        const $price = $('#price'), $price_s = $('#price-s'), $start_date = $('#start_date'), $course = $('#course_id'), $publish = $('#publish');
        const $lang = $('#lang'), $menu = $('#menu');
        var $description_overview = null, $description_admission = null, $description_cost_time = null,
            $description_difference = null, $description_course_competences = null;

        $('.date-picker').datepicker({dateFormat: 'dd/mm/yy'});
        $submit.on('click', function () {
            $form.submit();
            $submit.attr('disabled', 'disabled');
        });
        $form.on('submit', function (e) {
            e.preventDefault();
            $.post(ajaxurl, {
                action: 'update_ajax_course_category',
                course: $course.val(),
                price: $price.val(),
                start_date: $start_date.val(),
                publish: $publish.prop('checked'),
                lang: $lang.val(),
                menu: $menu.val(),
                description_overview: $description_overview.getContent(),
                description_admission: $description_admission.getContent(),
                description_cost_time: $description_cost_time.getContent(),
                description_difference: $description_difference.getContent(),
                description_course_competences: $description_course_competences.getContent(),
            }).then(function (data) {
                if (data.success) {
                    $modal.modal('hide');
                    document.location.href = '';
                } else {
                    $('#msg_content').html(data.message);
                    $msg.show();
                }
                $submit.removeAttr('disabled');
            }, function () {
                $('#msg_content').html("<?= __('Please check the values', 'devyai') ?>");
                $msg.show();
                $submit.removeAttr('disabled');
            });
        });
        $('.edit_course').on('click', function (e) {
            const $this = $(this);
            $msg.hide();
            $description_overview = tinyMCE.get('description-overview');
            $description_admission = tinyMCE.get('description-admission');
            $description_difference = tinyMCE.get('description-difference');
            $description_cost_time = tinyMCE.get('description-cost_time');
            $description_course_competences = tinyMCE.get('description-course_competences');

            $('#tabs-contents-loading').fadeIn();

            $.post(ajaxurl, {
                action: 'update_ajax_get_course_category',
                course: $this.data('id'),
            }).then(function (data) {
                if (data.success) {
                    $course.val($this.data('id'));
                    $price.val(data.category.price);
                    $price_s.val(data.category.price);
                    $start_date.val(data.category.start);
                    $menu.val(data.category.menu);
                    $lang.val(data.category.lang);

                    $description_overview.setContent(data.category.description_overview);
                    $description_cost_time.setContent(data.category.description_cost_time);
                    $description_admission.setContent(data.category.description_admission);
                    $description_difference.setContent(data.category.description_difference);
                    $description_course_competences.setContent(data.category.description_course_competences);

                    if (data.category.publish !== "true") {
                        $publish.prop('checked', false);
                    } else {
                        $publish.prop('checked', true);
                    }
                    $msg.hide();
                } else {
                    $('#msg_content').html(data.message);
                    $msg.show();
                }
                $('#tabs-contents-loading').fadeOut('slow');
            }, function () {
                $('#msg_content').html("<?= __('Please check the values', 'devyai') ?>");
                $msg.show();
                $('#tabs-contents-loading').fadeOut('slow');
            });
        })
    })
</script>
