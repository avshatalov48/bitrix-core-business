<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ERROR']))
{
	ShowError($arResult['ERROR']);
	return false;
}

global $USER_FIELD_MANAGER;

$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/js/highloadblock/css/highloadblock.css');

//$GLOBALS['APPLICATION']->SetTitle('Highloadblock Row');

$listUrl = str_replace('#BLOCK_ID#', intval($arParams['BLOCK_ID']),	$arParams['LIST_URL']);

?>

<a href="<?=htmlspecialcharsbx($listUrl)?>"><?=GetMessage('HLBLOCK_ROW_VIEW_BACK_TO_LIST')?></a><br><br>

<div class="reports-result-list-wrap">
	<div class="report-table-wrap">
		<div class="reports-list-left-corner"></div>
		<div class="reports-list-right-corner"></div>
		<table cellspacing="0" class="reports-list-table" id="report-result-table">
			<!-- head -->
			<tr>
				<th class="reports-first-column" style="cursor: default">
					<div class="reports-head-cell"><span class="reports-head-cell-title"><?=GetMessage('HLBLOCK_ROW_VIEW_NAME_COLUMN')?></span></div>
				</th>
				<th class="reports-last-column" style="cursor: default">
					<div class="reports-head-cell"><span class="reports-head-cell-title"><?=GetMessage('HLBLOCK_ROW_VIEW_VALUE_COLUMN')?></span></div>
				</th>
			</tr>

			<tr>
				<td class="reports-first-column">ID</td>
				<td class="reports-last-column"><?=$arResult['row']['ID']?></td>
			</tr>

			<? foreach($arResult['fields'] as $field): ?>
				<? $title = $field["LIST_COLUMN_LABEL"]? $field["LIST_COLUMN_LABEL"]: $field['FIELD_NAME']; ?>
				<tr>
					<td class="reports-first-column"><?=htmlspecialcharsEx($title)?></td>
					<?
					$valign = "";
					$html = $USER_FIELD_MANAGER->getListView($field, $arResult['row'][$field['FIELD_NAME']]);
					?>
					<td class="reports-last-column"><?=$html?></td>
				</tr>
			<? endforeach; ?>
		</table>
	</div>
</div>