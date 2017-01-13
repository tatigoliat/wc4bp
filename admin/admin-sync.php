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

class wc4bp_admin_sync {
    
	public function __construct() {
		add_action( 'admin_init', array( $this, 'wc4bp_register_admin_settings_sync' ) );
		add_action( 'wp_ajax_wc4bp_shop_profile_sync_ajax', array( $this, 'wc4bp_shop_profile_sync_ajax' ) );
		add_action( 'wp_ajax_nopriv_wc4bp_shop_profile_sync_ajax', array( $this, 'wc4bp_shop_profile_sync_ajax' ) );
	}
	
	
	/**
	 * The Admin Page
	 *
	 * @author Sven Lehnert
	 * @package WC4BP
	 * @since 1.3
	 */
	public function wc4bp_screen_sync() { ?>
        <div class="wrap">

        <div id="icon-options-general" class="icon32"><br></div>
        <h2> <?php _e('WooCommerce BuddyPress Integration', 'wc4bp'); ?></h2>
        <div style="overflow: auto;">

            <span style="font-size: 13px; float:right;"><?php _e('Proudly brought to you by', 'wc4bp'); ?><a href="http://themekraft.com/" target="_new">Themekraft</a>.</span>
        </div>
        <br>
		<?php
		
		$number = 20;
		
		$count_users = count_users();
		$total_users = $count_users['total_users'];
		$total_pages = intval( $total_users / $number ) + 1;
		
		?>

        <input id="wc4bp_total_user_pages" type="hidden" value="<?php echo $total_pages ?>">

        <div id="result"></div>

        <form method="post" action="options.php">
			<?php wp_nonce_field( 'update-options' ); ?>
			<?php settings_fields( 'wc4bp_options_sync' ); ?>
			<?php do_settings_sections( 'wc4bp_options_sync' ); ?>

        </form>
        </div><?php
		
	}
	
	/**
	 * Register the admin settings
	 *
	 * @author Sven Lehnert
	 * @package TK Loop Designer
	 * @since 1.0
	 */
	public function wc4bp_register_admin_settings_sync() {
		
		register_setting( 'wc4bp_options_sync', 'wc4bp_options_sync' );
		
		// Settings fields and sections
		add_settings_section( 'section_sync', 'Profile Field Synchronisation Settings', '', 'wc4bp_options_sync' );
		add_settings_section( 'section_general', 'Default BuddyPress WooCommerce Profile Field Settings', '', 'wc4bp_options_sync' );
		
		add_settings_field( 'wc4bp_shop_profile_sync', '<b>WooCommerce BuddyPress Profile Fields Sync </b>', array( $this, 'wc4bp_shop_profile_sync' ), 'wc4bp_options_sync', 'section_sync' );
		add_settings_field( 'wc4bp_change_xprofile_visabilyty_by_user', '<b>Change Profile Field Visibility for all Users</b>', array( $this, 'wc4bp_change_xprofile_visabilyty_by_user' ), 'wc4bp_options_sync', 'section_sync' );
		
		add_settings_field( 'wc4bp_change_xprofile_visabilyty_default', '<b>Set the Default Profile Fields Visibility</b>', array( $this, 'wc4bp_change_xprofile_visabilyty_default' ), 'wc4bp_options_sync', 'section_general' );
		add_settings_field( 'wc4bp_change_xprofile_allow_custom_visibility', '<b>Allow Custom Visibility Change by User</b>', array( $this, 'wc4bp_change_xprofile_allow_custom_visibility' ), 'wc4bp_options_sync', 'section_general' );
	}
	
	public function wc4bp_shop_profile_sync() {
		include_once( dirname( __FILE__ ) . '\views\html_admin_sync_shop_profile.php' );

	}
	
	public function wc4bp_shop_profile_sync_ajax() {
		$update_type = $_POST['update_type'];
		
		$number = 20;
		$paged  = isset( $_POST['wc4bp_page'] ) ? $_POST['wc4bp_page'] : 1;
		$offset = ( $paged - 1 ) * $number;
		$query  = get_users( '&offset=' . $offset . '&number=' . $number );
		include_once( dirname( __FILE__ ) . '\views\html_admin_sync_shop_profile_sync_ajax.php' );
	}
	
	
	public function wc4bp_sync_from_admin( $user_id ) {
		
		// get the profile fields
		$shipping = bp_get_option( 'wc4bp_shipping_address_ids' );
		$billing  = bp_get_option( 'wc4bp_billing_address_ids' );
		
		$groups = BP_XProfile_Group::get( array(
			'fetch_fields' => true
		) );
		
		
		if ( ! empty( $groups ) ) : foreach ( $groups as $group ) :
			
			if ( empty( $group->fields ) ) {
				continue;
			}
			
			foreach ( $group->fields as $field ) {
				
				$billing_key  = array_search( $field->id, $billing );
				$shipping_key = array_search( $field->id, $shipping );
				
				if ( $shipping_key ) {
					$type       = 'shipping';
					$field_slug = $shipping_key;
				}
				
				if ( $billing_key ) {
					$type       = 'billing';
					$field_slug = $billing_key;
				}
				
				if ( isset( $field_slug ) ) {
					xprofile_set_field_data( $field->id, $user_id, get_user_meta( $user_id, $type . '_' . $field_slug, true ) );
				}
			}
		
		endforeach; endif;
		
	}
	
	
	public function select_visibility_levels( $name ) {
		
		$visibility_levels = '<select id="wc4bp_set_bp_' . $name . '" name="wc4bp_options_sync[' . $name . ']">

    <option value="none">Select Visibility</option>';
		
		foreach ( bp_xprofile_get_visibility_levels() as $level ) {
			
			$visibility_levels .= '<option value="' . $level['id'] . '" >' . $level['label'] . '</option>';
			
		}
		$visibility_levels .= '</select>';
		
		echo $visibility_levels;
	}
	
	
	public function wc4bp_change_xprofile_visabilyty_by_user() {
		include_once( dirname( __FILE__ ) . '\views\html_admin_sync_change_xprofile.php' );
	}
	
