<?php
/*
Plugin Name: Divi Cloud
Plugin URI: https://www.layoutscloud.com/
Description: This plugin gives your Divi site a development supercharge with access to free and premium layouts listed on the Layouts Cloud website.
Author: Elegant Marketplace
Author URI: https://www.elegantmarketplace.com/
Version: 1.9.5

Changelog:

V1.0 - 8/3/17
- initial proof of concept

V1.14 - 5/4/17
- More features...

V1.15 - 19/4/17
- Added helpful error messages for debugging purposes. Also added an initial check for cURL SSL to avoid confusion at setup

V1.2 - 28/4/17
- Added gallery view to Divi Cloud near the divi icon in the admin area allowing direct download to library
- Included modules and sections in the gallery view for import
- Fixed bug in personal cloud whereby some layouts didn't completely send and therefore corrupted
- Stability improvements
- CSS changes to make using the plugin easier and more enjoyable, A whole new layout for the library selection
- Ability to save favourites and filter by them
- Ability to delete own layouts from the cloud
- Ability to preview own layouts from the cloud
- Added auto update to facilitate delivery of future versions
- Assigned category to builder layouts to make sorting/filtering easier

V1.3 - 3/6/17
- Fixed issue effecting a few servers whereby importing a personal cloud item returned an 'unknown error'. Reinitialised the $return array to fix.

V1.4 - 20/6/17
- Added Community Cloud settings and saving options
- Hit return to search instead of having to press the search button

V1.4.1 - 25/6/17
- Fixed community cloud load from library link
- Added EMP advert

V1.5 - 28/6/17
- Fixed a issue with the auto-update system

V1.6 - 2/8/17
- Added pagination to layout lists for easier and faster navigation
- Added 'replace content' checkbox so you can optionally append your layouts to the builder
- Allowed for categorisation of 'my cloud' layouts for easier archiving and recall

V1.7 - 25/9/17
- Removed superfluous 'replace content' box from within gallery view
- CSS changes
- Added basic white labelling functionality
- Layout Sets can now be filtered on within the Divi Cloud interface
- Added new snippet system for feeelancer/agency users
- Added new Navigation Bar templates within the Divi Cloud gallery view

V1.8 - 4/12/17
- Added images for own cloud items.
- Laid out ‘my cloud’ page to include images in backgrounds
- Added images for community cloud items
- Updated colorbox to the latest version / fixed CSS relating to this
- Made the categories on ‘my cloud’ interface easier when you have no categories yet
- Integrated intercom into the settings pages
- Added entire site skinning using a complete customiser import. includes nav bars, fonts, sizes, etc..
- Obfuscated API key
- White labelling now supports constraining “my cloud” to certain categories. This could be used whereby you may have categories for client/project names and assign them on the sites where you use DC in white labelling mode
- Added white label option to hide community cloud
- Added default view setting so that ‘my cloud’ can be shown first when hitting the Divi Cloud button
- Added pagination to top as well as of the layout grid
- Added helper ‘one moment please…’ notification to the gallery view to reassure the user that something is actually happening
- Added ability to hide the Layouts Cloud layouts leaving only ‘my cloud’ content
- Widened dialogue box for loading layouts to make layout prettier

V1.9 - 30/01/18
- added ?dc_conn_test={api_key} parameter for connectivity debugging purposes. Allows us to diagnose bvad connections between the DU server and the customers for faster support
- Added cache buster code to the js file to prevent any delay when requesting new layouts, snippets and nav bars.
- Changed the URL of the API from diviunited.com to layoutscloud.com due to the change in domain name with the company
- Changed all occurrences of 1divi.com to 2divi.com

V1.9.1 - 01/02/18
- Fixed scrolling in the latest version of Divi
- A bit of a facelift to remove some shadows and rounded corners so the design is more inkeeping with the new layouts system
- Removed 'replace content' checkbox as no longer supported by Divi

V1.9.2 - 13/02/18
- Fixed "my cloud" previews

V1.9.3 - 22/02/18
- Put back 'replace content' and hooked it up in the new way as it was re-added to Divi in the latest version
- Fixed custom width of the save section, row, module overlay as it was smaller before

V1.9.4 - 21/03/18
- Fixed community cloud styling (my CC)
- Replaced all references of divi united for layouts cloud

v1.9.5 - 5/6/18
- Finished replacing the branding
- Removed Site Skins and Navbars due to refocus
- Added admin sidebar link to 'my cloud'

v1.9.6 - 5/11/18
- Removed live chat as we have swapped systems and they don't have a direct integration
- New colour scheme

*/

//ini_set('display_errors', 'on');
//error_reporting(E_ERROR);

define( 'DU_VERSION', '1.9.6' );

$du_remote_site = 'http://layoutscloud.com/';
//$du_remote_site = 'http://wp.dev/';

add_action( 'plugins_loaded', 'du_init' );

function du_init() {

	du_get_constants();

	//add_action('wp_enqueue_scripts', 'du_enqueue_scripts');
	add_action( 'admin_enqueue_scripts', 'du_admin_general_scripts' );
	add_action( 'admin_print_scripts-post-new.php', 'du_admin_scripts' );
	add_action( 'admin_print_scripts-post.php', 'du_admin_scripts' );
	add_action( 'admin_print_scripts', 'du_conditional_admin_scripts' );
	add_action( 'admin_menu', 'du_menu' );
	//add_action( 'admin_head', 'du_admin_head' );
	add_action( 'admin_notices', 'du_enter_key' );

	add_filter( 'pre_set_site_transient_update_plugins', 'du_check_update' );
	add_filter( 'plugin_row_meta', 'du_plugin_row_meta', 10, 4 );
	add_filter( 'all_plugins', 'du_all_plugins', 10, 4 );
	add_action( 'admin_init', 'du_show_changelog' );
	add_action( 'admin_bar_menu', 'du_toolbar_links', 999 );

	add_action( 'init', 'du_handle_api' );

}

function du_admin_head() {
	if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], array( 'du_admin_home', 'layouts_cloud' ) ) ) {
		return;
	}

}

function du_toolbar_links( $wp_admin_bar ) {
	$url_root = admin_url( 'admin.php?page=du_admin_home' );
	$types    = array(
		'layout'  => 'Layouts',
		'section' => 'Sections',
		'module'  => 'Modules',
		//'navigation-bar' => 'Navigation Bars',
		//'skin' => 'Site Skins',
		'snippet' => 'Code/Text Snippets',
	);

	$args = array(
		'id'    => 'divi-cloud',
		'title' => DU_NAME,
		'href'  => $url_root,
		'meta'  => array(
			'class' => 'divi-cloud',
			'title' => DU_NAME . ' Gallery View'
		)
	);
	$wp_admin_bar->add_node( $args );

	foreach ( $types as $name => $label ) {
		$args = array(
			'id'     => 'divi-cloud-' . $name,
			'title'  => $label,
			'href'   => $url_root . '&layout_type=' . $name,
			'parent' => 'divi-cloud'
		);
		$wp_admin_bar->add_node( $args );
	}

}

function du_all_plugins( $plugins ) {
	//print_r($plugins['divi_cloud/divi_cloud.php']);

	$plugins['divi_cloud/divi_cloud.php']['Name']      = DU_NAME;
	$plugins['divi_cloud/divi_cloud.php']['Title']     = DU_NAME;
	$plugins['divi_cloud/divi_cloud.php']['Author']    = DU_AUTHOR;
	$plugins['divi_cloud/divi_cloud.php']['AuthorURI'] = DU_AUTHOR_URL;
	$plugins['divi_cloud/divi_cloud.php']['PluginURI'] = DU_URL;

	if ( DU_DESC ) {
		$plugins['divi_cloud/divi_cloud.php']['Description'] = DU_DESC;
	}

	return $plugins;
}

function du_save_section() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		die( - 1 );
	}

	if ( empty( $_POST['et_layout_name'] ) ) {
		die();
	}

	$args = array(
		'layout_type'    => isset( $_POST['et_layout_type'] ) ? sanitize_text_field( $_POST['et_layout_type'] ) : 'layout',
		'layout_new_cat' => isset( $_POST['et_layout_new_cat'] ) ? sanitize_text_field( $_POST['et_layout_new_cat'] ) : '',
		'columns_layout' => isset( $_POST['et_columns_layout'] ) ? sanitize_text_field( $_POST['et_columns_layout'] ) : '0',
		'module_type'    => isset( $_POST['et_module_type'] ) ? sanitize_text_field( $_POST['et_module_type'] ) : 'et_pb_unknown',
		'layout_content' => isset( $_POST['et_layout_content'] ) ? $_POST['et_layout_content'] : '',
		'layout_name'    => isset( $_POST['et_layout_name'] ) ? sanitize_text_field( $_POST['et_layout_name'] ) : '',
	);

	//print_r($args);

	//$new_layout_meta = et_pb_submit_layout( $args );
	//die( $new_layout_meta );
}

add_action( 'wp_ajax_et_pb_save_layout', 'du_save_section', 1, 1 );

function du_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
	if ( $plugin_file == 'divi_cloud/divi_cloud.php' ) {
		$plugin_meta[1] = 'By <a href="' . DU_AUTHOR_URL . '" target="_blank">' . DU_AUTHOR . '</a>';
	}

	return $plugin_meta;
}

function du_get_constants() {
	$default_view      = 'layouts';
	$plugin_name       = 'Divi Cloud';
	$plugin_url        = 'https://layoutscloud.com';
	$plugin_author     = 'Elegant Marketplace';
	$plugin_author_url = 'https://www.elegantmarketplace.com';
	$plugin_desc       = ''; //intentionally blank

	if ( $white_label = get_option( 'du_white_labelling', array() ) ) {
		if ( isset( $white_label['plugin_name'] ) && $white_label['plugin_name'] ) {
			$plugin_name = $white_label['plugin_name'];
		}
		if ( isset( $white_label['plugin_url'] ) && $white_label['plugin_url'] ) {
			$plugin_url = $white_label['plugin_url'];
		}
		if ( isset( $white_label['plugin_author'] ) && $white_label['plugin_author'] ) {
			$plugin_author = $white_label['plugin_author'];
		}
		if ( isset( $white_label['plugin_author_url'] ) && $white_label['plugin_author_url'] ) {
			$plugin_author_url = $white_label['plugin_author_url'];
		}
		if ( isset( $white_label['plugin_desc'] ) && $white_label['plugin_desc'] ) {
			$plugin_desc = $white_label['plugin_desc'];
		}
	}

	if ( $du_settings = get_option( 'du_settings', array() ) ) {
		if ( isset( $du_settings['default_view'] ) && $du_settings['default_view'] ) {
			$default_view = $du_settings['default_view'];
		}
	}

	define( 'DU_NAME', $plugin_name );
	define( 'DU_URL', $plugin_url );
	define( 'DU_DESC', $plugin_desc );
	define( 'DU_AUTHOR', $plugin_author );
	define( 'DU_AUTHOR_URL', $plugin_author_url );
	define( 'DU_DEFAULT_VIEW', $default_view );
}

