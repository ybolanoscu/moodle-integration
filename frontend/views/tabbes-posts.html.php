<div class="twothirds vertical-tabs">
    <div class="checklist-wrap">
        <?php
        $args = array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_parent' => get_the_ID(),
            'order' => 'ASC',
            'orderby' => 'menu_order'
        );


        $parent = new WP_Query($args);
        $lis = $tabs = "";
        if ($parent->have_posts()) :
            $i = 0;
            while ($parent->have_posts()) :
                $parent->the_post();
                ob_start(); ?>
                <li class="<?= !$i ? 'active' : ''; ?>" id="tabbed-<?= get_the_ID(); ?>" role="tab" aria-controls="tabbed-child-<?= get_the_ID(); ?>" aria-selected="<?= $i ? 'false' : 'true'; ?>"><?= the_title(); ?></li>
                <?php $lis .= ob_get_contents();
                ob_end_clean();

                $current_style_val = get_post_meta(get_the_ID(), 'atlantis_style_page', true);
                $current_script_val = get_post_meta(get_the_ID(), 'atlantis_script_page', true);

                ob_start(); ?>
                <div class="twothirds last box tab<?= $i + 1; ?>-box <?php echo !$i ? 'selected ' : ''; ?>" id="tabbed-child-<?= get_the_ID(); ?>" role="tabpanel" aria-labelledby="tabbed-<?= get_the_ID(); ?>">
                    <article class="deptBox">
                        <?php atlantis_edit_link(); ?>
                        <?php echo '<style type="text/css">' . $current_style_val . '</style>' ?>
                        <?php the_content(); ?>
                        <?php echo '<script type="text/javascript">' . $current_script_val . '</script>' ?>
                    </article>
                </div>
                <?php $tabs .= ob_get_contents();
                ob_end_clean();

                $i++;
            endwhile;
        endif;
        wp_reset_postdata(); ?>
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
<div class="clearfix"></div>