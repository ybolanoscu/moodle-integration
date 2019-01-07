<div class="container">
    <div class="row carousel carousel-multi center justify-content-center slide" data-ride="carousel" id="carouselPath" data-interval="100000">
        <div class="carousel-inner p-1">
            <?php $i = 0;
            foreach ($categories as $category):
                $image = 'http://aux.atlantisuniversity.edu/wp-content/themes/atlantis-admissions/images/atlantisuniv-min.png';
                $meta = get_term_meta($category->term_id, 'thumbnail_id', true);
                if ($meta) {
                    $tmp = wp_get_attachment_image_src($meta, array(220, 220), true);
                    if (!empty($tmp))
                        $image = $tmp[0];
                } ?>
                <div class="carousel-item <?= !$i ? 'active' : ''; ?>">
                    <div class="col-12 col-sm-6 col-lg-3 no_padding_xs">
                        <div class="card border rounded-0 course">
                            <img class="card-img-top" src="<?= $image; ?>" alt="">
                            <div class="card-body">
                                <h4 class="card-title text-primary"><?php echo $category->name; ?></h4>
                                <p class="card-text border-bottom time border-dark">
                                    <i class="fa fa-clock">&nbsp;</i>
                                    8 horas Lun-Vier
                                </p>
                                <p class="card-text text-justify">
                                    <?php echo apply_filters('devyai_trim_text', $category->description, 20); ?>
                                </p>
                                <div class="row buttons">
                                    <div class="col-xs-12 col-sm-6 no_padding">
                                        <button data-category-id="<?php echo $category->term_id; ?>" id="more_info" style="max-width: 95%;" class="btn btn-outline-danger w-100" type="button"><?= _q('[:en]MORE INFO[:es]VER MAS[:]') ?></button>
                                    </div>
                                    <div class="col-xs-12 col-sm-6 no_padding">
                                        <button data-category-id="<?php echo $category->term_id; ?>" id="apply_now" class="btn btn-outline-danger w-100" style="max-width: 95%;" type="button"><?= _q('[:en]APPLY[:es]APLICAR[:]') ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $i++;
            endforeach; ?>
        </div>
        <a style="margin-left:-38px" class="carousel-control-prev" href="#carouselPath" role="button" data-slide="prev">
            <i style="color:#5585b7" class="fa fa-chevron-circle-left fa-3x"></i>
        </a>
        <a style="margin-right:-38px" class="carousel-control-next" href="#carouselPath" role="button" data-slide="next">
            <i style="color:#5585b7" class="fa fa-chevron-circle-right fa-3x"></i>
        </a>
    </div>
    <div class="modal-path modal fade" role="dialog" id="modal-card" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function ($) {
        "use strict";
        $('.carousel-multi .carousel-item').each(function () {
            var next = $(this).next();
            if (!next.length)
                next = $(this).siblings(':first');
            next.children(':first-child').clone().appendTo($(this));

            var next2 = next.next();
            if (next2.length > 0) {
                next2.children(':first-child').clone().appendTo($(this));
                var next3 = next2.next();
                if (next3.length > 0) {
                    next3.children(':first-child').clone().appendTo($(this));
                }
                else
                    next.siblings(':first').children(':first-child').clone().appendTo($(this));
            }
            else
                $(this).siblings(':first').children(':first-child').clone().appendTo($(this));
        });

        var $modal = $('#modal-card');
        $('button[id=more_info]').click(function (evt) {
            evt.preventDefault();
            var form_data = {};
            form_data['action'] = 'category_ajax_modal';
            form_data['category_id'] = $(this).data('category-id');
            $modal.modal('show');
            $modal.find('.modal-content').html('Loading content...');
            $.post('<?php  echo admin_url('admin-ajax.php'); ?>', form_data, function (response) {
                if (response.success) {
                    $modal.find('.modal-content').html(response.html);
                    $modal.find('.card-header a').click();
                }
            });
        });
    })(jQuery);
</script>