<?php $this->layout('_layout', ['title' => $version . " 초기환경설정 2/3"]) ?>

<form id="frm_install" method="post" action="./install_result.php" autocomplete="off" onsubmit="return frm_install_submit(this)">

    <div class="ins_inner">
        <table class="ins_frm">
            <caption>MySQL 정보입력</caption>
            <colgroup>
                <col style="width:150px">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="mysql_host">Host</label></th>
                    <td><input name="mysql_host" type="text" value="localhost" id="mysql_host" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mysql_user">User</label></th>
                    <td><input name="mysql_user" type="text" id="mysql_user" required value="kjh"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mysql_pass">Password</label></th>
                    <td><input name="mysql_pass" type="text" id="mysql_pass" value="1234"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mysql_db">DB</label></th>
                    <td><input name="mysql_db" type="text" id="mysql_db" required value="gnuboard7"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="table_prefix">TABLE명 접두사</label></th>
                    <td>
                        <input name="table_prefix" type="text" value="g5_" id="table_prefix">
                        <span>TABLE명 접두사는 영문자, 숫자, _ 만 입력 가능합니다.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="shop_table_prefix">쇼핑몰TABLE명 접두사</label></th>
                    <td>
                        <input name="shop_table_prefix" type="text" value="g5_shop_" id="shop_table_prefix">
                        <span>쇼핑몰TABLE명 접두사는 영문자, 숫자, _ 만 입력 가능합니다.</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="reinstall"><?=$this->e($version)?> 재설치</label></th>
                    <td><input name="reinstall" type="checkbox" value="1" id="reinstall"> 재설치</td>
                </tr>
                <tr>
                    <th scope="row"><label for="shop_install">쇼핑몰설치</label></th>
                    <td><input name="shop_install" type="checkbox" value="1" id="shop_install" checked> 설치</td>
                </tr>
            </tbody>
        </table>

        <table class="ins_frm">
            <caption>최고관리자 정보입력</caption>
            <input type="hidden" name="ajax_token" value="<?=$this->e($ajax_token)?>">
            <colgroup>
                <col style="width:150px">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="admin_id">회원 ID</label></th>
                    <td><input name="admin_id" type="text" value="admin" id="admin_id" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_pass">비밀번호</label></th>
                    <td><input name="admin_pass" type="password" id="admin_pass" required value="1234"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_name">이름</label></th>
                    <td><input name="admin_name" type="text" value="최고관리자" id="admin_name" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_email">E-mail</label></th>
                    <td><input name="admin_email" type="email" value="admin@domain.com" id="admin_email" required></td>
                </tr>
            </tbody>
        </table>

        <p>
            <strong class="st_strong">주의! 이미 <?=$this->e($version)?>가 존재한다면 DB 자료가 망실되므로 주의하십시오.</strong><br>
            주의사항을 이해했으며, 그누보드 설치를 계속 진행하시려면 다음을 누르십시오.
        </p>

        <div class="inner_btn">
            <input type="submit" value="다음">
        </div>
    </div>
</form>

<script src="../js/jquery-1.8.3.min.js"></script>
<script>
    function frm_install_submit(f) {
        const requiredFields = [
            { field: f.mysql_host, message: 'MySQL Host 를 입력하십시오.' },
            { field: f.mysql_user, message: 'MySQL User 를 입력하십시오.' },
            { field: f.mysql_db, message: 'MySQL DB 를 입력하십시오.' },
            { field: f.admin_id, message: '최고관리자 ID 를 입력하십시오.' },
            { field: f.admin_pass, message: '최고관리자 비밀번호를 입력하십시오.' },
            { field: f.admin_name, message: '최고관리자 이름을 입력하십시오.' },
            { field: f.admin_email, message: '최고관리자 E-mail 을 입력하십시오.' }
        ];

        for (let i = 0; i < requiredFields.length; i++) {
            const { field, message } = requiredFields[i];
            if (field.value.trim() === '') {
                alert(message);
                field.focus();
                return false;
            }
        }

        const reg = /\);(passthru|eval|pcntl_exec|exec|system|popen|fopen|fsockopen|file|file_get_contents|readfile|unlink|include|include_once|require|require_once)\s?\(\$_(get|post|request)\s?\[.*?\]\s?\)/gi;
        const reg_msg = " 에 유효하지 않는 문자가 있습니다. 다른 문자로 대체해 주세요.";

        const fieldsToValidate = [
            { field: f.mysql_host, message: 'MySQL Host' },
            { field: f.mysql_user, message: 'MySQL User' },
            { field: f.mysql_pass, message: 'MySQL PASSWORD' },
            { field: f.mysql_db, message: 'MySQL DB' },
            { field: f.table_prefix, message: 'TABLE명 접두사' }
        ];

        for (let i = 0; i < fieldsToValidate.length; i++) {
            const { field, message } = fieldsToValidate[i];
            if (field.value && reg.test(field.value)) {
                alert(message + reg_msg);
                field.focus();
                return false;
            }
        }

        if (!/^[a-z]+[a-z0-9]{2,19}$/i.test(f.admin_id.value)) {
            alert('최고관리자 회원 ID는 첫자는 반드시 영문자 그리고 영문자와 숫자로만 만드셔야 합니다.');
            f.admin_id.focus();
            return false;
        }

        if (window.jQuery) {
            const jqxhr = jQuery.post("ajax.form_check.php", $(f).serialize(), function(data) {
                if (data.error) {
                    alert(data.error);
                } else if (data.exists) {
                    if (confirm(data.exists)) {
                        f.submit();
                    }
                } else if (data.success) {
                    f.submit();
                }
            }, "json");

            jqxhr.fail(function(xhr) {
                alert(xhr.responseText);
            });

            return false;
        }

        return true;
    }
</script>