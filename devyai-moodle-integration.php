<?php
/*
Plugin Name: DevYai Moodle Integration
Plugin URI:  http://www.adaved.com/moodle-integration
Description: Generates a periodic table of courses by category based on Moowodle integration
Version:     1.0
Author:      Yaineri Bolanos
Author URI:  http://www.adaved.com/team
*/
require_once __DIR__ . "/common.php";

function activation_add_custom_roles()
{
    add_role('secretary_last', 'Secretary', array(
        'read' => true,
        'submissions' => true,
        'course_student' => false,
        'level_0' => true,
        'subscriber' => true,
        'course_manager' => false,
    ));
    add_role('student_last', 'Student', array(
        'read' => true,
        'submissions' => false,
        'course_student' => true,
        'level_0' => true,
        'subscriber' => true,
        'course_manager' => false,
    ));
    add_role('academic', 'Academic', array(
        'read' => true,
        'course_academic' => true,
        'paths' => true,
        'level_0' => true,
        'subscriber' => true,
        'course_manager' => false,
    ));
    add_role('register', 'Register', array(
        'read' => true,
        'list_student' => true,
        'level_0' => true,
        'subscriber' => true,
        'course_manager' => false,
    ));
    add_role('accounting', 'Accounting', array(
        'read' => true,
        'course_academic' => true,
        'level_0' => true,
        'subscriber' => true,
        'course_manager' => false,
    ));
    add_role('course_manager', 'Course Manager', array(
        'read' => false,
        'submissions' => false,
        'course_student' => true,
        'level_0' => true,
        'subscriber' => false,
        'course_manager' => true,
    ));
}

register_activation_hook(__FILE__, 'activation_add_custom_roles');

register_deactivation_hook(__FILE__, 'dectivation_add_custom_roles');
function dectivation_add_custom_roles()
{
    remove_role('student');
    remove_role('student_last');
    remove_role('academic');
    remove_role('register');
    remove_role('secretary');
    remove_role('secretary_last');
    remove_role('accounting');
    remove_role('publisher');
    remove_role('course_manager');
}

add_action('plugins_loaded', 'devyai_load_languages');
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function devyai_load_languages()
{
    load_plugin_textdomain('devyai', false, basename(dirname(__FILE__)) . '/languages/');
}

if (is_admin())
    require_once __DIR__ . "/admin/admin.php";
else
    require_once __DIR__ . "/frontend/frontend.php";