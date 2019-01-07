<?php
/**
 * Created by DevYai.
 */

class MenuBuilder
{
    static $instance;
    private $visited = array();

    /**
     * CourseTerm constructor.
     */
    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance))
            self::$instance = new MenuBuilder();
        return self::$instance;
    }

    public function exists($parent)
    {
        if (empty($this->visited[$parent]))
            $this->visited[$parent] = false;
        return $this->visited[$parent];
    }

    public function visit($parent)
    {
        $this->visited[$parent] = true;
    }
}
