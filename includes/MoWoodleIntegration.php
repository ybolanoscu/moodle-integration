<?php
/**
 * MoWoodle Integration
 */

class MoWoodleIntegration
{
    private static $_instance;

    /** @return $this */
    public static function instance()
    {
        if (!isset(self::$_instance))
            self::$_instance = new MoWoodleIntegration();
        return self::$_instance;
    }

    public function init()
    {
        add_action('woodle_course_sync_product', array($this, 'woodle_course_sync_product'));
        add_action('woodle_course_after_sync', array($this, 'woodle_course_after_sync'));
    }

    public function woodle_course_after_sync($attr)
    {
        global $wpdb;
        $tablename = $wpdb->prefix . MIBase::TABLE_NAME;
        $entries = $wpdb->get_results("SELECT namepath, id_courses FROM $tablename WHERE status = 1;", ARRAY_A);
        foreach ($entries as $entry) {
            $category = get_term_by('name', $entry['namepath'], 'product_cat');
            $id_courses = explode('|', $entry['id_courses']);
            if ($category) {
                foreach ($id_courses as $id_course) {
                    $term_ids = [$category->term_id];
                    $terms = wp_get_object_terms($id_course, 'product_cat');
                    if (count($terms) > 0) {
                        foreach ($terms as $item)
                            $term_ids[] = $item->term_id;
                    }
                    wp_set_object_terms($id_course, $term_ids, 'product_cat');
                }
            }
        }

        $notificate = '';
        $querystr = "SELECT $wpdb->posts.* FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product' ORDER BY $wpdb->posts.post_date DESC";
        $courses = $wpdb->get_results($querystr, OBJECT);

        foreach ($courses as $i => $course) {
            $product = wc_get_product($course->ID);
            if (empty($product->price))
                $notificate .= $course->post_title . '<br/>';
        }

        if ($notificate != '') {
            $stdClass = new stdClass();
            $devyai_options = get_option('devyai_options', $stdClass);
            if (is_string($devyai_options)) {
                $devyai_options = unserialize($devyai_options);
            } else {
                $devyai_options = new stdClass();
            }

            $admin_email = get_site_option('admin_email');
            if ($admin_email == '') {
                $admin_email = 'no-reply@' . $_SERVER['SERVER_NAME'];
            }
            $email = @$devyai_options->notification_email ? $devyai_options->notification_email : $admin_email;

            ob_start();
            $email_content = array(
                'first_p' => _q('<p>Please check the next list of courses without a price:</p>' . $notificate)
            );
            include_once __DIR__ . '/../includes/template_mail.php';
            $message = ob_get_clean();

            $subject = __("Courses whitout price - " . get_bloginfo('name'));
            $headers = array();

            $headers[] = "From: Atlantis University <{$admin_email}>" . "\r\n";
            $headers[] = "Reply-To: Atlantis University <{$admin_email}>" . "\r\n";
            wp_mail($email, $subject, $message, $headers);
        }
    }

    public function woodle_course_sync_product($attr)
    {
        if (isset($attr[1]['startdate'])) {
            if ($attr[1]['startdate'] > 0) {
                $date = DateTime::createFromFormat('U', $attr[1]['startdate']);
                if ($date)
                    update_post_meta($attr[0], '_product_availability_from', $date->format('Y-m-d'));
            }
        }
        if (isset($attr[1]['enddate'])) {
            if ($attr[1]['enddate'] > 0) {
                $date = DateTime::createFromFormat('U', $attr[1]['enddate']);
                if ($date)
                    update_post_meta($attr[0], '_product_availability_to', $date->format('Y-m-d'));
            }
        }
    }
}