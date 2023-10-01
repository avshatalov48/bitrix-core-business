<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui',
	'ui.buttons',
	'ui.icons',
	'ui.alerts',
	"ui.viewer",
	"ui.fonts.opensans",
	'ui.buttons.icons',
	'ui.entity-selector',
]);
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/bizproc.workflow.faces/templates/.default/style.css');

if (IsModuleInstalled('crm'))
{
	CJSCore::Init('sidepanel');
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
}

if ($arResult["FatalErrorMessage"] <> '')
{
	?>
	<div class="bp-errortext">
		<?= $arResult["FatalErrorMessage"] ?>
	</div>
	<?
}
else
{
	if ($arResult["ErrorMessage"] <> '')
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
			$noPopup = (
				$record['data']['ACTIVITY'] == 'RequestInformationActivity'
				|| $record['data']['ACTIVITY'] == 'RequestInformationOptionalActivity'
				|| $record['data']['MODULE_ID'] == 'rpa'
			);

			$popupJs = 'return BX.Bizproc.showTaskPopup('.$record['data']['ID'].', () => BX.Bizproc.Component.TaskList.Instance.reloadGrid(), '.(int)$arResult['TARGET_USER_ID'].', this)';
			$taskHref = $record['data']['URL']['TASK'];

			$attrs = 'href="#" onclick="'.$popupJs.'"';
			if ($noPopup)
			{
				$attrs = 'href="'.$taskHref.'"';
			}

			if ($record['data']["DOCUMENT_URL"] <> '' && $record['data']["DOCUMENT_NAME"] <> '')
			{
				$arResult["RECORDS"][$key]['data']['DOCUMENT_NAME'] = '<a href="'.$record['data']["DOCUMENT_URL"].'" class="bp-folder-title-link">'.$record['data']['DOCUMENT_NAME'].'</a>';
			}
			$arResult["RECORDS"][$key]['data']['COMMENTS'] = '<div class="bp-comments"><a '.$attrs.'><span class="bp-comments-icon"></span>'
				.(!empty($arResult["COMMENTS_COUNT"]['WF_'.$record['data']["WORKFLOW_ID"]]) ? (int) $arResult["COMMENTS_COUNT"]['WF_'.$record['data']["WORKFLOW_ID"]] : '0')
				.'</a></div>';

			$arResult["RECORDS"][$key]['data']["NAME"] = '<span class="bp-task"><a '.$attrs.' title="'.$record['data']["NAME"].'">'.$record['data']["NAME"].'</a></span>';
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
						$isDecline =
							$control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No
							|| $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel
						;
						$class = $isDecline ? 'danger' : 'success';
						$icon = $isDecline ? 'cancel' : 'done';
						$props = CUtil::PhpToJSObject(array(
							'TASK_ID' => $record['data']['ID'],
							$control['NAME'] => $control['VALUE']
						));

						$safeGridId = htmlspecialcharsbx(CUtil::JSEscape($arResult['GRID_ID']));
						$arResult["RECORDS"][$key]['data']["NAME"] .= '<a href="#" onclick="return BX.Bizproc.doInlineTask('
							. $props
							. ', function(){'
							. "BX.Main.gridManager.reload('${safeGridId}');"
							. '}, this)" class="ui-btn ui-btn-' . $class . ' ui-btn-icon-' . $icon
							. '">'
							. $control['TEXT']
							. '</a>';
					}
					$arResult["RECORDS"][$key]['data']["NAME"] .= '</div>';
				}
				else
				{
					$anchor = '<a ' . $attrs . ' class="ui-btn ui-btn-primary">' . GetMessage("BPATL_BEGIN") . '</a>';
					if ($record['data']['ACTIVITY'] == 'RequestInformationActivity' || $record['data']['ACTIVITY'] == 'RequestInformationOptionalActivity')
					{
						$anchor = '<a href="'
							. $record['data']['URL']['TASK']
							. '" class="ui-btn ui-btn-primary">'
							. GetMessage("BPATL_BEGIN")
							. '</a>';
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

			if (array_key_exists("DESCRIPTION", $arResult["RECORDS"][$key]['data']))
			{
				$arResult["RECORDS"][$key]['data']["DESCRIPTION"] = \CBPViewHelper::prepareTaskDescription(
					$arResult["RECORDS"][$key]['data']["DESCRIPTION"]
				);
			}
		}
	}

	$actionHtml = '';
	$actionList = array();
	$showActions = \CBPHelper::getBool($arParams['SHOW_GROUP_ACTIONS'] ?? 'Y');

	if ($showActions && $arResult['IS_MY_TASKS'] && empty($arResult['IS_COMPLETED']))
	{
		$actionList['set_status_'.CBPTaskUserStatus::Yes] = GetMessage("BPATL_GROUP_ACTION_YES");
		$actionList['set_status_'.CBPTaskUserStatus::No] = GetMessage("BPATL_GROUP_ACTION_NO");
		$actionList['set_status_'.CBPTaskUserStatus::Ok] = GetMessage("BPATL_GROUP_ACTION_OK");
	}
	if ($showActions && $arResult['USE_SUBORDINATION'] && empty($arResult['IS_COMPLETED']))
	{
		$actionList['delegate_to'] = GetMessage("BPATL_GROUP_ACTION_DELEGATE");
	}

	if (isset($actionList['delegate_to']))
	{
		$actionHtml .= '<div id="ACTION_DELEGATE_TO_WRAPPER" style="display:none;"></div>';
	}

	$addToolbar = function() use ($arResult)
	{
		$filterParams = [
			'FILTER_ID' => $arResult['FILTER_ID'],
			'GRID_ID' => $arResult['GRID_ID'],
			'FILTER' => $arResult['FILTER'],
			'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
			'ENABLE_LABEL' => true,
			'DISABLE_SEARCH' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => \Bitrix\Main\UI\Filter\Theme::MUTED,
		];

		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterParams);
	};

	$addToolbar();
	$gridParams = [
		'GRID_ID' => $arResult['GRID_ID'],
		'COLUMNS' => $arResult['COLUMNS'],
		'NAV_OBJECT' => $arResult['NAV_RESULT'],
		'SORT' => $arResult['SORT'],
		'ROWS' => $arResult['RECORDS'],
		'TOTAL_ROWS_COUNT' => $arResult['ROWS_COUNT'],
		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',
		'SHOW_ROW_CHECKBOXES' => true,
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'SHOW_PAGESIZE' => true,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_INLINE_EDIT' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'HANDLE_RESPONSE_ERROR' => true,
		'MESSAGES' => array_map(
			fn ($message) => [
				'TEXT' => $message,
				'TYPE' => 'error',
			],
			$arResult['ERRORS'] ?? [],
		),
	];

	if ($actionList)
	{
		$snippet = new \Bitrix\Main\Grid\Panel\Snippet();

		$dropdownButtonItems = [];
		foreach ($actionList as $value => $name)
		{
			$action = '';
			if ($value === 'delegate_to')
			{
				$action = 'BX("ACTION_DELEGATE_TO_WRAPPER").style.display = ""';
			}
			else
			{
				$action = 'BX("ACTION_DELEGATE_TO_WRAPPER").style.display = "none"';
			}

			$dropdownButtonItems[] = [
				'NAME' => $name,
				'VALUE' => $value,
				'ONCHANGE' => [
					[
						'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
						'DATA' => [
							[
								'JS' => $action,
							],
						],
					]
				],
			];
		}

		$gridParams['SHOW_ACTION_PANEL'] = true;
		$gridParams['ACTION_PANEL'] = [
			'GROUPS' => [
				[
					'ITEMS' => [
						$snippet->getForAllCheckbox(),
						[
							'TYPE' => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
							'NAME' => 'action_button_' . $arResult['GRID_ID'],
							'MULTIPLE' => 'N',
							'ITEMS' => $dropdownButtonItems,
						],
						[
							'TYPE' => \Bitrix\Main\Grid\Panel\Types::CUSTOM,
							'NAME' => 'ACTION_DELEGATE_TO_ID',
							'VALUE' => $actionHtml,
						],
						$snippet->getApplyButton([
							'ONCHANGE' => [
								[
									'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
									'DATA' => [
										[
											'JS' => 'BX.Bizproc.Component.TaskList.Instance.applyActionPanelValues()',
										],
									],
								]
							],
						]),
					],
				],
			],
		];

		$gridParams['EDITABLE'] = true;
	}

	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
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
<script>
	BX.ready(function()
	{
		<?php
		\Bitrix\Main\UI\Extension::load(['sidepanel']);
		?>
		BX.SidePanel.Instance.bindAnchors({
			rules:
				[
					{
						condition: [
							"/rpa/task/",
						],
						options: {
							width: 580,
							cacheable: false,
							allowChangeHistory: false
						},
					}
				]
		});

		const gridId = '<?=CUtil::JSEscape($arResult['GRID_ID'])?>';

		BX.Bizproc.Component.TaskList.Instance = new BX.Bizproc.Component.TaskList({
			gridId: gridId,
		});

		BX.Bizproc.Component.TaskList.Instance.init();
		BX.addCustomEvent('Grid::updated', () => BX.Bizproc.Component.TaskList.Instance.init());
	});
</script>
