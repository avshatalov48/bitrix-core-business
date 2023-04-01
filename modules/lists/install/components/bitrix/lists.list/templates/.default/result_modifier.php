<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!empty($arResult["ELEMENTS_ROWS"]))
{
	foreach ($arResult["ELEMENTS_ROWS"] as $key => &$row)
	{
		if (!empty($row["actions"]))
		{
			foreach($row["actions"] as &$action)
			{
				if (isset($action['ID']) && $action["ID"] == "delete")
				{
					$action["ONCLICK"] = "javascript:BX.Lists['".$arResult['JS_OBJECT']."'].deleteElement('".
						$arResult["GRID_ID"]."', '".$row["id"]."')";
				}
			}
		}
	}
}