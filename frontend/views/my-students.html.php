<?php

if (!defined('ABSPATH'))
    exit;

?>

<h3 class="bg-primary-default"><?php echo __('My Students', 'devyai'); ?></h3>
<div class="alert alert-danger m-0 d-none" role="alert">
    There are no courses for this student.
</div>
<table class="shop_table shop_table_responsive my_account_orders">

    <thead>
    <tr style="background: #6d89af;">
        <th>#</th>
        <th><?php echo __('Name','devyai')?></th>
        <th><?php echo __('Fullname','devyai')?></th>
        <th><?php echo __('Last Access','devyai')?></th>
        <th><i class="fa fa-eye"></i></th>
    </tr>
    </thead>

    <tbody>

    <?php if (!empty($entries)):
        foreach ($entries as $i => $entry) : ?>
            <tr class="order">
                <td><?php echo $i + 1; ?></td>
                <td><?= $entry->fullname; ?></td>
                <td><?= $entry->email; ?></td>
                <td><?= !empty($entry->firstaccess) ? date("m/d/Y H:i:s",$entry->firstaccess) : '-'; ?></td>
                <td><?= !empty($entry->lastaccess) ? date("m/d/Y H:i:s", $entry->lastaccess) : '-'; ?></td>
                <td><a href="#!" data-student-id="<?=$entry->id?>" class="woocommerce-button button view">Courses</a></td>
            </tr>
        <?php endforeach;
    else: ?>
        <tr>
            <td><?php echo __('You have no active enrollments','devyai')?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>



<div class="modal fade" id="course-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        </div>
    </div>
</div>


<script>
    $(document).on('click',function () {
       $('div.alert').addClass('d-none');
    });
    var $modal = $('#course-modal');
    $('a.view').click(function (evt) {
        var $this=$(this);
        $this.text('');
        $this.append('<i class="fa fa-spinner fa-spin">');
        evt.preventDefault();
        var form_data = {};
        form_data['action'] = 'dashboard_courses_by_user';
        form_data['user_id'] = $(this).data('student-id');
        $modal.find('.modal-content').html('Loading content...');
        $.post('<?php  echo admin_url('admin-ajax.php'); ?>', form_data, function (response) {
            if (response.success) {
                $modal.find('.modal-content').html(response.html);
                $modal.modal('show');
                return
            }
            $('div.alert').removeClass('d-none');
            })
            .done(function(){
                $this.remove('i');
                $this.text('courses');
            });
    });
</script>