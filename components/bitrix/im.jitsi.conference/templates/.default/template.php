<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

\Bitrix\Main\Page\Asset::getInstance()->addJs($arResult['JITSI_EXTERNAL_API']);
?>

<div id="conference-root" class="im-jitsi-conference-root"></div>

<script>
	var domain = '<?= CUtil::JSEscape($arResult['JITSI_SERVER'])?>';
	var options = {
		roomName: '<?= CUtil::JSEscape($arResult['ALIAS']) ?>',
		subject: '<?= CUtil::JSEscape($arResult['CHAT_NAME']) ?>',
		parentNode: document.getElementById('conference-root'),
		userInfo: {
			email: '<?= CUtil::JSEscape($arResult['USER_EMAIL']) ?>',
			displayName: '<?= CUtil::JSEscape($arResult['USER_NAME']) ?>'
		},
		configOverwrite: {
			subject: '<?= CUtil::JSEscape($arResult['CHAT_NAME']) ?>',
			disableAudioLevels: true,
			apiLogLevels: ['error'],
			startWithAudioMuted: true,
			channelLastN: 5
		},
	};

	// creates iframe and starts conference
	var jitsiInstance = new JitsiMeetExternalAPI(domain, options);
	jitsiInstance.addListener('readyToClose', function()
	{
		setTimeout(function() {
			window.close();
		}, 1000)
	});

</script>