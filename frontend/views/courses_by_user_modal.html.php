<div class="row">
    <div id="accordion" class="col-12" role="tablist" aria-multiselectable="true">
        <?php foreach ($courses as $i => $course): ?>
        <div class="card">
            <div class="card-header" role="tab" id="tab-<?= $course->id ?>">
                <a data-toggle="collapse" data-parent="#accordion" href="#tab-content-<?= $course->id ?>" aria-expanded="false"
                   aria-controls="tab-<?= $course->id ?>" class="collapsed">
                    <h5 class="mb-0 float-left"><span class="icon">&nbsp;</span>
                        <?php echo $course->shortname; ?>
                    </h5>
                </a>
            </div>
            <div id="tab-content-<?=$course->id?>" data-parent="#accordion" class="collapse in <?= !$i?'show':''?>" role="tabpanel" aria-labelledby="tab-content-<?=$course->id?>">
                <div class="card-body p-4 course-content">
                    <h2 class="text-left">- <?=$course->fullname?></h2>
                   <?php if($course->summary):?>
                       <div class="description">
                           <?=$course->summary?>
                       </div>
                   <?php endif?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-xs btn-danger text-uppercase" data-dismiss="modal"><?php echo __('Close','devyai')?></button>
</div>
