<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.role_edit',
	'.default',
	array(
		'ROLE_EDIT' => $arResult['VARS']['role_edit'],
		'PAGE_URL_ROLES' => $arParams['PAGE_URL_ROLES'],
		'TYPE' => $arParams['TYPE']
	),
	$component
);?>

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
