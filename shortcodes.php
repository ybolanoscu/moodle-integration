<?php

require_once __DIR__ . '/frontend/MIFront.php';
require_once __DIR__ . '/frontend/CourseTerm.php';
require_once __DIR__ . '/frontend/ClassTerm.php';

add_shortcode('devyai-form', 'devyai_shortcode_formmaker');
function devyai_shortcode_formmaker($atts, $content = null)
{
    $values = shortcode_atts(array('fmk-id' => null), $atts);

    $frontend = new MIFront();

    return $frontend->formmaker($values);
}

add_shortcode('devyai-table', 'devyai_shortcode_periodict_table');
function devyai_shortcode_periodict_table($atts, $content = null)
{
    $values = shortcode_atts(
        array(
            'c7title' => 'Path of Courses',
        ),
        $atts
    );

    $frontend = new MIFront();

    return $frontend->periodic_table($values);
}

add_shortcode('devyai-collapse', 'devyai_shortcode_collapse');
function devyai_shortcode_collapse($atts, $content = null)
{
    $values = shortcode_atts(
        array(
            'id' => null,
            'includes' => '',
            'class' => 'accordion',
            'force' => null
        ),
        $atts
    );

    $frontend = new MIFront();

    return $frontend->collapsible($values);
}

add_shortcode('devyai-path-cards', 'devyai_shortcode_path_cards');
function devyai_shortcode_path_cards($atts, $content = null)
{
    $values = shortcode_atts(
        array(
            'c7title' => 'Path of Courses',
            'includes' => '',
            'force' => null
        ),
        $atts
    );

    $frontend = new MIFront();

    return $frontend->path_card($values);
}

