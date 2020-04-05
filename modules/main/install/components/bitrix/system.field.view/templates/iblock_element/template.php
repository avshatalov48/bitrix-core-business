<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bFirst = true;

	foreach ($arResult["VALUE"] as $ID => $res):
		$surl = GetIBlockElementLinkById($ID);
		if ($surl && strlen($surl) > 0)
			$res = '<a href="'.$surl.'">'.$res.'</a>';
	
		if (!$bFirst):
			?>, <?
		else:
			$bFirst = false;
		endif;

		?><span class="fields enumeration"><?=$res?></span><?
	endforeach;
?>