function du_show_spinner() {
    setTimeout(function () {
        jQuery('#et_pb_loading_animation').css({'z-index': '999999'}).show();
    }, 200);
}

function du_hide_spinner() {
    jQuery('#et_pb_loading_animation').css({'z-index': '98'}).hide();
}

function du_apply_nav(layout_id) {
    if (confirm("Are you sure you want to do this? Your site settings will globally be overwritten (nav bar settings only). We strongly recommend you back up your Divi Customizer settings before doing this.")) {

        jQuery.colorbox.close();

        var method = "get-layout"; //yep nav bars are layouts too.. even though they aren't!!
        var du_url_request = du_site_url + "?du_action=" + method + "&du_layout_id=" + layout_id;

        du_url_request += '&du_req_date=' + Date.now();

        du_show_spinner();

        jQuery.ajax({
            url: du_url_request,
            success: function (response) {
                du_hide_spinner();

                var response_obj = jQuery.parseJSON(response);

                if (response_obj.error_success) {
                    jQuery('.du-layout-item-' + layout_id).addClass('import-complete');
                    jQuery('.du-layout-item-' + layout_id + ' .actions .apply-layout').html('Import Complete!').attr('onclick', '');
                    alert("Done! Your site will now utilise the styles from the Nav Bar pack you downloaded.");
                } else {
                    if (response_obj.error) {
                        jQuery(".du-content").html(response_obj.error);
                    } else if (response_obj.content) {
                        jQuery(".du-content").html(response_obj.content);
                    } else {
                        jQuery(".du-content").html('Unknown error.... Please try again or contact Layouts Cloud for support.');
                    }
                }
            },
            timeout: 90000 //in milliseconds
        });
    }
}

function du_save_snippet(snippet_id) {
    if (confirm("Are you sure you want to do this? Your snippet will be permanently overwritten.")) {
        var content = jQuery('.du-snippet-item-' + snippet_id + ' .du-snippet-content').val();

        if (!content.length) {
            alert("Please enter some snippet content!");
            return false;
        }

        content = content.replace(/<p>\[/g, '[');
        content = content.replace(/\]<\/p>/g, ']');
        content = content.trim();
        content = encodeURIComponent(content);

        var du_url_request = du_site_url + "?du_action=save-snippet&du_snippet_id=" + snippet_id;

        du_url_request += '&du_req_date=' + Date.now();

        du_show_spinner();

        jQuery.ajax({
            type: 'POST',
            url: du_url_request,
            data: 'du_snippet_content=' + content,
            timeout: 90000, // in milliseconds
            success: function (data) {
                var response_obj = jQuery.parseJSON(data);

                du_hide_spinner();

                if (response_obj.error_success) {
                    du_populate_frame();
                } else {
                    alert(response_obj.error);
                }
            }
        });

        return true;
    }
}

function du_save_new_snippet(snippet_id) {

    var snippet_name = jQuery('.du-snippet-item-new .du_snippet_name').val();
    var content = jQuery('.du-snippet-item-new .du-snippet-content').val();

    if (!snippet_name.length) {
        alert("Please enter a snippet name!");
        return false;
    }
    if (!content.length) {
        alert("Please enter some snippet content!");
        return false;
    }

    content = content.replace(/<p>\[/g, '[');
    content = content.replace(/\]<\/p>/g, ']');
    content = content.trim();
    content = encodeURIComponent(content);

    var du_url_request = du_site_url + "?du_action=save-snippet&du_snippet_name=" + encodeURIComponent(snippet_name);

    du_url_request += '&du_req_date=' + Date.now();

    du_show_spinner();

    jQuery.ajax({
        type: 'POST',
        url: du_url_request,
        data: 'du_snippet_content=' + content,
        timeout: 90000, // in milliseconds
        success: function (data) {
            var response_obj = jQuery.parseJSON(data);

            du_hide_spinner();

            if (response_obj.error_success) {
                du_populate_frame();
            } else {
                alert(response_obj.error);
            }
        }
    });

    return true;
}

