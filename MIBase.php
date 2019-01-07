<?php

class MIBase
{
	const STATUS_WAITING = 0;
	const STATUS_APPROVED = 1;
	const STATUS_DENIED = 2;
	const STATUS_NO_PROCEED = 3;

	const TABLE_NAME = "devyai_submissions";
	const TABLE_NAME_USER = "devyai_users_pdf";

    const PLUGIN = 'devyai-moodle-integration';
    const TEXT_DOMAIN = 'devyai-moodle-integration';
    public $plugin_url;
    private $devyai_options;

    public function __construct()
    {
        $this->plugin_url = self::pluginUrl();

        $stdClass = new stdClass();
        $this->devyai_options = get_option('devyai_options', $stdClass);
        if (is_string($this->devyai_options)) {
            $this->devyai_options = unserialize($this->devyai_options);
        } else {
            $this->devyai_options = new stdClass();
        }
    }

    public static function pluginUrl()
    {
        return trailingslashit(plugins_url(self::PLUGIN, self::PLUGIN));
    }

    /**
     * @return stdClass
     */
    public function getDevyaiOptions()
    {
        return $this->devyai_options;
    }
}