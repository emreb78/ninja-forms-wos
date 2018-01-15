<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Wos_Fields_ProvinceSelector
 */
class NF_Wos_Fields_StuffSelector extends NF_Abstracts_List
{
	protected $_name = 'stuff_selector';

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

		$this->_nicename = __( 'WOS Stuff Selector', 'ninja-forms' );
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
      var stuffFieldController = Marionette.Object.extend( {
        initialize: function() {
          this.listenTo( nfRadio.channel( 'stuff_selector' ), 'attach:view', this.load );
        },
	      // Load stuff list
	      stuffs: function (cb) {
          jQuery.post(ajaxUrl, { action: 'get_stuff_list' }, function (response) {
            cb(response);
          });
        },
        load: function(stuffField) {
          jQuery(stuffField.$el).find('select').attr('disabled', 'disabled');
          var stuffModel = stuffField.model;
          this.stuffs(function(stuffs) {
            if (!stuffs || !stuffs.length) {
              stuffModel.set('options', []);
              stuffModel.trigger( 'reRender', stuffModel );
              return;
            }
            stuffModel.set('options', stuffs.map(function(p, k) {
              return {
                label: p.name,
                value: parseInt(p.id, 10),
                calc: '',
                selected: k === 0,
                order: k
              };
            }));
            stuffModel.trigger( 'reRender', stuffModel );
            stuffModel.set({ 'value': parseInt(stuffs[0].id, 10) });
          });
        }
      });
      jQuery( document ).ready( function( $ ) {
        new stuffFieldController();
      });
		</script>
		<?php
	}

}