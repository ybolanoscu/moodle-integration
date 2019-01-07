<?php
$devyaimsgs = $_SESSION['messages'];
?>
<div class="container">
    <div class="row">
        <?php if (!empty($devyaimsgs)) : ?>
            <div class="fm-form-container fm-theme4">
                <div class="fm-form" style="width: 100%">
                    <div class="fm-message fm-notice-success">
                        <?php foreach ($devyaimsgs as $message) : ?>
                            <p><?= $message ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php wd_form_maker($fmkid, "embedded"); ?>
    </div>
</div>