<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="job-element">
<?$showRequirements = true;?>
<div class="job-prop-text">
<?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
	<?if($arProperty["SORT"] < 501): 
		if($showRequirements): $showRequirements = false;?><div class="job-prop-title"><?=GetMessage("JOB_REQUIREMENTS")?></div><?endif;?>
		<div><?=$arProperty["NAME"]?>&nbsp;-&nbsp;
		<?
		if(is_array($arProperty["DISPLAY_VALUE"])):
			echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
		elseif($pid=="MANUAL"):
			?><a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a><?
		else:
			echo $arProperty["DISPLAY_VALUE"];?>
		<?endif?>
		</div>
	<?endif;?>
<?endforeach?>
</div>
<?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
	<?if($arProperty["SORT"] > 500 && $arProperty["SORT"] < 800 ):?>
		<div class="job-prop-title"><?=$arProperty["NAME"]?>:</div>
		<div class="job-prop-text">
			<?
			if(is_array($arProperty["DISPLAY_VALUE"])):
				echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
			elseif($pid=="MANUAL"):
				?><a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a><?
			else:
				echo $arProperty["DISPLAY_VALUE"];?>
			<?endif?>
		</div>
	<?endif;?>
<?endforeach?>
<?if(!empty($arResult["PREVIEW_TEXT"])):?>
	<div class="job-prop-title"><?=GetMessage("JOB_DESCRIPTION")?></div>
	<div class="job-prop-text"><?=$arResult["PREVIEW_TEXT"]?></div>
<?endif;?>
<div class="job-prop-text">
<?$showEmployer = true;?>
<?foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
	<?if($arProperty["SORT"] > 799): 
		if($showEmployer): $showEmployer = false;?><div class="job-prop-title"><?=GetMessage("JOB_EMPLOYER")?></div><?endif;?>
		<div><?if($pid != 'FIRM'):?><?=$arProperty["NAME"]?>:&nbsp;<?endif;?>
		<?
		if(is_array($arProperty["DISPLAY_VALUE"])):
			echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
		elseif($pid=="MANUAL"):
			?><a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a><?
		else:
			echo $arProperty["DISPLAY_VALUE"];?>
		<?endif?>
		</div>
	<?endif;?>
<?endforeach?>
</div>


<?if(is_array($arResult["SECTION"])):?>
	<br /><a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>"><?=GetMessage("CATALOG_BACK")?></a>
<?endif?>
</div>
