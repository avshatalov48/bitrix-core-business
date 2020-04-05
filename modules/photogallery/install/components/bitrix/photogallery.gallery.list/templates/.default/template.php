<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["GALLERIES"])):
	return false;
endif;

$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
if (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
?>
<style>
div.photo-gallery-avatar{
	width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;
	height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;}
</style>
<?
endif;

if ($arResult["I"]["ACTIONS"]["CREATE_GALLERY"] =="Y" && $arParams["USER_ID"] == $GLOBALS["USER"]->GetId()):
?>
<noindex>
	<div class="photo-controls photo-controls-buttons photo-controls-gallery-new">
		<ul class="photo-controls">
			<li class="photo-control photo-control-first  photo-control-last photo-control-create photo-control-create-gallery">
				<a href="<?=$arResult["LINK"]["NEW"].((strpos($arResult["LINK"]["NEW"], "?") === false ? "?" : "&"). 
										"back_url=".urlencode($APPLICATION->GetCurPageParam()))?>" rel="nofollow">
					<span><?=GetMessage("P_GALLERY_CREATE")?></span></a>
			</li>
		</ul>
		<div class="empty-clear"></div>
	</div> 
</noindex>
<?
endif;

if (!empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-top">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>
<ul class="photo-items-list photo-galleries-list">
<?
foreach($arResult["GALLERIES"] as $res):
$title = "";
if ($res["UF_DEFAULT"] == "Y")
	$title = GetMessage("P_GALLERY_ACTIVE");
if (empty($res["CODE"]))
	$title = " ".GetMessage("P_ERROR_CODE");
$title = trim($title);
if ($title != "")
	$title = "title =\"".$title."\"";
?>
<li class="photo-item photo-gallery-item<?=($res["UF_DEFAULT"] == "Y" ? " photo-gallery-active" : "")?><?=(empty($res["CODE"]) ? " photo-item-error" : "")?>" <?= $title?>>
	<table cellspacing="0" border="0" class="photo-table">
		<tbody><tr>
		<td class="photo-item-cover">
			<div class="photo-item-cover-block-container">
				<div class="photo-item-cover-block-outer">
					<div class="photo-item-cover-block-inner">
						<div class="photo-item-cover-block-inside">
							<a href="<?=$res["LINK"]["VIEW"]?>" class="photo-item-cover-link" <?
								?>title="<?=str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>">
								<div class="photo-item-cover photo-gallery-avatar <?=(empty($res["PICTURE"]["SRC"])? "photo-gallery-avatar-empty" : "")?>" <?
									if (!empty($res["PICTURE"]["SRC"])):
										?> style="background-image:url('<?=$res["PICTURE"]["SRC"]?>');" <?
									endif;?>>
									<div class="photo-item-cover-block-empty"></div><?
									if ($res["UF_DEFAULT"] == "Y"):
										?><div class="gallery-active" title="<?=GetMessage("P_GALLERY_ACTIVE")?>"></div><?
									endif;
									?></div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</td>
		<td class="photo-item-info">
			<div class="photo-item-info-block-container">
				<div class="photo-item-info-block-outer">
					<div class="photo-item-info-block-inner">
						<div class="photo-gallery-name">
							<span class="photo-gallery-name">
								<a href="<?=$res["LINK"]["VIEW"]?>" title="<?=str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>">
								<?=$res["NAME"]?></a></span><?
							if (empty($res["CODE"])):
								?><div class="gallery-error-code" title="<?=GetMessage("P_ERROR_CODE")?>"></div><?
							endif;
						?></div>
						<?
						if (!empty($res["DESCRIPTION"])):
						?>
						<div class="photo-gallery-description"><?=$res["DESCRIPTION"]?></div>
						<?
						endif;
						if ($arResult["I"]["PERMISSION"] >= "W"):
						?>
						<noindex>
						<div class="photo-controls photo-controls-gallery-edit">
							<ul class="photo-controls">
								<li class="photo-control photo-control-first photo-control-photo-upload">
									<a rel="nofollow" href="<?=$res["LINK"]["UPLOAD"]?>">
										<span><?=GetMessage("P_UPLOAD")?></span></a>
								</li>
								<li class="photo-control photo-control-gallery-edit">
									<a rel="nofollow" href="<?=$res["LINK"]["EDIT"].((strpos($res["LINK"]["EDIT"], "?") === false ? "?" : "&"). 
										"back_url=".urlencode($APPLICATION->GetCurPageParam()))?>">
										<span><?=GetMessage("P_GALLERY_EDIT")?></span></a>
								</li>
								<li class="photo-control photo-control-last photo-control-drop photo-control-gallery-drop">
									<a rel="nofollow" href="<?=$res["LINK"]["DROP"]?>" <?
										?>onclick="return confirm('<?=GetMessage('P_GALLERY_DELETE_ASK')?>');"><?
										?><span><?=GetMessage("P_GALLERY_DELETE")?></span></a>
								</li>
							</ul>
							<div class="empty-clear"></div>
						</div>
						</noindex>
						<?
						endif;
						?>
					</div>
				</div>
			</div>
		</td>
	</tr></tbody></table>
	</li>
<?
endforeach;
?>
</ul>
<div class="empty-clear"></div>
<?
if (!empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-bottom">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>