<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$mad = new CAdminMobileDetail;

if(is_array($arResult["TRANSACTS"]))
{
	foreach ($arResult["TRANSACTS"] as $key => $arTransact)
	{
		$stmp = MakeTimeStamp($arTransact["TRANSACT_DATE"], "DD.MM.YYYY HH:MI:SS");
		$dateInsert = date("d.m.Y", $stmp).' <div class="time_icon">'.date("H:i", $stmp).'</div>';

		if (array_key_exists($arTransact["DESCRIPTION"], $arResult["TYPES"]))
			$description = htmlspecialcharsbx($arResult["TYPES"][$arTransact["DESCRIPTION"]]);
		else
			$description = htmlspecialcharsbx($arTransact["DESCRIPTION"]);

		$arSection = array(
					"TITLE" => $dateInsert." ( ".$arTransact["AMOUNT_PREPARED"]." )",
					"ROWS" => array(
						array("TITLE" => GetMessage("SMOT_DATE").":", "VALUE" => $arTransact["TRANSACT_DATE"]),
						array("TITLE" => GetMessage("SMOT_USER").":", "VALUE" => CSaleMobileOrderUtils::GetFormatedUserName($arTransact["USER_ID"])),
						array("TITLE" => GetMessage("SMOT_SUMM").":", "VALUE" => $arTransact["AMOUNT_PREPARED"])
					));

		if(strlen($description) > 0)
			$arSection["ROWS"][] = array("TITLE" => GetMessage("SMOT_DESCRIPTION").":", "VALUE" => $description);

		if(strlen($arTransact["NOTES"]) > 0)
			$arSection["ROWS"][] = array("TITLE" => GetMessage("SMOT_COMMENTS").":", "VALUE" => htmlspecialcharsbx($arTransact["NOTES"]));

		$mad->addSection($arSection);
	}

	echo $mad->getHtml();
}
else
{
	echo GetMessage("SMOT_TRANS_EMPTY");
}

?>