<?php

if (!defined('ABSPATH'))
    exit;

?>

<h3 class="bg-primary-default"><?php echo __('My Courses', 'devyai'); ?></h3>
<div class="container">
    <div class="table-responsive">
        <table class="shop_table shop_table_responsive my_account_orders">
            <thead>
            <tr style="background: #6d89af;">
                <th>#</th>
                <th><?php echo __('Name','devyai')?></th>
                <th><?php echo __('Fullname','devyai')?></th>
                <th><?php echo __('StartDate','devyai')?></th>
                <th><?php echo __('EndDate','devyai')?></th>
                <th><?php echo __('Progress','devyai')?></th>
                <th><?php echo __('Grades Avg','devyai')?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($entries)):
                foreach ($entries as $i => $entry) : ?>
                    <tr class="order">
                        <td><?php echo $i + 1; ?></td>
                        <td><?= $entry->shortname; ?></td>
                        <td><?= $entry->fullname; ?></td>
                        <td><?= !empty($entry->startdate) ? date("m/d/Y H:i:s", $entry->startdate) : '-'; ?></td>
                        <td><?= !empty($entry->enddate) ? date("m/d/Y H:i:s", $entry->enddate) : '-'; ?></td>
                        <td><?= $entry->progress ? $entry->progress . '%' : '-'; ?></td>
                        <td><?= $entry->average ? $entry->average : '-'; ?></td>
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