function du_delete_snippet(snippet_id) {
    if (confirm("Are you sure you want to do this? Your snippet will be permanently deleted.")) {
        var du_url_request = du_site_url + "?du_action=delete-snippet&du_snippet_id=" + snippet_id;

        du_url_request += '&du_req_date=' + Date.now();

        du_show_spinner();

        jQuery.ajax({
            type: 'GET',
            url: du_url_request,
            timeout: 90000, // in milliseconds
            success: function (data) {
                var response_obj = jQuery.parseJSON(data);

                du_hide_spinner();

                if (response_obj.error_success) {
                    du_populate_frame();
                } else {
                    alert(response_obj.error);
                }
            }
        });

        return true;
    }
}

function du_copy_snippet(snippet_id) {
    var target = jQuery(".du-snippet-item-" + snippet_id + " .du-snippet-content");
    var succeed;
    var currentFocus = document.activeElement;

    target.focus();
    target.select();

    try {
        succeed = document.execCommand("copy");
    } catch (e) {
        succeed = false;
    }

    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }

    return succeed;
}

function du_toggle_snippet(snippet_id) {
    jQuery('.du-snippet-item .du-snippet-content-container').slideUp();
    jQuery('.du-snippet-item-' + snippet_id + ' .du-snippet-content-container').slideDown();
}

function du_apply_layout(layout_id, own_layout, just_import) {
    jQuery.colorbox.close();

    var method = "get-layout";

    if (own_layout) {
        method = "get-own-layout";
    }

    var du_url_request = du_site_url + "?du_action=" + method + "&du_layout_id=" + layout_id;

    du_url_request += '&du_req_date=' + Date.now();

    var du_replace_content = 'off';

    if (jQuery('#du-et_pb_load_layout_replace').attr("checked") == 'checked') {
        du_replace_content = 'on';
    }

    if (!just_import) {
        jQuery(".du-content").html('Loading your layout. Please wait...');
    }

    du_show_spinner();

    jQuery.ajax({
        url: du_url_request,
        success: function (response) {
            du_hide_spinner();

            var response_obj = jQuery.parseJSON(response);

            if (response_obj.error_success) {
                if (just_import) {
                    if (own_layout) {
                        jQuery('.du-my-cloud-item-' + layout_id).addClass('import-complete');
                        jQuery('.du-my-cloud-item-' + layout_id + ' .et_pb_layout_button_load').text('Import Complete!').attr('onclick', '');
                    } else {
                        jQuery('.du-layout-item-' + layout_id).addClass('import-complete');
                        jQuery('.du-layout-item-' + layout_id + ' .actions .apply-layout').html('Import Complete!').attr('onclick', '');
                    }

                } else if (response_obj.content.layout_type == "layout") {
                    var the_list = jQuery('.et-pb-all-modules-tab .et-pb-load-layouts');
                    var new_li = the_list.children('li').last().clone(true);

                    new_li.css('background-color', 'red');
                    new_li.addClass('du_new_layout');
                    new_li.appendTo(the_list);

                    jQuery('.du_new_layout').data('layout_id', {layout: response_obj.content.import_id, replace_content: du_replace_content} );
                    jQuery('.du_new_layout .et_pb_layout_button_load').click();

                } else if (response_obj.content.layout_type == "section") {

                    jQuery(".du-content").html('<h2>Section imported!</h2><p>Unfortunately you can not yet use this section directly. Please first save this page and click "Add from Library" again to see your section called "<strong>' + response_obj.content.import_name + '</strong>"</p>');

                    //setTimeout(function(){
                    //var the_list = jQuery('.et-pb-saved-modules-tab .et_pb_saved_layouts_list');
                    //var new_li = the_list.children('li').last().clone(true);

                    //new_li.css('background-color', 'red');
                    //new_li.addClass('du_new_layout');
                    //new_li.appendTo(the_list);

                    //jQuery('.du_new_layout').click();
                    //}, 3000);

                    //jQuery('.et-pb-saved-module a').click();
                    //var new_li = the_list.children('li').last().clone(true);

                } else if (response_obj.content.layout_type == "module") {
                    jQuery(".du-content").html('<h2>Module imported!</h2><p>Unfortunately you can not yet use this module directly. Please first save this page and click "Add from Library" again to see your module called "<strong>' + response_obj.content.import_name + '</strong>"</p>');

                } else {
                    jQuery(".du-content").html("Unknown layout type: '" + response_obj.layout_type + "'");
                }
            } else {
                if (response_obj.error) {
                    jQuery(".du-content").html(response_obj.error);
                } else if (response_obj.content) {
                    jQuery(".du-content").html(response_obj.content);
                } else {
                    jQuery(".du-content").html('Unknown error.... Please try again or contact Layouts Cloud for support.');
                }

            }
        },
        timeout: 90000 //in milliseconds
    });

    //jQuery.get(du_url_request, function(response) {
    //});
}