function du_show_changelog() {

	if ( empty( $_REQUEST['plugin'] ) ) {
		return;
	}

	if ( empty( $_REQUEST['tab'] ) ) {
		return;
	}

	if ( $_REQUEST['plugin'] == 'divi-cloud' ) {
		echo '<div style="background:#fff; padding:20px;">';

		if ( $version_info = du_get_latest_version_details() ) {
			echo wpautop( $version_info['changelog'] );
		}

		echo '</div>';
		exit;
	}
}

function du_check_update( $transient ) {
	//echo '<pre>';
	//print_r($transient);
	//echo '</pre>';

	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$plugin_slug = 'divi-cloud';
	$plugin      = 'divi_cloud/divi_cloud.php';

	if ( $version_info = du_get_latest_version_details() ) {
		if ( version_compare( DU_VERSION, $version_info['version'] ) ) {
			$obj                 = new stdClass();
			$obj->slug           = $plugin_slug;
			$obj->plugin         = $plugin;
			$obj->new_version    = $version_info['version'];
			$obj->url            = 'https://www.layoutscloud.com';
			$obj->package        = $version_info['file_url'];
			$obj->upgrade_notice = nl2br( wpautop( $version_info['changelog'] ) );

			$transient->response[ $plugin ] = $obj;
		}
	}

	return $transient;
}

function du_get_latest_version_details() {
	global $du_remote_site;

	$key = get_option( 'du_api_key' );
	$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=check-version';
	$rt  = false;

	if ( $return = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
		if ( ! is_wp_error( $return ) ) {
			if ( $return['body'] ) {
				if ( ! isset( $return['error'] ) || ! $return['error'] ) {
					if ( $body = json_decode( $return['body'], true ) ) {
						if ( $body['content'] ) {
							$rt = $body['content'];
						}
					}
				}
			}
		}
	}

	return $rt;
}

function du_enter_key() {
	if ( ! get_option( 'du_api_key' ) ) {
		echo '<div class="notice notice-error">
						<p>Congratulations on activating ' . DU_NAME . '! Your development process will never be the same again. Please visit the <a href="' . admin_url( 'admin.php?page=layouts_cloud' ) . '">settings page</a> and enter your API Key to get started</p>
					</div>';
	}
}

function du_backdate_1d_urls() {
	if ( ! get_option( 'du_1divi_backdate_run' ) ) {
		global $wpdb;

		$sql = 'UPDATE ' . $wpdb->posts . ' 
                SET post_content = REPLACE(post_content, "1divi.com", "2divi.com")';
		$wpdb->query( $sql );

		$sql = 'UPDATE ' . $wpdb->postmeta . ' 
                SET meta_value = REPLACE(meta_value, "1divi.com", "2divi.com")';
		$wpdb->query( $sql );

		update_option( 'du_1divi_backdate_run', time() ); //run once ever!
	}
}

