<?php



class WC_Fincon{




	var $_ID 		= 0;
	var $_AUTH_UN 	= '';
	var $_AUTH_PW 	= '';
	var $_URL 		= '';
	var $_DATA_UN  	= '';
	var $_DATA_PW 	= '';
	var $_DATA 		= '';

	var $_ACC 		= '';
	var $_SHIP 		= '';
	var $_S_LOC 	= '';
	var $_O_LOC 	= '';
	var $_S_ITERATE = '';
	var $_U_ITERATE	= '';
	var $_COUPON 	= '';
	var $_PRICE 	= '';

	var $_ONE_ACC 	= false;
	var $_EXCLUDE 	= false;
	var $_GUEST 	= false;
	var $_SYNC_IMAGES = false;


	var $_PROD_CAT 		= false;
	var $_PROD_GROUP 	= false;

	var $_PROD_STATUS = '';
	var $_PROD_ACTION = false;

	var $_ORDER_STATUS = false;

	var $_TITLES = false;
	var $_DETAILED = false;

	var $_ERR 		= '';
	var $_ERRORS 	= array();

	var $_PAYMENTS  = false;

	var $_DEBUG  = false;








	/*
	
	 */
	public function __construct($_DEBUG = false){

		$this->_URL 		= trailingslashit(get_option('fincon_woocommerce_url'));

		if(!strstr($this->_URL, 'datasnap/rest/FinconAPI')):
			$this->_URL = trailingslashit($this->_URL).'datasnap/rest/FinconAPI';
		endif;

		$this->_AUTH_UN		= get_option('fincon_woocommerce_auth_username');
		$this->_AUTH_PW 	= get_option('fincon_woocommerce_auth_password'); 
		$this->_DATA_UN 	= get_option('fincon_woocommerce_data_username');
		$this->_DATA_PW 	= get_option('fincon_woocommerce_data_password');
		$this->_DATA 		= get_option('fincon_woocommerce_data_id');

		$this->_ACC 		= get_option('fincon_woocommerce_account');
		$this->_SHIP 		= get_option('fincon_woocommerce_delivery');
		$this->_O_LOC 		= get_option('fincon_woocommerce_order_location');
		$this->_S_ITERATE 	= get_option('fincon_woocommerce_product_batch');
		$this->_U_ITERATE	= get_option('fincon_woocommerce_user_batch');
		$this->_COUPON 		= get_option('fincon_woocommerce_coupon');
		$this->_PRICE 		= get_option('fincon_woocommerce_price');
		$this->_PROD_STATUS = get_option('fincon_woocommerce_product_status');
		$this->_PROD_ACTION = get_option('fincon_woocommerce_product_action');
		$this->_DECIMAL		= wc_get_price_decimals();
		$this->_DETAILED  	= get_option('fincon_woocommerce_product_detailed');

		if(get_option('fincon_woocommerce_product_title') == 'yes'):
			$this->_TITLES = true;
		endif;

		if(get_option('fincon_woocommerce_sync_product_images') == 'yes'):
			$this->_SYNC_IMAGES = true;
		endif;		

		if(get_option('fincon_woocommerce_one_debtor_account') == 'yes'):
			$this->_ONE_ACC = true;
		endif;

		if(get_option('fincon_woocommerce_exclude_order') == 'yes'):
			$this->_EXCLUDE = true;
		endif;

		if(get_option('fincon_woocommerce_pass_guest_user_info') == 'yes'):
			$this->_GUEST = true;
		endif;

		if(get_option('fincon_woocommerce_sync_product_category') == 'yes'):
			$this->_PROD_CAT = true;
		endif;	

		if(get_option('fincon_woocommerce_sync_product_group') == 'yes'):
			$this->_PROD_GROUP = true;
		endif;	

		if(get_option('fincon_woocommerce_order_status') == 'yes'):
			$this->_ORDER_STATUS = true;
		endif;

		if(get_option('fincon_woocommerce_sync_orders_payments') == 'yes'):
			$this->_PAYMENTS = true;
		endif;

		$_SLB = get_option('fincon_woocommerce_stock_location');
		$_SLB = explode(",", $_SLB);
		$_SLC = array();
		foreach($_SLB as $_S):

			if(trim($_S) != ''):

				$_SLC[] = $_S;

			endif;

		endforeach;

		$this->_S_LOC 		= implode(',', $_SLC);

		$this->_DEBUG = $_DEBUG;
	}









	
	/*
	
	 */
	public function invoke_fincon($_ENDPOINT, $_DATA = array(), $_POSTING = NULL, $_DO_ENCODE = true){

		$_URL = trailingslashit($this->_URL);
		$_URL = trailingslashit($_URL.$_ENDPOINT);

		if(count($_DATA) > 0):
			$_URL = trailingslashit($_URL.implode("/", $_DATA));
		endif;

		if($this->_DEBUG):
			echo '<pre>'; print_r($_URL); echo '</pre>';
		endif;

		$_HEADER = array(
			'Content-Type: application/json'
		);

		$_CALL = curl_init();

		curl_setopt($_CALL, CURLOPT_URL,  $_URL);
		curl_setopt($_CALL, CURLOPT_USERPWD, $this->_AUTH_UN . ":" . $this->_AUTH_PW);
		curl_setopt($_CALL, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($_CALL, CURLOPT_TIMEOUT,        0);
		curl_setopt($_CALL, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($_CALL, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($_CALL, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($_CALL, CURLOPT_HTTPHEADER,     $_HEADER);

		if($_POSTING):
			curl_setopt($_CALL, CURLOPT_POST, 1);

			if($_DO_ENCODE):
				curl_setopt($_CALL, CURLOPT_POSTFIELDS, json_encode($_POSTING, JSON_NUMERIC_CHECK));
			else:
				curl_setopt($_CALL, CURLOPT_POSTFIELDS, $_POSTING);
			endif;
		endif;

		$_RESULT = curl_exec($_CALL);




		curl_close($_CALL);

		$_RESULT = json_decode($_RESULT,true);

				if($this->_DEBUG):
			echo '<pre>'; print_r($_RESULT); echo '</pre>';
		endif;

		return $_RESULT['result'][0];


	}









	
	/*
	
	 */
	public function Login(){

		if(!$this->_ID):

			$_ENDPOINT = 'Login';
			$_DATA = array($this->_DATA, $this->_DATA_UN, $this->_DATA_PW, 0);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			if($_RESPONSE['Connected']):

				$this->_ID = $_RESPONSE['ConnectID'];

				return true;

			elseif($_RESPONSE['ErrorInfo'] != ""):

				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 

				WC_Fincon_Logger::log('LOGIN::'.$_RESPONSE['ErrorInfo'].'');

			else:

				$this->_ERRORS[] = 'Could Not Login';

				WC_Fincon_Logger::log('LOGIN::ERROR--Could Not Login');

			endif;

		else:

			$this->KillUsers();	

		endif;

	}









	
	/*
	
	 */
	public function Logout(){

		if($this->_ID):

			$this->KillUsers();	

			$_ENDPOINT = 'Logout';
			$_DATA = array($this->_ID);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			if($_RESPONSE['Disconnected']):

				$this->_ID = 0;

				return true;

			elseif($_RESPONSE['ErrorInfo'] != ""):

				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 

				WC_Fincon_Logger::log('LOGOUT::'.$_RESPONSE['ErrorInfo'].'');

			else:

				$this->_ERRORS[] = 'Could Not Logout';

				WC_Fincon_Logger::log('LOGOUT::ERROR');

			endif;

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function GetSessionInfo(){

		if($this->_ID):

			$_ENDPOINT = 'GetSessionInfo';

			$_DATA = array(
				$TESTER
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			if(!isset($_RESPONSE['ErrorInfo']) || $_RESPONSE['ErrorInfo'] == ""):
				return true;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'Not ID to KeepAlive';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function KeepAlive($_TESTER = false){

		if(!$_TESTER):
			$_TESTER = $this->_ID;
		endif;

		if($_TESTER):

			$_ENDPOINT = 'KeepAlive';

			$_DATA = array(
				$_TESTER
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			if(!isset($_RESPONSE['ErrorInfo']) || $_RESPONSE['ErrorInfo'] == ""):
				return true;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'Not Logged Out';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function KillUsers(){

		if($this->_ID):

			$_ENDPOINT = 'KillUsers';

			$_DATA = array(
				$this->_ID,
				0
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			if(!isset($_RESPONSE['ErrorInfo']) || $_RESPONSE['ErrorInfo'] == ""):
				return true;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'Not Logged Out';

		endif;

		return false;

		
	}









	
	/*
	HELPER - VALIDATE CUSTOM
	 */
	public static function ValidateCustom($URL, $AUN, $APW, $DATA, $DUN, $DPW){

		$_SUCCESS = true;

		$_ERROR = '';

		$_RETURN = array();

		WC_Fincon_Logger::log('Settings Connection Sync Running');

		/* PART 1 */
		$_ENDPOINT = 'Login';
		$_DATA = array($DATA, $DUN, $DPW, 0);
		$_PING_URL = trailingslashit($URL);
		$_PING_URL = trailingslashit($_PING_URL.$_ENDPOINT);

		if(count($_DATA) > 0):
			$_PING_URL = trailingslashit($_PING_URL.implode("/", $_DATA));
		endif;

		$_HEADER = array(
			'Content-Type: application/json'
		);

		$_CALL = curl_init();

		curl_setopt($_CALL, CURLOPT_URL,  $_PING_URL);
		curl_setopt($_CALL, CURLOPT_USERPWD, $AUN . ":" . $APW);
		curl_setopt($_CALL, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($_CALL, CURLOPT_TIMEOUT,        10);
		curl_setopt($_CALL, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($_CALL, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($_CALL, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($_CALL, CURLOPT_HTTPHEADER,     $_HEADER);

		$_RESULT = curl_exec($_CALL);

		$_RESULT = json_decode($_RESULT,true);

		$_RESPONSE = $_RESULT['result'][0];

		if($_RESPONSE['Connected']):

			$_ID = $_RESPONSE['ConnectID'];

			/*PART 2 */
			$_ENDPOINT = 'Logout';
			$_DATA = array($_ID);
			$_PING_URL = trailingslashit($URL);
			$_PING_URL = trailingslashit($_PING_URL.$_ENDPOINT);

			if(count($_DATA) > 0):
				$_PING_URL = trailingslashit($_PING_URL.implode("/", $_DATA));
			endif;

			$_HEADER = array(
				'Content-Type: application/json'
			);

			$_CALL = curl_init();

			curl_setopt($_CALL, CURLOPT_URL,  $_PING_URL);
			curl_setopt($_CALL, CURLOPT_USERPWD, $AUN . ":" . $APW);
			curl_setopt($_CALL, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($_CALL, CURLOPT_TIMEOUT,        10);
			curl_setopt($_CALL, CURLOPT_RETURNTRANSFER, true );
			curl_setopt($_CALL, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($_CALL, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($_CALL, CURLOPT_HTTPHEADER,     $_HEADER);

			$_RESULT = curl_exec($_CALL);

			$_RESULT = json_decode($_RESULT,true);

			$_RESPONSE = $_RESULT['result'][0];

			if(!$_RESPONSE['Disconnected']):

			$_SUCCESS = false;

			$_ERROR = $_RESPONSE['ErrorInfo'];

			endif;

		else:

			$_SUCCESS = false;

			$_ERROR = $_RESPONSE['ErrorInfo'];

		endif;


		if($_SUCCESS):

			WC_Fincon_Logger::log('Settings Connection Sync SUCCESS');

			$_RETURN = array('status' => 'live');

		else:

			WC_Fincon_Logger::log('Settings Connection Sync FAILED -- '.$_ERROR);

			$_RETURN = array('status' => 'off', 'error' => $_ERROR);

		endif;

		WC_Fincon_Logger::log('Settings Connection Sync Finished');

		return $_RETURN;

	}	









	
	/*
	HELPER - VALIDATE SYNC
	 */
	public function Validate(){

		$_SUCCESS = true;
		$_RETURN = array();

		WC_Fincon_Logger::log('Connection Sync Running');

		$this->_ERRORS = array();

		$this->Login();

		if(count($this->_ERRORS) > 0):

			$_SUCCESS = false;

		else:

			if($this->_ID > 0 && $this->GetSessionInfo($this->_ID)):

				$this->_ERRORS = array();

				if(count($this->_ERRORS) > 0):						

					$_SUCCESS = false;

				endif;

			else:					

				$_SUCCESS = false;

			endif;

		endif;

		$this->Logout();

		if($_SUCCESS):
			WC_Fincon_Logger::log('Connection Sync SUCCESS');
			$_RETURN = array('status' => 'live');
		else:
			WC_Fincon_Logger::log('Connection Sync FAILED');
			$_RETURN = array('status' => 'off', 'error' => implode(", ", $this->_ERRORS));
		endif;


		WC_Fincon_Logger::log('Connection Sync Finished');

		return $_RETURN;

	}









	
	/*
	
	 */
	public function GetStock($RecNo = 0, $Count = null, $MinItemNo = '', $MaxItemNo = null, $LocNo = '', $WebOnly = true){

		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetStock';

			if(!$Count):
				$Count = $this->_S_ITERATE;
			endif;

			if(!$MaxItemNo && $Count == 1):
				$MaxItemNo = $MinItemNo;
			endif;

			$MinItemNo = rawurlencode($MinItemNo);
			$MaxItemNo = rawurlencode($MaxItemNo);

			if($LocNo == ''):
				$LocNo = $this->_S_LOC;
			endif;

			$_DATA = array(
				$this->_ID,
				$MinItemNo,
				$MaxItemNo,
				$LocNo,
				(int)$WebOnly,
				$RecNo,
				$Count
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['Count'] == 1):
				return reset($_RESPONSE['Stock']);
			elseif($_RESPONSE['Count'] > 1):
				return $_RESPONSE;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;
	}
		









	
	/*
	
	 */
	public function GetStockItem( $MinItemNo = '', $MaxItemNo = null, $RecNo = 0, $Count = 1, $LocNo = '', $WebOnly = false){

		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetStock';

			if(!$Count):
				$Count = $this->_S_ITERATE;
			endif;

			if(!$MaxItemNo && $Count == 1):
				$MaxItemNo = $MinItemNo;
			endif;

			$MinItemNo = rawurlencode($MinItemNo);
			$MaxItemNo = rawurlencode($MaxItemNo);

			if($LocNo == ''):
				$LocNo = $this->_S_LOC;
			endif;

			$_DATA = array(
				$this->_ID,
				$MinItemNo,
				$MaxItemNo,
				$LocNo,
				(int)$WebOnly,
				$RecNo,
				$Count
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['Count'] == 1):
				return reset($_RESPONSE['Stock']);
			elseif($_RESPONSE['Count'] > 1):
				return $_RESPONSE;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function GetStockQuantity($MinItemNo, $LocNo = null, $WebOnly = false, $SkipIfZero = false, $RecNo = 0, $Count = 1, $MaxItemNo = null){

		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetStockQuantities';

			if(!$LocNo):
			 	$LocNo = $this->_S_LOC;
			endif;

			$LocNo = str_replace(" ", "", $LocNo);
			
			$LocNo = explode(",", $LocNo);

			$_THE_LOCS = array();

			foreach($LocNo as $_L):
				$_THE_LOCS[] = (int)$_L;
			endforeach;

			$LocNo = implode(",", $_THE_LOCS);

			if(!$MaxItemNo):
				$MaxItemNo = $MinItemNo;
			endif;

			$MinItemNo = rawurlencode($MinItemNo);
			$MaxItemNo = rawurlencode($MaxItemNo);


			$_DATA = array(
				$this->_ID,
				$MinItemNo,
				$MaxItemNo,
				$LocNo,
				(int)$WebOnly,
				(int)$SkipIfZero,
				$RecNo,
				$Count
			);


			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['Count'] > 0):

				$_STOCK = reset($_RESPONSE['Stock']);

				$_STOCKLOCS = $_STOCK['StockLoc'];

				$_INSTOCK = 0;

				foreach($_STOCKLOCS as $_SL):
					$_INSTOCK += $_SL['InStock'];

					if($this->_EXCLUDE):
						$_INSTOCK -= $_SL['SalesOrders'];	
					endif;
					
				endforeach;
				
				return $_INSTOCK;

			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 

				//WC_Fincon_Logger::log('--STOCKQTY::'.$MinItemNo.'('.$_RESPONSE['ErrorInfo'].')');
			else:
				return 0;
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

			WC_Fincon_Logger::log('--STOCKQTY::'.$MinItemNo.'(Not Logged In)');

		endif;

		return 0;

		
	}









	
	/*
	
	 */
	public function GetStockChanged($FromDate,$FromTime,$RecNo = 0, $LocNo = null, $WebOnly = false){

		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetStockChanged';

			if(!$LocNo):
			 	$LocNo = $this->_S_LOC;
			endif;

			$LocNo = str_replace(" ", "", $LocNo);
			
			$LocNo = explode(",", $LocNo);

			$_THE_LOCS = array();

			foreach($LocNo as $_L):
				$_THE_LOCS[] = (int)$_L;
			endforeach;

			$LocNo = implode(",", $_THE_LOCS);

			$_DATA = array(
				$this->_ID,
				$FromDate,
				$FromTime,
				$LocNo,
				(int)$WebOnly,
				$RecNo,
				$this->_S_ITERATE
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['ErrorInfo'] == ""):
				return $_RESPONSE;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function GetDetailDescriptions( $MinItemNo = '', $MaxItemNo = false, $RecNo = 0, $Count = 1, $WebOnly = false, $SkipIfBlank = false){

		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetDetailDescriptions';

			if(!$MaxItemNo):
				$MaxItemNo = $MinItemNo;
			endif;

			$MinItemNo = rawurlencode($MinItemNo);
			$MaxItemNo = rawurlencode($MaxItemNo);


			$_DATA = array(
				$this->_ID,
				$MinItemNo,
				$MaxItemNo,
				(int)$WebOnly,
				(int)$SkipIfBlank,
				$RecNo,
				$Count
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['ErrorInfo'] == ""):
				return $_RESPONSE['Stock'];
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function GetStockPictures( $MinItemNo = '', $MaxItemNo = null, $RecNo = 0, $Count = 1, $WebOnly = false, $SkipIfBlank = false){
		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetStockPictures';

			if(!$MaxItemNo):
				$MaxItemNo = $MinItemNo;
			endif;

			$MinItemNo = rawurlencode($MinItemNo);
			$MaxItemNo = rawurlencode($MaxItemNo);

			$_DATA = array(
				$this->_ID,
				$MinItemNo,
				$MaxItemNo,
				(int)$WebOnly,
				(int)$SkipIfBlank,
				$RecNo,
				$Count
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['ErrorInfo'] == ""):
				return $_RESPONSE['Stock'];
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function GetDebAccount($MinAccNo = '', $Count = 1, $MaxAccNo = '', $RecNo = 0){
		
		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetDebAccounts';

			if(!$Count):
				$Count = $this->_U_ITERATE;
			endif;

			if(!$MaxAccNo && $Count == 1):
				$MaxAccNo = $MinAccNo;
			endif;

			$_DATA = array(
				$this->_ID,
				$MinAccNo,
				$MaxAccNo,
				$RecNo,
				$Count
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);
			$this->Logout();

			if($_RESPONSE['Count'] == 1):
				return reset($_RESPONSE['Accounts']);
			elseif($_RESPONSE['Count'] > 1):
				return $_RESPONSE;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function GetDebAccounts($RecNo = 0, $Count = null, $MinAccNo = '', $MaxAccNo = ''){

		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetDebAccounts';

			if(!$Count):
				$Count = $this->_U_ITERATE;
			endif;

			if(!$MaxAccNo && $Count == 1):
				$MaxAccNo = $MinAccNo;
			endif;

			$_DATA = array(
				$this->_ID,
				$MinAccNo,
				$MaxAccNo,
				$RecNo,
				$Count
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['Count'] == 1):
				return reset($_RESPONSE['Accounts']);
			elseif($_RESPONSE['Count'] > 1):
				return $_RESPONSE;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function GetDebAccountsChanged($FromDate,$FromTime,$RecNo = 0){
		
		$this->Login();

		if($this->_ID):

			$_ENDPOINT = 'GetDebAccountsChanged';

			$_DATA = array(
				$this->_ID,
				$FromDate,
				$FromTime,
				$RecNo,
				$this->_U_ITERATE
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA);

			$this->Logout();

			if($_RESPONSE['ErrorInfo'] == ""):
				return $_RESPONSE;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}









	
	/*
	
	 */
	public function CreateSalesOrder($_SO){

		$this->Login();

		if($this->_ID):

			$_ENDPOINT = '"CreateSalesOrder"';

			$_DATA = array(
				$this->_ID
			);

			$_RESPONSE = $this->invoke_fincon($_ENDPOINT, $_DATA, $_SO);

			$this->Logout();


			if($_RESPONSE['ErrorInfo'] == ""):
				return $_RESPONSE;
			elseif($_RESPONSE['ErrorInfo'] != ""):
				$this->_ERRORS[] = $_RESPONSE['ErrorInfo']; 
			else:
				$this->_ERRORS[] = 'An Error Occurred.';
			endif;

		else:

			$this->_ERRORS[] = 'No Connect ID Present';

		endif;

		return false;

		
	}







































	public function run_sales_order($_ORDER_ID){

		if(count($this->_ERRORS) > 0):
			update_post_meta($_ORDER_ID, '_fincon_sales_error', $this->_ERRORS);
			return;
		endif;


		/* GET BASE INFORMATION */
		$_ORDER = new WC_Order($_ORDER_ID);


		if($this->_ONE_ACC):

			$_ACC_TO_USE = $this->_ACC;

		else:

			$_CUST_ID 		= $_ORDER->get_customer_id();

			if($_CUST_ID != 0):

				$_CUST  		= new WC_Customer($_CUST_ID);
				$_ACC_TO_USE 	= $_CUST->get_username();

			else:

				$_ACC_TO_USE = $this->_ACC;

			endif;

		endif;

		$_ACC_TO_USE = apply_filters('fincon_filter_account_number_for_salesorder', $_ACC_TO_USE, $_ORDER->get_customer_id());

		$_DEP = $this->GetDebAccount($_ACC_TO_USE);

		if(!$_DEP):
			$_DEP = $this->GetDebAccount($this->_ACC);
			$_ACC_TO_USE = $this->_ACC;
		endif;

		$_SALES_ORDER = array();

		$_SALES_ORDER_DETAIL = array();
		
		$_NOTE = $_ORDER->get_customer_note();

		$_DECIMAL = wc_get_price_decimals();



		/* SPLIT NOTES BECAUSE OF FIELD LIMITATIONS */
		$_NOTE_1 = '.';
		$_NOTE_2 = '.';
		$_NOTE_3 = '.';

		if(strlen($_NOTE) > 0):

			$_NOTE_BLOCK = str_split($_NOTE, 40);
			$_NOTE_COUNT = count($_NOTE_BLOCK);

			if(count($_NOTE_BLOCK) >= 3):
				$_NOTE_1 = $_NOTE_BLOCK[0];
				$_NOTE_2 = $_NOTE_BLOCK[1];
				$_NOTE_3 = $_NOTE_BLOCK[2];
			elseif(count($_NOTE_BLOCK) == 2):
				$_NOTE_1 = $_NOTE_BLOCK[0];
				$_NOTE_2 = $_NOTE_BLOCK[1];
			elseif(count($_NOTE_BLOCK) == 1):
				$_NOTE_1 = $_NOTE_BLOCK[0];
			endif;

		endif;


		/* SET APPROPRIATE SHIPPING TYPE */
		$_METHODS = $_ORDER->get_shipping_methods();
		$_THIS_METHOD = '';

		foreach($_METHODS as $_METHOD):

			if($_METHOD['method_id'] == 'local_pickup'):
				$_THIS_METHOD = 'C';
			else:
				$_THIS_METHOD = 'R';
			endif;

		endforeach;

		/* SALES ORDER DETAILS */
		if($_DEP):

			$_SALES_ORDER['AccNo'] 				= $_ACC_TO_USE;
			$_SALES_ORDER['LocNo'] 				= $this->_O_LOC;
			$_SALES_ORDER['TotalExcl']			= number_format($_ORDER->get_total() - $_ORDER->get_total_tax(), $this->_DECIMAL, ".", "");
			$_SALES_ORDER['TotalTax']			= number_format($_ORDER->get_total_tax(), $this->_DECIMAL, ".", "");
			$_SALES_ORDER['CustomerRef'] 		= $_ORDER_ID;


			if($this->_GUEST):
				$_SALES_ORDER['DebName'] 		= $_ORDER->get_formatted_billing_full_name();;
				$_SALES_ORDER['Addr1'] 			= $_ORDER->get_billing_address_1();
				$_SALES_ORDER['Addr2'] 			= $_ORDER->get_billing_city();
				$_SALES_ORDER['Addr3'] 			= $_ORDER->get_billing_state().' '.$_ORDER->get_billing_country();
				$_SALES_ORDER['PCode'] 			= $_ORDER->get_billing_postcode();
			else:
				$_SALES_ORDER['DebName'] 		= $_DEP->DebName;
				$_SALES_ORDER['Addr1'] 			= $_DEP->Addr1;
				$_SALES_ORDER['Addr2'] 			= $_DEP->Addr2;
				$_SALES_ORDER['Addr3'] 			= $_DEP->Addr3;
				$_SALES_ORDER['PCode'] 			= $_DEP->PCode;
			endif; 
			
			if(!$_ORDER->get_formatted_shipping_full_name()):
				$_SALES_ORDER['DelName'] 			= $_ORDER->get_formatted_billing_full_name();
				$_SALES_ORDER['DelAddr1'] 			= $_ORDER->get_billing_address_1();
				$_SALES_ORDER['DelAddr2'] 			= $_ORDER->get_billing_city();
				$_SALES_ORDER['DelAddr3'] 			= $_ORDER->get_billing_state().' '.$_ORDER->get_billing_country();
				$_SALES_ORDER['DelPCode'] 			= $_ORDER->get_billing_postcode();
			else:	
				$_SALES_ORDER['DelName'] 			= $_ORDER->get_formatted_shipping_full_name();
				$_SALES_ORDER['DelAddr1'] 			= $_ORDER->get_shipping_address_1();
				$_SALES_ORDER['DelAddr2'] 			= $_ORDER->get_shipping_city();
				$_SALES_ORDER['DelAddr3'] 			= $_ORDER->get_shipping_state().' '.$_ORDER->get_shipping_country();
				$_SALES_ORDER['DelPCode'] 			= $_ORDER->get_shipping_postcode();
			endif;

			$_SALES_ORDER['DelInstruc1'] 		= $_NOTE_1;
			$_SALES_ORDER['DelInstruc2'] 		= $_NOTE_2;
			$_SALES_ORDER['DelInstruc3'] 		= $_NOTE_3;
			$_SALES_ORDER['DelInstruc4'] 		= $_DEP->DelInstruc4;
			$_SALES_ORDER['DelInstruc5'] 		= $_DEP->DelInstruc5;
			$_SALES_ORDER['DelInstruc6'] 		= $_DEP->DelInstruc6;
			$_SALES_ORDER['DeliveryMethod'] 	= $_THIS_METHOD;
			$_SALES_ORDER['RepCode'] 			= $_DEP->RepCode;
			$_SALES_ORDER['TaxNo'] 				= $_DEP->TaxNo;


			/* SALES ORDER LINE ITEMS */
			foreach($_ORDER->get_items() as $_ID => $_ITEM):

				if ($_ITEM['variation_id'] > 0 ):
					$_PRODUCT_ID = $_ITEM['variation_id'];
				else:
					$_PRODUCT_ID = $_ITEM['product_id'];
				endif;

				$_PROD = wc_get_product($_PRODUCT_ID);

				$_SKU = $_PROD->get_sku();

				$_STOCKITEM = $this->GetStock(0, 1, $_SKU);
				
				if($_STOCKITEM && $_STOCKITEM['ItemNo'] != ''):

					$_DETAIL = array();

					$_DETAIL['ItemNo'] 			= $_STOCKITEM['ItemNo'];
					$_DETAIL['Quantity'] 		= $_ITEM->get_quantity();
					$_DETAIL['LineTotalExcl'] 	= number_format($_ITEM->get_subtotal(), $_DECIMAL, ".", "");
					$_DETAIL['TaxCode']			= $_STOCKITEM['TaxCode'];
					$_DETAIL['LineTotalTax'] 	= number_format($_ITEM->get_subtotal_tax(), $_DECIMAL, ".", "");
					$_DETAIL['Description'] 	= $_STOCKITEM['Description'];

					$_SALES_ORDER_DETAIL[] 		= $_DETAIL;

				else:

					$this->_ERRORS[] = $_SKU.' Not Found';

				endif;
				

			endforeach;


			if(count($_SALES_ORDER_DETAIL) > 0):

				/* IF ITEM IS SHIPPED */
				if($_THIS_METHOD == 'R'):

					$_SHIPPING_ITEM = $this->GetStock(0, 1, $this->_SHIP);

					if($_SHIPPING_ITEM && $_SHIPPING_ITEM['ItemNo'] != ''):

						$_DETAIL = array();

						$_DETAIL['ItemNo']  		= $this->_SHIP;
						$_DETAIL['Quantity'] 		= 1;
						$_DETAIL['LineTotalExcl'] 	= number_format($_ORDER->get_shipping_total() - $_ORDER->get_shipping_tax(), $_DECIMAL, ".", "");
						$_DETAIL['TaxCode'] 		= $_SHIPPING_ITEM['TaxCode'];
						$_DETAIL['LineTotalTax'] 	= number_format($_ORDER->get_shipping_tax(), $_DECIMAL, ".", "");
						$_DETAIL['Description'] 	= $_SHIPPING_ITEM['Description'].'-'.$_ORDER->get_shipping_method();

						$_SALES_ORDER_DETAIL[] 		= $_DETAIL;

					else:

						$this->_ERRORS[] = $this->_SHIP.' Not Found';

					endif;

				endif;


				/* IF ITEM HAS COUPON */
				$_COUPONS = $_ORDER->get_items( array( 'coupon' ) );

				foreach ( $_COUPONS as $_ID => $_ITEM ):

					$_COUPON_ITEM = $this->GetStock(0, 1, $this->_COUPON);

					if($_COUPON_ITEM && $_COUPON_ITEM['ItemNo'] != ''):

						$_AMT_E = number_format($_ITEM['discount_amount'] - $_ITEM['discount_tax'], $_DECIMAL, ".", "");
						$_AMT_T = number_format($_ITEM['discount_tax'], $_DECIMAL, ".", "");

						$_DETAIL = array();

						$_DETAIL['ItemNo']  		= $this->_COUPON;
						$_DETAIL['Quantity'] 		= 1;
						$_DETAIL['LineTotalExcl'] 	= number_format($_AMT_E, $_DECIMAL, ".", "");
						$_DETAIL['TaxCode'] 		= $_SHIPPING_ITEM['TaxCode'];
						$_DETAIL['LineTotalTax'] 	= number_format($_AMT_T, $_DECIMAL, ".", "");
						$_DETAIL['Description'] 	= $_SHIPPING_ITEM['Description'].'-'.$_ITEM->get_name();

						$_SALES_ORDER_DETAIL[] 		= $_DETAIL;

					else:

						$this->_ERRORS[] = $this->_COUPON.' Not Found';

					endif;
					

				endforeach;


				/* FINALLY ASSIGN THE LINE ITEMS */
				$_SALES_ORDER['SalesOrderDetail'] = $_SALES_ORDER_DETAIL;


				if($this->_PAYMENTS):
					/* ASSIGN PAYMENT */
					$_PAYMENT_TYPE 		= $_ORDER->get_payment_method();
					$_PAYMENT_TITLE 	= $_ORDER->get_payment_method_title();
					$_SALES_ORDER_PAYMENT = array();
					
					switch($_PAYMENT_TYPE):

						case "bacs":
						case "cheque":
							$_SALES_ORDER_PAYMENT['PayType'] = 'T';
						break;

						default:
							$_SALES_ORDER_PAYMENT['PayType'] = 'C';
						break;

					endswitch;				
					

					$_SALES_ORDER_PAYMENT['CardNo'] = $_PAYMENT_TITLE;
					$_SALES_ORDER_PAYMENT['Amount'] = number_format($_ORDER->get_total(), $_DECIMAL, ".", "");

					$_SALES_ORDER['SalesOrderPayment'] = $_SALES_ORDER_PAYMENT;

				endif;

				if(count($this->_ERRORS) > 0):
					update_post_meta($_ORDER_ID, '_fincon_sales_error', $this->_ERRORS);

					foreach($this->_ERRORS as $_ERROR):

						WC_Fincon_Logger::log('SALES ORDER ERROR ('.$_ORDER_ID.')::'.$_ERROR);

					endforeach;

				else:

					/* LETS GO DUDES! */
					$_POSTING = array();
					$_POSTING['_parameters'][] = (int)$this->_ORDER_STATUS;
					$_POSTING['_parameters'][] = $_SALES_ORDER;

					update_post_meta($_ORDER_ID, 'fincon_sales_order_data', $_SALES_ORDER);

					$_SO_RESPONSE = $this->CreateSalesOrder($_POSTING);

					if($_SO_RESPONSE['SalesOrderInfo']['OrderNo']):

						$_SO_NUMBER = $_SO_RESPONSE['SalesOrderInfo']['OrderNo'];

						update_post_meta($_ORDER_ID, '_fincon_sales_order', $_SO_NUMBER);
						WC_Fincon_Logger::log('SALES ORDER CREATED::'.$_SO_NUMBER.' ('.$_ORDER_ID.')');

						if($this->_PAYMENTS):
							if($_SO_RESPONSE['SalesOrderInfo']['SalesOrderPayment']['ReceiptNo']):

								$_RP_NUMBER = $_SO_RESPONSE['SalesOrderInfo']['SalesOrderPayment']['ReceiptNo'];
								update_post_meta($_ORDER_ID, '_fincon_receipt_number', $_RP_NUMBER);
								WC_Fincon_Logger::log('SALES ORDER RECEIPT::'.$_RP_NUMBER.' ('.$_ORDER_ID.')');
						
							endif;
						endif;

					else:

						if(count($this->_ERRORS) > 0):
							update_post_meta($_ORDER_ID, '_fincon_sales_error', $this->_ERRORS);

							foreach($this->_ERRORS as $_ERROR):

								WC_Fincon_Logger::log('SALES ORDER ERROR ('.$_ORDER_ID.')::'.$_ERROR);

							endforeach;

						endif;



					endif;

				endif;

			else:

				WC_Fincon_Logger::log('SALES ORDER ERROR ('.$_ORDER_ID.'):: NO ITEMS FOUND');

			endif;

		else:


			WC_Fincon_Logger::log('SALES ORDER ERROR ('.$_ORDER_ID.'):: ACCOUNT ('.$_ACC_TO_USE.') NOT FOUND');

		endif;



	}




































	public function get_category_id($_NAME, $_DESC, $_PARENT = false, $_TAX = 'product_cat'){


      	if(get_term_by( 'name', $_NAME, $_TAX, ARRAY_A )):

      		$_TERM = get_term_by( 'name', $_NAME, $_TAX, ARRAY_A );
      		$_ID =  $_TERM['term_id'];

      	elseif(get_term_by( 'name', $_DESC, $_TAX, ARRAY_A )):

      		$_TERM = get_term_by( 'name', $_DESC, $_TAX, ARRAY_A );
      		$_ID =  $_TERM['term_id'];

      		if($_PARENT):
	      		wp_update_term($_ID, $_TAX, array('name' => $_NAME, 'parent' => $_PARENT));
	      	else:
	      		wp_update_term($_ID, $_TAX, array('name' => $_NAME));
	      	endif;

		    WC_Fincon_Logger::log('Category Update: Name Changed From '.$_DESC.' to '.$_NAME);

		else:
			if($_PARENT):
				$_TERM = wp_insert_term($_NAME, $_TAX, array('description'=> '' ,'slug' => $_NAME, 'parent' => $_PARENT));
			else:
				$_TERM = wp_insert_term($_NAME, $_TAX, array('description'=> '' ,'slug' => $_NAME));
			endif;

			if(!is_wp_error($_TERM)):
		    	$_ID =  $_TERM['term_id'];

		    	WC_Fincon_Logger::log('Category Created: '.$_NAME);

		    else:
		    	$_ID = false;
		    endif;
		endif;

		return $_ID;


	}






	/**
	 * UPLOAD AND ATTACH PRODUCT IMAGE
	 *
	 * @since    1.3.0
	 */
	public function upload_attach_image($SKU, $DATA){

		$post_id = wc_get_product_id_by_sku($SKU);

	    $image_data = base64_decode($DATA);

	    $file_info = $this->mime_extension($SKU, $DATA);

	    	
	    if($file_info):

		    $image_name       	= $SKU.'.'.$file_info['ext'];
		    $upload_dir       	= wp_upload_dir();

		    $unique_file_name 	= wp_unique_filename( $upload_dir['path'], $image_name );
		    $filename         	= basename( $unique_file_name );

		    if( wp_mkdir_p( $upload_dir['path'] ) ) {
		        $file = $upload_dir['path'] . '/' . $filename;
		    } else {
		        $file = $upload_dir['basedir'] . '/' . $filename;
		    }

		    file_put_contents( $file, $image_data );

		    $attachment = array(
		        'post_mime_type' => $file_info['mime'],
		        'post_title'     => sanitize_file_name( $filename ),
		        'post_content'   => '',
		        'post_status'    => 'inherit'
		    );

		    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		    
		    require_once(ABSPATH . 'wp-admin/includes/image.php');

		    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		    wp_update_attachment_metadata( $attach_id, $attach_data );

		    return $attach_id;

		else:

			return false;

		endif;


	}

	/**
	 * SORT OUT MIME AND EXTENSION
	 *
	 * @since    1.3.0
	 */
	public function mime_extension($SKU, $DATA){

	    $image_data       	= base64_decode($DATA);

	    $file_info = finfo_open();

		$mime_type = finfo_buffer($file_info, $image_data, FILEINFO_MIME_TYPE);

		finfo_close($file_info);

		$mimes = get_allowed_mime_types();

		$the_extension = false;

		foreach ( $mimes as $ext => $mime ):

		   if($mime == $mime_type):

		   	$_extensions = explode("|", $ext);

		   	$the_extension = $_extensions[0];

		   endif;

		endforeach;

		if($the_extension):

			return array('mime'=> $mime_type, 'ext' => $the_extension);

		else:

			WC_Fincon_Logger::log('Image for '.$SKU.' has failed to upload.');

			return false;

		endif;


	}


















	public function run_product_sync($ISCRON = false){

		$_TYPE = 'full';

		if(get_option('fincon_woocommerce_product_sync_rec_no') && get_option('fincon_woocommerce_product_sync_rec_no') != ''):
			$_REC_NO = get_option('fincon_woocommerce_product_sync_rec_no');
			WC_Fincon_Logger::log('PRODUCT SYNC CONTINUING');
		else:
			$_REC_NO = 0;
			WC_Fincon_Logger::log('PRODUCT SYNC STARTING');
		endif;

		if(get_option('fincon_woocommerce_product_sync_last_date') && get_option('fincon_woocommerce_product_sync_last_time')):

			$_SYNC_DATE = get_option('fincon_woocommerce_product_sync_last_date');
			$_SYNC_TIME = get_option('fincon_woocommerce_product_sync_last_time');

			$_SYNC_DATE = str_replace("-", "", $_SYNC_DATE);

			$_TYPE = 'partial';

		endif;

		update_option('fincon_woocommerce_product_sync_running', 'yes');

		$this->Login();


		switch($_TYPE):

			case "full";
				$_DATA = $this->GetStock($_REC_NO);
			break;

			case "partial";
				$_DATA = $this->GetStockChanged($_SYNC_DATE, $_SYNC_TIME, $_REC_NO);
			break;

		endswitch;

		$_COUNT 	= $_DATA['Count'];
		$_REC_NO 	= $_DATA['RecNo'];
		$_STOCK 	= $_DATA['Stock'];

		$_NOT_FOUND = false;


		if((int)$_DATA['Count'] > 0):

			foreach($_STOCK as $_ITEM):

				$this->run_update_insert_product($_ITEM);

			endforeach;

		else:
			$_NOT_FOUND = true;
			WC_Fincon_Logger::log('NO PRODUCTS TO SYNC');

		endif;

		$this->Logout();


		/* ORGANISE ERRORS */
		$_ERRORS = get_option('fincon_woocommerce_product_sync_errors');

		if(!$_ERRORS):
			$_ERRORS = array();
		else:
			$_ERRORS = maybe_unserialize($_ERRORS);
		endif;

		if(count($this->_ERRORS) == 1 && $this->_ERRORS[0] == 'No records found.'):
			$_MAIL_ERRORS = $_ERRORS;
		else:
			$_MAIL_ERRORS = array_merge($_ERRORS, $this->_ERRORS);
		endif;


		/* ORGANISE COUNTS */
		$_DONE = get_option('fincon_woocommerce_product_sync_count');
		
		if(!$_DONE): $_DONE = 0; endif;

		$_DONE += $_COUNT;

		if($_REC_NO == 0 || $_COUNT < $this->_S_ITERATE):

			delete_option('fincon_woocommerce_product_sync_rec_no', $_REC_NO);
			update_option('fincon_woocommerce_product_sync_last_date', wp_date('Y-m-d'));
			update_option('fincon_woocommerce_product_sync_last_time', wp_date('H:i'));

			update_option('fincon_woocommerce_product_sync_count', '0');

			update_option('fincon_woocommerce_product_sync_errors', array());

			if(get_option('fincon_woocommerce_enable_product_email') == 'yes' && $_DONE > 0):
				Fincon_Woocommerce_Admin::do_email_notification('products', $_MAIL_ERRORS);
			endif;

			WC_Fincon_Logger::log('PRODUCT SYNC FINISHED');	


		else:
			update_option('fincon_woocommerce_product_sync_rec_no', $_REC_NO);

			update_option('fincon_woocommerce_product_sync_count', $_DONE);

			update_option('fincon_woocommerce_product_sync_errors', $_MAIL_ERRORS);

			if($ISCRON):
				$this->run_product_sync($ISCRON);
			else:
				wp_schedule_single_event(time(), 'fincon_woocommerce_sync_products');
			endif;
		endif;


		update_option('fincon_woocommerce_product_sync_running', 'no');
		
	}









	
	/*
	
	 */
	public function run_update_insert_product($_PRODUCT){

		$_NEW = false;

		$_FLOC 		= $this->_S_LOC;
		$_PRICE     = 'SellingPrice'.$this->_PRICE;
		$_STATUS	= $this->_PROD_STATUS;
		$_DO_IMAGES	= $this->_SYNC_IMAGES;

		extract($_PRODUCT);

		$_ID = wc_get_product_id_by_sku($ItemNo);

		if($Active == 'Y' && $WebList == 'Y' && $CatWebList == 'Y'):			

			if ($_ID !== 0):

				/* UPDATE */
				$_PROD = wc_get_product($_ID);

			else:

				/* INSERT */
				$_PROD = new WC_Product();
				$_PROD->set_sku($ItemNo);

				$_NEW = true;		

				$_PROD->save();

				$_ID = $_PROD->get_id();		

			endif;

			if($_NEW):	

				$_PROD->set_name($Description);			

				$_PROD->set_status($_STATUS); 

				$_PROD->set_catalog_visibility('visible');

				if($this->_DETAILED == 'no'):

					$_PROD->set_description($Description);

				endif;

				$_PROD->set_manage_stock(true);

				$_PROD->set_backorders('no');

				$_PROD->set_reviews_allowed(true);

				$_PROD->set_sold_individually(false);


			endif;

			$_PROD_OBJ = get_post($_ID);

			if($_PROD_OBJ->post_status == 'draft'):
				wp_update_post( array(
				    'ID' => $_PROD_OBJ->ID,
				    'post_status' => 'publish',
				) );
			endif;


			$_PROD->set_catalog_visibility('visible');

			if($this->_TITLES && !$_NEW):
				$_PROD->set_name($Description);
			endif;

			if(($this->_DETAILED == 'create' && $_NEW) || $this->_DETAILED == 'update'):

				$_THE_DESCRIPTION = $Description;

				$_DESCRIPTIONS = $this->GetDetailDescriptions($ItemNo);

				if(count($_DESCRIPTIONS) > 0):

					$_FIRST_DESC = reset($_DESCRIPTIONS);

					if(isset($_FIRST_DESC['DetailDescription']) && $_FIRST_DESC['DetailDescription'] != ''):

						$_THE_DESCRIPTION = $_FIRST_DESC['DetailDescription'];

					endif;

				endif;

				$_PROD->set_description($_THE_DESCRIPTION);

			endif;

			$_CATS = array();

			if($this->_PROD_CAT):

				$_CAT_ID = 0;

				if($CatDescription):

					$_CAT_ITEMS = explode(" > ", $CatDescription);

					array_reverse($_CAT_ITEMS);

					foreach($_CAT_ITEMS as $_CAT):

						$_CAT_ID  	= $this->get_category_id($_CAT, $Category, $_CAT_ID);

						if($_CAT_ID):
							$_CATS[] = $_CAT_ID;
						endif;

					endforeach;

				else:

					$_CAT_ID = $this->get_category_id($Category, $Category, $_CAT_ID);

					if($_CAT_ID):
						$_CATS[] = $_CAT_ID;
					endif;

				endif;
			endif;

			if($this->_PROD_GROUP):
				if($GroupDescription):

					$_CAT_ITEMS = explode(" > ", $GroupDescription);

					array_reverse($_CAT_ITEMS);

					$_CAT_ID = 0;

					foreach($_CAT_ITEMS as $_CAT):

						$_CAT_ID  	= $this->get_category_id($_CAT, $Group, $_CAT_ID);

						if($_CAT_ID):
							$_CATS[] = $_CAT_ID;
						endif;

					endforeach;

				else:
					$_CAT_ID  	= $this->get_category_id($Group, $Group, $_CAT_ID);

					if($_CAT_ID):
						$_CATS[] = $_CAT_ID;
					endif;
				endif;
			endif;
			

			if(count($_CATS) > 0):
				$_PROD->set_category_ids($_CATS);
			endif;


			$_STOCKQTY = $this->GetStockQuantity($ItemNo);
			
			$_PROD->set_stock_quantity($_STOCKQTY);

			if ($_STOCKQTY > 0):
				$_PROD->set_stock_status('instock');
			else:
				$_PROD->set_stock_status('outofstock');
			endif;	

			$_PROD->save();

			$_PROD->set_price($_PRODUCT[$_PRICE]);

			$_PROD->set_regular_price($_PRODUCT[$_PRICE]);		

			$_PROD->set_weight($Weight);

			$_PROD->set_length($BoxLength);

			$_PROD->set_width($BoxWidth);

			$_PROD->set_height($BoxHeight);


			if($_DO_IMAGES && !$_PROD->get_image_id()):

				$_IMAGES = $this->GetStockPictures($ItemNo);

				if($_IMAGES):

					$_IMAGE = reset($_IMAGES);

					if($_IMAGE['Picture'] != ''):

						$_ATTACHMENT_ID =  $this->upload_attach_image($ItemNo, $_IMAGE['Picture']);

						if($_ATTACHMENT_ID):

							$_PROD->set_image_id($_ATTACHMENT_ID);

						endif;

					endif;

				endif;

			endif;


			$_PROD->save();

			$_ID = $_PROD->get_id();

			if((int)$_ID > 0):

				if($_NEW):

					WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): CREATED');

				else:

					WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): UPDATED');

				endif;
			

			else:


				if($_NEW):

					$this->_ERRORS[] = 'PRODUCT ('.$ItemNo.'): CREATE FAILED';
					WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): CREATE FAILED');

				else:

					$this->_ERRORS[] = 'PRODUCT ('.$ItemNo.'): UPDATE FAILED';
					WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): UPDATE FAILED');

				endif;
				

			endif;

		else:

			if ($_ID !== 0):

				$_WHAT_TO_DO_WITH_YOU = wc_get_product($_ID);

				switch($this->_PROD_ACTION):

					case "draft":

						wp_update_post(
							array(
								'ID' => $_ID,
								'post_status' => 'draft'
							)

						);


						WC_Fincon_Logger::log('FINCON--PRODUCT('.$ItemNo.')::DRAFTED');

					break;

					case "hide":

						$_WHAT_TO_DO_WITH_YOU->set_catalog_visibility('hidden');
						$_WHAT_TO_DO_WITH_YOU->save();

						WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): HIDDEN');

					break;

					case "trash":


		                $_WHAT_TO_DO_WITH_YOU->delete();

		                if('trash' === $_WHAT_TO_DO_WITH_YOU->get_status()):

		                	wc_delete_product_transients($_ID);
		                	WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): DELETED');
		                	
		                else:
		                	WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): DELETE FAILED');
		                	
		                endif;

					break;

				endswitch;

				

			else:

                WC_Fincon_Logger::log('PRODUCT ('.$ItemNo.'): SKIPPED');

			endif;	

		endif;


	}









	
	/*
	
	 */
	public function run_account_sync($ISCRON = false){

		require_once(ABSPATH.'wp-admin/includes/user.php');

		$_TYPE = 'full';

		

		if(get_option('fincon_woocommerce_user_sync_rec_no') && get_option('fincon_woocommerce_user_sync_rec_no') != ''):
			$_REC_NO = get_option('fincon_woocommerce_user_sync_rec_no');
			WC_Fincon_Logger::log('USER SYNC CONTINUING');
		else:
			$_REC_NO = 0;
			WC_Fincon_Logger::log('USER SYNC STARTING');
		endif;

		if(get_option('fincon_woocommerce_user_sync_last_date') && get_option('fincon_woocommerce_user_sync_last_time')):

			$_SYNC_DATE = get_option('fincon_woocommerce_user_sync_last_date');
			$_SYNC_TIME = get_option('fincon_woocommerce_user_sync_last_time');

			$_SYNC_DATE = str_replace("-", "", $_SYNC_DATE);

			$_TYPE = 'partial';

		endif;

		update_option('fincon_woocommerce_user_sync_running', 'yes');

		$this->Login();

		switch($_TYPE):

			case "full";
				$_DATA = $this->GetDebAccounts($_REC_NO);
			break;

			case "partial";
				$_DATA = $this->GetDebAccountsChanged($_SYNC_DATE, $_SYNC_TIME, $_REC_NO);
			break;

		endswitch;

		$this->Logout();

		$_COUNT 	= $_DATA['Count'];
		$_REC_NO 	= $_DATA['RecNo'];
		$_ACCOUNTS 	= $_DATA['Accounts'];


		if((int)$_DATA['Count'] > 0):

			foreach($_ACCOUNTS as $_ITEM):

				$this->run_update_insert_account($_ITEM);

			endforeach;

		else:

			WC_Fincon_Logger::log('NO USERS TO SYNC');

		endif;

		
		/* ORGANISE ERRORS */
		$_ERRORS = get_option('fincon_woocommerce_user_sync_errors');

		if(!$_ERRORS):
			$_ERRORS = array();
		else:
			$_ERRORS = maybe_unserialize($_ERRORS);
		endif;

		if(count($this->_ERRORS) == 1 && $this->_ERRORS[0] == 'No records found.'):
			$_MAIL_ERRORS = $_ERRORS;
		else:
			$_MAIL_ERRORS = array_merge($_ERRORS, $this->_ERRORS);
		endif;


		/* ORGANISE COUNTS */
		$_DONE = get_option('fincon_woocommerce_user_sync_count');

		if(!$_DONE): $_DONE = 0; endif;

		$_DONE += $_COUNT;

		if($_REC_NO == 0 || $_COUNT < $this->_U_ITERATE):

			delete_option('fincon_woocommerce_user_sync_rec_no', $_REC_NO);
			update_option('fincon_woocommerce_user_sync_last_date', wp_date('Y-m-d'));
			update_option('fincon_woocommerce_user_sync_last_time', wp_date('H:i'));
			
			update_option('fincon_woocommerce_user_sync_count', '0');

			update_option('fincon_woocommerce_user_sync_errors', array());

			if(get_option('fincon_woocommerce_enable_user_email') == 'yes' && $_DONE > 0):
				
				Fincon_Woocommerce_Admin::do_email_notification('users', $_MAIL_ERRORS);
				
			endif;

			WC_Fincon_Logger::log('USER SYNC FINISHED');

		else:

			update_option('fincon_woocommerce_user_sync_rec_no', $_REC_NO);

			update_option('fincon_woocommerce_user_sync_count', $_DONE);

			update_option('fincon_woocommerce_user_sync_errors', $_MAIL_ERRORS);		

			if($ISCRON):
				$this->run_account_sync($ISCRON);
			else:
				wp_schedule_single_event(time(), 'fincon_woocommerce_sync_accounts');
			endif;	

		endif;

		update_option('fincon_woocommerce_user_sync_running', 'no');

	}









	
	/*
	
	 */
	public function run_update_insert_account($_ACCOUNT){

		$_DO_INSERT = false;

		extract($_ACCOUNT);

		$_ID = username_exists($AccNo);

		if ($WebList == 'Y' && $Active == 'Y'):

			$_DO_INSERT = true;

			if(!validate_username($AccNo)):

				WC_Fincon_Logger::log('USER ('.$AccNo.') INSERT FAILED: Invalid Username.');
				$this->_ERRORS[] = 'USER ('.$AccNo.') INSERT FAILED: Invalid Username.';
				$_DO_INSERT = false;
				
			else:

				if($_ID == 0):

					if($Email && filter_var($Email, FILTER_VALIDATE_EMAIL)):

						$_ID = wp_create_user($AccNo, $Password, $Email);

						if(is_wp_error($_ID)):

							$_DO_INSERT = false;

							WC_Fincon_Logger::log('USER ('.$AccNo.') INSERT FAILED: '.$_ID->get_error_message());

							$this->_ERRORS[] = 'USER ('.$AccNo.') INSERT FAILED: '.$_ID->get_error_message();

						else:

							$_USER = new WP_User($_ID);
							$_USER->set_role('customer');

							WC_Fincon_Logger::log('USER ('.$AccNo.'): INSERTED');

						endif;	

					else:

						$this->_ERRORS[] = 'USER ('.$AccNo.') INSERT FAILED: Invalid Email Address';

					endif;			

				else:

					WC_Fincon_Logger::log('USER ('.$AccNo.'): Update');

				endif;

			endif;


			if($_DO_INSERT):

				update_user_meta( $_ID, "billing_company", $DebName);
				update_user_meta( $_ID, "billing_address_1", $Addr1);
				update_user_meta( $_ID, "billing_address_2", $Addr2);
				update_user_meta( $_ID, "billing_city", $Addr3);
				update_user_meta( $_ID, "billing_postcode", $PCode);
				update_user_meta( $_ID, "billing_country", 'ZA' );
				update_user_meta( $_ID, "billing_state", '' );
				update_user_meta( $_ID, "billing_email", $StatementMail);
				update_user_meta( $_ID, "billing_phone", $TelNo);

				update_user_meta( $_ID, "shipping_first_name",$DelName);
				update_user_meta( $_ID, "shipping_last_name", '' );
				update_user_meta( $_ID, "shipping_company", $DebName );
				update_user_meta( $_ID, "shipping_address_1", $DelAddr1 );
				update_user_meta( $_ID, "shipping_address_2", $DelAddr2 );
				update_user_meta( $_ID, "shipping_city", $DelAddr3 );
				update_user_meta( $_ID, "shipping_postcode", $DelPCode );
				update_user_meta( $_ID, "shipping_country", 'ZA' );
				update_user_meta( $_ID, "shipping_state", $DelAddr4 );

			endif;


		else:

			if($_ID > 0):

				wp_delete_user($_ID);
				WC_Fincon_Logger::log('User ('.$AccNo.'): Deleted');

			endif;


		endif;

	}



}



?>