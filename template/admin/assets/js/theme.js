$(function () {
    $(".theme_active").on("click", function () {
        var name = $(this).data("name");
        var uri = $(this).data("uri");

        if (!confirm(name + " 테마를 적용하시겠습니까?")) {
            return false;
        }

        $.ajax({
            type: "POST",
            url: uri,
            cache: false,
            async: false,
            beforeSend: function (xhr) {
                for (let key in csrf) {
                    if (!csrf[key]) {
                        alert("CSRF 토큰이 유효하지 않습니다. 새로고침 후 다시 시도해 주세요.");
                        return false;
                    }
                    xhr.setRequestHeader(key, csrf[key]);
                }
            },
            success: function (data) {
                alert(data.message);
                document.location.reload();
            },
            error: function (xhr, status, error) {
                let result = xhr.responseJSON;
                alert(xhr.status + ' ' + error + ': ' + result.error.message);
            }
        });
    });

    $(".theme_deactive").on("click", function () {
        var name = $(this).data("name");
        var uri = $(this).data("uri");

        if (!confirm(name + " 테마 사용설정을 해제하시겠습니까?\n\n테마 설정을 해제하셔도 게시판 등의 스킨은 변경되지 않으므로 개별 변경작업이 필요합니다.")) {
            return false;
        }

        $.ajax({
            type: "POST",
            url: uri,
            cache: false,
            async: false,
            beforeSend: function (xhr) {
                for (let key in csrf) {
                    if (!csrf[key]) {
                        alert("CSRF 토큰이 유효하지 않습니다. 새로고침 후 다시 시도해 주세요.");
                        return false;
                    }
                    xhr.setRequestHeader(key, csrf[key]);
                }
            },
            success: function (data) {
                alert(data.message);
                document.location.reload();
            },
            error: function (xhr, status, error) {
                let result = xhr.responseJSON;
                alert(xhr.status + ' ' + error + ': ' + result.error.message);
            }
        });
    });

    $(".theme_preview").on("click", function () {
        // 버튼에 저장된 데이터 속성 값들을 가져옴
        const theme = this.dataset.theme;
        const themeName = this.dataset.themeName;
        const themeUri = this.dataset.themeUri;
        const themeMaker = this.dataset.themeMaker;
        const themeMakerUri = this.dataset.themeMakerUri;
        const themeVersion = this.dataset.themeVersion;
        const themeDetail = this.dataset.themeDetail;
        const themeLicense = this.dataset.themeLicense;
        const themeLicenseUri = this.dataset.themeLicenseUri;
        const themeScreenshot = this.dataset.themeScreenshot;

        // 레이어 팝업 내부에 데이터 세팅
        document.getElementById('theme_screenshot').src = themeScreenshot;
        document.getElementById('theme_version').innerText = themeVersion;
        document.getElementById('theme_description').innerText = themeDetail;

        const themePreview = document.getElementById('theme_preview');
        themePreview.value = theme;

        // 테마 이름 설정
        const themeNameElement = document.getElementById('theme_name');
        if (themeUri) {
            themeNameElement.innerHTML = `<a href="${themeUri}" target="_blank">${themeName}</a>`;
        } else {
            themeNameElement.innerText = themeName;
        }

        // Maker 정보 설정
        const themeMakerElement = document.getElementById('theme_maker');
        if (themeMakerUri) {
            themeMakerElement.innerHTML = `<a href="${themeMakerUri}" target="_blank">${themeMaker}</a>`;
        } else {
            themeMakerElement.innerText = themeMaker;
        }

        // License 정보 설정
        const themeLicenseElement = document.getElementById('theme_license');
        if (themeLicenseUri) {
            themeLicenseElement.innerHTML = `<a href="${themeLicenseUri}" target="_blank">${themeLicense}</a>`;
        } else {
            themeLicenseElement.innerText = themeLicense;
        }

        // 삭제버튼 표시 여부 설정
        const currentTheme = document.getElementById('current_theme');
        if (theme === currentTheme.value || theme === 'basic') {
            $("#delete_btn").hide();
        } else {
            $("#delete_btn").show();
        }

        $("#theme_detail").show();
    });

    $(".close_btn").on("click", function () {
        $(this).parents("#theme_detail").hide();
    });

    $("#delete_btn").on("click", function () {
        let theme = $("#theme_preview").val();
        let url = $(this).data("url");
        delete_confirm(url.replace('__REPLACE_ID__', theme));
    });
});