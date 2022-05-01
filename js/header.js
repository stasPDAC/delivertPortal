$(document).ready(function () {
    $('.icon').click(function () {
        $('.icon').toggleClass('active');
        $('.menu_mobile').toggleClass('active');
        $('.menu_mobile_bg').toggleClass('active');
    });
    $('.menu_mobile_bg').click(function () {
        $('.icon').toggleClass('active');
        $('.menu_mobile').toggleClass('active');
        $('.menu_mobile_bg').toggleClass('active');
    });

    $(window).resize(function () {
        const winWidth = $(window).width();
        if (winWidth > 992) {
            $('.icon').removeClass('active');
            $('.menu_mobile').removeClass('active');
            $('.menu_mobile_bg').removeClass('active');
        }
    });

    $('.sub_bt_mobile').click(function () {
        $('.sub_bt_mobile').toggleClass('active');
        $('.sub_menu_mobile').toggleClass('active');
    });
});