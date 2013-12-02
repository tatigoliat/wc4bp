<?php
/**
 * @package       	WordPress
 * @subpackage    	BuddyPress, Woocommerce
 * @author        	Boris Glumpler
 * @copyright    	2011, Themekraft
 * @link        	https://github.com/Themekraft/BP-Shop-Integration
 * @license        	http://www.opensource.org/licenses/gpl-2.0.php GPL License
 */

// No direct access is allowed
if( ! defined( 'ABSPATH' ) ) exit;

class BPSHOP_Component extends BP_Component
{
    /**
     * Holds the ID of the component
	 *
	 * @var		string
     * @since   1.0
     */
	public $id = 'shop';

    /**
     * Start the shop component creation process
     *
     * @todo    Move self::includes() out of constructor once the BP bug
     *             (hook priority) has been resolved to allow use of parent
     *             method
     * @since     1.0
     */
    function __construct() {
        parent::start( $this->id, __( 'Woocommerce Integration', 'bpshop' ), BPSHOP_ABSPATH );
        
        $this->includes();

        add_action( 'bp_register_activity_actions',	array( &$this, 'register_activity_actions' ) );
		add_action( 'bp_located_template', 			array( &$this, 'bpshop_members_load_template_filter'), 10, 2);
    }

    /**
     * Register acctivity actions
     *
     * @since     1.0.4
     */
    function register_activity_actions() {
        if( ! bp_is_active( 'activity' ) )
            return false;

        bp_activity_set_action( $this->id, 'new_shop_review',   __( 'New review created', 'bpshop' ) );
        bp_activity_set_action( $this->id, 'new_shop_purchase', __( 'New purchase made',   'bpshop' ) );

        do_action( 'bpshop_register_activity_actions' );
    }
    
    /**
     * Include files
     *
     * @since     1.0
     */
    function includes() {
        
		$wc4bp_options			= get_option( 'wc4bp_options' ); 
		
        $includes = array(
            'bpshop-helpers',
            'bpshop-conditionals',
            'bpshop-screen',
            'bpshop-redirect',
            'bpshop-deprecated'
        );
        
        foreach( $includes as $file )
            require( BPSHOP_ABSPATH .'core/'. $file .'.php' );
		
		if ( ! class_exists( 'BP_Theme_Compat' ) )
    		require(  BPSHOP_ABSPATH .'core/bpshop-template-compatibility.php'  );

		if(!isset($wc4bp_options['tab_sync_disabled'])){
    		require(  BPSHOP_ABSPATH .'core/bpshop-sync.php'  );
		}
	
	}

    /**
     * Setup globals
     *
     * @since     1.0
     * @global    object    $bp
     */
    function setup_globals() {
        global $bp;

        $globals = array(
            'path'          => BPSHOP_ABSPATH .'core',
            'slug'          => 'shop',
            'has_directory' => false
        );

        parent::setup_globals( $globals );
    }