function du_delete_layout(layout_id) {

    if (confirm("Are you sure you want to delete this layout? You will not be able to recover it once it has been removed from our system.")) {
        var du_url_request = du_site_url + "?du_action=delete-own-layout&du_layout_id=" + layout_id;

        du_url_request += '&du_req_date=' + Date.now();

        du_show_spinner();

        jQuery.ajax({
            url: du_url_request,
            success: function (response) {
                du_hide_spinner();

                var response_obj = jQuery.parseJSON(response);

                if (response_obj.error_success) {
                    du_my_cloud();
                } else {
                    alert(response_obj.error);
                }
            },
            timeout: 90000 //in milliseconds
        });
    }

}

function du_save_section_to_cloud() {
    //var module_cid = cid_or_element.model.get( 'cid' );
    //alert(ET_PageBuilder_App.generateCompleteShortcode( module_cid, 'section', 0, 0));
    //alert(ET_PageBuilder_App.generateCompleteShortcode( this, 'section', 0, 0));

    //console.log(ET_PageBuilder.get(cid));
    alert("Sorry but this function is not yet complete. We suggest that you remove the sections you don't want then save the entire layout to the library or the cloud for the moment. Sorry for any inconvenience.");
}

function du_save_to_cloud() {

    var textarea_id = 'content';
    var fix_shortcodes = true;
    var content;
    var layout_name = jQuery('#et_pb_new_layout_name').val();
    var layout_type = 'layout'; //@todo: make dynamic when called for sections and modules

    if (layout_name == 'undefined' || !layout_name) {
        return false; //we need a name
    }

    if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get(textarea_id) && !window.tinyMCE.get(textarea_id).isHidden()) {
        content = window.tinyMCE.get(textarea_id).getContent();
    } else {
        content = jQuery('#' + textarea_id).val();
    }

    if (fix_shortcodes && typeof window.tinyMCE !== 'undefined') {
        content = content.replace(/<p>\[/g, '[');
        content = content.replace(/\]<\/p>/g, ']');
    }

    content = content.trim();
    content = encodeURIComponent(content);

    var du_url_request = du_site_url + "?du_action=save-layout&du_layout_type=" + layout_type + "&du_layout_name=" + layout_name;

    du_url_request += '&du_req_date=' + Date.now();

    du_show_spinner();

    jQuery.ajax({
        type: 'POST',
        url: du_url_request,
        data: 'du_layout_content=' + content,
        timeout: 90000, // in milliseconds
        success: function (data) {
            var response_obj = jQuery.parseJSON(data);

            du_hide_spinner();

            if (response_obj.error_success) {
                jQuery('.et-pb-modal-close').click();
            } else {
                alert(response_obj.error);
            }
        }
    });

    return true;
}

function du_what_community_cloud() {
    alert("Sometimes when you make something so good it just has to be shared with others! The community cloud is our name for layouts submitted by you and others in the community. This lets you save your own layouts to our servers. The Community Cloud lets you share your work with the community so that they can use them also. You will of course, see your name in the directory of community cloud submissions next to each of your layouts.");
}

function du_form_submit(e) {
    if (e.keyCode == 13) {
        du_populate_frame();
        return false;
    }

    return e;
}

