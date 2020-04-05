<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

CJSCore::init('im_web');

?>

<div id="im-conference-wrapper" style="height: 600px;"></div>

<script>
	BX.ready(function()
	{
		window.conferenceController = new BX.ImConferenceController({
			viewContainer: BX('im-conference-wrapper'),
			callFields: '<?= \Bitrix\Main\Web\Json::encode($arResult['CALL'])?>',
			callUsers: '<?= \Bitrix\Main\Web\Json::encode($arResult['CALL_USERS'])?>',
			publicIds: '<?= \Bitrix\Main\Web\Json::encode($arResult['PUBLIC_IDS'])?>',
		});
	});
</script>




