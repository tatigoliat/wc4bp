<?php
/**
 * @package        WordPress
 * @subpackage     BuddyPress, Woocommerce
 * @author         GFireM
 * @copyright      2017, Themekraft
 * @link           http://themekraft.com/store/woocommerce-buddypress-integration-wordpress-plugin/
 * @license        http://www.opensource.org/licenses/gpl-2.0.php GPL License
 */

// No direct access is allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wc4bp_admin {
    
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wc4bp_admin_menu' ) );
		add_action( 'admin_footer', array( $this, 'wc4bp_admin_js_footer' ), 10, 1 );
		add_action( 'admin_init', array( $this, 'wc4bp_register_admin_settings' ) );
	}
	
	/**
	 * Adding the Admin Page
	 *
	 * @author Sven Lehnert
	 * @package WC4BP
	 * @since 1.3
	 */
	public function wc4bp_admin_menu() {
		add_menu_page(  __( 'WooCommerce for BuddyPress', 'wc4bp' ), 'WC4BP Settings', 'manage_options', 'wc4bp-options-page', array( $this, 'wc4bp_screen' ) );
		do_action( 'wc4bp_add_submenu_page' );
		
		require_once( WC4BP_ABSPATH . 'admin/admin-sync.php' );
		$admin_sync          = new wc4bp_admin_sync();
		$wc4bp_options = get_option( 'wc4bp_options' );
		if ( ! isset( $wc4bp_options['tab_sync_disabled'] ) ) {
			add_submenu_page( 'wc4bp-options-page', __( 'WC4BP Profile Fields Sync','wc4bp' ), 'Profile Fields Sync', 'manage_options', 'wc4bp-options-page-sync', array( $admin_sync, 'wc4bp_screen_sync' ) );
		}

		require_once( WC4BP_ABSPATH . 'admin/admin-pages.php' );
		$admin_pages = new wc4bp_admin_pages();
		add_submenu_page( 'wc4bp-options-page', __('WC4BP Integrate Pages','wc4bp' ), 'Integrate Pages', 'manage_options', 'wc4bp-options-page-pages', array( $admin_pages, 'wc4bp_screen_pages' ) );

		require_once( WC4BP_ABSPATH . 'admin/admin-delete.php' );
		$admin_delete = new wc4bp_admin_delete();
		add_submenu_page( 'wc4bp-options-page',  __('Delete','wc4bp' ), 'Delete', 'manage_options', 'wc4bp-options-page-delete', array( $admin_delete, 'wc4bp_screen_delete' ) );

		require_once( WC4BP_ABSPATH . 'admin/admin-ajax.php' );
		new wc4bp_admin_ajax();
	}
	
	public function wc4bp_admin_js_footer( $hook_suffix ) {
		global $hook_suffix;
		
		if ( $hook_suffix == 'toplevel_page_wc4bp-options-page' ) {
			?>
            <script>!function (e, o, n) {
                    window.HSCW = o, window.HS = n, n.beacon = n.beacon || {};
                    var t = n.beacon;
                    t.userConfig = {}, t.readyQueue = [], t.config = function (e) {
                        this.userConfig = e
                    }, t.ready = function (e) {
                        this.readyQueue.push(e)
                    }, o.config = {
                        docs: {enabled: !0, baseUrl: "//themekraft.helpscoutdocs.com/"},
                        contact: {enabled: !0, formId: "ef61dbbb-83ab-11e5-8846-0e599dc12a51"}
                    };
                    var r = e.getElementsByTagName("script")[0], c = e.createElement("script");
                    c.type = "text/javascript", c.async = !0, c.src = "https://djtflbt20bdde.cloudfront.net/", r.parentNode.insertBefore(c, r)
                }(document, window.HSCW || {}, window.HS || {});</script>
			<?php
		}
	}
	
	/**
	 * The Admin Page
	 *
	 * @author Sven Lehnert
	 * @package WC4BP
	 * @since 1.3
	 */
	public function wc4bp_screen() {
		include_once( dirname( __FILE__ ) . '\views\html_admin_screen.php' );
	}
	
	/**
	 * Register the admin settings
	 *
	 * @author Sven Lehnert
	 * @package TK Loop Designer
	 * @since 1.0
	 */
	public function wc4bp_register_admin_settings() {

		register_setting( 'wc4bp_options', 'wc4bp_options' );
		// Settings fields and sections
		add_settings_section( 'section_general', '', '', 'wc4bp_options' );
		add_settings_section( 'section_general2', '', '', 'wc4bp_options' );

		add_settings_field( 'tabs_shop', __( '<b>Shop Settings</b>', 'wc4bp' ), array( $this, 'wc4bp_shop_tabs' ), 'wc4bp_options', 'section_general' );
		add_settings_field( 'tabs_enable',  __('<b>Shop Tabs</b>', 'wc4bp' ), array( $this, 'wc4bp_shop_tabs_enable'), 'wc4bp_options',  'section_general' );
		add_settings_field( 'tabs_disabled', __('<b>Remove Shop Tabs</b>', 'wc4bp' ), array( $this, 'wc4bp_shop_tabs_disable' ), 'wc4bp_options', 'section_general' );
		add_settings_field( 'profile sync', __('<b>Turn off the profile sync</b>','wc4bp' ), array( $this, 'wc4bp_turn_off_profile_sync' ), 'wc4bp_options', 'section_general' );
		add_settings_field( 'overwrite', __('<b>Overwrite the Content of your Shop Home/Main Tab</b>','wc4bp' ), array( $this, 'wc4bp_overwrite_default_shop_home_tab' ), 'wc4bp_options', 'section_general' );
		add_settings_field( 'template',  __('<b>Change the page template to be used for the attached pages.</b>','wc4bp' ), array( $this, 'wc4bp_page_template' ), 'wc4bp_options', 'section_general' );

	}
	
	public function wc4bp_shop_tabs() {
		$wc4bp_options = get_option( 'wc4bp_options' );
		
		$tab_activity_disabled = 0;
		if ( isset( $wc4bp_options['tab_activity_disabled'] ) ) {
			$tab_activity_disabled = $wc4bp_options['tab_activity_disabled'];
		}
		include_once( dirname( __FILE__ ) . '\views\html_admin_shop_tabs.php' );
	}
	
	public function wc4bp_shop_tabs_enable() {
		$wc4bp_options = get_option( 'wc4bp_options' );
		$end_points = wc_get_account_menu_items();
		include_once( dirname( __FILE__ ) . '\views\html_admin_shop_tabs_enable.php' );
	}
	
	/**
	 * Do you want to use the WordPress Customizer? This is the option to turn on/off the WordPress Customizer Support.
	 *
	 * @author Sven Lehnert
	 * @package TK Loop Designer
	 * @since 1.0
	 */
	public function wc4bp_shop_tabs_disable() {
		$wc4bp_options = get_option( 'wc4bp_options' );
		
		$tab_cart_disabled = 0;
		if ( isset( $wc4bp_options['tab_cart_disabled'] ) ) {
			$tab_cart_disabled = $wc4bp_options['tab_cart_disabled'];
		}
		
		$tab_checkout_disabled = 0;
		if ( isset( $wc4bp_options['tab_checkout_disabled'] ) ) {
			$tab_checkout_disabled = $wc4bp_options['tab_checkout_disabled'];
		}
		
		$tab_history_disabled = 0;
		if ( isset( $wc4bp_options['tab_history_disabled'] ) ) {
			$tab_history_disabled = $wc4bp_options['tab_history_disabled'];
		}
		
		$tab_track_disabled = 0;
		if ( isset( $wc4bp_options['tab_track_disabled'] ) ) {
			$tab_track_disabled = $wc4bp_options['tab_track_disabled'];
		}
		
		include_once( dirname( __FILE__ ) . '\views\html_admin_shop_disable.php' );
	}
	
	public function wc4bp_turn_off_profile_sync() {
		$wc4bp_options = get_option( 'wc4bp_options' );
		
		$tab_sync_disabled = 0;
		if ( isset( $wc4bp_options['tab_sync_disabled'] ) ) {
			$tab_sync_disabled = $wc4bp_options['tab_sync_disabled'];
		}
		include_once( dirname( __FILE__ ) . '\views\html_admin_profile_sync.php' );
		?>
		
		<?php
		if ( isset( $tab_sync_disabled ) && true == $tab_sync_disabled ) {
			include_once( dirname( __FILE__ ) . '/wc4bp-activate.php' );
			wc4bp_cleanup();
		} else {
			include_once( dirname( __FILE__ ) . '/wc4bp-activate.php' );
			wc4bp_activate();
		}
		
		
	}
	
	public function wc4bp_overwrite_default_shop_home_tab() {
		$wc4bp_options       = get_option( 'wc4bp_options' );
		$wc4bp_pages_options = get_option( 'wc4bp_pages_options' );
		
		include_once( dirname( __FILE__ ) . '\views\html_admin_shop_home.php' );
		
	}
	
	
	public function wc4bp_page_template() {
		$wc4bp_options = get_option( 'wc4bp_options' );
		
		$page_template = '';
		if ( ! empty( $wc4bp_options['page_template'] ) ) {
			$page_template = $wc4bp_options['page_template'];
		}
		include_once( dirname( __FILE__ ) . '\views\html_admin_page_template.php' );
		
		submit_button();
	}
}