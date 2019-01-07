<?php

require_once __DIR__ . '/../MIBase.php';
require_once __DIR__ . '/../admin/Moodle.php';

class MIFront extends MIBase
{

    public function __construct()
    {
        parent::__construct();
        $GLOBALS['devyai'] = new stdClass();
    }

    public function init()
    {
        wp_enqueue_script('devyai-frontend', MIBase::pluginUrl() . "assets/js/frontend.js", array('jquery'));

        add_filter('woocommerce_account_menu_items', array($this, 'account_menu_items'), 10, 1);
        add_action('init', array($this, 'add_my_account_endpoint'));
        add_action('woocommerce_account_path_endpoint', array($this, 'path_endpoint_content'), 10);
        add_action('woocommerce_account_courses_endpoint', array($this, 'courses_endpoint_content'), 10);
        add_action('woocommerce_account_students_endpoint', array($this, 'students_endpoint_content'), 10);
        add_action('woocommerce_account_path_requests_endpoint', array($this, 'path_requests_endpoint_content'), 10);
        add_action('woocommerce_account_course_incoming_endpoint', array($this, 'course_incoming_endpoint_content'), 10);
        add_action('woocommerce_account_evaluations_endpoint', array($this, 'evaluations_endpoint_content'), 10);
        add_action('woocommerce_account_courses_not_ready_endpoint', array($this, 'courses_not_ready_content'), 10);
        add_action('woocommerce_account_categories_not_ready_endpoint', array($this, 'categories_not_ready_content'), 10);
        add_action('woocommerce_account_logout_endpoint', array($this, 'logout_endpoint_content'), 10);

        add_filter('query_vars', array($this, 'custom_query_vars'), 0);
        add_action('wp_loaded', array($this, 'custom_flush_rewrite_rules'));

        add_filter('login_redirect', array($this, 'woocommerce_login_redirect'), 10, 3);
        add_action('woocommerce_checkout_order_processed', array($this, 'update_meta_user_checkout'));

        add_action('plugins_loaded', array($this, 'after_loading'));
    }

    public function after_loading()
    {
//        $user = get_user_by( 'ID', get_current_user_id());
//        if ($user) {
//            var_dump($user->get_role_caps());
//            $user->remove_cap('student');
//        }
    }

    public function woocommerce_login_redirect($redirect, $url, $user)
    {
        $is_redirect = get_user_meta($user->ID, '_devyai_redirect', true);
        if ($is_redirect) {
            update_user_meta($user->ID, '_devyai_redirect', false);
            $redirect = wc_get_checkout_url();
        }
        return $redirect;
    }

    public function update_meta_user_checkout()
    {
        if (get_current_user_id() > 0) {
            update_user_meta(get_current_user_id(), '_devyai_redirect', false);
        }
    }

    public function modal_response($course_id)
    {
        /** @var WP_Post $post */
        $post = get_post($course_id);
        if ($post):
            setup_postdata($post);
            $GLOBALS['post'] = $post;

            ob_start();
            include __DIR__ . "/views/course_modal.html.php";
            $html = ob_get_contents();
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(array('success' => 1, 'html' => $html));
            die;
        endif;
        echo json_encode(array('success' => 0));
        die;
    }

