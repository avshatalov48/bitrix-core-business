<?
/** CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

global $APPLICATION;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CJSCore::Init(array('report', 'socnetlogdest'));

$GLOBALS['APPLICATION']->SetTitle(GetMessage('REPORT_LIST'));

$ownerId = htmlspecialcharsbx(strtolower($arResult['OWNER_ID']));
$containerID = 'reports_list_table_'.$ownerId;
$jsClass = 'ReportListClass_'.$arResult['randomString'];

$bCrmViewTarget = defined('CRM_REPORT_UPDATE_14_5_2_MESSAGE') ?
	CRM_REPORT_UPDATE_14_5_2_MESSAGE === 'Y' : false;
if($arResult['NEED_DISPLAY_UPDATE_14_5_2_MESSAGE']):
	if ($bCrmViewTarget)
		$this->SetViewTarget('REPORT_UPDATE_14_5_2_MESSAGE'); ?>
	<div style="font-size: 14px; color: #4A4A4A; background-color: #DBE7C4; border: 1px solid #D7D7D7;
		border-radius: 4px; padding: 12px; margin: 0 0 16px 0; clear: both;">
	<?=GetMessage('REPORT_UPDATE_14_5_2_MESSAGE')?>
	</div>

	<?if ($bCrmViewTarget)
		$this->EndViewTarget();
	endif;
	unset($bCrmViewTarget); ?>

<div class="reports-list-wrap">
	<div class="reports-list" id="<?=htmlspecialcharsbx($containerID)?>">
		<div class="reports-list-left-corner"></div>
		<div class="reports-list-right-corner"></div>
		<style>
			.reports-list-table th:hover {
				cursor: default;
			}
		</style>

		<? if(!empty($arParams['REPORT_TITLE'])): ?>
			<div class="report-entity-title report-entity-title-blue">
				<?= htmlspecialcharsbx($arParams['REPORT_TITLE']) ?>
			</div>
		<? endif ?>

		<? if (!empty($arResult['SHARED_REPORT'])): ?>
			<div class="report-table-title"><?= GetMessage('REPORT_COMPANY_TITLE')?></div>
			<table cellspacing="0" class="reports-list-table"
					id="reports-company-<?=$ownerId?>">
				<tr>
					<th class="reports-first-column reports-head-cell-top" colspan="2">
						<div class="reports-head-cell">
							<span class="reports-head-cell-title">
								<?=GetMessage('REPORT_TABLE_TITLE')?>
							</span>
						</div>
					</th>
					<th class="reports-second-column">
						<div class="reports-head-cell">
							<span class="reports-head-cell-title">
								<?=GetMessage('REPORT_TABLE_CREATE_BY')?>
							</span>
						</div>
					</th>
					<th class="reports-last-column">
						<div class="reports-head-cell">
							<span class="reports-head-cell-title">
								<?=GetMessage('REPORT_TABLE_CREATE_DATE')?>
							</span>
						</div>
					</th>
				</tr>
				<? foreach($arResult['SHARED_REPORT'] as $listItem): ?>
					<?
						$accessMark = '';
						switch($listItem['RIGHTS'])
						{
							case Bitrix\Report\RightsManager::ACCESS_READ:
								$accessMark = 'r';
								break;
							case Bitrix\Report\RightsManager::ACCESS_EDIT:
								$accessMark = 'e';
								break;
							case Bitrix\Report\RightsManager::ACCESS_FULL:
								$accessMark = 'f';
								break;
						}
					?>
					<tr class="reports-list-item" data-item="<?=$listItem['CREATED_BY']?>">
						<td class="reports-first-column">
							<a title="<?=htmlspecialcharsbx(strip_tags($listItem['DESCRIPTION']))?>"
								href="<?=CComponentEngine::MakePathFromTemplate(
									$arParams["PATH_TO_REPORT_VIEW"],
									array("report_id" => $listItem['ID']));?>" class="reports-title-link">
								<?=htmlspecialcharsbx($listItem['TITLE'])?>
							</a>
						</td>
						<td class="reports-menu-column">
							<a id="rmb-<?=$listItem['ID'].$accessMark?>" href="#" class="reports-menu-button">
								<i class="reports-menu-button-icon"></i>
							</a>
						</td>
						<td class="reports-second-column">
							<?=htmlspecialcharsbx($listItem['CREATED_BY_FULL'])?>
						</td>
						<td  class="reports-date-column reports-last-column">
							<?= ($listItem['CREATED_DATE'] instanceof \Bitrix\Main\Type\DateTime ||
								$listItem['CREATED_DATE'] instanceof \Bitrix\Main\Type\Date) ?
								FormatDate($arResult['dateFormat'],
									$listItem['CREATED_DATE']->getTimestamp()) : '' ?>
						</td>
					</tr>
				<? endforeach; ?>
			</table>

		<? endif; ?>

		<? if (empty($arResult['list'])): ?>

			<?=GetMessage('REPORT_EMPTY_LIST')?><br/><br/>

			<form action="" method="POST">
				<?=bitrix_sessid_post();?>
				<input type="hidden" name="CREATE_DEFAULT" value="1" />
				<input type="hidden" name="HELPER_CLASS"
					value="<?=htmlspecialcharsbx($arResult['HELPER_CLASS'])?>" />
				<input type="submit" value="<?=GetMessage('REPORT_CREATE_DEFAULT')?>" />
			</form>

		<? else: ?>

			<? if($arResult['list']['personal']): ?>
				<div class="report-table-title"><?= GetMessage('REPORT_PERSONAL_TITLE')?></div>
				<table cellspacing="0" class="reports-list-table">
					<tr>
						<th class="reports-first-column reports-head-cell-top" colspan="2">
							<div class="reports-head-cell">
							<span class="reports-head-cell-title">
								<?=GetMessage('REPORT_TABLE_TITLE')?>
							</span>
							</div>
						</th>
						<th class="reports-second-column">
							<div class="reports-head-cell">
							<span class="reports-head-cell-title">
							</span>
							</div>
						</th>
						<th class="reports-last-column">
							<div class="reports-head-cell">
							<span class="reports-head-cell-title">
								<?=GetMessage('REPORT_TABLE_CREATE_DATE')?>
							</span>
							</div>
						</th>
					</tr>
					<? foreach($arResult['list']['personal'] as $listItem): ?>
						<tr class="reports-list-item">
							<td class="reports-first-column">
								<a title="<?=htmlspecialcharsbx(strip_tags($listItem['DESCRIPTION']))?>"
									href="<?=CComponentEngine::MakePathFromTemplate(
										$arParams["PATH_TO_REPORT_VIEW"],
										array("report_id" => $listItem['ID']));?>" class="reports-title-link">
									<?=htmlspecialcharsbx($listItem['TITLE'])?>
								</a>
							</td>
							<td class="reports-menu-column">
								<a id="rmb-<?=$listItem['ID']?>" href="#" class="reports-menu-button">
									<i class="reports-menu-button-icon"></i>
								</a>
							</td>
							<td class="reports-second-column">
							</td>
							<td  class="reports-date-column reports-last-column">
								<?= ($listItem['CREATED_DATE'] instanceof \Bitrix\Main\Type\DateTime ||
									$listItem['CREATED_DATE'] instanceof \Bitrix\Main\Type\Date) ?
									FormatDate($arResult['dateFormat'],
										$listItem['CREATED_DATE']->getTimestamp()) : '' ?>
							</td>
						</tr>
					<? endforeach; ?>
				</table>
			<? endif ?>

			<? if($arResult['list']['default']): ?>
			<div class="report-table-title"><?= GetMessage('REPORT_DEFAULT_TITLE')?></div>
			<table cellspacing="0" class="reports-list-table">
				<tr>
					<th class="reports-first-column reports-head-cell-top" colspan="2">
						<div class="reports-head-cell">
							<span class="reports-head-cell-title">
								<?=GetMessage('REPORT_TABLE_TITLE')?>
							</span>
						</div>
					</th>
					<th class="reports-second-column">
						<div class="reports-head-cell">
							<span class="reports-head-cell-title">
							</span>
						</div>
					</th>
					<th class="reports-last-column">
						<div class="reports-head-cell">
							<span class="reports-head-cell-title">
								<?=GetMessage('REPORT_TABLE_CREATE_DATE')?>
							</span>
						</div>
					</th>
				</tr>
				<? foreach($arResult['list']['default'] as $listItem): ?>
					<?
					$defaultMark = '';
					if (isset($listItem['MARK_DEFAULT']))
					{
						$markNum = intval($listItem['MARK_DEFAULT']);
						if ($markNum > 0)
							$defaultMark = 'd'.$markNum;
						unset($markNum);
					}
					?>
					<tr class="reports-list-item">
						<td class="reports-first-column">
							<a title="<?=htmlspecialcharsbx(strip_tags($listItem['DESCRIPTION']))?>"
								href="<?=CComponentEngine::MakePathFromTemplate(
									$arParams["PATH_TO_REPORT_VIEW"],
									array("report_id" => $listItem['ID']));?>" class="reports-title-link">
								<?=htmlspecialcharsbx($listItem['TITLE'])?>
							</a>
						</td>
						<td class="reports-menu-column">
							<a id="rmb-<?=$listItem['ID'].$defaultMark?>" href="#" class="reports-menu-button">
								<i class="reports-menu-button-icon"></i>
							</a>
						</td>
						<td class="reports-second-column">
						</td>
						<td  class="reports-date-column reports-last-column">
							<?= ($listItem['CREATED_DATE'] instanceof \Bitrix\Main\Type\DateTime ||
								$listItem['CREATED_DATE'] instanceof \Bitrix\Main\Type\Date) ?
								FormatDate($arResult['dateFormat'],
									$listItem['CREATED_DATE']->getTimestamp()) : '' ?>
						</td>
					</tr>
				<? endforeach; ?>
			</table>
			<? endif ?>

		<? endif; ?>
	</div>
</div>

<script type="text/javascript">
	BX(function () {

		BX.Report['<?=$jsClass?>'] = new BX.Report.ReportListClass({
			jsClass:'<?=$jsClass?>',
			containerId:'<?=CUtil::JSEscape($containerID)?>',
			ownerId: '<?=$ownerId?>',
			sessionError: '<?= !empty($_SESSION['REPORT_LIST_ERROR']) ? true : false ?>',
			editUrl:'<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_REPORT_CONSTRUCT'],
				array('report_id' => 'REPORT_ID', 'action' => 'edit'))?>',
			deleteUrl:'<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_REPORT_CONSTRUCT'],
				array('report_id' => 'REPORT_ID', 'action' => 'delete'));?>',
			copyUrl:'<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_REPORT_CONSTRUCT'],
				array('report_id' => 'REPORT_ID', 'action' => 'copy'));?>',
			deleteConfirmUrl:'<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_REPORT_CONSTRUCT'],
				array('report_id' => 'REPORT_ID', 'action' => 'delete_confirmed'));?>'
		});

		BX.message({
			REPORT_COPY_SHORT: '<?=GetMessageJS("REPORT_COPY_SHORT")?>',
			REPORT_COPY_FULL: '<?=GetMessageJS("REPORT_COPY_FULL")?>',
			REPORT_EDIT_SHORT: '<?=GetMessageJS("REPORT_EDIT_SHORT")?>',
			REPORT_EDIT_FULL: '<?=GetMessageJS("REPORT_EDIT_FULL")?>',
			REPORT_SHARING_SHORT: '<?=GetMessageJS("REPORT_SHARING_SHORT")?>',
			REPORT_SHARING_FULL: '<?=GetMessageJS("REPORT_SHARING_FULL")?>',
			REPORT_DELETE_SHORT: '<?=GetMessageJS("REPORT_DELETE_SHORT")?>',
			REPORT_DELETE_FULL: '<?=GetMessageJS("REPORT_DELETE_FULL")?>',
			REPORT_DELETE_CONFIRM: '<?=GetMessageJS("REPORT_DELETE_CONFIRM")?>',
			REPORT_LIST_BTN_SAVE: '<?=GetMessageJS("REPORT_LIST_BTN_SAVE")?>',
			REPORT_LIST_BTN_CLOSE: '<?=GetMessageJS("REPORT_LIST_BTN_CLOSE")?>',
			REPORT_LIST_SHARING_TITLE_POPUP: '<?=GetMessageJS("REPORT_LIST_SHARING_TITLE_POPUP")?>',
			REPORT_LIST_SHARING_OWNER: '<?=GetMessageJS("REPORT_LIST_SHARING_OWNER")?>',
			REPORT_LIST_SHARING_NAME_RIGHTS_USER:'<?=GetMessageJS("REPORT_LIST_SHARING_NAME_RIGHTS_USER")?>',
			REPORT_LIST_SHARING_NAME_RIGHTS:'<?=GetMessageJS("REPORT_LIST_SHARING_NAME_RIGHTS")?>',
			REPORT_LIST_SHARING_NAME_ADD_RIGHTS_USER:
				'<?=GetMessageJS("REPORT_LIST_SHARING_NAME_ADD_RIGHTS_USER")?>',
			REPORT_EXPORT_SHORT:'<?=GetMessageJS("REPORT_EXPORT_SHORT")?>',
			REPORT_EXPORT_FULL:'<?=GetMessageJS("REPORT_EXPORT_FULL")?>',
			REPORT_IMPORT_TITLE:'<?=GetMessageJS("REPORT_IMPORT_TITLE")?>',
			REPORT_IMPORT_BUTTON_TEXT:'<?=GetMessageJS("REPORT_IMPORT_BUTTON_TEXT")?>',
			REPORT_IMPORT_ERROR_UPLOADED_FILE:'<?=GetMessageJS("REPORT_IMPORT_ERROR_UPLOADED_FILE")?>',
			REPORT_IMPORT_ERROR_FILE_EXT:'<?=GetMessageJS("REPORT_IMPORT_ERROR_FILE_EXT")?>',
			REPORT_IMPORT_DESCRIPTION:'<?=GetMessageJS("REPORT_IMPORT_DESCRIPTION")?>'
		});

	});
</script>

<?if(!defined('REPORT_LIST_ERROR') && !empty($_SESSION['REPORT_LIST_ERROR'])):?>
	<? define("REPORT_LIST_ERROR", true); ?>
	<div id="report-list-error" style="display: none;"><?=$_SESSION['REPORT_LIST_ERROR']?></div>
	<? unset($_SESSION['REPORT_LIST_ERROR']); ?>
<? endif ?>

<? if (!defined("REPORT_LIST_CREATE_BUTTON")):
define("REPORT_LIST_CREATE_BUTTON", true);?>
<div id="form-container" style="display: none;">

</div>
<? $this->SetViewTarget("pagetitle", 100);?>
	<a class="webform-small-button webform-small-button-blue"
		onclick="BX.Report['<?=$jsClass?>'].import()">
		<span class="webform-small-button-text"><?=GetMessage('REPORT_IMPORT_BUTTON')?></span>
	</a>

	<a class="webform-small-button webform-small-button-blue webform-small-button-add"
		href="<?=CComponentEngine::MakePathFromTemplate(
					$arParams["PATH_TO_REPORT_CONSTRUCT"],
					array("report_id" => 0, 'action' => 'create'));?>
	">
		<span class="webform-small-button-icon"></span>
		<span class="webform-small-button-text"><?=GetMessage('REPORT_ADD')?></span>
	</a>
<?

$this->EndViewTarget();
endif;