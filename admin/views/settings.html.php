<?php
settings_errors('devyai_messages');

$cf7Forms = get_posts(array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1));
$post_ids = wp_list_pluck($cf7Forms, 'ID');
$form_titles = wp_list_pluck($cf7Forms, 'post_title');

$product_categories = get_terms(array('taxonomy' => "product_cat", 'number' => 1000));
$pages = get_pages(array('post_status' => 'publish,private'));

global $wpdb;
$forms_maker = $wpdb->get_results("SELECT id, title FROM " . $wpdb->prefix . "formmaker", ARRAY_A);
?>
<style>
    fieldset {
        border: 1px solid #ccc;
        padding: 1em;
        border-radius: 0.3em;
    }

    fieldset legend {
        width: auto;
        padding: 0 0.5em;
    }
</style>
<form method="POST" class="mt-5">
    <div class="container mt-3">
        <fieldset class="form-group">
            <legend>Options Templates</legend>
            <div class="row">
                <div class="col-12">
                    <label for="template_base">Base Template: </label>
                    <select class="input-sm form-control" id="template_base" name="template_base">
                        <?php foreach ($pages as $page) : ?>
                            <option <?php echo (@$devyai_options->template_base == $page->ID) ? 'selected' : ''; ?>
                                    value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?> <?php echo $page->post_status == "private" ? '<b>(Private)</b>' : ''; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="template_overview">Overview: </label>
                    <select class="input-sm form-control" id="template_overview" name="template_overview">
                        <?php foreach ($pages as $page) : ?>
                            <option <?php echo (@$devyai_options->template_overview == $page->ID) ? 'selected' : ''; ?>
                                    value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?> <?php echo $page->post_status == "private" ? '<b>(Private)</b>' : ''; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="template_courses">Course & Competences: </label>
                    <select class="input-sm form-control" id="template_courses" name="template_courses">
                        <?php foreach ($pages as $page) : ?>
                            <option <?php echo (@$devyai_options->template_courses == $page->ID) ? 'selected' : ''; ?>
                                    value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?> <?php echo $page->post_status == "private" ? '<b>(Private)</b>' : ''; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="template_admissions">Admission: </label>
                    <select class="input-sm form-control" id="template_admissions" name="template_admissions">
                        <?php foreach ($pages as $page) : ?>
                            <option <?php echo (@$devyai_options->template_admissions == $page->ID) ? 'selected' : ''; ?>
                                    value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?> <?php echo $page->post_status == "private" ? '<b>(Private)</b>' : ''; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="template_cost">Cost & Time: </label>
                    <select class="input-sm form-control" id="template_cost" name="template_cost">
                        <?php foreach ($pages as $page) : ?>
                            <option <?php echo (@$devyai_options->template_cost == $page->ID) ? 'selected' : ''; ?>
                                    value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?> <?php echo $page->post_status == "private" ? '<b>(Private)</b>' : ''; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="template_difference">AU Difference: </label>
                    <select class="input-sm form-control" id="template_difference" name="template_difference">
                        <?php foreach ($pages as $page) : ?>
                            <option <?php echo (@$devyai_options->template_difference == $page->ID) ? 'selected' : ''; ?>
                                    value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?> <?php echo $page->post_status == "private" ? '<b>(Private)</b>' : ''; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="template_class">Class: </label>
                    <select class="input-sm form-control" id="template_class" name="template_class">
                        <?php foreach ($pages as $page) : ?>
                            <option <?php echo (@$devyai_options->template_class == $page->ID) ? 'selected' : ''; ?>
                                    value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?> <?php echo $page->post_status == "private" ? '<b>(Private)</b>' : ''; ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-group">
            <legend>Options Moodle</legend>
            <div class="row">
                <div class="col">
                    <label for="moodle_url">Moodle URL</label>
                    <input type="text" class="form-control form-control-sm" id="moodle_url" name="moodle_url"
                           value="<?php echo @$devyai_options->moodle_url; ?>">
                </div>
                <div class="col">
                    <label for="moodle_token">Moodle API Token</label>
                    <input type="text" class="form-control form-control-sm" id="moodle_token" name="moodle_token"
                           value="<?php echo @$devyai_options->moodle_token; ?>">
                </div>
                <div class="col">
                    <label for="root_category">Root Category</label>
                    <select class="form-control" name="root_category" id="root_category">
                        <?php foreach ($product_categories as $product_category) { ?>
                            <option value="<?= $product_category->term_id ?>" <?php echo $product_category->term_id == @$devyai_options->root_category ? "selected" : ''; ?>> <?= $product_category->name ?> </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="moodle_paths">Learning Paths</label>
                        <select multiple class="input-sm form-control" name="moodle_paths[]" id="moodle_paths">
                            <?php foreach ($product_categories as $product_category) { ?>
                                <option value="<?= $product_category->term_id ?>" <?php echo array_search($product_category->term_id, @$devyai_options->moodle_paths) !== false ? "selected" : ''; ?>> <?= $product_category->name ?> </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-group">
            <legend>Options Forms</legend>
            <div class="row">
                <div class="col">
                    <label for="form_register">ID Form Register:</label>
                    <select class="input-sm form-control" id="form_request" name="form_register">
                        <?php foreach ($forms_maker as $value) : ?>
                            <option <?php echo (@$devyai_options->form_register == $value['id']) ? 'selected' : ''; ?>
                                    value="<?php echo $value['id']; ?>"><?php echo $value['title']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="checkbox-inline">
                        <input type="hidden" name="form_justregister" value="0">
                        <input type="checkbox" name="form_justregister" id="form_justregister" value="1" <?php echo $devyai_options->form_justregister ? 'checked' : '' ?> >Just Register
                    </label>
                </div>
                <div class="col">
                    <label for="form_request">ID Form Request: </label>
                    <select class="input-sm form-control" id="form_request" name="form_request">
                        <?php foreach ($post_ids as $key => $postId) : ?>
                            <option <?php echo (@$devyai_options->form_request == $postId) ? 'selected' : ''; ?>
                                    value="<?php echo $postId; ?>"><?php echo $form_titles[$key]; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset class="form-group">
            <legend>Notifications</legend>
            <div class="row">
                <div class="col">
                    <label for="notification_email">Email:</label>
                    <input type="text" class="form-control form-control-sm" id="notification_email" name="notification_email"
                           value="<?php echo @$devyai_options->notification_email; ?>">
                </div>
            </div>
        </fieldset>
        <div class="row mt-2">
            <div class="col">
                <input type="hidden" name="action_settings" value="changeSettings">
                <button type="submit" class="btn btn-sm btn-success">Save & Test</button>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function(){
        $('select').select2();
    });
</script>