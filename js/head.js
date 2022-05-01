$(document).ready(function () {
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
});