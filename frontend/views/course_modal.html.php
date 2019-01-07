<div class="modal-header">
    <h4 class="modal-title"><?php the_title(); ?></h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="row">
            <?php $image = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail_id');
            if (!empty($image)):?>
                <div class="col-4">
                    <img src="<?php echo $image; ?>" data-id="<?php echo $post->ID; ?>">
                </div>
            <?php endif; ?>
            <div class="col<?php echo (!empty($image)) ? '-8' : ''; ?>">
                <p><?php the_content(); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php $from = get_post_meta(get_the_ID(), '_product_availability_from', true);
                $date = DateTime::createFromFormat('Y-m-d', $from);
                if ($date):
                    echo _q('[:en]From[:es]Inicio[:]') . ': ' . $date->format('d/m/Y');
                endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php $to = get_post_meta(get_the_ID(), '_product_availability_to', true);
                $date_to = DateTime::createFromFormat('Y-m-d', $to);
                if ($date_to && $date):
                    echo _q('[:en]To[:es]Fin[:]') . ': ' . $date_to->format('d/m/Y');
                endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <?php echo get_post_meta(get_the_ID(), '_devyai-description-overview', true); ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php echo get_post_meta(get_the_ID(), '_devyai-description-admission', true); ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php echo get_post_meta(get_the_ID(), '_devyai-description-difference', true); ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php echo get_post_meta(get_the_ID(), '_devyai-description-cost-time', true); ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php echo get_post_meta(get_the_ID(), '_devyai-description-course-competences', true); ?>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <!--    <a class="btn btn-xs btn-success text-uppercase" href="/courses-of-interest/?course=--><?php //echo urlencode(the_title()); ?><!--">Apply Now</a>-->
    <?php if (!@$_POST['path']) : ?>
        <button type="button" class="btn btn-xs btn-info text-uppercase" data-path-id="<?php the_ID(); ?>" data-dismiss="modal"><?php echo _q('[:en]Add to path[:es]Adicionar a la Ruta[:]') ?></button>
    <?php else: ?>
        <button type="button" id="apply_now" data-class-id="<?php the_ID(); ?>" class="btn btn-xs btn-info text-uppercase"><?php echo _q('[:en]APPLY[:es]APLICAR[:]') ?></button>
    <?php endif; ?>
    <button type="button" class="offset-md-1 btn btn-xs btn-danger text-uppercase" data-dismiss="modal">
    <?php  if (!@$_POST['path']) :?>
        <?php echo _q('[:en]Cancel[:es]Cancelar[:]') ?>
    <?php else: ?>
        <?php echo _q('[:en]Close[:es]Cerrar[:]') ?>
    <?php endif; ?>
    </button>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var $modal = $('#course-modal');
        <?php  if (!@$_POST['path']) :?>
        var $path_container = $('div#myPath'), $path = $('#my-path'), $table = $('#original-courses');
        $modal.find('.modal-footer button[data-path-id]').click(function (evt) {
            evt.preventDefault();

                var $course = $table.find('a[data-course-id="' + $(this).data('path-id') + '"]');
                var $clone = $course.clone();
                $path_container.show();
                $clone.hide().css({'width': '0', 'border-width': '1px', 'overflow': 'hidden', 'word-wrap': 'normal', 'white-space': 'nowrap', 'transition': 'width 2s', '-webkit-transition': 'width 2s'});
                $clone.append('<i style="cursor:pointer;margin-left:5px" class="fa fa-times">&nbsp;</i>');
                $clone.children('i').click(function (evt) {
                    evt.preventDefault();
                    var id = $(this).closest('a').data('course-id');
                    var $course = $table.find('a[data-course-id="' + id + '"]');
                    $course.show();
                    setTimeout(function () {
                        $course.css({'border-width': '1px', 'width': '95px'});
                    }, 100);
                    var $remove = $(this).parent();
                    $remove.css({'width': '0', 'border-width': '1px 0', 'overflow': 'hidden', 'word-wrap': 'normal', 'white-space': 'nowrap'});
                    $remove.removeAttr('data-course-id');
                    setTimeout(function () {
                        $remove.remove();
                        console.log($path);
                        if ($path.find('[data-course-id]').length === 0)
                            $path_container.hide();
                    }, 2000);
                });
                $course.css({'border-width': '1px 0', 'overflow': 'hidden', 'word-wrap': 'normal', 'white-space': 'nowrap'});
                $path.append($clone);
                $clone.css({'border': 'solid 1px white', 'border-width': '1px 0', 'overflow': 'hidden', 'word-wrap': 'normal', 'white-space': 'nowrap'}).show();
                $clone.css({'width': '95px', 'border-width': '1px 1px'});
                $clone.click(function (evt) {
                    evt.preventDefault();
                });
                setTimeout(function () {
                    $course.hide();
                }, 2000);
                $course.css({'width': 0});
                $clone.data('course-id', '<?php echo get_the_ID(); ?>');
                $clone.data('name', '<?php echo substr(strtoupper($post->post_name), 0, 7); ?>');
                $clone.data('title', '<?php the_title(); ?>');
                $modal.modal('hide');
            });
<?php else: ?>
    $('button[id=apply_now]').click(function () {
        $.post(WPURLS.siteurl + '/wp-admin/admin-ajax.php', {
            action: 'periodic_ajax_apply_now',
            category_id: $(this).data('category-id'),
            class_id: $(this).data('class-id')
        }, function (response) {
            if (response.success)
                window.location = response.route;
        });
    });
<?php endif; ?>
        });
    </script>
