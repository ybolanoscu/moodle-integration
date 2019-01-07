<?php
/**
 * Created by DevYai
 */

require_once __DIR__ . '/MIFront.php';
require_once __DIR__ . '/CourseTerm.php';

if (!is_admin() && !function_exists('add_settings_error')) {
    function add_settings_error($handle, $namespace, $message, $type)
    {
        echo '<div class="alert alert-danger alert-dismissable text-left">' .
            '      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' .
            '      ' . $message .
            '</div>';
    }
}

add_action('rest_api_init', function () {
    register_rest_route('devyai-api/v1', '/courses/sync', array(
        'methods' => 'GET',
        'callback' => 'devyai_api_courses_sync',
    ));
    register_rest_route('devyai-api/v1', '/categories/sync', array(
        'methods' => 'GET',
        'callback' => 'devyai_api_categories_sync',
    ));
});

function devyai_api_courses_sync(WP_REST_Request $request)
{
    require_once __DIR__ . "/../../moowoodle/classes/class-dc-woodle-sync.php";
    require_once __DIR__ . "/../includes/MoWoodleIntegration.php";

    MoWoodleIntegration::instance()->init();

    $woodle = new DC_Woodle_Sync();
    $woodle->sync(true);

    return new WP_REST_Response(['success' => 1], 200);
}

function devyai_api_categories_sync(WP_REST_Request $request)
{
    require_once __DIR__ . "/../../moowoodle/classes/class-dc-woodle-sync.php";
    require_once __DIR__ . "/../includes/MoWoodleIntegration.php";
    require_once __DIR__ . "/../admin/Moodle.php";

    global $wpdb;
    $querystr = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'product' ORDER BY $wpdb->posts.post_date DESC";
    $courses = $wpdb->get_results($querystr, OBJECT);
    foreach ($courses as $course) {
        $categories = get_the_terms($course->ID, 'product_cat');
        $course->course_id = get_post_meta($course->ID, '_course_id', true);
        if (empty($course->course_id))
            continue;

        $mcourse = Moodle::instance()->getCourseInfo($course->course_id);
        if (empty($mcourse))
            continue;
        if (empty($mcourse->overviewfiles))
            continue;

//        $meta = get_post_meta($course->ID, 'thumbnail_id');
//        if (!empty($meta))
//            continue;

        foreach ($mcourse->overviewfiles as $file) {
            if (in_array($file->mimetype, array('image/jpeg', 'image/png'))) {
                $extension = str_replace('image/', '', $file->mimetype);
                $file->filename = 'course-' . $course->course_id . '.' . $extension;

                $filename = __DIR__ . '/../../../uploads/' . date('Y') . '/' . date('m') . '/' . $file->filename;
                file_put_contents($filename, file_get_contents($file->fileurl . '?token=' . Moodle::instance()->getDevyaiOptions()->moodle_token));

                $wordpress_upload_dir = wp_upload_dir();
                $new_file_path = $wordpress_upload_dir['path'] . '/' . $file->filename;
                $new_file_mime = mime_content_type($filename);

                $upload_id = wp_insert_attachment(array(
                    'guid' => $new_file_path,
                    'post_mime_type' => $new_file_mime,
                    'post_title' => preg_replace('/\.[^.]+$/', '', $file->name),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ), $new_file_path);

                set_post_thumbnail($course->ID, $upload_id);
                update_post_meta($course->ID, 'thumbnail_id', $upload_id);
                foreach ($categories as $category) {
                    $meta = get_term_meta($category->term_id, 'thumbnail_id');
                    if (!empty($meta))
                        update_term_meta($category->term_id, 'thumbnail_id', $upload_id);
                }
            }
        }
    }

    return new WP_REST_Response(['success' => 1], 200);
}

$frontend = new MIFront();
$frontend->init();
