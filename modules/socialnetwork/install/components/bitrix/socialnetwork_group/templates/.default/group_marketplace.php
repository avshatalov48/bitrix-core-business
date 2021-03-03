<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "group_marketplace";
include("util_group_menu.php");
include("util_group_profile.php");

if (\Bitrix\Main\Loader::includeModule('rest'))
{
	CJSCore::Init(array('marketplace'));
	$APPLICATION->setTitle(Bitrix\Main\Localization\Loc::getMessage('GROUP_MARKETPLACE_TITLE_2'));

	?><script>
		BX.ready(function() {
			BX.rest.Marketplace.open({
				PLACEMENT: 'SONET_GROUP_DETAIL_TAB'
			});
		});
	</script><?
}
?>