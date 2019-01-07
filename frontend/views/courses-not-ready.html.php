<h3 class="bg-primary-default"><?php echo __('Courses Status', 'devyai'); ?></h3>
<div class="container">
    <div class="table-responsive">
        <table class="shop_table shop_table_responsive">
            <thead>
            <tr style="background: #6d89af;">
                <th>#</th>
                <th><?php echo __('Category', 'devyai') ?></th>
                <th style="white-space: nowrap;"><?php echo __('Course Name', 'devyai') ?></th>
                <th><?php echo __('Price', 'devyai') ?></th>
                <th style="white-space: nowrap;"><?php echo __('Start Date', 'devyai') ?></th>
                <th style="white-space: nowrap;"><?php echo __('End Date', 'devyai') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($courses as $i => $course):
                $product = wc_get_product($course->ID);
                $course->saved = get_post_meta($course->ID, '_saved', true);
                $course->course_id = get_post_meta($course->ID, '_course_id', true);
                $course->categories = get_the_terms($course->ID, 'product_cat');
                $course->availability_from = DateTime::createFromFormat('Y-m-d', get_post_meta($course->ID, '_product_availability_from', true));
                $course->availability_to = DateTime::createFromFormat('Y-m-d', get_post_meta($course->ID, '_product_availability_to', true));
                ?>
                <tr>
                    <td><?= $i + 1; ?></td>
                    <td>
                        <?php foreach ($course->categories as $category):
                            echo $category->name . '<br>';
                        endforeach; ?>
                    </td>
                    <td><?= $course->post_title; ?></td>
                    <td>
                        <?php if ($course->saved): ?>
                            <span class="badge badge-success"><?= ($product->price == 0) ? "FREE" : '$' . $product->price; ?></span>
                        <?php else: ?>
                            <span class="badge badge-danger">Price</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$course->availability_from): ?>
                            <span class="badge badge-danger"><?php echo __('Start Date', 'devyai'); ?></span>
                        <?php else: ?>
                            <span class="badge badge-success"><?= $course->availability_from->format('d/m/Y'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$course->availability_to): ?>
                            <span class="badge badge-danger"><?php echo __('End Date', 'devyai'); ?></span>
                        <?php else: ?>
                            <span class="badge badge-success"><?= $course->availability_to->format('d/m/Y'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" style="padding: 5px;min-width: 30px;" class="btn btn-xs btn-dark edit_course" data-toggle="modal" data-target="#modal_edit_course"
                                data-id="<?= $course->ID ?>"><i class="fa fa-pencil"></i></button>
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
                    <h4 class="modal-title" id="modalTitle"><?= __('Edit Course', 'devyai') ?></h4>
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
                            <div class="row p-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="price"><?= __('Price', 'devyai') ?></label>
                                        <input type="text" class="form-control" name="price" id="price" placeholder="<?= __('Price', 'devyai') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date"><?= __('Start Date', 'devyai') ?></label>
                                        <input type="text" class="form-control date-picker" name="start_date" id="start_date" placeholder="<?= __('Start Date', 'devyai') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date"><?= __('End Date', 'devyai') ?></label>
                                        <input type="text" class="form-control date-picker" name="end_date" id="end_date" placeholder="<?= __('End Date', 'devyai') ?>">
                                    </div>
                                </div>
                            </div>
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
        const $price = $('#price'), $start_date = $('#start_date'), $end_date = $('#end_date'), $course = $('#course_id');
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
                action: 'update_ajax_course',
                course: $course.val(),
                price: $price.val(),
                start_date: $start_date.val(),
                end_date: $end_date.val(),
                description_overview: $description_overview.getContent(),
                description_admission: $description_admission.getContent(),
                description_cost_time: $description_cost_time.getContent(),
                description_difference: $description_difference.getContent(),
                description_course_competences: $description_course_competences.getContent(),

            }).then(function (data) {
                if (data.success) {
                    $modal.modal('hide');
                    document.location.href = '';
                }
                $submit.removeAttr('disabled');
            }, function () {
                $('#msg_content').html(data.message);
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
                action: 'update_ajax_get_course_class',
                course: $this.data('id'),
            }).then(function (data) {
                if (data.success) {
                    $course.val($this.data('id'));
                    $price.val(data.class.price);
                    $start_date.val(data.class.start);
                    $end_date.val(data.class.end);

                    $description_overview.setContent(data.class.description_overview);
                    $description_cost_time.setContent(data.class.description_cost_time);
                    $description_admission.setContent(data.class.description_admission);
                    $description_difference.setContent(data.class.description_difference);
                    $description_course_competences.setContent(data.class.description_course_competences);

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
        });
    })
</script>