    /**
     * Setup BuddyBar navigation
     *
     * @since    1.0
     * @global   object    $bp
     */
    function setup_nav() {

		$wc4bp_options			= get_option( 'wc4bp_options' ); 
		$wc4bp_pages_options	= get_option( 'wc4bp_pages_options' ); 
		
		if($wc4bp_options['tab_shop_default'] != -1 ){
			$default_screen = 'bpshop_screen_plugins';
		} else {
			$default_screen = 'bpshop_screen_shopping_cart';
		}
			
        // Add 'Shop' to the main navigation
        $main_nav = array(
            'name'                          => __( 'Shop', 'bpshop' ),
            'slug'                          => $this->slug,
            'position'                      => 70,
            'screen_function'               => $default_screen,
            'default_subnav_slug'           => 'cart',
            'item_css_id'                   => $this->id,
            'show_for_displayed_user'       => false
        );

        $shop_link = trailingslashit( bp_loggedin_user_domain() . $this->slug );
		
		// Add the cart nav item
		if( ! isset( $wc4bp_options['tab_cart_disabled'])) {
	        $sub_nav[] = array(
	            'name'            => __( 'Shopping Cart', 'bpshop' ),
	            'slug'            => 'cart',
	            'parent_url'      => $shop_link,
	            'parent_slug'     => $this->slug,
	            'screen_function' => 'bpshop_screen_shopping_cart',
	            'position'        => 10,
	            'item_css_id'     => 'shop-cart',
	            'user_has_access' => bp_is_my_profile()
	        );
		}

		// Add the checkout nav item
		if( ! isset( $wc4bp_options['tab_history_disabled'])) {
	        
	        $sub_nav[] = array(
	            'name'            => __( 'History', 'bpshop' ),
	            'slug'            => 'history',
	            'parent_url'      => $shop_link,
	            'parent_slug'     => $this->slug,
	            'screen_function' => 'bpshop_screen_history',
	            'position'        => 30,
	            'item_css_id'     => 'shop-history',
	            'user_has_access' => bp_is_my_profile()
	        );
		}
        // Add the checkout nav item
        if( ! isset( $wc4bp_options['tab_track_disabled'])) {
        $sub_nav[] = array(
            'name'            => __( 'Track your order', 'bpshop' ),
            'slug'            => 'track',
            'parent_url'      => $shop_link,
            'parent_slug'     => $this->slug,
            'screen_function' => 'bpshop_screen_track_order',
            'position'        => 30,
            'item_css_id'     => 'shop-track',
            'user_has_access' => bp_is_my_profile()
        );
		}
		
        // Add shop settings subpage
        if( ! isset( $wc4bp_options['tab_activity_disabled'])) {
	        $sub_nav[] = array(
	            'name'            => __( 'Shop', 'bpshop' ),
	            'slug'            => 'shop',
	            'parent_url'      => trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug()),
	            'parent_slug'     => bp_get_settings_slug(),
	            'screen_function' => 'bpshop_screen_settings',
	            'position'        => 30,
	            'item_css_id'     => 'shop-settings',
	            'user_has_access' => bp_is_my_profile()
	        );
		}
		$position = 40;
		
		if(isset($wc4bp_pages_options['selected_pages']) && is_array($wc4bp_pages_options['selected_pages'])){
			foreach ($wc4bp_pages_options['selected_pages'] as $key => $attached_page) {
				$position++;
				$sub_nav[] = array(
		            'name'            => $attached_page['tab_name'],
		            'slug'            => $attached_page['tab_slug'],
		            'parent_url'      => $shop_link,
		            'parent_slug'     => $this->slug,
		            'screen_function' => 'bpshop_screen_plugins',
		            'position'        => $position,
		            'item_css_id'     => 'shop-cart',
		            'user_has_access' => bp_is_my_profile()
		        );
		 	}
		} 
        do_action( 'bp_shop_setup_nav' );
        