function du_handle_api() {
	global $du_remote_site;

	du_backdate_1d_urls(); //swapping 1divi.com to 2divi.com

	if ( isset( $_GET['dc_conn_test'] ) ) {
		$_GET['du_action'] = 'conntest';
	}

	if ( isset( $_GET['du_action'] ) ) {

		$action = $_GET['du_action'];
		$key    = get_option( 'du_api_key' );

		$context = 'builder';
		if ( isset( $_GET['du_context'] ) ) {
			$context = $_GET['du_context'];
		}

		ini_set( 'display_errors', 'on' );
		error_reporting( E_ERROR );

		$return = '';

		switch ( $action ) {
			case 'conntest':
				if ( $_GET['dc_conn_test'] == $key ) {
					$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=' . $action;

					if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
						echo '<pre>';
						print_r( $remote );
						echo '</pre>';
					}
				} else {
					echo 'Bad Key!';
				}

				die; //die here because we are simply doing a conn test and not using the API
			case 'add-user-cloud-category':
				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( isset( $_GET['du_category_name'] ) && $_GET['du_category_name'] ) {

					$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=' . $action . '&du_category_name=' . $_GET['du_category_name'];

					if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
						if ( ! is_wp_error( $remote ) ) {
							if ( $return = json_decode( $remote['body'], true ) ) {
								if ( ! isset( $return['error'] ) || ! $return['error'] ) {
									$return['content'] = 1;
									//$return['debug'] = $url;
								} else {
									//there was an error. the text is contained inside the array and returned to the calling function
								}
							}
						} else {
							$return['error'] = 'Could not connect to the Layouts Cloud server (AUCC) - ' . $remote->get_error_message();
							$return['debug'] = false;
						}
					}

				} else {
					$return['error'] = '<p>No category name was passed.</p>';
				}

				$return = json_encode( $return );

				break;
			case 'assign-user-cloud-category':
				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( isset( $_GET['du_categories'] ) && isset( $_GET['du_layout_id'] ) ) {

					$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=' . $action . '&du_categories=' . $_GET['du_categories'] . '&du_layout_id=' . $_GET['du_layout_id'];
					if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
						if ( ! is_wp_error( $remote ) ) {
							if ( $return = json_decode( $remote['body'], true ) ) {
								if ( ! isset( $return['error'] ) || ! $return['error'] ) {
									$return['content'] = 1;
								} else {
									//there was an error. the text is contained inside the array and returned to the calling function
								}
							}
						} else {
							$return['error'] = 'Could not connect to the Layouts Cloud server (ASUCC) - ' . $remote->get_error_message();
							$return['debug'] = false;
						}
					}

				} else {
					$return['error'] = '<p>No category or layout id was set/passed.</p>';
				}

				$return = json_encode( $return );

				break;
			case 'get-layouts':

				$white_label = get_option( 'du_white_labelling', array() );

				if ( $type = $_GET['du_type'] ) {

					$source = 'cloud';
					if ( isset( $_GET['du_source'] ) ) {
						$source = $_GET['du_source'];
					}

					if ( $layouts = du_get_layouts( $type ) ) {
						//if ($layouts['error_success'] && $layouts['content']) {
						//$favourites = $layouts['user']['favourites'];

						$return = '';
						//$return .= '<div style="display: none;">' . print_r($layouts, true) . '</div>';
						//$return .= '<div>' . print_r($layouts, true) . '</div>';

						if ( $context == 'builder' ) {
							$return .= '<div class="du-replace"><label for="du-et_pb_load_layout_replace">
			                            <input type="checkbox" id="du-et_pb_load_layout_replace" class="du-et_pb_load_layout_replace" checked="checked">&nbsp;Replace the existing content with loaded layout
                                    </label>';
							$return .= '</div>';
						}

						$return .= '<div class="du-emp-plug"><a href="https://elegantmarketplace.com/" target="_blank" class="emp-link">Can\'t find what you need? Try Elegant Marketplace</a></div>';

						$return .= '<div class="du-clear">&nbsp;</div>';

						//start filter options
						$return .= '<div class="du-layout-filters du-layout-filters-source-' . $source . '">';

						$return .= '<div class="du-filter-liner">';

						//$return .= '<h2>Search</h2>';
						$return .= '<div class="du-search-container">
													<input type="du-search-filter" placeholder="Search..." class="du-search-filter" value="" onkeypress="return du_form_submit(event);" />
												</div>';

						if ( ! isset( $white_label['disable_cc'] ) ) {
							$return .= '<div class="du-source-filter-container">'; //filter container
							$return .= '<span class="filter-title filter-other"><span class="dashicons dashicons-plus-alt"></span>&nbsp;Source</span>';

							$return .= '<div class="du-category-filters">';
							$return .= '<label class="du-checkbox-row"><input type="radio" class="du-layout du-cloud" value="1" name="du-layout" checked="checked" />&nbsp;Cloud Layouts</label>';
							$return .= '<label class="du-checkbox-row"><input type="radio" class="du-layout du-community" name="du-layout" value="1" />&nbsp;Community Layouts</label>';
							$return .= '</div>';

							$return .= '</div>'; //end filter container
						} else {
							$return .= '<input type="hidden" class="du-layout du-cloud" value="1" name="du-layout" />'; //we need a default so the search works
						}

						if ( $categories = $layouts['categories'] ) {
							$return .= '<div class="du-category-filter-container">';

							$return .= '<span class="filter-title filter-other"><span class="dashicons dashicons-plus-alt"></span>&nbsp;Categories</span>
													<div class="du-categories du-category-filters">';

							foreach ( $categories as $slug => $category ) {
								$return .= '<label class="du-checkbox-row"><input type="checkbox" class="du-category-filter du-category-' . $slug . '" value="' . $slug . '" />&nbsp;' . $category . '</label>';
							}

							$return .= '</div>';
							$return .= '</div>';
						}

						if ( $sets = $layouts['sets'] ) {
							$return .= '<div class="du-set-filter-container">';

							$return .= '<span class="filter-title filter-other"><span class="dashicons dashicons-plus-alt"></span>&nbsp;Sets</span>
													<div class="du-sets du-category-filters">';

							foreach ( $sets as $slug => $set ) {
								$return .= '<label class="du-checkbox-row"><input type="checkbox" class="du-set-filter du-set-' . $slug . '" value="' . $slug . '" />&nbsp;' . $set . '</label>';
							}

							$return .= '</div>';
							$return .= '</div>';
						}

						$return .= '<div class="du-other-filter-container">';
						$return .= '<span class="filter-title filter-other"><span class="dashicons dashicons-plus-alt"></span>&nbsp;Other</span>';
						$return .= '<div class="du-category-filters">';
						$return .= '<label class="du-checkbox-row"><input type="checkbox" class="du-avail-me" value="1" />&nbsp;Available to me only?</label>';
						$return .= '<label class="du-checkbox-row"><input type="checkbox" class="du-favs" value="1" />&nbsp;My Favourites only?</label>';
						$return .= '</div>';
						$return .= '</div>';

						//$return .= $layouts['tags'];

						$return .= '<a href="https://elegantmarketplace.com/" target="_blank" class="emp-link">Can\'t find what you need?<br />Try Elegant Marketplace</a>';

						$return .= '<a onclick="du_my_cloud();" class="du-my-cloud">View My Cloud &#0187;</a>';

						$return .= '<div class="du-search-filter-submit-container">
													<input type="button" onclick="du_populate_frame();" class="du-submit-filter" value="Search" />
												</div>';

						$return .= '<div class="du-clear">&nbsp;</div>';

						$return .= '</div>';
						$return .= '</div>';

						$return .= '<div class="du-clear">&nbsp;</div>';

						//end filter options

						if ( ! isset( $white_label['saved_user'] ) ) {
							if ( $type == 'section' ) {
								$return .= '<div class="du-lsd-advert">Try the <u>NEW</u> Layout Section Designer exclusively from Layouts Cloud <a href="https://layoutscloud.com/layout-section-designer/" target="_blank">Try Now</a></div>';
							}
						}

						$return .= '<div class="du-layouts-container du-layouts-container-source-' . $source . '">';

						if ( $layouts['error_success'] && $layouts['content'] ) {
							$favourites = $layouts['user']['favourites'];

							if ( is_array( $layouts['content'] ) ) {

								if ( $layouts['pages'] > 1 ) {
									$return .= '<div class="du-clear">&nbsp;</div>';
									$return .= '<div class="du-pagination du-pagination-first">';

									for ( $i = 1; $i <= $layouts['pages']; $i ++ ) {
										$return .= '<a class="du-page-link ' . ( $layouts['page'] == $i ? 'du-current-page-link' : '' ) . '" onclick="du_populate_frame(' . $i . ');">' . $i . '</a>';
									}

									$return .= '</div>';
								}

								foreach ( $layouts['content'] as $i => $layout ) {
									$favourite = isset( $favourites[ $layout['id'] ] );

									if ( $type == 'layout' ) {
										//$return .= '<pre>' . print_r($layout, true) . '</pre>';
										$return .= '<div class="du-layout-item du-layout-item-source-' . $source . ' du-type-' . $type . ' du-layout-item-' . $layout['id'] . '">' . ( ! $layout['has_access'] ? '<span class="du-no-access"><span><p><strong>Available to:</strong></p>' . implode( '<br />', $layout['rules'] ) . '</span></span>' : '' ) . '
																	<div class="bg" style="' . ( isset( $layout['image'] ) && $layout['image'] ? 'background-image: url(\'' . $layout['image'] . '\');' : '' ) . '">
																		<h2>' . $layout['name'] . '</h2>
																		' . ( $source == 'community' ? '<div class="du-layout-owner"><span class="du-author">' . $layout['author'] . '</span><span class="du-date-added">Uploaded ' . $layout['date'] . '</span><span class="du-hits">' . $layout['hits'] . ' Downloads</span></div>' : '' ) . '
																		<div class="actions">
																			<a onclick="du_preview_layout(\'' . $layout['id'] . '\', \'' . $layout['name'] . '\', \'' . $layout['iframe'] . '\', ' . $layout['has_access'] . ( $context == 'gallery' ? ', 1' : '' ) . ', 0);" class="first-button preview-layout">Preview</a>';

										if ( $layout['has_access'] ) {
											if ( $context == 'builder' ) {
												$return .= '<a onclick="du_apply_layout(\'' . $layout['id'] . '\', 0, 0);" class="last-button apply-layout">Apply</a>';
											} else if ( $context == 'gallery' ) {
												$return .= '<a onclick="du_apply_layout(\'' . $layout['id'] . '\', 0, 1);" class="last-button apply-layout">Add to Library</a>';
											}
										} else {
											$return .= '<a target="_blank" href="https://www.layoutscloud.com" class="last-button upgrade-for-layout">Upgrade</a>';
										}

										$return .= '		</div>
																	<div class="add-to-favourites">
																		<span onclick="du_toggle_favourite(' . $layout['id'] . ');" class="' . ( $favourite ? 'favourited' : '' ) . ' dashicons dashicons-heart" title="Add to Favourites"></span>
																	</div>
																	</div>
																</div>';

									} else if ( $type == 'snippet' ) { //code snippet

										$return .= '<div class="du-layout-item du-type-not-layout du-layout-item-source-' . $source . ' du-type-' . $type . ' du-layout-item-' . $layout['id'] . '">' . ( ! $layout['has_access'] ? '<span class="du-no-access"><span><p><strong>Available to:</strong></p>' . implode( '<br />', $layout['rules'] ) . '</span></span>' : '' ) . '
																	<div>
																		<h2>' . $layout['name'] . '</h2>
																	</div>
																</div>';

										/*} else if ($type == 'navigation-bar') { //nav bar

                                        $return .= '<div class="du-layout-item du-type-not-layout du-layout-item-source-' . $source . ' du-type-' . $type . ' du-layout-item-' . $layout['id'] . '">' . (!$layout['has_access'] ? '<span class="du-no-access"><span><p><strong>Available to:</strong></p>' . implode('<br />', $layout['rules']) . '</span></span>' : '') . '
																	<div class="bg" style="' . (isset($layout['image']) && $layout['image'] ? 'background-image: url(\'' . $layout['image'] . '\');' : '') . '">
																		<h2>' . $layout['name'] . '</h2>
																		<div class="actions">
																			<a onclick="du_preview_layout(\'' . $layout['id'] . '\', \'' . $layout['name'] . '\', \'' . $layout['iframe'] . '\', ' . $layout['has_access'] . ($context == 'gallery' ? ', 1' : '') . ', 1);" class="first-button preview-layout">Preview</a>';

                                        if ($layout['has_access']) {
                                            if ($context == 'gallery') {
                                                $return .= '<a onclick="du_apply_nav(\'' . $layout['id'] . '\');" class="last-button apply-layout">Apply Navigation</a>';
                                            }
                                        } else {
                                            $return .= '<a target="_blank" href="https://www.layoutscloud.com" class="last-button upgrade-for-layout">Upgrade</a>';
                                        }

                                        $return .= '		</div>
																	<div class="add-to-favourites">
																		<span onclick="du_toggle_favourite(' . $layout['id'] . ');" class="' . ($favourite ? 'favourited' : '') . ' dashicons dashicons-heart" title="Add to Favourites"></span>
																	</div>
																	</div>
																</div>';

                                    } else if ($type == 'skin') { //skin

                                        $return .= '<div class="du-layout-item du-type-not-layout du-layout-item-source-' . $source . ' du-type-' . $type . ' du-layout-item-' . $layout['id'] . '">' . (!$layout['has_access'] ? '<span class="du-no-access"><span><p><strong>Available to:</strong></p>' . implode('<br />', $layout['rules']) . '</span></span>' : '') . '
																	<div class="bg" style="' . (isset($layout['image']) && $layout['image'] ? 'background-image: url(\'' . $layout['image'] . '\');' : '') . '">
																		<h2>' . $layout['name'] . '</h2>
																		<div class="actions">
																			<a onclick="du_preview_layout(\'' . $layout['id'] . '\', \'' . $layout['name'] . '\', \'' . $layout['iframe'] . '\', ' . $layout['has_access'] . ($context == 'gallery' ? ', 1' : '') . ', 1);" class="first-button preview-layout">Preview</a>';

                                        if ($layout['has_access']) {
                                            if ($context == 'gallery') {
                                                $return .= '<a onclick="du_apply_skin(\'' . $layout['id'] . '\');" class="last-button apply-layout">Apply Skin</a>';
                                            }
                                        } else {
                                            $return .= '<a target="_blank" href="https://www.layoutscloud.com" class="last-button upgrade-for-layout">Upgrade</a>';
                                        }

                                        $return .= '		</div>
																	<div class="add-to-favourites">
																		<span onclick="du_toggle_favourite(' . $layout['id'] . ');" class="' . ($favourite ? 'favourited' : '') . ' dashicons dashicons-heart" title="Add to Favourites"></span>
																	</div>
																	</div>
																</div>';

                                    */
									} else { //modules and sections
										$return .= '<div class="du-layout-item du-type-not-layout du-type-' . $type . ' du-layout-item-' . $layout['id'] . '">' . ( ! $layout['has_access'] ? '<span class="du-no-access"><span><p><strong>Available to:</strong></p>' . implode( '<br />', $layout['rules'] ) . '</span></span>' : '' ) . '
																		<h2>' . $layout['name'] . '</h2>
																		<img src="' . $layout['image'] . '" />
																		<div class="actions">
																			<a onclick="du_preview_layout(\'' . $layout['id'] . '\', \'' . $layout['name'] . '\', \'' . $layout['iframe'] . '\', ' . $layout['has_access'] . ( $context == 'gallery' ? ', 1' : '' ) . ', 0);" class="first-button preview-layout">Preview</a>';

										if ( $layout['has_access'] ) {
											if ( $context == 'builder' ) {
												$return .= '<a onclick="du_apply_layout(\'' . $layout['id'] . '\', 0, 0);" class="last-button apply-layout">Apply</a>';
											} else if ( $context == 'gallery' ) {
												$return .= '<a onclick="du_apply_layout(\'' . $layout['id'] . '\', 0, 1);" class="last-button apply-layout">Add to Library</a>';
											}
										} else {
											$return .= '<a target="_blank" href="https://www.layoutscloud.com" class="last-button upgrade-for-layout">Upgrade</a>';
										}

										$return .= '		</div>
																	<div class="add-to-favourites">
																		<span onclick="du_toggle_favourite(' . $layout['id'] . ');" class="' . ( $favourite ? 'favourited' : '' ) . ' dashicons dashicons-heart" title="Add to Favourites"></span>
																	</div>
																</div>';
									}
								}

								if ( $layouts['pages'] > 1 ) {
									$return .= '<div class="du-clear">&nbsp;</div>';
									$return .= '<div class="du-pagination du-pagination-last">';

									for ( $i = 1; $i <= $layouts['pages']; $i ++ ) {
										$return .= '<a class="du-page-link ' . ( $layouts['page'] == $i ? 'du-current-page-link' : '' ) . '" onclick="du_populate_frame(' . $i . ');">' . $i . '</a>';
									}

									$return .= '</div>';
								}

							} else {
								$return .= $layouts['content'];
							}

						} else {
							$return .= '<div class="du-layouts-error"><div>' . wpautop( $layouts['error'] ) . '</div></div>';
						}

						$return .= '</div>';

						//$return = '<pre>' . print_r($layouts, true) . '</pre>';

					} else {
						$return = '<p>There was a problem communicating with the server. Please try again.</p>';
					}
				} else {
					$return = '<p>No type was passed. Please send du_type for a proper response</p>';
				}
				break;
			case 'get-snippets':

				if ( $layouts = du_get_snippets() ) {
					$return = '';

					$return .= '<div class="du-clear">&nbsp;</div>';

					$return .= '<div class="du-snippet-container">';

					if ( $layouts['error_success'] && $layouts['content'] ) {

						if ( is_array( $layouts['content'] ) ) {

							foreach ( $layouts['content'] as $i => $layout ) {
								$return .= '<div class="du-snippet-item du-type-snippet du-snippet-item-' . $layout['id'] . '">
                                                                        <div class="du-snippet-title-container">
                                                                            <h2>' . $layout['name'] . '</h2>
                                                                            <span class="et_pb_layout_buttons">
                                                                                <a onclick="du_toggle_snippet(\'' . $layout['id'] . '\');" class="et_pb_layout_button_load">Toggle Snippet</a>
                                                                                <div class="du-clear">&nbsp;</div>
                                                                            </span>
                                                                            <div class="du-clear">&nbsp;</div>
                                                                        </div>
                                                                        <div class="du-snippet-content-container">
                                                                            <textarea class="du-snippet-content">' . base64_decode( $layout['content'] ) . '</textarea>
                                                                            <span class="et_pb_layout_buttons">
                                                                                <a onclick="du_save_snippet(\'' . $layout['id'] . '\');" class="et_pb_layout_button_load">Save</a>
                                                                                <a onclick="du_delete_snippet(\'' . $layout['id'] . '\');" class="et_pb_layout_button_delete">Delete</a>
                                                                                <a onclick="du_copy_snippet(\'' . $layout['id'] . '\');" class="et_pb_layout_button_preview">Copy to clipboard</a>
                                                                            <div class="du-clear">&nbsp;</div>
                                                                            </span>
                                                                            <div class="du-clear">&nbsp;</div>
                                                                        </div>
																</div>';
							}

						} else {
							$return .= $layouts['content'];
						}

					} else {
						$return .= '<div class="du-layouts-error"><div>' . wpautop( $layouts['error'] ) . '</div></div>';
					}

					//add new!!

					$return .= '<div class="du-snippet-item du-type-snippet du-snippet-item-new">
                                                                        <div class="du-snippet-title-container">
                                                                            <h2>Add a New Snippet</h2>
                                                                            <span class="et_pb_layout_buttons">
                                                                                <a onclick="du_toggle_snippet(\'new\');" class="et_pb_layout_button_load">Toggle Snippet</a>
                                                                                <div class="du-clear">&nbsp;</div>
                                                                            </span>
                                                                            <div class="du-clear">&nbsp;</div>
                                                                        </div>
                                                                        <div class="du-snippet-content-container">
                                                                            <p>Snippet Name</p>
                                                                            <input type="text" class="du_snippet_name" />
                                                                            <p>Snippet Content</p>
                                                                            <textarea class="du-snippet-content"></textarea>
                                                                            <span class="et_pb_layout_buttons">
                                                                                <a onclick="du_save_new_snippet();" class="et_pb_layout_button_load">Save</a>
                                                                            <div class="du-clear">&nbsp;</div>
                                                                            </span>
                                                                            <div class="du-clear">&nbsp;</div>
                                                                        </div>
																</div>';

					//end add new!!

					$return .= '</div>';

					//$return = '<pre>' . print_r($layouts, true) . '</pre>';

				} else {
					$return = '<p>There was a problem communicating with the server. Please try again.</p>';
				}

				break;
			case 'get-my-cloud':

				$white_label = get_option( 'du_white_labelling', array() );
				$return      = '';

				if ( $type = $_GET['du_type'] ) {

					//$return = '<pre>' . print_r($layouts, true) . '</pre>';

					if ( $context == 'builder' ) {
						$return .= '<div class="du-replace"><label for="du-et_pb_load_layout_replace">
			                            <input type="checkbox" id="du-et_pb_load_layout_replace" class="du-et_pb_load_layout_replace" checked="checked">&nbsp;Replace the existing content with loaded layout
                                    </label>';
						$return .= '</div>';
					}

					$return .= '<div class="du-emp-plug"><a href="https://elegantmarketplace.com/" target="_blank" class="emp-link">Can\'t find what you need? Try Elegant Marketplace</a></div>';

					$return .= '<div class="du-clear">&nbsp;</div>';

					//my cloud
					$layouts = du_get_my_cloud( $type );
					$return  .= '<script>var du_current_cats = []; ';

					if ( $categories = $layouts['categories'] ) {
						foreach ( $categories as $slug => $category ) {
							$return .= ' du_current_cats.push("' . $category . '"); ';
						}
					}
					$return .= '</script>';

					$return .= '<div class="du-layouts-container">
									<div class="du-layout-filters">
										<div class="du-filter-liner">
											<h2 class="du-filter-label">My Cloud</h2>
											<a href="https://elegantmarketplace.com/" target="_blank" class="emp-link">Can\'t find what you need?<br>Try Elegant Marketplace</a>';

					if ( ! isset( $white_label['disable_dc'] ) ) {
						$return .= '<a onclick="du_populate_frame();" class="du-my-cloud">View All Layouts &#0187;</a>';
					}

					if ( ! isset( $white_label['cats'] ) || ! count( $white_label['cats'] ) ) {
						$return .= '<div class="du-category-filter-container du-user-category-filter-container">';

						$return .= '<span class="filter-title filter-other"><span class="dashicons dashicons-plus-alt"></span>&nbsp;Categories</span>
													<div class="du-categories du-category-filters du-user-category-filters">';

						$return .= '<div class="du-checkbox-row">';
						$return .= '<h3>Click To Filter</h3>';

						if ( $categories ) {
							$return .= '<input type="button" onclick="du_filter_own_cloud(\'all\');" class="du-filter-user-cat du-filter-user-cat-selected du-category-all" value="All Categories" />';

							foreach ( $categories as $slug => $category ) {
								$return .= '<input type="button" onclick="du_filter_own_cloud(\'' . $slug . '\');" class="du-filter-user-cat du-filter-user-cat-' . $slug . ' du-category-' . $slug . '" value="' . $category . '" />';
							}
						} else {
							$return .= '<p>Categories will appear here once you have added one.</p>';
						}

						$return .= '</div>';

						$return .= '<div class="du-checkbox-row">';
						$return .= '<h3>Add New Category</h3>';

						$return .= '<input type="text" class="du-new-category-text" />
                                <input type="button" onclick="du_create_new_user_category();" value="Create Category" />';

						$return .= '</div>';
						$return .= '</div>';
						$return .= '</div>';
					}

					$return .= '            <div class="du-clear">&nbsp;</div>
										</div>
									</div>';

					if ( $layouts['error_success'] && $layouts['content'] ) {

						$context = 'builder';
						if ( isset( $_GET['du_context'] ) ) {
							$context = $_GET['du_context'];
						}

						if ( is_array( $layouts['content'] ) ) {
							$return .= '<div class="du-my-cloud-container">';

							foreach ( $layouts['content'] as $i => $layout ) {
								$extra_classes = 'du-user-cat-all';

								if ( $layout['categories'] ) {
									foreach ( $layout['categories'] as $slug => $label ) {
										$extra_classes .= ' du-user-cat-' . $slug;
									}
								}

								////////////////////////////////////

								//for white labelling
								if ( isset( $white_label['cats'] ) && count( $white_label['cats'] ) ) {
									$in_cat = false;

									foreach ( $white_label['cats'] as $cat => $ignore ) {
										if ( in_array( $cat, array_keys( $layout['categories'] ) ) ) {
											$in_cat = true;
											break;
										}
									}

									if ( ! $in_cat ) {
										continue; //don't show this one
									}
								}

								////////////////////////////////////

								//start item
								$return .= '<div class="du-layout-item du-layout-item-source-my-cloud du-type-' . $type . ' du-layout-item-' . $layout['id'] . ' du-my-cloud-item-' . $layout['id'] . ' ' . $extra_classes . '">
																	<div class="bg" style="' . ( isset( $layout['image'] ) && $layout['image'] ? 'background-image: url(\'' . $layout['image'] . '\');' : '' ) . '">
																		<h2>' . $layout['name'] . '</h2>
																		<span class="du-cloud-date">' . $layout['date'] . '</span>';

								$return .= '<div class="actions">';

								///////////////////////////////////////////////////////

								//$return .= '<span class="du-cloud-categories">';
								//$return .= '<strong>Categories:</strong> ';

								//if ($layout['categories']) {
//                                    $return .= '<span class="du-cloud-category-text">' . implode(',  ', $layout['categories']) . '</span>';
								//}

								//$return .= '</span>';

								$return .= '<a onclick="jQuery(\'.du-my-cloud-item-' . $layout['id'] . ' .du-category-assign\').slideToggle();" class="du-add-edit-cloud-cats">Edit Categories</a>';

								if ( $categories = $layouts['categories'] ) {
									$return .= '<div class="du-category-assign">';

									foreach ( $categories as $slug => $category ) {
										$selected = ( isset( $layout['categories'][ $slug ] ) ? 'checked="checked"' : '' );
										$return   .= '<label><input ' . $selected . ' type="checkbox" class="du-assign-user-cat du-assign-user-cat-' . $slug . '" value="' . $slug . '" />&nbsp;' . $category . '</label>';
									}

									$return .= '<div class="du-assign-category-cta"><input type="button" onclick="du_assign_user_categories(' . $layout['id'] . ');" value="Assign Categories" /></div>';
									$return .= '<div class="du-clear">&nbsp;</div>';

									$return .= '</div>';
								}

								//$return .= '<pre>';
								//$return .= print_r($layout, true);
								//$return .= '</pre>';

								///////////////////////////////////////////////////////

								$return .= ( $context == 'builder' ? '<a onclick="du_apply_layout(\'' . $layout['id'] . '\', 1);" class="et_pb_layout_button_load">Apply</a>' : '<a onclick="du_apply_layout(\'' . $layout['id'] . '\', 1, 1);" class="et_pb_layout_button_load">Add to Library</a>' );
								$return .= '<a onclick="du_preview_own_layout(\'' . $layout['id'] . '\', \'' . $layout['name'] . '\', \'' . $layout['url'] . '\', 1' . ( $context == 'gallery' ? ', 1' : ', 0' ) . ', 0);" class="first-button preview-layout et_pb_layout_button_preview">Preview</a>';
								$return .= '<a onclick="du_delete_layout(\'' . $layout['id'] . '\');" class="et_pb_layout_button_delete">Delete</a>';
								$return .= ( ! isset( $white_label['disable_cc'] ) ? '<a onclick="du_submit_cc(\'' . $layout['id'] . '\');" class="et_pb_layout_button_load et_pb_layout_cc">Add to Community Cloud</a>' : '' );

								$return .= '		<div class="du-clear">&nbsp;</div>
		                                                                </div>
																	</div>
																</div>';
								//end item
							}

							$return .= '</div>';

						} else {
							$return .= '<div class="du-layouts-error du-layouts-message"><div>' . wpautop( $layouts['content'] ) . '</div></div>';
						}
					} else {
						$return .= '<div class="du-layouts-error"><div>' . wpautop( $layouts['error'] ) . '</div></div>';
					}

					$return .= '</div>';

					$return .= '<div class="du-clear">&nbsp;</div>';

					//cc
					if ( ! isset( $white_label['disable_cc'] ) ) {
						$layouts = du_get_community_cloud( $type );

						$return .= '<div class="du-layouts-container">
									<div class="du-layout-filters">
										<div class="du-filter-liner">
											<h2 class="du-filter-label">My Community Cloud Submissions</h2>
											<a href="https://elegantmarketplace.com/" target="_blank" class="emp-link">Can\'t find what you need?<br>Try Elegant Marketplace</a>
											<a onclick="du_populate_frame();" class="du-my-cloud">View All Layouts &#0187;</a>
											<a onclick="du_what_community_cloud();" class="du-my-cloud du-what-my-cloud">?</a>
											<div class="du-clear">&nbsp;</div>
										</div>
									</div>';

						if ( $layouts['error_success'] && $layouts['content'] ) {

							if ( is_array( $layouts['content'] ) ) {
								$return .= '<div class="du-my-cloud-container">';

								foreach ( $layouts['content'] as $i => $layout ) {
									$return .= '<div class="du-my-cloud-item du-layout-item-' . $layout['id'] . ' du-my-cloud-item-' . $layout['id'] . '">
												<span class="du-ol-title">
													<span class="dashicons dashicons-schedule"></span> ' . $layout['name'] . '
													<br />
													<span class="du-cloud-date">' . $layout['date'] . '</span> - <span class="du-cloud-status">Status: <span class="du-cloud-status-' . $layout['post_status'] . '">' . ( ucwords( $layout['post_status'] ) ) . '</span></span>
												</span>
												<span class="et_pb_layout_buttons">
													<a onclick="du_preview_own_layout(\'' . $layout['id'] . '\', \'' . $layout['name'] . '\', \'' . $layout['url'] . '\', 1' . ( $context == 'gallery' ? ', 1' : ', 0' ) . ', 0);" class="et_pb_layout_button_preview">Preview</a>
													' . ( $context == 'builder' ? '<a onclick="du_apply_layout(\'' . $layout['id'] . '\');" class="et_pb_layout_button_load">Apply</a>' : '<a onclick="du_apply_layout(\'' . $layout['id'] . '\', 0, 1);" class="et_pb_layout_button_load">Add to Library</a>' ) . '
													<div class="du-clear">&nbsp;</div>
												</span>
												<div class="du-clear">&nbsp;</div>
											</div>';
								}

								$return .= '</div>';
							} else {
								$return .= '<div class="du-layouts-error du-layouts-message"><div>' . wpautop( $layouts['content'] ) . '</div></div>';
							}

						} else {
							$return .= '<div class="du-layouts-error"><div>' . wpautop( $layouts['error'] ) . '</div></div>';
						}

						$return .= '</div>';
						$return .= '<div class="du-clear">&nbsp;</div>';
					}
					//end cc
				} else {
					$return .= '<p>No type was passed. Please send du_type for a proper response</p>';
				}
				break;

			case
			'save-to-cc':

				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( $id = $_GET['du_layout_id'] ) {

					$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=' . $action . '&du_layout_id=' . $id;

					if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
						if ( ! is_wp_error( $remote ) ) {
							if ( $return = json_decode( $remote['body'], true ) ) {
								if ( ! isset( $return['error'] ) || ! $return['error'] ) {
									$return['content'] = 1;
								} else {
									//there was an error. the text is contained inside the array and returned to the calling function
								}
							}
						} else {
							$return['error'] = 'Could not connect to the Layouts Cloud server (SCC) - ' . $remote->get_error_message();
							$return['debug'] = false;
						}
					}

				} else {
					$return['error'] = '<p>No layout id was passed.</p>';
				}

				$return = json_encode( $return );

				break;

			case 'delete-own-layout':

				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( $id = $_GET['du_layout_id'] ) {

					$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=' . $action . '&du_layout_id=' . $id;

					if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
						if ( ! is_wp_error( $remote ) ) {
							if ( $return = json_decode( $remote['body'], true ) ) {
								if ( ! isset( $return['error'] ) || ! $return['error'] ) {
									$return['content'] = 1;
								} else {
									//there was an error. the text is contained inside the array and returned to the calling function
								}
							}
						} else {
							$return['error'] = 'Could not connect to the Layouts Cloud server (DOL) - ' . $remote->get_error_message();
							$return['debug'] = false;
						}
					}

				} else {
					$return['error'] = '<p>No layout id was passed.</p>';
				}

				$return = json_encode( $return );

				break;
			case 'get-layout':

				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( $id = $_GET['du_layout_id'] ) {
					if ( $layout = du_get_layout( $id ) ) {
						if ( $layout['error_success'] && $layout['content'] ) {
							$data                    = array(
								'import_id'   => $layout['import_id'],
								'layout_type' => $layout['layout_type'],
								'import_name' => $layout['import_name']
							);
							$return['content']       = $data;
							$return['error_success'] = true;
						} else {
							$return['content'] = wpautop( $layout['error'] );
						}
					} else {
						$return['error'] = '<p>There was a problem communicating with the server. Please try again.</p>';
					}
				} else {
					$return['error'] = '<p>No layout id was passed.</p>';
				}

				$return = json_encode( $return );
				break;

			case 'add-favourite':
			case 'remove-favourite':

				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( $id = $_GET['du_layout_id'] ) {

					$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=' . $action . '&du_layout_id=' . $id;

					if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
						if ( ! is_wp_error( $remote ) ) {
							if ( $return = json_decode( $remote['body'], true ) ) {
								if ( ! isset( $return['error'] ) || ! $return['error'] ) {
									$return['content'] = 1;
								} else {
									//there was an error. the text is contained inside the array and returned to the calling function
								}
							}
						} else {
							$return['error'] = 'Could not connect to the Layouts Cloud server (AF) - ' . $remote->get_error_message();
							$return['debug'] = false;
						}
					}

				} else {
					$return['error'] = '<p>No layout id was passed.</p>';
				}

				$return = json_encode( $return );
				break;

			case 'delete-snippet':

				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( $id = $_GET['du_snippet_id'] ) {

					$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=' . $action . '&du_snippet_id=' . $id;
					if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
						if ( ! is_wp_error( $remote ) ) {
							if ( $return = json_decode( $remote['body'], true ) ) {
								if ( ! isset( $return['error'] ) || ! $return['error'] ) {
									$return['content'] = 1;
								} else {
									//there was an error. the text is contained inside the array and returned to the calling function
								}
							}
						} else {
							$return['error'] = 'Could not connect to the Layouts Cloud server (DELS) - ' . $remote->get_error_message();
							$return['debug'] = false;
						}
					}

				} else {
					$return['error'] = '<p>No snippet id was passed.</p>';
				}

				$return = json_encode( $return );
				break;

			case 'get-own-layout':

				$return                  = array();
				$return['content']       = '';
				$return['error_success'] = false;

				if ( $id = $_GET['du_layout_id'] ) {
					if ( $layout = du_get_own_layout( $id ) ) {
						if ( $layout['error_success'] && $layout['content'] ) {
							$data                    = array(
								'import_id'   => $layout['import_id'],
								'import_name' => $layout['import_name'],
								'layout_type' => $layout['layout_type']
							);
							$return['content']       = $data;
							$return['error_success'] = true;
						} else {
							$return['error'] = wpautop( $layout['error'] );
						}
					} else {
						$return['error'] = '<p>There was a problem communicating with the server. Please try again.</p>';
					}
				} else {
					$return['error'] = '<p>No type was passed. Please send du_type for a proper response</p>';
				}

				$return = json_encode( $return );
				break;

			case 'save-layout':

				if ( isset( $_GET['du_layout_name'] ) && $_GET['du_layout_name'] ) {
					$name = strip_tags( $_GET['du_layout_name'] );

					if ( isset( $_POST['du_layout_content'] ) && $_POST['du_layout_content'] ) {
						$content     = base64_encode( $_POST['du_layout_content'] );
						$layout_type = strip_tags( $_REQUEST['du_layout_type'] );
						$url         = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=save-layout&du_layout_name=' . $name . '&du_layout_type=' . $layout_type;

						//echo $url;

						if ( $remote = wp_remote_post( $url, array(
							'timeout' => 90,
							'body'    => array( 'du_layout_content' => $content )
						) ) ) {
							//print_r($remote);

							if ( ! is_wp_error( $remote ) ) {
								if ( $return = json_decode( $remote['body'], true ) ) {
									if ( ! isset( $return['error'] ) || ! $return['error'] ) {
										//$return['debug'] = $content;
										//$return['debug2'] = $url;
										//$return['debug3'] = $_POST;
										//there was no error
									} else {
										//there was an error. the text is contained inside the array and returned to the calling function
									}
								}
							} else {
								$return['error'] = 'Could not connect to the Layouts Cloud server (SL) - ' . $remote->get_error_message();
								//$return['debug'] = false;
							}
						}
					} else {
						$return['error'] = 'Please enter a layout into the builder before trying to save';
					}
				} else {
					$return['error'] = 'No layout name was specified. Please enter one and resubmit.';
				}

				$return = json_encode( $return );
				break;

			case 'save-snippet':

				if ( ( isset( $_GET['du_snippet_name'] ) && $_GET['du_snippet_name'] ) || isset( $_GET['du_snippet_id'] ) ) {
					$name = strip_tags( @$_GET['du_snippet_name'] );

					if ( isset( $_POST['du_snippet_content'] ) && $_POST['du_snippet_content'] ) {
						$content = base64_encode( $_POST['du_snippet_content'] );
						$url     = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=save-snippet&du_snippet_name=' . $name;

						if ( isset( $_GET['du_snippet_id'] ) && $_GET['du_snippet_id'] ) {
							$url .= '&du_snippet_id=' . $_GET['du_snippet_id']; //makes this an edit
						}

						//echo $url;

						if ( $remote = wp_remote_post( $url, array(
							'timeout' => 90,
							'body'    => array( 'du_snippet_content' => $content )
						) ) ) {
							if ( ! is_wp_error( $remote ) ) {
								if ( $return = json_decode( $remote['body'], true ) ) {
									if ( ! isset( $return['error'] ) || ! $return['error'] ) {
										//$return['debug'] = $content;
										//$return['debug2'] = $url;
										//$return['debug3'] = $_POST;
										//there was no error
									} else {
										//there was an error. the text is contained inside the array and returned to the calling function
									}
								}
							} else {
								$return['error'] = 'Could not connect to the Layouts Cloud server (SSN) - ' . $remote->get_error_message();
								//$return['debug'] = false;
							}
						}
					} else {
						$return['error'] = 'Please enter some snippet content before trying to save';
					}
				} else {
					$return['error'] = 'No layout name was specified. Please enter one and resubmit.';
				}

				$return = json_encode( $return );
				break;
		}

		echo $return;
		die;
	}
}