function du_preview_layout(layout_id, layout_name, layout_iframe, has_access, import_only, navbar) {

    if (layout_iframe == '') {
        layout_iframe = 'https://www.layoutscloud.com/?p=' + layout_id;
    }

    jQuery.colorbox({iframe: true, href: layout_iframe, title: layout_name, width: "85%", height: "85%"});
    if (has_access) {
        if (navbar) {
            jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a onclick="du_apply_nav(\'' + layout_id + '\');">Apply Navigation Bar</a></div>');
        } else if (import_only) {
            jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a onclick="du_apply_layout(\'' + layout_id + '\', 0, 1);">Add to Library</a></div>');
        } else {
            jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a onclick="du_apply_layout(\'' + layout_id + '\');">Apply Layout</a></div>');
        }
    } else {
        jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a href="https://www.layoutscloud.com">Upgrade to get this layout</a></div>');
    }
}

function du_preview_own_layout(layout_id, layout_name, layout_iframe, has_access, import_only, navbar) {

    if (layout_iframe == '') {
        layout_iframe = 'https://www.layoutscloud.com/?p=' + layout_id + '&du_api_key=' + du_api_key;
    } else {
        layout_iframe = layout_iframe + '?du_api_key=' + du_api_key;
    }

    jQuery.colorbox({iframe: true, href: layout_iframe, title: layout_name, width: "85%", height: "85%"});
    if (has_access) {
        if (navbar) {
            jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a onclick="du_apply_nav(\'' + layout_id + '\');">Apply Navigation Bar</a></div>');
        } else if (import_only) {
            jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a onclick="du_apply_layout(\'' + layout_id + '\', 0, 1);">Add to Library</a></div>');
        } else {
            jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a onclick="du_apply_layout(\'' + layout_id + '\');">Apply Layout</a></div>');
        }
    } else {
        jQuery('#cboxLoadedContent').append('<div class="du_iframe_apply_layout"><a href="https://www.layoutscloud.com">Upgrade to get this layout</a></div>');
    }

    //var iframe_url = layout_id + '?du_api_key=' + du_api_key;
    //jQuery.colorbox({iframe: true, href: iframe_url, title: "Cloud Layout Preview", width: "85%", height: "85%"});
    //jQuery('#cboxLoadedContent').append('<div class="du_iframe_preview_own_layout"><span>This is just to give you an idea of what it may look like and is not intended to match exactly</span></div>');
}

function du_submit_cc(layout_id) {
    var message = 'You are about to submit your layout to the Community Cloud. By pressing OK below you are agreeing to our terms and conditions. We manually approve all layouts so it will not immediately appear in the directory. Don\'t worry though, we do review submissions regularly! We may rename the layout to be more descriptive/unique also. Your name will show against the layout and you will have full credit in our directory.';

    if (confirm(message)) {
        var du_url_request = du_site_url + "?du_action=save-to-cc&du_layout_id=" + layout_id;

        du_url_request += '&du_req_date=' + Date.now();

        du_show_spinner();

        jQuery.ajax({
            type: 'GET',
            url: du_url_request,
            timeout: 90000, // in milliseconds
            success: function (data) {
                var response_obj = jQuery.parseJSON(data);

                du_hide_spinner();

                du_my_cloud(); //load the my cloud interface

                if (response_obj.error_success) {
                    alert('Your layout was successfully submitted to the Community Cloud. Someone will approve it as soon as possible and it will appear on the system. In the meantime you can still access it in your own "My Cloud" page as normal.');
                } else {
                    alert(response_obj.error);
                }
            }
        });
    }

    return true;
}

