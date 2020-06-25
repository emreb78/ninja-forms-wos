<?php if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' )) exit;

require_once plugin_dir_path(dirname(dirname(__FILE__))) . '/WosApiClient.php';

/**
 * Class NF_Wos_Actions_SendStuffNotification
 */
final class NF_Wos_Actions_SendStuffNotification extends NF_Abstracts_Action
{
  /**
   * @var string
   */
  protected $_name  = 'stuff_notification';

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

		$settings = NF_Wos::config( 'ActionSendStuffNotificationSettings' );

		$this->_settings = array_merge( $this->_settings, $settings );

    $this->_nicename = __( 'Stuff Notification', 'ninja-forms' );
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
  	// Set errors
  	$errors = array();

  	// Get config
		$fields = array_keys(NF_Wos::config( 'ActionSendStuffNotificationSettings' ));
		// Get only required fields
	  $wasteData = array_filter(
		  $action_settings,
		  function ($key) use ($fields) {
			  return in_array($key, $fields);
		  },
		  ARRAY_FILTER_USE_KEY
	  );


	  $fieldErrors = array();
	  try {
		  // Init client
		  WosApiClient::Init(Ninja_Forms()->get_setting('host'), Ninja_Forms()->get_setting('token'));
		  // Send operation data
		  $results = WosApiClient::StuffOperationRecord($wasteData);
			// Check errors
		  if (!$results || $results["success"] !== "ok") {
		  	$message = $results ? $results["message"] : "Bilinmeyen hata";
		  	if ($message == "Doğrulama hatası." && $results["invalid_fields"]) {
		  		foreach ($results["invalid_fields"] as $fieldName => $fieldError) {
					  $fieldErrors[ 'invalid_' . $fieldName ] = $fieldError;
				  }
			  }
			  throw new Exception($message);
		  }
		  $data[ 'actions' ][ 'stuff_notification' ][ 'sent' ] = true;
	  } catch (Exception $e) {
			// Set errors
	  	$errors[ 'wos_api_error' ] = sprintf( __( 'Your email action "%s" has an error. Please check this setting and try again. ERROR: %s', 'ninja-forms'), $action_settings[ 'label' ], $e->getMessage() );
	  }

	  // Report admin burası formun çalışmasını engeliyor
	  // if ( current_user_can( 'manage_options' ) ){
	 //	  $data[ 'errors' ][ 'form' ] = $errors;
	 // }

	  // Show field errors
	  if ($fieldErrors) {
		  $data[ 'errors' ][ 'form' ] = $fieldErrors;
	  }

    return $data;
  }

}