function du_menu() {
	add_menu_page(
		DU_NAME,
		DU_NAME,
		'manage_options',
		'du_admin_home',
		'du_gallery_view',
		'dashicons-cloud',
		500
	);

	add_submenu_page(
		'du_admin_home',
		'Settings',
		'Settings',
		'manage_options',
		'layouts_cloud',
		'du_submenu_cb'
	);

	add_submenu_page(
		'du_admin_home',
		'My Cloud',
		'My Cloud',
		'manage_options',
		'du_submenu_mc',
		'du_submenu_mc'
	);
}

function du_account_info( $key ) {
	global $du_remote_site;

	$return  = false;
	$testkey = 'testapikey';

	if ( $key == $testkey ) {
		$return = array(
			'debug'   => true
		,
			'error'   => false
		,
			'name'    => 'Test Local Name'
		,
			'email'   => 'test@layoutscloud.com'
		,
			'expiry'  => '2100-01-01 23:59:59'
		,
			'expired' => false
		);
	} else {
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=get-account';

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			//api key passed and an action 'get-account' to get info on the user to whom the key belongs

			if ( isset( $_GET['debug'] ) && $_GET['debug'] == 1 ) {
				echo $url;
				echo '<pre>';
				print_r( $remote );
				echo '</pre>';
			}

			if ( ! is_wp_error( $remote ) ) {

				if ( $return = json_decode( $remote['body'], true ) ) {
					if ( ! isset( $return['error'] ) || ! $return['error'] ) {
						//there was no error
						$return['debug'] = false;
					} else {
						//there was an error. the text is contained inside the array and returned to the calling function
					}
				}
			} else {
				$return['error'] = 'Could not connect to the Layouts Cloud server (AI) - ' . $remote->get_error_message();
				$return['debug'] = false;
			}
		}
	}

	return $return;
}

