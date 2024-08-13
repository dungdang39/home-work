<h2>관리자 주메뉴</h2>
<ul class="gnb_ul">
    {% for menu in admin_menu %}
    <li class="gnb_li {{ menu.active ? 'on' : '' }}">
        <button type="button" class="btn_op menu-{{ loop.index }}00 menu-order-{{ loop.index }}" title="{{ menu.am_name }}">{{ menu.am_name }}</button>
        <div class="gnb_oparea_wr">
            <div class="gnb_oparea">
                <h3>{{ menu.am_name }}</h3>
                <ul>
                    {% for child in menu.children %}
                    <li><a href="{{ child.url }}" class="gnb_2da {{ child.active ? 'on' : '' }}">{{ child.am_name }}</a></li>
                    {% endfor %}
                </ul>
            </div>
        </div>
    </li>
    {% endfor %}
</ul>

<script>
    jQuery(function($) {
        $(".gnb_ul li .btn_op").click(function() {
            $(this).parent().addClass("on").siblings().removeClass("on");
        });
    });
</script>