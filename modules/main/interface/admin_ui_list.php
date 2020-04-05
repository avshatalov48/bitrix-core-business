<?php
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Grid\Editor\Types;

class CAdminUiList extends CAdminList
{
	public $enableNextPage = false;
	public $totalRowCount = 0;

	protected $filterPresets = array();

	private $isShownContext = false;

	public function AddHeaders($aParams)
	{
		parent::AddHeaders($aParams);
		$this->SetVisibleHeaderColumn();
	}

	public function SetVisibleHeaderColumn()
	{
		$gridOptions = new Bitrix\Main\Grid\Options($this->table_id);
		$gridColumns = $gridOptions->GetVisibleColumns();
		if ($gridColumns)
		{
			$this->arVisibleColumns = array();
			$this->aVisibleHeaders = array();
			foreach ($gridColumns as $columnId)
			{
				if (isset($this->aHeaders[$columnId]) && !isset($this->aVisibleHeaders[$columnId]))
				{
					$this->arVisibleColumns[] = $columnId;
					$this->aVisibleHeaders[$columnId] = $this->aHeaders[$columnId];
				}
			}
		}
	}

	public function SetNavigationParams(\CAdminUiResult $queryObject)
	{
		$this->NavText($queryObject->GetNavPrint(""));
		$this->totalRowCount = $queryObject->NavRecordCount;
		$this->enableNextPage = $queryObject->PAGEN < $queryObject->NavPageCount;
	}

	public function setNavigation(\Bitrix\Main\UI\PageNavigation $nav, $title, $showAllways = true, $post = false)
	{
		global $APPLICATION;

		$this->totalRowCount = $nav->getRecordCount();
		$this->enableNextPage = $nav->getCurrentPage() < $nav->getPageCount();

		ob_start();

		$APPLICATION->IncludeComponent(
			"bitrix:main.pagenavigation",
			"grid",
			array(
				"NAV_OBJECT" => $nav,
				"TITLE" => $title,
				"PAGE_WINDOW" => 5,
				"SHOW_ALWAYS" => $showAllways,
				"POST" => $post,
				"TABLE_ID" => $this->table_id,
			),
			false,
			array(
				"HIDE_ICONS" => "Y",
			)
		);

		$this->NavText(ob_get_clean());
	}

	public function getNavSize()
	{
		$gridOptions = new Bitrix\Main\Grid\Options($this->table_id);
		$navParams = $gridOptions->getNavParams();
		return $navParams["nPageSize"];
	}

	public function EditAction()
	{
		if($_SERVER["REQUEST_METHOD"] == "POST" &&
			!empty($_REQUEST["action_button_".$this->table_id]) && check_bitrix_sessid())
		{
			$arrays = array(&$_POST, &$_REQUEST, &$GLOBALS);
			foreach ($arrays as $i => &$array)
			{
				if(is_array($array["FIELDS"]))
				{
					foreach ($array["FIELDS"] as $id => &$fields)
					{
						if(is_array($fields))
						{
							CUtil::decodeURIComponent($fields);
							$keys = array_keys($fields);
							foreach ($keys as $key)
							{
								if(($c = substr($key, 0, 1)) == '~' || $c == '=')
								{
									unset($arrays[$i]["FIELDS"][$id][$key]);
								}
							}
						}
					}
				}
			}
			return true;
		}
		return false;
	}

	public function GroupAction()
	{
		if (!check_bitrix_sessid())
		{
			return false;
		}

		if (is_array($_REQUEST["action"]))
		{
			foreach ($_REQUEST["action"] as $actionKey => $actionValue)
				$_REQUEST[$actionKey] = $actionValue;
		}
		if (!empty($_REQUEST["action_button_".$this->table_id]))
		{
			$_REQUEST["action"] = $_REQUEST["action_button_".$this->table_id];
		}

		if ((empty($_REQUEST["action_all_rows_".$this->table_id]) ||
				$_REQUEST["action_all_rows_".$this->table_id] === "N") && isset($_REQUEST["ID"]))
		{
			if(!is_array($_REQUEST["ID"]))
				$arID = array($_REQUEST["ID"]);
			else
				$arID = $_REQUEST["ID"];

			return $arID;
		}
		else
		{
			return array("");
		}
	}

	public function ActionDoGroup($id, $action_id, $add_params = "")
	{
		$postParams = array(
			"action_button_".$this->table_id => $action_id,
			"ID" => $id
		);
		return $this->ActionAjaxPostGrid($postParams);
	}