function du_get_layouts( $type = 'layout', $key = false ) {
	global $du_remote_site;

	if ( ! $key ) {
		$key = get_option( 'du_api_key' );
	}

	$return = false;

	if ( $key ) {
		//if ($type == 'layout') { //for now only layouts work
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=get-layouts&du_type=' . $type;

		if ( isset( $_GET['du_kw'] ) ) {
			$url .= '&du_kw=' . rawurlencode( $_GET['du_kw'] );
		}
		if ( isset( $_GET['du_view'] ) ) {
			$url .= '&du_view=' . rawurlencode( $_GET['du_view'] );
		}
		if ( isset( $_GET['du_cats'] ) ) {
			$url .= '&du_cats=' . rawurlencode( $_GET['du_cats'] );
		}
		if ( isset( $_GET['du_sets'] ) ) {
			$url .= '&du_sets=' . rawurlencode( $_GET['du_sets'] );
		}
		if ( isset( $_GET['du_source'] ) ) {
			$url .= '&du_source=' . $_GET['du_source'];
		}
		if ( isset( $_GET['du_avm'] ) ) {
			$url .= '&du_avm=' . (int) $_GET['du_avm'];
		}
		if ( isset( $_GET['du_favs'] ) ) {
			$url .= '&du_favs=' . (int) $_GET['du_favs'];
		}
		if ( isset( $_GET['du_paged'] ) ) {
			$url .= '&du_paged=' . (int) $_GET['du_paged'];
		}
		if ( isset( $_GET['du_pp'] ) ) {
			$url .= '&du_pp=' . (int) $_GET['du_pp'];
		}

		//echo $url;

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			if ( ! is_wp_error( $remote ) ) {

				if ( $return = json_decode( $remote['body'], true ) ) {
					if ( ! isset( $return['error'] ) || ! $return['error'] ) {
						//there was no error
						$return['debug'] = false;
					} else {
						//there was an error. the text is contained inside the array and returned to the calling function
					}
				}
			} else {
				$return['error'] = 'Could not connect to the Layouts Cloud server (GL) - ' . $remote->get_error_message();
				$return['debug'] = false;
			}
		}
		//} else {
		//$coming_soon_image = trailingslashit(plugin_dir_url( __FILE__ )) . 'images/coming-soon.jpg';
		//$return['content'] = '<a href="https://layoutscloud.com" target="_blank">
		//<img src="' . $coming_soon_image . '" style="display: block;" />
		//</a>';
		//$return['debug'] = false;
		//$return['error_success'] = true;
		//}
	}

	return $return;
}