	public function wc4bp_change_xprofile_visabilyty_by_user_ajax( $user_id ) {
		
		// get the corresponding  wc4bp fields
		$shipping         = bp_get_option( 'wc4bp_shipping_address_ids' );
		$billing          = bp_get_option( 'wc4bp_billing_address_ids' );
		$visibility_level = $_POST['visibility_level'];
		
		foreach ( $shipping as $key => $field_id ) {
			xprofile_set_field_visibility_level( $field_id, $user_id, $visibility_level );
		}
		foreach ( $billing as $key => $field_id ) {
			xprofile_set_field_visibility_level( $field_id, $user_id, $visibility_level );
		}
	}
	
	public function wc4bp_change_xprofile_visabilyty_default() {
		?><p><?php _e('Set the default profile field viability to', 'wc4bp'); ?></p>
		<?php $this->select_visibility_levels( 'default_visibility' ); ?>
        <input type="submit" class="button" name="wc4bp_options_sync[change_xprofile_visabilyty_field_default]" value="Change now">
		<?php
		$wc4bp_options_sync = get_option( 'wc4bp_options_sync' );
		
		$billing  = bp_get_option( 'wc4bp_billing_address_ids' );
		$shipping = bp_get_option( 'wc4bp_shipping_address_ids' );
		
		if ( isset( $wc4bp_options_sync['change_xprofile_visabilyty_field_default'] ) ) {
			
			echo '<ul>';
			
			foreach ( $billing as $key => $field_id ) {
				bp_xprofile_update_field_meta( $field_id, 'default_visibility', $wc4bp_options_sync['default_visibility'] );
				echo '<li>billing_' . $key . ' default visibility changed to ' . $wc4bp_options_sync['default_visibility'] . '</li>';
			}
			echo '</ul>';
			
			echo '<ul>';
			foreach ( $shipping as $key => $field_id ) {
				bp_xprofile_update_field_meta( $field_id, 'default_visibility', $wc4bp_options_sync['default_visibility'] );
				echo '<li>shipping_' . $key . ' default visibility changed to ' . $wc4bp_options_sync['default_visibility'] . '</li>';
			}
			echo '</ul>';
			echo '<h3>All Done!</h3>';
			unset( $wc4bp_options_sync['change_xprofile_visabilyty_field_default'] );
			update_option( 'wc4bp_options_sync', $wc4bp_options_sync );
		}
	}
	
	public function wc4bp_change_xprofile_allow_custom_visibility() {
		include_once( dirname( __FILE__ ) . '\views\html_admin_sync_change_xprofile_allow_custom.php' );

		$wc4bp_options_sync = get_option( 'wc4bp_options_sync' );
		
		$billing  = bp_get_option( 'wc4bp_billing_address_ids' );
		$shipping = bp_get_option( 'wc4bp_shipping_address_ids' );
		
		if ( isset( $wc4bp_options_sync['allow_custom_visibility'] ) ) {
			echo '<ul>';
			foreach ( $billing as $key => $field_id ) {
				bp_xprofile_update_field_meta( $field_id, 'allow_custom_visibility', $wc4bp_options_sync['custom_visibility'] );
				echo '<li>billing_' . $key . ' default visibility changed to ' . $wc4bp_options_sync['custom_visibility'] . '</li>';
			}
			echo '</ul>';
			
			echo '<ul>';
			foreach ( $shipping as $key => $field_id ) {
				bp_xprofile_update_field_meta( $field_id, 'allow_custom_visibility', $wc4bp_options_sync['visibility_levels'] );
				echo '<li>shipping_' . $key . ' default visibility changed to ' . $wc4bp_options_sync['custom_visibility'] . '</li>';
			}
			echo '</ul>';
			
			echo '<h3>All Done!</h3>';
			unset( $wc4bp_options_sync['allow_custom_visibility'] );
			update_option( 'wc4bp_options_sync', $wc4bp_options_sync );
		}
	}
}