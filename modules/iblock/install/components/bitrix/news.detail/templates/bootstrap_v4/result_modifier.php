<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */

/*TAGS*/
$arResult["FIELDS"]["TAGS"] ??= '';
if ($arParams["SEARCH_PAGE"])
{
	if ($arResult["FIELDS"] && isset($arResult["FIELDS"]["TAGS"]))
	{
		$tags = array();
		foreach (explode(",", $arResult["FIELDS"]["TAGS"]) as $tag)
		{
			$tag = trim($tag, " \t\n\r");
			if ($tag)
			{
				$url = CHTTP::urlAddParams(
					$arParams["SEARCH_PAGE"],
					array(
						"tags" => $tag,
					),
					array(
						"encode" => true,
					)
				);
				$tags[] = '<a href="'.$url.'">'.$tag.'</a>';
			}
		}
		$arResult["FIELDS"]["TAGS"] = implode(", ", $tags);
	}
}
/*VIDEO & AUDIO*/
$arResult['VIDEO'] = '';
$arResult['SOUND_CLOUD'] = '';
$mediaProperty = trim($arParams["MEDIA_PROPERTY"]);
if ($mediaProperty)
{
	if (is_numeric($mediaProperty))
	{
		$propertyFilter = array(
			"ID" => $mediaProperty,
		);
	}
	else
	{
		$propertyFilter = array(
			"CODE" => $mediaProperty,
		);
	}

	$elementIndex = array();
	$elementIndex[$arResult["ID"]] = array(
		"PROPERTIES" => array(),
	);

	CIBlockElement::GetPropertyValuesArray($elementIndex, $arResult["IBLOCK_ID"], array(
		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		"ID" => $arResult["ID"],
	), $propertyFilter);

	foreach ($elementIndex as $idx)
	{
		foreach ($idx["PROPERTIES"] as $property)
		{
			$url = '';
			if ($property["MULTIPLE"] == "Y" && $property["VALUE"])
			{
				$url = current($property["VALUE"]);
			}
			if ($property["MULTIPLE"] == "N" && $property["VALUE"])
			{
				$url = $property["VALUE"];
			}

			if (preg_match('/(?:youtube\\.com|youtu\\.be).*?[\\?\\&]v=([^\\?\\&]+)/i', $url, $matches))
			{
				$arResult["VIDEO"] = 'https://www.youtube.com/embed/'.$matches[1].'/?rel=0&controls=0showinfo=0';
			}
			elseif (preg_match('/(?:vimeo\\.com)\\/([0-9]+)/i', $url, $matches))
			{
				$arResult["VIDEO"] = 'https://player.vimeo.com/video/'.$matches[1];
			}
			elseif (preg_match('/(?:rutube\\.ru).*?\\/video\\/([0-9a-f]+)/i', $url, $matches))
			{
				$arResult["VIDEO"] = 'http://rutube.ru/video/embed/'.$matches[1].'?sTitle=false&sAuthor=false';
			}
			elseif (preg_match('/(?:soundcloud\\.com)/i', $url, $matches))
			{
				$arResult["SOUND_CLOUD"] = $url;
			}
		}
	}
}

/*SLIDER*/
$arResult["SLIDER"] = [];
$sliderProperty = trim($arParams["SLIDER_PROPERTY"]);
if ($sliderProperty)
{
	if (is_numeric($sliderProperty))
	{
		$propertyFilter = array(
			"ID" => $sliderProperty,
		);
	}
	else
	{
		$propertyFilter = array(
			"CODE" => $sliderProperty,
		);
	}

	$elementIndex = array();
	$elementIndex[$arResult["ID"]] = array(
		"PROPERTIES" => array(),
	);

	CIBlockElement::GetPropertyValuesArray($elementIndex, $arResult["IBLOCK_ID"], array(
		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		"ID" => $arResult["ID"],
	), $propertyFilter);

	foreach ($elementIndex as $idx)
	{
		foreach ($idx["PROPERTIES"] as $property)
		{
			$files = array();
			if ($property["MULTIPLE"] == "Y" && $property["VALUE"])
			{
				$files = $property["VALUE"];
			}
			if ($property["MULTIPLE"] == "N" && $property["VALUE"])
			{
				$files = array($property["VALUE"]);
			}

			if ($files)
			{
				foreach ($files as $fileId)
				{
					$file = CFile::GetFileArray($fileId);
					if ($file && $file["WIDTH"] > 0 && $file["HEIGHT"] > 0)
					{
						$arResult["SLIDER"][] = $file;
					}
				}
			}
		}
	}
}
