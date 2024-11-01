<?php
/*
 * Plugin Name:  Users Import Export with Excel for WordPress
 * Description: Import Export Users for WordPress with Excel.
 * Version: 1.5
 * Author: extendWP
 * Author URI: https://extend-wp.com
 *
 * WC requires at least: 2.2
 * WC tested up to: 8.4
 * 
 * License: GPL2
 * Created On: 28-05-2016
 * Updated On: 03-01-2024
 * Text Domain:       imue
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /lang 
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


add_action('plugins_loaded', 'imue_translate');
function imue_translate() {
	load_plugin_textdomain( 'imue', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}


/**
 * Check if WooCommerce is active
 * if wooCommerce is not active plugin will not work.
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    function imue_notice() {

        // Deactivate the plugin
           deactivate_plugins(__FILE__);
		$error_message = __('This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be installed and active!', 'woocommerce');
		die($error_message);
	}
	add_action( 'admin_notices', 'imue_notice' );
}

include( plugin_dir_path(__FILE__) .'/users-customers-import-export-excel-wp_users.php');

function load_imue_js(){
	wp_enqueue_style( 'imue_css', plugins_url( "/css/imue.css?v=lotts", __FILE__ ) );	
	wp_enqueue_style( 'imue_css');		

	
	if( ! wp_script_is( "imue_fa", 'enqueued' ) ) {
		wp_enqueue_style( 'imue_fa', plugins_url( '/css/font-awesome.min.css', __FILE__ ));	
	}
	
	wp_enqueue_script( 'imue-xlsx', plugins_url( "/js/xlsx.js", __FILE__ ), array('jquery') , null, true );	
	wp_enqueue_script( 'imue-xlsx');	
	wp_enqueue_script( 'imue-filesaver', plugins_url( "/js/filesaver.js", __FILE__ ), array('jquery') , null, true );	
	wp_enqueue_script( 'imue-filesaver');	

    wp_enqueue_script( 'imue-mainJs', plugins_url( '/js/imue.js?v=lotts', __FILE__ ), array('jquery','jquery-ui-core','jquery-ui-tabs','jquery-ui-draggable','jquery-ui-droppable') , null, true);		
	wp_enqueue_script( 'imue-mainJs');

    $imue = array( 
		'RestRoot' => esc_url_raw( rest_url() ),
		'plugin_url' => plugins_url( '', __FILE__ ),
		'siteUrl'	=>	site_url(),
		'nonce' => wp_create_nonce( 'wp_rest' ),
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'exportfile' => plugins_url( '/js/tableexport.js', __FILE__ )
	);	
    wp_localize_script( 'imue-mainJs', 'imue', $imue );	
	
}

add_action('admin_enqueue_scripts', 'load_imue_js');

//RUN IMPORT BATCH

add_action( 'wp_ajax_imue_process', 'imue_process' );
add_action( 'wp_ajax_nopriv_imue_process',  'imue_process' );

//RUN EXPORT BATCH

add_action( 'wp_ajax_imue_exportUsers', 'imue_exportUsers' );
add_action( 'wp_ajax_nopriv_imue_exportUsers',  'imue_exportUsers' );

//ON ACTIVATION OF FREE , DEACTIVE THE PRO IF ENABLED

function imue_activate(){
	require_once(ABSPATH .'/wp-admin/includes/plugin.php');
	$pro = content_url(). "/plugins/users-customers-import-export-excel-wp-pro/users-customers-import-export-excel-wp-pro.php";
	deactivate_plugins($pro);
}
register_activation_hook( __FILE__, 'imue_activate' );

//ADD MENU LINK AND PAGE FOR WOOCOMMERCE IMPORTER

add_action('admin_menu', 'imue_menu');

function imue_menu() {
	
	add_submenu_page( 'users.php',__("Import-Export","imue"), __("Users Import / Export","imue"), 'manage_options', 'imue', 'imue_init' );	

}


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_imue_links' );

function add_imue_links ( $links ) {
	
	$links[] =  "<a href='" . admin_url( 'admin.php?page=imue' ) . "'>".__("Settings","imue")."</a>";
	return $links;
   
}


//MAIN VIEW
function imue_init() {
		
		imue_form_header();
		
		$users = new ImueUsers;
		?>
		<div class="imue">
			<?php			
			$tabs = array(
				'main' => __("Import/Update Users","imue"),
				'exportUsers' =>  __("Export Users","imue"),
			);
			
			if(isset($_GET['tab']) && $_GET['tab'] ){
				$current = $_GET['tab'] ;
			}else $current = 'main';
			echo '<h2 class="nav-tab-wrapper" >';
			foreach( $tabs as $tab => $name ){
				$class = ( $tab === $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?page=imue&tab=$tab'>$name</a>";

			}
			echo "<a class='nav-tab$class prem' target='_blank' style='background:#00a32a;color:#fff' href='https://extend-wp.com/product/users-customers-import-export-excel-wordpress-woocommerce/'>PRO Version</a>";
			echo '</h2>';?>
			
			<div class='msg'></div>


			
			<div class='mainWrapper'>
			<?php
			if(isset ( $_GET['tab'] )  && $_GET['tab']==='exportUsers'){
				$users->exportUsersDisplay() ;
			}else  $users->importUsersDisplay();?>
			</div>			

			<div class=' rightToLeft proVersion'>
			<h2  class='center'><?php _e("NEED MORE FEATURES? GO PRO!","imue");?></h2>
					<p>&#10004; <?php _e("Automap excel columns to User fields for ease of use","imue");?></p>
					<p>&#10004; <?php _e("Export WooCommerce Customer in Excel","imue");?></p>
					<p>&#10004; <?php _e("Choose Role to Export","imue");?></p>
					<p>&#10004; <?php _e("Filter Export by User Creation Date","imue");?></p>
					<p>&#10004; <?php _e("Send Notification Email to Excel Imported Users","imue");?></p>
					<p>&#10004; <?php _e("Choose to Update on Import Existing Users","imue");?></p>	
			<p class='center'>			
				<a target='_blank'  href='https://extend-wp.com/product/users-customers-import-export-excel-wordpress-woocommerce'>
					<img class='premium_img' src='<?php echo plugins_url( 'images/users-customers-import-export-excel-wp-woocommerce-pro.png', __FILE__ ); ?>' alt='<?php _e("Users-Customers Import Export with Excel for WordPress & WooCommerce PRO","imue");?>' title='<?php _e("Users-Customers Import Export with Excel for WordPress & WooCommerce PRO","imue");?>' />
				</a>
			<p  class='center'>
				<a class='premium_button' target='_blank'  href='https://extend-wp.com/product/users-customers-import-export-excel-wordpress-woocommerce'>
					<span class="dashicons dashicons-tag"></span> <?php _e("Get PRO Version here","imue");?>	
				</a>
			</p>
			<p>	
				<a target='_blank' class='wp_extensions'  style='text-align:center;margin:0 auto' href='https://extend-wp.com'>
					<b>
						<span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e( "Check More extend-wp Plugins", "imue" ); ?>
					</b>
				</a>
			</p>			
			</div>

		<div class='get_ajax' style='width:100%;overflow:hidden;' ></div>
			<div class=''>	
				<?php imue_Rating(); ?>
			</div>				
		</div>
	
		<?php	
		imue_form_footer();		
}

function imue_form_header() {
?>
	<h2>
		<?php _e("Users Import Export with Excel for WordPress","imue");?>
	</h2>
<?php
}

function imue_form_footer() {
?>
	<hr>

			
		<a target='_blank' class='imueLogo' href='https://extend-wp.com'>
			<img  src='<?php echo plugins_url( 'images/extendwp.png', __FILE__ ); ?>' alt='<?php _e("By extend-wp.com","imue");?>' title='<?php _e("By extend-wp.com","imue");?>' />
		</a>
		
		
<?php
}

function imue_Rating(){
	?>
		<div class="notice notice-success rating is-dismissible">
			<p>
			<strong><?php esc_html_e( "You like this plugin? ", 'imue' ); ?></strong><i class='fa fa-2x fa-smile-o' ></i><br/> <?php esc_html_e( "Then please give us ", 'imue' ); ?> 
				<a target='_blank' href='https://wordpress.org/support/plugin/users-import-export-with-excel-for-wp/reviews/#new-post'>
					<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
				</a>
			</p>
		</div> 	
	<?php	
}

	 function imue_Popup(){ ?>
		<div id="imue_popup">
		  <!-- Modal content -->
		  <div class="modal-content">
			<div class='clearfix'><span class="close">&times;</span></div>
			<div class='clearfix verticalAlign'>
				<div class='columns2'>
					<center>
						<img style='width:90%' src='<?php echo plugins_url( 'images/users-customers-import-export-excel-wp-woocommerce-pro.png', __FILE__ ); ?>' style='width:100%' />
					</center>
				</div>
				
				<div class='columns2'>
					<h3><?php _e("Go PRO and get more important features!","imue");?></h3>
					<p>&#10004; <?php _e("Export WooCommerce Customer in Excel","imue");?></p>
					<p>&#10004; <?php _e("Choose Role to Export","imue");?></p>
					<p>&#10004; <?php _e("Filter Export by User Creation Date","imue");?></p>
					<p>&#10004; <?php _e("Send Notification Email to Excel Imported Users","imue");?></p>
					<p>&#10004; <?php _e("Choose to Update on Import Existing Users","imue");?></p>
					<p class='bottomToUp'><center><a target='_blank' class='premium_button' href='https://extend-wp.com/product/users-customers-import-export-excel-wordpress-woocommerce'><span class="dashicons dashicons-tag"></span> <?php _e("GET IT HERE","imue");?></a></center></p>
				</div>
			</div>
		  </div>
		</div>		
		<?php
	}
	add_action( 'admin_footer', 'imue_Popup');
	

// deactivation survey 

include( plugin_dir_path(__FILE__) .'/lib/codecabin/plugin-deactivation-survey/deactivate-feedback-form.php');	
add_filter('codecabin_deactivate_feedback_form_plugins', function($plugins) {

	$plugins[] = (object)array(
		'slug'		=> 'users-import-export-with-excel-for-wp',
		'version'	=> '1.5'
	);

	return $plugins;

});


// Email notification form
	
register_activation_hook( __FILE__, 'imue_notification_hook' );

function imue_notification_hook() {
    set_transient( 'imue_notification', true );
}

add_action( 'admin_notices', 'imue_notification' );

function imue_notification(){

	$screen = get_current_screen();
	//var_dump( $screen );
	if ( 'users_page_imue'  !== $screen->base )
	return;
		
    /* Check transient, if available display notice */
    if( get_transient( 'imue_notification' ) ){
        ?>
        <div class="updated notice  imue_notification">
			<a href="#" class='dismiss' style='float:right;padding:4px' >close</a>
            <h3><i><?php esc_html_e( "Add your Email below & get discounts ", 'imue' ); ?><strong style='color:#00a32a'><?php esc_html_e( " discounts", 'imue' ); ?></strong><?php esc_html_e( " in our pro plugins at", 'imue' ); ?> <a href='https://extend-wp.com' target='_blank' >extend-wp.com!</a></i></h3>
			<form method='post' id='imue_signup'>
				<p>
				<input required type='email' name='woopei_email' />
				<input required type='hidden' name='product' value='2677' />
				<input type='submit' class='button button-primary' name='submit' value='<?php esc_html_e("Sign up", "imue" ); ?>' />
				</p>
				
			</form>
        </div>
        <?php
    }
}
add_action( 'wp_ajax_nopriv_imue_push_not', 'imue_push_not'  );
add_action( 'wp_ajax_imue_push_not', 'imue_push_not' );

