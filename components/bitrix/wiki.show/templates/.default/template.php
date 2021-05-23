<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\UI\Extension::load("main.rating");

?><div id="wiki-post">
<?

if(!empty($arResult["FATAL_MESSAGE"])):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=htmlspecialcharsEx($arResult['FATAL_MESSAGE'])?>
		</div>
	</div>
	<?
else:
?>
	<div id="wiki-post-content">
	<? if (isset($arResult['VERSION'])) : ?>
		<div id="wiki-sub-post_content">
			<div id="wiki-version-info">
			<?=GetMessage('WIKI_VERSION_FROM')?> <?=htmlspecialcharsbx($arResult['VERSION']['MODIFIED'])?>;
			<?=$arResult['VERSION']['USER_LOGIN']?>  (<? if (!empty($arResult['VERSION']['CUR_LINK'])){ ?><a title="<?=htmlspecialcharsbx($arResult['ELEMENT']['NAME']);?>" href="<?=htmlspecialcharsbx($arResult['VERSION']['CANCEL_LINK'])?>"><?=GetMessage('WIKI_RESTORE_TO_CURRENT')?></a><? } else { ?><?=GetMessage('WIKI_RESTORE_TO_CURRENT')?><? } ?>)
			</div>
			<div id="wiki-version-nav">
			<? if (!empty($arResult['VERSION']['PREV_LINK'])){ ?><a title="<?=htmlspecialcharsbx($arResult['ELEMENT']['NAME']);?>" href="<?=htmlspecialcharsbx($arResult['VERSION']['PREV_LINK'])?>"><?=GetMessage('WIKI_PREV_VERSION')?></a> <? } else { ?> <?=GetMessage('WIKI_PREV_VERSION')?> <? } ?> |
			<? if (!empty($arResult['VERSION']['CUR_LINK'])){ ?><a title="<?=htmlspecialcharsbx($arResult['ELEMENT']['NAME']);?>" href="<?=htmlspecialcharsbx($arResult['VERSION']['CUR_LINK'])?>"><?=GetMessage('WIKI_CURR_VERSION')?></a> <? } else { ?> <?=GetMessage('WIKI_CURR_VERSION')?> <? } ?> |
			<? if (!empty($arResult['VERSION']['NEXT_LINK'])){ ?><a title="<?=htmlspecialcharsbx($arResult['ELEMENT']['NAME']);?>" href="<?=htmlspecialcharsbx($arResult['VERSION']['NEXT_LINK'])?>"><?=GetMessage('WIKI_NEXT_VERSION')?></a> <? } else { ?> <?=GetMessage('WIKI_NEXT_VERSION')?> <? } ?>
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

					$voteEntityType = "IBLOCK_ELEMENT";
					$voteEntityId = $arResult['ELEMENT']['ID'];

					$voteId = $voteEntityType.'_'.$voteEntityId.'-'.(time()+rand(0, 1000));

					$likeTemplate = (
						$arResult["isIntranetInstalled"]
							? 'like_react'
							: $arParams["RATING_TYPE"]
					);

					$componentParams = array(
						'ENTITY_TYPE_ID' => $voteEntityType,
						'ENTITY_ID' => $voteEntityId,
						'OWNER_ID' => $arResult['ELEMENT']['CREATED_BY'],
						'PATH_TO_USER_PROFILE' => $arParams['PATH_TO_USER'],
						"VOTE_ID" => $voteId
					);

					if ($arResult["isIntranetInstalled"])
					{
						$arRating = \CRatings::getRatingVoteResult($voteEntityType, $voteEntityId);
						$emotion = (!empty($arRating["USER_REACTION"])? mb_strtoupper($arRating["USER_REACTION"]) : 'LIKE');

						?><span id="bx-ilike-button-<?=htmlspecialcharsbx($voteId)?>" class="feed-inform-ilike feed-new-like"><?
							?><span class="bx-ilike-left-wrap<?=(isset($arRating["USER_HAS_VOTED"]) && $arRating["USER_HAS_VOTED"] == "Y" ? ' bx-you-like-button' : '')?>"><a href="#like" class="bx-ilike-text"><?=\CRatingsComponentsMain::getRatingLikeMessage($emotion)?></a></span><?
						?></span><?

						if (!empty($arRating))
						{
							$componentParams = array_merge($componentParams, $arRating);
							$componentParams['TOP_DATA'] = (!empty($arResult['TOP_RATING_DATA']) ? $arResult['TOP_RATING_DATA'] : array());
						}

						?><div class="feed-post-emoji-top-panel-outer"><?
							?><div id="feed-post-emoji-top-panel-container-<?=htmlspecialcharsbx($voteId)?>" class="feed-post-emoji-top-panel-box <?=(intval($arRating["TOTAL_POSITIVE_VOTES"]) > 0 ? 'feed-post-emoji-top-panel-container-active' : '')?>"><?
					}

					$APPLICATION->IncludeComponent(
						'bitrix:rating.vote',
						$likeTemplate,
						$componentParams,
						$component,
						array('HIDE_ICONS' => 'Y')
					);

					if ($arResult["isIntranetInstalled"])
					{
							?></div><?
						?></div><?
					}

				?></div>
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
