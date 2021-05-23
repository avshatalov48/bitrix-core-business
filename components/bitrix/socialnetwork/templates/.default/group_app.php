<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "group_placement_".$arResult["VARIABLES"]["placement_id"];;

include("util_group_menu.php");
include("util_group_profile.php");

if (\Bitrix\Main\Loader::includeModule('rest'))
{
	\CJSCore::Init(array('applayout'));

	$placementHandlerList = \Bitrix\Rest\PlacementTable::getHandlersList('SONET_GROUP_DETAIL_TAB');

	$placement = false;

	if(is_array($placementHandlerList))
	{
		foreach($placementHandlerList as $placementHandler)
		{
			if ($placementHandler['ID'] == $arResult["VARIABLES"]["placement_id"])
			{
				$placement = $placementHandler;
				break;
			}
		}
	}

	if ($placement)
	{
		$placementSid = $APPLICATION->includeComponent(
			'bitrix:app.layout',
			'',
			array(
				'ID' => $placement['APP_ID'],
				'PLACEMENT' => 'SONET_GROUP_DETAIL_TAB',
				'PLACEMENT_ID' => $placement['ID'],
				"PLACEMENT_OPTIONS" => array(
					'GROUP_ID' => $arResult["VARIABLES"]["group_id"]
				),
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}
}

?>