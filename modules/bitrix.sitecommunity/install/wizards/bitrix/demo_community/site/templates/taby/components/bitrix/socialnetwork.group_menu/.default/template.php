<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$this->SetViewTarget("sidebar", 100);
?>
<div class="rounded-block">
	<div class="corner left-top"></div>
	<div class="corner right-top"></div>
	<div class="block-content">
		<div class="content-list user-sidebar">
			<div class="content-item">
				<div class="content-avatar">
					<a<?if ($arResult["CurrentUserPerms"]["UserCanViewGroup"]):?> href="<?=$arResult["Urls"]["View"]?>"<?endif;?><?if (strlen($arResult["Group"]["IMAGE_FILE"]["src"]) > 0):?> style="background:url('<?=$arResult["Group"]["IMAGE_FILE"]["src"]?>') no-repeat scroll center center transparent;"<?endif;?>></a>
				</div>			
				<div class="content-info">
					<div class="content-title"><a <?if ($arResult["CurrentUserPerms"]["UserCanViewGroup"]):?> href="<?=$arResult["Urls"]["View"]?>"<?endif;?>><?=$arResult["Group"]["NAME"]?></a></div>
					<?if($arResult["Group"]["CLOSED"] == "Y"):?>
						<div class="content-description"><?= GetMessage("SONET_UM_ARCHIVE_GROUP") ?></div>
					<?endif;?>
				</div>
			</div>
		</div>
		<div class="hr"></div>
		<ul class="mdash-list">
			<li class="<?if ($arParams["PAGE_ID"] == "group"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["View"]?>"><?=GetMessage("SONET_UM_GENERAL")?></a></li>
			<?
			foreach ($arResult["CanView"] as $key => $val)
			{
				if (!$val)
					continue;
				?><li class="<?if ($arParams["PAGE_ID"] == "group_".$key):?>selected<?endif?>"><a href="<?= $arResult["Urls"][$key] ?>"><?=$arResult["Title"][$key]?></a></li><?
			}
			?>
			<li class="<?if ($arParams["PAGE_ID"] == "group_users"):?>selected<?endif?>"><a href="<?=$arResult["Urls"]["GroupUsers"]?>"><?=GetMessage("SONET_UM_USERS")?></a></li>
		</ul>
	</div>
	<div class="corner left-bottom"></div>
	<div class="corner right-bottom"></div>
</div>
<?
$this->EndViewTarget();
?>