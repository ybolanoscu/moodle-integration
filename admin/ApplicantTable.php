<?php

require_once __DIR__ . '/Moodle.php';

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ApplicantTable extends WP_List_Table
{
    function __construct()
    {
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'applicant',     //singular name of the listed records
            'plural' => 'applicants',    //plural name of the listed records￼
            'ajax' => false        //does this table support ajax?
        ));
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'status':
                $colors = array("warning", "success", "danger", "dark");
                $status = array("Waiting", "Approved", "Denied", "No proceed");
                $keys = array("waiting", "approve", "deny", "noproceed");
                $color = $colors[$item['status']];
                $state = $status[$item['status']];
                $options = "";
                foreach ($status as $key => $statu) {
                    $options .= "<a class='dropdown-item action-status' href='#' data-id='{$item['id']}' data-state='{$keys[$key]}' data-toggle='modal' data-target='#changeStatus'>$statu</a>";
                }

                $html = "<div class='btn-group btn-group-sm dropdown'>" .
                    "<span class='btn btn-$color'>$state</span>" .
                    "<button type='button' class='btn btn-$color dropdown-toggle pl-1' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>" .
                    "<span class='sr-only'>Toggle Dropdown</span>" .
                    "</button>" .
                    "<div class='dropdown-menu'>" .
                    $options .
                    "</div>" .
                    "</div>";

                return $html;
