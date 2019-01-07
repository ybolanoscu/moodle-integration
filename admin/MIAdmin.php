<?php

require_once __DIR__ . '/../MIBase.php';
require_once __DIR__ . '/WoocommerceIntegration.php';
require_once __DIR__ . '/../../../../wp-admin/includes/screen.php';
require_once __DIR__ . '/../includes/MoWoodleIntegration.php';

class MIAdmin extends MIBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'add_settings_page'), 100);
        add_action('activated_plugin', array($this, 'plugins_loaded'));
        WoocommerceIntegration::instance()->integrate();
        MoWoodleIntegration::instance()->init();
    }

    public function plugins_loaded()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . MIBase::TABLE_NAME;
        $table_user = $wpdb->prefix . MIBase::TABLE_NAME_USER;

        $sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			wpcf7_id INTEGER(11) NOT NULL,
			email VARCHAR(250) NOT NULL,
			lang VARCHAR(20) NOT NULL,
			firstname VARCHAR(250) NOT NULL,
			lastname VARCHAR(250) NOT NULL,
			namepath text NOT NULL,
			descriptionpath text NOT NULL,
			description text NOT NULL,
			paths text,
			id_courses text,
			courses text,
			status smallint(2) NOT NULL,
			PRIMARY KEY  (id)
		);";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $sql = "CREATE TABLE $table_user (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			title VARCHAR(250) NOT NULL,
			email VARCHAR(250) NOT NULL,
			pdf_link text NOT NULL,
			PRIMARY KEY (id)
		);";
        dbDelta($sql);
    }

    /**
     * Add options page
     */
    public function add_settings_page()
    {
        add_menu_page(
            __('DevYai', self::TEXT_DOMAIN),
            __('DevYai', self::TEXT_DOMAIN),
            'manage_options',
            'devyai-moodle-integration',
            array($this, 'home_page'),
            $this->plugin_url . 'assets/images/envelope.png'
        );

        add_submenu_page('devyai-moodle-integration',
            __('Settings', self::TEXT_DOMAIN),
            __('Settings', self::TEXT_DOMAIN),
            'manage_options',
            'devyai-moodle-integration-setting',
            array($this, 'settings_page')
        );

        function my_admin_enqueue($hook_suffix)
        {
            if (preg_match('/devyai-moodle-integration/', $hook_suffix)) {
                wp_enqueue_style('bootstrap-min', MIBase::pluginUrl() . 'assets/css/bootstrap.min.css');
                wp_enqueue_style('style', MIBase::pluginUrl() . 'assets/css/style.css');
                wp_enqueue_style('select2-min', MIBase::pluginUrl() . 'assets/select2/css/select2.min.css');

                wp_enqueue_script('jquery-bt', MIBase::pluginUrl() . "assets/js/jquery.min.js");
                wp_enqueue_script('popper', MIBase::pluginUrl() . "assets/js/popper.js");
                wp_enqueue_script('bootstrap', MIBase::pluginUrl() . "assets/js/bootstrap.min.js");
                wp_enqueue_script('select2-min', MIBase::pluginUrl() . 'assets/select2/js/select2.full.min.js');
            } elseif (preg_match('/post.php/', $hook_suffix)) {
                wp_enqueue_script('devyai-admin', MIBase::pluginUrl() . "assets/js/product.js", array('jquery'), false, true);
            }
        }

        add_action('admin_enqueue_scripts', 'my_admin_enqueue');
    }

    public function home_page()
    {
        include __DIR__ . '/views/home.html.php';
    }

    public function settings_page()
    {
        $stdClass = new stdClass();
        $devyai_options = get_option('devyai_options', $stdClass);
        if (is_string($devyai_options)) {
            $devyai_options = unserialize($devyai_options);
        } else {
            $devyai_options = new stdClass();
        }

        if (isset($_POST['action_settings'])
            && filter_input(INPUT_POST, 'action_settings', FILTER_SANITIZE_STRING) === "changeSettings") {
            $devyai_options->moodle_url = filter_input(INPUT_POST, 'moodle_url', FILTER_SANITIZE_URL);
            $devyai_options->moodle_token = filter_input(INPUT_POST, 'moodle_token', FILTER_SANITIZE_STRING);
            $devyai_options->form_register = filter_input(INPUT_POST, 'form_register', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->form_justregister = filter_input(INPUT_POST, 'form_justregister', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->form_request = filter_input(INPUT_POST, 'form_request', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->moodle_paths = filter_input(INPUT_POST, 'moodle_paths', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
            $devyai_options->root_category = filter_input(INPUT_POST, 'root_category', FILTER_SANITIZE_NUMBER_INT);

            $devyai_options->template_base = filter_input(INPUT_POST, 'template_base', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->template_overview = filter_input(INPUT_POST, 'template_overview', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->template_courses = filter_input(INPUT_POST, 'template_courses', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->template_admissions = filter_input(INPUT_POST, 'template_admissions', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->template_cost = filter_input(INPUT_POST, 'template_cost', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->template_difference = filter_input(INPUT_POST, 'template_difference', FILTER_SANITIZE_NUMBER_INT);
            $devyai_options->template_class = filter_input(INPUT_POST, 'template_class', FILTER_SANITIZE_NUMBER_INT);

            $devyai_options->notification_email = filter_input(INPUT_POST, 'notification_email', FILTER_SANITIZE_EMAIL);

            update_option('devyai_options', serialize($devyai_options));
            add_settings_error('devyai_messages', 'devyai_message', __('Saved', 'devyai'), 'updated');
        }

        if (!isset($devyai_options->moodle_paths))
            $devyai_options->moodle_paths = array();

        include __DIR__ . '/views/settings.html.php';
    }
}
