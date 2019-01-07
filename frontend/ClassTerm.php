<?php
/**
 * Created by PhpStorm.
 */

class ClassTerm
{
    public static function getTitle()
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $post = get_post($moodle_class);
            return $post->post_title;
        }
    }

    public static function getDescription($values)
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $post = get_post($moodle_class);
            if ($values['format'] === '0') {
                $html = strip_tags($post->post_content);
                if (($length = (int)$values['length']) > 0) {
                    $part = substr($html, 0, $length);
                    $part .= strlen($html) > $length ? '...' : '';
                    $html = $part;
                }
            } else
                $html = apply_filters('the_content', $post->post_content);
            return $html;
        }
        return '';
    }

    public static function getStartDate($format = 'd/m/Y')
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $from = get_post_meta($moodle_class, '_product_availability_from', true);
            $date = DateTime::createFromFormat('Y-m-d', $from);
            if ($date)
                return $date->format($format);
        }
        return '';
    }

    public static function getEndDate($format = 'd/m/Y')
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $to = get_post_meta($moodle_class, '_product_availability_to', true);
            $date = DateTime::createFromFormat('Y-m-d', $to);
            if ($date)
                return $date->format($format);
        }
        return '';
    }

    public static function getPrice()
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $product = wc_get_product($moodle_class);
            return $product->get_price();
        }
        return '';
    }

    public static function getImage($values)
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $html = '';
            $meta = get_post_thumbnail_id($moodle_class);
            if ($meta) {
                $tmp = wp_get_attachment_image_src($meta, array(220, 220), true);
                if (!empty($tmp))
                    $html = '<img src="' . $tmp[0] . '" class="' . @$values['class'] . '" style="' . @$values['style'] . '">';
            }
            return $html;
        }
        return '';
    }

    public static function getOverview()
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $product_id = $moodle_class;
            return get_post_meta($product_id, '_devyai-description-overview', true) ? get_post_meta($product_id, '_devyai-description-overview', true) : '';
        }
        return '';
    }

    public static function getAdmission()
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $product_id = $moodle_class;
            return get_post_meta($product_id, '_devyai-description-admission', true) ? get_post_meta($product_id, '_devyai-description-admission', true) : '';
        }
        return '';
    }

    public static function getDifference()
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $product_id = $moodle_class;
            return get_post_meta($product_id, '_devyai-description-difference', true) ? get_post_meta($product_id, '_devyai-description-difference', true) : '';
        }
        return '';
    }

    public static function getCostTime()
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $product_id = $moodle_class;
            return get_post_meta($product_id, '_devyai-description-cost-time', true) ? get_post_meta($product_id, '_devyai-description-cost-time', true) : '';
        }
        return '';
    }

    public static function getCourseCompetences()
    {
        global $moodle_class;
        if (!empty($moodle_class)) {
            $product_id = $moodle_class;
            return get_post_meta($product_id, '_devyai-description-course-competences', true) ? get_post_meta($product_id, '_devyai-description-course-competences', true) : '';
        }
        return '';
    }
    
    public static function getCourseButtonModal($values) 
    {
        global $moodle_class;
        return "<a href='#' class='class-button-modal " . @$values['class'] . "' data-course-id='$moodle_class'>" . __('Continue reading') . "</a>";
    }
}
