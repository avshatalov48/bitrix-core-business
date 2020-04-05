<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'news-detail',
	'section' => ['dynamic', 'dynamic:detail', 'dynamic:socialnetwork:livefeed'],
	'name' => Loc::getMessage('LANDING_DEMO_NEWS_DETAIL-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_NEWS_DETAIL-DESC'),
	'publication' => true,
	'version' => 3,
	'fields' => array(
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_NEWS_DETAIL-NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_NEWS_DETAIL-DESC'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/news-detail/preview.jpg',
			'VIEW_USE' => 'N',
			'THEME_CODE' => '2business',
			'THEME_CODE_TYPO' => 'app',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
		),
	),
	'layout' => array(
	),
	
	'items' => array(
		'0' => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' =>
				array(
					'.landing-block-node-title' =>
						array(
							0 => 'News in details',
						),
					'.landing-block-node-text' =>
						array(
							0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel. <br /><br />Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
						),
				),
			'style' =>
				array(
					'.landing-block-node-title' =>
						array(
							0 => 'landing-block-node-title h2 g-mb-40 text-left g-font-montserrat g-font-size-40',
						),
					'.landing-block-node-text' =>
						array(
							0 => 'landing-block-node-text g-font-size-16 g-pb-1 text-left g-font-open-sans',
						),
					'.landing-block-node-text-container' =>
						array(
							0 => 'landing-block-node-text-container container g-max-width-container',
						),
					'#wrapper' =>
						array(
							0 => 'landing-block js-animation g-pt-40 g-pb-40 g-pl-0 g-pr-0 animation-none',
						),
				),
			'dynamic' =>
				array(
					'wrapper' =>
						array(
							'settings' =>
								array(
									'source' => 'socialnetwork:livefeed',
								),
							'references' =>
								array(
									'.landing-block-node-title@0' =>
										array(
											'id' => 'TITLE',
										),
									'.landing-block-node-text@0' =>
										array(
											'id' => 'DETAIL_TEXT',
										),
								),
							'source' => 'socialnetwork:livefeed',
							'filterId' => 0,
						),
				),
		),
	),
);