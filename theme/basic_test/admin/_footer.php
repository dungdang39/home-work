<p>
    Copyright &copy; 127.0.0.1. All rights reserved. Version 5.6.3<br>
    <button type="button" class="scroll_top"><span class="top_img"></span><span class="top_txt">TOP</span></button>
</p>
<div id="ft_copy"></div>

<script>
    $(".scroll_top").click(function() {
        $("body,html").animate({
            scrollTop: 0
        }, 400);
    })
</script>

<!-- <p>실행시간 : 0.013005971908569 -->

<script src="http://127.0.0.1/g5-update/adm/admin.js?ver=2304171"></script>
<script src="http://127.0.0.1/g5-update/js/jquery.anchorScroll.js?ver=2304171"></script>
<script>
    $(function() {

        var admin_head_height = $("#hd_top").height() + $("#container_title").height() + 5;

        $("a[href^='#']").anchorScroll({
            scrollSpeed: 0, // scroll speed
            offsetTop: admin_head_height, // offset for fixed top bars (defaults to 0)
            onScroll: function() {
                // callback on scroll start
            },
            scrollEnd: function() {
                // callback on scroll end
            }
        });

        var hide_menu = false;
        var mouse_event = false;
        var oldX = oldY = 0;

        $(document).mousemove(function(e) {
            if (oldX == 0) {
                oldX = e.pageX;
                oldY = e.pageY;
            }

            if (oldX != e.pageX || oldY != e.pageY) {
                mouse_event = true;
            }
        });

        // 주메뉴
        var $gnb = $(".gnb_1dli > a");
        $gnb.mouseover(function() {
            if (mouse_event) {
                $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
                $(this).parent().addClass("gnb_1dli_over gnb_1dli_on");
                menu_rearrange($(this).parent());
                hide_menu = false;
            }
        });

        $gnb.mouseout(function() {
            hide_menu = true;
        });

        $(".gnb_2dli").mouseover(function() {
            hide_menu = false;
        });

        $(".gnb_2dli").mouseout(function() {
            hide_menu = true;
        });

        $gnb.focusin(function() {
            $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
            $(this).parent().addClass("gnb_1dli_over gnb_1dli_on");
            menu_rearrange($(this).parent());
            hide_menu = false;
        });

        $gnb.focusout(function() {
            hide_menu = true;
        });

        $(".gnb_2da").focusin(function() {
            $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
            var $gnb_li = $(this).closest(".gnb_1dli").addClass("gnb_1dli_over gnb_1dli_on");
            menu_rearrange($(this).closest(".gnb_1dli"));
            hide_menu = false;
        });

        $(".gnb_2da").focusout(function() {
            hide_menu = true;
        });

        $('#gnb_1dul>li').bind('mouseleave', function() {
            submenu_hide();
        });

        $(document).bind('click focusin', function() {
            if (hide_menu) {
                submenu_hide();
            }
        });

        // 폰트 리사이즈 쿠키있으면 실행
        var font_resize_act = get_cookie("ck_font_resize_act");
        if (font_resize_act != "") {
            font_resize("container", font_resize_act);
        }
    });

    function submenu_hide() {
        $(".gnb_1dli").removeClass("gnb_1dli_over gnb_1dli_over2 gnb_1dli_on");
    }

    function menu_rearrange(el) {
        var width = $("#gnb_1dul").width();
        var left = w1 = w2 = 0;
        var idx = $(".gnb_1dli").index(el);

        for (i = 0; i <= idx; i++) {
            w1 = $(".gnb_1dli:eq(" + i + ")").outerWidth();
            w2 = $(".gnb_2dli > a:eq(" + i + ")").outerWidth(true);

            if ((left + w2) > width) {
                el.removeClass("gnb_1dli_over").addClass("gnb_1dli_over2");
            }

            left += w1;
        }
    }
</script>


<!-- <div style='float:left; text-align:center;'>RUN TIME : 0.013273954391479<br></div> -->