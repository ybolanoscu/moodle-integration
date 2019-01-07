<?php
/**
 * Created by PhpStorm.
 */

class CourseTerm
{
    static $instance;
    private $term;

    /**
     * CourseTerm constructor.
     */
    public function __construct($term)
    {
        $this->term = $term;
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
            self::$instance = new CourseTerm($term);
        }
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param mixed $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getPrice()
    {
        return get_term_meta($this->term->term_id, '_devyai-price', true);
    }

    public function getStartDate()
    {
        return get_term_meta($this->term->term_id, '_devyai-start-date', true);
    }

    public function getAU()
    {
        return get_term_meta($this->term->term_id, '_devyai-au-page', true);
    }

    public function getImage($class = "")
    {
        $html = '';
        $meta = get_term_meta($this->term->term_id, 'thumbnail_id', true);
        if ($meta) {
            $tmp = wp_get_attachment_image_src($meta, array(220, 220), true);
            if (!empty($tmp))
                $html = '<img src="' . $tmp[0] . '" class="' . $class . '">';
        }
        return $html;
    }

    public function getTitle()
    {
        return $this->term->name;
    }

    public function getDescription()
    {
        return $this->term->description;
    }

    public function getApplyButton($text, $class)
    {
        return '<button type="button" id="apply_now" data-category-id="' . $this->term->term_id . '" class="' . $class . '">' . $text . '</button>';
    }

    public function getAttribute($meta, $format)
    {
        $value = get_term_meta($this->term->term_id, '_devyai-' . $meta, true);
        if ($format && $value instanceof \DateTime) {
            return $value->format($format);
        }
        return $value ? $value : '';
    }

    public function getCourses($term_id)
    {
        $args = array(
            'post_type' => 'product',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'terms' => array($term_id),
                    'include_children' => false
                )
            ),
            'orderby' => 'date', 'order' => 'ASC'
        );
        return new WP_Query($args);
    }

    public function calculateCategoryPrice($term_id)
    {
        $products = $this->getCourses($term_id);
        $price = 0;
        while ($products->have_posts()) : $products->the_post();
            global $product;
            $price += $product->price;
        endwhile;
        wp_reset_query();
        return $price;
    }
}
