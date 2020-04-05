<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '230',
	'code' => 'wiki/detail',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_DETAIL-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_DETAIL-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DETAIL-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DETAIL-TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_DETAIL-DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DETAIL-TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_DETAIL-DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki/detail/preview.jpg',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
		],
	],
	'layout' => [],
	'items' => [
		'#block3448' => [
			'old_id' => 3448,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: bold;">Detail page</span><br />',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-38 container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-20 g-pb-auto text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3437' => [
			'old_id' => 3437,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel. </p>
						<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel. </p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1 container g-pa-0 g-max-width-container text-left g-font-open-sans g-color-black-opacity-0_8',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-15 g-pt-30 g-pl-7 g-pr-15',
				],
			],
		],
		'#block3436' => [
			'old_id' => 3436,
			'code' => '32.2.img_one_big',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-img' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1445x750/img1.png',
						'url' => '{"text":"","href":"https://www.bitrix24.com/","target":"_blank","enabled":true}',
					],
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-10',
				],
			],
		],
		'#block3438' => [
			'old_id' => 3438,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.
						In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt scelerisque non et erat.<br /><br />Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus.
						Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. <br /><br />Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi.
						Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. <br /><br />Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt scelerisque non et erat.
						Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Maecenas ut mauris risus.<br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1 container g-pa-0 g-max-width-container text-left g-font-open-sans g-color-black-opacity-0_8',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-30 g-pb-20 g-pl-7 g-pr-15',
				],
			],
		],
		'#block3446' => [
			'old_id' => 3446,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">The subtitle of the article</span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-30 container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-20 g-pb-auto text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3441' => [
			'old_id' => 3441,
			'code' => '31.4.two_cols_img_text_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => ' ',
				],
				'.landing-block-node-text' => [
					0 => '<p>Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed
						tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam
						et massa eros. Maecenas sit amet lacinia lectus.</p>
						<p>Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio.</p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black-opacity-0_8 g-font-size-16 g-font-open-sans',
				],
				'.landing-block-node-block' => [
					0 => 'row landing-block-node-block align-items-center',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-25 g-pb-5',
				],
			],
		],
		'#block3443' => [
			'old_id' => 3443,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt scelerisque non et erat.<br /><br />
						Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet.<br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1 container g-pa-0 g-max-width-container text-left g-font-open-sans g-color-black-opacity-0_8',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-15 g-pt-30 g-pl-7 g-pr-15',
				],
			],
		],
		'#block3442' => [
			'old_id' => 3442,
			'code' => '49.3.two_cols_video_text_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => ' ',
				],
				'.landing-block-node-text' => [
					0 => '<p>Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed
							tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam
							et massa eros. Maecenas sit amet lacinia lectus.</p>
						<p>	Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu
							malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna.</p>',
				],
				'.landing-block-node-video' => [
					0 => [
						'src' => '//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=0&controls=1&loop=1&mute=0&rel=0',
						'data-source' => 'https://www.youtube.com/watch?v=q4d8g9Dn3ww',
					],
				],
			],
			'style' => [
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container d-flex js-animation slideInLeft col-md-6 col-lg-6 g-pb-20 g-pb-0--md align-items-center',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black-opacity-0_8 g-font-size-16 g-font-open-sans',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-25 g-pb-5',
				],
			],
		],
		'#block3440' => [
			'old_id' => 3440,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi.
						Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. <br /><br />
						Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt scelerisque non et erat.
						Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Maecenas ut mauris risus. <br /><br />
						Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
						Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.<br /><br />
						Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.<br /><br />
						Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.<br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1 container g-pa-0 g-max-width-container text-left g-font-open-sans g-color-black-opacity-0_8',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-30 g-pb-20 g-pl-7 g-pr-15',
				],
			],
		],
	],
];