function du_get_snippets( $key = false ) {
	global $du_remote_site;

	if ( ! $key ) {
		$key = get_option( 'du_api_key' );
	}

	$return = false;

	if ( $key ) {
		//if ($type == 'layout') { //for now only layouts work
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=get-snippets';

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			if ( ! is_wp_error( $remote ) ) {

				if ( $return = json_decode( $remote['body'], true ) ) {
					if ( ! isset( $return['error'] ) || ! $return['error'] ) {
						//there was no error
						$return['debug'] = false;
					} else {
						//there was an error. the text is contained inside the array and returned to the calling function
					}
				}
			} else {
				$return['error'] = 'Could not connect to the Layouts Cloud server (GSN) - ' . $remote->get_error_message();
				$return['debug'] = false;
			}
		}
	}

	return $return;
}

function du_get_my_cloud( $type = 'layout', $key = false ) {
	global $du_remote_site;

	if ( ! $key ) {
		$key = get_option( 'du_api_key' );
	}

	$return                  = array();
	$return['error']         = '';
	$return['error_success'] = false;

	if ( $key ) {
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=get-my-cloud&du_type=' . $type;

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			if ( ! is_wp_error( $remote ) ) {
				$return['error_success'] = true;

				if ( $return = json_decode( $remote['body'], true ) ) {
					if ( ! isset( $return['error'] ) || ! $return['error'] ) {
						//there was no error
						$return['debug'] = false;
					} else {
						//there was an error. the text is contained inside the array and returned to the calling function
					}
				}
			} else {
				$return['error'] = 'Could not connect to the Layouts Cloud server (GC) - ' . $remote->get_error_message();
				$return['debug'] = false;
			}
		} else {
			$return['error'] = 'Could not connect to the Layouts Cloud server (GC2) - ' . print_r( $remote, true );
			$return['debug'] = false;
		}
	} else {
		$return['error'] = 'No API Key';
		$return['debug'] = false;
	}

	return $return;
}

function du_get_community_cloud( $type = 'layout', $key = false ) {
	global $du_remote_site;

	if ( ! $key ) {
		$key = get_option( 'du_api_key' );
	}

	$return                  = array();
	$return['error']         = '';
	$return['error_success'] = false;

	if ( $key ) {
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=get-community-cloud&du_type=' . $type;

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			if ( ! is_wp_error( $remote ) ) {
				$return['error_success'] = true;

				if ( $return = json_decode( $remote['body'], true ) ) {
					if ( ! isset( $return['error'] ) || ! $return['error'] ) {
						//there was no error
						$return['debug'] = false;
					} else {
						//there was an error. the text is contained inside the array and returned to the calling function
					}
				}
			} else {
				$return['error'] = 'Could not connect to the Layouts Cloud server (GCC) - ' . $remote->get_error_message();
				$return['debug'] = false;
			}
		} else {
			$return['error'] = 'Could not connect to the Layouts Cloud server (GCC2) - ' . print_r( $remote, true );
			$return['debug'] = false;
		}
	} else {
		$return['error'] = 'No API Key';
		$return['debug'] = false;
	}

	return $return;
}

function du_get_layout( $id, $key = false ) {
	global $du_remote_site;

	if ( ! $key ) {
		$key = get_option( 'du_api_key' );
	}

	$return = false;

	if ( $key ) {
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=get-layout&du_layout_id=' . $id;

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			if ( ! is_wp_error( $remote ) ) {

				if ( $return = json_decode( $remote['body'], true ) ) {
					if ( ! isset( $return['error'] ) || ! $return['error'] ) {
						if ( is_array( $return['content'] ) ) {
							$return = du_get_community_layout( $return );

						} else if ( $content = json_decode( $return['content'], true ) ) {

							if ( isset( $return['layout_type'] ) && ( $return['layout_type'] == 'navigation-bar' || $return['layout_type'] == 'skin' ) ) {
								if ( ! $mods = get_option( 'et_divi' ) ) {
									$mods = array();
								}

								foreach ( $content['data'] as $key => $value ) {
									$mods[ $key ] = $value;
								}

								if ( isset( $return['custom_css'] ) && $return['custom_css'] ) {
									$return['custom_css']    = base64_decode( $return['custom_css'] );
									$mods['divi_custom_css'] = '/*dc-custom-css - Added by Divi Cloud. Do not remove this line*/' . "\n" . $return['custom_css'] . '/*end-dc-custom-css - Added by Divi Cloud. Do not remove this line*/' . "\n\n" . $mods['divi_custom_css'];

									if ( $stylesheet = get_option( 'stylesheet' ) ) {
										if ( $theme_mods = get_option( 'theme_mods_' . $stylesheet ) ) {
											if ( isset( $theme_mods['custom_css_post_id'] ) && $theme_mods['custom_css_post_id'] > 0 ) {
												if ( $custom_css_post = get_post( $theme_mods['custom_css_post_id'] ) ) {

													//find and remove old DC css
													if ( ( $pos = strpos( $custom_css_post->post_content, '/*dc-custom-css' ) ) !== false ) {
														if ( ( $end_pos = strrpos( $custom_css_post->post_content, 'end-dc-custom-css*/' ) ) !== false ) {
															$end_pos                       += 19;
															$custom_css_post->post_content = substr_replace( $custom_css_post->post_content, '', $pos, ( $end_pos - $pos ) );
														}
													}
													//end find and remove

													$custom_css_post->post_content = '/*dc-custom-css - Added by Divi Cloud. Do not remove this line*/' . "\n" . trim( $return['custom_css'] ) . "\n" . '/* Added by Divi Cloud. Do not remove this line - end-dc-custom-css*/' . "\n\n" . trim( $custom_css_post->post_content );

													wp_update_post( $custom_css_post );
												}
											}
										}
									}
								}

								update_option( 'et_divi', $mods );

							} else {
								$imported = du_import_posts( $content['data'] );

								foreach ( $imported as $import_id => $import_name ) {
									$return['import_id']   = $import_id;
									$return['import_name'] = $import_name;
									break; //first one only in case archive has multiple
								}
							}

							$return['layout_type']   = $return['layout_type']; //so the system knows what to do with the data
							$return['error_success'] = true;
						} else {
							$return['error']         = 'No content found';
							$return['error_success'] = false;
						}

						//there was no error
						$return['debug'] = false;
					} else {
						//there was an error. the text is contained inside the array and returned to the calling function
					}
				}
			} else {
				$return['error']         = 'Could not connect to the Layouts Cloud server (LY) - ' . $remote->get_error_message();
				$return['debug']         = false;
				$return['error_success'] = false;
			}
		}
	}

	return $return;
}

function du_get_community_layout( $return ) {
	if ( $content = json_decode( $return['content']['post_content'], true ) ) {
		if ( $imported = du_import_own_post( $content ) ) {
			$return['import_id']     = $imported['id'];
			$return['import_name']   = $imported['name'];
			$return['error_success'] = true;
		} else {
			$return['error_success'] = false;
			$return['error']         = 'There was an unknown error. Please contact us providing your layout as a JSON file for testing';
		}

		$return['layout_type'] = $return['layout_type']; //so the system knows what to do with the data

	} else {
		$return['error']         = 'No content found';
		$return['error_success'] = false;
	}

	return $return;
}

function du_get_own_layout( $id, $key = false ) {
	global $du_remote_site;

	if ( ! $key ) {
		$key = get_option( 'du_api_key' );
	}

	$return = false;

	if ( $key ) {
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=get-user-layout&du_layout_id=' . $id;

		//echo $url;

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			if ( ! is_wp_error( $remote ) ) {

				if ( $return = json_decode( $remote['body'], true ) ) {

					if ( ! isset( $return['error'] ) || ! $return['error'] ) {
						if ( $content = json_decode( $return['content']['post_content'], true ) ) {

							if ( $imported = du_import_own_post( $content ) ) {
								$return['import_id']     = $imported['id'];
								$return['import_name']   = $imported['name'];
								$return['error_success'] = true;
							} else {
								$return['error_success'] = false;
								$return['error']         = 'There was an unknown error. Please contact us providing your layout as a JSON file for testing';
							}

							$return['layout_type'] = $return['layout_type']; //so the system knows what to do with the data

						} else {
							$return['error']         = 'No content found';
							$return['error_success'] = false;
						}

						//there was no error
						$return['debug'] = false;
					} else {
						//there was an error. the text is contained inside the array and returned to the calling function
					}
				}
			} else {
				$return['error']         = 'Could not connect to the Layouts Cloud server (OL) - ' . $remote->get_error_message();
				$return['debug']         = false;
				$return['error_success'] = false;
			}
		}
	}

	return $return;
}

