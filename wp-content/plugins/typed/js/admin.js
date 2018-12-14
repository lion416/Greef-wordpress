
jQuery(document).ready(function (jQuery) {

    jQuery(document).on('click', '.add_typed_string', function () {
    var html = '<p><input type="text" name="typed_strings[]" value=""/> <a href="javascript:void(0)" class="add_typed_string button-secondary">+</a> <a href="javascript:void(0)" class="remove_typed_string button-secondary">-</a></p>';
    jQuery('.typed_strings_container').append(html);
});

jQuery(document).on('click', '.remove_typed_string', function () {
    jQuery(this).parent().remove();
});



    //color field
    jQuery('.typed-color-field').wpColorPicker();

    //typed admin perview
    if (jQuery('.typed_perview_div').length > 0) {
        var strings = jQuery('.typed_perview_div').attr('data-strings').split(';#$#;');
        var speed = jQuery('.typed_perview_div').attr('data-speed');
        var delay = jQuery('.typed_perview_div').attr('data-delay');
        var back_delay = jQuery('.typed_perview_div').attr('data-back-delay');
        var back_speed = jQuery('.typed_perview_div').attr('data-back-speed');
        var loop = jQuery('.typed_perview_div').attr('data-loop');
        if (loop == 'yes')
            loop = true
        else
            loop = false;

        var cursor = jQuery('.typed_perview_div').attr('data-cursor');
        if (cursor == 'yes')
            cursor = true
        else
            cursor = false;

        var cursor_char = jQuery('.typed_perview_div').attr('data-cursor-char');

        jQuery(".typed_perview_div").typed({
            strings: strings,
            typeSpeed: parseInt(speed),
            // time before typing starts
            startDelay: parseInt(delay),
            // backspacing speed
            backSpeed: parseInt(back_speed),
            // time before backspacing
            backDelay: parseInt(back_delay),
            // loop
            loop: loop,
            // show cursor
            showCursor: cursor,
            // character for cursor
            cursorChar: cursor_char,
        });
    }
});