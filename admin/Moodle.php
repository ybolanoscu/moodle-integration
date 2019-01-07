<?php

require_once __DIR__ . '/../MIBase.php';
require_once(__DIR__ . '/../includes/curl/curl.php');

class Moodle extends MIBase
{
    private static $_instance;
    private $options;

    const MOODLE_USER_CREATE_AUTH = 'auth_email_signup_user';
    const MOODLE_GET_CATEGORIES = 'core_course_get_categories';
    const MOODLE_GET_COURSES = 'core_course_get_courses';
    const MOODLE_GET_COURSES_BY_FIELD = 'core_course_get_courses_by_field';
    const MOODLE_GET_COURSES_INFO = 'core_course_get_contents';
    const MOODLE_GET_COURSE_GROUPS = 'core_group_get_course_groups';
    const MOODLE_GET_COURSE_UPDATE = 'core_course_update_courses';
    const MOODLE_USER_GET = 'core_user_get_users';
    const MOODLE_USER_CREATE = 'core_user_create_users';
    const MOODLE_USER_ENROL = 'enrol_manual_enrol_users';
    const MOODLE_USER_UNENROL = 'enrol_manual_unenrol_users';
    const MOODLE_USER_ADD_TO_GROUP = 'core_user_get_users';
    const MOODLE_USER_ENROLLED_COURSES = 'core_enrol_get_users_courses';
    const MOODLE_USER_COURSE_EVALUATIONS = 'core_completion_get_course_completion_status';
    const MOODLE_GROUP_CREATE = 'core_group_create_groups';
    const MOODLE_GROUP_ADD_USER = 'core_group_add_group_members';
    const MOODLE_GET_GROUP_BY_USER_COURSE = 'core_group_get_course_user_groups';
    const MOODLE_GET_GROUPS_MEMBERS = 'core_group_get_group_members';
    const MOODLE_GROUP_REMOVE_USER = 'core_group_delete_group_members';
    const MOODLE_GET_COURSES_BY_USER = 'core_group_delete_group_members';
    const MOODLE_GET_ACTIVITIES_COMPLETION_COURSE_BY_USER = 'core_completion_get_activities_completion_status';

    const MOODLE_USERS_ENROLLED = 'core_enrol_get_enrolled_users';
    const MOODLE_GRADES_COURSE = 'gradereport_user_get_grade_items';

    /** @return Moodle */
    public static function instance()
    {
        if (!isset(self::$_instance))
            self::$_instance = new Moodle();
        return self::$_instance;
    }

    public function __construct()
    {
        $this->options = unserialize(get_option('devyai_options', ''));
        parent::__construct();
    }

    protected function getUrl($function_name)
    {
        return $this->options->moodle_url . "/webservice/rest/server.php?wstoken=" . $this->options->moodle_token . "&wsfunction=" . $function_name . "&moodlewsrestformat=json";
    }

    protected function notCommunicated()
    {
        add_settings_error('devyai_messages', 'devyai_message', __('Unable to communicate to Moodle using current configuration.', 'devyai'), 'error');
    }

