<?php
/**
 * Widget
 *
 * @package    widget
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Adding required files.
 */
require 'class-mooauth-debug.php';

/**
 * [Add Widget Functionality]
 */
class MOOAuth_Widget extends WP_Widget {

	/**
	 * Initialzie widget parameters.
	 */
	public function __construct() {
		update_option( 'host_name', 'https://login.xecurify.com' );
		add_action( 'wp_enqueue_scripts', array( $this, 'mo_oauth_register_plugin_styles' ) );
		add_action( 'init', array( $this, 'mo_oauth_start_session' ) );
		add_action( 'wp_logout', array( $this, 'mo_oauth_end_session' ) );
		add_action( 'login_form', array( $this, 'mo_oauth_wplogin_form_button' ) );
		parent::__construct( 'mooauth_widget', MO_OAUTH_ADMIN_MENU, array( 'description' => __( 'Login to Apps with OAuth', 'flw' ) ) );

	}

	/**
	 * Enqueue CSS for widget
	 */
	public function mo_oauth_wplogin_form_style() {

		wp_enqueue_style( 'mo_oauth_fontawesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ), array(), '4.7.0' );
		wp_enqueue_style( 'mo_oauth_wploginform', plugins_url( 'css/login-page.min.css', __FILE__ ), array(), MO_OAUTH_CSS_JS_VERSION );
	}

	/**
	 * Display Login widget
	 */
	public function mo_oauth_wplogin_form_button() {
		$appslist = get_option( 'mo_oauth_apps_list' );
		if ( is_array( $appslist ) && count( $appslist ) > 0 ) {
			$this->mo_oauth_load_login_script();
			foreach ( $appslist as $key => $app ) {

				if ( isset( $app['show_on_login_page'] ) && 1 === $app['show_on_login_page'] ) {

					$this->mo_oauth_wplogin_form_style();

					echo '<br>';
					echo '<h4>Connect with :</h4><br>';
					echo '<div class="row">';

					$logo_class = $this->mo_oauth_client_login_button_logo( $app['appId'] );

					echo '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\'' . esc_attr( $key ) . '\');"><div class="mo_oauth_login_button mo_oauth_login_button_text"><i class="' . esc_attr( $logo_class ) . ' mo_oauth_login_button_icon"></i>Login with ' . esc_attr( ucwords( $key ) ) . '</div></a>';
					echo '</div><br><br>';
				}
			}
		}
	}

	/**
	 * Get logo class for the configured app.
	 *
	 * @param mixed $current_app_id current app for which the logo needs to be displayed.
	 */
	public function mo_oauth_client_login_button_logo( $current_app_id ) {
		$currentapp = mooauth_client_get_app( $current_app_id );
		$logo_class = $currentapp->logo_class;
		return $logo_class;
	}

	/**
	 * Redirect to SSO after clicking on button
	 */
	public function mo_oauth_start_session() {
		if ( ! session_id() && ! mooauth_client_is_ajax_request() && ! mooauth_client_is_rest_api_call() ) {
			session_start();
		}

		if ( isset( $_REQUEST['option'] ) && sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ) === 'testattrmappingconfig' ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			$mo_oauth_app_name = ! empty( $_REQUEST['app'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['app'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			wp_safe_redirect( site_url() . '?option=oauthredirect&app_name=' . rawurlencode( $mo_oauth_app_name ) . '&test=true' );
			exit();
		}

	}

	/**
	 * Destroy user session.
	 */
	public function mo_oauth_end_session() {
		if ( ! session_id() ) {
			session_start();
		}
		session_destroy();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @param mixed $args Display arguments including 'before_title', 'after_title',
	 *                         'before_widget', and 'after_widget'..
	 * @param mixed $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$wid_title = '';
		if ( ! empty( $instance['wid_title'] ) ) {
			$wid_title = $instance['wid_title'];
		}
		$wid_title = apply_filters( 'widget_title', $wid_title );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_widget'] is html that needs to render on dom escaping will not render html.
		if ( ! empty( $wid_title ) ) {
			echo esc_attr( $args['before_title'] ) . esc_html( $wid_title ) . esc_attr( $args['after_title'] );
		}
		$this->mo_oauth_login_form();
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['after_widget'] is html that needs to render on dom escaping will not render html.
	}

	/**
	 * MiniOrange method to override parent method to update a particular instance of a widget.
	 *
	 * @param mixed $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param mixed $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		if ( isset( $new_instance['wid_title'] ) ) {
			$instance['wid_title'] = wp_strip_all_tags( $new_instance['wid_title'] );
		}

		return $instance;
	}

	/**
	 * Display login widget content.
	 */
	public function mo_oauth_login_form() {
		global $post;
		$this->mo_oauth_error_message();
		$appslist = get_option( 'mo_oauth_apps_list' );
		if ( $appslist && count( $appslist ) > 0 ) {
			$apps_configured = true;
		}

		if ( ! is_user_logged_in() ) {

			if ( isset( $apps_configured ) && $apps_configured ) {

				$this->mo_oauth_wplogin_form_style();
				$this->mo_oauth_load_login_script();

				$style      = get_option( 'mo_oauth_icon_width' ) ? 'width:' . get_option( 'mo_oauth_icon_width' ) . ';' : '';
				$style     .= get_option( 'mo_oauth_icon_height' ) ? 'height:' . get_option( 'mo_oauth_icon_height' ) . ';' : '';
				$style     .= get_option( 'mo_oauth_icon_margin' ) ? 'margin:' . get_option( 'mo_oauth_icon_margin' ) . ';' : '';
				$custom_css = get_option( 'mo_oauth_icon_configure_css' );
				if ( empty( $custom_css ) ) {
					echo '<style>.oauthloginbutton{background: #7272dc;height:40px;padding:8px;text-align:center;color:#fff;}</style>';
				} else {
					echo '<style>' . esc_html( $custom_css ) . '</style>';
				}

				if ( is_array( $appslist ) ) {
					foreach ( $appslist as $key => $app ) {
						$logo_class = $this->mo_oauth_client_login_button_logo( $app['appId'] );

						echo '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\'' . esc_attr( $key ) . '\');"><div class="mo_oauth_login_button_widget"><i class="' . esc_attr( $logo_class ) . ' mo_oauth_login_button_icon_widget"></i><h3 class="mo_oauth_login_button_text_widget">Login with ' . esc_attr( ucwords( $key ) ) . '</h3></div></a>';
					}
				}
			} else {
				echo '<div>No apps configured.</div>';
			}
		} else {
			$current_user       = wp_get_current_user();
			$link_with_username = __( 'Howdy, ', 'flw' ) . $current_user->display_name;
			echo '<div id="logged_in_user" class="login_wid">
			<li>' . esc_attr( $link_with_username ) . ' | <a href="' . esc_url( wp_logout_url( site_url() ) ) . '" >Logout</a></li>
		</div>';

		}
	}

	/**
	 * Load login script
	 */
	private function mo_oauth_load_login_script() {
		?>
	<script type="text/javascript">

		function HandlePopupResult(result) {
			window.location.href = result;
		}

		function moOAuthLoginNew(app_name) {
			window.location.href = '<?php echo esc_attr( site_url() ); ?>' + '/?option=oauthredirect&app_name=' + app_name;
		}
	</script>
		<?php
	}

	/**
	 * Display Error message
	 */
	public function mo_oauth_error_message() {
		if ( isset( $_SESSION['msg'] ) && $_SESSION['msg'] ) {
			echo '<div class="' . esc_attr( $_SESSION['msg_class'] ) . '">' . esc_attr( $_SESSION['msg'] ) . '</div>';
			unset( $_SESSION['msg'] );
			unset( $_SESSION['msg_class'] );
		}
	}

	/**
	 * Register Plugin styles.
	 */
	public function mo_oauth_register_plugin_styles() {
		wp_enqueue_style( 'style_login_widget', plugins_url( 'css/style_login_widget.min.css', __FILE__ ), array(), MO_OAUTH_CSS_JS_VERSION );
	}
}

/**
 * Update email as username attribute.
 *
 * @param mixed $currentappname Current SSO app name.
 */
function mooauth_update_email_to_username_attr($currentappname) {
	$appslist                                     = get_option( 'mo_oauth_apps_list' );
	$appslist[ $currentappname ]['username_attr'] = $appslist[ $currentappname ]['email_attr'];
	update_option( 'mo_oauth_apps_list', $appslist );
}

function get_username_attr($app, $appname) {
	$username_attr = $app['username_attr'];
	$email_attr = $app['email_attr'];
	if ((!isset($username_attr) || empty(!$username_attr)) && isset($email_attr)) {
		mooauth_update_email_to_username_attr($appname);
		$username_attr = $email_attr;
	}

	return $username_attr;
}

function get_user_attr($app, $key) {
	return $app[$key] ? $app[$key] : $key;
}

function validating_username($username) {
	if (empty($username) || '' === $username) {
		
		exit('Username not received. Check your <b>Attribute Mapping</b> configuration.');
	}

	if (!is_string($username)) {
		
		wp_die('Username is not a string. It is '.esc_html(mooauth_client_get_proper_prefix(gettype($username))));
	}

	if (strlen($username) > 60) {
		
		wp_die( 'You are not allowed to login. Please contact your administrator' );
	}

	if (preg_match('/[+,\/~!#$%^&*():={}|;">?\/\\\\\/\\\\\']/', $username)) {
		
		wp_die( 'You are not allowed to login. Please contact your administrator' );
	}
}

function build_user_info($appname, $app, $resource_owner) {
	$username_attr    = get_username_attr($app, $appname);
	$firstname_attr   = get_user_attr($app, 'given_name');
	$lastname_attr    = get_user_attr($app, 'family_name');
	$displayname_attr = get_user_attr($app, 'name');
	$email_attr       = get_user_attr($app, 'email');
	$groups_attr      = get_user_attr($app, 'groups');

	$username = mooauth_client_getnestedattribute($resource_owner, $username_attr);
	$firstname = mooauth_client_getnestedattribute($resource_owner, $firstname_attr);
	$lastname = mooauth_client_getnestedattribute($resource_owner, $lastname_attr);
	$displayname = mooauth_client_getnestedattribute($resource_owner, $displayname_attr);
	$email = mooauth_client_getnestedattribute($resource_owner, $email_attr);
	$groups = mooauth_client_getnestedattribute($resource_owner, $groups_attr);
	$clientid = $app['clientid'];

	$userInfo = array();
	if (!empty($username)) {
		$userInfo['login'] = $username;
	}
	if (!empty($firstname)) {
		$userInfo['first_name'] = $firstname;
	}
	if (!empty($lastname)) {
		$userInfo['last_name'] = $lastname;
	}
	if (!empty($displayname)) {
		$userInfo['display_name'] = $displayname;
	}
	if (!empty($email)) {
		$userInfo['email'] = $email;
	}
	if (!empty($groups)) {
		$userInfo['groups'] = $groups;
	}
	if (!empty($clientid)) {
		$userInfo['clientid'] = $clientid;
	}

	return $userInfo;
}

function get_logined_user($appname, $app, $resource_owner) {
	$user_info = build_user_info($appname, $app, $resource_owner);
	$username = $user_info['login'];
	validating_username($username);

	$user = get_user_by('login', $username);
	if ($user) {
		$user_info['id'] = $user->ID;
	} else {
		$user_info['id'] = 0;
		if (mooauth_migrate_customers()) {
			$user = mooauth_looped_user($user_info);
		} else {
			$user = mooauth_handle_user_registration($user_info);
		}
	}

	return $user;
}

/**
 * Main SSO flow.
 */
function mooauth_login_validate() {
	$mo_oauth_handler = new MO_OAuth_Handler();
	$requestUri = $mo_oauth_handler->sanitize_unslash_text($_SERVER['REQUEST_URI']);
	
	if (isset($_REQUEST['option'])) {
		$requestOption = $mo_oauth_handler->sanitize_unslash_text($_REQUEST['option']);
	}
	
	/* Handle Authorize request */
	if (isset($requestOption) && strpos($requestOption, 'oauthredirect') !== false ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		$appname  = ! empty( $_REQUEST['app_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['app_name'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		$appslist = get_option( 'mo_oauth_apps_list' );
		if ( isset( $_REQUEST['redirect_url'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			update_option( 'mo_oauth_redirect_url', sanitize_text_field( wp_unslash( $_REQUEST['redirect_url'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		}

		if ( isset( $_REQUEST['test'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			setcookie( 'mo_oauth_test', true, time() + 3600, '/', '', true, true );
		} else {
			setcookie( 'mo_oauth_test', false, time() + 3600, '/', '', true, true );
		}

		if ( false === $appslist ) {
			
			exit( 'Looks like you have not configured OAuth provider, please try to configure OAuth provider first' );
		}

		foreach ( $appslist as $key => $app ) {

			if ( $appname === $key && ( isset( $app['send_state'] ) !== true || $app['send_state'] | 'oauth1' === $app['appId'] || 'twitter' === $app['appId'] ) ) {

				if ( 'twitter' === $app['appId'] || 'oauth1' === $app['appId'] ) {
					include 'class-mo-oauth-custom-oauth1.php';
					setcookie( 'tappname', $appname, time() + 3600, '/', '', true, true );
					$setcookie = ! empty( $_COOKIE['tappname'] ) ? MO_OAuth_Custom_OAuth1::mo_oauth1_auth_request( sanitize_text_field( wp_unslash( $_COOKIE['tappname'] ) ) ) : '';
					exit();
				}

				$state             = base64_encode( $appname ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Base64 encode will be required for fetching appname from state.
				$authorization_url = $app['authorizeurl'];

				if ( strpos( $authorization_url, '?' ) !== false ) {
					$authorization_url = $authorization_url . '&client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code&state=' . $state;
				} else {
					$authorization_url = $authorization_url . '?client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code&state=' . $state;
				}

				if ( strpos( $authorization_url, 'apple' ) !== false ) {
					$authorization_url = str_replace( 'response_type=code', 'response_type=code+id_token', $authorization_url );
					$authorization_url = $authorization_url . '&response_mode=form_post';
				}

				if ( session_id() === '' || ! isset( $_SESSION ) ) {
					session_start();
				}
				$_SESSION['oauth2state'] = $state;
				$_SESSION['appname']     = $appname;

				
				header( 'Location: ' . $authorization_url );
				exit;
			} else {
				$state             = null;
				$authorization_url = $app['authorizeurl'];

				if ( strpos( $authorization_url, '?' ) !== false ) {
					$authorization_url = $authorization_url . '&client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code';
				} else {
					$authorization_url = $authorization_url . '?client_id=' . $app['clientid'] . '&scope=' . $app['scope'] . '&redirect_uri=' . $app['redirecturi'] . '&response_type=code';
				}

				if ( session_id() === '' || ! isset( $_SESSION ) ) {
					session_start();
				}
				$_SESSION['oauth2state'] = $state;
				$_SESSION['appname']     = $appname;

				
				header( 'Location: ' . $authorization_url );
				exit;
			}
		}
	} elseif ((!empty($requestUri) && strpos($requestUri, 'openidcallback') !== false) || (strpos($requestUri, 'oauth_token') !== false) && (strpos($requestUri, 'oauth_verifier'))) {
		$currentappname = $mo_oauth_handler->get_current_app_name();
		$currentapp     = $mo_oauth_handler->get_app_by_name($currentappname);

		$resource_owner = MO_OAuth_Custom_OAuth1::mo_oidc1_get_access_token($currentappname);
		update_option( 'mo_oauth_attr_name_list', $resource_owner );
		// Test Configuration.
		if ( isset( $_COOKIE['mo_oauth_test'] ) && sanitize_text_field( wp_unslash( $_COOKIE['mo_oauth_test'] ) ) ) {
			setcookie( 'mo_oauth_test', false, time() + 3600, '/', '', true, true );
			echo '<div style="font-family:Calibri;padding:0 3%;color:012970;">';
			echo '<style>table{border-collapse:collapse;color:#012970;}th{background-color: #c6d8f6bd; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#012970;}tr:nth-child(odd) {background-color: #e4eeff;}td{padding:8px;border-width:1px; border-style:solid; border-color:#012970;word-break: break-all;}</style>';
			echo '<h2>Test Configuration</h2><table><tr><th>Attribute Name</th><th>Attribute Value</th></tr>';
			mooauth_client_testattrmappingconfig( '', $resource_owner );
			echo '</table>';
			echo '<div style="padding: 10px;"></div><input style="padding:7px 12px;width:100px;background: #012970 none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA; inset;color: #FFF;"type="button" value="Done" onClick="self.close();">&emsp;';
			echo '</div>';
			exit();
		}

		$user = get_logined_user($currentappname, $currentapp, $resource_owner);
		if ($user) {
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID );
			$user = get_user_by( 'ID', $user->ID );
			do_action( 'wp_login', $user->user_login, $user );

			$redirect_to = get_option( 'mo_oauth_redirect_url' );

			if ( false === $redirect_to ) {
				$redirect_to = home_url();
			}

			wp_safe_redirect( $redirect_to );
			exit;
		}
	} elseif (strpos($requestUri, '/wp-json/moserver/token') === false && !isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strpos($requestUri, '/oauthcallback') !== false || isset($_REQUEST['code']))) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
		if ( session_id() === '' || ! isset( $_SESSION ) ) {
			session_start();
		}

		if ( ! isset( $_REQUEST['code'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			if ( isset( $_REQUEST['error_description'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
				
				exit( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['error_description'] ) ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			} elseif ( isset( $_REQUEST['error'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
				
				exit( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
			}
			
			exit( 'Invalid response' );
		} else {
			// exit from our control when user is already logged in. This it to prevent the issue with Ecwid Ecommerce plugin.
			if ( is_user_logged_in() && ! isset( $_COOKIE['mo_oauth_test'] ) ) {
				return;
			}

			try {
				$currentappname   = $mo_oauth_handler->get_current_app_name();
				if ( empty( $currentappname ) ) {
					
					return;
				}

				$currentapp = $mo_oauth_handler->get_app_by_name($currentappname);
				if ( ! $currentapp ) {
					
					exit( 'Application not configured.' );
				}

				$resource_owner_details_url = $currentapp['resourceownerdetailsurl'];
				
				if ( isset( $currentapp['apptype'] ) && 'openidconnect' === $currentapp['apptype'] ) {
					// OpenId connect.
					
					$code = ! empty( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
					if ( isset( $_REQUEST['id_token'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
						$id_token = sanitize_text_field( wp_unslash( $_REQUEST['id_token'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.
					} else {
						if ( ! isset( $currentapp['send_headers'] ) ) {
							$currentapp['send_headers'] = false;
						}
						if ( ! isset( $currentapp['send_body'] ) ) {
							$currentapp['send_body'] = false;
						}
						$token_response = $mo_oauth_handler->get_id_token(
							$currentapp['accesstokenurl'],
							'authorization_code',
							$currentapp['clientid'],
							$currentapp['clientsecret'],
							$code,
							$currentapp['redirecturi'],
							$currentapp['send_headers'],
							$currentapp['send_body']
						);

						$id_token = isset( $token_response['id_token'] ) ? $token_response['id_token'] : $token_response['access_token'];

					}

					if ( ! $id_token ) {
						
						exit( 'Invalid token received.' );
					} else {
						
						
						$resource_owner = $mo_oauth_handler->get_resource_owner_from_id_token( $id_token );
						
					}
				} else {
					
					$access_token_url = $currentapp['accesstokenurl'];
					if ( ! isset( $currentapp['send_headers'] ) ) {
						$currentapp['send_headers'] = false;
					}
					if ( ! isset( $currentapp['send_body'] ) ) {
						$currentapp['send_body'] = false;
					}

					$access_token = $mo_oauth_handler->get_access_token( $access_token_url, 'authorization_code', $currentapp['clientid'], $currentapp['clientsecret'], sanitize_text_field( wp_unslash( $_GET['code'] ) ), $currentapp['redirecturi'], $currentapp['send_headers'], $currentapp['send_body'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignoring nonce verification because we are fetching data from URL and not on form submission.

					if ( ! $access_token ) {
						
						exit( 'Invalid token received.' );
					}

					if ( substr( $resource_owner_details_url, -1 ) === '=' ) {
						$resource_owner_details_url .= $access_token;
					}
					
					$resource_owner = $mo_oauth_handler->get_resource_owner( $resource_owner_details_url, $access_token );
					
					
				}

				update_option( 'mo_oauth_attr_name_list', $resource_owner );
				// Test Configuration.
				if ( isset( $_COOKIE['mo_oauth_test'] ) && sanitize_text_field( wp_unslash( $_COOKIE['mo_oauth_test'] ) ) ) {
					setcookie( 'mo_oauth_test', false, time() + 3600, '/', '', true, true );
					echo '<div style="font-family:Calibri;padding:0 3%;color:012970;">';
					echo '<style>table{border-collapse:collapse;color:#012970;}th{background-color: #c6d8f6bd; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#012970;}tr:nth-child(odd) {background-color: #e4eeff;}td{padding:8px;border-width:1px; border-style:solid; border-color:#012970;word-break: break-all;}</style>';
					echo '<h2>' . esc_html__( 'Test Configuration', 'miniorange-login-with-eve-online-google-facebook' ) . '</h2><table><tr><th>' . esc_attr__( 'Attribute Name', 'miniorange-login-with-eve-online-google-facebook' ) . '</th><th>' . esc_attr__( 'Attribute Value', 'miniorange-login-with-eve-online-google-facebook' ) . '</th></tr>';
					mooauth_client_testattrmappingconfig( '', $resource_owner );
					$app = array_values( get_option( 'mo_oauth_apps_list' ) )[0];
					if ( isset( $app['username_attr'] ) ) {
						$username_attr_mapping = $app['username_attr'];
					} else {
						$username_attr_mapping = false;
					}
					echo '</table>';
					echo '<div style="padding: 10px;"></div><input style="padding:7px 12px;width:100px;background: #012970 none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA; inset;color: #FFF;"type="button" value="Done" onClick="self.close();">&emsp;';
					echo '</div>';

					exit();
				}

				$user = get_logined_user($currentappname, $currentapp, $resource_owner);
				if ($user) {
					wp_set_current_user( $user->ID );
					wp_set_auth_cookie( $user->ID );

					if (!empty($id_token)) {
						
						update_user_meta( $user->ID, 'id_token', $id_token );
					}

					if (!empty($access_token)) {
						
						update_user_meta( $user->ID, 'access_token', $access_token );
					}

					$redirect_to = get_option( 'mo_oauth_redirect_url' );
					if ( has_action( 'mo_hack_login_session_redirect' ) ) {
						$token    = mooauth_gen_rand_str();
						$password = mooauth_gen_rand_str();
						$config   = array(
							'user_id'       => $user->ID,
							'user_password' => $password,
						);
						set_transient( $token, $config );
						do_action( 'mo_hack_login_session_redirect', $user, $password, $token, $redirect_to );
					}
					$user = get_user_by( 'ID', $user->ID );
					do_action( 'wp_login', $user->user_login, $user );

					if ( false === $redirect_to ) {
						$redirect_to = home_url();
					}

					wp_safe_redirect( $redirect_to );
					exit;
				}
			} catch ( Exception $e ) {

				// Failed to get the access token or user details.

				
				exit( esc_attr( $e->getMessage() ) );

			}
		}
	}
}

function build_user_model($user_id, $email, $display_name) {
	$user_model = array('ID' => $user_id);
	if (!empty($email)) {
		$user_model['user_email'] = $email;
	}
	if (!empty($display_name)) {
		$user_model['display_name'] = $display_name;
	}

	return $user_model;
}

function is_not_empty_array($arrs) {
	return isset($arrs) && is_array($arrs) &&  count($arrs) > 0;
}

function is_manager_user($email, $groups, $clientid) {
	
	
	if (!isset($email) || !isset($groups) || !isset($clientid) || !is_array($groups)) {
		
		return false;
	}

	
	$group_path = "/".$email."/".$clientid;
	
	$filtered_groups = array_filter($groups, function ($value, $index) use ($group_path) {
		return $group_path === $value;
	}, ARRAY_FILTER_USE_BOTH);
	

	return is_not_empty_array($filtered_groups);
}

function update_user_info($user_id, $userInfo) {
	if (!empty($userInfo['first_name'])) {
		update_user_meta($user_id, 'first_name', $userInfo['first_name']);
	}
	if (!empty($userInfo['last_name'])) {
		update_user_meta($user_id, 'last_name', $userInfo['last_name']);
	}
}

/**
 * Adding manager role if it does not exit
 */
function add_manager_role() {
	
	global $wp_roles;
	if ( ! isset( $wp_roles ) ){
		$wp_roles = new WP_Roles();
	}
	
	$manager = $wp_roles->get_role("manager");
	
	if ( isset( $manager ) ) {
		
		return;
	}

	
	$editor = $wp_roles->get_role('editor');
	$manager = $wp_roles->add_role('manager', 'Manager', $editor->capabilities);
	$manager->add_cap("list_users");
	$manager->add_cap("promote_users");
	
}

/**
 * Handle user registration.
 *
 * @param mixed $username username for the current user.
 */
function mooauth_handle_user_registration($userInfo) {
	$user_id = $userInfo['id'];
	$username = $userInfo['login'];
	$email = $userInfo['email'];

	if ($user_id === 0) {
		$random_password = wp_generate_password(10, false);
		$user_id = wp_create_user($username, $random_password);
	}

	$user_model = build_user_model($user_id, $email, $userInfo['display_name']);
	wp_update_user($user_model);
	update_user_info($user_id, $userInfo);

	$user = get_user_by('login', $username);
	
	if (is_manager_user($email, $userInfo['groups'], $userInfo['clientid'])) {
		
		add_manager_role();
		
		$user->set_role('manager');
		
	}
	

	return $user;
}

/**
 * Handler User registration.
 *
 * @param mixed $temp_var temp var.
 */
function mooauth_looped_user( $temp_var ) {
	return mooauth_looped_redirect( $temp_var );
}

/**
 * Display attribute mapping in Test Configuration.
 *
 * @param mixed  $nestedprefix nested prefix.
 * @param mixed  $resource_owner_details resource owner details of the current user.
 * @param string $tr_class_prefix prefix for tr class.
 */
function mooauth_client_testattrmappingconfig( $nestedprefix, $resource_owner_details, $tr_class_prefix = '' ) {

	$username_value = '';
	foreach ( $resource_owner_details as $key => $resource ) {
		if ( is_array( $resource ) || is_object( $resource ) ) {
			if ( ! empty( $nestedprefix ) ) {
				$nestedprefix .= '.';
			}
			mooauth_client_testattrmappingconfig( $nestedprefix . $key, $resource, $tr_class_prefix );
			$nestedprefix = rtrim( $nestedprefix, '.' );
		} else {
			echo '<tr class="' . esc_attr( $tr_class_prefix ) . 'tr"><td class="' . esc_attr( $tr_class_prefix ) . 'td">';
			if ( ! empty( $nestedprefix ) ) {
				$key = $nestedprefix . '.' . $key;
			}
			echo esc_html( $key ) . '</td><td class="' . esc_attr( $tr_class_prefix ) . 'td">' . esc_html( $resource ) . '</td></tr>';

			$appslist       = get_option( 'mo_oauth_apps_list' );
			$currentapp     = null;
			$currentappname = null;
			if ( is_array( $appslist ) ) {
				foreach ( $appslist as $currentappname => $currentapp ) {
					break;
				}
			}
			if ( strpos( $username_value, 'username' ) === false ) {
				if ( strpos( $key, 'username' ) !== false ) {
					$username_value = $key;
				} elseif ( strpos( $key, 'email' ) !== false && filter_var( $resource, FILTER_VALIDATE_EMAIL ) ) {
					$username_value = $key;
				}
			}
		}
	}

	if ( ! isset( $currentapp['username_attr'] ) && $username_value ) {
		$currentapp['username_attr'] = $username_value;
		$appslist[ $currentappname ] = $currentapp;
		update_option( 'mo_oauth_apps_list', $appslist );
	}
}

/**
 * Get nested attribute.
 *
 * @param mixed $resource resource owner info.
 * @param mixed $key attriubte key.
 */
function mooauth_client_getnestedattribute( $resource, $key ) {
	if ( '' === $key ) {
		return '';
	}

	$keys = explode( '.', $key );
	if ( count( $keys ) > 1 ) {
		$current_key = $keys[0];
		if ( isset( $resource[ $current_key ] ) ) {
			return mooauth_client_getnestedattribute( $resource[ $current_key ], str_replace( $current_key . '.', '', $key ) );
		}
	} else {
		$current_key = $keys[0];
		if ( isset( $resource[ $current_key ] ) ) {
			return $resource[ $current_key ];
		}
	}
}

/**
 * Handle user registration.
 *
 * @param mixed $ejhi temp var.
 */
function mooauth_looped_redirect( $ejhi ) {
	$user = mooauth_handle_user_registration( $ejhi );
	return $user;
}

/**
 * Get prefix.
 *
 * @param mixed $type type of variable.
 * @return array
 */
function mooauth_client_get_proper_prefix( $type ) {
	$letter = substr( $type, 0, 1 );
	$vowels = array( 'a', 'e', 'i', 'o', 'u' );
	return ( in_array( $letter, $vowels, true ) ) ? ' an ' . $type : ' a ' . $type;
}

/**
 * Register widget.
 */
function mooauth_register_widget() {
	register_widget( 'mooauth_widget' );
}

/**
 * Check if DOING_AJAX is defined.
 */
function mooauth_client_is_ajax_request() {
	return defined( 'DOING_AJAX' ) && DOING_AJAX;
}

/**
 * Valid html
 *
 * Helper function for escaping.
 *
 * @param array $args HTML to add to valid args.
 *
 * @return array valid html.
 **/
function mo_oauth_get_valid_html( $args = array() ) {
	$retval = array(
		'strong' => array(),
		'em'     => array(),
		'b'      => array(),
		'i'      => array(),
		'a'      => array(
			'href'   => array(),
			'target' => array(),
		),
	);
	if ( ! empty( $args ) ) {
		return array_merge( $args, $retval );
	}
	return $retval;
}

/**
 * Check for REST API call.
 *
 * @return [type]
 */
function mooauth_client_is_rest_api_call() {
	return ! empty( $_SERVER['REQUEST_URI'] ) ? strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wp-json' ) === false : '';
}

/**
 * Generate random string.
 *
 * @param int $length length of the string to be generated.
 * @return string
 */
function mooauth_gen_rand_str( $length = 10 ) {
	$characters        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characters_length = strlen( $characters );
	$random_string     = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
	}
	return $random_string;
}

	add_action( 'widgets_init', 'mooauth_register_widget' );
	add_action( 'init', 'mooauth_login_validate' );
?>