function du_import_posts( $posts ) {
	global $wpdb;

	if ( ! function_exists( 'post_exists' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
	}

	$imported = array();

	if ( empty( $posts ) ) {
		return;
	}

	foreach ( $posts as $old_post_id => $post ) {
		if ( isset( $post['post_status'] ) && 'auto-draft' === $post['post_status'] ) {
			continue;
		}

		$post_exists = post_exists( $post['post_title'] );

		// Make sure the post is published and stop here if the post exists.
		if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
			if ( 'publish' == get_post_status( $post_exists ) ) {
				$imported[ $post_exists ] = $post['post_title'];
				continue;
			}
		}

		if ( isset( $post['ID'] ) ) {
			$post['import_id'] = $post['ID'];
			unset( $post['ID'] );
		}

		$post['post_author'] = (int) get_current_user_id();

		// Insert or update post.
		$post_id = wp_insert_post( $post, true );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		if ( ! isset( $post['terms'] ) ) {
			$post['terms'] = array();
		}

		$post['terms'][] = array(
			'name'        => 'Layouts Cloud',
			'slug'        => 'layouts-cloud',
			'taxonomy'    => 'layout_category',
			'parent'      => 0,
			'description' => ''
		); //add on layouts cloud

		// Insert and set terms.
		if ( count( $post['terms'] ) > 0 ) {
			$processed_terms = array();

			foreach ( $post['terms'] as $term ) {

				if ( empty( $term['parent'] ) ) {
					$parent = 0;
				} else {
					$parent = term_exists( $term['name'], $term['taxonomy'], $term['parent'] );

					if ( is_array( $parent ) ) {
						$parent = $parent['term_id'];
					}
				}

				if ( ! $insert = term_exists( $term['name'], $term['taxonomy'], $term['parent'] ) ) {
					$insert = wp_insert_term( $term['name'], $term['taxonomy'], array(
						'slug'        => $term['slug'],
						'description' => $term['description'],
						'parent'      => intval( $parent ),
					) );
				}

				if ( is_array( $insert ) && ! is_wp_error( $insert ) ) {
					$processed_terms[ $term['taxonomy'] ][] = $term['slug'];
				}
			}

			// Set post terms.
			foreach ( $processed_terms as $taxonomy => $ids ) {
				wp_set_object_terms( $post_id, $ids, $taxonomy );
			}
		}

		// Insert or update post meta.
		if ( isset( $post['post_meta'] ) && is_array( $post['post_meta'] ) ) {
			foreach ( $post['post_meta'] as $meta_key => $meta ) {

				$meta_key = sanitize_text_field( $meta_key );

				if ( count( $meta ) < 2 ) {
					$meta = wp_kses_post( $meta[0] );
				} else {
					$meta = array_map( 'wp_kses_post', $meta );
				}

				update_post_meta( $post_id, $meta_key, $meta );
			}
		}

		$imported[ $post_id ] = $post['post_title'];

	}

	return $imported;
}

function du_import_own_post( $post ) {
	global $wpdb;

	if ( ! function_exists( 'post_exists' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
	}

	$imported = array();

	//echo '<pre>';
	//print_r($post);
	//echo '</pre>';
	//die;

	$post_exists = post_exists( $post['post_title'] );

	// Make sure the post is published and stop here if the post exists.
	if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
		if ( 'publish' !== get_post_status( $post_exists ) ) {
			wp_update_post( array(
				'ID'          => intval( $post_exists ),
				'post_status' => 'publish',
			) );
		}

		$imported[ $post_exists ] = $post['post_title'];
		$imported                 = array( 'id' => $post_exists, 'name' => $post['post_title'] );

		return $imported;
	}

	if ( isset( $post['ID'] ) ) {
		$post['import_id'] = $post['ID'];
		unset( $post['ID'] );
	} else if ( isset( $post['id'] ) ) {
		$post['import_id'] = $post['id'];
		unset( $post['id'] );
	}

	$post['post_content'] = base64_decode( $post['post_content'] );
	$post['post_author']  = (int) get_current_user_id();

	// Insert or update post.
	$post_id = wp_insert_post( $post, true );

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// Insert and set terms.
	if ( isset( $post['terms'] ) && is_array( $post['terms'] ) ) {
		$processed_terms = array();

		foreach ( $post['terms'] as $term ) {

			if ( empty( $term['parent'] ) ) {
				$parent = 0;
			} else {
				$parent = term_exists( $term['parent'], $term['taxonomy'] );

				if ( is_array( $parent ) ) {
					$parent = $parent['term_id'];
				}
			}

			if ( ! $insert = term_exists( $term['slug'], $term['taxonomy'] ) ) {
				$insert = wp_insert_term( $term['name'], $term['taxonomy'], array(
					'slug'        => $term['slug'],
					'description' => $term['description'],
					'parent'      => intval( $parent ),
				) );
			}

			if ( is_array( $insert ) && ! is_wp_error( $insert ) ) {
				$processed_terms[ $term['taxonomy'] ][] = $term['slug'];
			}
		}

		// Set post terms.
		foreach ( $processed_terms as $taxonomy => $ids ) {
			wp_set_object_terms( $post_id, $ids, $taxonomy );
		}
	}

	// Insert or update post meta.
	if ( isset( $post['post_meta'] ) && is_array( $post['post_meta'] ) ) {
		foreach ( $post['post_meta'] as $meta_key => $meta ) {
			$meta_key = sanitize_text_field( $meta_key );
			update_post_meta( $post_id, $meta_key, $meta );
		}
	}

	$imported = array( 'id' => $post_id, 'name' => $post['post_title'] );

	return $imported;
}

function du_gallery_view() {
	$key         = get_option( 'du_api_key' );
	$key_active  = get_option( 'du_api_key_active' );
	$white_label = get_option( 'du_white_labelling', array() );

	if ( ! $key && ! $key_active ) {
		echo '<script>window.location="' . admin_url( 'admin.php?page=layouts_cloud' ) . '";</script>';
		die; //redirecting to settings page
	}

	echo '<div class="wrap">
					<h2><span style="width: 30px; height: 30px; font-size: 30px;" class="dashicons dashicons-cloud"></span> ' . DU_NAME . ' - V' . DU_VERSION . '</h2>';

	echo '<div id="poststuff2">
					<div id="post-body" class="metabox-holder columns-2">';
	////////////////

	echo '<div style="clear: both;">';

	//echo du_box_start('Divi Cloud');

	echo '<div class="du-gallery">
					<div class="du" data-layout_type="layout"></div>';

	if ( ! isset( $white_label['disable_dc'] ) ) {
		echo '      <div class="du-gallery-layout-types">
						<div class="du-layout-type-filter active" data-layout_type="layout">Layouts</div>
						<div class="du-layout-type-filter" data-layout_type="section">Sections</div>
						<div class="du-layout-type-filter" data-layout_type="module">Modules</div>
						<!-- <div class="du-layout-type-filter" data-layout_type="navigation-bar">Nav Bars</div>
						<div class="du-layout-type-filter" data-layout_type="skin">Site Skins</div> -->
						<div class="du-layout-type-filter" data-layout_type="snippet">Snippets</div>
			        </div>';
	}

	echo '        <div class="du-tab"></div>
				</div>';

	echo '<script> jQuery(document).ready(function() {';

	if ( DU_DEFAULT_VIEW == 'my-cloud' ) {
		echo ' du_my_cloud(); ';
	} else {
		echo ' du_populate_frame(); ';
	}

	echo '}); </script>';

	//echo du_box_end();

	echo '</div>';

	////////////////
	echo '</div>'; //poststuff
	echo '</div>';
	echo '</div>';
}

function du_submenu_mc() {
	$key         = get_option( 'du_api_key' );
	$key_active  = get_option( 'du_api_key_active' );
	$white_label = get_option( 'du_white_labelling', array() );

	if ( ! $key && ! $key_active ) {
		echo '<script>window.location="' . admin_url( 'admin.php?page=layouts_cloud' ) . '";</script>';
		die; //redirecting to settings page
	}

	echo '<div class="wrap">
					<h2><span style="width: 30px; height: 30px; font-size: 30px;" class="dashicons dashicons-cloud"></span> ' . DU_NAME . ' - V' . DU_VERSION . '</h2>';

	echo '<div id="poststuff2">
					<div id="post-body" class="metabox-holder columns-2">';
	////////////////

	echo '<div style="clear: both;">';

	//echo du_box_start('Divi Cloud');

	echo '<div class="du-gallery">
					<div class="du" data-layout_type="layout"></div>';

	echo '        <div class="du-tab"></div>
				</div>';

	echo '<script> jQuery(document).ready(function() { du_my_cloud(); }); </script>';

	//echo du_box_end();

	echo '</div>';

	////////////////
	echo '</div>'; //poststuff
	echo '</div>';
	echo '</div>';
}

