<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arResult["FORUMS"] = (is_array($arResult["FORUMS"]) ? $arResult["FORUMS"] : array());
/********************************************************************
				/Input params
********************************************************************/
if (!function_exists("__SetForumStatGroups"))
{
	function __SetForumStatGroups(&$arGroups, $deep = 0)
	{
		$result = [
			"POSTS" => 0, 
			"TOPICS" => 0, 
			"LAST_MESSAGE_ID" => 0, 
			"URL" => [],
			"MODERATE" => [
				"POSTS" => 0, "TOPICS" => 0
			],
			"NewMessage" => "N"
		];
		if (!is_array($arGroups) || $deep > 50)
			return $result;

		if (is_set($arGroups, "FORUMS"))
		{
			foreach ($arGroups["FORUMS"] as $res)
			{
				$result["POSTS"] += $res["POSTS"];
				$result["TOPICS"] += $res["TOPICS"];
				if ($result["LAST_MESSAGE_ID"] < $res["LAST_MESSAGE_ID"]):
					$result["LAST_MESSAGE_ID"] = $res["LAST_MESSAGE_ID"];
					$result["~LAST_POSTER_NAME"] = $res["~LAST_POSTER_NAME"];
					$result["LAST_POSTER_NAME"] = $res["LAST_POSTER_NAME"];
					$result["LAST_POST_DATE"] = $res["LAST_POST_DATE"];
					$result["TITLE"] = $res["TITLE"];
					$result["~TITLE"] = $res["~TITLE"];
					$result["URL"] = $res["URL"];
				endif;
				if ($result["NewMessage"] !== "Y" && isset($res["NewMessage"]))
				{
					$result["NewMessage"] = $res["NewMessage"];
				}
				if ($res["PERMISSION"] >= "Q"):
					$result["MODERATE"]["POSTS"] += $res["MODERATE"]["POSTS"];
					$result["MODERATE"]["TOPICS"] += $res["MODERATE"]["TOPICS"];
				endif;
			}
		}
		
		if (is_set($arGroups, "GROUPS"))
		{
			$deep++;
			foreach ($arGroups["GROUPS"] as $key => $val)
			{
				$res = __SetForumStatGroups($arGroups["GROUPS"][$key], $deep);
				$result["POSTS"] += $res["POSTS"];
				$result["TOPICS"] += $res["TOPICS"];
				if ($result["LAST_MESSAGE_ID"] < $res["LAST_MESSAGE_ID"]):
					$result["LAST_MESSAGE_ID"] = $res["LAST_MESSAGE_ID"];
					$result["~LAST_POSTER_NAME"] = $res["~LAST_POSTER_NAME"];
					$result["LAST_POSTER_NAME"] = $res["LAST_POSTER_NAME"];
					$result["LAST_POST_DATE"] = $res["LAST_POST_DATE"];
					$result["TITLE"] = $res["TITLE"];
					$result["~TITLE"] = $res["~TITLE"];
					$result["URL"] = $res["URL"];
				endif;
				$result["MODERATE"]["POSTS"] += $res["MODERATE"]["POSTS"];
				$result["MODERATE"]["TOPICS"] += $res["MODERATE"]["TOPICS"];
				$result["NewMessage"] = ($result["NewMessage"] == "Y" ? "Y" : $res["NewMessage"]);
			}
		}
		foreach ($result as $key => $val):
			$arGroups[$key] = $val;
		endforeach;
		return $result;
	}
}
__SetForumStatGroups($arResult["FORUMS"], 0);

?>