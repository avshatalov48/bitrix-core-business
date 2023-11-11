<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load(['im.public', 'translit']);
?>
<div class="bx-messenger-box-hello-wrap">
	<div class="bx-messenger-box-hello"><?=GetMessage('IM_MESSENGER_EMPTY_PAGE');?></div>
</div>
<script type="text/javascript">
BX.Messenger.Public.disableDesktopRedirect();

<?if (
	!isset($_GET['IM_SETTINGS'])
	&& !isset($_GET['IM_HISTORY'])
	&& !isset($_GET['IM_NOTIFY'])
	&& !isset($_GET['IM_DIALOG'])
	&& !isset($_GET['IM_LINES'])
):?>
	BX.addCustomEvent('onImInitBefore', function(im){
		im.fullScreen = true;
	});
	BX.addCustomEvent('onImInit', function(im){
		im.messenger.openMessenger();
	});

	<?if (isset($arResult['MESSENGER_V2']) && $arResult['MESSENGER_V2'] === 'Y'):?>
		BX.Messenger.Public.openChat();
	<?endif;?>
<?endif;?>
</script>