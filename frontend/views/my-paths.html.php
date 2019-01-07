<?php

if (!defined('ABSPATH'))
    exit;

global $wpdb;
$tablename = $wpdb->prefix . 'devyai_submissions';

/** @var WP_User $user */
$user = wp_get_current_user();

$total_items = $wpdb->get_var("SELECT count(*) FROM $tablename;");

$entries = $wpdb->get_results('SELECT * FROM ' . $tablename . ' WHERE email = "' . $user->user_email . '";', ARRAY_A);
?>

<h3 class="bg-primary-default"><?php echo __('My Paths','devyai') ?></h3>
<div class="container">
    <div class="table-responsive">
        <table class="shop_table shop_table_responsive my_account_orders">
            <thead>
            <tr style="background: #6d89af;">
                <th>#</th>
                <th><?php echo __('Date','devyai')?></th>
                <th><?php echo __('Path Requests','devyai')?></th>
                <th><?php echo __('State','devyai')?></th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($entries)):
                foreach ($entries as $i => $entry) : ?>
                    <tr class="order">
                        <td><?php echo $i + 1; ?></td>
                        <td><?= $entry['time']; ?></td>
                        <td><?= str_replace("\n", "<br>", $entry['paths']); ?></td>
                        <td><?php
                            $colors = array("warning", "success", "danger", "dark");
                            $status = array("Waiting", "Approved", "Denied", "No proceed");
                            $state = $status[$entry['status']];
                            $color = $colors[$entry['status']];
                            echo "<span class='btn btn-$color btn-xs'>$state</span>";
                            ?></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td><?php echo __('You have no active enrollments','devyai')?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>