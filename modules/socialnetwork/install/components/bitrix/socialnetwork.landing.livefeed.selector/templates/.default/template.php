<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

$APPLICATION->SetTitle(Loc::getMessage("SLLS_TEMPLATE_PAGE_TITLE"));

\Bitrix\Main\UI\Extension::load(["ui.buttons", "ui.buttons.icons", "sidepanel"]);

?><script>
	BX.message({
		'SITE_ID' : '<?=\CUtil::jsEscape($arParams['SITE_ID'])?>'
	});
	BX.ready(function() {
		BX.SocialnetworkLandingLivefeedSelector.create('<?=\CUtil::jsEscape($arResult["FILTER_ID"])?>', {
			filterValue: <?=(!empty($arResult['FILTER_INIT_VALUE']) ? \CUtil::phpToJSObject($arResult['FILTER_INIT_VALUE']) : [])?>,
			urlToGroupCreate: '<?=\CUtil::jsEscape($arResult["URL_GROUP_CREATE"])?>'
		});
	});
</script><?

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");

Toolbar::addFilter([
	'FILTER_ID' => $arResult["FILTER_ID"],
	'FILTER' => $arResult["FILTER"],
	'FILTER_FIELDS' => [],
	'FILTER_PRESETS' => $arResult["FILTER_PRESETS"],
	'DISABLE_SEARCH' => true,
	'ENABLE_LIVE_SEARCH' => false,
	'ENABLE_LABEL' => true,
	'RESET_TO_DEFAULT_MODE' => false,
	'CONFIG' => array(
		'AUTOFOCUS' => false
	)
]);


if (!empty($arResult["URL_GROUP_CREATE"]))
{
	$menuButton = new \Bitrix\UI\Buttons\Button([
		"color" => \Bitrix\UI\Buttons\Color::PRIMARY,
		"icon" => \Bitrix\UI\Buttons\Icon::ADD,
		"click" => new \Bitrix\UI\Buttons\JsHandler(
			"BX.SocialnetworkLandingLivefeedSelector.createWorkgroup",
			"BX.SocialnetworkLandingLivefeedSelector.Instance"
		),
		"text" => Loc::getMessage('SLLS_TEMPLATE_CREATE_GROUP_BUTTON')
	]);
	$menuButton->addAttribute('id', $buttonID);
	Toolbar::addButton($menuButton);
}

Toolbar::deleteFavoriteStar();

if (
	!empty($arResult['EMPTY_NOWORKGROUPS'])
	&& $arResult['EMPTY_NOWORKGROUPS'] == 'Y'
)
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."landing-livefeed-selector-wrapper-empty");

	?><div class="landing-livefeed-selector-content landing-livefeed-selector-content-empty">
		<div class="landing-livefeed-selector-content-message"><?=Loc::getMessage("SLLS_TEMPLATE_NO_GROUPS")?></div>
		<?
		if (!empty($arResult["URL_GROUP_CREATE"]))
		{
			?>
			<div class="landing-livefeed-selector-content-control">
				<a class="ui-btn ui-btn-md ui-btn-primary ui-btn-icon-add" id="slls_group_create"><?=Loc::getMessage("SLLS_TEMPLATE_CREATE_GROUP")?></a>
			</div>
			<?
		}
		?>
	</div>
	<script>
		BX.ready(function () {
			BX.addCustomEvent("BX.Livefeed.Filter:apply", function(filterValues, filterPromise, filterParams) {
				BX.SidePanel.Instance.getSliderByWindow(window).reload();
			});
		});
	</script><?
}
else
{
	$componentParams = [
		"MODE" => (!empty($arResult["LIVEFEED_MODE"]) ? $arResult["LIVEFEED_MODE"] : "LANDING"),
		"EMPTY_EXPLICIT" => (!empty($arResult["EMPTY_EXPLICIT"]) ? $arResult["EMPTY_EXPLICIT"] : 'N'),
		'FILTER_ID' => $arResult["FILTER_ID"],
		"PAGE_SIZE" => "10",
		"NAME_TEMPLATE" => CSite::GetNameFormat(),
		"SHOW_LOGIN" => "Y",
		"DATE_TIME_FORMAT" => $arResult['CURRENT_DATETIME_FORMAT'], // d.m.Y H:i:s
		"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arResult['CURRENT_DATETIME_FORMAT_WOYEAR'], // d.m H:i:s
		"SHOW_YEAR" => "M",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"SHOW_EVENT_ID_FILTER" => "Y",
		"SHOW_SETTINGS_LINK" => "Y",
		"SET_LOG_CACHE" => "Y",
		"USE_COMMENTS" => "Y",
		"BLOG_ALLOW_POST_CODE" => "Y",
		"BLOG_GROUP_ID" => "1",
		"PHOTO_USER_IBLOCK_TYPE" => "photos",
		"PHOTO_USER_IBLOCK_ID" => "16",
		"PHOTO_USE_COMMENTS" => "Y",
		"PHOTO_COMMENTS_TYPE" => "FORUM",
		"PHOTO_FORUM_ID" => "2",
		"PHOTO_USE_CAPTCHA" => "N",
		"FORUM_ID" => "3",
		"PAGER_DESC_NUMBERING" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_SHADOW" => "N",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"NEW_TEMPLATE" => "Y",
		"AVATAR_SIZE" => 50,
		"AVATAR_SIZE_COMMENT" => 39,
		"AUTH" => "Y",
		"ORDER" => [
			"LOG_DATE" => "DESC"
		],
	];

	if (!empty($arResult['FILTER_VALUE']))
	{
		$componentParams['DESTINATION'] = $arResult['FILTER_VALUE'];
	}
	if (
		!empty($arResult['FILTER_AUTHOR_VALUE'])
		&& !is_array($arResult['FILTER_AUTHOR_VALUE'])
		&& preg_match('/^U(\d+)$/', $arResult['FILTER_AUTHOR_VALUE'], $matches)
	)
	{
		$componentParams['DESTINATION_AUTHOR_ID'] = $matches[1];
	}

	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.log.ex",
		"",
		$componentParams
	);
}

$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
	'BUTTONS' => [
		['TYPE' => 'save'],
		['TYPE' => 'cancel']
	]
]);

