<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["GALLERIES"])):
	return false;
endif;
/********************************************************************
				Input params
********************************************************************/
$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ? $arParams["SHOW_PAGE_NAVIGATION"] : "bottom");
/***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation-top">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>
<ul class="photo-items-list photo-galleries-list-modern">
<?

foreach($arResult["GALLERIES"] as $res):
	if (empty($res["ALBUMS"]))
		continue; 
?>
	<li class="photo-item photo-gallery-item">
		<div class="photo-item photo-gallery-item">
			<div class="photo-gallery-name">
				<a href="<?=$res["LINK"]["VIEW"]?>"><?=$res["NAME"]?></a> 
			</div>
<ul class="photo-items-list photo-album-thumbs-list">
<?
	$iCount = 0;
	foreach ($res["ALBUMS"] as $res2)
	{
		$iCount++; 
?>
	<li class="photo-item photo-album-item photo-album-<?=($res2["ACTIVE"] != "Y" ? "nonactive" : "active")?> <?=(
		!empty($res2["PASSWORD"]) ? " photo-album-password " : "")?><?
		?><?=($iCount == 1 ? " photo-item-first" : "")?><?
		?><?=($iCount == count($res["ALBUMS"]) ? " photo-item-last" : "")?><?
		?>" id="photo_album_info_<?=$res2["ID"]?>" <?
		?> title="<?=trim("&laquo;".$res2["~NAME"]."&raquo;  ".(!empty($res2["DATE"]) ? " ".$res2["DATE"]." " : ""))?>" <?
			
		?>>
		<div class="photo-item-cover-block-outside">
			<div class="photo-item-cover-block-container">
				<div class="photo-item-cover-block-outer">
					<div class="photo-item-cover-block-inner">
						<div class="photo-item-cover-block-inside">
							<a href="<?=$res2["URL"]["VIEW"]?>" class="photo-item-cover-link"><?
								?><div class="photo-item-cover photo-album-thumbs-avatar <?=(empty($res2["PICTURE"]["SRC"])? "photo-album-avatar-empty" : "")?>" <?
									?> id="photo_album_cover_<?=$res2["ID"]?>" <?
									if (!empty($res2["PICTURE"]["SRC"])):
										?>style="background-image:url('<?=$res2["PICTURE"]["SRC"]?>'); "<?
									endif;
									?>>
								</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</li>
<?
	}
?>
</ul>
			<div class="empty-clear"></div>
			<div class="photo-gallery-description">
				<?=$res["DESCRIPTION"]?>
			</div>
		</div>
	</li>
<?
endforeach;
?></ul>
<div class="empty-clear"></div>
<?

if (($arParams["SHOW_PAGE_NAVIGATION"] == "bottom" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation-bottom">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>