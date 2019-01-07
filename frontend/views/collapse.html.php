<div id="accordion" class="col-12" role="tablist" aria-multiselectable="true">
    <?php foreach ($categories as $category): ?>
        <div class="card">
            <div class="card-header" role="tab" id="tab-<?= $attributes['class'] ?>-header-<?= $category->term_id; ?>">
                <a data-toggle="collapse" data-parent="#accordion" href="#tab-<?= $attributes['class'] ?>-tab-<?= $category->term_id; ?>" aria-expanded="false" aria-controls="tab-<?= $attributes['class'] ?>-tab-<?= $category->term_id; ?>" class="collapsed">
                    <h5 class="mb-0"><span class="icon">&nbsp;</span>
                        <?php echo $category->name; ?>
                    </h5>
                </a>
            </div>
            <div data-parent="#accordion" id="tab-<?= $attributes['class'] ?>-tab-<?= $category->term_id; ?>" class="collapse" role="tabpanel" aria-labelledby="tab-<?= $attributes['class'] ?>-header-<?= $category->term_id; ?>" style="">
                <?php if ($category->description):
                    $image = false;
                    $meta = get_term_meta($category->term_id, 'thumbnail_id', true);
                    if ($meta) {
                        $tmp = wp_get_attachment_image_src($meta, array(220, 220), true);
                        if (!empty($tmp))
                            $image = $tmp[0];
                    } ?>
                    <div class="container">
                        <div class="col" style="margin: 15px 0 ;">
                            <?php if ($image): ?>
                                <img class="img-thumbnail" style="float:right;max-width: 32%;" src="<?= $image; ?>" alt="">
                            <?php endif; ?>
                            <?= html_entity_decode($category->description); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                <?php endif; ?>
                <?php $courseID = bin2hex(random_bytes(4)); ?>
                <div class="card-body" id="courses-<?php echo $courseID; ?>" role="tablist" aria-multiselectable="true">
                    <?php foreach (@$course_by_categories[$category->term_id] as $course):
                        $image = get_the_post_thumbnail_url($course->ID, 'thumbnail_id');

                        $postID = bin2hex(random_bytes(4)); ?>
                        <div class="tooltipLink col-12 <?php echo (empty($category->description)) ? 'no-top-border' : ''; ?>">
                            <div id="post-<?php echo $course->ID . $postID; ?>" role="tab">
                                <span class="card-link-title collapsed" data-toggle="collapse" data-parent="#courses-<?php echo $courseID; ?>" href="#postheader-<?php echo $course->ID . $postID; ?>" aria-expanded="false" aria-controls="postheader-<?php echo $course->ID . $postID; ?>">
                                    <i class="fa fa-info-circle">&nbsp;</i> <?php echo $course->post_title; ?>
                                </span>
                            </div>
                            <div class="clearfix"></div>
                            <div id="postheader-<?php echo $course->ID . $postID; ?>" data-parent="#courses-<?php echo $courseID; ?>" class="collapse in" role="tabpanel" aria-labelledby="post-<?php echo $course->ID . $postID; ?>">
                                <div class="row">
                                    <div class="col">
                                        <?php if ($image): ?>
                                            <img class="img-thumbnail" style="float:right;max-width: 32%;" src="<?= $image; ?>" alt="">
                                        <?php endif; ?>
                                        <?php echo (empty($course->post_content)) ? _q('[:en]This course has no description.[:es]No se ha encontrado descripión para el curso.[:]') : strip_tags($course->post_content,'<p><a>'); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <?php $from = get_post_meta($course->ID, '_product_availability_from', true);
                                        $date = DateTime::createFromFormat('Y-m-d', $from);
                                        if ($date):
                                            echo _q('[:en]From[:es]Inicio[:]') . ': ' . $date->format('d/m/Y');
                                        endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <?php $to = get_post_meta($course->ID, '_product_availability_to', true);
                                        $date_to = DateTime::createFromFormat('Y-m-d', $to);
                                        if ($date_to && $date):
                                            echo _q('[:en]To[:es]Fin[:]') . ': ' . $date_to->format('d/m/Y');
                                        endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <?php echo get_post_meta($course->ID, '_devyai-description-overview', true); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <?php echo get_post_meta($course->ID, '_devyai-description-admission', true); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <?php echo get_post_meta($course->ID, '_devyai-description-difference', true); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <?php echo get_post_meta($course->ID, '_devyai-description-cost-time', true); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <?php echo get_post_meta($course->ID, '_devyai-description-course-competences', true); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<script type="text/javascript">
    $(function () {
        $('div[role="tablist"]').on('hide.bs.collapse', function (ev) {
            var span = $(ev.target).parent().find('span.icon');
            var cardHeader = $(ev.target).siblings('div');
            span.toggleClass('open');
            cardHeader.removeClass('open');
        }).on('show.bs.collapse', function (ev) {
            var span = $(ev.target).parent().find('span.icon');
            var cardHeader = $(ev.target).siblings('div');
            span.toggleClass('open');
            cardHeader.addClass('open');
        });
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>