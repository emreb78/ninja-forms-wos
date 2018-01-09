<?php if ( ! defined( 'ABSPATH' ) ) exit;

return array(

	/*
	 * Name
	 */
	'name' => array(
		'name' => 'name',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'Firma ünvanı', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
	 * Province Id
	 */
	'province_id' => array(
		'name' => 'province_id',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'İl ID', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
	 * District Id
	 */
	'district_id' => array(
		'name' => 'district_id',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'İlçe ID', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Adres
   */
	'adress' => array(
		'name' => 'adress',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'Firma adresi', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Telefon
   */
	'telephone' => array(
		'name' => 'telephone',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'Telefon numarası', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Telefon
   */
	'telephone_2' => array(
		'name' => 'telephone_2',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'Telefon numarası 2', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Stuff
   */
	'stuff_id' => array(
		'name' => 'stuff_id',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'İstenen malzeme', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Yetkili
   */
	'yetkili' => array(
		'name' => 'yetkili',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'Yetkili', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Mail
   */
	'mail' => array(
		'name' => 'mail',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'E-Posta adresi', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Talep edilen miktar
   */
	'requested_quantity' => array(
		'name' => 'requested_quantity',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'Telep edilen miktar', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	),

	/*
   * Ip Adresi
   */
	'ip_addr' => array(
		'name' => 'ip_addr',
		'type' => 'textbox',
		'group' => 'primary',
		'label' => __( 'IP Adresi', 'ninja-forms' ),
		'placeholder' => '',
		'width' => 'full',
		'use_merge_tags' => array(
			'include' => array(
				'calcs',
			),
		)
	)

);
