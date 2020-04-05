<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!function_exists("__array_stretch"))
{
	function __array_stretch($arGroup, $depth = 0)
	{
		$arResult = array();
		
		if (intVal($arGroup["ID"]) > 0)
		{
			$arResult["GROUP_".$arGroup["ID"]] = $arGroup;
			unset($arResult["GROUP_".$arGroup["ID"]]["GROUPS"]);
			unset($arResult["GROUP_".$arGroup["ID"]]["FORUM"]);
			$arResult["GROUP_".$arGroup["ID"]]["DEPTH"] = $depth; 
			$arResult["GROUP_".$arGroup["ID"]]["TYPE"] = "GROUP"; 
		}
		if (array_key_exists("FORUMS", $arGroup))
		{
			foreach ($arGroup["FORUMS"] as $res)
			{
				$arResult["FORUM_".$res["ID"]] = $res; 
				$arResult["FORUM_".$res["ID"]]["DEPTH"] = $depth; 
				$arResult["FORUM_".$res["ID"]]["TYPE"] = "FORUM"; 
			}
		}
				
		if (array_key_exists("GROUPS", $arGroup))
		{
			$depth++;
			foreach ($arGroup["GROUPS"] as $key => $val)
			{
				$res = __array_stretch($arGroup["GROUPS"][$key], $depth);
				$arResult = array_merge($arResult, $res);
			}
		}
		return $arResult;
	}
}
$arResult["GROUPS_FORUMS"] = __array_stretch($arResult["GROUPS_FORUMS"]);
?>