    public function getCourseInfo($course_id)
    {
        $params = ['field' => 'id', 'value' => $course_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GET_COURSES_BY_FIELD), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                return false;
            }
            if (empty($response->courses))
                return false;
            return $response->courses[0];
        }
        return false;
    }

    public function createUser($username, $name, $last_name, $email, $password)
    {
        $params = [
            'username' => $username,
            'firstname' => $name,
            'lastname' => $last_name,
            'email' => $email,
            'password' => 'a' . $password . 'R' . $password . '*-'
        ];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_USER_CREATE_AUTH), $params);
        if (!empty($resp->body)) {
            $response = json_decode(@$resp->body);
            if (isset($response->message)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to get data from moodle.', 'devyai') . '<br>' . $response->message, 'error');
                return false;
            }
            return $response->id;
        }
        $this->notCommunicated();
        return -1;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getUsers()
    {
        $params = ['criteria' => [['key' => 'confirmed', 'value' => 1]]];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_USER_GET), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to load students from moodle.', 'devyai'), 'error');
                return array();
            }

            return $response->users;
        }
        $this->notCommunicated();
        return array();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getCategories($params)
    {
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GET_CATEGORIES), $params);
        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to load categories from moodle.', 'devyai'), 'error');
                return array();
            }

            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    /**
     * @param $user_id
     * @param array $courses_id
     * @return array|mixed|object
     */
    public function enrolUser($user_id, $courses_id = array())
    {
        $params = ['enrolments' => []];
        foreach ($courses_id as $item) {
            $params['enrolments'][] = [
                'roleid' => 5,
                'userid' => $user_id,
                'courseid' => $item,
            ];
        }
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_USER_ENROL), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to enrol user to moodle courses.', 'devyai') . '<br>' . $response->message, 'error');
                return array();
            }
            return true;
        }
        $this->notCommunicated();
        return array();
    }

    /**
     * @param $user_id
     * @param array $courses_id
     * @return array|mixed|object
     */
    public function unenrolUser($user_id, $courses_id = array())
    {
        $params = ['enrolments' => []];
        foreach ($courses_id as $item) {
            $params['enrolments'][] = [
                'roleid' => 5,
                'userid' => $user_id,
                'courseid' => $item,
            ];
        }
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_USER_UNENROL), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to unenrol uset to moodle courses.', 'devyai'), 'error');
                return array();
            }
            return true;
        }
        $this->notCommunicated();
        return array();
    }

    public function getUserCourses($user_id)
    {
        $params = ['userid' => $user_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_USER_ENROLLED_COURSES), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to unenrol uset to moodle courses.', 'devyai'), 'error');
                return array();
            }
            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    public function getUserCourseEvaluations($user_id, $course_id)
    {
        $params = ['userid' => $user_id, 'courseid' => $course_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_USER_COURSE_EVALUATIONS), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                if (in_array($response->errorcode, array('nocriteriaset', 'cannotviewreport'))) {
                    add_settings_error('devyai_messages', 'devyai_message', __('Unable to load evaluations from moodle courses.', 'devyai') . '<br>' . $response->message, 'error');
                    return array();
                }
                var_dump($response);
                return array();
            }

            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    public function getUserCourseActivitiesCompletion($user_id, $course_id)
    {
        $params = ['userid' => $user_id, 'courseid' => $course_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GET_ACTIVITIES_COMPLETION_COURSE_BY_USER), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                if (in_array($response->errorcode, array('nocriteriaset', 'cannotviewreport'))) {
                    add_settings_error('devyai_messages', 'devyai_message', __('Unable to load activities completion from moodle courses.', 'devyai') . '<br>' . $response->message, 'error');
                    return array();
                }
                var_dump($response);
                return array();
            }

            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    public function getCourseGroups($course_id)
    {
        $params = ['courseid' => $course_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GET_COURSE_GROUPS), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to load groups from moodle courses.', 'devyai'), 'error');
                return array();
            }

            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    public function getCreateCourseGroup($course_id, $name = 'A', $description = 'Group A')
    {
        $params = ['groups' => [['courseid' => $course_id, 'name' => $name, 'description' => $description]]];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GROUP_CREATE), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to create groups on moodle courses.', 'devyai'), 'error');
                return array();
            }
            if (empty($response))
                return array();
            return $response[0];
        }
        $this->notCommunicated();
        return array();
    }

    public function addUserToGroup($group_id, $user_id)
    {
        $params = ['members' => [['groupid' => $group_id, 'userid' => $user_id]]];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GROUP_ADD_USER), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to create groups on moodle courses.', 'devyai'), 'error');
                return false;
            }
            return true;
        }
        $this->notCommunicated();
        return false;
    }

    public function getCourseGroupUser($course_id, $user_id)
    {
        $params = ['courseid' => $course_id, 'userid' => $user_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GET_GROUP_BY_USER_COURSE), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to create groups on moodle courses.', 'devyai'), 'error');
                return array();
            }

            if (empty($response->groups))
                return array();
            return $response->groups[0];
        }
        $this->notCommunicated();
        return array();
    }

    public function getGroupsUsers($groups_id = array())
    {
        $params = ['groupids' => $groups_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GET_GROUPS_MEMBERS), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to create groups on moodle courses.', 'devyai'), 'error');
                return array();
            }
            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    public function checkUserCourseGroup($user_id, $course_id)
    {
        if ($group = $this->getCourseGroupUser($course_id, $user_id)) {
            return $group->id;
        } else {
            if (empty($groups = $this->getCourseGroups($course_id))) {
                if (!empty($group = $this->getCreateCourseGroup($course_id, 'GROUP-' . time(), 'Automatic Group ' . time()))) {
                    if ($this->addUserToGroup($group->id, $user_id))
                        return true;
                }
            } else {
                $groups_id = array();
                foreach ($groups as $group)
                    $groups_id[] = $group->id;
                if (!empty($user_groups = $this->getGroupsUsers($groups_id))) {
                    $group_id = false;
                    foreach ($user_groups as $user_group) {
                        if (count($user_group->userids) < 15) {
                            $group_id = $user_group->groupid;
                            break;
                        }
                    }

                    if (!$group_id) {
                        if (!empty($group = $this->getCreateCourseGroup($course_id, 'GROUP-' . time(), 'Automatic Group ' . time())))
                            $group_id = $group->id;
                        else
                            return false;
                    }

                    if ($this->addUserToGroup($group_id, $user_id))
                        return true;
                }
            }
        }
        return false;
    }

    public function getCourseUsersEnrolled($course_id)
    {
        $params = ['courseid' => $course_id];
        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_USERS_ENROLLED), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode)) {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to get moodle courses.', 'devyai'), 'error');
                return array();
            }
            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    public function getGradesCourseUser($course_id, $user_id = 0)
    {
        $params = ['courseid' => $course_id, 'userid' => $user_id];

        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GRADES_COURSE), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            if (isset($response->errorcode) && $response->errorcode != 'usernotconfirmed') {
                add_settings_error('devyai_messages', 'devyai_message', __('Unable to get grades of moodle courses.', 'devyai'), 'error');
                return array();
            }
            return $response;
        }
        $this->notCommunicated();
        return array();
    }

    /**
     * @param $course_id
     * @param DateTime $start
     * @param DateTime $end
     * @return bool
     */
    public function udpateCourse($course_id, $start, $end)
    {
        $params = ['courses' => [['id' => $course_id, 'startdate' => $start ? $start->format('U') : 0, 'enddate' => $end ? $end->format('U') : 0]]];

        $curl = new Curl();
        $resp = $curl->post($this->getUrl(self::MOODLE_GET_COURSE_UPDATE), $params);

        if (!empty($resp->body)) {
            $response = json_decode($resp->body);
            return empty($response->warnings);
        }
        return false;
    }
}