	public function AddGroupActionTable($arActions, $arParams = array())
	{
		$this->arActions = $arActions;
		$this->arActionsParams = $arParams;
	}

	public function ActionAjaxPostGrid($postParams)
	{
		return "BX.Main.gridManager.getById('".$this->table_id."').instance.reloadTable('POST', ".
			CUtil::PhpToJsObject($postParams).");";
	}

	public function AddFilter(array $filterFields, array &$arFilter)
	{
		$filterOption = new Bitrix\Main\UI\Filter\Options($this->table_id);
		$filterData = $filterOption->getFilter($filterFields);
		$filterable = array();
		$quickSearchKey = "";
		foreach ($filterFields as $filterField)
		{
			if (isset($filterField["quickSearch"]))
			{
				$quickSearchKey = $filterField["quickSearch"].$filterField["id"];
			}
			$filterable[$filterField["id"]] = $filterField["filterable"];
		}

		foreach ($filterData as $fieldId => $fieldValue)
		{
			if (empty($fieldValue))
			{
				continue;
			}

			if (substr($fieldId, -5) == "_from")
			{
				$realFieldId = substr($fieldId, 0, strlen($fieldId)-5);
				if (substr($realFieldId, -2) == "_1")
				{
					$arFilter[$realFieldId] = $fieldValue;
				}
				else
				{
					if (!empty($filterData[$realFieldId."_numsel"]) && $filterData[$realFieldId."_numsel"] == "more")
						$filterPrefix = ">";
					else
						$filterPrefix = ">=";
					$arFilter[$filterPrefix.$realFieldId] = trim($fieldValue);
				}
			}
			elseif (substr($fieldId, -3) == "_to")
			{
				$realFieldId = substr($fieldId, 0, strlen($fieldId)-3);
				if (substr($realFieldId, -2) == "_1")
				{
					$realFieldId = substr($realFieldId, 0, strlen($realFieldId)-2);
					$arFilter[$realFieldId."_2"] = $fieldValue;
				}
				else
				{
					if (!empty($filterData[$realFieldId."_numsel"]) && $filterData[$realFieldId."_numsel"] == "less")
						$filterPrefix = "<";
					else
						$filterPrefix = "<=";
					$arFilter[$filterPrefix.$realFieldId] = trim($fieldValue);
				}
			}
			else
			{
				if (array_key_exists($fieldId, $filterable))
				{
					$filterPrefix = $filterable[$fieldId];
					$arFilter[$filterPrefix.$fieldId] = $fieldValue;
				}
				if ($fieldId == "FIND" && trim($fieldValue) && $quickSearchKey)
				{
					$arFilter[$quickSearchKey] = $fieldValue;
				}
			}
		}
	}

	public function AddAdminContextMenu($aContext=array(), $bShowExcel=true, $bShowSettings=true)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$aAdditionalMenu = array();

		if ($bShowExcel)
		{
			$link = DeleteParam(array("mode"));
			$link = $APPLICATION->GetCurPage()."?mode=excel".($link <> ""? "&".$link:"");
			$aAdditionalMenu[] = array(
				"TEXT"=>"Excel",
				"TITLE"=>GetMessage("admin_lib_excel"),
				"ONCLICK"=>"location.href='".htmlspecialcharsbx($link)."'",
				"GLOBAL_ICON"=>"adm-menu-excel",
			);
		}