function du_populate_frame(page_number) {

    if (page_number === undefined) {
        var page_number = 1;
    }

    ////// build the call using ajax to get the content of the frame
    var layout_type = jQuery('.du').data('layout_type');
    var du_view = 'recent'; //this could be recent, popular, mine, alpha
    var du_url_request = du_site_url + '?du_action=get-layouts&du_type=' + layout_type;
    var du_cats = [];
    var du_sets = [];
    var du_search = jQuery('.du-search-filter').val();
    var du_avail_me = 0;
    var du_context = 'builder';
    var du_source = 'cloud';
    var du_pp = 36;

    if (layout_type == "snippet") {
        var du_url_request = du_site_url + '?du_action=get-snippets';
    }

    if (jQuery('.du-tab').parent().hasClass('du-gallery')) {
        du_context = 'gallery';
    }

    jQuery('.du-categories input[type=checkbox]').each(function () {
        if (jQuery(this).is(':checked')) {
            du_cats.push(jQuery(this).attr('value'));
        }
    });

    jQuery('.du-sets input[type=checkbox]').each(function () {
        if (jQuery(this).is(':checked')) {
            du_sets.push(jQuery(this).attr('value'));
        }
    });

    du_url_request += '&du_paged=' + page_number;

    //if (layout_type == 'layout' && du_context == 'gallery') {
    //du_pp = 32;
    //}

    du_url_request += '&du_pp=' + du_pp;

    if (du_cats.length > 0) {
        du_url_request += '&du_cats=' + encodeURIComponent(du_cats);
        du_view = 'filtered';
    }

    if (du_sets.length > 0) {
        du_url_request += '&du_sets=' + encodeURIComponent(du_sets);
        du_view = 'filtered';
    }

    if (du_search) {
        du_url_request += '&du_kw=' + encodeURIComponent(du_search);
        du_view = 'filtered';
    }

    if (jQuery('.du-layout.du-community').is(':checked')) {
        du_source = 'community';
    }

    if (jQuery('.du-avail-me').is(':checked')) {
        du_url_request += '&du_avm=1';
        du_view = 'filtered';
        du_avail_me = 1;
    }

    if (jQuery('.du-favs').is(':checked')) {
        du_url_request += '&du_favs=1';
        du_view = 'filtered';
        var du_favs = 1;
    }

    du_url_request += '&du_source=' + du_source;
    du_url_request += '&du_view=' + du_view;
    du_url_request += '&du_context=' + du_context;
    du_url_request += '&du_req_date=' + Date.now();

    //////start the call and output the code/html
    du_show_spinner();

    var du_tab_container = '<div class="du-content"><p>Loading. One moment please...</p></div> \
													<div style="display: none;"> \
														<div class="du-iframe-container">	\
														</div>	\
													</div>';
    jQuery(".du-tab").html(du_tab_container);

    jQuery.ajax({
        url: du_url_request,
        success: function (response) {
            du_hide_spinner();

            jQuery(".du-content").html(response);

            //repopulate the search results
            if (du_search) {
                jQuery('.du-search-filter').val(du_search);
            }

            if (du_cats.length > 0) {
                jQuery.each(du_cats, function (index, value) {
                    jQuery('.du-category-' + value).attr('checked', 'checked');
                });
            }

            if (du_sets.length > 0) {
                jQuery.each(du_sets, function (index, value) {
                    jQuery('.du-set-' + value).attr('checked', 'checked');
                });
            }

            jQuery('.du-layout.du-' + du_source).attr('checked', 'checked');

            if (du_avail_me == 1) {
                jQuery('.du-avail-me').attr('checked', 'checked');
            }
            if (du_favs == 1) {
                jQuery('.du-favs').attr('checked', 'checked');
            }

            du_reinstate_actions();
        },
        timeout: 90000 //in milliseconds
    });

}

function du_toggle_favourite(layout_id) {
    var favourite = jQuery('.du-layout-item-' + layout_id + ' .add-to-favourites .dashicons');
    var action = 'add-favourite';

    if (favourite.hasClass('favourited')) {
        action = 'remove-favourite';
    }

    ////// build the call using ajax to get the content of the frame

    var du_url_request = du_site_url + '?du_action=' + action + '&du_layout_id=' + layout_id;

    du_url_request += '&du_req_date=' + Date.now();

    //////start the call and output the code/html

    du_show_spinner();

    jQuery.ajax({
        url: du_url_request,
        success: function (response) {
            du_hide_spinner();

            if (favourite.hasClass('favourited')) {
                favourite.removeClass('favourited'); //remove by default
            } else {
                favourite.addClass('favourited');
            }

            //jQuery(".du-content").html(response);
        },
        timeout: 90000 //in milliseconds
    });

}

