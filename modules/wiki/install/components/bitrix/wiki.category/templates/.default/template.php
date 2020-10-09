<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult['FATAL_MESSAGE'])):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	if($arResult['COLUMNS_COUNT']==0)
		$arResult['COLUMNS_COUNT'] = 1;

	$iSectCount = ceil(count($arResult['CATEGORIES'])/$arResult['COLUMNS_COUNT']);
	$iPageCount = ceil(count($arResult['PAGES'])/$arResult['COLUMNS_COUNT']);


	if ($iSectCount < 2)
		$iSectCount = count($arResult['CATEGORIES']);
	if ($iPageCount < 2)
		$iPageCount = count($arResult['PAGES']);

	$iWidth = round(100/$arResult['COLUMNS_COUNT']);
	if (!empty($arResult['CATEGORIES'])):
		$sPrevLetter = '';
		$iEl = 0;
		$iCol = 0;
		?>
		<div class="wiki-post-header"><?=GetMessage('WIKI_SUBCATEGORY')?></div>
		<div>
		<?
		foreach ($arResult['CATEGORIES'] as $arSect)
		{
			if ($iEl == 0):
				?> <div style="float:left;  width: <?=$iWidth?>%">  <?
			endif;
			$sCurLetter = mb_strtoupper(mb_substr($arSect['NAME'], 0, 1));
			if ($sPrevLetter != $sCurLetter) :
				$sPrevLetter = $sCurLetter;
			?>
			<div><?=$sPrevLetter?></div>
			<?
			elseif ($iEl == 0 && $iCol > 0):
				?>
				<div><?=$sPrevLetter?>(<?=GetMessage('WIKI_CONTINUED')?>)</div>
				<?
			endif;

			?>
			<a title="<?=$arSect['TITLE']?>" class="<?=($arSect['IS_RED'] == 'Y' ? 'wiki_red' : '')?>" href="<?=$arSect['LINK']?>"><?=$arSect['NAME']?></a>
			<br/>
			<?
			$iEl++;
			if ($iEl == $iSectCount):
				$iEl = 0;
				$iCol++;
				?> </div> <?
			endif;
		}
		if ($iEl != 0 && $iEl < $iSectCount):
			?> </div> <?
		endif;?>
		<div style="clear:both"></div>
		</div> <?
	endif;

	if (!empty($arResult['PAGES'])):
		$sPrevLetter = '';
		$iEl = 0;
		$iCol = 0;
		$arResult['DB_LIST']->NavPrint(GetMessage('NAV_TITLE'));
		?>
		<div class="wiki-post-header"><?=GetMessage('WIKI_PAGES_IN_SUBCATEGORY')?> "<?=$arResult['CUR_CAT']['NAME']?>"</div>
		<div>
		<?
		foreach ($arResult['PAGES'] as $arPage)
		{
			if ($iEl == 0):
				?> <div style="float:left;  width: <?=$iWidth?>%">  <?
			endif;

			$sCurLetter = mb_strtoupper(mb_substr($arPage['NAME'], 0, 1));
			if ($sPrevLetter != $sCurLetter):
				$sPrevLetter = $sCurLetter;
				?>
				<div><?=$sPrevLetter?></div>
				<?
			elseif ($iEl == 0 && $iCol > 0):
				?>
				<div><?=$sPrevLetter?>(<?=GetMessage('WIKI_CONTINUED')?>)</div>
				<?
			endif;

			?>
			<a title="<?=$arPage['TITLE']?>" class="<?=($arPage['IS_RED'] == 'Y' ? 'wiki_red' : '')?>" href="<?=$arPage['LINK']?>"><?=$arPage['NAME']?></a>
			<br/>
			<?
			$iEl++;
			if ($iEl == $iPageCount):
				$iEl = 0;
				$iCol++;
				?> </div> <?
			endif;
		}
		if ($iEl != 0 && $iEl < $iPageCount):
			?> </div> <?
		endif;?>
		<div style="clear:both"></div>
		</div>
		<?
		$arResult['DB_LIST']->NavPrint(GetMessage('NAV_TITLE'));
	endif;
endif;
?>