		if (count($aContext) > 0 || count($aAdditionalMenu) > 0)
			$this->context = new CAdminUiContextMenu($aContext, $aAdditionalMenu);
	}

	//TODO Finalize the function so that it can create a structure of any complexity.
	public function GetGroupAction()
	{
		if (empty($this->arActions))
		{
			return array();
		}

		$actionPanel = array();

		$snippet = new Bitrix\Main\Grid\Panel\Snippet();

		$actionList = array(array("NAME" => GetMessage("admin_lib_list_actions"), "VALUE" => ""));
		$skipKey = array("edit", "delete", "for_all");
		foreach ($this->arActions as $actionKey => $action)
		{
			if (in_array($actionKey, $skipKey))
				continue;

			if (is_array($action))
			{
				if (!empty($action["type"]))
				{
					switch ($action["type"])
					{
						case "select":
							$actionList[] = array(
								"NAME" => $action["lable"],
								"VALUE" => $actionKey,
								"ONCHANGE" => array(
									array(
										"ACTION" => Bitrix\Main\Grid\Panel\Actions::CREATE,
										"DATA" => array(
											array(
												"TYPE" => Bitrix\Main\Grid\Panel\Types::DROPDOWN,
												"ID" => "selected_action_{$this->table_id}",
												"NAME" => $action["name"],
												"ITEMS" => $action["items"]
											)
										)
									)
								)
							);
							break;
					}
				}
			}
			else
			{
				$actionList[] = array(
					"NAME" => $action,
					"VALUE" => $actionKey,
					"ONCHANGE" => Bitrix\Main\Grid\Panel\Actions::RESET_CONTROLS,
				);
			}
		}

		$items = array();

		/* Default actions */
		if ($this->arActions["edit"])
			$items[] = $snippet->getEditButton();
		if ($this->arActions["delete"])
			$items[] = $snippet->getRemoveButton();

		/* Action list (select and apply button) */
		$items[] = array(
			"TYPE" => Bitrix\Main\Grid\Panel\Types::DROPDOWN,
			"ID" => "base_action_select_{$this->table_id}",
			"NAME" => "action_button_{$this->table_id}",
			"ITEMS" => $actionList
		);
		$items[] = $snippet->getApplyButton(
			array(
				"ONCHANGE" => array(
					array(
						"ACTION" => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
						"DATA" => array(
							array(
								"JS" => "BX.adminList.SendSelected('{$this->table_id}')"
							)
						)
					)
				)
			)
		);

		if ($this->arActions["for_all"])
			$items[] = $snippet->getForAllCheckbox();

		$actionPanel["GROUPS"][] = array("ITEMS" => $items);

		return $actionPanel;
	}

	public function &AddRow($id = false, $arRes = Array(), $link = false, $title = false)
	{
		$row = new CAdminListRow($this->aHeaders, $this->table_id);
		$row->id = $id;
		$row->arRes = $arRes;
		$row->link = $link;
		$row->title = $title;
		$row->pList = &$this;
		$row->bEditMode = true;
		$this->aRows[] = &$row;
		return $row;
	}

	/**
	 * The method set the default fields for the filter.
	 *
	 * @param array $fields array("fieldId1", "fieldId2", "fieldId3")
	 */
	public function setDefaultFilterFields(array $fields)
	{
		$filterOptions = new Bitrix\Main\UI\Filter\Options($this->table_id);
		$filterOptions->setFilterSettings(
			"default_filter",
			array("rows" => $fields),
			false
		);
		$filterOptions->save();
	}

	/**
	 * The method set filter presets.
	 *
	 * @param array $filterPresets array("presetId" => array("name" => "presetName", "fields" => array(...)))
	 */
	public function setFilterPresets(array $filterPresets)
	{
		$this->filterPresets = $filterPresets;
	}

	public function DisplayFilter($filterFields)
	{
		global $APPLICATION;
		$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');
		?>
		<div class="adm-toolbar-panel-container">
			<div class="adm-toolbar-panel-flexible-space">
			<?
			$APPLICATION->includeComponent(
				"bitrix:main.ui.filter",
				"",
				array(
					"FILTER_ID" => $this->table_id,
					"GRID_ID" => $this->table_id,
					"FILTER" => $filterFields,
					"FILTER_PRESETS" => $this->filterPresets,
					"ENABLE_LABEL" => true,
					"ENABLE_LIVE_SEARCH" => true
				),
				false,
				array("HIDE_ICONS" => true)
			);
			?>
			</div>
			<?
			$this->ShowContext();
			?>
		</div>
		<?
	}

	public function DisplayList($arParams = array())
	{
		global $APPLICATION;
		$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

		foreach(GetModuleEvents("main", "OnAdminListDisplay", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$this));

		$this->ShowContext();

		$gridParameters = array(
			"GRID_ID" => $this->table_id,
			"AJAX_MODE" => "Y",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_HISTORY" => "N",
			"SHOW_PAGESIZE" => true,
			"AJAX_ID" => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
			"ALLOW_PIN_HEADER" => true
		);

		$actionPanel = $this->GetGroupAction();
		if ($actionPanel)
		{
			$gridParameters["ACTION_PANEL"] = $actionPanel;
		}
		else
		{
			$gridParameters["SHOW_CHECK_ALL_CHECKBOXES"] = false;
			$gridParameters["SHOW_ROW_CHECKBOXES"] = false;
			$gridParameters["SHOW_SELECTED_COUNTER"] = false;
			$gridParameters["SHOW_ACTION_PANEL"] = false;
		}

		$gridOptions = new Bitrix\Main\Grid\Options($gridParameters["GRID_ID"]);
		$gridColumns = $gridOptions->getVisibleColumns();
		if (empty($gridColumns))
			$gridColumns = array_keys($this->aVisibleHeaders);

		$gridParameters["ENABLE_NEXT_PAGE"] = $this->enableNextPage;
		$gridParameters["TOTAL_ROWS_COUNT"] = $this->totalRowCount;
		$gridParameters["NAV_STRING"] = $this->sNavText;
		$gridParameters["PAGE_SIZES"] = array(
			array("NAME" => "5", "VALUE" => "5"),
			array("NAME" => "10", "VALUE" => "10"),
			array("NAME" => "20", "VALUE" => "20"),
			array("NAME" => "50", "VALUE" => "50"),
			array("NAME" => "100", "VALUE" => "100"),
			array("NAME" => "200", "VALUE" => "200"),
			array("NAME" => "500", "VALUE" => "500")
		);

		$gridParameters["ROWS"] = array();
		foreach ($this->aRows as $row)
		{
			$gridRow = array(
				"id" => $row->id,
			);
			$aActions = array();
			foreach ($row->aActions as $aAction)
			{
				if (!empty($aAction["LINK"]) && empty($aAction["ACTION"]))
					$aAction["ONCLICK"] = "BX.adminPanel.Redirect([], '".$aAction["LINK"]."', event);";
				else
					$aAction["ONCLICK"] = $aAction["ACTION"];
				$aActions[] = $aAction;
			}
			$gridRow["actions"] = $aActions;

			if ($row->link)
			{
				$gridRow["default_action"] = array();
				$gridRow["default_action"]["href"] = $row->link;
				if ($row->title)
					$gridRow["default_action"]["title"] = $row->title;
			}

			foreach ($row->aFields as $fieldId => $field)
			{
				if (!empty($field["edit"]["type"]))
					$this->SetHeaderEditType($fieldId, $field);
			}
			foreach ($gridColumns as $columnId)
			{
				$field = $row->aFields[$columnId];
				if (!is_array($row->arRes[$columnId]))
					$value = trim($row->arRes[$columnId]);
				else
					$value = $row->arRes[$columnId];
				$gridRow["data"][$columnId] = $value;
				switch ($field["view"]["type"])
				{
					case "checkbox":
						if ($value == "Y")
							$value = htmlspecialcharsex(GetMessage("admin_lib_list_yes"));
						else
							$value = htmlspecialcharsex(GetMessage("admin_lib_list_no"));
						break;
					case "select":
						if ($field["edit"]["values"][$value])
							$value = htmlspecialcharsex($field["edit"]["values"][$value]);
						break;
					case "file":
						$arFile = CFile::getFileArray($value);
						if (is_array($arFile))
							$value = htmlspecialcharsex(CHTTP::URN2URI($arFile["SRC"]));
						else
							$value = "";
						break;
					case "html":
						$value = $field["view"]["value"];
						break;
					default:
						$value = htmlspecialcharsex($value);
						break;
				}
				$gridRow["columns"][$columnId] = $value;
			}
			$gridParameters["ROWS"][] = $gridRow;
		}

		$gridParameters["COLUMNS"] = array();
		foreach ($this->aHeaders as $header)
		{
			$header["name"] = $header["content"];
			$gridParameters["COLUMNS"][] = $header;
		}

		$errorMessage = "";
		foreach ($this->arFilterErrors as $error)
			$errorMessage .= " ".$error;
		foreach ($this->arUpdateErrors as $arError)
			$errorMessage .= " ".$arError[0];
		foreach ($this->arGroupErrors as $arError)
			$errorMessage .= " ".$arError[0];
		if ($errorMessage <> "")
		{
			$gridParameters["MESSAGES"] = array(
				array(
					"TYPE" => Bitrix\Main\Grid\MessageType::ERROR,
					"TEXT" => $errorMessage
				)
			);
		}

		$APPLICATION->includeComponent(
			"bitrix:main.ui.grid",
			"",
			$gridParameters,
			false, array("HIDE_ICONS" => "Y")
		);
	}

	private function ShowContext()
	{
		if ($this->context && !$this->isShownContext)
		{
			$this->isShownContext = true;
			$this->context->Show();
		}
	}

	private function SetHeaderEditType($headerId, $field)
	{
		switch ($field["edit"]["type"])
		{
			case "input":
				$editable = array("TYPE" => Types::TEXT);
				break;
			case "calendar":
				$editable = array("TYPE" => Types::DATE);
				break;
			case "checkbox":
				$editable = array("TYPE" => Types::CHECKBOX);
				break;
			case "select":
				$editable = array(
					"TYPE" => Types::DROPDOWN,
					"items" => $field["edit"]["values"]
				);
				break;
			case "html":
				$editable = array("TYPE" => Types::CUSTOM, "HTML" => $field["edit"]["value"]);
				break;
			default:
				$editable = array("TYPE" => Types::TEXT);
		}

		$this->aHeaders[$headerId]["editable"] = $editable;
	}
}

