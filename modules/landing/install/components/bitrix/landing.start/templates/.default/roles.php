<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */
/** @var \CBitrixComponent $component */

$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:landing.roles',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '.default',
		'POPUP_COMPONENT_PARAMS' => [
			'PAGE_URL_ROLE_EDIT' => $arParams['PAGE_URL_ROLE_EDIT'],
			'TYPE' => $arParams['TYPE'],
		],
		'POPUP_COMPONENT_PARENT' =>$component,
		'USE_PADDING' => true,
		'USE_UI_TOOLBAR' => 'Y',
		'PAGE_MODE' => true,
	]
);
?>

<?if (!$arResult['CHECK_FEATURE_PERM']):?>
	<script>
		BX.ready(function()
		{
			var disableFunc = function(e)
			{
				<?= \Bitrix\Landing\Restriction\Manager::getActionCode('limit_sites_access_permissions');?>
				if (e)
				{
					e.preventDefault();
				}
			};
			BX.bind(
				BX('landing-rights-save'),
				'click',
				BX.delegate(disableFunc)
			);
			setTimeout(function() {
				disableFunc();
			}, 0);
		});
	</script>
<?endif;?>
