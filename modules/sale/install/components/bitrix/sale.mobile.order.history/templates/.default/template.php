<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(is_array($arResult["HISTORY"]) && !empty($arResult["HISTORY"]))
{
	$mad = new CAdminMobileDetail;

	foreach ($arResult["HISTORY"] as $arItemHistory)
	{
		$stmp = MakeTimeStamp($arItemHistory["DATE_CREATE"], "DD.MM.YYYY HH:MI:SS");
		$dateInsert = date("d.m.Y", $stmp).' <div class="time_icon">'.date("H:i", $stmp).'</div>';

		$arSection = array(
					"TITLE" => $dateInsert,
					"ROWS" => array(
						array(
							"TITLE" => GetMessage("SMOH_USER").":",
							"VALUE" => htmlspecialcharsbx($arItemHistory['USER']['LOGIN'])),
						array(
							"TITLE" => GetMessage("SMOH_FIO").":",
							"VALUE" => htmlspecialcharsbx($arItemHistory['USER']['NAME']." ".$arItemHistory['USER']['LAST_NAME'])),
						array(
							"TITLE" => GetMessage("SMOH_OPERATION").":",
							"VALUE" => $arItemHistory["NAME"])
						)
					);

		if ($arItemHistory["INFO"] <> '')
		{
			$arSection["ROWS"][] = array(
				"TITLE" => GetMessage("SMOH_DESCRIPTION").":",
				"VALUE" => htmlspecialcharsbx($arItemHistory["INFO"]));
		}

		$mad->addSection($arSection);
	}

	echo $mad->getHtml();
}
else
{
	echo GetMessage("SMOH_HISTORY_EMPTY");
}
?>
