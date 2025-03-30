<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arResult['TAGS_CHAIN'] = [];
if ($arResult['REQUEST']['~TAGS'])
{
	$res = array_unique(explode(',', $arResult['REQUEST']['~TAGS']));
	$url = [];
	foreach ($res as $key => $tags)
	{
		$tags = trim($tags);
		if (!empty($tags))
		{
			$url_without = $res;
			unset($url_without[$key]);
			$url[$tags] = $tags;
			$result = [
				'TAG_NAME' => htmlspecialcharsex($tags),
				'TAG_PATH' => $APPLICATION->GetCurPageParam('tags=' . urlencode(implode(',', $url)), ['tags']),
				'TAG_WITHOUT' => $APPLICATION->GetCurPageParam((count($url_without) > 0 ? 'tags=' . urlencode(implode(',', $url_without)) : ''), ['tags']),
			];
			$arResult['TAGS_CHAIN'][] = $result;
		}
	}
}
