<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Wos_Fields_ProvinceSelector
 */
class NF_Wos_Fields_ProvinceSelector extends NF_Abstracts_List
{
	protected $_name = 'province_selector';

	protected $_section = 'common';

	protected $_type = 'listselect';

	protected $_templates = 'listselect';

	protected $_settings_all_fields = array(
		'key', 'label', 'label_pos', 'required', 'classes', 'admin_label', 'help', 'description'
	);

	public function __construct()
	{
		parent::__construct();

		add_filter( 'ninja_forms_render_options_' . $this->_name,          array( $this, 'get_options'   ), 10, 2 );

		$this->_nicename = __( 'WOS Province Selector', 'ninja-forms' );
	}

	public function get_options($options, $settings )
	{
		print_r('test');
		$order = 0;
		$options = array();
		foreach( Ninja_Forms()->config( 'CountryList' ) as $label => $value ){
			$options[] = array(
				'label'  => $label,
				'value' => $value,
				'calc' => '',
				'selected' => 0,
				'order' => $order
			);

			$order++;
		}

		return $options;
	}


}