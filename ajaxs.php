<?php

require_once __DIR__ . '/frontend/MIFront.php';
require_once __DIR__ . '/frontend/CourseTerm.php';

add_action("wp_ajax_periodic_ajax_modal", "periodic_ajax_modal_callback");
add_action("wp_ajax_nopriv_periodic_ajax_modal", "periodic_ajax_modal_callback");

function periodic_ajax_modal_callback()
{
    $frontend = new MIFront();

    return $frontend->modal_response($_POST['course_id']);
}

add_action("wp_ajax_category_ajax_modal", "category_ajax_modal_callback");
add_action("wp_ajax_nopriv_category_ajax_modal", "category_ajax_modal_callback");

function category_ajax_modal_callback()
{
    $frontend = new MIFront();

    return $frontend->modal_category($_POST['category_id']);
}

add_action("wp_ajax_periodic_ajax_add_cart", "periodic_ajax_add_cart_callback");
add_action("wp_ajax_nopriv_periodic_ajax_add_cart", "periodic_ajax_add_cart_callback");

function periodic_ajax_add_cart_callback()
{
    $frontend = new MIFront();

    return $frontend->addToCart($_POST['course_id']);
}

add_action("wp_ajax_periodic_ajax_remove_from_cart", "periodic_ajax_remove_from_cart_callback");
add_action("wp_ajax_nopriv_periodic_ajax_remove_from_cart", "periodic_ajax_remove_from_cart_callback");

function periodic_ajax_remove_from_cart_callback()
{
    $frontend = new MIFront();

    return $frontend->removeFromCart($_POST['course_id']);
}

add_action("wp_ajax_periodic_ajax_apply_now", "periodic_ajax_apply_now_callback");
add_action("wp_ajax_nopriv_periodic_ajax_apply_now", "periodic_ajax_apply_now_callback");

function periodic_ajax_apply_now_callback()
{
    $frontend = new MIFront();

    return $frontend->applyNow($_POST['category_id'], $_POST['class_id']);
}


add_action("wp_ajax_periodic_ajax_add_path", "periodic_ajax_add_path_callback");
add_action("wp_ajax_nopriv_periodic_ajax_add_path", "periodic_ajax_add_callback");

function periodic_ajax_add_path_callback()
{
    $frontend = new MIFront();

    return $frontend->addPath($_POST['category_id']);
}


add_action("wp_ajax_dashboard_courses_by_user", "dashboard_courses_by_user_callback");
add_action("wp_ajax_nopriv_dashboard_courses_by_user", "dashboard_courses_by_user_callback");

function dashboard_courses_by_user_callback()
{
    $frontend = new MIFront();

    return $frontend->modal_courses_by_user($_POST['user_id']);
}

add_action('wp_ajax_update_ajax_course', 'update_ajax_course');

function update_ajax_course()
{
    $array = array('success' => false);
    $product_id = @$_POST['course'];
    $price = @$_POST['price'];
    $start = DateTime::createFromFormat('d/m/Y', @$_POST['start_date']);
    $end = DateTime::createFromFormat('d/m/Y', @$_POST['end_date']);

    $description_overview = @$_POST['description_overview'];
    $description_cost_time = @$_POST['description_cost_time'];
    $description_admission = @$_POST['description_admission'];
    $description_difference = @$_POST['description_difference'];
    $description_course_competences = @$_POST['description_course_competences'];

    /** @var WC_Product_Simple $product */
    $product = wc_get_product($product_id);
    if ($product) {
        $course_id = get_post_meta($product_id, '_course_id', true);
        if (!empty($course_id)) {
            if (Moodle::instance()->udpateCourse($course_id, $start, $end)) {
                update_post_meta($product_id, '_saved', true);
                update_post_meta($product_id, '_product_availability_from', $start ? $start->format('Y-m-d') : '');
                update_post_meta($product_id, '_product_availability_to', $end ? $end->format('Y-m-d') : '');

                update_post_meta($product_id, '_devyai-description-overview', $description_overview);
                update_post_meta($product_id, '_devyai-description-cost-time', $description_cost_time);
                update_post_meta($product_id, '_devyai-description-admission', $description_admission);
                update_post_meta($product_id, '_devyai-description-difference', $description_difference);
                update_post_meta($product_id, '_devyai-description-course-competences', $description_course_competences);

                $product->set_props(array('price' => $price, 'regular_price' => $price));
                $product->save();
                $array['success'] = true;
            }
        }
    } else
        $array['message'] = __('Course not found on the system. Please reload and try again.');
    wp_send_json($array);
}

add_action('wp_ajax_update_ajax_get_course_class', 'update_ajax_get_course_class');

function update_ajax_get_course_class()
{
    $array = array('success' => false);
    $product_id = @$_POST['course'];
    /** @var WC_Product_Simple $product */
    $product = wc_get_product($product_id);
    if ($product) {
        $class = array();
        $class['price'] = $product->get_price();

        $class['start'] = get_post_meta($product_id, '_product_availability_from', true);
        $class['end'] = get_post_meta($product_id, '_product_availability_to', true);
        $start = DateTime::createFromFormat('Y-m-d', $class['start']);
        $class['start'] = $start ? $start->format('d/m/Y') : '';
        $end = DateTime::createFromFormat('Y-m-d', $class['end']);
        $class['end'] = $end ? $end->format('d/m/Y') : '';

        $class['description_overview'] = get_post_meta($product_id, '_devyai-description-overview', true) ? get_post_meta($product_id, '_devyai-description-overview', true) : '';
        $class['description_admission'] = get_post_meta($product_id, '_devyai-description-admission', true) ? get_post_meta($product_id, '_devyai-description-admission', true) : '';
        $class['description_difference'] = get_post_meta($product_id, '_devyai-description-difference', true) ? get_post_meta($product_id, '_devyai-description-difference', true) : '';
        $class['description_cost_time'] = get_post_meta($product_id, '_devyai-description-cost-time', true) ? get_post_meta($product_id, '_devyai-description-cost-time', true) : '';
        $class['description_course_competences'] = get_post_meta($product_id, '_devyai-description-course-competences', true) ? get_post_meta($product_id, '_devyai-description-course-competences', true) : '';

        $array['class'] = $class;
        $array['success'] = true;
    } else
        $array['message'] = __('Course not found on the system. Please reload and try again.');

    wp_send_json($array);
}

