<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!empty($_REQUEST['action_button_'.$arResult["GRID_ID"]]))
{
	//@TODO remake
	unset($_REQUEST['bxajaxid'], $_REQUEST['AJAX_CALL']);
}
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/bizproc.workflow.faces/templates/.default/style.css');

if (IsModuleInstalled('crm'))
{
	CJSCore::Init('sidepanel');
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
}

if (strlen($arResult["FatalErrorMessage"]) > 0)
{
	?>
	<div class="bp-errortext">
		<?= $arResult["FatalErrorMessage"] ?>
	</div>
	<?
}
else
{
	?>
	<div class="bp-interface-toolbar-container">
		<div class="bp-interface-toolbar">
			<table cellpadding="0" cellspacing="0" border="0" class="" style="width: 100%;">
				<tbody>
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" border="0">
							<tbody>
							<tr>
								<?foreach ($arResult['DOCUMENT_TYPES'] as $uid => $dt):?>
								<td>
									<a href="<?=$APPLICATION->GetCurPage().($uid!='*'?'?type='.$uid:'')?>" hidefocus="true" class="bp-context-button <?=!empty($dt['ACTIVE'])?'active':''?>">
										<span class="bp-context-button-text"><?=htmlspecialcharsbx($dt['NAME'])?></span>
										<?if (empty($dt['FILTER']) && !empty($arResult["COUNTERS"]['*'])):?>
										<span class="bp-context-button-notice"><?=$arResult["COUNTERS"]['*']?></span>
										<?elseif (!empty($dt['FILTER']['ENTITY']) && !empty($arResult["COUNTERS"][$dt['FILTER']['MODULE_ID']][$dt['FILTER']['ENTITY']])):?>
										<span class="bp-context-button-notice"><?=$arResult["COUNTERS"][$dt['FILTER']['MODULE_ID']][$dt['FILTER']['ENTITY']]?></span>
										<?elseif (empty($dt['FILTER']['ENTITY']) && !empty($arResult["COUNTERS"][$dt['FILTER']['MODULE_ID']]['*'])):?>
										<span class="bp-context-button-notice"><?=$arResult["COUNTERS"][$dt['FILTER']['MODULE_ID']]['*']?></span>
										<?endif?>
									</a>
								</td>
								<?endforeach;?>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?

	if (strlen($arResult["ErrorMessage"]) > 0)
	{
		?>
		<div class="bp-errortext">
			<p><?= $arResult["ErrorMessage"] ?></p>
		</div>
	<?
	}

	if (is_array($arResult["RECORDS"]))
	{
		foreach ($arResult["RECORDS"] as $key => $record)
		{
			$popupJs = 'return BX.Bizproc.showTaskPopup('.$record['data']['ID'].', function(){window[\'bxGrid_'.$arResult["GRID_ID"].'\'].Reload()}, '.(int)$arResult['TARGET_USER_ID'].', this)';

			if (strlen($record['data']["DOCUMENT_URL"]) > 0 && strlen($record['data']["DOCUMENT_NAME"]) > 0)
			{
				$arResult["RECORDS"][$key]['data']['DOCUMENT_NAME'] = '<a href="'.$record['data']["DOCUMENT_URL"].'" class="bp-folder-title-link">'.$record['data']['DOCUMENT_NAME'].'</a>';
			}
			$arResult["RECORDS"][$key]['data']['COMMENTS'] = '<div class="bp-comments"><a onclick="'.$popupJs.'"><span class="bp-comments-icon"></span>'
				.(!empty($arResult["COMMENTS_COUNT"]['WF_'.$record['data']["WORKFLOW_ID"]]) ? (int) $arResult["COMMENTS_COUNT"]['WF_'.$record['data']["WORKFLOW_ID"]] : '0')
				.'</a></div>';

			$arResult["RECORDS"][$key]['data']["NAME"] = '<span class="bp-task"><a href="#" onclick="'.$popupJs.'" title="'.$record['data']["NAME"].'">'.$record['data']["NAME"].'</a></span>';
			if ($record['data']['IS_MY'])
			{
				if ($record['data']['USER_STATUS'] > CBPTaskUserStatus::Waiting)
				{
					switch($record['data']['USER_STATUS'])
					{
						case CBPTaskUserStatus::Yes:
							$arResult["RECORDS"][$key]['data']["NAME"] .= '<span class="bp-status-ready">'.GetMessage('BPATL_USER_STATUS_YES').'</span>';
							break;
						case CBPTaskUserStatus::No:
						case CBPTaskUserStatus::Cancel:
							$arResult["RECORDS"][$key]['data']["NAME"] .= '<span class="bp-status-cancel">'.GetMessage('BPATL_USER_STATUS_NO').'</span>';
							break;
						default:
							$arResult["RECORDS"][$key]['data']["NAME"] .= '<span class="bp-status-ready">'.GetMessage('BPATL_USER_STATUS_OK').'</span>';
					}
				}
				elseif ($record['data']['IS_INLINE'] == 'Y')
				{
					$arResult["RECORDS"][$key]['data']["NAME"] .= '<div class="bp-btn-panel">';
					$controls = CBPDocument::getTaskControls($record['data']);
					foreach ($controls['BUTTONS'] as $control)
					{
						$class = $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No || $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel ? 'decline' : 'accept';
						$props = CUtil::PhpToJSObject(array(
							'TASK_ID' => $record['data']['ID'],
							$control['NAME'] => $control['VALUE']
						));

						$arResult["RECORDS"][$key]['data']["NAME"] .= '<a href="#" onclick="return BX.Bizproc.doInlineTask('
							.$props.', function(){window[\'bxGrid_'.$arResult["GRID_ID"].'\'].Reload()}, this)" class="bp-button bp-button bp-button-'
							.$class.'"><span class="bp-button-icon"></span><span class="bp-button-text">'.$control['TEXT'].'</span></a>';
					}
					$arResult["RECORDS"][$key]['data']["NAME"] .= '</div>';
				}
				else
				{
					$anchor = '<a href="#" class="bp-button bp-button bp-button-blue" onclick="'.$popupJs.'">'.GetMessage("BPATL_BEGIN").'</a>';
					if ($record['data']['ACTIVITY'] == 'RequestInformationActivity' || $record['data']['ACTIVITY'] == 'RequestInformationOptionalActivity')
					{
						$anchor = '<a href="'.$record['data']['URL']['TASK'].'" class="bp-button bp-button bp-button-blue">'.GetMessage("BPATL_BEGIN").'</a>';
					}

					$arResult["RECORDS"][$key]['data']["NAME"] .= '<div class="bp-btn-panel">'.$anchor.'</div>';
				}
			}
			else
			{
				$arResult["RECORDS"][$key]['data']["NAME"] .= '<span class="bp-status"><span class="bp-status-inner"><span>'.$record['data']["WORKFLOW_STATE"].'</span></span></span>';
			}

			$arResult["RECORDS"][$key]['data']['WORKFLOW_PROGRESS'] = '';
			if (empty($arResult['HIDE_WORKFLOW_PROGRESS']))
			{
				ob_start();
				$APPLICATION->IncludeComponent(
					"bitrix:bizproc.workflow.faces",
					"",
					array(
						"WORKFLOW_ID"    => $record['data']["WORKFLOW_ID"],
						'TARGET_TASK_ID' => $record['data']['ID']
					),
					$component
				);
				$arResult["RECORDS"][$key]['data']['WORKFLOW_PROGRESS'] = ob_get_clean();
			}
		}
	}

	$actionHtml = '';
	$actionList = array();
	if ($arResult['IS_MY_TASKS'] && empty($arResult['IS_COMPLETED']))
	{
		$actionList['set_status_'.CBPTaskUserStatus::Yes] = GetMessage("BPATL_GROUP_ACTION_YES");
		$actionList['set_status_'.CBPTaskUserStatus::No] = GetMessage("BPATL_GROUP_ACTION_NO");
		$actionList['set_status_'.CBPTaskUserStatus::Ok] = GetMessage("BPATL_GROUP_ACTION_OK");
	}
	if ($arResult['USE_SUBORDINATION'] && empty($arResult['IS_COMPLETED']))
		$actionList['delegate_to'] = GetMessage("BPATL_GROUP_ACTION_DELEGATE");

	if (isset($actionList['delegate_to']))
	{
		ob_start();
		CBPViewHelper::RenderUserSearch(
			"ACTION_DELEGATE_TO",
			"ACTION_DELEGATE_TO_SEARCH",
			"ACTION_DELEGATE_TO_ID",
			"ACTION_DELEGATE_TO",
			SITE_ID,
			$arParams['~NAME_TEMPLATE'],
			500
		);
		$actionHtml .= '<div id="ACTION_DELEGATE_TO_WRAPPER" style="display:none;">'.ob_get_clean().'</div>';

		$actionHtml .= '
		<script type="text/javascript">
			BX.ready(
				function(){
				var select = BX.findChild(BX.findPreviousSibling(BX.findParent(BX("ACTION_DELEGATE_TO_WRAPPER"), { "tagName":"td" })), { "tagName":"select" });
				BX.bind(
					select,
					"change",
					function(e){
						BX("ACTION_DELEGATE_TO_WRAPPER").style.display = select.value === "delegate_to" ? "" : "none";
					}
				)
			}
		);
		</script>';
	}

	$gridParams = array(
		"GRID_ID"=>$arResult["GRID_ID"],
		"HEADERS"=>$arResult["HEADERS"],
		"SORT"=>$arResult["SORT"],
		"ROWS"=>$arResult["RECORDS"],
		"FOOTER"=>array(array("title"=>GetMessage("BPWC_WLCT_TOTAL"), "value"=>$arResult["ROWS_COUNT"])),
		"NAV_OBJECT"=>$arResult["NAV_RESULT"],
		"AJAX_MODE"=>"Y",
		"AJAX_OPTION_JUMP"=>"Y",
		"FILTER"=>$arResult["FILTER"],
		"FILTER_PRESETS" => $arResult['FILTER_PRESETS'],
		'ERROR_MESSAGES' => $arResult['ERRORS']
	);

	if ($actionList)
	{
		$gridParams['ACTIONS'] = array(
			"list"=> $actionList,
			'custom_html' => $actionHtml
		);
		$gridParams['ACTION_ALL_ROWS'] = true;
		$gridParams['EDITABLE'] = true;
	}

	$APPLICATION->IncludeComponent(
		'bitrix:bizproc.interface.grid',
		"",
		$gridParams,
		$component
	);
	?>

	<?
	if ($arParams["SHOW_TRACKING"] == "Y")
	{
		?><h2><?=GetMessage("BPATL_FINISHED_TASKS")?></h2>
		<?
		$APPLICATION->IncludeComponent(
			"bitrix:bizproc.interface.grid",
			"",
			array(
				"GRID_ID"=>$arResult["H_GRID_ID"],
				"HEADERS"=>$arResult["H_HEADERS"],
				"SORT"=>$arResult["H_SORT"],
				"ROWS"=>$arResult["H_RECORDS"],
				"FOOTER"=>array(array("title"=>GetMessage("BPWC_WLCT_TOTAL"), "value"=>$arResult["H_ROWS_COUNT"])),
				"ACTIONS"=>array("delete"=>false, "list"=>array()),
				"ACTION_ALL_ROWS"=>false,
				"EDITABLE"=>false,
				"NAV_OBJECT"=>$arResult["H_NAV_RESULT"],
				"AJAX_MODE"=>"Y",
				"AJAX_OPTION_JUMP"=>"N",
				"FILTER"=>$arResult["H_FILTER"],
			),
			$component
		);
	}
}
?>