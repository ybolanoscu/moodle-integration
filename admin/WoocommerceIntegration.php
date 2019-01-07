<?php

require_once __DIR__ . '/../MIBase.php';

class WoocommerceIntegration extends MIBase
{
    private static $_instance;

    /** @return $this */
    public static function instance()
    {
        if (!isset(self::$_instance))
            self::$_instance = new WoocommerceIntegration();
        return self::$_instance;
    }

    public function integrate()
    {
        add_action('woocommerce_product_options_general_product_data', array($this, 'woocommerce_product_custom_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'woocommerce_product_custom_fields_save'));
    }


    public function woocommerce_product_custom_fields()
    {
        global $post;

        echo '<div class="product_custom_field">';

        $from = get_post_meta($post->ID, '_product_availability_from', true);
        echo '<p class="form-field">
					<label for="_product_availability_from">' . esc_html__('Product Availability From', 'devyai') . '</label>
					<input type="text" class="short" name="_product_availability_from" id="_product_availability_from" value="' . esc_attr($from) . '" placeholder="' . esc_html(_x('From&hellip;', 'placeholder', 'devyai')) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr(apply_filters('woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])')) . '" />
				</p>';

        $to = get_post_meta($post->ID, '_product_availability_to', true);
        echo '<p class="form-field">
					<label for="_product_availability_to">' . esc_html__('Product Availability To', 'devyai') . '</label>
					<input type="text" class="short" name="_product_availability_to" id="_product_availability_to" value="' . esc_attr($to) . '" placeholder="' . esc_html(_x('From&hellip;', 'placeholder', 'devyai')) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr(apply_filters('woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])')) . '" />
				</p>';

        echo '</div>';
    }

    public function woocommerce_product_custom_fields_save($post_id)
    {
        $from = DateTime::createFromFormat('Y-m-d', @$_POST['_product_availability_from']);
        if ($from)
            update_post_meta($post_id, '_product_availability_from', esc_attr($from->format('Y-m-d')));
        $to = DateTime::createFromFormat('Y-m-d', @$_POST['_product_availability_to']);
        if ($to)
            update_post_meta($post_id, '_product_availability_to', esc_attr($to->format('Y-m-d')));
    }

}

