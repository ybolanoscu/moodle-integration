<?php

require_once __DIR__ . '/frontend/MIFront.php';
require_once __DIR__ . '/frontend/CourseTerm.php';
require_once __DIR__ . '/shortcodes.php';
require_once __DIR__ . '/ajaxs.php';

add_action('wpcf7_mail_sent', 'wpcf7_after_save_callback');
add_action('wpcf7_mail_failed', 'wpcf7_after_save_callback');
function wpcf7_after_save_callback(WPCF7_ContactForm $element)
{
    $options = get_option('devyai_options', new stdClass());
    if (is_string($options)) {
        $options = unserialize($options);
    }
    $form_id = @$options->form_request;

    if ($element->id() == $form_id) {
        global $wpdb;
        $data = array();
        $submission = WPCF7_Submission::get_instance();
        $posted_data = $submission->get_posted_data();
        $parse = "";

        $now = new \DateTime();
        $data['time'] = $now->format('Y-m-d H:i:s');
        $data['wpcf7_id'] = $element->id();
        $data['email'] = isset($posted_data['Email']) ? $posted_data['Email'] : null;
        $data['firstname'] = isset($posted_data['FirstName']) ? $posted_data['FirstName'] : '';
        $data['lastname'] = isset($posted_data['LastName']) ? $posted_data['LastName'] : '';
        $data['status'] = MIBase::STATUS_WAITING;

        $data['courses'] = trim(@$posted_data['PathName'], "|");
        $data['id_courses'] = trim(@$posted_data['IdsName'], "|");

        $data['paths'] = $posted_data['Path'];
        $data['lang'] = $posted_data['current_laguage'];

        foreach ($posted_data as $key => $posted) {
            if ($key[0] != "_" && $key != 'g-recaptcha-response') {
                $parse .= "<b>" . mb_convert_case($key, MB_CASE_TITLE) . "</b>:" . $posted . "<br/>";
            }
        }
        $data['description'] = $parse;

        $datetable = $wpdb->prefix . MIBase::TABLE_NAME;
        $results = $wpdb->get_results($wpdb->prepare("SELECT id_courses FROM $datetable WHERE email = \"%s\"", array($data['email'])));

        $toSave = explode('|', $data['courses']);
        sort($toSave, SORT_STRING);
        $data['courses'] = implode('|', $toSave);

        $toSave = explode('|', $data['id_courses']);
        sort($toSave, SORT_STRING);
        $data['id_courses'] = implode('|', $toSave);
        $do = true;
        foreach ($results as $result) {
            if ($result->id_courses == $toSave) {
                $do = false;
                break;
            }
        }
        if ($do) {
            $wpdb->insert($datetable, $data);
        }
    }
}

add_action("ff-pdf__fields-value", 'read_fields_admission', 10, 2);
function read_fields_admission($values, $form_id)
{
    $options = get_option('devyai_options', new stdClass());
    if (is_string($options)) {
        $options = unserialize($options);
    }
    $form_register = @$options->form_register;
    if ($form_id == $form_register) {
        $email_address = isset($values['[:es]Correo[:en]Email[:]']) ? $values['[:es]Correo[:en]Email[:]'] : @$values['email'];
        $user = get_user_by('email', $email_address);
        $exists = true;
        if (null == $user) {
            $exists = false;
            $password = wp_generate_password(12, false);

            $login = explode('@', $email_address);
            $login = $login[0];

            $now = new \DateTime();
            global $wpdb;
            $wpdb->insert($wpdb->users, [
                'user_login' => $login,
                'user_pass' => $password,
                'user_nicename' => $values['[:es]Nombre[:en]Name[:]'],
                'display_name' => $values['[:es]Nombre[:en]Name[:]'],
                'user_email' => $email_address,
                'user_registered' => $now->format('Y-m-d H:i:s'),
            ]);

            $user = get_user_by('email', $email_address);
            $user->set_role('student_last');

            $firstname = @$values['[:es]Nombre[:en]Name[:]'];
            $email = $user->user_email;
            $adt_rp_key = get_password_reset_key($user);
            $user_login = $user->user_login;
            $rp_link = network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login));

            if ($firstname == "") {
                $firstname = "Buddy";
            }
            ob_start();
            $email_content = array(
                'action_label' => 'Set Password',
                'action_link' => $rp_link,
                'firstname' => $firstname,
                'first_p' => _q('[:es]Estimado(a) usuario, se le ha ha creado una cuenta en ' . get_bloginfo('name') . '.<br>Presione en este link para obtener sus credenciales...<br>
                            [:en]Dear user, an account has been created on ' . get_bloginfo('name') . '.<br>Please press this link for getting your credentials<br>[:]')
//                'first_p' => "An account has been created on " . get_bloginfo('name') . "<br>Click here to set the password for your account: <br>"
            );
            include_once __DIR__ . '/includes/template_mail.php';
            $message = ob_get_clean();

            $subject = __("Your account on " . get_bloginfo('name'));
            $headers = array();

            $admin_email = get_site_option('admin_email');
            if ($admin_email == '') {
                $admin_email = 'no-reply@' . $_SERVER['SERVER_NAME'];
            }
            $headers[] = "From: Atlantis University <{$admin_email}>" . "\r\n";
            $headers[] = "Reply-To: Atlantis University <{$admin_email}>" . "\r\n";
            wp_mail($email, $subject, $message, $headers);

            $GLOBALS['form_justregister'] = $options->form_justregister;
        }

        if (WC()->cart->get_cart_contents_count() > 0) {
            update_user_meta($user->ID, '_devyai_redirect', true);
        }

        if ($exists) {
            $_SESSION['messages'] = [_q('[:es]Ya tienas una cuenta en nuestro sistema creada con anterioridad, acceda con su email.[:en]You already had an account on our system, please access with your email address.[:]')];
        }
    }
}

add_action("ff-pdf__pdf-link", 'pdf_save_submission', 10, 4);
function pdf_save_submission($values, $pdf_path, $form_id, $form_title)
{
    $options = get_option('devyai_options', new stdClass());
    if (is_string($options)) {
        $options = unserialize($options);
    }
    $form_register = @$options->form_register;

    if ($form_id == $form_register) {
        global $wpdb;
        $data = array();
        $now = new \DateTime();
        $data['time'] = $now->format('Y-m-d H:i:s');
        $data['email'] = isset($values['[:es]Correo[:en]Email[:]']) ? $values['[:es]Correo[:en]Email[:]'] : null;
        $data['title'] = $form_title;
        $data['pdf_link'] = $pdf_path;

        $datetable = $wpdb->prefix . MIBase::TABLE_NAME_USER;
        $wpdb->insert($datetable, $data);
    }
}

function actions_plugin_action_links($links)
{
    $page = plugin_basename(__DIR__) . "-setting";
    $links = array_merge(array(
        '<a href="' . esc_url(admin_url("/admin.php?page=$page")) . '">' . __('Settings', 'textdomain') . '</a>'
    ), $links);
    return $links;
}

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'actions_plugin_action_links');

add_filter('pre_option_default_role', function ($default_role) {
    return 'student_last';
});

function devyai_set_content_type()
{
    return "text/html";
}

add_filter('wp_mail_content_type', 'devyai_set_content_type');
