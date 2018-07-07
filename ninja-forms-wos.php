<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Ninja Forms - WOS
 * Plugin URI: http://www.burak-dogan.com/ninja-forms-firebase
 * Description: Plugin simply integrates Waste Operation System with ninja forms
 * Version: 3.0.0
 * Author: Burak Doğan
 * Author URI: http://www.burak-dogan.com
 * Text Domain: ninja-forms-wos
 *
 * Copyright 2017 Burak Doğan.
 */

require_once 'WosApiClient.php';
require_once 'YoutubeApiClient.php';

if( version_compare( get_option( 'ninja_forms_version', '0.0.0' ), '3', '<' ) || get_option( 'ninja_forms_load_deprecated', FALSE ) ) {

    //include 'deprecated/ninja-forms-firebase.php';

} else {

    /**
     * Class NF_Wos
     */
    final class NF_Wos
    {
        const VERSION = '0.0.1';
        const SLUG    = 'wos';
        const NAME    = 'Wos';
        const AUTHOR  = 'Burak Doğan';
        const PREFIX  = 'NF_Wos';

        /**
         * @var NF_Wos
         * @since 3.0
         */
        private static $instance;

        /**
         * Plugin Directory
         *
         * @since 3.0
         * @var string $dir
         */
        public static $dir = '';

        /**
         * Plugin URL
         *
         * @since 3.0
         * @var string $url
         */
        public static $url = '';

        /**
         * Main Plugin Instance
         *
         * Insures that only one instance of a plugin class exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 3.0
         * @static
         * @static var array $instance
         * @return NF_Wos Highlander Instance
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof NF_Wos)) {
                self::$instance = new NF_Wos();

                self::$dir = plugin_dir_path(__FILE__);

                self::$url = plugin_dir_url(__FILE__);

                /*
                 * Register our autoloader
                 */
                spl_autoload_register(array(self::$instance, 'autoloader'));
            }
            
            return self::$instance;
        }

        public function __construct()
        {
            /*
             * Required for all Extensions.
             */
            add_action( 'admin_init', array( $this, 'setup_license') );

		        /*
						 * Optional. If your extension creates a new field interaction or display template...
						 */
		        add_filter( 'ninja_forms_register_fields', array($this, 'register_fields'));

            /*
             * Optional. If your extension processes or alters form submission data on a per form basis...
             */
            add_filter( 'ninja_forms_register_actions', array($this, 'register_actions'));

            /*
             * Settings
             */
	          add_filter( 'ninja_forms_plugin_settings', array($this, 'plugin_settings'), 10, 1 );

	          /*
	           * Setttings Group
	           */
		        add_filter( 'ninja_forms_plugin_settings_groups', array( $this, 'plugin_settings_groups' ), 10, 1 );

		        /**
		         * Ajax calls
		         */
	          add_filter('wp_ajax_nopriv_get_province_list', array($this, 'get_province_list'), 0, 0);
	          add_filter('wp_ajax_get_province_list', array($this, 'get_province_list'), 0, 0);
	          add_filter('wp_ajax_nopriv_get_district_list', array($this, 'get_district_list'), 0, 1);
	          add_filter('wp_ajax_get_district_list', array($this, 'get_district_list'), 0, 1);
	          add_filter('wp_ajax_nopriv_get_stuff_list', array($this, 'get_stuff_list'), 0, 1);
	          add_filter('wp_ajax_get_stuff_list', array($this, 'get_stuff_list'), 0, 1);

		        /**
		         * Custom events
		         */
	          add_filter('wos_get_events', array($this, 'wos_get_events'), 0, 1);

	          /**
	           * Short codes
	           */
			  add_shortcode('wos_total_collected', array($this, 'get_total_collected'));
			  add_shortcode('youtube_video', array($this, 'youtube_video'));
        }

        public function wos_get_events($atts = array()) {
	        $atts = array_values($atts);
	        WosApiClient::Init(Ninja_Forms()->get_setting('host'), Ninja_Forms()->get_setting('token'));
	        return WosApiClient::EventsRecord($atts[0], $atts[1]);
        }

	      public function get_total_collected($atts = array(), $content = null, $tag = '') {
			$atts = array_change_key_case((array)$atts, CASE_LOWER);
			$args = shortcode_atts([ 'year' => null ], $atts, $tag);
        	$atts = array_merge(array(
        		'format' => true
			), $atts ? $atts : array());
				$cache_key = !$args['year'] ? 'wos_total_collected' : 'wos_total_collected_' . $args['year'];
		      $totalCollected = wp_cache_get($cache_key);
		      if ($totalCollected === false) {
			      WosApiClient::Init(Ninja_Forms()->get_setting('host'), Ninja_Forms()->get_setting('token'));
			      $totalCollected = WosApiClient::TotalCollectedRecord($args['year']);
			      wp_cache_set($cache_key, $totalCollected,'', 3600);
		      }
		      $total = $totalCollected && isset($totalCollected['total_collected']) ? $totalCollected['total_collected'] : -1;
					return $atts['format'] ? number_format($total, 0, '', '.') . ' kg' : $total;
		  }
		  
		  
	      public function youtube_video($atts = array(), $content) {
		      $atts = array_merge(array(
			      'channel_id' => null,
			      'limit' => 4
		      ), $atts ? $atts : array());
		      YoutubeApiClient::Init(Ninja_Forms()->get_setting('youtube_key'));
		      $videos = YoutubeApiClient::getChannelVideos($atts['channel_id'], $atts['limit']);
		      // Render banner
		      ob_start();
		      include(locate_template('template-parts/section/youtube.php'));
		      wp_reset_postdata();
		      return ob_get_clean();
	      }

	      public function get_province_list()
	      {
		      header('Content-Type: application/json');
		      header('Cache-Control: no-cache');
		      header('Pragma: no-cache');
		      $provinceList = wp_cache_get('wos_province_list');
		      if ($provinceList === false) {
			      WosApiClient::Init(Ninja_Forms()->get_setting('host'), Ninja_Forms()->get_setting('token'));
			      $provinceList = WosApiClient::ProvinceList();
			      wp_cache_set('wos_province_list', $provinceList,'', 3600);
		      }
		      echo json_encode($provinceList);
		      wp_die();
	      }

		    public function get_district_list()
		    {
			    header('Content-Type: application/json');
			    header('Cache-Control: no-cache');
			    header('Pragma: no-cache');
			    $districtList = wp_cache_get('wos_district_list');
			    if ($districtList === false) {
				    WosApiClient::Init(Ninja_Forms()->get_setting('host'), Ninja_Forms()->get_setting('token'));
				    $districtList = WosApiClient::DistrictList(intval( $_POST['provinceId'] ));
				    wp_cache_set('wos_district_list', $districtList,'', 3600);
			    }
			    echo json_encode($districtList);
			    wp_die();
		    }

		    public function get_stuff_list()
		    {
			    header('Content-Type: application/json');
			    header('Cache-Control: no-cache');
			    header('Pragma: no-cache');
			    $stuffList = wp_cache_get('wos_stuff_list');
			    if ($stuffList === false) {
				    WosApiClient::Init(Ninja_Forms()->get_setting('host'), Ninja_Forms()->get_setting('token'));
				    $stuffList = WosApiClient::StuffList();
				    wp_cache_set('wos_stuff_list', $stuffList,'', 3600);
			    }
			    echo json_encode($stuffList);
			    wp_die();
		    }

	     /**
	      * Plugin Settings Groups
	      *
	      * @param $groups
	      *
	      * @return mixed
	      */
        public function plugin_settings_groups($groups)
        {
	        $groups[ 'wos_settings' ] = array(
		        'id' => 'wos_settings',
		        'label' => __( 'Waste Operation Settings', 'ninja-forms-wos' ),
	        );
	        return $groups;
        }

	     /**
	      * Plugin Settings
	      *
	      * @param $settings
	      *
	      * @return mixed
	      */
        public function plugin_settings($settings)
        {
	        $settings[ 'wos_settings' ] = array(
		        'token' => array(
			        'id'    => 'token',
			        'type'  => 'textbox',
			        'label'  => __( 'Token', 'ninja-forms-wos' ),
			        'desc'  => __( 'WOS token for api calls.', 'ninja-forms-wos' ),
		        ),
		        'host' => array(
			        'id'    => 'host',
			        'type'  => 'textbox',
			        'label'  => __( 'Host address', 'ninja-forms-wos' ),
			        'desc'  => __( 'WOS host address.', 'ninja-forms-wos' ),
		        ),
		        'youtube_key' => array(
			        'id'    => 'youtube_key',
			        'type'  => 'textbox',
			        'label'  => __( 'Youtube Key', 'ninja-forms-wos' ),
			        'desc'  => __( 'Youtube api key for api calls.', 'ninja-forms-wos' ),
		        )
	        );
	        return $settings;
        }

        /**
         * Optional. If your extension processes or alters form submission data on a per form basis...
         */
        public function register_actions($actions)
        {
            $actions[ 'waste_notification' ] = new NF_Wos_Actions_SendWasteNotification();
            $actions[ 'stuff_notification' ] = new NF_Wos_Actions_SendStuffNotification();

            return $actions;
        }

		    /**
		     * Optional. If your extension creates a new field interaction or display template...
		     */
		    public function register_fields($actions)
		    {
			    $actions[ 'province_selector' ] = new NF_Wos_Fields_ProvinceSelector();
			    $actions[ 'district_selector' ] = new NF_Wos_Fields_DistrictSelector();
			    $actions[ 'stuff_selector' ] = new NF_Wos_Fields_StuffSelector();

			    return $actions;
		    }

        /*
         * Optional methods for convenience.
         */

        public function autoloader($class_name)
        {
            if (class_exists($class_name)) return;

            if ( false === strpos( $class_name, self::PREFIX ) ) return;

            $class_name = str_replace( self::PREFIX, '', $class_name );
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

            if (file_exists($classes_dir . $class_file)) {
                require_once $classes_dir . $class_file;
            }
        }
        
        /**
         * Template
         *
         * @param string $file_name
         * @param array $data
         */
        public static function template( $file_name = '', array $data = array() )
        {
            if( ! $file_name ) return;

            extract( $data );

            include self::$dir . 'includes/Templates/' . $file_name;
        }
        
        /**
         * Config
         *
         * @param $file_name
         * @return mixed
         */
        public static function config( $file_name )
        {
            return include self::$dir . 'includes/Config/' . $file_name . '.php';
        }

        /*
         * Required methods for all extension.
         */

        public function setup_license()
        {
            if ( ! class_exists( 'NF_Extension_Updater' ) ) return;

            new NF_Extension_Updater( self::NAME, self::VERSION, self::AUTHOR, __FILE__, self::SLUG );
        }
    }

    /**
     * The main function responsible for returning The Highlander Plugin
     * Instance to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     * @since 3.0
     * @return {class} Highlander Instance
     */
    function NF_Wos()
    {
        return NF_Wos::instance();
    }

    NF_Wos();
}