        parent::setup_nav( $main_nav, $sub_nav );
    }


	/**
	 * Set up the Toolbar
	 *
	 * @global BuddyPress $bp The one true BuddyPress instance
	 */
	function setup_admin_bar() {
		global $bp;
		
		$wc4bp_options			= get_option( 'wc4bp_options' ); 
		$wc4bp_pages_options	= get_option( 'wc4bp_pages_options' ); 
		
		$wp_admin_nav = array();

		if ( is_user_logged_in() ) {
			$user_domain   = bp_loggedin_user_domain();
			$settings_link = trailingslashit( $user_domain . BP_SETTINGS_SLUG );

			// Shop settings menu
			$wp_admin_nav[] = array(
				'parent' => 'my-account-settings',
				'id'     => 'my-account-settings-shop',
				'title'  => __( 'Shop', 'bpshop' ),
				'href'   => trailingslashit( $settings_link . 'shop' )
			);
			
			$shop_link = trailingslashit( $user_domain . $this->id );
			
			// Shop menu items
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Shop', 'bpshop' ),
				'href'   => trailingslashit( $shop_link )
			);
			
			if( ! isset( $wc4bp_options['tab_cart_disabled'])) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-cart',
					'title'  => __( 'Shopping Cart', 'bpshop' ),
					'href'   => trailingslashit( $shop_link )
				);
			}
			
			if( ! isset( $wc4bp_options['tab_history_disabled'])) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-history',
					'title'  => __( 'History', 'bpshop' ),
					'href'   => trailingslashit( $shop_link . 'history' )
				);
			}
			
			if( ! isset( $wc4bp_options['tab_track_disabled'])) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-track',
					'title'  => __( 'Track your order', 'bpshop' ),
					'href'   => trailingslashit( $shop_link . 'track' )
				);
			}

			if(isset($wc4bp_pages_options['selected_pages']) && is_array($wc4bp_pages_options['selected_pages'])){
				foreach ($wc4bp_pages_options['selected_pages'] as $key => $attached_page) {
					
					$wp_admin_nav[] = array(
						'parent' => 'my-account-' . $this->id,
						'id'     => 'my-account-' . $this->id . '-'.$attached_page['tab_slug'],
						'title'  => $attached_page['tab_name'],
						'href'   => trailingslashit( $shop_link . $attached_page['tab_slug'] )
					);

			 	}
			}
			
		parent::setup_admin_bar( $wp_admin_nav );
	}
	}

	/**
	 * WC4BP template loader.
	 * 
	 * I copied this function from the buddypress.org website and modified it for my needs. 
	 *
	 * This function sets up WC4BP to use custom templates.
	 *
	 * If a template does not exist in the current theme, we will use our own
	 * bundled templates.
	 *
	 * We're doing two things here:
	 *  1) Support the older template format for themes that are using them
	 *     for backwards-compatibility (the template passed in
	 *     {@link bp_core_load_template()}).
	 *  2) Route older template names to use our new template locations and
	 *     format.
	 *
	 * View the inline doc for more details.
	 *
	 * @since 1.0
	 */
	function bpshop_members_load_template_filter($found_template, $templates) {
	global $bp;
	//$wc4bp_options		= get_option( 'wc4bp_options' );
	// echo '<pre>';
	// print_r($bp);
	// echo '</pre>';

	if ($bp->current_component == 'shop') {

			if (empty($found_template)) {
				// register our theme compat directory
				//
				// this tells BP to look for templates in our plugin directory last
				// when the template isn't found in the parent / child theme
				bp_register_template_stack('bpshop_members_get_template_directory', 14);
	
				// locate_template() will attempt to find the plugins.php template in the
				// child and parent theme and return the located template when found
				//
				// plugins.php is the preferred template to use, since all we'd need to do is
				// inject our content into BP
				//
				// note: this is only really relevant for bp-default themes as theme compat
				// will kick in on its own when this template isn't found
				$found_template = locate_template('members/single/plugins.php', false, false);
	
				// add our hook to inject content into BP
				$wc4bp_options			= get_option( 'wc4bp_options' ); 
				if ($bp->current_action == 'cart') {
					if(isset( $wc4bp_options['tab_cart_disabled'])){
						$bp->current_action = $wc4bp_options['tab_shop_default'];
						add_action('bp_template_content', create_function('', "
						bp_get_template_part( 'shop/member/plugin' );
						"));
					} else{
						add_action('bp_template_content', create_function('', "
						bp_get_template_part( 'shop/member/cart' );
						"));
					}
				} elseif ($bp->current_action == 'history') {
					add_action('bp_template_content', create_function('', "
					bp_get_template_part( 'shop/member/history' );
					"));
				} elseif ($bp->current_action == 'track') {
					add_action('bp_template_content', create_function('', "
					bp_get_template_part( 'shop/member/track' );
					"));
				} else {
					add_action('bp_template_content', create_function('', "
					bp_get_template_part( 'shop/member/plugin' );
					"));
				}
				 
			}
		
		}
	
	return apply_filters(' bpshop_members_load_template_filter', $found_template);
	}

}

/**
 * Get the WC4BP template directory
 *
 * @package WC4BP
 * @since 0.1 beta
 *
 * @uses apply_filters()
 * @return string
 */
function bpshop_members_get_template_directory() {
	return apply_filters('bpshop_members_get_template_directory', constant('BPSHOP_ABSPATH_TEMPLATE_PATH'));
}


// Create the shop component
global $bp;
$bp->shop = new BPSHOP_Component();
?>