class CAdminUiResult extends CAdminResult
{
	public function NavStart($nPageSize=20, $bShowAll=true, $iNumPage=false)
	{
		$nPageSize = $this->GetNavSize($this->table_id);

		$nSize = $this->GetNavSize($this->table_id, $nPageSize);

		if(!is_array($nPageSize))
			$nPageSize = array();

		$nPageSize["nPageSize"] = $nSize;
		if($_REQUEST["mode"] == "excel")
			$nPageSize["NavShowAll"] = true;

		$this->nInitialSize = $nPageSize["nPageSize"];

		$this->parentNavStart($nPageSize, $bShowAll, $iNumPage);
	}

	public function GetNavPrint($title, $show_allways=true, $StyleText="", $template_path=false, $arDeleteParam=false)
	{
		$gridOptions = new Bitrix\Main\Grid\Options($this->table_id);
		$componentObject = null;
		$this->bShowAll = false;
		return $this->getPageNavStringEx($componentObject, "", "grid", false, null, $gridOptions->getNavParams());
	}

	public function GetNavSize($tableId = false, $nPageSize = 20, $listUrl = '')
	{
		$tableId = $tableId ? $tableId : $this->table_id;
		$gridOptions = new Bitrix\Main\Grid\Options($tableId);
		$navParams = $gridOptions->getNavParams();
		return $navParams["nPageSize"];
	}
}

