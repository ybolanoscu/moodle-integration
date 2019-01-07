<h3 class="bg-primary-default"><?php echo __('My Evaluations', 'devyai'); ?></h3>
<div class="container">
    <div class="table-responsive">
        <table class="shop_table shop_table_responsive my_account_orders">
            <thead>
            <tr style="background: #6d89af;">
                <th>#</th>
                <th><?php echo __('Name','devyai')?></th>
                <th><?php echo __('Last name','devyai')?></th>
                <th><?php echo __('Start Date','devyai')?></th>
                <th><?php echo __('End Date','devyai')?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($entries)):
                foreach ($entries as $i => $entry) : ?>
                    <tr class="order">
                        <td><?php echo $i + 1; ?></td>
                        <td><?= $entry->course->shortname; ?></td>
                        <td><?= $entry->course->fullname; ?></td>
                        <td><?= !empty($entry->course->startdate) ? date("m/d/Y H:i:s", $entry->course->startdate) : '-'; ?></td>
                        <td><?= !empty($entry->course->enddate) ? date("m/d/Y H:i:s", $entry->course->enddate) : '-'; ?></td>
                    </tr>
                    <?php foreach ($entry->evaluations as $evaluation):
                        if ($evaluation->failed):?>
                            <tr class="order">
                                <td colspan="5"><?php echo $evaluation->failed; ?></td>
                            </tr>
                        <? else: ?>
                        <?php endif;
                    endforeach;
                endforeach;
            else: ?>
                <tr>
                    <td><?php echo __('You have no active enrollments','devyai')?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>