add_shortcode('devyai-category-course', 'devyai_shortcode_category_course');
function devyai_shortcode_category_course()
{
    ob_start();
    include __DIR__ . '/frontend/template/template_tabs.php';
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

add_shortcode('devyai-category-course-overview', 'devyai_shortcode_category_courseoverview');
function devyai_shortcode_category_courseoverview()
{
    $cat_id = CourseTerm::getInstance()->getTerm()->term_id;
    $categories = get_categories(array('parent' => $cat_id, 'taxonomy' => 'product_cat'));

    $ids = '';
    while (!empty($categories)) {
        $category = array_pop($categories);
        $categories = array_merge(get_categories(array('parent' => $category->term_id, 'taxonomy' => 'product_cat')), $categories);
        $ids .= $category->term_id . ',';
    }
    $ids = trim($ids, ",");
    if (empty($ids))
        $ids .= $cat_id;

    $html = '';
    if (!empty($ids))
        $html = apply_filters("the_content", '[:' . qtrans_getLanguage() . ']' . "[devyai-collapse force='true' class='courses'  includes='$ids'][:]");
    return $html;
}

add_shortcode('devyai-category-course-competence', 'devyai_shortcode_category_coursecompetence');
function devyai_shortcode_category_coursecompetence()
{
    $cat_id = CourseTerm::getInstance()->getTerm()->term_id;
    $categories = get_categories(array('parent' => $cat_id, 'taxonomy' => 'product_cat'));

    $ids = '';
    while (!empty($categories)) {
        $category = array_pop($categories);
        $categories = array_merge(get_categories(array('parent' => $category->term_id, 'taxonomy' => 'product_cat')), $categories);
        $ids .= $category->term_id . ',';
    }
    $ids = trim($ids, ",");
    if (empty($ids))
        $ids .= $cat_id;
    $html = '';
    if (!empty($ids))
        $html = apply_filters("the_content", '[:' . qtrans_getLanguage() . ']' . "[devyai-path-cards force='true' class='courses'  includes='$ids'][:]");
    return $html;
}

add_shortcode('devyai-category-course-title', 'devyai_shortcode_category_coursetitle');
function devyai_shortcode_category_coursetitle()
{
    return CourseTerm::getInstance()->getTitle();
}

add_shortcode('devyai-category-course-description', 'devyai_shortcode_category_coursedescription');
function devyai_shortcode_category_coursedescription()
{
    return htmlspecialchars_decode(CourseTerm::getInstance()->getDescription());
}

add_shortcode('devyai-category-course-price', 'devyai_shortcode_category_courseprice');
function devyai_shortcode_category_courseprice()
{
    return CourseTerm::getInstance()->getPrice();
}

add_shortcode('devyai-category-course-image', 'devyai_shortcode_category_courseimage');
function devyai_shortcode_category_courseimage($atts)
{
    $values = shortcode_atts(array('class' => '',), $atts);

    return CourseTerm::getInstance()->getImage($values['class']);
}

add_shortcode('devyai-category-apply', 'devyai_shortcode_category_apply');
function devyai_shortcode_category_apply($atts, $content = null)
{
    $values = shortcode_atts(
        array(
            'text' => '',
            'class' => '',
        ),
        $atts
    );

    return CourseTerm::getInstance()->getApplyButton($values['text'], $values['class']);
}

add_shortcode('devyai-category-course-start-date', 'devyai_shortcode_category_coursestartdate');
add_shortcode('devyai-category-course-startdate', 'devyai_shortcode_category_coursestartdate');
function devyai_shortcode_category_coursestartdate()
{
    $date = CourseTerm::getInstance()->getStartDate();
    if ($date)
        return date_i18n('F, d', $date->getTimestamp());
    else
        return '';
}

add_shortcode('devyai-category-course-attribute', 'devyai_shortcode_category_course_attribute');
function devyai_shortcode_category_course_attribute($attrs)
{
    $values = shortcode_atts(array('attr' => '', 'format' => null), $attrs);

    return CourseTerm::getInstance()->getAttribute($values['attr'], $values['format']);
}

add_shortcode('current-language', 'devyai_shortcode_currentlanguage');
function devyai_shortcode_currentlanguage($atts, $content = null)
{
    return qtrans_getLanguage();
}

add_shortcode('devyai-tabs', 'devyai_shortcode_tabs');
function devyai_shortcode_tabs($atts, $content = null)
{
    $values = shortcode_atts(
        array(
            'id' => null,
        ),
        $atts
    );

    $frontend = new MIFront();

    return $frontend->posts($values);
}

add_shortcode('devyai-category-class', 'devyai_category_class');
function devyai_category_class()
{
    $devyai_options = get_option('devyai_options', new stdClass());
    if (is_string($devyai_options))
        $devyai_options = unserialize($devyai_options);
    else
        $devyai_options = new stdClass();

    if ($devyai_options->template_class) {
        $page_class = get_post($devyai_options->template_class);
        if (!$page_class)
            return '';

        $category = CourseTerm::getInstance()->getTerm();
        $courses = CourseTerm::getInstance()->getCourses($category->term_id);
        $html = '';
        while ($courses->have_posts()) :
            $courses->the_post();
            /** @var WC_Product $product */
            global $product;
            $GLOBALS['moodle_class'] = $product->get_id();
            $html .= apply_filters('the_content', $page_class->post_content);;
        endwhile;
        wp_reset_query();

        return $html;
    }

    return '';
}

add_shortcode('devyai-category-class-title', 'devyai_category_class_title');
function devyai_category_class_title()
{
    return ClassTerm::getTitle();
}

add_shortcode('devyai-category-class-description', 'devyai_category_class_description');
function devyai_category_class_description($attrs)
{
    $values = shortcode_atts(array('format' => true, 'length' => false), $attrs);
    return ClassTerm::getDescription($values);
}

add_shortcode('devyai-category-class-start-date', 'devyai_category_class_start_date');
function devyai_category_class_start_date($attrs)
{
    $values = shortcode_atts(array('format' => null), $attrs);
    return ClassTerm::getStartDate($values['format']);
}

add_shortcode('devyai-category-class-end-date', 'devyai_category_class_end_date');
function devyai_category_class_end_date($attrs)
{
    $values = shortcode_atts(array('format' => null), $attrs);
    return ClassTerm::getEndDate($values['format']);
}

add_shortcode('devyai-category-class-price', 'devyai_category_class_price');
function devyai_category_class_price()
{
    return ClassTerm::getPrice();
}

add_shortcode('devyai-category-class-image', 'devyai_category_class_image');
function devyai_category_class_image($attr)
{
    $values = shortcode_atts(array('class' => '', 'style' => '',), $attr);
    return ClassTerm::getImage($values);
}

add_shortcode('devyai-category-class-overview', 'devyai_category_class_overview');
function devyai_category_class_overview()
{
    return ClassTerm::getOverview();
}

add_shortcode('devyai-category-class-admission', 'devyai_category_class_admission');
function devyai_category_class_admission()
{
    return ClassTerm::getAdmission();
}

add_shortcode('devyai-category-class-difference', 'devyai_category_class_difference');
function devyai_category_class_difference()
{
    return ClassTerm::getDifference();
}

add_shortcode('devyai-category-class-cost-time', 'devyai_category_class_cost_time');
function devyai_category_class_cost_time()
{
    return ClassTerm::getCostTime();
}

add_shortcode('devyai-category-class-course-competences', 'devyai_category_class_course_competences');
function devyai_category_class_course_competences()
{
    return ClassTerm::getCourseCompetences();
}

add_shortcode('devyai-category-class-button-modal', 'devyai_category_class_button_modal');
function devyai_category_class_button_modal($attr)
{
    $values = shortcode_atts(array('class' => ''), $attr);
    return ClassTerm::getCourseButtonModal($values);
}
