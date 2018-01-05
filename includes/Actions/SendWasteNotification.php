<?php if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' )) exit;

/**
 * Class NF_Wos_Actions_SendWasteNotification
 */
final class NF_Wos_Actions_SendWasteNotification extends NF_Abstracts_Action
{
  /**
   * @var string
   */
  protected $_name  = 'waste_notification';

  /**
   * @var array
   */
  protected $_tags = array();

  /**
   * @var string
   */
  protected $_timing = 'early';

  /**
   * @var int
   */
  protected $_priority = '10';

  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct();

		$settings = NF_Wos::config( 'ActionSendWasteNotificationSettings' );

		$this->_settings = array_merge( $this->_settings, $settings );

		$this->_settings = array_merge($this->_settings, $settings);

    $this->_nicename = __( 'Send Waste Notification', 'ninja-forms' );
	}

	/**
	 * Process form
	 *
	 * @param $action_settings
	 * @param $form_id
	 * @param $data
	 *
	 * @return mixed
	 */
  public function process( $action_settings, $form_id, $data )
  {
    $errors = $this->check_for_errors($action_settings);

    // If there is no errors so send to firebase
    if (empty($errors)) {

    	// Get Firebase Database Type
	    $firebaseDbType = $action_settings['firebase_db_type'];

	    // Generate Access Token
	    $access_token = null;
	    try {
	    	$access_token = $this->get_access_token($action_settings['firebase_json_key'], $this->scopes[$firebaseDbType]);
	    } catch (Exception $e) {
		    $errors[ 'access_token' ] = sprintf( __( 'Your email action "%s" has an error. Please check this setting and try again. ERROR: %s', 'ninja-forms'), $action_settings[ 'label' ], $e->getMessage() );
	    }

    	// Request Method
    	$requestMethod = $action_settings['request_method'];

    	// Generate Request Address
    	$requestAddress = sprintf($this->endpoints[$firebaseDbType], $action_settings['database_name'], $action_settings['endpoint']);

	    // Generate For Firestore body
	    if ($firebaseDbType === 'firestore') {
	    	if (!isset($action_settings['json_body']['fields'])) $action_settings['json_body'] = array('fields' => $action_settings['json_body']);
	    	foreach ($action_settings['json_body']['fields'] as $key => $value) {
			    $action_settings['json_body']['fields'][$key] = array( "stringValue" => $value );
		    }
	    }
	    // Request Body
    	$requestBody = json_encode($action_settings['json_body']);

	    // Initialize cURL
	    $curl = curl_init();

	    // Set cURL options
	    curl_setopt( $curl, CURLOPT_URL, $requestAddress );
	    curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $requestMethod );
	    curl_setopt( $curl, CURLOPT_POSTFIELDS, $requestBody );
	    curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    sprintf('Authorization: Bearer %s', $access_token),
			    'Content-Length: ' . strlen($requestBody))
	    );

	    // Timeout
	    curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT ,0 );
	    curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );

	    // Get return value
	    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

	    // Make request
	    $response = curl_exec( $curl );
	    $httpcode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

	    // Check Request
	    if ($response === false || $httpcode < 200 || $httpcode >= 300) {
	    	$curlError = curl_error( $curl );
		    $errors[ 'data_not_sent' ] = sprintf( __( 'Your email action "%s" has an error. Please check this setting and try again. ERROR: %s', 'ninja-forms'), $action_settings[ 'label' ], $curlError ? $curlError : $httpcode . ' - ' . $response );
	    }

	    // Close connection
	    curl_close( $curl );
    }

	  // Only show errors to Administrators.
	  if ( $errors && current_user_can( 'manage_options' ) ){
		  $data[ 'errors' ][ 'form' ] = $errors;
	  }

    return $data;
  }

	/**
	 * @param $firebase_json_key
	 * @param $scope
	 *
	 * @return mixed
	 */
	protected function get_access_token( $firebase_json_key, $scope ) {
		$credential = new \Google\Auth\Credentials\ServiceAccountCredentials($scope, $firebase_json_key);
		$code = $credential->fetchAuthToken();
		return $code['access_token'];
	}

	/**
	 * Check form data for errors
	 *
	 * @param $action_settings
	 *
	 * @return array
	 */
	protected function check_for_errors( &$action_settings )
	{
		$errors = array();

		$fields = array('firebase_json_key', 'firebase_db_type', 'request_method', 'database_name', 'endpoint', 'json_body');

		foreach( $fields as $field ){
			// First Check for empty
			if(!isset($action_settings[ $field ]) || !$action_settings[ $field ]) {
				$errors['empty_' . $field] = sprintf( __( 'Your email action "%s" has an empty value for the "%s" setting. Please check this setting and try again.', 'ninja-forms'), $action_settings[ 'label' ], $field );
				continue;
			}

			switch (true) {
				// Check Json Body
				case ($field == 'json_body' || $field == 'firebase_json_key'):
					$action_settings[$field] = trim($action_settings[$field]);
					if (strpos($action_settings[$field], '{') === false) $action_settings[$field] = "{" . $action_settings[$field] . "}";
					// Decode & Check Json
					$action_settings[$field] = json_decode($action_settings[$field], true);
					if (json_last_error() !== JSON_ERROR_NONE) {
						$errors['invalid_' . $field] = sprintf( __( 'Your email action "%s" has an invalid value for the "%s" setting. Please check this setting and try again.', 'ninja-forms'), $action_settings[ 'label' ], $field );
						continue;
					}
					break;
			}

		}

		return $errors;
	}
}
