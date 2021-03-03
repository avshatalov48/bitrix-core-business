<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');?>
<?php $APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	array(
		'POPUP_COMPONENT_NAME' => 'bitrix:app.placement',
		'POPUP_COMPONENT_TEMPLATE_NAME' => 'list',
		'POPUP_COMPONENT_PARAMS' => array(
			'COMPONENT_TEMPLATE' => '.default',
			'SEF_MODE' => 'Y',
			'IS_SLIDER' => 'Y',
			'PLACEMENT' => 'QUICK_VIEW',
			'SEF_FOLDER' => SITE_DIR . 'marketplace/view/quick/',
			'USE_PADDING' => 'N',
			'PARAM' => [
				'FRAME_WIDTH' => '100%',
				'FRAME_HEIGHT' => '200px',
			],
			'SAVE_LAST_APP' => 'Y',
			'SHOW_MARKET_EMPTY_COUNT' => 8
		),
		'USE_PADDING' => false,
		'PAGE_MODE' => false,
		'USE_UI_TOOLBAR' => 'N',
		'PLAIN_VIEW' => (\CRestUtil::isSlider() ? 'Y' : 'N')
	)
);?>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');?>