add_action('wp_ajax_update_ajax_get_course_category', 'update_ajax_get_course_category');

function update_ajax_get_course_category()
{
    $array = array('success' => false);
    $product_id = @$_POST['course'];
    $category = get_term($product_id);
    if ($category) {
        $cat = array();
        $cat['price'] = CourseTerm::getInstance()->calculateCategoryPrice($category->term_id);
        $cat['publish'] = get_term_meta($category->term_id, '_devyai-publish', true) ? get_term_meta($category->term_id, '_devyai-publish', true) : '';
        $dt = get_term_meta($category->term_id, '_devyai-start-date', true);
        $cat['start'] = $dt ? $dt->format('d/m/Y') : '';
        $cat['menu'] = get_term_meta($category->term_id, '_devyai-menu', true) ? get_term_meta($category->term_id, '_devyai-menu', true) : '';
        $cat['lang'] = get_term_meta($category->term_id, '_devyai-lang', true) ? get_term_meta($category->term_id, '_devyai-lang', true) : '';

        $cat['description_overview'] = get_term_meta($category->term_id, '_devyai-description-overview', true) ? get_term_meta($category->term_id, '_devyai-description-overview', true) : '';
        $cat['description_admission'] = get_term_meta($category->term_id, '_devyai-description-admission', true) ? get_term_meta($category->term_id, '_devyai-description-admission', true) : '';
        $cat['description_difference'] = get_term_meta($category->term_id, '_devyai-description-difference', true) ? get_term_meta($category->term_id, '_devyai-description-difference', true) : '';
        $cat['description_cost_time'] = get_term_meta($category->term_id, '_devyai-description-cost-time', true) ? get_term_meta($category->term_id, '_devyai-description-cost-time', true) : '';
        $cat['description_course_competences'] = get_term_meta($category->term_id, '_devyai-description-course-competences', true) ? get_term_meta($category->term_id, '_devyai-description-course-competences', true) : '';

        $array['success'] = true;
        $array['category'] = $cat;
    } else
        $array['message'] = __('Course not found on the system. Please reload and try again.');

    wp_send_json($array);
}

add_action('wp_ajax_update_ajax_course_category', 'update_ajax_course_category');

function update_ajax_course_category()
{
    $array = array('success' => false);
    $product_id = @$_POST['course'];
    $price = @$_POST['price'];
    $start = DateTime::createFromFormat('d/m/Y', @$_POST['start_date']);
    $publish = @$_POST['publish'];
    $menu = @$_POST['menu'];
    $lang = @$_POST['lang'];

    $description_overview = @$_POST['description_overview'];
    $description_cost_time = @$_POST['description_cost_time'];
    $description_admission = @$_POST['description_admission'];
    $description_difference = @$_POST['description_difference'];
    $description_course_competences = @$_POST['description_course_competences'];

    $category = get_term($product_id);
    if ($category) {
        $valid = true;
        if ($price < 1 && $publish == 'true') {
            $array['message'] = __("The course could't be publish if not have a price greater than 0. Please reload and try again.");
        }

        if ($valid) {
            $args = array(
                'post_type' => 'product', 'posts_per_page' => 10000,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => array($category->term_id),
                        'include_children' => false
                    )),
                'orderby' => 'date', 'order' => 'ASC');
            $products = new WP_Query($args);
            while ($products->have_posts()) : $products->the_post();
                $date = get_post_meta(get_the_ID(), '_product_availability_from', true);
                $dt = DateTime::createFromFormat('Y-m-d', $date);
                if ($start > $dt) {
                    $valid = false;
                    $array['message'] = __("The course could't start after their class. Please try again.");
                    break;
                }
            endwhile;
            wp_reset_query();
        }

        if ($valid) {
            update_term_meta($category->term_id, '_devyai-price', $price);
            update_term_meta($category->term_id, '_devyai-publish', $publish);
            update_term_meta($category->term_id, '_devyai-start-date', $start);
            update_term_meta($category->term_id, '_devyai-menu', $menu);
            update_term_meta($category->term_id, '_devyai-lang', $lang);

            update_term_meta($category->term_id, '_saved', true);
            update_term_meta($category->term_id, '_devyai-description-overview', $description_overview);
            update_term_meta($category->term_id, '_devyai-description-cost-time', $description_cost_time);
            update_term_meta($category->term_id, '_devyai-description-admission', $description_admission);
            update_term_meta($category->term_id, '_devyai-description-difference', $description_difference);
            update_term_meta($category->term_id, '_devyai-description-course-competences', $description_course_competences);

            $array['success'] = true;
        }
    } else
        $array['message'] = __('Course not found on the system. Please reload and try again.');

    wp_send_json($array);
}
