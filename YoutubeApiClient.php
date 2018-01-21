<?php if ( ! defined( 'ABSPATH' ) ) exit;

class YoutubeApiClient {

	private static $host = "https://www.googleapis.com/";
	private static $key;

	public static function Init($key) {
		self::$key = $key;
	}

	/**
	 * @param $endpoint
	 * @param null $data
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	private static function _apiCall($endpoint, $query = array()) {
		// Initialize cURL
		$curl = curl_init();

		// Set key
		$query['key'] = self::$key;

		// Set cURL options
		curl_setopt( $curl, CURLOPT_URL, self::$host . $endpoint . '?' . http_build_query($query) );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'GET' );
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
	 * @param $channelId
	 * @param int $maxResults
	 * @param string $order
	 * @param string $part
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function getChannelVideos($channelId, $maxResults = 4, $order = 'date', $part = array('snippet')) {
		$results = self::_apiCall('/youtube/v3/search', array(
			'channelId' => $channelId,
			'part' => implode(',', $part),
			'type' => 'video',
			'maxResults' => $maxResults,
			'order' => $order
		));
		return $results && isset($results['items']) ? $results['items'] : array();
	}

}