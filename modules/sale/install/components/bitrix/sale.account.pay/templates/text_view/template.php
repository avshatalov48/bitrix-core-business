<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if (!empty($arResult["errorMessage"]))
{
	if (!is_array($arResult["errorMessage"]))
	{
		ShowError($arResult["errorMessage"]);
	}
	else
	{
		foreach ($arResult["errorMessage"] as $errorMessage)
		{
			ShowError($errorMessage);
		}
	}
}
else
{
	if ($arParams['REFRESHED_COMPONENT_MODE'] === 'Y')
	{
		ShowError(Loc::getMessage("NEW_COMPONENT_TEMPLATE_ERROR"));
	}
	else
	{
		?>
		<h3><?=Loc::getMessage("SAP_BUY_MONEY")?></h3>
		<?
		$adit="";
		if(strlen($arResult["CURRENT_PAGE"]) > 0)
			$adit = "&CURRENT_PAGE=".$arResult["CURRENT_PAGE"];
		foreach($arResult["AMOUNT_TO_SHOW"] as $value)
		{

			?><a href="<?=$value["LINK"].$adit?>" title="<?=Loc::getMessage("SAP_LINK_TITLE")." ".$value["NAME"]?>"><?=$value["NAME"]?></a><br /><?
		}
	}
}
?>