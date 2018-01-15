<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Wos_Fields_ProvinceSelector
 */
class NF_Wos_Fields_DistrictSelector extends NF_Abstracts_List
{
	protected $_name = 'district_selector';

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

		$this->_nicename = __( 'WOS District Selector', 'ninja-forms' );
	}

	public function before_field_display()
	{
		add_filter('wp_footer', array($this, 'add_inline_script'), 21, 0);
	}

	public function add_inline_script()
	{
		?>
		<script>
			var ajaxUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
      var districtFieldController = Marionette.Object.extend( {
        initialize: function() {
          this.listenTo( nfRadio.channel( 'province_selector' ), 'change:model', this.onChange );
          this.listenTo( nfRadio.channel( 'district_selector' ), 'attach:view', this.load );
        },
        onChange: function (provinceModel) {
          if (provinceModel.previous('value') === provinceModel.get('value')) return;
          this.onProvinceSelected(provinceModel.get('value'));
        },
        load: function(districtField) {
          this.districtField = districtField;
        },
	      // Load district list
	      districts: function (provinceId, cb) {
          jQuery.post(ajaxUrl, { action: 'get_district_list', provinceId: provinceId }, function (response) {
            cb(response);
          });
        },
        districtField: null,
        onProvinceSelected: function(provinceId) {
          if (!this.districtField) return;
          jQuery(this.districtField.$el).find('select').attr('disabled', 'disabled');
          var districtModel = this.districtField.model;
          this.districts(provinceId, function(districts) {
            if (!districts || !districts.length) {
              districtModel.set('options', []);
              districtModel.trigger( 'reRender', districtModel );
              return;
            }
            districtModel.set('options', districts.map(function(p, k) {
              return {
                label: p.name,
                value: parseInt(p.id, 10),
                calc: '',
                selected: k === 0,
                order: k
              };
            }));
            districtModel.trigger( 'reRender', districtModel );
            districtModel.set({ 'value': parseInt(districts[0].id, 10) });
          });
        }
      });
      jQuery( document ).ready( function( $ ) {
        new districtFieldController();
      });
		</script>
		<?php
	}

}