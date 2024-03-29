<?php
/**
 * Redux Core Class
 *
 * @class Redux_Core
 * @version 4.0.0
 * @package Redux Framework
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Redux_Core', false ) ) {

	/**
	 * Class Redux_Core
	 */
	class Redux_Core {

		/**
		 * Class instance.
		 *
		 * @var object
		 */
		public static $instance;

		/**
		 * Project version
		 *
		 * @var project string
		 */
		public static $version;

		/**
		 * Project directory.
		 *
		 * @var project string.
		 */
		public static $dir;

		/**
		 * Project URL.
		 *
		 * @var project URL.
		 */
		public static $url;

		/**
		 * Base directory path.
		 *
		 * @var string
		 */
		public static $redux_path;

		/**
		 * Absolute direction path to WordPress upload directory.
		 *
		 * @var null
		 */
		public static $upload_dir = null;

		/**
		 * Full URL to WordPress upload directory.
		 *
		 * @var string
		 */
		public static $upload_url = null;

		/**
		 * Set when Redux is run as a plugin.
		 *
		 * @var bool
		 */
		public static $is_plugin = true;

		/**
		 * Indicated in_theme or in_plugin.
		 *
		 * @var string
		 */
		public static $installed = '';

		/**
		 * Set when Redux is run as a plugin.
		 *
		 * @var bool
		 */
		public static $as_plugin = false;

		/**
		 * Set when Redux is embedded within a theme.
		 *
		 * @var bool
		 */
		public static $in_theme = false;

		/**
		 * Set when Redux Pro plugin is loaded and active.
		 *
		 * @var bool
		 */
		public static $pro_loaded = false;

		/**
		 * Pointer to updated google fonts array.
		 *
		 * @var array
		 */
		public static $google_fonts = array();

		/**
		 * List of files calling Redux.
		 *
		 * @var array
		 */
		public static $callers = array();

		/**
		 * Nonce.
		 *
		 * @var string
		 */
		public static $wp_nonce;

		/**
		 * Pointer to _SERVER global.
		 *
		 * @var null
		 */
		public static $server = null;

		/**
		 * Pointer to the thirdparty fixes class.
		 *
		 * @var null
		 */
		public static $third_party_fixes = null;

		/**
		 * Redux Welcome screen object.
		 *
		 * @var null
		 */
		public static $welcome = null;

		/**
		 * Redux Appsero object.
		 *
		 * @var null
		 */
		public static $appsero = null;

		/**
		 * Redux Insights object.
		 *
		 * @var null
		 */
		public static $insights = null;

		/**
		 * Creates instance of class.
		 *
		 * @return Redux_Core
		 * @throws Exception Comment.
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();

				self::$instance->includes();
				self::$instance->init();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		/**
		 * Class init.
		 */
		private function init() {
			self::$dir = trailingslashit( wp_normalize_path( dirname( realpath( __FILE__ ) ) ) );

			Redux_Functions_Ex::generator();

			if ( ! class_exists( 'ReduxAppsero\Client' ) ) {
				require_once Redux_Path::get_path( '/appsero/Client.php' );
			}

			if ( defined( 'REDUX_PLUGIN_FILE' ) ) {
				$client      = new ReduxAppsero\Client( 'f6b61361-757e-4600-bb0f-fe404ae9871b', 'Redux Framework', REDUX_PLUGIN_FILE );
				$plugin_info = Redux_Functions_Ex::is_inside_plugin( REDUX_PLUGIN_FILE );
			} else {
				$client = new ReduxAppsero\Client( 'f6b61361-757e-4600-bb0f-fe404ae9871b', 'Redux Framework', __FILE__ );
				// See if Redux is a plugin or not.

				$client->slug       = 'redux-framework';
				$client->textdomain = 'redux-framework';
				$client->version    = \Redux_Core::$version;
			}

			$plugin_info = Redux_Functions_Ex::is_inside_plugin( __FILE__ );

			if ( false !== $plugin_info ) {
				self::$installed = class_exists( 'Redux_Framework_Plugin' ) ? 'plugin' : 'in_plugin';
				self::$is_plugin = class_exists( 'Redux_Framework_Plugin' );
				self::$as_plugin = true;
				self::$url       = trailingslashit( dirname( $plugin_info['url'] ) );
				if ( isset( $plugin_info['slug'] ) && ! empty( $plugin_info['slug'] ) ) {
					$client->slug = $plugin_info['slug'];
				}
				$client->type = 'plugin';
			} else {
				$theme_info = Redux_Functions_Ex::is_inside_theme( __FILE__ );
				if ( false !== $theme_info ) {
					self::$url       = trailingslashit( dirname( $theme_info['url'] ) );
					self::$in_theme  = true;
					self::$installed = 'in_theme';
					if ( isset( $theme_info['slug'] ) && ! empty( $theme_info['slug'] ) ) {
						$client->slug = $theme_info['slug'];
					}
					$client->type = 'theme';
				}
			}

			self::$appsero = $client;

			// Activate insights.
			self::$insights = self::$appsero->insights();

			$metadata = array();
			if ( defined( 'RDX_MOKAMA' ) ) {
				self::$insights->add_extra( array( 'mokama' => RDX_MOKAMA ) );
			}

			self::$insights->hide_notice()->init();
			if ( ! defined( 'REDUX_PLUGIN_FILE' ) ) {
				remove_action( 'admin_footer', array( self::$insights, 'deactivate_scripts' ) );
			}

			if ( ! self::$insights->tracking_allowed() ) {
				if ( ! self::$insights->notice_dismissed() ) {
					// Old tracking permissions.
					$tracking_options = get_option( 'redux-framework-tracking', array() );
					if ( ! empty( $tracking_options ) ) {
						if ( isset( $tracking_options['allow_tracking'] ) && 'yes' === $tracking_options['allow_tracking'] ) {
							Redux_Functions_Ex::set_activated();
						}
					}
				}
			}

			remove_action( 'redux_admin_notices_run', array( 'ReduxAppsero\Insights', 'admin_notice' ) );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			self::$url = apply_filters( 'redux/url', self::$url );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			self::$dir = apply_filters( 'redux/dir', self::$dir );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			self::$is_plugin = apply_filters( 'redux/is_plugin', self::$is_plugin );

			$upload_dir       = wp_upload_dir();
			self::$upload_dir = $upload_dir['basedir'] . '/redux/';
			self::$upload_url = str_replace( array( 'https://', 'http://' ), '//', $upload_dir['baseurl'] . '/redux/' );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			self::$upload_dir = apply_filters( 'redux/upload_dir', self::$upload_dir );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			self::$upload_url = apply_filters( 'redux/upload_url', self::$upload_url );

			self::$server = filter_input_array( INPUT_SERVER, $_SERVER ); // phpcs:ignore WordPress.Security.EscapeOutput

		}

		/**
		 * Code to execute on framework __construct.
		 *
		 * @param object $parent Pointer to ReduxFramework object.
		 * @param array  $args Global arguments array.
		 */
		public static function core_construct( $parent, $args ) {
			self::$third_party_fixes = new Redux_ThirdParty_Fixes( $parent );

			Redux_ThemeCheck::get_instance();

		}

		/**
		 * Autoregister run.
		 *
		 * @throws Exception Comment.
		 */
		private function includes() {
			if ( class_exists( 'Redux_Pro' ) && isset( Redux_Pro::$dir ) ) {
				self::$pro_loaded = true;
			}

			require_once dirname( __FILE__ ) . '/inc/classes/class-redux-path.php';
			require_once dirname( __FILE__ ) . '/inc/classes/class-redux-extension-abstract.php';

			spl_autoload_register( array( $this, 'register_classes' ) );
			Redux_Functions_Ex::register_class_path( 'Redux', dirname( __FILE__ ) );

			self::$welcome = new Redux_Welcome();
			new Redux_Rest_Api_Builder( $this );

			add_action( 'admin_init', array( $this, 'admin_init' ) );

			$support_hash = md5( md5( Redux_Functions_Ex::hash_key() . '-redux' ) . '-support' );
			add_action( 'wp_ajax_nopriv_' . $support_hash, array( 'Redux_Helpers', 'support_args' ) );
			add_action( 'wp_ajax_' . $support_hash, array( 'Redux_Helpers', 'support_args' ) );
			$hash_arg = md5( trailingslashit( network_site_url() ) . '-redux' );
			add_action( 'wp_ajax_nopriv_' . $hash_arg, array( 'Redux_Helpers', 'hash_arg' ) );
			add_action( 'wp_ajax_' . $hash_arg, array( 'Redux_Helpers', 'hash_arg' ) );
			add_action( 'wp_ajax_redux_support_hash', array( 'Redux_Functions', 'support_hash' ) );

			add_filter( 'redux/tracking/options', array( 'Redux_Helpers', 'redux_stats_additions' ) );
		}

		/**
		 * Register callback for autoload.
		 *
		 * @param string $class_name name of class.
		 */
		public function register_classes( $class_name ) {
			if ( ! class_exists( $class_name ) ) {

				// Backward compatibility for extensions sucks!
				if ( 'Redux_Instances' === $class_name && ! class_exists( 'ReduxFrameworkInstances', false ) ) {
					require_once Redux_Path::get_path( '/inc/classes/class-redux-instances.php' );
					require_once Redux_Path::get_path( '/inc/lib/redux-instances.php' );

					return;
				}

				// Load Redux APIs.
				if ( 'Redux' === $class_name ) {
					require_once Redux_Path::get_path( '/inc/classes/class-redux-api.php' );

					return;
				}

				// Redux extra theme checks.
				if ( 'Redux_ThemeCheck' === $class_name ) {
					require_once Redux_Path::get_path( '/inc/themecheck/class-redux-themecheck.php' );

					return;
				}

				if ( 'Redux_Welcome' === $class_name ) {
					require_once Redux_Path::get_path( '/inc/welcome/class-redux-welcome.php' );

					return;
				}

				if ( 'Redux_Connection_Banner' === $class_name ) {
					require_once Redux_Path::get_path( '/inc/classes/class-redux-connection-banner.php' );

					return;
				}

				if ( class_exists( 'Redux_Framework_Plugin' ) && ! class_exists( 'Redux_User_Feedback' ) ) {
					require_once Redux_Path::get_path( '/inc/classes/class-redux-user-feedback.php' );
				}

				// Everything else.
				$file = 'class.' . strtolower( $class_name ) . '.php';

				$class_path = Redux_Path::get_path( '/inc/classes/' . $file );

				if ( ! file_exists( $class_path ) ) {
					$class_name = str_replace( '_', '-', $class_name );
					$file       = 'class-' . strtolower( $class_name ) . '.php';
					$class_path = Redux_Path::get_path( '/inc/classes/' . $file );
				}

				if ( file_exists( $class_path ) ) {
					require_once $class_path;
				}
			}

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( 'redux/core/includes', $this );
		}

		/**
		 * Hooks to run on instance creation.
		 */
		private function hooks() {
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( 'redux/core/hooks', $this );
		}

		/**
		 * Display the connection banner.
		 */
		public function admin_init() {
			Redux_Connection_Banner::init();
		}

		/**
		 * Action to run on WordPress heartbeat.
		 *
		 * @return bool
		 */
		public static function is_heartbeat() {
			// Disregard WP AJAX 'heartbeat'call.  Why waste resources?
			if ( isset( $_POST ) && isset( $_POST['_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_nonce'] ) ), 'heartbeat-nonce' ) ) {
				if ( isset( $_POST['action'] ) && 'heartbeat' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {

					// Hook, for purists.
					if ( has_action( 'redux/ajax/heartbeat' ) ) {
						// phpcs:ignore WordPress.NamingConventions.ValidHookName
						do_action( 'redux/ajax/heartbeat' );
					}

					return true;
				}

				return false;
			}

			// Buh bye!
			return false;
		}
	}

	/*
	 * Backwards comparability alias
	 */
	class_alias( 'Redux_Core', 'redux-core' );
}
