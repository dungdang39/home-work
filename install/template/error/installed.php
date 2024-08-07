<?php $this->layout('_layout', ['title' => $version . " 설치 오류"]) ?>

<h1><?=$this->e($version)?> 프로그램이 이미 설치되어 있습니다.</h1>

<div class="ins_inner">
    <p>프로그램이 이미 설치되어 있습니다.<br/>새로 설치하시려면 다음 파일을 삭제 하신 후 새로고침 하십시오.</p>
    <ul>
        <li><?=$this->e($env_file)?></li>
    </ul>
</div>