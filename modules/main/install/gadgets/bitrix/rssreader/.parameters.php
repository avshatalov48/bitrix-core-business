<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParameters = Array(
		"PARAMETERS"=> Array(
			"CACHE_TIME" => array(
				"NAME" => GetMessage("GD_RSS_READER_P_CACHE_TIME"),
				"TYPE" => "STRING",
				"DEFAULT" => "3600"
			),
			"SHOW_URL" => Array(
					"NAME" => GetMessage("GD_RSS_READER_P_SHOW_DETAIL"),
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "N",
			),
			"PREDEFINED_RSS" => Array(
				"NAME" => GetMessage("GD_RSS_READER_P_RSSS"),
				"TYPE" => "STRING",
				"ROWS" => 5,
				"MULTIPLE" => "N",
				"DEFAULT" => "",
				"REFRESH" => "Y",
			),
		),
		"USER_PARAMETERS" => Array(
			"CNT" => Array(
				"NAME" => GetMessage("GD_RSS_READER_P_CNT"),
				"TYPE" => "STRING",
				"MULTIPLE" => "N",
				"DEFAULT" => "10",
			),
		),
	);

if (
	is_array($arCurrentValues)
	&& !empty($arCurrentValues["PREDEFINED_RSS"])
	&& trim($arCurrentValues["PREDEFINED_RSS"]) != ""
)
{
	include_once(__DIR__.'/include.php');
	$arVTemp = preg_split("/[\\r\\n \\t]+/", $arCurrentValues["PREDEFINED_RSS"]);
	foreach($arVTemp as $v)
	{
		$v = trim($v);
		if ($v == '')
		{
			continue;
		}

		$rss = gdGetRss($v, intval($arCurrentValues["CACHE_TIME"]));

		if($rss && $rss->title)
		{
			$arV[$v] = $rss->title;
		}
		else
		{
			$arV[$v] = $v;
		}
	}

	$arParameters["USER_PARAMETERS"]["RSS_URL"] = array(
		"NAME" => GetMessage("GD_RSS_READER_P_RSS_LINK"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"VALUES" => $arV
	);
}
else
{
	$arParameters["USER_PARAMETERS"]["RSS_URL"] = array(
		"NAME" => GetMessage("GD_RSS_READER_P_RSS_LINK"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
	);
	
	$arParameters["USER_PARAMETERS"]["IS_HTML"] = array(
		"NAME" => GetMessage("GD_RSS_READER_P_IS_HTML"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "N",
	);
}

?>
