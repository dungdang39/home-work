function check_all(f) {
    var chk = document.getElementsByName("chk[]");

    for (i = 0; i < chk.length; i++)
        chk[i].checked = f.chkall.checked;
}

function btn_check(f, act) {
    if (act == "update") // 선택수정
    {
        f.action = list_update_php;
        str = "수정";
    }
    else if (act == "delete") // 선택삭제
    {
        f.action = list_delete_php;
        str = "삭제";
    }
    else
        return;

    var chk = document.getElementsByName("chk[]");
    var bchk = false;

    for (i = 0; i < chk.length; i++) {
        if (chk[i].checked)
            bchk = true;
    }

    if (!bchk) {
        alert(str + "할 자료를 하나 이상 선택하세요.");
        return;
    }

    if (act == "delete") {
        if (!confirm("선택한 자료를 정말 삭제 하시겠습니까?"))
            return;
    }

    f.submit();
}

function is_checked(elements_name) {
    var checked = false;
    var chk = document.getElementsByName(elements_name);
    for (var i = 0; i < chk.length; i++) {
        if (chk[i].checked) {
            checked = true;
        }
    }
    return checked;
}

function delete_confirm(href, message = "") {
    if (message == "") {
        message = "한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?";
    }
    if (!confirm(message)) {
        return false;
    }

    $.ajax({
        type: "DELETE",
        url: href,
        beforeSend: function (xhr) {
            for (let key in csrf) {
                if (!csrf[key]) {
                    alert("CSRF 토큰이 유효하지 않습니다. 새로고침 후 다시 시도해 주세요.");
                    return false;
                }
                xhr.setRequestHeader(key, csrf[key]);
            }
        },
        cache: false,
        async: false,
        success: function (data) {
            if (data.message) {
                alert(data.message);
            }
            document.location.reload();
        },
        error: function (xhr, status, error) {
            let result = xhr.responseJSON;
            alert(xhr.status + ' ' + error + ': ' + result.error.message);
        }
    });
}

async function get_member_info(mb_id) {
    return new Promise((resolve, reject) => {
        if (!mb_id || String(mb_id).trim() === "") {
            alert("유효하지 않은 회원 ID입니다.");
            return;
        }

        $.ajax({
            url: member_info_url.replace('__REPLACE_ID__', mb_id),
            type: "get",
            success: function (data) {
                resolve(data.member);
            },
            error: function (xhr, status, error) {
                let result = xhr.responseJSON;
                alert(xhr.status + ' ' + error + ': ' + result.error.message);
                reject(null);
            }
        });
    });
}

// 비밀번호 보기/숨기기 토글 함수
function togglePasswordVisibility(button) {
    const input = button.previousElementSibling;
    if (input && input.type === 'password') {
        input.type = 'text';
        button.textContent = '숨기기';
    } else if (input) {
        input.type = 'password';
        button.textContent = '보기';
    }
}