    public function periodic_table($attributes)
    {
        $options = $this->getDevyaiOptions();
        $args = array('lp' => true);
        if (@$options->moodle_paths) {
            $args['includes'] = implode(',', $options->moodle_paths);
        }
        $form_id = @$options->form_request;

        list($categories, $course_by_categories) = $this->get_categories_courses($args);
        ob_start();
        include __DIR__ . "/views/table.html.php";
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    protected function get_categories_courses($attributes = array())
    {
        global $wpdb;
        $querystr = "SELECT $wpdb->posts.* FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product' ORDER BY $wpdb->posts.post_date DESC";

        $pageposts = $wpdb->get_results($querystr, OBJECT);
        $course_by_categories = array();
        $posts = array();
        foreach ($pageposts as $pagepost) {
            $posts[] = $pagepost->ID;
            $pagepost->category = get_the_terms($pagepost->ID, 'product_cat');
            if (!empty($pagepost->category)) {
                foreach ($pagepost->category as $category) {
                    if (!isset($course_by_categories[$category->term_id]))
                        $course_by_categories[$category->term_id] = array();
                    $course_by_categories[$category->term_id][] = $pagepost;
                }
            }
        }

        $language = qtrans_getLanguage();
        $root_categories = woocommerce_get_product_subcategories(@$this->getDevyaiOptions()->root_category);
        $ids = array_map(function ($cat) {
            return $cat->term_id;
        }, $root_categories);
        while (!empty($root_categories)) {
            $wc_category = array_pop($root_categories);
            $tmp = woocommerce_get_product_subcategories($wc_category->term_id);
            if (!empty($tmp)) {
                $root_categories = array_merge($root_categories, $tmp);
                $ids = array_merge(array_map(function ($cat) {
                    return $cat->term_id;
                }, $tmp), $ids);
            }
        }

        $parametrized = false;
        $categories = array();
        if (!empty($attributes['id'])) {
            $categories[] = get_term($attributes['id'], 'product_cat', OBJECT, 'raw');
            $parametrized = true;
        } elseif (!empty($attributes['includes'])) {
            $includes = explode(',', $attributes['includes']);
            if ($includes) {
                $parametrized = true;
                if (!isset($attributes['lp']) && !$attributes['lp'] && $language == 'es') {
                    $includes = array_intersect($ids, $includes);
                }
                $args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all', 'include' => $includes);
                $categories = wp_get_object_terms($posts, 'product_cat', $args);
            }
        } elseif (!empty($attributes['excludes'])) {
            $excludes = explode(',', $attributes['excludes']);
            if ($excludes) {
                $parametrized = true;
                $args = array();
                if ($language == 'es') {
                    $excludes = array_diff($ids, $excludes);
                    $args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all', 'include' => $excludes);
                } else {
                    $excludes = array_merge($excludes, $ids, array(@$this->getDevyaiOptions()->root_category));
                    $args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all', 'exclude' => $excludes);
                }
                $categories = wp_get_object_terms($posts, 'product_cat', $args);
            }
        }
        if (!$parametrized) {
            $args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all', 'include' => $ids);
            $categories = wp_get_object_terms($posts, 'product_cat', $args);
        }

        return array($categories, $course_by_categories);
    }

    public function collapsible($attributes)
    {
        list($categories, $course_by_categories) = $this->get_categories_courses($attributes);

        ob_start();
        include __DIR__ . '/views/collapse.html.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function posts($attributes)
    {
        ob_start();
        include __DIR__ . '/views/tabbes-posts.html.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function path_card($values)
    {
        $options = $this->getDevyaiOptions();
        $args = array_merge_recursive([], $values);
        if (@$options->moodle_paths) {
            $args['excludes'] = implode(',', $options->moodle_paths);
        }
        list($categories, $course_by_categories) = $this->get_categories_courses($args);
        ob_start();
        include __DIR__ . '/views/path_card.html.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html ;
    }

    public function modal_category($category_id)
    {
        $category = get_term($category_id, 'product_cat', OBJECT, 'raw');
        if ($category):
            ob_start();
            include __DIR__ . "/views/category_modal.html.php";
            $html = ob_get_contents();
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(array('success' => 1, 'html' => $html));
            die;
        endif;
        echo json_encode(array('success' => 0));
        die;
    }

    public function get_posts($values)
    {
        global $wpdb;
        $querystr = "SELECT $wpdb->posts.* FROM $wpdb->posts WHERE $wpdb->posts.id IN $values ORDER BY $wpdb->posts.post_date DESC";

        $posts = $wpdb->get_results($querystr, OBJECT);
        return $posts;
    }


    public function account_menu_items($items)
    {
        unset($items['customer-logout']);

        foreach (wp_get_current_user()->roles as $role) {
            switch ($role) {
                case 'register':
                    $items['students'] = __('My Students', 'devyai');
                    break;
                case 'academic':
                    $items['enrol_courses'] = __('Courses', 'devyai');
                    $items['path_requests'] = __('Path Requests', 'devyai');
                    break;
                case 'accounting':
                    $items['enrol_courses'] = __('Courses', 'devyai');
                    $items['course_incoming'] = __('Incomming Courses', 'devyai');
                    break;
                case 'student_last':
                    $items['path'] = __('My Paths', 'devyai');
                    $items['courses'] = __('My Courses', 'devyai');
                    $items['evaluations'] = __('My Evaluations', 'devyai');
                    break;
                case 'course_manager':
                    $items['courses_not_ready'] = __('Course Status', 'devyai');
                    $items['categories_not_ready'] = __('Category Status', 'devyai');
                    break;
            }
        }

        $items['logout'] = __('Logout', 'devyai');
        return $items;
    }

    public function path_endpoint_content()
    {
        if ($this->is_endpoint('path'))
            require_once __DIR__ . "/views/my-paths.html.php";
    }

    /**
     * @throws Exception
     */
    public function courses_endpoint_content()
    {
        if ($this->is_endpoint('courses') && is_user_logged_in()) {
            /** @var WP_User|null $user */
            $user = wp_get_current_user();
            $entries = array();
            if ($user_id = Moodle::instance()->userExists($user->user_email))
                $entries = Moodle::instance()->getUserCourses($user_id);

            //Progress operation
            foreach ($entries as $entry) {
                $completed = $pending = $performance = $sum = 0;
                array_map(function ($status) use (&$completed, &$pending) {
                    $status->state != 0 ? $completed++ : $pending++;
                }, Moodle::instance()->getUserCourseActivitiesCompletion($user_id, $entry->id)->statuses);
                $entry->pending = $pending;
                $entry->completed = $completed;
                $entry->progress = $completed / (($completed + $pending) == 0 ? 1 : ($completed + $pending)) * 100;

                //Performance operation
                array_map(function ($item) use (&$sum) {
                    $sum += $item->gradeRaw;
                }, $grades = Moodle::instance()->getGradesCourseUser($entry->id, $user_id)->usergrades[0]->gradeitems);
                $entry->average = $sum / (count($grades) ? count($grades) : 1);

            }
            require_once __DIR__ . "/views/my-courses.html.php";
        }
    }

    /**
     * @throws Exception
     */
    public function students_endpoint_content()
    {
        if ($this->is_endpoint('students')) {
            $entries = array();
            $entries = Moodle::instance()->getUsers();
            require_once __DIR__ . "/views/my-students.html.php";
        }
    }

    public function get_courses_by_user($user_id)
    {
        $courses = Moodle::instance()->getUserCourses($user_id);
        return $courses;
    }


    public function modal_courses_by_user($user_id)
    {
        $courses = $this->get_courses_by_user($user_id);
        if (!empty($courses)):
            ob_start();
            include __DIR__ . "/views/courses_by_user_modal.html.php";
            $html = ob_get_contents();
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(array('success' => 1, 'html' => $html));
            die;
        endif;
        echo json_encode(array('success' => 0));
        die;
    }

    public function enrol_courses_endpoint_content()
    {
        if ($this->is_endpoint('enrol_courses') && is_user_logged_in()) {
            global $wpdb;
            $entries = array();
            $querystr = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product' ORDER BY $wpdb->posts.post_date DESC";
            $pageposts = $wpdb->get_results($querystr, OBJECT);

            foreach ($pageposts as $pagepost) {
                $entry = array(
                    'name' => $pagepost->post_title,
                    'from' => get_post_meta($pagepost->ID, '_product_availability_from', true),
                    'to' => get_post_meta($pagepost->ID, '_product_availability_to', true),
                );

                $course_id = get_post_meta($pagepost->ID, '_course_id', true);
                $usersEnrolled = Moodle::instance()->getCourseUsersEnrolled($course_id);
                $amount_students = count($usersEnrolled) > 0 ? count($usersEnrolled) : null;
                $entry['qta_students'] = $amount_students;

                $sumGrades = 0;
                foreach ($usersEnrolled as $user) {
                    $grades = Moodle::instance()->getGradesCourseUser($course_id, $user->id);
                    foreach ($grades->usergrades as $item) {
                        foreach ($item->gradeitems as $grade) {
                            $sumGrades += $grade->graderaw;
                        };
                    }
                }

                $entry['avg_grade'] = $amount_students > 0 ? $sumGrades / $amount_students : null;

                $entries[] = $entry;
            }
            require_once __DIR__ . "/views/enrol-courses.html.php";
        }
    }

    /**
     * @throws Exception
     */
    public function categories_not_ready_content()
    {
        if ($this->is_endpoint('categories_not_ready') && is_user_logged_in()) {
            global $wpdb;

            $menus = wp_get_nav_menu_items('top-menu', array('post_parent' => 0));
            $mapMenu = array();
            foreach ($menus as $menu) {
                $mapMenu['m' . $menu->ID] = $menu->title;
            }

            $languages = array();
            $enabled_languages = qtrans_getSortedLanguages();
            $language_names = get_option('qtranslate_language_names');
            foreach ($enabled_languages as $enable_language) {
                $languages[] = array('code' => $enable_language, 'label' => qtrans_getLanguageName($enable_language));
            }

            $categories = get_terms(array('taxonomy' => "product_cat", 'number' => 1000));
            require_once __DIR__ . "/views/category-not-ready.html.php";
        }
    }

    /**
     * @throws Exception
     */
    public function courses_not_ready_content()
    {
        if ($this->is_endpoint('courses_not_ready') && is_user_logged_in()) {
            global $wpdb;
            $entries = array();
            $querystr = "SELECT $wpdb->posts.* FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product' ORDER BY $wpdb->posts.post_date DESC";
            $courses = $wpdb->get_results($querystr, OBJECT);
            require_once __DIR__ . "/views/courses-not-ready.html.php";
        }
    }

    /**
     * @throws Exception
     */
    public function evaluations_endpoint_content()
    {
        if ($this->is_endpoint('evaluations') && is_user_logged_in()) {
            /** @var WP_User $user */
            $user = wp_get_current_user();
            $entries = array();
            if ($user_id = Moodle::instance()->userExists($user->user_email)) {
                $courses = Moodle::instance()->getUserCourses($user_id);
                foreach ($courses as $entry) {
                    $entries[$entry->id] = new stdClass();
                    $entries[$entry->id]->course = $entry;
                    $entries[$entry->id]->evaluations = Moodle::instance()->getUserCourseEvaluations($user_id, $entry->id);
                }
            }
            require_once __DIR__ . "/views/my-evaluations.html.php";
        }
    }

    public function add_my_account_endpoint()
    {
        foreach (wp_get_current_user()->roles as $role)
            switch ($role) {
                case 'register':
                    add_rewrite_endpoint('students', EP_ROOT | EP_PAGES);
                    break;
                case 'academic':
                    add_rewrite_endpoint('enrol_courses', EP_ROOT | EP_PAGES);
                    add_rewrite_endpoint('path_requests', EP_ROOT | EP_PAGES);
                    break;
                case 'accounting':
                    add_rewrite_endpoint('enrol_courses', EP_ROOT | EP_PAGES);
                    add_rewrite_endpoint('course_incoming', EP_ROOT | EP_PAGES);
                    break;
                case 'student_last':
                    add_rewrite_endpoint('courses', EP_ROOT | EP_PAGES);
                    add_rewrite_endpoint('path', EP_ROOT | EP_PAGES);
                    add_rewrite_endpoint('evaluations', EP_ROOT | EP_PAGES);
                    break;
                case 'course_manager':
                    add_rewrite_endpoint('courses_not_ready', EP_ROOT | EP_PAGES);
                    add_rewrite_endpoint('categories_not_ready', EP_ROOT | EP_PAGES);
                    break;
            }
    }

    public function is_endpoint($endpoint = false)
    {
        global $wp_query;
        if (!$wp_query)
            return false;
        return isset($wp_query->query[$endpoint]);
    }

    function custom_query_vars($vars)
    {
        $vars[] = 'path';
        $vars[] = 'courses';
        $vars[] = 'students';
        $vars[] = 'enrol_courses';
        $vars[] = 'path_requests';
        $vars[] = 'course_incoming';
        $vars[] = 'evaluations';
        $vars[] = 'courses_not_ready';
        $vars[] = 'categories_not_ready';
        $vars[] = 'logout';
        return $vars;
    }

    function custom_flush_rewrite_rules()
    {
        flush_rewrite_rules();
    }

    function addToCart($id)
    {
        try {
            WC()->cart->add_to_cart($id);
            header('Content-Type: application/json');
            echo json_encode(array('success' => 1));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => 0));
        }
        die;
    }

    function removeFromCart($id)
    {
        try {
            foreach (WC()->cart->get_cart() as $key => $cart_item) {
                if ($cart_item['product_id'] == $id)
                    WC()->cart->remove_cart_item($key);
            }
            header('Content-Type: application/json');
            echo json_encode(array('success' => 1));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => 0));
        }
        die;
    }

    function addPath($id)
    {

        $_SESSION['path'] = [];
        try {
            list($categories, $courses) = $this->get_categories_courses(['id' => $id]);
            foreach (current($courses) as $course) {
                WC()->cart->add_to_cart($course->ID);
            }
            header('Content-Type: application/json');
            echo json_encode(array('success' => 1));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => 0));
        }
        die;
    }

    function applyNow($id, $class)
    {
        $route = wc_get_checkout_url();
        try {
            if (get_current_user_id() > 0)
                update_user_meta(get_current_user_id(), '_devyai_redirect', true);
            else
                $route = get_permalink(377);

            $filtered = [];
            if ($class) {
                $post = get_post($class);
            }
            else {
                list($categories, $courses) = $this->get_categories_courses(['id' => $id]);
                array_walk($courses, function ($value, $index) use ($id, &$filtered) {
                    if ($index == $id)
                        $filtered[] = $value;
                });
            }

            WC()->cart->empty_cart();
            if ($class && $post) {
                    WC()->cart->add_to_cart($post->ID);
            } else {
                foreach (current($filtered) as $course) {
                    WC()->cart->add_to_cart($course->ID);
                }
            }
            header('Content-Type: application/json');
            echo json_encode(array('success' => 1, 'route' => $route));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => 0));
        }
        die;
    }

    public function path_requests_endpoint_content()
    {
        if ($this->is_endpoint('path_requests'))
            require_once __DIR__ . "/views/path-requests.html.php";
    }

    public function course_incoming_endpoint_content()
    {
        if ($this->is_endpoint('course_incoming')) {
            global $wpdb;
            $entries = array();
            $querystr = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product' ORDER BY $wpdb->posts.post_date DESC";
            $pageposts = $wpdb->get_results($querystr, OBJECT);

            foreach ($pageposts as $pagepost) {
                $entry = array(
                    'name' => $pagepost->post_title,
                    'from' => get_post_meta($pagepost->ID, '_product_availability_from', true),
                    'to' => get_post_meta($pagepost->ID, '_product_availability_to', true),
                );
                $course_id = get_post_meta($pagepost->ID, '_course_id', true);
                $usersEnrolled = Moodle::instance()->getCourseUsersEnrolled($course_id);
                $amount_students = count($usersEnrolled) > 0 ? count($usersEnrolled) : null;
                $entry['qta_students'] = $amount_students;
                $product = wc_get_product($pagepost->ID);
                $entry['incoming'] = $amount_students ? $product->get_price() * $amount_students : null;

                $entries[] = $entry;
            }

            require_once __DIR__ . "/views/course-incoming.html.php";
        }
    }

    public function logout_endpoint_content()
    {
        wp_safe_redirect(wp_logout_url());
        exit();
    }

    public function formmaker($values)
    {
        $fmkid = $values['fmk-id'];
        if ($fmkid) {
            ob_start();
            include __DIR__ . '/views/form_maker.html.php';
            $html = ob_get_contents();
            ob_end_clean();
            unset($_SESSION['messages']);
            return $html;
        }
        return '';
    }
}
