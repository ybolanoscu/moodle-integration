<div class="row">
    <?= do_shortcode('[devyai-collapse class="modal" id="' . $category->term_id . '"]'); ?>
</div>
<div class="modal-footer">
    <button id="apply" type="button" class="btn btn-xs btn-info text-uppercase" data-category-id="<?php echo $category->term_id; ?>" data-dismiss="modal"><?php echo _q('[:en]Apply[:es]Aplicar[:]')?></button>
    <button type="button" class="btn btn-xs btn-danger text-uppercase" data-dismiss="modal"><?php echo _q('[:en]Cancel[:es]Cancelar[:]')?></button>
</div>

<script>
    $(document).ready(function () {
        $('button[id=apply]').click(function() {
            $.post('<?php  echo admin_url('admin-ajax.php'); ?>', {
                action: 'periodic_ajax_apply_now',
                category_id: $(this).data('category-id')
            }, function (response) {
                switch(response.success){
                    case 1: window.location='../checkout';break;
                    case 2: window.location='../admission';
                }
            });
        });
    });
</script>