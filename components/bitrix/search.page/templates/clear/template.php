<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>
<div class="search-page">
	<?php if ($arParams['SHOW_TAGS_CLOUD'] == 'Y')
	{
		$arCloudParams = [
			'SEARCH' => $arResult['REQUEST']['~QUERY'],
			'TAGS' => $arResult['REQUEST']['~TAGS'],
			'CHECK_DATES' => $arParams['CHECK_DATES'],
			'arrFILTER' => $arParams['arrFILTER'],
			'SORT' => $arParams['TAGS_SORT'],
			'PAGE_ELEMENTS' => $arParams['TAGS_PAGE_ELEMENTS'],
			'PERIOD' => $arParams['TAGS_PERIOD'],
			'URL_SEARCH' => $arParams['TAGS_URL_SEARCH'],
			'TAGS_INHERIT' => $arParams['TAGS_INHERIT'],
			'FONT_MAX' => $arParams['FONT_MAX'],
			'FONT_MIN' => $arParams['FONT_MIN'],
			'COLOR_NEW' => $arParams['COLOR_NEW'],
			'COLOR_OLD' => $arParams['COLOR_OLD'],
			'PERIOD_NEW_TAGS' => $arParams['PERIOD_NEW_TAGS'],
			'SHOW_CHAIN' => 'N',
			'COLOR_TYPE' => $arParams['COLOR_TYPE'],
			'WIDTH' => $arParams['WIDTH'],
			'CACHE_TIME' => $arParams['CACHE_TIME'],
			'CACHE_TYPE' => $arParams['CACHE_TYPE'],
			'RESTART' => $arParams['RESTART'],
		];

		if (is_array($arCloudParams['arrFILTER']))
		{
			foreach ($arCloudParams['arrFILTER'] as $strFILTER)
			{
				if ($strFILTER == 'main')
				{
					$arCloudParams['arrFILTER_main'] = $arParams['arrFILTER_main'];
				}
				elseif ($strFILTER == 'forum' && IsModuleInstalled('forum'))
				{
					$arCloudParams['arrFILTER_forum'] = $arParams['arrFILTER_forum'];
				}
				elseif (mb_strpos($strFILTER,'iblock_') === 0)
				{
					foreach ($arParams['arrFILTER_' . $strFILTER] as $strIBlock)
					{
						$arCloudParams['arrFILTER_' . $strFILTER] = $arParams['arrFILTER_' . $strFILTER];
					}
				}
				elseif ($strFILTER == 'blog')
				{
					$arCloudParams['arrFILTER_blog'] = $arParams['arrFILTER_blog'];
				}
				elseif ($strFILTER == 'socialnetwork')
				{
					$arCloudParams['arrFILTER_socialnetwork'] = $arParams['arrFILTER_socialnetwork'];
				}
			}
		}
		$APPLICATION->IncludeComponent('bitrix:search.tags.cloud', '.default', $arCloudParams, $component, ['HIDE_ICONS' => 'Y']);
	}
	?>
	<form action="" method="get">
		<input type="hidden" name="tags" value="<?php echo $arResult['REQUEST']['TAGS']?>" />
		<input type="hidden" name="how" value="<?php echo $arResult['REQUEST']['HOW'] == 'd' ? 'd' : 'r'?>" />
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tbody><tr>
				<td style="width: 100%;">
					<?php if ($arParams['USE_SUGGEST'] === 'Y'):
						if (mb_strlen($arResult['REQUEST']['~QUERY']) && is_object($arResult['NAV_RESULT']))
						{
							$arResult['FILTER_MD5'] = $arResult['NAV_RESULT']->GetFilterMD5();
							$obSearchSuggest = new CSearchSuggest($arResult['FILTER_MD5'], $arResult['REQUEST']['~QUERY']);
							$obSearchSuggest->SetResultCount($arResult['NAV_RESULT']->NavRecordCount);
						}
						?>
						<?php $APPLICATION->IncludeComponent(
							'bitrix:search.suggest.input',
							'',
							[
								'NAME' => 'q',
								'VALUE' => $arResult['REQUEST']['~QUERY'],
								'INPUT_SIZE' => -1,
								'DROPDOWN_SIZE' => 10,
								'FILTER_MD5' => $arResult['FILTER_MD5'],
							],
							$component, ['HIDE_ICONS' => 'Y']
						);?>
					<?php else:?>
						<input class="search-query" type="text" name="q" value="<?=$arResult['REQUEST']['QUERY']?>" />
					<?php endif;?>
				</td>
				<td>
					&nbsp;
				</td>
				<td>
					<input class="search-button" type="submit" value="<?php echo GetMessage('CT_BSP_GO')?>" />
				</td>
			</tr>
		</tbody></table>

		<noindex>
		<div class="search-advanced">
			<div class="search-advanced-result">
				<?php if (is_object($arResult['NAV_RESULT'])):?>
					<div class="search-result"><?php echo GetMessage('CT_BSP_FOUND')?>: <?php echo $arResult['NAV_RESULT']->SelectedRowsCount()?></div>
				<?php endif;?>
				<?php
				$arWhere = [];

				if (!empty($arResult['TAGS_CHAIN']))
				{
					$tags_chain = '';
					foreach ($arResult['TAGS_CHAIN'] as $arTag)
					{
						$tags_chain .= ' ' . $arTag['TAG_NAME'] . ' [<a href="' . $arTag['TAG_WITHOUT'] . '" class="search-tags-link" rel="nofollow">x</a>]';
					}

					$arWhere[] = GetMessage('CT_BSP_TAGS') . ' &mdash; ' . $tags_chain;
				}

				if ($arParams['SHOW_WHERE'])
				{
					$where = GetMessage('CT_BSP_EVERYWHERE');
					foreach ($arResult['DROPDOWN'] as $key => $value)
					{
						if ($arResult['REQUEST']['WHERE'] == $key)
						{
							$where = $value;
						}
					}

					$arWhere[] = GetMessage('CT_BSP_WHERE') . ' &mdash; ' . $where;
				}

				if ($arParams['SHOW_WHEN'])
				{
					if ($arResult['REQUEST']['FROM'] && $arResult['REQUEST']['TO'])
					{
						$when = GetMessage('CT_BSP_DATES_FROM_TO', ['#FROM#' => $arResult['REQUEST']['FROM'], '#TO#' => $arResult['REQUEST']['TO']]);
					}
					elseif ($arResult['REQUEST']['FROM'])
					{
						$when = GetMessage('CT_BSP_DATES_FROM', ['#FROM#' => $arResult['REQUEST']['FROM']]);
					}
					elseif ($arResult['REQUEST']['TO'])
					{
						$when = GetMessage('CT_BSP_DATES_TO', ['#TO#' => $arResult['REQUEST']['TO']]);
					}
					else
					{
						$when = GetMessage('CT_BSP_DATES_ALL');
					}

					$arWhere[] = GetMessage('CT_BSP_WHEN') . ' &mdash; ' . $when;
				}

				if (count($arWhere))
				{
					echo GetMessage('CT_BSP_WHERE_LABEL'),': ',implode(', ', $arWhere);
				}
				?>
			</div><?php //div class="search-advanced-result"?>
			<?php if ($arParams['SHOW_WHERE'] || $arParams['SHOW_WHEN']):?>
				<script>
				function switch_search_params()
				{
					var sp = document.getElementById('search_params');
					if(sp.style.display == 'none')
					{
						disable_search_input(sp, false);
						sp.style.display = 'block'
					}
					else
					{
						disable_search_input(sp, true);
						sp.style.display = 'none';
					}
					return false;
				}

				function disable_search_input(obj, flag)
				{
					var n = obj.childNodes.length;
					for(var j=0; j<n; j++)
					{
						var child = obj.childNodes[j];
						if(child.type)
						{
							switch(child.type.toLowerCase())
							{
								case 'select-one':
								case 'file':
								case 'text':
								case 'textarea':
								case 'hidden':
								case 'radio':
								case 'checkbox':
								case 'select-multiple':
									child.disabled = flag;
									break;
								default:
									break;
							}
						}
						disable_search_input(child, flag);
					}
				}
				</script>
				<div class="search-advanced-filter"><a href="#" onclick="return switch_search_params()"><?php echo GetMessage('CT_BSP_ADVANCED_SEARCH')?></a></div>
		</div><?php //div class="search-advanced"?>
				<div id="search_params" class="search-filter" style="display:<?php echo $arResult['REQUEST']['FROM'] || $arResult['REQUEST']['TO'] || $arResult['REQUEST']['WHERE'] ? 'block' : 'none'?>">
					<h2><?php echo GetMessage('CT_BSP_ADVANCED_SEARCH')?></h2>
					<table class="search-filter" cellspacing="0"><tbody>
						<?php if ($arParams['SHOW_WHERE']):?>
						<tr>
							<td class="search-filter-name"><?php echo GetMessage('CT_BSP_WHERE')?></td>
							<td class="search-filter-field">
								<select class="select-field" name="where">
									<option value=""><?=GetMessage('CT_BSP_ALL')?></option>
									<?php foreach ($arResult['DROPDOWN'] as $key => $value):?>
										<option value="<?=$key?>"<?php echo ($arResult['REQUEST']['WHERE'] == $key) ? ' selected' : '';?>><?=$value?></option>
									<?php endforeach?>
								</select>
							</td>
						</tr>
						<?php endif;?>
						<?php if ($arParams['SHOW_WHEN']):?>
						<tr>
							<td class="search-filter-name"><?php echo GetMessage('CT_BSP_WHEN')?></td>
							<td class="search-filter-field">
								<?php $APPLICATION->IncludeComponent(
									'bitrix:main.calendar',
									'',
									[
										'SHOW_INPUT' => 'Y',
										'INPUT_NAME' => 'from',
										'INPUT_VALUE' => $arResult['REQUEST']['~FROM'],
										'INPUT_NAME_FINISH' => 'to',
										'INPUT_VALUE_FINISH' => $arResult['REQUEST']['~TO'],
										'INPUT_ADDITIONAL_ATTR' => 'class="input-field" size="10"',
									],
									null,
									['HIDE_ICONS' => 'Y']
								);?>
							</td>
						</tr>
						<?php endif;?>
						<tr>
							<td class="search-filter-name">&nbsp;</td>
							<td class="search-filter-field"><input class="search-button" value="<?php echo GetMessage('CT_BSP_GO')?>" type="submit"></td>
						</tr>
					</tbody></table>
				</div>
			<?php else:?>
		</div><?php //div class="search-advanced"?>
			<?php endif;//if($arParams["SHOW_WHERE"] || $arParams["SHOW_WHEN"])?>
		</noindex>
	</form>

