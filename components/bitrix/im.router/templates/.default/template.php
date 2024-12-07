<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['im.public', 'translit', 'ui.design-tokens']);
?>
<div class="bx-im-online-page__container bx-im-online-page__scope">
	<div class="bx-im-online-page__logo"></div>
	<div class="bx-im-online-page__title"><?=GetMessage('IM_MESSENGER_EMPTY_PAGE_TITLE')?></div>
	<div class="bx-im-online-page__description"><?=GetMessage('IM_MESSENGER_EMPTY_PAGE_DESCRIPTION')?></div>
	<button class="bx-im-online-page__button"><?=GetMessage('IM_MESSENGER_EMPTY_PAGE_BUTTON_TITLE')?></button>
</div>
<script>
BX.Messenger.Public.disableDesktopRedirect();

const button = document.querySelector('.bx-im-online-page__button');
if (button)
{
	button.addEventListener('click', function() {
		BX.Messenger.Public.openChat();
	});
}

<?if (
	!isset($_GET['IM_SETTINGS'])
	&& !isset($_GET['IM_HISTORY'])
	&& !isset($_GET['IM_NOTIFY'])
	&& !isset($_GET['IM_DIALOG'])
	&& !isset($_GET['IM_LINES'])
	&& !isset($_GET['IM_COPILOT'])
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