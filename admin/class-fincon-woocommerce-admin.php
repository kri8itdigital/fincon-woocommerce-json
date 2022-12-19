<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.kri8it.com
 * @since      1.0.0
 *
 * @package    Fincon_Woocommerce
 * @subpackage Fincon_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fincon_Woocommerce
 * @subpackage Fincon_Woocommerce/admin
 * @author     Hilton Moore <hilton@kri8it.com>
 */
class Fincon_Woocommerce_Admin {









	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;









	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}









	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fincon-woocommerce-admin.css', array(), $this->version, 'all' );

	}









	
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fincon-woocommerce-admin.js', array( 'jquery' ), $this->version, false );

		$params = array(
			'ajax_url' => get_bloginfo('url').'/wp-admin/admin-ajax.php'
		);

		wp_localize_script( $this->plugin_name, 'fincon_params', $params );  

	}









	
	/**
	 * Creating Settings Tab Page
	 *
	 * @since    1.0.0
	 */
	public function get_settings_pages($settings){


		include plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fincon-woocommerce-settings.php';

		$settings[] = new fincon_woocommerce_settings();


		return $settings;

	}









	
	/**
	 * Set Cron Schedules for intial import
	 *
	 * @since    1.1.2
	 */
	public function setup_initial_schedules(){


		if(get_option('fincon_woocommerce_active') == 'yes'):

			if(get_option('fincon_woocommerce_sync_stock') == 'yes' && !get_option('fincon_woocommerce_do_inital_product_sync')):

				if(!get_option('fincon_woocommerce_product_sync_running') || get_option('fincon_woocommerce_product_sync_running') == 'no'):

					wp_schedule_single_event(time(), 'fincon_woocommerce_sync_products');

				endif;
				
			endif;

			if(get_option('fincon_woocommerce_sync_accounts') == 'yes' && !get_option('fincon_woocommerce_do_inital_user_sync')):

				if(!get_option('fincon_woocommerce_user_sync_running') || get_option('fincon_woocommerce_user_sync_running') == 'no'):

					wp_schedule_single_event(time(), 'fincon_woocommerce_sync_accounts');

				endif;

			endif;

		endif;

	}









	
	/**
	 * Get Cron Schedules
	 *
	 * @since    1.0.0
	 */
	public function cron_schedules($schedules){

		$schedules['twohours'] = array(
	        'interval' => 7200,
	        'display'  => esc_html__( 'Every Two Hours' ),
	    );

	    $schedules['fiveseconds'] = array(
        	'interval' => 5,
        	'display'  => esc_html__( 'Every Five Seconds' ), 
    	);



		return $schedules;

	}









	
	/**
	 * Set Cron Schedules
	 *
	 * @since    1.0.0
	 */
	public function setup_cron_schedules(){


		if (! wp_next_scheduled( 'fincon_woocommerce_check_status')):


			$_INTERVAL = get_option('fincon_woocommerce_interval');

			wp_schedule_event(time(), $_INTERVAL, 'fincon_woocommerce_check_status');


		endif;


		if (! wp_next_scheduled( 'fincon_woocommerce_clean_logs')):

			wp_schedule_event(time(), 'daily', 'fincon_woocommerce_clean_logs');

		endif;


		if(get_option('fincon_woocommerce_sync_stock') == 'yes'):


			if (! wp_next_scheduled( 'fincon_woocommerce_sync_products')):


				$_INTERVAL = get_option('fincon_woocommerce_interval');

				wp_schedule_event(time(), $_INTERVAL, 'fincon_woocommerce_sync_products');


			endif;

		else:

			if (wp_next_scheduled( 'fincon_woocommerce_sync_products')):

				wp_clear_scheduled_hook('fincon_woocommerce_sync_products');
				
			endif;

		endif;
			

		if(get_option('fincon_woocommerce_sync_users') == 'yes'):


			if (! wp_next_scheduled( 'fincon_woocommerce_sync_accounts')):


				$_INTERVAL = get_option('fincon_woocommerce_interval');

				wp_schedule_event(time(), $_INTERVAL, 'fincon_woocommerce_sync_accounts');


			endif;

		else:

			if (wp_next_scheduled( 'fincon_woocommerce_sync_accounts')):

				wp_clear_scheduled_hook('fincon_woocommerce_sync_accounts');
				
			endif;

		endif;

	}









	
	/**
	 * Increase HTTP Timeouts
	 *
	 * @since    2.3.2
	 */
	public function http_request_timeout(){
		return 10000;
	}









	
	/**
	 * Add Columns to shop order table for the Fincon Sales Order Number
	 *
	 * @since    1.0.0
	 */
	public static function shop_order_columns($columns){

	    $reordered_columns = array();

	    // Inserting columns to a specific location
	    foreach( $columns as $key => $column){
	        $reordered_columns[$key] = $column;
	        if( $key ==  'order_status' ){
	            // Inserting after "Status" column
	            $reordered_columns['order_fincon_so'] = __( 'Fincon Sales Order','woocommerce');
	        }
	    }
	    return $reordered_columns;

	}









	
	/**
	 * Outputs Column Data for Fincon Sales Order Number on the shop order table
	 *
	 * @since    1.0.0
	 */
	public static function shop_order_posts_custom_column($column){

		global $post;

		if ( 'order_fincon_so' === $column ):
			if(get_post_meta($post->ID, '_fincon_sales_order', true)):
				echo '<div class="fincon-woocommerce-column-_content"><div class="fincon_sales_order">'.get_post_meta($post->ID, '_fincon_sales_order', true).'</div></div>';
			elseif(get_post_meta($post->ID, '_fincon_sales_error', true)):
				echo '<div class="fincon-woocommerce-column-_content">';
				echo '<em class="error_title">ERRORS:</em><ol>';
				foreach(get_post_meta($post->ID, '_fincon_sales_error', true) as $ERR):
					echo '<li>'.$ERR.'</li>';
				endforeach;
				echo '</ol>';
				echo '</div>';
				echo '<a class="fincon_woocommerce_ajax_create_sales_order button wc-action-button" data-o="'.$post->ID.'">Send</a>';
			else:

				if($post->post_status == 'wc-processing' || $post->post_status == 'wc-completed'):

					echo '<div class="k8_sync_column_content"></div>';
					echo '<a class="fincon_woocommerce_ajax_create_sales_order button wc-action-button" data-o="'.$post->ID.'">Send</a>';
					
				endif;
			endif;
		endif;
	
	}









	
	/**
	 * Once an order is marked as paid - send to fincon
	 *
	 * @since    1.0.0
	 */
	public function order_status_processing($order_id){

		if(!get_post_meta($order_id, '_fincon_sales_order', true)):

			$_FINCON = new WC_Fincon();
			
			$_FINCON->run_sales_order($order_id);

			if(get_option('fincon_woocommerce_enable_so_email') == 'yes' && get_post_meta($order_id, '_fincon_sales_error', true)):
				$this->do_email_notification('so', $_FINCON->_ERRORS, $order_id);
			endif;

			unset($_FINCON);

		endif;

	}









	
	/**
	 * Ajax function for manually sending a Sales Order to Fincon
	 *
	 * @since    1.0.0
	 */
	public function ajax_create_so(){

		$O = $_POST['o'];

		$_FINCON = new WC_Fincon();
		
		$_FINCON->run_sales_order($O);

		unset($_FINCON);

		sleep(1);

		$_RETURN = array();

		if(get_post_meta($O, '_fincon_sales_order', true)):

			$_RETURN['status'] = 'yes';
			$_RETURN['so'] = get_post_meta($O, '_fincon_sales_order', true);
			$_RETURN['text'] = '<div class="fincon_sales_order">'.$_RETURN['so'].'</div>';

		else:
			$_RETURN['status'] = 'no';

			$_ERRORS = get_post_meta($O, '_fincon_sales_error', true);
			$_ERRORS_LIST = '';
			$_ERRORS_LIST.= '<em class="error_title">ERRORS:</em><ol>';
				foreach($_ERRORS as $ERR):
					$_ERRORS_LIST.= '<li>'.$ERR.'</li>';
				endforeach;
				$_ERRORS_LIST.= '</ol>';

			$_RETURN['errors'] = $_ERRORS_LIST;


		endif;

		echo json_encode($_RETURN);			

		exit;
	}









	
	/**
	 * Checks whether the Fincon Connection is active
	 *
	 * @since    1.1.1
	 */
	public static function check_details($URL, $AUN, $APW, $DATA, $DUN, $DPW){

		$_LIVE = WC_Fincon::ValidateCustom($URL, $AUN, $APW, $DATA, $DUN, $DPW);

		if($_LIVE['status'] == 'live'):
			update_option('fincon_woocommerce_admin_message_text', 'Fincon <strong><em>is</em></strong> connected.');
			update_option('fincon_woocommerce_admin_message_type', 'notice-info');
		else:
			update_option('fincon_woocommerce_admin_message_text', 'Fincon is <strong><em>not</em></strong> connected: '.$_LIVE['error']);
			update_option('fincon_woocommerce_admin_message_type', 'notice-error');
		endif;

		update_option('fincon_woocommerce_admin_message_date', wp_date('Y-m-d H:i:s'));

		return $_LIVE['status'];

	}









	
	/**
	 * Checks whether the Fincon Connection is active
	 *
	 * @since    1.0.0
	 */
	public static function check_api(){

		$_FINCON = new WC_Fincon();
		$_LIVE = $_FINCON->Validate();

		if($_LIVE['status'] == 'live'):
			update_option('fincon_woocommerce_admin_message_text', 'Fincon <strong><em>is</em></strong> connected.');
			update_option('fincon_woocommerce_admin_message_type', 'notice-info');
		else:
			update_option('fincon_woocommerce_admin_message_text', 'Fincon is <strong><em>not</em></strong> connected: '.$_LIVE['error']);
			update_option('fincon_woocommerce_admin_message_type', 'notice-error');

			update_option('fincon_woocommerce_active', 'no');

			if(get_option('fincon_woocommerce_enable_connection_email') == 'yes'):
				self::do_email_notification('connection', $_LIVE['error']);
			endif;

		endif;

		update_option('fincon_woocommerce_admin_message_date', wp_date('Y-m-d H:i:s'));

	}









	
	/**
	 * Cleans Old Logs
	 *
	 * @since    1.2.0
	 */
	public static function clean_logs(){
		WC_Fincon_Logger::clean();
	}









	
	/**
	 * Email Notifications
	 *
	 * @since    1.1.1
	 */
	public static function do_email_notification($TYPE, $_ERROR = null, $_ID = null){

		$_SEND_TO = get_option('fincon_woocommerce_email_list');

		$_LOG_FILE = WC_Fincon_Logger::attachment();

		$_ATTACHMENTS = array($_LOG_FILE);

		$_MESSAGE = '<p>To whom it may concern, </p>';


		$_HAS_ERRORS = false;
		$_ERROR_TEXT = '';

		if(is_string($_ERROR) && strlen($_ERROR) > 0):
			$_HAS_ERRORS = true;
			$_ERROR_TEXT .= '<p>The error that has been logged is: <strong>'.$_ERROR.'</strong></p>';
		endif;

		if(is_array($_ERROR) && count($_ERROR) > 0):
			$_HAS_ERRORS = true;

			$_ERROR_TEXT .= '<p><em>The errors encountered were:</em></p>';
			$_ERROR_TEXT .= '<ul>';
			foreach($_ERROR as $_ERR):
				$_ERROR_TEXT .= '<li>'.$_ERR.'</li>';
			endforeach;
			$_ERROR_TEXT .= '</ul>';
		endif;

		switch($TYPE):

			case "connection":

				$_SUBJECT = 'Fincon Connection on '.get_bloginfo('name').' has gone down';
				
				$_MESSAGE .= '<p>This is a courtesy email to inform you that the Fincon connection on your website <strong>'.get_bloginfo('name').'</strong> has gone down.</p>';

				$_MESSAGE .= '<p>The sync has been disabled automatically for now. Please check your settings and adjust accordingly.</p>';

			break;

			case "products":

				if($_HAS_ERRORS):

					$_SUBJECT = 'Product Sync on '.get_bloginfo('name').' has completed with errors';

				else:

					$_SUBJECT = 'Product Sync on '.get_bloginfo('name').' has completed successfully';

				endif;

				$_MESSAGE .= '<p>This is a courtesy email to inform you that a Fincon product sync on your website <strong>'.get_bloginfo('name').'</strong> has completed.</p>';

			break;

			case "users":

				if($_HAS_ERRORS):

					$_SUBJECT = 'User Sync on '.get_bloginfo('name').' has completed with errors';

				else:

					$_SUBJECT = 'User Sync on '.get_bloginfo('name').' has completed successfully';

				endif;

				$_MESSAGE .= '<p>This is a courtesy email to inform you that a Fincon user sync on your website <strong>'.get_bloginfo('name').'</strong> has completed.</p>';


			break;

			case "so":

				$_SUBJECT = 'Sales Order Creation failure on '.get_bloginfo('name');

				$_MESSAGE .= '<p>This is a courtesy email to inform you that a Fincon Sales Order failed to be created for Order #'.$_ID.' on your website <strong>'.get_bloginfo('name').'</strong>.</p>';

			break;

		endswitch;

		if($_HAS_ERRORS && strlen($_ERROR_TEXT) > 0):
			$_MESSAGE .= $_ERROR_TEXT;
		endif;

		$_MESSAGE .= '<p>The log file for today is attached. You can also view it on your Fincon status page.</p>';

		$_MESSAGE .= '<p>Fincon Accounting.</p>';


		$_HEADERS = array('Content-Type: text/html; charset=UTF-8');

		wp_mail($_SEND_TO, $_SUBJECT, $_MESSAGE, $_HEADERS, $_ATTACHMENTS);

	}









	
	/**
	 * Admin hook for custom pages
	 *
	 * @since    1.2.0
	 */
	public function admin_menu(){

		add_submenu_page( 'woocommerce', 'Fincon Status', 'Fincon Status', 'manage_woocommerce', 'fincon-status', array($this, 'admin_menu_stats_display'));

	}









	
	/**
	 * Status page
	 *
	 * @since    1.2.0
	 */
	public function admin_menu_stats_display(){

    	$_FILES = WC_Fincon_Logger::fetch();

		$_ARRAY_OF_FINCON_VARIABLES = array(
			'fincon_woocommerce_active' 						=> get_option('fincon_woocommerce_active'),
			'fincon_woocommerce_admin_message_text' 			=> get_option('fincon_woocommerce_admin_message_text'),
			'fincon_woocommerce_admin_message_type' 			=> get_option('fincon_woocommerce_admin_message_type'),
			'fincon_woocommerce_admin_message_date' 			=> get_option('fincon_woocommerce_admin_message_date'),
			'fincon_woocommerce_do_inital_product_sync' 		=> get_option('fincon_woocommerce_do_inital_product_sync'),
			'fincon_woocommerce_do_inital_product_sync_date' 	=> get_option('fincon_woocommerce_do_inital_product_sync_date'),
			'fincon_woocommerce_do_inital_user_sync' 			=> get_option('fincon_woocommerce_do_inital_user_sync'),
			'fincon_woocommerce_do_inital_user_sync_date' 		=> get_option('fincon_woocommerce_do_inital_user_sync_date'),
			'fincon_woocommerce_product_sync_running' 			=> get_option('fincon_woocommerce_product_sync_running'),
			'fincon_woocommerce_last_product_update' 			=> get_option('fincon_woocommerce_product_sync_last_date').' '.get_option('fincon_woocommerce_product_sync_last_time'),
			'fincon_woocommerce_user_sync_running' 				=> get_option('fincon_woocommerce_user_sync_running'),
			'fincon_woocommerce_last_user_update' 				=> get_option('fincon_woocommerce_user_sync_last_date').' '.get_option('fincon_woocommerce_user_sync_last_time'),
			'fincon_woocommerce_logged_in_session' 				=> get_option('fincon_woocommerce_logged_in_session')
		);
		extract($_ARRAY_OF_FINCON_VARIABLES);


		$_CRON_URL = FINCON_WOOCOMMERCE_CRON_BASE;

		?>
		<div class="wrap fincon-status-page">

       	<h1>Fincon Status</h1>
		<div id="FC30SECONDS" class="fincon-admin-info-block"><small><em>This page will refresh every 30 seconds</em></small></div>

		<div class="fincon-admin-status-block">
			<h2>Server Variables</h2>

			<div class="fincon-admin-status-item"> <span>PHP Version:</span> <strong><?php echo phpversion(); ?></strong></div>
		</div>

    	<div class="fincon-admin-status-block">
    		<h2>Fincon Status</h2>

    		<div class="fincon-admin-status-item">
    			<span>Is Fincon Activated:</span> 
				<?php if($fincon_woocommerce_active == 'yes'): ?>
					<strong class="fincon-admin-success">Yes</strong>
				<?php else: ?>
					<strong class="fincon-admin-failure">No</strong>
				<?php endif; ?>
    		</div>

    		<?php if($fincon_woocommerce_logged_in_session != ''): ?>
    			<div class="fincon-admin-status-item">
	    			<span>Current Logged In Session:</span> 
	    			<strong><?php echo $fincon_woocommerce_logged_in_session; ?></strong>
	    		</div>
    		<?php endif; ?>
			
			<?php if($fincon_woocommerce_admin_message_text != ''): ?>
	    		<div class="fincon-admin-status-item">
	    			<span>Fincon Connection Text:</span> 
	    			<strong class="<?php echo $fincon_woocommerce_admin_message_type; ?>"><?php echo $fincon_woocommerce_admin_message_text; ?></strong>
	    		</div>
    		<?php endif; ?>

    		<?php if($fincon_woocommerce_admin_message_date): ?>
				<div class="fincon-admin-status-item">
	    			<span>Last Connection Check:</span> <strong><?php echo $fincon_woocommerce_admin_message_date; ?></strong>
	    		</div>
    		<?php endif; ?>

	    	<?php if(wp_next_scheduled('fincon_woocommerce_check_status')): ?>
				<div class="fincon-admin-status-item">
	    			<span>Next Connection Check:</span> <strong><?php echo wp_date('Y-m-d H:i:s', wp_next_scheduled('fincon_woocommerce_check_status')); ?></strong>
	    		</div>
	    	<?php endif; ?>



    	</div>

    	<div class="fincon-admin-status-block">
    		<h2>Sync Status</h2>

    		<h4>Initial Syncs</h4>

    		<div class="fincon-admin-status-item">
    			<span>Did Initial Product Sync Run:</span> 
				<?php if($fincon_woocommerce_do_inital_product_sync == 'yes'): ?>
					<strong class="fincon-admin-success">Yes</strong>
				<?php else: ?>
					<strong class="fincon-admin-failure">No</strong>
				<?php endif; ?>
    		</div>
    		<?php if($fincon_woocommerce_do_inital_product_sync_date): ?>
	    		<div class="fincon-admin-status-item">
	    			<span>Initial Product Sync:</span> <strong><?php echo $fincon_woocommerce_do_inital_product_sync_date; ?></strong>
	    		</div>
	    	<?php endif; ?>

    		<div class="fincon-admin-status-item">
    			<span>Did Initial User Sync Run:</span>
				<?php if($fincon_woocommerce_do_inital_user_sync == 'yes'): ?>
					<strong class="fincon-admin-success">Yes</strong>
				<?php else: ?>
					<strong class="fincon-admin-failure">No</strong>
				<?php endif; ?>
    		</div>
    		<?php if($fincon_woocommerce_do_inital_user_sync_date): ?>
	    		<div class="fincon-admin-status-item">
	    			<span>Initial User Sync:</span> <strong><?php echo $fincon_woocommerce_do_inital_user_sync_date; ?></strong>
	    		</div>
	    	<?php endif; ?>

    		<h4>Product Syncs</h4>

    		<div class="fincon-admin-status-item">
    			<span>Is a Product Sync Running:</span>
    			<?php if($fincon_woocommerce_product_sync_running == 'yes'): ?>
					<strong class="fincon-admin-success">Yes</strong>
				<?php else: ?>
					<strong class="fincon-admin-failure">No</strong>
				<?php endif; ?>
    		</div>

			<?php if($fincon_woocommerce_product_sync_running == 'yes'): ?>
				<div class="fincon-admin-status-item">
	    			<span>Last Product Sync:</span> <strong class="fincon-admin-success">In Progress</strong>
	    		</div>
    		<?php elseif($fincon_woocommerce_last_product_update): ?>
	    		<div class="fincon-admin-status-item">
	    			<span>Last Product Sync:</span> <strong><?php echo $fincon_woocommerce_last_product_update; ?></strong>
	    		</div>
	    	<?php endif; ?>

	    	<?php if(wp_next_scheduled('fincon_woocommerce_sync_products')): ?>
				<div class="fincon-admin-status-item">
	    			<span>Next Product Sync:</span> <strong><?php echo wp_date('Y-m-d H:i:s', wp_next_scheduled('fincon_woocommerce_sync_products')); ?></strong>
	    		</div>
	    	<?php endif; ?>

    		<h4>User Syncs</h4>

    		<div class="fincon-admin-status-item">
    			<span>Is a User Sync Running:</span>
    			<?php if($fincon_woocommerce_user_sync_running == 'yes'): ?>
					<strong class="fincon-admin-success">Yes</strong>
				<?php else: ?>
					<strong class="fincon-admin-failure">No</strong>
				<?php endif; ?>
    		</div>

    		<?php if($fincon_woocommerce_user_sync_running == 'yes'): ?>
				<div class="fincon-admin-status-item">
	    			<span>Last Product Sync:</span> <strong class="fincon-admin-success">In Progress</strong>
	    		</div>
    		<?php elseif($fincon_woocommerce_last_user_update): ?>
	    		<div class="fincon-admin-status-item">
	    			<span>Last User Sync:</span> <strong><?php echo $fincon_woocommerce_last_user_update; ?></strong>
	    		</div>
	    	<?php endif; ?>

	    	<?php if(wp_next_scheduled('fincon_woocommerce_sync_accounts')): ?>
				<div class="fincon-admin-status-item">
	    			<span>Next User Sync:</span> <strong><?php echo wp_date('Y-m-d H:i:s', wp_next_scheduled('fincon_woocommerce_sync_accounts')); ?></strong>
	    		</div>
	    	<?php endif; ?>


    	</div>



    	<div class="fincon-admin-status-block">
    		<h2>WP Cron Status</h2>

    		<div class="fincon-admin-status-item">
    			<span>Is WP CRON enabled:</span>
    			<?php if(defined('DISABLE_WP_CRON')): ?>
					<?php if(DISABLE_WP_CRON == true || DISABLE_WP_CRON == 1 || DISABLE_WP_CRON == "true"): ?>
					<strong class="fincon-admin-success">No</strong>
					<?php else: ?>
					<strong class="fincon-admin-success">Yes</strong>
					<?php endif; ?>
    			<?php else: ?>
					<strong class="fincon-admin-success">Yes</strong>
    			<?php endif; ?>
    		</div>
			

    		<h2>External Crons</h2>
    		<div class="fincon-admin-status-item">
    			<span>Sync Connection: </span><br/><strong><?php echo $_CRON_URL; ?>?type=connection</strong>
    		</div>
    		<div class="fincon-admin-status-item">
    			<span>Sync Products: </span><br/><strong> <?php echo $_CRON_URL; ?>?type=stock</strong>
    		</div>
    		<div class="fincon-admin-status-item">
    			<span>Sync Users: </span><br/><strong> <?php echo $_CRON_URL; ?>?type=users</strong>
    		</div>
    		<div class="fincon-admin-status-item">
    			<span>Clean Logs: </span><br/><strong> <?php echo $_CRON_URL; ?>?type=logs</strong>
    		</div>
    		<div class="fincon-admin-status-item">
    			<span>Run All: </span><br/><strong> <?php echo $_CRON_URL; ?>?type=all</strong>
    		</div>


    	</div>

    	<?php if(is_array($_FILES) && count($_FILES) > 0): ?>

    	<div class="fincon-admin-status-block">
    		<h2>Logs</h2>

    		<?php foreach($_FILES as $_FILE): ?>
			
				<div class="fincon-admin-status-item">
	    			<a target="_blank" href="<?php WC_Fincon_Logger::link($_FILE); ?>"><?php echo $_FILE; ?></a>
	    		</div>

    		<?php endforeach; ?>

    	</div>

    	<?php endif; ?>

    	<div class="fincon-admin-trigger-block">
    		<a class="fincon-admin-trigger" data-trigger="fincon_admin_trigger_product_sync_full"><span class="dashicons dashicons-cart"></span> Trigger Full Product Sync</a> |<a class="fincon-admin-trigger" data-trigger="fincon_admin_trigger_product_sync"><span class="dashicons dashicons-cart"></span> Trigger Update Product Sync</a> | <a class="fincon-admin-trigger" data-trigger="fincon_admin_trigger_user_sync_full"><span class="dashicons dashicons-admin-users"></span> Trigger Full User Sync</a> | <a class="fincon-admin-trigger" data-trigger="fincon_admin_trigger_user_sync"><span class="dashicons dashicons-admin-users"></span> Trigger Update User Sync</a> | <a class="fincon-admin-trigger" data-trigger="fincon_admin_trigger_connection_sync"><span class="dashicons dashicons-admin-tools"></span> Trigger Connection Sync</a>
    	</div>
        </div>

        <?php
	}









	
	/**
	 * Adds loading overlay
	 *
	 * @since    1.0.0
	 */
	public function in_admin_header(){
		?>
	  
		  <div id="the-overlay">
		  	<img src="<?php echo plugins_url('woocommerce' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'wpspin-2x.gif'); ?>" />
		  </div>
	  
	  	<?php
	}









	
	/**
	 * Function to manage import and update of stock.
	 *
	 * @since    1.0.0
	 */
	public static function sync_stock_items($ISCRON = false){

		$DOWORK = $ISCRON;

		if(!get_option('fincon_product_sync_running') || get_option('fincon_product_sync_running') == 'no'):
			$DOWORK = true;
		endif;

		if($DOWORK):

			$_FINCON = new WC_Fincon();	
			$_FINCON->run_product_sync($ISCRON);	

		endif;




		/*
		if(get_option('fincon_woocommerce_enable_product_email') == 'yes' && $_COUNT > 0):
					self::do_email_notification('products', $_FINCON->_ERRORS);
				endif;
		 */		

	}









	
	/**
	 * Function to manage import and update of Users.
	 *
	 * @since    1.0.0
	 */
	public static function sync_user_items($ISCRON = false){	

		$DOWORK = $ISCRON;

		if(!get_option('fincon_user_sync_running') || get_option('fincon_user_sync_running') == 'no'):
			$DOWORK = true;
		endif;

		if($DOWORK):

			$_FINCON = new WC_Fincon();	
			$_FINCON->run_account_sync($ISCRON);	

		endif;




		/*
		if(get_option('fincon_woocommerce_enable_product_email') == 'yes' && $_COUNT > 0):
					self::do_email_notification('products', $_FINCON->_ERRORS);
				endif;
		 */	

		

	}









	
	/**
	 * AJAX Function to run a full sync
	 *
	 * @since    1.2.0
	 */
	public static function fincon_admin_trigger_product_sync_full(){



		if(!get_option('fincon_woocommerce_product_sync_running') || get_option('fincon_woocommerce_product_sync_running') == 'no'):

			delete_option('fincon_woocommerce_product_sync_last_date');
			delete_option('fincon_woocommerce_product_sync_last_time');
			delete_option('fincon_woocommerce_product_sync_rec_no');

			wp_schedule_single_event(time(), 'fincon_woocommerce_sync_products');

		endif;

		echo 'yes';

		exit;

	}









	
	/**
	 * AJAX Function to run a product sync
	 *
	 * @since    1.2.0
	 */
	public static function fincon_admin_trigger_product_sync(){

		if(!get_option('fincon_woocommerce_product_sync_running') || get_option('fincon_woocommerce_product_sync_running') == 'no'):

			wp_schedule_single_event(time(), 'fincon_woocommerce_sync_products');

		endif;

		echo 'yes';

		exit;

	}









	
	/**
	 * AJAX Function to run a user sync
	 *
	 * @since    1.2.0
	 */
	public static function fincon_admin_trigger_user_sync_full(){

		if(!get_option('fincon_woocommerce_user_sync_running') || get_option('fincon_woocommerce_user_sync_running') == 'no'):

			delete_option('fincon_woocommerce_user_sync_last_date');
			delete_option('fincon_woocommerce_user_sync_last_time');
			delete_option('fincon_woocommerce_user_sync_rec_no');

			wp_schedule_single_event(time(), 'fincon_woocommerce_sync_accounts');

		endif;

		echo 'yes';

		exit;
	}









	
	/**
	 * AJAX Function to run a user sync
	 *
	 * @since    1.2.0
	 */
	public static function fincon_admin_trigger_user_sync(){

		if(!get_option('fincon_woocommerce_user_sync_running') || get_option('fincon_woocommerce_user_sync_running') == 'no'):

			wp_schedule_single_event(time(), 'fincon_woocommerce_sync_accounts');

		endif;

		echo 'yes';

		exit;
	}









	
	/**
	 * AJAX Function to check the FINCON connection
	 *
	 * @since    1.2.0
	 */
	public static function fincon_admin_trigger_connection_sync(){

		self::check_api();

		echo 'yes';

		exit;
	}









	
	/**
	 * REMOVE AND LIMIT CHECKOUT FIELDS 
	 *
	 * @since    1.3.0
	 */
	public static function woocommerce_default_address_fields($fields){


        unset($fields['address_2']);

        $fields['address_1']['maxlength'] 	= 40;
        $fields['city']['maxlength'] 		= 40;
        $fields['state']['maxlength'] 		= 40;
        $fields['postcode']['maxlength'] 	= 4;

        return $fields;

	}





}
