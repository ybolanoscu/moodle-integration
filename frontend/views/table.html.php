<div id="original-courses" class="coursesTable container">
    <?php $colors = array('purple', 'teal', 'yellow', 'pink', 'lightBlue', 'ultraLightBlue', 'lightenTeal', 'green');
    $max_colors = count($colors) - 1;
    foreach ($categories as $category): ?>
        <div class="row">
            <div class="col-lg-3 topic d-flex align-items-center justify-content-center">
                <?php echo $category->name; ?>
            </div>
            <div class="cursos col-lg-9 d-flex align-items-center">
                <?php foreach ($course_by_categories[$category->term_id] as $course):
                    $int = random_int(0, $max_colors); ?>
                    <a class="<?php echo $colors[$int] . ' fg_' . $colors[$int]; ?>" href="#" data-course-id="<?php echo $course->ID; ?>">
                        <span><?php echo substr(strtoupper($course->post_name), 0, 7); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="alert alert-info d-none m-3" role="alert">
        Your new path was created, and is waiting for admin confirmation.
    </div>
</div>
<div class="row">
    <div style="display:none;overflow: hidden;" class="col" id="myPath">
        <div class="coursesTable container">
            <div class="col"><h4 class="text-left"><?php echo _q('[:en]My Path[:es]Mi Ruta[:]') ?></h4></div>
            <div class="row" style="padding: 0 15px;">
                <div class="col">
                    <div id="my-path" class="cursos margin_top_50"></div>
                </div>
            </div>
            <div class="row" style="padding: 5px 15px;">
                <a name="request" id="request" class="btn btn-default btn-unfill btn-redbold"
                   data-toggle="modal" data-target="#course-path-modal" href="#" role="button"><?php echo _q('[:en]Request[:es]Solicitar[:]')?></a>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="modal fade" id="course-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            </div>
        </div>
    </div>
    <?php if ($form_id): ?>
        <div class="modal fade" id="course-path-modal" tabindex="-1" role="dialog" aria-labelledby="Courses Paths" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="course-path-modal"><?php echo $attributes['c7title']; ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php echo do_shortcode('[contact-form-7 id="' . $form_id . '" title="' . $attributes['c7title'] . '"]'); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="clearfix"></div>
<style>
    .modal label {
        width: 100%;
    }
</style>
<script type="text/javascript">
    $(document).ready(function () {
        var alert = $('div#original-courses').find('.alert');
        $(document).on('click',function(){
            alert.addClass('d-none');
        });

        var $modal = $('#course-modal');
        document.courses = [];
        $('.coursesTable .cursos a[data-course-id]').click(function (evt) {
            evt.preventDefault();
            var form_data = {};
            form_data['action'] = 'periodic_ajax_modal';
            form_data['course_id'] = $(this).data('course-id');
            $modal.modal('show');
            $modal.find('.modal-content').html('Loading content...');
            $.post('<?php  echo admin_url('admin-ajax.php'); ?>', form_data, function (response) {
                if (response.success) {
                    $modal.find('.modal-content').html(response.html);
                }
            });
        });

        $('#request').on('click', function () {
            var paths = '', names = '', ids = '';
            var $path = $('#my-path');
            $path.find('[data-course-id]').each(function () {
                paths += ($(this).data('title') + "\n");
                names += ($(this).data('name') + "|");
                ids += ($(this).data('course-id') + "|");
            });
            // document.courses.forEach(function (path) {
            //     paths += (path.title + "\n");
            //     names += (path.name + "|");
            // });
            $('#path_course').val(paths);
            $('#name_course').val(names);
            $('#id_course').val(ids);
            $('#course-path-modal').on('wpcf7mailsent', function () {
                var $this = $(this);
                setTimeout(function () {
                    if ($this.find('.wpcf7-validation-errors').length === 0){
                        $('div#my-path').children('a').each(function(index,val){
                            $(val).find('i').click();
                        });
                        alert.removeClass('d-none');
                        $this.modal('hide');
                    }
                }, 1500);
            });
        });
    });
</script>