<?php $this->layout('_layout', ['title' => $version . " 설치 오류"]) ?>

<div class="ins_inner">
    <p>
        루트 디렉토리에 아래로 <?=$this->e($data_dir)?> 디렉토리를 생성하여 주십시오.<br />
        (common.php 파일이 있는곳이 루트 디렉토리 입니다.)<br /><br />
        $> mkdir <?=$this->e($data_dir)?><br /><br />
        윈도우의 경우 data 폴더를 하나 생성해 주시기 바랍니다.<br /><br />
        위 명령 실행후 브라우저를 새로고침 하십시오.
    </p>
</div>