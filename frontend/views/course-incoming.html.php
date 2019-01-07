<?php

if (!defined('ABSPATH'))
    exit;
?>

<h3><?php echo __('Course Incoming', 'atlantis'); ?></h3>
<div class="container">
    <div class="table-responsive">
        <table class="shop_table shop_table_responsive my_account_orders">
            <thead>
            <tr>
                <th>#</th>
                <th><?php echo __('Name','devyai')?></th>
                <th><?php echo __('Start Date','devyai')?></th>
                <th><?php echo __('End Date','devyai')?></th>
                <th><?php echo __('Enrollments','devyai')?></th>
                <th><?php echo __('Incomings','devyai')?></th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($entries)):
                foreach ($entries as $i => $entry) : ?>
                    <tr class="order">
                        <td><?= $entry['name']; ?></td>
                        <td><?= $entry['from']; ?></td>
                        <td><?= $entry['to']; ?></td>
                        <td><?= $entry['qta_students']; ?></td>
                        <td><?= $entry['incoming']; ?></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td><?php echo __('No Courses','devyai')?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>