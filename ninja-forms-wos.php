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

        }

        /**
         * Optional. If your extension processes or alters form submission data on a per form basis...
         */
        public function register_actions($actions)
        {
            $actions[ 'send_waste_notification' ] = new NF_Wos_Actions_SendWasteNotification();

            return $actions;
        }

		    /**
		     * Optional. If your extension creates a new field interaction or display template...
		     */
		    public function register_fields($actions)
		    {
			    $actions[ 'province_selector' ] = new NF_Wos_Fields_ProvinceSelector();

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
