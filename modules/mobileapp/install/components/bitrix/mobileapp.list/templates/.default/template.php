<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if($arResult["AJAX_MODE"])
	$arItemsHtml = array();
else
{
	?>
		<div id="mobile-list">
	<?

	$finalHtml = '';
}

foreach ($arResult["ITEMS"] as $arItem)
{
	if(isset($arItem["FOLDED"]) && $arItem["FOLDED"])
		$bFolded = true;
	else
		$bFolded = false;

	$itemHtml = '
			<div id="mobile-list-item-'.$arItem["ID"].'" class="mapp_itemlist_item_container';

	if($bFolded || !isset($arItem["TITLE_COLOR"]) || !$arItem["TITLE_COLOR"])
		$itemHtml .= ' mapp_item_folded';
	elseif(isset($arItem["TITLE_COLOR"]) && $arItem["TITLE_COLOR"])
		$itemHtml .= ' item_'.strtolower($arItem["TITLE_COLOR"]);
	else
		$itemHtml .= ' mapp_item_gray';

	$itemHtml .= '">';

	if(isset($arItem['TITLE']))
		$itemHtml .= '
			<div class="mapp_itemlist_item_title">'.
				'<span'.(!isset($arItem['DETAIL_LINK']) ? ' class="no_arrow"' : '').'>'.
					$arItem['TITLE'].
				'</span>'.
			'</div>';

	if(isset($arItem['ROW']) && is_array($arItem['ROW']))
	{
		$itemHtml .= '
			<div class="mapp_itemlist_item_content'.($bFolded ? ' closed' : '').'">';

			foreach ($arItem['ROW'] as $arRow)
			{
				$itemHtml .= '<div class="mapp_itemlist_item_';

				if(isset($arRow["TYPE"]))
					$itemHtml .= strtolower($arRow["TYPE"]);
				else
					$itemHtml .= 'BULLET';

				$itemHtml .='"><table><tr><td><span class="mapp_itemlist_row_picture"></span></td><td>'.$arRow["CONTENT"].'</td></tr></table></div>';
			}

		if(isset($arItem['CONTENT_RIGHT']))
			$itemHtml .= '
				<div class="mapp_itemlist_item_right">'.$arItem['CONTENT_RIGHT'].'</div>';

		$itemHtml .= '
			</div>';
	}

	if(isset($arItem['BOTTOM']))
	{
		$itemHtml .= '
			<div class="mapp_itemlist_item_bottom">';

		if(isset($arItem['BOTTOM']['LEFT']))
			$itemHtml .='
				<div class="mapp_itemlist_bottom_left">'.$arItem['BOTTOM']['LEFT'].'</div>';

		if(isset($arItem['BOTTOM']['RIGHT']) && !$bFolded)
			$itemHtml .= '
				<div class="'.($bFolded ? 'mapp_itemlist_item_bottom_completed' : 'mapp_itemlist_bottom_right').'">'.$arItem['BOTTOM']['RIGHT'].'</div>';

		if($bFolded && isset($arItem['CONTENT_RIGHT']))
			$itemHtml .= '
				<div class="mapp_itemlist_item_right">'.$arItem['CONTENT_RIGHT'].'</div>';

		$itemHtml .= '
		</div>
		</div>';
	}

	if(isset($arItem['DETAIL_LINK']))
		$itemHtml = '
		<a href="'.$arItem['DETAIL_LINK'].'" class="mapp_itemlist_item_link">
		'.$itemHtml.'
		</a>';

	if(isset($arItem['TOGGLABLE']) && $arItem['TOGGLABLE'] == true)
		$itemHtml .= '<script type="text/javascript">'.
						'BX.ready(function(){ mobileAppList.makeFastButton("mobile-list-item-'.$arItem["ID"].'");})'.
					'</script>';

	if($arResult["AJAX_MODE"])
		$arItemsHtml[$arItem["ID"]] = $itemHtml;
	else
		$finalHtml .= $itemHtml;
}

if($arResult["AJAX_MODE"])
{
	$arItemsHtml = $APPLICATION->ConvertCharsetArray($arItemsHtml, SITE_CHARSET, 'utf-8');
	echo json_encode($arItemsHtml);
	die();
}

echo $finalHtml;

?>
		</div>

<script type="text/javascript">

	<?if(isset($arParams["TITLE"])):?>
		app.setPageTitle({title: "<?=$arParams["TITLE"]?>"});
	<?endif;?>

	var mobileAppListParams  = {
		ajaxUrl: "<?=$arResult["AJAX_PATH"]?>"
	};

	var mobileAppList = new __MobileAppList(mobileAppListParams);

	<?if($arResult["JS_EVENT_ITEM_CHANGE"]):?>
		BX.addCustomEvent('<?=$arResult["JS_EVENT_ITEM_CHANGE"]?>', function (params){ mobileAppList.getItemsHtml(params.arItems, params.insertToBottom);});
	<?endif;?>

	var bottomReached = false;
	window.onscroll = function ()
	{
		var preloadCoefficient = <?=$arResult["MAPP_LIST_PRELOAD_START"]?>;
		var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
		var documentHeight = document.documentElement.scrollHeight ? document.documentElement.scrollHeight : document.body.scrollHeight;
		var scrollTop = window.pageYOffset ? window.pageYOffset : (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);

		if((documentHeight - clientHeight*(1+preloadCoefficient)) <= scrollTop)
		{
			if(!bottomReached)
			{
				BX.onCustomEvent('<?=$arResult["JS_EVENT_BOTTOM_REACHED"]?>');
				bottomReached = true;
			}
		}
	}

</script>