<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'name' => 'Thanksgiving Day',
	'description' => "It's the day to be thankful. It's the time to add more colors to the palette of your page.
Send your Thanksgiving wishes with special Bitrix24 Thanksgiving Template. Your clients will enjoy it!",
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'page',
	'version' => 2,
	'active' => \LandingSiteDemoComponent::checkActive(array(
		'ONLY_IN' => array('en'),
	)),
	'fields' => array(
		'TITLE' => 'Thanksgiving Day',
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'real-estate',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'SETTINGS_HIDE_NOT_AVAILABLE' => 'L',
			'SETTINGS_HIDE_NOT_AVAILABLE_OFFERS' => 'N',
			'SETTINGS_PRODUCT_SUBSCRIPTION' => 'Y',
			'SETTINGS_USE_PRODUCT_QUANTITY' => 'Y',
			'SETTINGS_DISPLAY_COMPARE' => 'Y',
			'SETTINGS_PRICE_CODE' => array(
				0 => 'BASE',
			),
			'SETTINGS_USE_PRICE_COUNT' => 'N',
			'SETTINGS_SHOW_PRICE_COUNT' => 1,
			'SETTINGS_PRICE_VAT_INCLUDE' => 'Y',
			'SETTINGS_SHOW_OLD_PRICE' => 'Y',
			'SETTINGS_SHOW_DISCOUNT_PERCENT' => 'Y',
			'SETTINGS_USE_ENHANCED_ECOMMERCE' => 'Y',
			'METAOG_TITLE' => 'Thanksgiving Day',
			'METAOG_DESCRIPTION' => "It's the day to be thankful. It's the time to add more colors to the palette of your page.
Send your Thanksgiving wishes with special Bitrix24 Thanksgiving Template. Your clients will enjoy it!",
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.thanksgiving/preview.jpg',
			'GTM_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => 'Thanksgiving Day',
			'METAMAIN_DESCRIPTION' => "It's the day to be thankful. It's the time to add more colors to the palette of your page.
Send your Thanksgiving wishes with special Bitrix24 Thanksgiving Template. Your clients will enjoy it!",
			'HEADBLOCK_USE' => 'N',
		),
	),
	'layout' => array(),
	'items' => array(
		'#block8707' => array(
			'old_id' => 8707,
			'code' => '01.big_with_text_3',
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img9.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-title' => array(
					0 => 'Happy<br /> Thanksgiving <br />Day!',
				),
				'.landing-block-node-text' => array(
					0 => 'Morbi a suscipit ipsum. Suspendisse mollis libero ante.
			Pellentesque finibus convallis nulla vel placerat.<br />Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo.',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#form',
						'target' => null,
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'View more',
					),
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container container g-max-width-800 js-animation fadeInDown text-center u-bg-overlay__inner g-mx-1',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-color-white g-mb-20 g-text-transform-none g-font-size-86 g-line-height-1_2',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white-opacity-0_7 g-mb-35',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80',
				),
			),
		),
		'#block8718' => array(
			'old_id' => 8718,
			'code' => '47.1.title_with_icon',
			'cards' => array(
				'.landing-block-node-icon-element' => 5,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-style: normal;">Thanksgiving cakes</span>',
				),
				'.landing-block-node-icon' => array(
					0 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					3 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					4 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-text' => array(
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-font-size-75',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp g-color-gray-dark-v5 mb-0 g-pb-1',
				),
				'.landing-block-node-icon-element@0' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-8',
				),
				'.landing-block-node-icon-element@1' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@2' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-14',
				),
				'.landing-block-node-icon-element@3' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@4' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-8',
				),
				
				'#wrapper' => array(
					0 => 'landing-block g-pt-80 g-pb-80',
				),
			),
		),
		'#block8704' => array(
			'old_id' => 8704,
			'code' => '43.1.big_tiles_with_slider',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-img1' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/810x524/img1.jpg',
					),
				),
				'.landing-block-node-subtitle' => array(
					0 => 'Choose your cake',
				),
				'.landing-block-node-title' => array(
					0 => 'Perfect cakes for whole family',
				),
				'.landing-block-node-text' => array(
					0 => '<p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim
							justo, rhoncus ut, imperdiet a, venenatis vitae, justo.</p>',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#bestwishes',
						'target' => null,
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'View more',
					),
				),
				'.landing-block-node-img2' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x625/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-card-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x625/img2.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x625/img2.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-block-top' => array(
					0 => 'landing-block-node-block-top js-animation fadeInRight col-md-6 d-flex align-items-center text-center g-pa-50',
				),
				'.landing-block-node-block-bottom@0' => array(
					0 => 'col-md-6 landing-block-node-block-bottom js-animation fadeInLeft d-flex align-items-center g-max-height-300--md g-max-height-625--lg text-center g-overflow-hidden',
				),
				'.landing-block-node-block-bottom@1' => array(
					0 => 'landing-block-node-block-bottom col-md-6 js-animation fadeInLeft',
				),
				'.landing-block-node-img1' => array(
					0 => 'landing-block-node-img1 col-md-6 g-bg-img-hero g-min-height-400 js-animation fadeInLeft',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-600 mb-0 g-text-transform-none g-font-size-30',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-20 g-color-primary g-mb-25',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-mb-35',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-py-10 g-rounded-50',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block',
				),
			),
		),
		'#block8719' => array(
			'old_id' => 8719,
			'code' => '47.1.title_with_icon',
			'cards' => array(
				'.landing-block-node-icon-element' => 5,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-style: normal;">Happy family day</span><br />',
				),
				'.landing-block-node-icon' => array(
					0 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					3 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					4 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-text' => array(
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-font-size-75 g-color-white',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp mb-0 g-pb-1 g-color-white-opacity-0_8',
				),
				'.landing-block-node-icon-element@0' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-white d-inline g-font-size-8',
				),
				'.landing-block-node-icon-element@1' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-white d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@2' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-white d-inline g-font-size-14',
				),
				'.landing-block-node-icon-element@3' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-white d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@4' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-white d-inline g-font-size-8',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-80 g-pb-80 g-bg-primary',
				),
			),
		),
		'#block8705' => array(
			'old_id' => 8705,
			'code' => '31.1.two_cols_text_img',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => 'Family day',
				),
				'.landing-block-node-text' => array(
					0 => '<p>Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus.<br /><br />Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi.<br /></p>',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#bestwishes',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'VIEW MORE',
					),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x600/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-mb-25 js-animation fadeInUp g-color-white g-font-size-30 g-text-transform-none',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-mb-30 js-animation fadeInUp g-color-white-opacity-0_8',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button text-uppercase btn g-btn-type-solid g-btn-size-md g-btn-px-m js-animation fadeInUp g-btn-primary g-color-white g-rounded-50',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block g-bg-black',
				),
			),
		),
		'#block8720' => array(
			'old_id' => 8720,
			'code' => '47.1.title_with_icon',
			'cards' => array(
				'.landing-block-node-icon-element' => 5,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-style: normal;">Choose your one</span><br />',
				),
				'.landing-block-node-icon' => array(
					0 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					3 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					4 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-text' => array(
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-font-size-75',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp g-color-gray-dark-v5 mb-0 g-pb-1',
				),
				'.landing-block-node-icon-element@0' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-8',
				),
				'.landing-block-node-icon-element@1' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@2' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-14',
				),
				'.landing-block-node-icon-element@3' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@4' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-8',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-80 g-pb-80',
				),
			),
		),
		'#block8706' => array(
			'old_id' => 8706,
			'code' => '32.6.img_grid_4cols_1_no_gutters',
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/534x534/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/534x534/img2.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/534x534/img3.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					3 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/534x534/img4.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img-container-leftleft' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-leftleft js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover',
				),
				'.landing-block-node-img-container-left' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover',
				),
				'.landing-block-node-img-container-right' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInDown h-100 g-pos-rel g-parent u-block-hover',
				),
				'.landing-block-node-img-container-rightright' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-rightright js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-0 g-pb-0',
				),
			),
		),
		'#block8713' => array(
			'old_id' => 8713,
			'anchor' => 'bestwishes',
			'code' => '01.big_with_text_3',
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img10.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-title' => array(
					0 => 'Best wishes on you. <br />Happy Thanksgiving!',
				),
				'.landing-block-node-text' => array(
					0 => 'Morbi a suscipit ipsum. Suspendisse mollis libero ante.
			Pellentesque finibus convallis nulla vel placerat.<br />Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo.',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#form',
						'target' => null,
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'View more',
					),
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container container g-max-width-800 js-animation fadeInDown text-center u-bg-overlay__inner g-mx-1',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-line-height-1 g-font-weight-700 g-color-white g-mb-20 g-text-transform-none g-font-size-60',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white-opacity-0_7 g-mb-35',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80',
				),
			),
		),
		'#block8714' => array(
			'old_id' => 8714,
			'code' => '47.1.title_with_icon',
			'cards' => array(
				'.landing-block-node-icon-element' => 5,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-style: normal;">Make your order</span>',
				),
				'.landing-block-node-icon' => array(
					0 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
					),
					1 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
					),
					2 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
					),
					3 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
					),
					4 => array(
						'classList' => array(
							0 => 'landing-block-node-icon fa fa-star',
						),
					),
				),
				'.landing-block-node-text' => array(
					0 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title font-italic g-font-weight-600 g-mb-20 g-font-size-50',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp g-color-gray-dark-v5 mb-0 g-pb-1',
				),
				'.landing-block-node-icon-element@0' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-8',
				),
				'.landing-block-node-icon-element@1' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@2' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-14',
				),
				'.landing-block-node-icon-element@3' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-11',
				),
				'.landing-block-node-icon-element@4' => array(
					0 => 'landing-block-node-icon-element d-inline g-color-primary d-inline g-font-size-8',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-80 g-pb-0',
				),
			),
		),
		'#block8709' => array(
			'old_id' => 8709,
			'code' => '51.1.countdown_01',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => 'Hurry up! You have not a lot of time.',
				),
				'.landing-block-node-number-text-days' => array(
					0 => 'Days',
					1 => 'Hours',
					2 => 'Minutes',
					3 => 'Seconds',
				),
				'.landing-block-node-number-text-hours' => array(),
				'.landing-block-node-number-text-minutes' => array(),
				'.landing-block-node-number-text-seconds' => array(),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-300 g-font-size-16',
				),
				'.landing-block-node-number-number' => array(
					0 => 'landing-block-node-number-number landing-block-node-number-number-days g-color-black g-font-size-25 g-font-size-36--sm mb-0',
					1 => 'landing-block-node-number-number landing-block-node-number-number-days g-color-black g-font-size-25 g-font-size-36--sm mb-0',
					2 => 'landing-block-node-number-number landing-block-node-number-number-days g-color-black g-font-size-25 g-font-size-36--sm mb-0',
					3 => 'landing-block-node-number-number landing-block-node-number-number-days g-color-black g-font-size-25 g-font-size-36--sm mb-0',
				),
				'.landing-block-node-number-text' => array(
					0 => 'landing-block-node-number-text landing-block-node-number-text-days g-color-black g-font-size-10 g-font-size-12--sm',
					1 => 'landing-block-node-number-text landing-block-node-number-text-days g-color-black g-font-size-10 g-font-size-12--sm',
					2 => 'landing-block-node-number-text landing-block-node-number-text-days g-color-black g-font-size-10 g-font-size-12--sm',
					3 => 'landing-block-node-number-text landing-block-node-number-text-days g-color-black g-font-size-10 g-font-size-12--sm',
				),
				'.landing-block-node-number-delimiter@0' => array(
					0 => 'landing-block-node-number-delimiter u-countdown--days-hide d-inline-block align-top g-font-size-25 g-font-size-36--sm',
				),
				'.landing-block-node-number-delimiter@1' => array(
					0 => 'landing-block-node-number-delimiter d-inline-block align-top g-font-size-25 g-font-size-36--sm',
				),
				'.landing-block-node-number-delimiter@2' => array(
					0 => 'landing-block-node-number-delimiter d-inline-block align-top g-font-size-25 g-font-size-36--sm',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-70 g-pt-0',
				),
			),
			'attrs' => array(
				'.landing-block-node-date' => array(
					0 => array(
						'data-end-date' => '1542837600000',
					),
				),
			),
		),
		'#block8710' => array(
			'old_id' => 8710,
			'anchor' => 'form',
			'code' => '33.3.form_1_transparent_black_no_text',
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x474/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pos-rel g-bg-primary-dark-v1 g-pt-120 g-pb-120 landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after',
				),
			),
		),
	),
);