function du_submenu_cb() {
	global $du_remote_site;

	$is_premium = false;

	echo '<div class="wrap">
								<h2><span style="width: 30px; height: 30px; font-size: 30px;" class="dashicons dashicons-cloud"></span> ' . DU_NAME . ' - V' . DU_VERSION . '</h2>';

	echo '<div id="poststuff">
								<div id="post-body" class="metabox-holder columns-2">';

	if ( isset( $_POST['du_edit_wl_submit'] ) ) {
		$_POST['du_white_labelling']['saved_user'] = get_current_user_id();

		update_option( 'du_white_labelling', $_POST['du_white_labelling'] );

		if ( isset( $_POST['du_white_labelling']['disable_dc'] ) ) {
			update_option( 'du_settings', array( 'default_view' => 'my-cloud' ) );
		}

		echo '<div class="notice notice-success is-dismissible">
													<p>The white labelling settings have been saved. To revert back to normal please clear the white labelling fields.</p>
											</div>';
	}
	if ( isset( $_POST['du_edit_wl_delete_submit'] ) ) {
		delete_option( 'du_white_labelling' );

		echo '<div class="notice notice-success is-dismissible">
													<p>The white labelling settings have been removed.</p>
											</div>';
	}

	if ( isset( $_POST['du_edit_submit'] ) ) {
		update_option( 'du_settings', @$_POST['du_settings'] );
		update_option( 'du_api_key', @$_POST['du_api_key'] );

		echo '<div id="message" class="updated fade">
                    <p>Settings Saved Successfully</p>
                </div>';
	}

	$key = get_option( 'du_api_key' );

	//test mode
	if ( isset( $_GET['test'] ) && $_GET['test'] == 1 ) {
		$url = $du_remote_site . '?du_api_key=' . $key . '&du_domain=' . urlencode( site_url() ) . '&du_action=test';

		if ( $remote = wp_remote_get( $url, array( 'timeout' => 90 ) ) ) {
			echo $url;
			echo '<pre>';
			print_r( $remote );
			echo '</pre>';
		}
	}
	//end test mode

	if ( $account = du_account_info( $key ) ) {
		if ( ! isset( $account['error'] ) || $account['error'] == '' ) {
			if ( $account['name'] ) { //if we can link their account to a name then it's technically valid.
				update_option( 'du_api_key_active', 1 );

				echo '<div class="notice notice-success is-dismissible">
													<p>Your API Key is currently Active. You can now use the ' . DU_NAME . ' interface within the Divi Bulder</p>
											</div>';
			} else {
				update_option( 'du_api_key_active', 0 );
			}
		}
	}

	echo '<p>Use this page to enter your details for the ' . DU_NAME . ' site. Once you have added your API key then the system will begin to function and layouts can be loaded.</p>';

	echo '<form method="POST">';

	echo '<div style="clear: both;">';

	echo du_box_start( 'API Key' );

	echo '<label>
<input style="padding: 6px; width: 500px;" placeholder="Enter your API Key" type="password" name="du_api_key" value="' . $key . '" />
</label>';

	echo '<input type="submit" name="du_edit_submit" class="button-primary" value="Save Settings" style="margin-left: 20px;" />';

	echo '<p>
<small>You can get your API Key from the Layouts Cloud website within the "my account" area.</small>
</p>';

	echo du_box_end();

	if ( $key ) {

		echo du_box_start( 'Account Information' );

		if ( $account ) {
			//echo '<pre>';
			//print_r($account);
			//echo '</pre>';

			if ( ! isset( $account['error'] ) || $account['error'] == '' ) {

				echo '<table class="form-table">';

				echo '<tr>
											<th>Name</th>
											<td>' . $account['name'] . '</td>
										</tr>';

				echo '<tr>
											<th>Email</th>
											<td>' . $account['email'] . '</td>
										</tr>';

				echo '<tr>
											<th>Membership Level(s)</th>
											<td>';

				$mm = false;

				if ( count( $account['memberships'] ) > 1 ) {
					$mm = true; //multiple memberships

					echo '<ul>';
				}

				if ( $account['memberships'] ) {
					foreach ( $account['memberships'] as $name => $expiry ) {
						if ( in_array( strtolower( $name ), array( 'freelancer', 'agency' ) ) ) {
							$is_premium = true;
						}

						if ( $mm ) {
							echo '<li>';
						}

						echo '<strong>' . $name . '</strong>';

						if ( $expiry != '-' ) {
							echo ' <em>
	<small>(Expires ' . $expiry . ')</small>
	</em>';
						}

						if ( $mm ) {
							echo '</li>';
						}
					}
				}

				if ( $mm ) {
					echo '</ul>';
				}

				echo '<p>
<a class="button-primary" href="https://layoutscloud.com/product/membership-upgrade/" target="_blank">View Upgrade Options</a>
</p>';

				echo '	</td>
										</tr>';

				echo '</table>';

			} else {
				echo '<p style="color: #FF9999; font-size: 20px;"><strong>It looks like there is a problem connecting to the Layouts Cloud server. Don\'t panic though!</strong></p>
										<p>If the message below mentions "SSL" then the easiest solution is to speak to your web host and pass the error below. Layouts Cloud used a very common system called cURL to communicate layouts etc. Sometimes your site will have an old version of this and it may not work.</p>
										<p>If you ask your web host to upgrade cURL to the latest stable version this will fix the issue. If it does not then please contact us to resolve things.</p>
										<p>A simple test of the system for your web host is for them to access the following URL from the server. If it is possible then ' . DU_NAME . ' will function properly: ' . $du_remote_site . '?du_api_key=' . $key . '&du_action=test</p>';

				echo '<p style="font-size: 20px;">' . $account['error'] . '</p>';
			}
		} else {
			echo '<p>Sorry but your API Key is not valid or there was an error. Please enter another or contact ' . DU_NAME . ' for help</p>';
		}

		echo du_box_end();

		$white_label = get_option( 'du_white_labelling', array() );

		/////////////////////////////////////////////////////////////////

		if ( $is_premium ) {
			if ( ! isset( $white_label['saved_user'] ) || ( @$white_label['saved_user'] && get_current_user_id() == $white_label['saved_user'] ) || isset( $_GET['du_debug'] ) ) {
				$settings = get_option( 'du_settings', array() );
				echo du_box_start( 'General Settings' );

				echo '<p><label style="display: inline-block; width: 250px;">Default View</label>
                <select name="du_settings[default_view]" style="padding: 6px; width: 250px;">
                    <option value="layouts" ' . ( @$settings['default_view'] == 'layouts' ? 'selected="selected"' : '' ) . '>All Layouts</option>
                    <option value="my-cloud" ' . ( @$settings['default_view'] == 'my-cloud' ? 'selected="selected"' : '' ) . '>My Cloud</option>
                </select></p>';

				echo '<input type="submit" name="du_edit_submit" class="button-primary" value="Save Settings" />';

				echo du_box_end();
			}
		}

		/////////////////////////////////////////////////////////////////

		echo du_box_start( 'White Labelling' );

		if ( ! isset( $white_label['saved_user'] ) || ( @$white_label['saved_user'] && get_current_user_id() == $white_label['saved_user'] ) || isset( $_GET['du_debug'] ) ) {

			echo '<p>This plugin can be white labelled meaning that you can tune the name of the plugin and author information to suit your needs. Fill in the boxes below and references to the plugin throughout the site will change accordingly.</p>';

			echo '<table class="form-table">
<colgroup><col width="25%" /></colgroup>';

			echo '<tr>
                            <th>Plugin Name</th>
                            <td>';

			echo '<label>
                    <input style="padding: 6px; width: 500px;" placeholder="Divi Cloud" type="text" name="du_white_labelling[plugin_name]" value="' . @$white_label['plugin_name'] . '" />
                    </label>';

			echo '</td>
                        </tr>
                        <tr>
                            <th>Plugin URL</th>
                            <td>';

			echo '<label>
                    <input style="padding: 6px; width: 500px;" placeholder="https://www.layoutscloud.com" type="text" name="du_white_labelling[plugin_url]" value="' . @$white_label['plugin_url'] . '" />
                    </label>';

			echo '</td>
                        </tr>
                        <tr>
                            <th>Plugin Author</th>
                            <td>';

			echo '<label>
                    <input style="padding: 6px; width: 500px;" placeholder="Elegant Marketplace" type="text" name="du_white_labelling[plugin_author]" value="' . @$white_label['plugin_author'] . '" />
                    </label>';

			echo '</td>
                        </tr>
                        <tr>
                            <th>Author URL</th>
                            <td>';

			echo '<label>
                    <input style="padding: 6px; width: 500px;" placeholder="https://www.elegantmarketplace.com" type="text" name="du_white_labelling[plugin_author_url]" value="' . @$white_label['plugin_author_url'] . '" />
                    </label>';

			echo '</td>
                        </tr>
                        <tr>
                            <th>Plugin Description</th>
                            <td>';

			echo '<label>
                    <textarea style="padding: 6px; height: 200px; width: 500px;" name="du_white_labelling[plugin_desc]">' . @$white_label['plugin_desc'] . '</textarea>
                    </label>';

			echo '</td></tr>
                        <tr>
                            <th>Disable Community Cloud?</th>
                            <td>';

			echo '<p><label><input type="checkbox" name="du_white_labelling[disable_cc]" ' . ( isset( $white_label['disable_cc'] ) ? 'checked="checked"' : '' ) . ' value="1" /></label></p>';

			echo '</td></tr>
                        <tr>
                            <th>Disable Divi Cloud Layouts?</th>
                            <td>';

			echo '<p><label><input type="checkbox" name="du_white_labelling[disable_dc]" ' . ( isset( $white_label['disable_dc'] ) ? 'checked="checked"' : '' ) . ' value="1" /></label></p>';

			echo '</td></tr>
                        <tr>
                            <th>"My Cloud" Subsets?</th>
                            <td>';

			if ( $layouts = du_get_my_cloud() ) {
				if ( isset( $layouts['categories'] ) ) {
					foreach ( $layouts['categories'] as $cat_name => $cat_label ) {
						echo '<p><label><input type="checkbox" name="du_white_labelling[cats][' . $cat_name . ']" ' . ( isset( $white_label['cats'][ $cat_name ] ) ? 'checked="checked"' : '' ) . ' value="' . $cat_name . '" />&nbsp;' . $cat_label . '</label></p>';
					}
				} else {
					echo '<p>When you start categorising your own cloud layouts, the categories will appear here for you to choose from.</p>';
				}
			}

			echo '<p><small>Using this feature you can make sure that the "My Cloud" section of the Divi Builder will ONLY show layouts from certain categories.</small></p>';


			echo '</td></tr>
                    </table>';

			echo '<p>';

			echo '<input type="submit" name="du_edit_wl_submit" class="button-primary" value="Save White labelling Settings" />';

			if ( isset( $white_label['saved_user'] ) ) {
				echo '<input type="submit" name="du_edit_wl_delete_submit" class="button-secondary" style="margin-left: 10px;" value="Delete White labelling Configuration" />';
			}

			echo '</p>';
		} else {
			if ( @$white_label['saved_user'] ) {
				if ( $saved_user = get_userdata( @$white_label['saved_user'] ) ) {
					echo '<p>This plugin has been white labelled. Only the user who set this up can edit it. Please login as ' . $saved_user->user_login . ' and revisit this page.</p>';
				} else {
					delete_option( 'du_white_labelling' ); //delete db code
					echo '<p>This plugin has been white labelled. However, it has now been reset to defaults as the user who set it up has been deleted from your site. </p>';
				}
			}
		}

		echo du_box_end();

	}

	echo '</div>';

	echo '</form>';

	echo '</div>';
	echo '</div>';

	echo '</div>';
}

function du_box_start( $title, $width = false, $float = 'left' ) {
	return '<div class="postbox" style="' . ( $width ? 'float: ' . $float . '; margin-bottom: 20px; width: ' . $width : 'clear: both;' ) . '">
								<h2 class="hndle">' . $title . '</h2>
								<div class="inside" style="clear: both;">';
}

function du_box_end() {
	return '    <div style="display: table; clear: both;">&nbsp;</div>
</div>
						</div>';
}

function du_admin_general_scripts() {
	$css_url = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'admin-general.css';
	wp_enqueue_style( 'du_admin_general_css', $css_url );
}

function du_conditional_admin_scripts() {
	if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'du_submenu_mc', 'du_admin_home' ) ) ) {
		du_admin_scripts();
	}
}

function du_admin_scripts() {
	global $du_remote_site;

	$js_url        = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'script.js';
	$iframe_js_url = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'cbox-theme/jquery.colorbox-min.js';

	$css_url        = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'admin.css';
	$iframe_css_url = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'cbox-theme/colorbox.css';

	wp_enqueue_script( 'du_admin_js', $js_url );

	$translation_array = array(
		'plugin_name'  => DU_NAME,
		'default_view' => DU_DEFAULT_VIEW,
	);

	wp_localize_script( 'du_admin_js', 'du_constants', $translation_array );

	wp_enqueue_style( 'du_admin_css', $css_url );

	wp_enqueue_script( 'du_cbox_admin_js', $iframe_js_url );
	wp_enqueue_style( 'du_cbox_admin_css', $iframe_css_url );

	$key_present = 0;
	$key         = '';
	if ( get_option( 'du_api_key' ) && get_option( 'du_api_key_active' ) ) {
		$key_present = 1;
		$key         = get_option( 'du_api_key' );
	}

	echo '<script>
						var du_api_key_present = ' . $key_present . ';
						var du_api_key = "' . $key . '";
						var du_site_url = "' . trailingslashit( site_url() ) . '";
						var du_remote_site_url = "' . $du_remote_site . '";
					</script>';
}

function du_enqueue_scripts() {
	$css_url = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'style.css';

	wp_enqueue_script( 'jquery' );
	wp_enqueue_style( 'du_css', $css_url );
}

?>