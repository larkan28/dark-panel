jQuery(function ($) {
    $(".sidebar-dropdown > a").click(function() {
        $(".sidebar-submenu").slideUp(200);

        if ($(this).parent().hasClass("active")) {
            $(".sidebar-dropdown").removeClass("active");
            $(this).parent().removeClass("active");
        }
        else {
            $(".sidebar-dropdown").removeClass("active");
            $(this).next(".sidebar-submenu").slideDown(200);
            $(this).parent().addClass("active");
        }
    });

    $("#close-sidebar").click(function() {
        $(".page-wrapper").removeClass("toggled");
    });
    
    $("#show-sidebar").click(function() {
        $(".page-wrapper").addClass("toggled");
    });

    $(".sel-GameType").change(function() {
        var value = $(this).val();

        if (value === "-")
            window.location = "index.php?m=adm-servers&p=new";
        else
            window.location = "index.php?m=adm-servers&p=new&game_type=" + value;
    });

    $(".sel-ServerID").change(function() {
        var values = $(this).val().split(",");

        if (values[1] === "-")
            window.location = "index.php?m=sub-users&p=edit&user_id=" + values[0];
        else
            window.location = "index.php?m=sub-users&p=edit&user_id=" + values[0] + "&server_id=" + values[1];
    });

    $(".sel-CurrLang").change(function() {
        var value = $(this).val();

        if (value)
            window.location = $(location).attr("href") + "&lang=" + value;
    });

    $('.check-AccessServer').change(function () {
        if ($(this).is(":checked")) {
            $('.check-AccessAllow').prop('checked', false);
            $('.check-AccessAllow').removeAttr("disabled");
        }
        else {
            $('.check-AccessAllow').prop('checked', false);
            $('.check-AccessAllow').attr("disabled", true);
        }
    });
});