jQuery(document).ready(function (jQuery) {
    jQuery('.typed_perview_div').each(function () {
        var strings = jQuery(this).attr('data-strings').split(';#$#;');
        var speed = jQuery(this).attr('data-speed');
        var delay = jQuery(this).attr('data-delay');
        var back_delay = jQuery(this).attr('data-back-delay');
        var back_speed = jQuery(this).attr('data-back-speed');
        var loop = jQuery(this).attr('data-loop');
        if (loop == 'yes')
            loop = true
        else
            loop = false;

        var cursor = jQuery(this).attr('data-cursor');
        if (cursor == 'yes')
            cursor = true
        else
            cursor = false;

        var cursor_char = jQuery(this).attr('data-cursor-char');

        jQuery(this).typed({
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
    });
});