<!doctype html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <meta http-equiv="imagetoolbar" content="no">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>관리자메인 | 그누보드55</title>
    <link rel="stylesheet" href="http://127.0.0.1/g5-update/adm/css/admin.css?ver=2303229">
    <link rel="stylesheet" href="http://127.0.0.1/g5-update/js/font-awesome/css/font-awesome.min.css?ver=2303229">
    <!--[if lte IE 8]>
<script src="http://127.0.0.1/g5-update/js/html5.js"></script>
<![endif]-->
    <script>
        // 자바스크립트에서 사용하는 전역변수 선언
        var g5_url = "http://127.0.0.1/g5-update";
        var g5_bbs_url = "http://127.0.0.1/g5-update/bbs";
        var g5_is_member = "1";
        var g5_is_admin = "super";
        var g5_is_mobile = "";
        var g5_bo_table = "";
        var g5_sca = "";
        var g5_editor = "";
        var g5_cookie_domain = "";
        var g5_shop_url = "http://127.0.0.1/g5-update/shop";
        var g5_admin_url = "http://127.0.0.1/g5-update/adm";
    </script>
    <script src="http://127.0.0.1/g5-update/js/jquery-1.12.4.min.js?ver=2304171"></script>
    <script src="http://127.0.0.1/g5-update/js/jquery-migrate-1.4.1.min.js?ver=2304171"></script>
    <script src="http://127.0.0.1/g5-update/js/jquery.menu.js?ver=2304171"></script>
    <script src="http://127.0.0.1/g5-update/js/common.js?ver=2304171"></script>
    <script src="http://127.0.0.1/g5-update/js/wrest.js?ver=2304171"></script>
    <script src="http://127.0.0.1/g5-update/js/placeholders.min.js?ver=2304171"></script>
</head>

<body>
    <script>
        var g5_admin_csrf_token_key = "d852959afed8549405841de4dfb45559";
    </script>
    <div id="hd_login_msg">최고관리자님 로그인 중 <a href="http://127.0.0.1/g5-update/bbs/logout.php">로그아웃</a></div>

    <div id="to_content"><a href="#container">본문 바로가기</a></div>

    <header id="hd">
        {% block header %}
            {{ include('/theme/basic_test/admin/_header.php') }}
        {% endblock header %}
    </header>

    <nav id="gnb" class="gnb_large ">
        {% block snb %}
            {{ include('/theme/basic_test/admin/_snb.php') }}
        {% endblock snb %}
    </nav>

    <div id="wrapper">
        <div id="container" class="">{% block content %}{% endblock content %}</div>
    </div>

    <footer id="ft">
        {% block footer %}
            {{ include('/theme/basic_test/admin/_footer.php') }}
        {% endblock footer %}
    </footer>
</body>

</html>