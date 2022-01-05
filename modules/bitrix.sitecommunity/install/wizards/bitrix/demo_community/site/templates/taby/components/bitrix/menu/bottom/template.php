<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
	<? 
	$firstNum = 0; 
	$lastNum = 0;

	for ($i = 0; $i < count($arResult); $i++)
	{
		if ($arResult[$i]["PERMISSION"] <= "D") 
			continue;
		
		if ($firstNum == 0)
			$firstNum = $i;

		$lastNum = $i;
	}

	?>
	<ul id="footer-links">
	<?
	for ($i = 0; $i < count($arResult); $i++ )
	{
		$arItem = $arResult[$i];
		if ($arItem["PERMISSION"] > "D"):
	
			$cssClass = "";
			if ($i == $firstNum) 
				$cssClass .= ($cssClass <> '' ? " first-item" : "first-item");
				
			if ($arItem["SELECTED"]) 
				$cssClass .= ($cssClass <> '' ? " selected" : "selected");
				
			if ($i == $lastNum) 
				$cssClass .= ($cssClass <> '' ? " last-item" : "last-item");
                
			?>
			<li <? if ($cssClass <> '') { ?>class="<?= $cssClass ?>"<? } ?>>
				<a href="<?=$arItem["LINK"]?>"><span><?=$arItem["TEXT"]?></span></a>
		            </li>
			<?
		endif;
	};
	?>
	</ul>
	<?
    ?>
<? endif; ?>