class CAdminUiContextMenu extends CAdminContextMenu
{
	public function Show()
	{
		foreach (GetModuleEvents("main", "OnAdminContextMenuShow", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(&$this->items));
		}

		if (empty($this->items) && empty($this->additional_items))
		{
			return;
		}

		?>
		<div class="adm-toolbar-panel-align-right">
		<?

		if (!empty($this->additional_items))
		{
			$menuUrl = "BX.adminList.ShowMenu(this, ".HtmlFilter::encode(
				CAdminPopup::PhpToJavaScript($this->additional_items)).");";
			?>
			<div class="adm-toolbar-panel-button webform-small-button webform-small-button-transparent
				webform-cogwheel" onclick="<?=$menuUrl?>">
				<span class="webform-button-icon"></span>
			</div>
			<?
		}

		if (!empty($this->items))
		{
			$items = $this->items;
			$firstItem = array_shift($items);
			$menuUrl = "BX.adminList.ShowMenu(this, ".HtmlFilter::encode(
					CAdminPopup::PhpToJavaScript($items)).");";
			if (count($items) > 0):?>
			<span class="webform-small-button-separate-wrap adm-toolbar-panel-button">
				<a href="<?=HtmlFilter::encode($firstItem["LINK"])?>" class="
					webform-small-button webform-small-button-blue">
					<span class="webform-small-button-icon"></span>
					<span class="webform-small-button-text"><?=HtmlFilter::encode($firstItem["TEXT"])?></span>
				</a>
				<span class="webform-small-button-right-part" onclick="<?=$menuUrl?>"></span>
			</span>
			<? else:?>
				<a href="<?=HtmlFilter::encode($firstItem["LINK"])?>">
					<span class="webform-small-button webform-small-button-blue bx24-top-toolbar-add
						adm-toolbar-panel-button">
						<?=HtmlFilter::encode($firstItem["TEXT"])?>
					</span>
				</a>
			<?endif;
		}

		?>
		</div>
		<?
	}
}