<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-all-paddings no-background");
\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.buttons",
	"ui.icons",
	"ui.progressbar",
	"ui.sidepanel-content",
	"ui.sidepanel.layout",
	"seo.seoadbuilder",
	"loader",
]);

\Bitrix\Main\Page\Asset::getInstance()->addCss($this->GetFolder().'/postlist.css');
\Bitrix\Main\Page\Asset::getInstance()->addCss($this->GetFolder().'/configurator.css');
$accountId = $arParams['ACCOUNT_ID'];
$clientId = $arParams['CLIENT_ID'];
$type = $arParams['TYPE'];

?>

<div class="ui-sidepanel-layout-header" style="padding-left: 0">
	<div class="ui-sidepanel-layout-title"><?php echo Loc::getMessage('CRM_ADS_RTG_POST_LIST_TITLE')?></div>
</div>
<div class="crm-order-instagram-view crm-ads-new-campaign">
	<div class="crm-order-instagram-edit-block" style="display: none;">
		<div class="crm-order-instagram-view-list">
		</div>
	</div>
	<div class="crm-order-instagram-edit-block seo-ads-empty-post-list-block" style="display: none">
		<div class="crm-order-instagram-view-block">
			<div class="crm-order-instagram-view-block-empty-icon"></div>
			<div class="crm-order-instagram-view-block-subtitle"><?=Loc::getMessage('CRM_ADS_POST_LIST_EMPTY')?></div>
		</div>
	</div>
</div>

<script type="text/javascript">
	new BX.Seo.SeoPostSelector( {
		accountId: '<?=$accountId?>',
		clientId: '<?=$clientId?>',
		type:'<?=$type?>',
		signedParameters: <?=\Bitrix\Main\Web\Json::encode($this->getComponent()->getSignedParameters())?>
	});
</script>