<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="wiki-post">
<?

if(!empty($arResult["FATAL_MESSAGE"])):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
?>
	<div id="wiki-post-content">
	<? if (isset($arResult['VERSION'])) : ?>
		<div id="wiki-sub-post_content">
			<div id="wiki-version-info">
			<?=GetMessage('WIKI_VERSION_FROM')?> <?=$arResult['VERSION']['MODIFIED']?>;
			<?=$arResult['VERSION']['USER_LOGIN']?>  (<? if (!empty($arResult['VERSION']['CUR_LINK'])){ ?><a title="<?=$arResult['ELEMENT']['NAME'];?>" href="<?=$arResult['VERSION']['CANCEL_LINK']?>"><?=GetMessage('WIKI_RESTORE_TO_CURRENT')?></a><? } else { ?><?=GetMessage('WIKI_RESTORE_TO_CURRENT')?><? } ?>)
			</div>
			<div id="wiki-version-nav">
			<? if (!empty($arResult['VERSION']['PREV_LINK'])){ ?><a title="<?=$arResult['ELEMENT']['NAME'];?>" href="<?=$arResult['VERSION']['PREV_LINK']?>"><?=GetMessage('WIKI_PREV_VERSION')?></a> <? } else { ?> <?=GetMessage('WIKI_PREV_VERSION')?> <? } ?> |
			<? if (!empty($arResult['VERSION']['CUR_LINK'])){ ?><a title="<?=$arResult['ELEMENT']['NAME'];?>" href="<?=$arResult['VERSION']['CUR_LINK']?>"><?=GetMessage('WIKI_CURR_VERSION')?></a> <? } else { ?> <?=GetMessage('WIKI_CURR_VERSION')?> <? } ?> |
			<? if (!empty($arResult['VERSION']['NEXT_LINK'])){ ?><a title="<?=$arResult['ELEMENT']['NAME'];?>" href="<?=$arResult['VERSION']['NEXT_LINK']?>"><?=GetMessage('WIKI_NEXT_VERSION')?></a> <? } else { ?> <?=GetMessage('WIKI_NEXT_VERSION')?> <? } ?>
			</div>
		</div>
	<? endif ?>
	<?=$arResult['ELEMENT']['DETAIL_TEXT'];?>
	<?
	switch($arResult['SERVICE_PAGE'])
	{
		case 'category' :

			if($arResult['ELEMENT']['DETAIL_TEXT'])
				echo "<br /><br />";

			$APPLICATION->IncludeComponent(
				'bitrix:wiki.category',
				'',
				Array(
					'PATH_TO_POST' => $arParams['PATH_TO_POST'],
					'PATH_TO_POST_EDIT' => $arParams['PATH_TO_POST_EDIT'],
					'PATH_TO_CATEGORY' => $arParams['PATH_TO_CATEGORY'],
					'PATH_TO_CATEGORIES' => $arParams['PATH_TO_CATEGORIES'],
					'PATH_TO_DISCUSSION' => $arParams['PATH_TO_DISCUSSION'],
					'PATH_TO_HISTORY' => $arParams['PATH_TO_HISTORY'],
					'PAGE_VAR' => $arParams['ALIASES']['wiki_name'],
					'OPER_VAR' => $arParams['ALIASES']['oper'],
					'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
					'IBLOCK_ID' => $arParams['IBLOCK_ID'],
					'CACHE_TYPE' => $arParams['CACHE_TYPE'],
					'CACHE_TIME' => $arParams['CACHE_TIME'],
					'ELEMENT_NAME' => $arResult['ELEMENT']['NAME'],
					'PAGES_COUNT' => '100',
					'COLUMNS_COUNT' => '3'
				),
				$component
			);

			echo "<br /><br />";
		break;
	}

	if (!empty($arResult['ELEMENT']['SECTIONS'])):
		?><div id="wiki_category">
			<div class="wiki-category-item">
				<?
				$_i = 1;
				foreach ($arResult['ELEMENT']['SECTIONS'] as $arSect)
				{
					?><a title="<?=htmlspecialcharsbx($arSect['TITLE'], ENT_QUOTES)?>" class="<?=($arSect['IS_RED'] == 'Y' ? 'wiki_red' : '')?>" href="<?=htmlspecialcharsbx($arSect['LINK'], ENT_QUOTES)?>"><?=htmlspecialcharsbx($arSect['NAME'], ENT_QUOTES)?></a><?
					if ($_i < count($arResult['ELEMENT']['SECTIONS']))
						echo $arSect['IS_SERVICE'] == 'Y' ? ': ' : ' | ';
					$_i++;

				}
				?>
			</div>
			<?if ($arParams['SHOW_RATING'] == 'Y'):?>
				<div class="wiki-category-rating"><?
				$APPLICATION->IncludeComponent(
					'bitrix:rating.vote', $arParams['RATING_TYPE'],
					Array(
						'ENTITY_TYPE_ID' => "IBLOCK_ELEMENT",
						'ENTITY_ID' => $arResult['ELEMENT']['ID'],
						'OWNER_ID' => $arResult['ELEMENT']['CREATED_BY'],
						'PATH_TO_USER_PROFILE' => $arParams['PATH_TO_USER'],
					),
					$component,
					array('HIDE_ICONS' => 'Y')
				);?>
			</div>
		<?endif;?>
			<div style="clear:both"></div>
		</div>
	<?
	endif;
	?>
	<?
	if (!empty($arResult['ELEMENT']['_TAGS'])):
		?><div id="wiki_category">
		<?=GetMessage('WIKI_TAGS')?>:
		<?=CWikiUtils::GetTagsAsLinks($arResult['ELEMENT']['_TAGS'])?>
		</div>
		<?
	endif;
	?>
	</div>
<?
endif;
?>
</div>