function imue_push_not(){
	
	delete_transient( 'imue_notification' );
			
}
	
// HPOS compatibility declaration

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );



	// check more if you like!
	add_action( 'wp_ajax_nopriv_imue_extensions', 'imue_extensions' );
	add_action( 'wp_ajax_imue_extensions', 'imue_extensions' );
	
	function imue_extensions(){
		
		if( is_admin() && current_user_can( 'administrator' ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'imue_extensions' ){
			
			$response = wp_remote_get( 'https://extend-wp.com/wp-json/products/v2/product/category/woocommerce' );
			
			if( is_wp_error( $response ) ) {
				return;
			}	
			
			$posts = json_decode( wp_remote_retrieve_body( $response ) );

			if( empty( $posts ) ) {
				return;
			}

			if( !empty( $posts ) ) {
				
				$allowed_html = array(
							'a' => array(
								'style' => array(),
								'href' => array(),
								'title' => array(),
								'class' => array(),
								'id'=>array()                   
							),
							'i' => array('style' => array(),'class' => array(),'id'=>array() ),
							'br' => array('style' => array(),'class' => array(),'id'=>array() ),
							'em' => array('style' => array(),'class' => array(),'id'=>array() ),
							'strong' => array('style' => array(),'class' => array(),'id'=>array() ),
							'h1' => array('style' => array(),'class' => array(),'id'=>array() ),
							'h2' => array('style' => array(),'class' => array(),'id'=>array() ),
							'h3' => array('style' => array(),'class' => array(),'id'=>array() ),
							'h4' => array('style' => array(),'class' => array(),'id'=>array() ),
							'h5' => array('style' => array(),'class' => array(),'id'=>array() ),
							'h6' => array('style' => array(),'class' => array(),'id'=>array() ),
							'img' => array('style' => array(),'class' => array(),'id'=>array() ),
							'p' => array('style' => array(),'class' => array(),'id'=>array() ),
							'ul' => array('style' => array(),'class' => array(),'id'=>array() ),
							'li' => array('style' => array(),'class' => array(),'id'=>array() ),
							'ol' => array('style' => array(),'class' => array(),'id'=>array() ),
							'video' => array('style' => array(),'class' => array(),'id'=>array() ),
							'blockquote' => array('style' => array(),'class' => array(),'id'=>array() ),
							'style' => array(),            
							'img' => array(
								'alt' => array(),
								'src' => array(),
								'title' => array(),
								'style' => array(),
								'class' => array(),
								'id'=>array()
							),
					);				
				
				echo "<div id='imue_extensions_popup'>";
					echo "<div class='imue_extensions_content'>";	
						?>
						<span class="imueclose">&times;</span>
						<h2><i><?php esc_html_e( 'Extend your WordPress functionality with Extend-WP.com well crafted Premium Plugins!','imue' ); ?></i></h2>
						<hr/>
						<?php
						foreach( $posts as $post ) {
							
							echo "<div class='ex_columns'><a target='_blank' href='".esc_url( $post->url )."' /><img src='".esc_url( $post->image )."' /></a>
							<h3><a target='_blank' href='".esc_url( $post->url )."' />". esc_html( $post->title ) . "</a></h3>
							<div>". wp_kses( $post->excerpt, $allowed_html )."</div>
							<a class='button_extensions button-primary' target='_blank' href='".esc_url( $post->url )."' />". esc_html__( 'Get it here', 'imue' ) . " <i class='fa fa-angle-double-right'></i></a>
							</div>";
						}
					echo '</div>';
				echo '</div>';	
			}
			wp_die();
		}
	}	