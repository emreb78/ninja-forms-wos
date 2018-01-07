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

	private static function _apiCall($endpoint, $data = null) {
		// Request Body
		$requestBody = $data ? json_encode($data) : null;
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
			$headers['Content-Type'] = 'application/json';
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
		return json_decode($response, true);
	}

	public static function ProvinceList() {
		return self::_apiCall('/api/provinces.json');
	}

	public static function DistrictList($provinceId) {
		return self::_apiCall('/api/districts.json?province_id=' . $provinceId);
	}
	public static function StuffList() {
		return self::_apiCall('/api/stuff.json');
	}
}