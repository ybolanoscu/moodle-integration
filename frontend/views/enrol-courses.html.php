<?php

if (!defined('ABSPATH'))
    exit;

?>

<h3 class="bg-primary-default" ><?php echo __('Courses', 'devyai'); ?></h3>

<table class="shop_table shop_table_responsive my_account_orders">
    <thead>
    <tr style="background: #6d89af;">
        <th>#</th>
        <th><?php echo __('Fullname','devyai')?></th>
        <th><?php echo __('Start Date','devyai')?></th>
        <th><?php echo __('End Date','devyai')?></th>
        <th><?php echo __('Students','devyai')?></th>
        <th><?php echo __('Grades Avg','devyai')?></th>
    </tr>
    </thead>

    <tbody>
    <?php if (!empty($entries)):
        foreach ($entries as $i => $entry) : ?>
            <tr class="order">
                <td><?php echo $i + 1; ?></td>
                <td><?= $entry['name']; ?></td>
                <td><?= $entry['from']; ?></td>
                <td><?= $entry['to']; ?></td>
                <td><?= $entry['qta_students']; ?></td>
                <td><?= $entry['avg_grade']; ?></td>
            </tr>
        <?php endforeach;
    else: ?>
        <tr>
            <td><?php echo __('You have no active enrollments','devyai')?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