function du_filter_own_cloud(filter_name) {
    jQuery(".du-filter-user-cat-selected").removeClass('du-filter-user-cat-selected');
    jQuery(".du-filter-user-cat-" + filter_name).addClass('du-filter-user-cat-selected');
    jQuery(".du-user-cat-all").slideUp();
    jQuery(".du-user-cat-" + filter_name).slideDown();
    jQuery(".du-user-category-filters").slideUp();
}

function du_create_new_user_category() {
    var new_category = jQuery('.du-new-category-text').val();

    if (new_category.length > 0) {
        //sanitize/strip html etc
        new_category = new_category.replace(/(<([^>]+)>)/ig, "");

        //is already in list?
        if (jQuery.inArray(new_category, du_current_cats) !== -1) {
            alert("It looks like you already have a category with that name. Please enter a different category name and retry");
        } else {
            //send api call
            var du_url_request = du_site_url + '?du_action=add-user-cloud-category&du_category_name=' + new_category;

            du_url_request += '&du_req_date=' + Date.now();

            du_show_spinner();

            jQuery.ajax({
                url: du_url_request,
                success: function (response) {
                    du_hide_spinner();

                    var response_obj = jQuery.parseJSON(response);

                    if (response_obj.error_success) {
                        //reload my_cloud window to then include category
                        du_my_cloud();
                    } else {
                        alert(response_obj.error);
                    }

                },
                timeout: 90000 //in milliseconds
            });
        }
    } else {
        alert("You should probably type something into the category name box before submitting the form. Please type a category name to add and then retry");
    }

}

function du_assign_user_categories(layout_id) {
    var categories = jQuery('.du-my-cloud-item-' + layout_id + ' .du-assign-user-cat:checked').map(function () {
        return this.value;
    }).get();

    console.log(categories);

    //send api call
    var du_url_request = du_site_url + '?du_action=assign-user-cloud-category&du_layout_id=' + layout_id + '&du_categories=' + categories;

    du_url_request += '&du_req_date=' + Date.now();

    du_show_spinner();

    jQuery.ajax({
        url: du_url_request,
        success: function (response) {
            du_hide_spinner();

            var response_obj = jQuery.parseJSON(response);

            if (response_obj.error_success) {
                //reload my_cloud window to then include category
                du_my_cloud();
            } else {
                alert(response_obj.error);
            }

        },
        timeout: 90000 //in milliseconds
    });
}

function du_my_cloud() {

    ////// build the call using ajax to get the content of the frame
    var layout_type = jQuery('.du').data('layout_type');
    var du_url_request = du_site_url + '?du_action=get-my-cloud&du_type=' + layout_type;
    var du_context = 'builder';

    if (jQuery('.du-tab').parent().hasClass('du-gallery')) {
        du_context = 'gallery';
    }

    du_url_request += '&du_context=' + du_context;
    du_url_request += '&du_req_date=' + Date.now();

    //////start the call and output the code/html
    du_show_spinner();

    var du_tab_container = '<div class="du-content"><p>Loading. One moment please...</p></div> \
													<div style="display: none;"> \
														<div class="du-iframe-container">	\
														</div>	\
													</div>';
    jQuery(".du-tab").html(du_tab_container);
    jQuery(".du-content").html('<p>Loading. One moment please...</p>');

    jQuery.ajax({
        url: du_url_request,
        success: function (response) {
            du_hide_spinner();
            jQuery(".du-content").html(response);

            du_reinstate_actions();
        },
        timeout: 90000 //in milliseconds
    });

}

function du_reinstate_actions() {
    jQuery('.filter-title').click(function () {
        jQuery(this).parent().children('.du-category-filters').slideToggle();
    });
}

