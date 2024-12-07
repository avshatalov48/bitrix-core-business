<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?CUtil::InitJSCore();?>
<?if (!empty($arResult["ITEMS"])):?>

<div class="learn-course-tree">
	<ul>

	<?php

		$bracketLevel = 0;
		foreach ($arResult["ITEMS"] as $arItem):
			if ( $arItem["DEPTH_LEVEL"] <= $bracketLevel )
			{
				$deltaLevel = $bracketLevel - $arItem['DEPTH_LEVEL'] + 1;
				echo str_repeat("</ul></li>", $deltaLevel);
				$bracketLevel -= $deltaLevel;
			}

		if ($arItem["TYPE"] == "CH"):
			$bracketLevel++;
			?>
			<li class="tree-item tree-item-chapter <?if($arItem["CHAPTER_OPEN"] === false):?> tree-item-closed<?endif?>">
				<div class="tree-item-wrapper<?if($arItem["SELECTED"] === true):?> tree-item-selected<?endif?>" onmouseover="BX.addClass(this, 'tree-item-hover'); BX.PreventDefault(event);" onmouseout="BX.removeClass(this, 'tree-item-hover')">
					<b class="r0"></b>
					<div class="tree-item-text">
						<div class="chapter" onclick="JMenu.OpenChapter(this,'<?=$arItem["ID"]?>')"></div>
						<a class="tree-item-link" hidefocus="true" href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a>
					</div>
					<b class="r0"></b>
				</div>
				<ul>
		<?elseif($arItem["TYPE"] == "LE"):?>
			<li class="tree-item tree-lesson">
				<div class="tree-item-wrapper<?if($arItem["SELECTED"]):?> tree-item-selected<?endif?>" onmouseover="BX.addClass(this, 'tree-item-hover'); BX.PreventDefault(event);" onmouseout="BX.removeClass(this, 'tree-item-hover'); BX.PreventDefault(event);">
					<b class="r0"></b>
					<div class="tree-item-text">
						<div class="lesson" onclick="window.location=BX.findNextSibling(this, { className : 'tree-item-link'}).href"></div>
						<a class="tree-item-link" hidefocus="true" href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a>
					</div>
					<b class="r0"></b>
				</div>
			</li>
		<?elseif($arItem["TYPE"] == "CD"):?>
			<li class="tree-item tree-item-course">
				<div class="tree-item-wrapper<?if($arItem["SELECTED"]):?> tree-item-selected<?endif?>" onmouseover="BX.addClass(this, 'tree-item-hover'); BX.PreventDefault(event);" onmouseout="BX.removeClass(this, 'tree-item-hover'); BX.PreventDefault(event);">
					<b class="r0"></b>
					<div class="tree-item-text">
						<div class="course-detail"></div>
						<a class="tree-item-link" hidefocus="true" href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a>
					</div>
					<b class="r0"></b>
				</div>
			</li>
		<?elseif($arItem["TYPE"] == "TL"):?>
			<li class="tree-item tree-item-tests">
				<div class="tree-item-wrapper<?if($arItem["SELECTED"]):?> tree-item-selected<?endif?>" onmouseover="BX.addClass(this, 'tree-item-hover'); BX.PreventDefault(event);" onmouseout="BX.removeClass(this, 'tree-item-hover'); BX.PreventDefault(event);">
					<b class="r0"></b>
					<div class="tree-item-text">
						<div class="test-list"></div>
						<a class="tree-item-link" hidefocus="true" href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a>
					</div>	
					<b class="r0"></b>
				</div>
			</li>
		<?endif?>

	<?endforeach?>

	</ul>
</div>

<script>
	var JMenu = new JCMenu('<?=(array_key_exists("LEARN_MENU_".$arParams["COURSE_ID"],$_COOKIE ) ? CUtil::JSEscape($_COOKIE["LEARN_MENU_".$arParams["COURSE_ID"]]) :"")?>', '<?=$arParams["COURSE_ID"]?>');
</script>

<?endif?>