//	            return "<span class='rounded p-2 bg-$color text-white '>$status <i class='fa fa-$icon border-left border-white pl-1'></i></span><span class='h5 '></span>";
            default:
                return @$item[$column_name];
        }
    }

    function column_email($item)
    {
        //Build row actions
        $actions = array(
            // 'edit' => sprintf('<a href="?page=%s&action=%s&submission=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
            'delete' => sprintf('<a href="?page=%s&action=%s&submission=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
        );
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(ID:%2$s)</span>%3$s',
            /*$1%s*/
            $item['email'],
            /*$2%s*/
            $item['id'],
            /*$3%s*/
            $this->row_actions($actions)
        );
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'email' => 'Email',
            'namepath' => 'Name',
            'time' => 'Creación',
            'description' => 'Data',
            'lang' => 'Language',
            'courses' => 'Courses',
            'status' => 'Status'
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
//            'id' => array('id', false),     //true means it's already sorted
//            'email' => array('email', false),
//            'director' => array('director', false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'waiting' => 'Waiting',
            'noproceed' => 'No proceed',
            'delete' => 'Delete',
        );
        return $actions;
    }

    /**
     * @throws Exception
     */
    function process_bulk_action()
    {
        if ($this->current_action()) {
            global $wpdb;
            $tablename = $wpdb->prefix . MIBase::TABLE_NAME;

            $applicants = filter_input(INPUT_POST, $this->_args['singular'], FILTER_SANITIZE_NUMBER_INT, array('flags' => FILTER_REQUIRE_ARRAY));
            if (!$applicants)
                $applicants = array(filter_input(INPUT_GET, 'submission', FILTER_SANITIZE_NUMBER_INT));
            if (empty($applicants))
                return;

            $state = 0;
            $name = '';
            $description = '';
            $update_entry = true;
            switch ($this->current_action()) {
                case 'delete':
                    $wpdb->query("DELETE FROM " . $wpdb->prefix . MIBase::TABLE_NAME . " WHERE id in (" . implode(',', $applicants) . ")");
                    $update_entry = false;
                    break;
                case 'waiting':
                    $state = MIBase::STATUS_WAITING;
                    break;
                case 'approve':
                    $update_entry = false;

                    $category_name = filter_input(INPUT_POST, 'category-name', FILTER_SANITIZE_STRING);
                    $category_description = filter_input(INPUT_POST, 'category-description', FILTER_SANITIZE_STRING);
                    $data = $wpdb->get_row("SELECT firstname, lastname, email, id_courses, status FROM $tablename WHERE id = " . $applicants[0] . ";", ARRAY_A);
                    $data['id_courses'] = explode('|', $data['id_courses']);

                    if (empty($data['id_courses'])) {
                        add_settings_error('devyai_messages', 'devyai_message', __('Unable to process empty request.', 'devyai'), 'error');
                        break;
                    }

                    $querystr = "SELECT $wpdb->posts.* FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID in (" . join(',', $data['id_courses']) . ") AND $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' ORDER BY $wpdb->posts.post_date DESC";
                    $courses = $wpdb->get_results($querystr, OBJECT);
                    $course_names = '';
                    $courses_id = array();
                    foreach ($courses as $course) {
                        $course_names .= '<br />' . $course->post_title;
                        $courses_id[] = $course->ID;
                    }

                    $categories = Moodle::instance()->getCategories(['criteria' => [['key' => 'name', 'value' => $category_name]]]);
                    if (count($categories) > 1) {
                        add_settings_error('devyai_messages', 'devyai_message', __("Already exists a category with the name: $category_name.", 'devyai'), 'error');
                        $update_entry = false;
                        break;
                    }

                    $clean = true;
                    $term = wp_insert_term(
                        $category_name,
                        'product_cat',
                        array(
                            'description' => $category_description,
                            'slug' => sanitize_title($category_name)
                        )
                    );

                    if (is_wp_error($term)) {
                        add_settings_error('devyai_messages', 'devyai_message', __("Already exists a category with the name: $category_name.", 'devyai'), 'error');
                        $clean = false;
                        break;
                    }

                    if ($clean) {
                        foreach ($data['id_courses'] as $id_course) {
                            $term_ids = [$term['term_id']];
                            $terms = wp_get_object_terms($id_course, 'product_cat');
                            if (count($terms) > 0) {
                                foreach ($terms as $item) {
                                    $term_ids[] = $item->term_id;
                                }
                            }
                            wp_set_object_terms($id_course, $term_ids, 'product_cat');
                        }
                        $name = $category_name;
                        $description = $category_description;
                    }

                    $message = '<p>The path you requested is now approved and live to be purchased under the name "' . $category_name . '".</p><p>' . $course_names . '</p>';
                    wp_mail($data['email'], 'Path Pending Approval', $message);

                    $state = MIBase::STATUS_APPROVED;
                    $update_entry = true;
                    break;
                case 'deny':
                    $update_entry = false;
                    $data = $wpdb->get_row("SELECT firstname, lastname, email, id_courses, status FROM $tablename WHERE id = " . $applicants[0] . ";", ARRAY_A);
                    $data['id_courses'] = explode('|', $data['id_courses']);

                    if ($data['status'] != MIBase::STATUS_APPROVED) {
                        add_settings_error('devyai_messages', 'devyai_message', __('Unable to deny a unapproved applicant.', 'devyai'), 'error');
                        break;
                    }

                    $querystr = "SELECT $wpdb->posts.* FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID in (" . join(',', $data['id_courses']) . ") AND $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_course_id' AND $wpdb->posts.post_status = 'publish' ORDER BY $wpdb->posts.post_date DESC";
                    $courses = $wpdb->get_results($querystr, OBJECT);
                    $courses_id = array();
                    foreach ($courses as $course)
                        $courses_id[] = get_post_meta($course->ID, "_course_id", true);

                    if ($user_id = Moodle::instance()->userExists($data['email']))
                        $update_entry = Moodle::instance()->unenrolUser($user_id, $courses_id);

                    $state = MIBase::STATUS_DENIED;
                    break;
                case 'noproceed':
                    $state = MIBase::STATUS_NO_PROCEED;
                    break;
            }

            if ($update_entry) {
                foreach ($applicants as $applicant) {
                    $data = array('status' => $state);
                    $data_filter = array('%d');
                    $where = array('id' => $applicant);
                    if ($name !== '' && $this->current_action() == 'approve') {
                        $data['namepath'] = $name;
                        $data['descriptionpath'] = $description;
                        $data_filter[] = '%s';
                        $data_filter[] = '%s';
                    }
                    $wpdb->update($tablename, $data, $where, $data_filter, '%d');
                }

                add_settings_error('devyai_messages', 'devyai_message', __('All done.', 'devyai'), 'updated');
            }
        }
    }

    /**
     * @throws Exception
     */
    function prepare_items()
    {
        global $wpdb;
        $per_page = $this->get_items_per_page("devyai_courses");
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
//        $data = $this->example_data;
//        function usort_reorder($a, $b)
//        {
//            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
//            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
//            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
//            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
//        }
//
//        usort($data, 'usort_reorder');

        $tablename = $wpdb->prefix . MIBase::TABLE_NAME;

        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var("SELECT count(*) FROM $tablename;");

        $offset = ($current_page - 1) * $per_page;
        $data = $wpdb->get_results("SELECT * FROM $tablename LIMIT $per_page OFFSET $offset;", ARRAY_A);
        $this->items = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }


}