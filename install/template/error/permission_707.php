<?php $this->layout('_layout', ['title' => $version . " 설치 오류"]) ?>

<div class="ins_inner">
    <p>
        <?=$this->e($data_dir)?> 디렉토리의 퍼미션을 707로 변경하여 주십시오.<br /><br />
        $> chmod 707 <?=$this->e($data_dir)?> 또는 chmod uo+rwx <?=$this->e($data_dir)?><br /><br />
        위 명령 실행후 브라우저를 새로고침 하십시오.
    </p>
</div>