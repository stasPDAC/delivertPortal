$(document).ready(function () {
    // $('.icon').click(function () {
    //     $('.icon').toggleClass('active');
    //     $('.menu_mobile').toggleClass('show_menu');
    //     $('.sub_menu').css("display", "none");
    //     if ($(".icon").attr("aria-expanded") == "false") {
    //         $(".icon").attr("aria-expanded", "true");
    //         $(".icon").attr("aria-label", "לחץ לסגירת תפריט");
    //         $(".menu_label a").attr("tabindex", "0");
    //     } else {
    //         $(".icon").attr("aria-expanded", "false");
    //         $(".icon").attr("aria-label", "לחץ לפתיחת תפריט");
    //         $(".menu_mobile a").attr("tabindex", "-1");
    //     }
    // });

    // $(window).resize(function () {
    //     var winWidth = $(window).width();
    //     if (winWidth > 768) {
    //         $('.icon').removeClass('active');
    //         $('.menu_mobile').removeClass('show_menu');
    //     }
    // });

    // $(document).on('keydown', function (event) {
    //     if (event.keyCode == 27) {
    //         if ($('.icon').hasClass('active')) {
    //             $('.icon').toggleClass('active');
    //             $('.menu_mobile').toggleClass('show_menu');
    //             if ($(".icon").attr("aria-expanded") == "false") {
    //                 $(".icon").attr("aria-expanded", "true");
    //                 $(".icon").attr("aria-label", "לחץ לסגירת תפריט");
    //                 $(".menu_label a").attr("tabindex", "0");
    //             } else {
    //                 $(".icon").attr("aria-expanded", "false");
    //                 $(".icon").attr("aria-label", "לחץ לפתיחת תפריט");
    //                 $(".menu_mobile a").attr("tabindex", "-1");
    //
    //             }
    //         }
    //     }
    // });

    // $(".open_sub_menu").click(function () {
    //     $(this).find(".sub_menu").slideToggle();
    //     var svgElem = $(this).find("svg");
    //
    //     if (svgElem.css("transform") === "none") {
    //         svgElem.css("transform", "rotate(-180deg)");
    //
    //     } else {
    //         svgElem.css("transform", "none");
    //     }
    // });


    //	start accessibility

    $(".show_accessibility").click(function () {
        $(".accessibility_btns").slideToggle();
        $(".accessibility_open").slideToggle();
        if ($(".show_accessibility").attr("aria-expanded") === "false") {
            $(".show_accessibility").attr("aria-expanded", "true");
        } else {
            $(".show_accessibility").attr("aria-expanded", "false");
        }
    });

    $("#invert").click(function () {
        if ($("body").css("filter") === "invert(1)") {
            $("body").css("filter", "invert(0)");
            $.post("ajax.php", {invert: "false"});
        } else {
            $("body").css("filter", "invert(1)");
            $.post("ajax.php", {invert: "true"});
        }
        if ($("#invert").attr("aria-pressed") === "false") {
            $("#invert").attr("aria-pressed", "true");
        } else {
            $("#invert").attr("aria-pressed", "false");
        }
    });


    $("#small_text").click(function () {
        $("body").css("font-size", "16px");
        $.post("ajax.php", {font_size: "true"});
        if ($("body").css("font-size") === "16px") {
            $("#small_text").attr("aria-pressed", "true");
            $("#large_text").attr("aria-pressed", "false");
        } else {
            $("#small_text").attr("aria-pressed", "false");
            $("#large_text").attr("aria-pressed", "true");
        }
    });


    $("#large_text").click(function () {
        $("body").css("font-size", "22px");
        $.post("ajax.php", {font_size: "false"});
        if ($("body").css("font-size") === "22px") {
            $("#large_text").attr("aria-pressed", "true");
            $("#small_text").attr("aria-pressed", "false");
        } else {
            $("#large_text").attr("aria-pressed", "false");
            $("#small_text").attr("aria-pressed", "true");
        }
    });

//	end accessibility


});