jQuery(document).ready(function ($) {

    if (du_api_key_present) {

        // Save to cloud
        $(document).on('mouseup', '.et-pb-layout-buttons-save', function () {
            setTimeout(function () {

                var submit = $('.et_pb_prompt_buttons');

                submit.addClass('du-buttons-present');

                if (submit.length) {
                    submit.append('<input type="button" onclick="du_save_to_cloud();" class="et_pb_prompt_proceed dus-to-cloud" value="Save to ' + du_constants.plugin_name + '">');
                }

            }, 200);
        });

        // Save section to cloud
        $(document).on('mouseup', '.et-pb-modal-save-template', function () {
            setTimeout(function () {

                var submit = $('.et_pb_prompt_buttons');

                submit.addClass('du-buttons-present du-popup-buttons-present');

                if (submit.length) {
                    submit.append('<input type="button" onclick="du_save_section_to_cloud();" class="et_pb_prompt_proceed dus-to-cloud" value="Save to ' + du_constants.plugin_name + '">');
                }

            }, 200);
        });

        // Inserting fullwidth module
        $(document).on('mouseup', '.et_pb_fullwidth_sortable_area .et-pb-insert-module', function () {
            setTimeout(function () {

                var tabbar = $('.et-pb-saved-modules-switcher');
                if (tabbar.length) {
                    tabbar.append('<li class="du du_fullwidth" data-open_tab="du-tab" data-layout_type="module"><a href="#">' + du_constants.plugin_name + '</a></li>');
                    $(".et_pb_modal_settings").append('<div class="et-pb-main-settings et-pb-main-settings-full du-tab du-tab-module"></div>');
                }

            }, 200);
        });

        // Inserting standard module
        $(document).on('mouseup', '.et-pb-column .et-pb-insert-module', function () {
            setTimeout(function () {

                var tabbar = $('.et-pb-saved-modules-switcher');
                if (tabbar.length) {
                    tabbar.append('<li class="du" data-open_tab="du-tab" data-layout_type="module"><a href="#">' + du_constants.plugin_name + '</a></li>');
                    $(".et_pb_modal_settings").append('<div class="et-pb-main-settings et-pb-main-settings-full du-tab du-tab-module"></div>');
                }

            }, 200);
        });

        // Insert layout from library
        $(document).on('mouseup', '.et-pb-layout-buttons-load', function () {
            setTimeout(function () {

                var tabbar = $('.et-pb-saved-modules-switcher');
                if (tabbar.length) {
                    tabbar.append('<li class="du" data-open_tab="du-tab" data-layout_type="layout"><a href="#">' + du_constants.plugin_name + '</a></li>');
                    $(".et_pb_modal_settings").append('<div class="et-pb-main-settings et-pb-main-settings-full du-tab du-tab-layout"></div>');
                }

            }, 200);
        });

        // Insert section from library
        $(document).on('mouseup', '.et-pb-section-add-saved', function () {
            setTimeout(function () {

                jQuery('.et_pb_modal_settings.et_pb_modal_no_tabs').removeClass('et_pb_modal_no_tabs');

                jQuery('.et_pb_modal_settings_container h3').after(' \
					<ul class="et-pb-options-tabs-links et-pb-saved-modules-switcher">	\
						<li class="et-pb-saved-module" data-open_tab="et-pb-saved-modules-tab">	\
							<a href="#">Add From Library</a>	\
						</li>	\
						<li class="du" data-open_tab="du-tab" data-layout_type="section"><a href="#">' + du_constants.plugin_name + '</a></li>	\
					</ul>	\
					<div class="et-pb-main-settings et-pb-main-settings-full du-tab du-tab-section"></div>	\
				');

            }, 200);
        });

        $(document).on('click', '.et_pb_modal_settings_container .du', function () {
            $('.du-tab').css('top', $('.et-pb-all-modules-tab').css('top'));

            var requested_type = jQuery(this).data('layout_type');

            if (du_constants.default_view == 'my-cloud' && requested_type == 'layout') {
                du_my_cloud();
            } else {
                du_populate_frame();
            }

        });

        $(document).on('click', '.du-gallery .du', function () {
            $('.du-tab').css('top', $('.et-pb-all-modules-tab').css('top'));

            du_populate_frame();
        });

        // Restore spinner behaviour when tab switched
        jQuery(document).on('click', '.et-pb-saved-modules-switcher li:not(.du)', function () {
            jQuery('#et_pb_loading_animation').css({'z-index': '999999'}).hide();
        });

    }

    jQuery('.du-layout-type-filter').click(function () {
        var requested_type = jQuery(this).data('layout_type');

        jQuery('.du-layout-type-filter.active').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('.du').data('layout_type', requested_type).click();
    });

});