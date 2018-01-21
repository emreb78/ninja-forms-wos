<?php if ( ! defined( 'ABSPATH' ) ) exit;

class WosApiClient {

	private static $host;
	private static $token;
	private static $ipAddress;

	public static function Init($host, $token) {
		self::$host = $host;
		self::$token = $token;
	}

	private static function publicIpAddress() {
		return self::$ipAddress ? self::$ipAddress : (self::$ipAddress = trim(file_get_contents('http://icanhazip.com')));
	}

	private static function getAuth() {
		$ipAddress = self::publicIpAddress();
		return base64_encode($ipAddress . ':' . hash('sha256', $ipAddress . self::$token));
	}

	/**
	 * @param $endpoint
	 * @param null $data
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	private static function _apiCall($endpoint, $data = null) {
		// Request Body
		$requestBody = $data ? http_build_query($data) : null;
		// Initialize cURL
		$curl = curl_init();
		// Headers
		$headers = array(
			sprintf('Authorization: Basic %s', self::getAuth())
		);
		// Set cURL options
		curl_setopt( $curl, CURLOPT_URL, 'http://' . self::$host . $endpoint );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $requestBody ? 'POST' : 'GET' );
		if ($requestBody) {
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $requestBody );
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
			$headers['Content-Length'] = strlen($requestBody);
		}
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
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
			throw new Exception(sprintf('[WOS_CLIENT ERROR] %s', $curlError ? $curlError : $httpcode . ' - ' . $response));
		}
		// Close connection
		curl_close( $curl );
		$result = json_decode($response, true);

		// Check json errors
		switch(json_last_error())
		{
			case JSON_ERROR_DEPTH:
				throw new Exception(sprintf('[WOS_JSON_PARSE ERROR] %s', 'Azami yığın derinliği aşıldı'));
				break;
			case JSON_ERROR_CTRL_CHAR:
				throw new Exception(sprintf('[WOS_JSON_PARSE ERROR] %s', 'Beklenmeyen kontol karakteri bulundu'));
				break;
			case JSON_ERROR_SYNTAX:
				throw new Exception(sprintf('[WOS_JSON_PARSE ERROR] %s', 'Sözdizimi hatası, kusurlu JSON'));
				break;
		}

		return $result;
	}

	/**
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function ProvinceList() {
		return self::_apiCall('/api/provinces.json');
	}

	/**
	 * @param $provinceId
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function DistrictList($provinceId) {
		return self::_apiCall('/api/districts.json?province_id=' . $provinceId);
	}

	/**
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function StuffList() {
		return self::_apiCall('/api/stuff.json');
	}

	/**
	 * @param $data
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function WasteOperationRecord($data) {
		return self::_apiCall('/api/waste_operations/create.json', $data);
	}

	/**
 * @param $data
 *
 * @return array|mixed|object
 * @throws Exception
 */
	public static function StuffOperationRecord($data) {
		return self::_apiCall('/api/stuff_operations/create.json', $data);
	}

	/**
	 * @param $data
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function TotalCollectedRecord() {
		return self::_apiCall('/api/total.json');
	}

	/**
	 * @param $start
	 * @param $end
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function EventsRecord($start, $end) {
		return self::_apiCall('/api/calendar.json', array( 'start' => $start, 'end' => $end ));
	}

}