<?php if (isset($arResult['REQUEST']['ORIGINAL_QUERY'])):
	?>
	<div class="search-language-guess">
		<?php echo GetMessage('CT_BSP_KEYBOARD_WARNING', ['#query#' => '<a href="' . $arResult['ORIGINAL_QUERY_URL'] . '">' . $arResult['REQUEST']['ORIGINAL_QUERY'] . '</a>'])?>
	</div><br /><?php
endif;?>

	<div class="search-result">
	<?php if ($arResult['REQUEST']['QUERY'] === false && $arResult['REQUEST']['TAGS'] === false):?>
	<?php elseif ($arResult['ERROR_CODE'] != 0):?>
		<p><?=GetMessage('CT_BSP_ERROR')?></p>
		<?php ShowError($arResult['ERROR_TEXT']);?>
		<p><?=GetMessage('CT_BSP_CORRECT_AND_CONTINUE')?></p>
		<br /><br />
		<p><?=GetMessage('CT_BSP_SINTAX')?><br /><b><?=GetMessage('CT_BSP_LOGIC')?></b></p>
		<table border="0" cellpadding="5">
			<tr>
				<td align="center" valign="top"><?=GetMessage('CT_BSP_OPERATOR')?></td><td valign="top"><?=GetMessage('CT_BSP_SYNONIM')?></td>
				<td><?=GetMessage('CT_BSP_DESCRIPTION')?></td>
			</tr>
			<tr>
				<td align="center" valign="top"><?=GetMessage('CT_BSP_AND')?></td><td valign="top">and, &amp;, +</td>
				<td><?=GetMessage('CT_BSP_AND_ALT')?></td>
			</tr>
			<tr>
				<td align="center" valign="top"><?=GetMessage('CT_BSP_OR')?></td><td valign="top">or, |</td>
				<td><?=GetMessage('CT_BSP_OR_ALT')?></td>
			</tr>
			<tr>
				<td align="center" valign="top"><?=GetMessage('CT_BSP_NOT')?></td><td valign="top">not, ~</td>
				<td><?=GetMessage('CT_BSP_NOT_ALT')?></td>
			</tr>
			<tr>
				<td align="center" valign="top">( )</td>
				<td valign="top">&nbsp;</td>
				<td><?=GetMessage('CT_BSP_BRACKETS_ALT')?></td>
			</tr>
		</table>
	<?php elseif (count($arResult['SEARCH']) > 0):?>
		<?php echo ($arParams['DISPLAY_TOP_PAGER'] != 'N') ? $arResult['NAV_STRING'] : '';?>
		<?php foreach ($arResult['SEARCH'] as $arItem):?>
			<div class="search-item">
				<h4><a href="<?php echo $arItem['URL']?>"><?php echo $arItem['TITLE_FORMATED']?></a></h4>
				<div class="search-preview"><?php echo $arItem['BODY_FORMATED']?></div>
				<?php if (
					($arParams['SHOW_ITEM_DATE_CHANGE'] != 'N')
					|| ($arParams['SHOW_ITEM_PATH'] == 'Y' && $arItem['CHAIN_PATH'])
					|| ($arParams['SHOW_ITEM_TAGS'] != 'N' && !empty($arItem['TAGS']))
				):?>
				<div class="search-item-meta">
					<?php if (
						$arParams['SHOW_RATING'] == 'Y'
						&& $arItem['RATING_TYPE_ID'] <> ''
						&& $arItem['RATING_ENTITY_ID'] > 0
					):?>
					<div class="search-item-rate">
					<?php
					$APPLICATION->IncludeComponent(
						'bitrix:rating.vote', $arParams['RATING_TYPE'],
						[
							'ENTITY_TYPE_ID' => $arItem['RATING_TYPE_ID'],
							'ENTITY_ID' => $arItem['RATING_ENTITY_ID'],
							'OWNER_ID' => $arItem['USER_ID'],
							'USER_VOTE' => $arItem['RATING_USER_VOTE_VALUE'],
							'USER_HAS_VOTED' => $arItem['RATING_USER_VOTE_VALUE'] == 0 ? 'N' : 'Y',
							'TOTAL_VOTES' => $arItem['RATING_TOTAL_VOTES'],
							'TOTAL_POSITIVE_VOTES' => $arItem['RATING_TOTAL_POSITIVE_VOTES'],
							'TOTAL_NEGATIVE_VOTES' => $arItem['RATING_TOTAL_NEGATIVE_VOTES'],
							'TOTAL_VALUE' => $arItem['RATING_TOTAL_VALUE'],
							'PATH_TO_USER_PROFILE' => $arParams['~PATH_TO_USER_PROFILE'],
						],
						$component,
						['HIDE_ICONS' => 'Y']
					);?>
					</div>
					<?php endif;?>
					<?php if ($arParams['SHOW_ITEM_TAGS'] != 'N' && !empty($arItem['TAGS'])):?>
						<div class="search-item-tags"><label><?php echo GetMessage('CT_BSP_ITEM_TAGS')?>: </label><?php
						foreach ($arItem['TAGS'] as $tags):
							?><a href="<?=$tags['URL']?>"><?=$tags['TAG_NAME']?></a> <?php
						endforeach;
						?></div>
					<?php endif;?>

					<?php if ($arParams['SHOW_ITEM_DATE_CHANGE'] != 'N'):?>
						<div class="search-item-date"><label><?php echo GetMessage('CT_BSP_DATE_CHANGE')?>: </label><span><?php echo $arItem['DATE_CHANGE']?></span></div>
					<?php endif;?>
				</div>
				<?php endif?>
			</div>
		<?php endforeach;?>
		<?php echo ($arParams['DISPLAY_BOTTOM_PAGER'] != 'N') ? $arResult['NAV_STRING'] : '';?>
		<?php if ($arParams['SHOW_ORDER_BY'] != 'N'):?>
			<div class="search-sorting"><label><?php echo GetMessage('CT_BSP_ORDER')?>:</label>&nbsp;
			<?php if ($arResult['REQUEST']['HOW'] == 'd'):?>
				<a href="<?=$arResult['URL']?>&amp;how=r"><?=GetMessage('CT_BSP_ORDER_BY_RANK')?></a>&nbsp;<b><?=GetMessage('CT_BSP_ORDER_BY_DATE')?></b>
			<?php else:?>
				<b><?=GetMessage('CT_BSP_ORDER_BY_RANK')?></b>&nbsp;<a href="<?=$arResult['URL']?>&amp;how=d"><?=GetMessage('CT_BSP_ORDER_BY_DATE')?></a>
			<?php endif;?>
			</div>
		<?php endif;?>
	<?php else:?>
		<?php ShowNote(GetMessage('CT_BSP_NOTHING_TO_FOUND'));?>
	<?php endif;?>

	</div>
</div>
