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

		add_filter( 'ninja_forms_display_after_field_type_' . $this->_name, array( $this, 'before_field_display' ),10, 1 );

		$this->_nicename = __( 'WOS Province Selector', 'ninja-forms' );
	}

	public function before_field_display()
	{
		add_filter('wp_footer', array($this, 'add_inline_script'), 20, 0);
	}

	public function add_inline_script()
	{
		?>
		<script>
			var ajaxUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
      var provinceFieldController = Marionette.Object.extend( {
        initialize: function() {
          this.listenTo( nfRadio.channel( 'province_selector' ), 'attach:view', this.load );
        },
	      // Load province list
	      provinces: function (cb) {
          jQuery.post(ajaxUrl, { action: 'get_province_list' }, function (response) {
            cb(response);
          });
        },
	      load: function(provinceField) {
          jQuery(provinceField.$el).find('select').attr('disabled', 'disabled');
          var provinceModel = provinceField.model;
		      this.provinces(function(provinces) {
            provinceModel.set('options', provinces.map(function(p, k) {
			        return {
			          label: p.name,
				        value: parseInt(p.id, 10),
				        calc: '',
				        selected: k === 0,
				        order: k
			        };
			      }));
            provinceModel.trigger( 'reRender', provinceModel );
            provinceModel.set({ 'value': parseInt(provinces[0].id, 10) });
          });
        }
      });
      jQuery( document ).ready( function( $ ) {
        new provinceFieldController();
      });
		</script>
		<?php
	}

}