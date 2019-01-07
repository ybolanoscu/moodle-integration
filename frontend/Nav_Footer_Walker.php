<?php

require_once 'MenuBuilder.php';

class Nav_Footer_Walker extends Walker_Nav_Menu
{
    protected function static_childs_count($item)
    {
        global $wpdb;
        $sub_querystr = 'SELECT count(sub_term_meta.term_id) FROM ' . $wpdb->termmeta . ' as sub_term_meta WHERE term_meta.term_id = sub_term_meta.term_id AND sub_term_meta.meta_value = "true" AND sub_term_meta.meta_key = "_devyai-publish"';
        $querystr = 'SELECT count(term_meta.term_id) as cant FROM ' . $wpdb->termmeta . ' as term_meta WHERE (' . $sub_querystr . ') > 0 AND term_meta.meta_value = "' . $item . '" AND term_meta.meta_key = "_devyai-menu"';
        $metas = $wpdb->get_results($querystr, OBJECT);
        return !empty($metas[0]->cant) ? $metas[0]->cant : 0;
    }

    protected function static_childs(&$item_output, $item, $category_slug, $current_lang)
    {
        global $wpdb;
        $sub_querystr = 'SELECT count(sub_term_meta.term_id) FROM ' . $wpdb->termmeta . ' as sub_term_meta WHERE term_meta.term_id = sub_term_meta.term_id AND sub_term_meta.meta_value = "true" AND sub_term_meta.meta_key = "_devyai-publish"';
        $querystr = "SELECT term_meta.* FROM $wpdb->termmeta as term_meta WHERE (" . $sub_querystr . ") > 0 AND term_meta.meta_value = '" . $item . "' AND term_meta.meta_key = '_devyai-menu'";
        $metas = $wpdb->get_results($querystr, OBJECT);

        foreach ($metas as $meta) {
            $term = get_term($meta->term_id);
            $publish = get_term_meta($term->term_id, '_devyai-publish', true);
            $lang = get_term_meta($term->term_id, '_devyai-lang', true);
            if ($publish == 'true' && $lang == $current_lang)
                $item_output .= '<li id="menu-item-' . $term->term_id . '" class="menu-item menu-item-type-custom menu-item-object-custom nav-item dropdown menu-item menu-item-type-custom menu-item-object-custom nav-item dropdown"><a class="nav-link" href="' . $category_slug . '/' . $term->slug . '">' . $term->name . '</a></li>';
        }
        return $item_output;
    }

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $indent = ($depth) ? str_repeat("    ", $depth) : '';
        $classes = empty($item->classes) ? array() : (array)$item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        $class_names = "menu-item menu-item-type-custom menu-item-object-custom nav-item dropdown ";
        $class_names .= in_array("current_page_item", $item->classes) ? 'active ' : '';
        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $item_id = $id ? ' id="' . esc_attr($id) . '"' : '';
        $output .= $indent . '';
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $item_output = $args->before;
        $class_names .= " " . $class_names . " ";
        $item_output .= '<li ' . $item_id . ' class="' . $class_names . '">';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';

        $category_slug = get_site_url() . '/' . (!empty(get_option('woocommerce_permalinks')['category_base']) ? get_option('woocommerce_permalinks')['category_base'] : 'product-category');
        $current_lang = qtrans_getLanguage();

        if ($depth == 0 && !MenuBuilder::getInstance()->exists(-1)) {
            MenuBuilder::getInstance()->visit(-1);
            $this->static_childs($item_output, '', $category_slug, $current_lang);
        } elseif ($depth == 0 && !$item->has_children) {
            if ($this->static_childs_count($item->ID)) {
                $item_output .= '<ul class="dropdown-menu">';
                $this->static_childs($item_output, $item->ID, $category_slug, $current_lang);
                $item_output .= '</ul>';
            }
        } elseif ($depth == 1 && !MenuBuilder::getInstance()->exists($item->menu_item_parent)) {
            MenuBuilder::getInstance()->visit($item->menu_item_parent);

            $this->static_childs($item_output, $item->menu_item_parent, $category_slug, $current_lang);
        }

        $item_output .= $args->after;
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}
