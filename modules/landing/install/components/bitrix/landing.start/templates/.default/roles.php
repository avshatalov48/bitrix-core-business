<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.roles',
	'.default',
	array(
		'PAGE_URL_ROLE_EDIT' => $arParams['PAGE_URL_ROLE_EDIT'],
		'TYPE' => $arParams['TYPE']
	),
	$component
);?>

<?if (!$arResult['CHECK_FEATURE_PERM']):?>
	<script type="text/javascript">
		BX.ready(function()
		{
			var disableFunc = function(e)
			{
				var errorText = "<?= \CUtil::jsEscape(Loc::getMessage('LANDING_ROLES_UNAVAILABLE'));?>";
				if (typeof BX.Landing.PaymentAlertShow !== "undefined")
				{
					BX.Landing.PaymentAlertShow({
						message: errorText
					});
				}
				else
				{
					var msg = BX.Landing.UI.Tool.ActionDialog.getInstance();
					msg.show({
						content: errorText,
						confirm: 'OK',
						contentColor: 'grey'
					});
				}
				if (e)
				{
					e.preventDefault();
				}
			};
			BX.bind(
				BX("landing-rights-save"),
				"click",
				BX.delegate(disableFunc)
			);
			disableFunc();
		});
	</script>
<?endif;?>
