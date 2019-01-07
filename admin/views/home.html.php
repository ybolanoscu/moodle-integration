<?php include_once __DIR__ . '/../ApplicantTable.php';

$testListTable = new ApplicantTable();
$testListTable->prepare_items();

settings_errors('devyai_messages');
?>
<h2 style="margin-top: 20px;">Applicant for Paths</h2>

<form id="submissions-filter" method="post">
    <?php $testListTable->display() ?>
</form>

<div class="modal fade" id="changeStatus" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
     aria-hidden="true">
    <div class="modal-dialog mt-5" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h4 class="modal-title" id="modelTitleId">Create / Approve New Path</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="category">
                        <div class="form-group">
                            <label for="cat-name" class="control-label">Path Name:</label>
                            <div class="col-sm-12" style="padding: 0;">
                                <input type="text" id="cat-name" name="category-name" class="form-control input-sm" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cat-description" class="control-label">Path Description:</label>
                            <div class="col-sm-12" style="padding: 0;">
                                <textarea id="cat-description" type="text" name="category-description" class="form-control input-sm" required="required"></textarea>
                            </div>
                        </div>
                    </div>
                    <span id="are_sure">Are you sure?</span>
                    <input type="hidden" name="action" id="action_modal">
                    <input type="hidden" name="applicant[]" id="applicant_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.action-status').on('click', function () {
            var $this = $(this);
            var state = $(this).data('state');
            var categoryName = $('form').find('#category');
            var areSure = $('form').find('#are_sure');
            $('#applicant_id').val($this.data('id'));
            $('#action_modal').val(state);
            if (state == 'approve') {
                categoryName.show();
                areSure.hide();
            }
            else {
                categoryName.hide();
                areSure.show();
            }
        });
    })
</script>