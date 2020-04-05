<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load("ui.buttons");

$serviceUrl = \CUtil::JSEscape(\CBitrix24::PATH_COUNTER);
$popupId = 'log_entry_stub_'.intval($arParams['EVENT']['ID']);

?>
<div class="feed-post-stub-block">
	<div class="feed-post-stub-icon feed-post-stub-icon-<?=str_replace('_', '-', $arResult['EVENT_ID'])?>"></div>
	<div class="feed-post-stub-message"><?=$arResult['MESSAGE'];?></div>
	<div class="feed-post-stub-more"><a href="https://helpdesk.bitrix24.ru/open/7258193/" onclick="if(top.BX.Helper) { top.BX.Helper.show("redirect=detail&code=7258193"); event.preventDefault(); }"><?=Loc::getMessage('SLEB_TEMPLATE_MORE')?></a></div>
</div>
<div class="feed-post-stub-buttons">
	<a href="javascript:void(0)" onclick="BX.SocialnetworkLogEntryStub.items['<?=$popupId?>'].execute();" class="ui-btn ui-btn-md ui-btn-primary"><?=Loc::getMessage('SLEB_TEMPLATE_BUTTON');?></a>
</div>
<script>
	BX.ready(function() {
		BX.SocialnetworkLogEntryStub.create(
			'<?=$popupId?>',
			{
				redirectUrl: '<?=\CUtil::JSEscape(\CBitrix24::PATH_LICENSE_ALL)?>',
				serviceUrl: '<?=\CUtil::JSEscape(\CBitrix24::PATH_COUNTER)?>',
				host: '<?=\CUtil::JSEscape(BX24_HOST_NAME)?>'
			}
		);
	});
</script>
