<?php
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Grid\Panel;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\Grid\Context;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid;
use Bitrix\Main\Security;

class CAdminUiList extends CAdminList
{
	public $enableNextPage = false;
	public $totalRowCount = 0;

	protected $filterPresets = array();
	protected $currentPreset = array();

	private $isShownContext = false;
	private $contextSettings = array();

	/** @var CAdminUiContextMenu */
	public $context = false;

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

	/**
	 * @param $navigationId
	 *
	 * @return PageNavigation
	 */
	public function getPageNavigation($navigationId)
	{
		$pageNum = 1;

		if (!Context::isInternalRequest()
			&& !($this->request->get('clear_nav') === 'Y')
			&& isset($this->session['ADMIN_PAGINATION_DATA'][$this->table_id])
		)
		{
			$paginationData = $this->session['ADMIN_PAGINATION_DATA'][$this->table_id];
			if (isset($paginationData['PAGE_NUM']))
			{
				$pageNum = (int)$paginationData['PAGE_NUM'];
			}
		}

		$nav = new PageNavigation($navigationId);
		$nav->setPageSize($this->getNavSize());
		$nav->setCurrentPage($pageNum);
		$nav->initFromUri();

		if (Context::isInternalRequest())
		{
			if (!isset($this->session['ADMIN_PAGINATION_DATA']))
			{
				$this->session['ADMIN_PAGINATION_DATA'] = [];
			}
			$this->session['ADMIN_PAGINATION_DATA'][$this->table_id] = ['PAGE_NUM' => $nav->getCurrentPage()];
		}

		return $nav;
	}

	public function isTotalCountRequest()
	{
		$request = Bitrix\Main\Context::getCurrent()->getRequest();
		if ($request->isAjaxRequest() && $request->get("action") == "getTotalCount")
		{
			return true;
		}
		return false;
	}

	public function sendTotalCountResponse($totalCount)
	{
		global $adminAjaxHelper;
		if (!is_object($adminAjaxHelper))
		{
			require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
			$adminAjaxHelper = new CAdminAjaxHelper();
		}

		$adminAjaxHelper->sendJsonResponse(["totalCountHtml" => GetMessage("admin_lib_list_all_title").": ".(int) $totalCount]);
	}

	public function SetNavigationParams(\CAdminUiResult $queryObject, $params = array())
	{
		if ($this->isPublicMode)
		{
			unset($params["BASE_LINK"]);
		}
		$queryObject->setNavigationParams($params);
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

	public function getCurPageParam($strParam="", $arParamKill=array(), $get_index_page=null)
	{
		global $APPLICATION;

		if (Context::isInternalRequest())
		{
			$arParamKill = array_merge($arParamKill, array("internal", "grid_id", "grid_action", "bxajaxid", "sessid"));
		}

		return $APPLICATION->GetCurPageParam($strParam, $arParamKill, $get_index_page);
	}

	public function getNavSize()
	{
		$gridOptions = new Bitrix\Main\Grid\Options($this->table_id);
		$navParams = $gridOptions->getNavParams();
		return $navParams["nPageSize"];
	}

	public function EditAction()
	{
		if(
			$_SERVER["REQUEST_METHOD"] == "POST" &&
			!empty($_REQUEST["action_button_".$this->table_id]) &&
			is_array($_POST["FIELDS"]) &&
			check_bitrix_sessid()
		)
		{
			$arrays = array(&$_POST, &$_REQUEST, &$GLOBALS);
			foreach ($arrays as $i => &$array)
			{
				$customFields = [];
				foreach ($array["FIELDS"] as $id => &$fields)
				{
					if (is_array($fields))
					{
						CUtil::decodeURIComponent($fields);
						$keys = array_keys($fields);
						foreach ($keys as $key)
						{
							if (preg_match("/_custom/i", $key, $match))
							{
								if (!is_array($arrays[$i]["FIELDS"][$id][$key]))
								{
									continue;
								}
								foreach ($arrays[$i]["FIELDS"][$id][$key] as $index => $value)
								{
									if (!isset($value["name"]) || !isset($value["value"]))
									{
										continue;
									}
									if (preg_match_all("/(.*?)\[(.*?)\]/", $value["name"], $listMatchKeys))
									{
										$listPreparedKeys = [];
										foreach ($listMatchKeys as $matchKeys)
										{
											foreach ($matchKeys as $matchKey)
											{
												if (!is_string($matchKey) || trim($matchKey) == '')
												{
													continue;
												}
												if (mb_strpos($matchKey, "[") === false && mb_strpos($matchKey, "]") === false)
												{
													$listPreparedKeys[] = $matchKey;
												}
											}
										}
										$listPreparedKeys[] = $value["value"];
										$customFields = array_replace_recursive($customFields, $this->prepareCustomKey(
											array_shift($listPreparedKeys), $listPreparedKeys));
									}
								}
								unset($arrays[$i]["FIELDS"][$id][$key]);
							}

							if(($c = mb_substr($key, 0, 1)) == '~' || $c == '=')
							{
								unset($arrays[$i]["FIELDS"][$id][$key]);
							}
						}
					}
				}
				if ($customFields)
				{
					$arrays[$i] = array_replace_recursive($arrays[$i], $customFields);
				}
			}
			return true;
		}
		return false;
	}

	private function prepareCustomKey($key, array $keys)
	{
		return (count($keys) == 1 ? [$key => array_shift($keys)] :
			[$key => $this->prepareCustomKey(array_shift($keys), $keys)]);
	}

	/**
	 * @return array|false
	 */
	public function GroupAction()
	{
		$this->PrepareAction();

		if ($this->GetAction() === null)
		{
			return false;
		}

		if (!check_bitrix_sessid())
		{
			return false;
		}

		if (!empty($_REQUEST["bxajaxid"]))
		{
			global $adminSidePanelHelper;
			$adminSidePanelHelper->setSkipResponse(true);
		}

		if (!$this->IsGroupActionToAll())
		{
			$arID = $this->GetGroupIds();
			if ($arID === null)
			{
				$arID = false;
			}
		}
		else
		{
			$arID = array("");
		}

		return $arID;
	}

	/**
	 * Returns true if the user has set the flag "To all" in the list.
	 *
	 * @return bool
	 */
	public function IsGroupActionToAll()
	{
		return (
			isset($_REQUEST["action_all_rows_".$this->table_id])
			&& $_REQUEST["action_all_rows_".$this->table_id] === 'Y'
		);
	}

	/**
	 * @return void
	 */
	protected function PrepareAction()
	{
		if (isset($_REQUEST["action"]) && is_array($_REQUEST["action"]))
		{
			foreach ($_REQUEST["action"] as $actionKey => $actionValue)
				$_REQUEST[$actionKey] = $actionValue;
		}
		if (!empty($_REQUEST["action_button_".$this->table_id]))
		{
			$_REQUEST["action"] = $_REQUEST["action_button_".$this->table_id];
			$_REQUEST["action_button"] = $_REQUEST["action_button_".$this->table_id];
		}
	}

	public function ActionDoGroup($id, $action_id, $add_params = "")
	{
		$listParams = explode("&", $add_params);
		$addParams = array();
		if ($listParams)
		{
			foreach($listParams as $param)
			{
				$explode = explode("=", $param);
				if ($explode[0] && $explode[1])
				{
					$addParams[$explode[0]] = $explode[1];
				}
			}
		}

		$postParams = array_merge(array(
			"action_button_".$this->table_id => $action_id,
			"ID" => $id
		), $addParams);

		return $this->ActionAjaxPostGrid($postParams);
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
			if ((is_array($fieldValue) && empty($fieldValue)) || (is_string($fieldValue) && $fieldValue == ''))
			{
				continue;
			}

			if (mb_substr($fieldId, -5) == "_from")
			{
				$realFieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 5);
				if (!array_key_exists($realFieldId, $filterable))
				{
					continue;
				}
				if (mb_substr($realFieldId, -2) == "_1")
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
			elseif (mb_substr($fieldId, -3) == "_to")
			{
				$realFieldId = mb_substr($fieldId, 0, mb_strlen($fieldId) - 3);
				if (!array_key_exists($realFieldId, $filterable))
				{
					continue;
				}
				if (mb_substr($realFieldId, -2) == "_1")
				{
					$realFieldId = mb_substr($realFieldId, 0, mb_strlen($realFieldId) - 2);
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

	public function hasGroupErrors()
	{
		return (bool)(count($this->arGroupErrors));
	}

	public function getGroupErrors()
	{
		$error = "";
		foreach ($this->arGroupErrors as $groupError)
		{
			$error .= " ".$groupError[0];
		}

		return trim($error);
	}

	public function setContextSettings(array $settings)
	{
		$this->contextSettings = $settings;
	}

	protected function GetSystemContextMenu(array $config = []): array
	{
		$result = [];
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if (isset($config['excel']))
		{
			if ($this->contextSettings["pagePath"])
			{
				$pageParam = (!empty($_GET) ? http_build_query($_GET, "", "&") : "");
				$pagePath = $this->contextSettings["pagePath"]."?".$pageParam;
				$pageParams = static::getModeExportParam();
				if ($this->isPublicMode)
					$pageParams["public"] = "y";
				$link = CHTTP::urlAddParams($pagePath, $pageParams);
			}
			else
			{
				$link = CHTTP::urlAddParams($APPLICATION->GetCurPageParam(), static::getModeExportParam());
			}
			$link = CHTTP::urlDeleteParams($link, ["apply_filter"]);
			$result[] = [
				"TEXT" => "Excel",
				"TITLE" => GetMessage("admin_lib_excel"),
				"LINK" => $link,
				"GLOBAL_ICON"=>"adm-menu-excel",
			];
		}
		return $result;
	}

	protected function InitContextMenu(array $menu = [], array $additional = []): void
	{
		if (!empty($menu) || !empty($additional))
		{
			$this->context = new CAdminUiContextMenu($menu, $additional);
		}
	}

	private function GetGroupAction()
	{
		$actionPanelConstructor = new CAdminUiListActionPanel(
			$this->table_id, $this->arActions, $this->arActionsParams);

		return $actionPanelConstructor->getActionPanel();
	}

	public function &AddRow($id = false, $arRes = Array(), $link = false, $title = false)
	{
		$row = new CAdminUiListRow($this->aHeaders, $this->table_id);
		$row->id = ($id ?: Security\Random::getString(4));
		$row->arRes = $arRes;
		$publicMode = $this->getPublicModeState();
		if ($publicMode)
		{
			$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
			$reqValue = "/".str_replace("/", "\/", $selfFolderUrl)."/i";
			if (!empty($link) && !preg_match($reqValue, $link) && preg_match("/\.php/i", $link))
			{
				$link = $selfFolderUrl.$link;
			}
		}
		$row->link = $link;
		$row->title = $title;
		$row->pList = &$this;
		$row->bEditMode = true;
		$row->setPublicModeState($publicMode);

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
		foreach ($filterPresets as $filterPreset)
		{
			if (!empty($filterPreset["current"]))
			{
				$this->currentPreset = $filterPreset;
			}
		}
	}

	public function deletePreset($presetId)
	{
		$options = new Options($this->table_id);
		$options->deleteFilter($presetId);
		$options->save();
	}

	public function DisplayFilter(array $filterFields = array())
	{
		global $APPLICATION;

		$params = array(
			"FILTER_ID" => $this->table_id,
			"GRID_ID" => $this->table_id,
			"FILTER" => $filterFields,
			"FILTER_PRESETS" => $this->filterPresets,
			"ENABLE_LABEL" => true,
			"ENABLE_LIVE_SEARCH" => true
		);

		if ($this->currentPreset)
		{
			$options = new Options($this->table_id, $this->filterPresets);
			$options->setFilterSettings($this->currentPreset["id"], $this->currentPreset, true, false);
			$options->save();
		}

		if ($this->context)
		{
			$this->context->setFilterContextParam(true);
		}

		if ($this->isPublicMode)
		{
			ob_start();
			?>
				<div class="pagetitle-container pagetitle-flexible-space">
					<?
					$APPLICATION->includeComponent(
						"bitrix:main.ui.filter",
						"",
						$params,
						false,
						array("HIDE_ICONS" => true)
					);
					?>
				</div>
			<?
			$APPLICATION->AddViewContent("inside_pagetitle", ob_get_clean(), 600);
		}
		else
		{
			$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');
			?>
			<div class="adm-toolbar-panel-container">
				<div class="adm-toolbar-panel-flexible-space">
					<?
					$APPLICATION->includeComponent(
						"bitrix:main.ui.filter",
						"",
						$params,
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

		$this->createFilterSelectorHandlers($filterFields);

		?>
		<script type="text/javascript">
			BX.ready(function () {
				if (!window['filter_<?=$this->table_id?>'] ||
					!BX.is_subclass_of(window['filter_<?=$this->table_id?>'], BX.adminUiFilter))
				{
					window['filter_<?=$this->table_id?>'] = new BX.adminUiFilter('<?=$this->table_id?>',
						<?=CUtil::PhpToJsObject(array())?>);
				}
			});
		</script>
		<?
	}

	private function createFilterSelectorHandlers(array $filterFields = array())
	{
		$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
		foreach ($filterFields as $filterField)
		{
			if (isset($filterField["type"]) && $filterField["type"] !== "custom_entity")
			{
				continue;
			}

			if (isset($filterField["selector"]) && isset($filterField["selector"]["type"]))
			{
				switch ($filterField["selector"]["type"])
				{
					case "user":
						?>
						<script>
							BX.ready(function() {
								if (!window["userFilterHandler_<?=$filterField["id"]?>"])
								{
									var params = {
										filterId: "<?=$this->table_id?>",
										fieldId: "<?=$filterField["id"]?>",
										languageId: "<?=LANGUAGE_ID?>",
										selfFolderUrl: "<?=$selfFolderUrl?>"
									};
									window["userFilterHandler_<?=$filterField["id"]?>"] =
										new BX.adminUserFilterHandler(params);
								}
							});
							if (typeof(SUVsetUserId_<?=$filterField["id"]?>) === "undefined")
							{
								function SUVsetUserId_<?=$filterField["id"]?>(userId)
								{
									if (window["userFilterHandler_<?=$filterField["id"]?>"])
									{
										var adminUserFilterHandler = window["userFilterHandler_<?=$filterField["id"]?>"];
										adminUserFilterHandler.setSelected(userId);
									}
								}
							}
						</script>
						<?
						break;
					case "product":
						?>
						<script>
							BX.ready(function() {
								if (!window["productFilterHandler_<?=$filterField["id"]?>"])
								{
									var params = {
										filterId: "<?=$this->table_id?>",
										fieldId: "<?=$filterField["id"]?>",
										languageId: "<?=LANGUAGE_ID?>",
										publicMode: "<?=($this->isPublicMode ? "Y" : "N")?>",
										selfFolderUrl: "<?=$selfFolderUrl?>"
									};
									window["productFilterHandler_<?=$filterField["id"]?>"] =
										new BX.adminProductFilterHandler(params);
								}
							});
							if (typeof(FillProductFields_<?=$filterField["id"]?>) === "undefined")
							{
								function FillProductFields_<?=$filterField["id"]?>(product)
								{
									if (window["productFilterHandler_<?=$filterField["id"]?>"])
									{
										var adminProductFilterHandler =
											window["productFilterHandler_<?=$filterField["id"]?>"];
										adminProductFilterHandler.closeProductSearchDialog();
										adminProductFilterHandler.setSelected(product["id"], product["name"]);
									}
								}
							}
						</script>
						<?
						break;
				}
			}
		}
	}

	public function ShowActionTable() {}

	public function DisplayList($arParams = array())
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		foreach(GetModuleEvents("main", "OnAdminListDisplay", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$this));

		$errorMessages = [];
		foreach ($this->arFilterErrors as $error)
		{
			$errorMessages[] = [
				'TYPE' => Bitrix\Main\Grid\MessageType::ERROR,
				'TEXT' => $error,
			];
		}
		foreach ($this->arUpdateErrors as $arError)
		{
			$errorMessages[] = [
				'TYPE' => Bitrix\Main\Grid\MessageType::ERROR,
				'TEXT' => $arError[0],
			];
		}
		foreach ($this->arGroupErrors as $arError)
		{
			$errorMessages[] = [
				'TYPE' => Bitrix\Main\Grid\MessageType::ERROR,
				'TEXT' => $arError[0],
			];
		}

		if (Context::isValidateRequest())
		{
			global $adminAjaxHelper;
			if (!is_object($adminAjaxHelper))
			{
				require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
				$adminAjaxHelper = new CAdminAjaxHelper();
			}
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			if (!empty($errorMessages))
			{
				$adminAjaxHelper->sendJsonResponse(["messages" => $errorMessages]);
			}
			else
			{
				$adminAjaxHelper->sendJsonResponse(array("messages" => array()));
			}
		}

		if (Context::isShowpageRequest() && !empty($errorMessages))
		{
			global $adminAjaxHelper;
			if (!is_object($adminAjaxHelper))
			{
				require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
				$adminAjaxHelper = new CAdminAjaxHelper();
			}
			global $APPLICATION;
			$APPLICATION->RestartBuffer();

			$adminAjaxHelper->sendJsonResponse(["messages" => $errorMessages]);
		}

		global $APPLICATION;
		$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

		echo $this->sPrologContent;

		$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");

		$this->ShowContext();

		$gridParameters = array(
			"GRID_ID" => $this->table_id,
			"AJAX_MODE" => "Y",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_HISTORY" => "N",
			"SHOW_PAGESIZE" => true,
			"AJAX_ID" => CAjax::getComponentID("bitrix:main.ui.grid", ".default", ""),
			"ALLOW_PIN_HEADER" => true,
			"ALLOW_VALIDATE" => false,
			"HANDLE_RESPONSE_ERRORS" => true
		);

		$actionPanel = ($arParams["ACTION_PANEL"] ?? $this->GetGroupAction());
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

		if (isset($arParams["SHOW_TOTAL_COUNTER"]))
		{
			$gridParameters["SHOW_TOTAL_COUNTER"] = $arParams["SHOW_TOTAL_COUNTER"];
		}

		$showTotalCountHtml = (isset($arParams["SHOW_COUNT_HTML"]) && $arParams["SHOW_COUNT_HTML"] === true);
		if ($showTotalCountHtml)
		{
			$gridParameters["TOTAL_ROWS_COUNT_HTML"] = $this->getTotalRowsCountHtml();
		}

		$gridOptions = new Bitrix\Main\Grid\Options($gridParameters["GRID_ID"]);
		$defaultSort = array();
		if ($this->sort instanceof CAdminSorting)
		{
			$defaultSort = array("sort" => array($this->sort->getField() => $this->sort->getOrder()));
		}
		$sorting = $gridOptions->GetSorting($defaultSort);
		$gridParameters["SORT"] = $sorting["sort"];
		$gridParameters["SORT_VARS"] = $sorting["vars"];

		$gridColumns = $gridOptions->getVisibleColumns();
		if (empty($gridColumns))
			$gridColumns = array_keys($this->aVisibleHeaders);

		$gridParameters["ENABLE_NEXT_PAGE"] = $this->enableNextPage;
		$gridParameters["TOTAL_ROWS_COUNT"] = $this->totalRowCount;
		if ($this->sNavText)
		{
			$gridParameters["NAV_STRING"] = $this->sNavText;
		}
		else
		{
			$gridParameters["SHOW_PAGINATION"] = false;
		}

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
		/** @var \CAdminUiListRow $row */
		foreach ($this->aRows as $row)
		{
			$gridRow = array(
				"id" => $row->id,
				"actions" => $row->getPreparedActions()
			);

			$gridRow["default_action"] = array();
			if ($row->title)
			{
				$gridRow["default_action"]["title"] = $row->title;
			}
			$defaultActionType = $row->getConfigValue(CAdminUiListRow::DEFAULT_ACTION_TYPE_FIELD);
			switch ($defaultActionType)
			{
				case CAdminUiListRow::LINK_TYPE_SLIDER:
					$skipUrlModify = $row->getConfigValue(CAdminUiListRow::SKIP_URL_MODIFY_FIELD) === true
						? 'true'
						: 'false'
					;
					$gridRow["default_action"]["onclick"] = "BX.adminSidePanel.onOpenPage('".$row->link."', ".$skipUrlModify.");";
					break;
				case CAdminUiListRow::LINK_TYPE_URL:
					$gridRow["default_action"]["href"] = htmlspecialcharsback($row->link);
					break;
				default:
					if ($arParams["DEFAULT_ACTION"])
					{
						if ($this->isPublicMode)
						{
							if (!empty($row->link))
							{
								$row->link = str_replace("/bitrix/admin/", $selfFolderUrl, $row->link);
							}
						}
						$gridRow["default_action"]["href"] = htmlspecialcharsback($row->link);
					}
					elseif ($row->link)
					{
						if ($this->isPublicMode)
						{
							$skipUrlModificationEnabled = ($arParams['SKIP_URL_MODIFICATION'] ?? false) === true;
							$skipUrlModification = $skipUrlModificationEnabled && mb_strpos($row->link, '/bitrix/admin/') === false
								? 'true'
								: 'false';
							$gridRow["default_action"]["onclick"] = "BX.adminSidePanel.onOpenPage('".$row->link."', ".$skipUrlModification.");";
						}
						else
						{
							$gridRow["default_action"]["href"] = htmlspecialcharsback($row->link);
						}
					}
					else
					{
						$gridRow["default_action"]["onclick"] = "";
					}
					break;
			}

			foreach ($row->aFields as $fieldId => $field)
			{
				if (!empty($field["edit"]["type"]))
					$this->SetHeaderEditType($fieldId, $field);
			}

			$listEditable = array();
			foreach (array_diff_key($this->aHeaders, $row->aFields) as $fieldId => $field)
			{
				$listEditable[$fieldId] = false;
			}

			$disableEditColumns = array();

			foreach ($gridColumns as $columnId)
			{
				$field = $row->aFields[$columnId];
				if (!is_array($row->arRes[$columnId]))
					$value = trim($row->arRes[$columnId]);
				else
					$value = $row->arRes[$columnId];

				$editValue = $value;
				if (isset($field["edit"]["type"]))
				{
					switch ($field["edit"]["type"])
					{
						case "file":
							if ($fileArray = CFile::getFileArray($value))
								$editValue = $fileArray["SRC"];
							break;
						case "html":
							$editValue = $field["edit"]["value"];
							break;
						case "money":
							$moneyAttributes = $field["edit"]["attributes"];
							$editValue = [
								'PRICE' => $moneyAttributes['PRICE'],
								'CURRENCY' => $moneyAttributes['CURRENCY'],
								'ATTRIBUTES' => $moneyAttributes['ATTRIBUTES'],
							];

							if (is_array($moneyAttributes['HIDDEN']))
							{
								$editValue['HIDDEN'] = [];
								foreach ($moneyAttributes['HIDDEN'] as $hiddenItem)
								{
									$editValue['HIDDEN'][$hiddenItem['NAME']] = $hiddenItem['VALUE'];
								}
							}
							break;
					}
				}
				else
				{
					$disableEditColumns[$columnId] = false;
				}

				$gridRow["data"][$columnId] = $editValue;

				if (isset($field["view"]["type"]))
				{
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
							$value = $value ? CFileInput::Show("fileInput_".$value, $value,
								$field["view"]["showInfo"], $field["view"]["inputs"]) : "";
							break;
						case "html":
							$value = $field["view"]["value"];
							break;
						default:
							$value = htmlspecialcharsex($value);
							break;
					}
				}
				else
				{
					$value = htmlspecialcharsbx($value);
				}

				$gridRow["columns"][$columnId] = $value;
			}
			$gridRow["editable"] = $listEditable;
			if (!empty($disableEditColumns))
				$gridRow["editableColumns"] = $disableEditColumns;

			$gridParameters["ROWS"][] = $gridRow;
		}

		$gridParameters["COLUMNS"] = array();
		foreach ($this->aHeaders as $header)
		{
			$header["name"] = $header["content"];
			$gridParameters["COLUMNS"][] = $header;
		}

		if (!empty($errorMessages))
		{
			$gridParameters["MESSAGES"] = $errorMessages;
		}

		$APPLICATION->includeComponent(
			"bitrix:main.ui.grid",
			"",
			$gridParameters,
			false, array("HIDE_ICONS" => "Y")
		);

		echo $this->sEpilogContent;

		$jsParams = [];
		$jsParams["publicMode"] = $this->isPublicMode;
		$jsParams["showTotalCountHtml"] = $showTotalCountHtml;
		$jsParams["serviceUrl"] = ($arParams["SERVICE_URL"] ?? "");

		?>
		<script type="text/javascript">
			if (!window['<?=$this->table_id?>'] || !BX.is_subclass_of(window['<?=$this->table_id?>'], BX.adminUiList))
			{
				window['<?=$this->table_id?>'] = new BX.adminUiList(
					'<?=$this->table_id?>', <?=CUtil::PhpToJsObject($jsParams)?>);
			}
			BX.adminChain.addItems("<?=$this->table_id?>_navchain_div");
		</script>
		<?
	}

	private function getTotalRowsCountHtml()
	{
		ob_start();
		?>
			<div><?= GetMessage("admin_lib_list_all_title").": " ?>
				<a id="<?=$this->table_id?>_show_total_count" href="#"><?= GetMessage("admin_lib_list_show_row_count_title")?></a>
			</div>
		<?
		return ob_get_clean();
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
		if (!isset($this->aHeaders[$headerId]))
		{
			return;
		}

		if (isset($this->aHeaders[$headerId]["editable"]) && $this->aHeaders[$headerId]["editable"] === false)
		{
			return;
		}

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
			case "file":
				$editable = array(
					"TYPE" => Types::IMAGE,
				);
				break;
			case "html":
				$editable = array("TYPE" => Types::CUSTOM);
				break;
			case "money":
				$editable = array(
					"TYPE" => Types::MONEY,
					"CURRENCY_LIST" => $field["edit"]["attributes"]["CURRENCY_LIST"],
					"HTML_ENTITY" => $field["edit"]["attributes"]["HTML_ENTITY"] ?? false,
				);
				break;
			default:
				$editable = array("TYPE" => Types::TEXT);
		}

		$this->aHeaders[$headerId]["editable"] = $editable;
	}
}

/**
 * Class CAdminUiListActionPanel
 * A class for working with group actions. Allows you to create your own group actions.
	Example of use:

	// The array for $lAdmin->AddGroupActionTable($arGroupActions, $arParamsGroupActions);
	$arGroupActions['test_my_type'] = array('type' => 'my_type', 'name' => 'Check custom actions');

	$actionPanelConstructor = new CAdminUiListActionPanel(
	$this->table_id, $this->arActions, $this->arActionsParams);

	//Set your own section
	$actionPanelConstructor->setActionSections(["my_section" => []], ["default"]);
	//Set your own action type
	$actionPanelConstructor->setTypeToSectionMap(["my_type" => "my_section"]);
	//Set handler for your type
	$actionPanelConstructor->setHandlerToType(["my_type" => function ($actionKey, $action) {
		$onChange = [
			[
				"ACTION" => Panel\Actions::CREATE,
				"DATA" => [
					[
						'TYPE' => Panel\Types::CUSTOM,
						'ID' => 'my_custom_html',
						'VALUE' => '<b>Hello!</b>',
					]
				]
			]
		];
		return [
			"ID" => $actionKey,
			"TYPE" => Bitrix\Main\Grid\Panel\Types::BUTTON,
			"TEXT" => $action["name"],
			"ONCHANGE" => $onChange
		];
	}]);

	return $actionPanelConstructor->getActionPanel();
 */
class CAdminUiListActionPanel
{
	private $tableId;
	private $inputActions;
	private $inputActionsParams;

	/**
	 * @var Panel\Snippet
	 */
	private $gridSnippets;

	private $actionSections = [];
	private $mapTypesAndSections = [
		"edit" => "default",
		"delete" => "default",
		"button" => "button",
		"select" => "list",
		"customjs" => "list",
		"html" => "html",
		"for_all" => "forAll"
	];
	private $mapTypesAndHandlers = [];

	public function __construct($tableId, array $actions, array $actionsParams)
	{
		$this->tableId = $tableId;
		$this->inputActions = $actions;
		$this->inputActionsParams = $actionsParams;

		$this->gridSnippets = new Panel\Snippet();

		$this->actionSections = [
			"default" => [],
			"button" => [],
			"list" => [
				"TYPE" => Panel\Types::DROPDOWN,
				"ID" => "base_action_select_{$this->tableId}",
				"NAME" => "action_button_{$this->tableId}",
				"ITEMS" => [
					[
						"NAME" => GetMessage("admin_lib_list_actions"),
						"VALUE" => "default",
						"ONCHANGE" => [["ACTION" => Panel\Actions::RESET_CONTROLS]]
					]
				]
			],
			"html" => [],
			"forAll" => []
		];
	}

	/**
	 * The method returns an array of data of the desired format for the grid.
	 * @return array
	 */
	public function getActionPanel()
	{
		$actionPanel = [];

		$items = $this->getItems();

		$actionPanel["GROUPS"][] = array("ITEMS" => $items);

		return $actionPanel;
	}

	/**
	 * The method writes a value into an array of sections.
	 * This array is the structure of the blocks into which you place your actions.
	 * @param array $actionSections Map sections.
	 * @param array $listKeyForDelete List keys for delete default sections.
		Example:
		[
			"default" => [
				"TYPE" => Types::BUTTON,
				"ID" => "button_id",
				"CLASS" => "apply",
				"TEXT" => "My button",
				"ONCHANGE" => [
					[
						"ACTION" => Panel\Actions::CALLBACK,
						"DATA" => [
							[
								"JS" => "alert('Click!');"
							]
						]
					]
				]
			]
		];
	 */
	public function setActionSections(array $actionSections, $listKeyForDelete = [])
	{
		foreach ($listKeyForDelete as $keyForDelete)
		{
			if (isset($this->actionSections[$keyForDelete]))
			{
				unset($this->actionSections[$keyForDelete]);
			}
		}

		$this->actionSections = array_merge($this->actionSections, $actionSections);
	}

	/**
	 * The method writes values to a map of types and partitions.
	 * This makes it possible to place any type of action in a specific action section.
	 * @param array $mapTypesAndSections Map of types and sections. Example ["html" => "default"].
	 */
	public function setTypeToSectionMap(array $mapTypesAndSections)
	{
		$this->mapTypesAndSections = array_merge($this->mapTypesAndSections, $mapTypesAndSections);
	}

	/**
	 * The method writes a handler for a particular type of action. This allows you to create your own action.
	 *
	 * @param array $mapTypesAndHandlers Map of types and handlers.
	 * Example:
	 * [
		"button" => function ($actionKey, $action) {
			$onChange = [
				[
					"ACTION" => Panel\Actions::CALLBACK,
					"DATA" => [
						[
							"JS" => $action["action"] ? $action["action"] :
							"BX.adminUiList.SendSelected('{$this->tableId}')"
						]
					]
				]
			]
			return [
				"ID" => $actionKey,
				"TYPE" => Bitrix\Main\Grid\Panel\Types::BUTTON,
				"TEXT" => $action["name"],
				"ONCHANGE" => $onChange
			];
		}]
	 */
	public function setHandlerToType(array $mapTypesAndHandlers)
	{
		$this->mapTypesAndHandlers = array_merge($this->mapTypesAndHandlers, $mapTypesAndHandlers);
	}

	/**
	 * @return array
	 */
	private function getDefaultApplyAction()
	{
		return ["JS" => "BX.adminUiList.SendSelected('{$this->tableId}')"];
	}

	/**
	 * @return array
	 */
	private function getItems()
	{
		$items = [];

		$actionSections = $this->getActionSections();

		foreach ($actionSections as $actionSection)
		{
			if ($this->isAssociativeArray($actionSection))
			{
				$items[] = $actionSection;
			}
			else
			{
				foreach ($actionSection as $aSection)
				{
					$items[] = $aSection;
				}
			}
		}

		return $items;
	}

	/**
	 * @return array
	 */
	private function getActionSections()
	{
		if (isset($this->inputActions["edit"]) && isset($this->actionSections[$this->mapTypesAndSections["edit"]]))
		{
			$this->actionSections[$this->mapTypesAndSections["edit"]][] = $this->gridSnippets->getEditButton();
		}
		if (isset($this->inputActions["delete"]) && isset($this->actionSections[$this->mapTypesAndSections["delete"]]))
		{
			$this->actionSections[$this->mapTypesAndSections["delete"]][] = $this->gridSnippets->getRemoveButton();
		}

		foreach ($this->inputActions as $actionKey => $action)
		{
			$this->setActionSection($this->actionSections, $actionKey, $action);
		}

		if (isset($this->inputActions["for_all"]) && isset($this->actionSections[$this->mapTypesAndSections["for_all"]]))
		{
			$this->actionSections[$this->mapTypesAndSections["for_all"]][] = $this->gridSnippets->getForAllCheckbox();
		}

		if (count($this->actionSections["list"]["ITEMS"]) == 1)
		{
			$this->actionSections["list"] = [];
		}

		return $this->actionSections;
	}

	/**
	 * @param array &$actionSections
	 * @param string $actionKey
	 * @param string|array $action
	 * @return void
	 */
	private function setActionSection(array &$actionSections, $actionKey, $action)
	{
		if (is_array($action))
		{
			self::prepareAction($action);
			$type = $action["type"];
			$actionSection = $this->mapTypesAndSections[$type] ?? "list";

			$method = "get".$type."ActionData";
			if ($this->mapTypesAndHandlers[$type] && is_callable($this->mapTypesAndHandlers[$type]))
			{
				$actionSections[$actionSection][] = $this->mapTypesAndHandlers[$type]($actionKey, $action);
			}
			elseif (method_exists(__CLASS__, $method))
			{
				if ($actionSection == "list")
				{
					$actionSections["list"]["ITEMS"][] = $this->$method($actionKey, $action);
				}
				else
				{
					$actionSections[$actionSection][] = $this->$method($actionKey, $action);
				}
			}
		}
		else
		{
			if (!in_array($actionKey, ["edit", "delete", "for_all"]))
			{
				$actionSections["list"]["ITEMS"][] = [
					"NAME" => $action,
					"VALUE" => $actionKey,
					"ONCHANGE" => [
						[
							"ACTION" => Panel\Actions::RESET_CONTROLS
						],
						$this->getApplyButtonCreationAction()
					]
				];
			}
		}
	}

	/**
	 * @param string $actionKey
	 * @param array $action
	 * @return array
	 */
	private function getButtonActionData($actionKey, $action)
	{
		$onChange = $action["action"] ? [
			["ACTION" => Panel\Actions::CALLBACK, "DATA" => [["JS" => $action["action"]]]]] : [];

		return [
			"ID" => $actionKey,
			"TYPE" => Bitrix\Main\Grid\Panel\Types::BUTTON,
			"TEXT" => $action["name"],
			"ONCHANGE" => $onChange
		];
	}

	/**
	 * @param string $actionKey
	 * @param array $action
	 * @return array
	 */
	private function getSelectActionData($actionKey, $action)
	{
		$internalOnchange = [];
		if (!empty($this->inputActionsParams["internal_select_onchange"]))
		{
			$internalOnchange[] = [
				"ACTION" => Panel\Actions::CALLBACK,
				"DATA" => [
					["JS" => $this->inputActionsParams["internal_select_onchange"]]
				]
			];
		}

		/**
			For each value of the list, you can pass a handler.
			example client code:
			$arGroupActions["test_section"] = array(
				"name" => "Menu item title",
				"type" => "select",
				"controlName" => "value name in request",
				"controlId" => "Dom id for dropdown control" (if empty, get from controlName),
				"items" => array(
					array("NAME" => "One", "VALUE" => "one", "ONCHANGE" => "alert('one');"),
					array("NAME" => "Two", "VALUE" => "two", "ONCHANGE" => "alert('two');")
				)
			);
		 */
		if (is_array($action["items"]))
		{
			foreach ($action["items"] as &$items)
			{
				if (empty($items["ONCHANGE"]))
				{
					$items["ONCHANGE"] = $internalOnchange;
				}
				else
				{
					$items["ONCHANGE"] = [
						[
							"ACTION" => Panel\Actions::CALLBACK,
							"DATA" => [
								["JS" => $items["ONCHANGE"]]
							]
						]
					];
				}
			}
		}

		$onchange = [
			[
				"ACTION" => Panel\Actions::RESET_CONTROLS
			],
			[
				"ACTION" => Panel\Actions::CREATE,
				"DATA" => [
					[
						"TYPE" => Panel\Types::DROPDOWN,
						"ID" => "selected_action_{$this->tableId}_".$action["controlId"],
						"NAME" => $action["controlName"],
						"ITEMS" => $action["items"]
					],
					$this->gridSnippets->getApplyButton(
						[
							"ONCHANGE" => [
								[
									"ACTION" => Panel\Actions::CALLBACK,
									"DATA" => [
										$this->getDefaultApplyAction()
									]
								]
							]
						]
					)
				]
			]
		];

		if (!empty($this->inputActionsParams["select_onchange"]))
		{
			$onchange[] = [
				"ACTION" => Panel\Actions::CALLBACK,
				"DATA" => [
					["JS" => $this->inputActionsParams["select_onchange"]]
				]
			];
		}

		return [
			"NAME" => $action["name"],
			"VALUE" => $actionKey,
			"ONCHANGE" => $onchange
		];
	}

	/**
	 * @param string $actionKey
	 * @param array $action
	 * @return array
	 */
	private function getCustomJsActionData($actionKey, $action)
	{
		return [
			"NAME" => $action["name"],
			"VALUE" => $actionKey,
			"ONCHANGE" => [
				["ACTION" => Panel\Actions::RESET_CONTROLS],
				$this->getApplyButtonCreationAction($action["js"])
			]
		];
	}

	/**
	 * @param string $actionKey
	 * @param array $action
	 * @return array
	 */
	private function getBaseActionData($actionKey, $action)
	{
		return [
			"NAME" => $action["name"],
			"VALUE" => $actionKey,
			"ONCHANGE" => [
				["ACTION" => Panel\Actions::RESET_CONTROLS],
				$this->getApplyButtonCreationAction($action["action"])
			]
		];
	}

	/**
	 * @param string $actionKey
	 * @param array $action
	 * @return array
	 */
	private function getHtmlActionData($actionKey, $action)
	{
		return [
			"ID" => $actionKey,
			"TYPE" => Panel\Types::CUSTOM,
			"VALUE" => $action["value"]
		];
	}

	/**
	 * @param string $actionKey
	 * @param array $action
	 * @return array
	 */
	private function getMultiControlActionData($actionKey, array $action)
	{
		return [
			"NAME" => $action["name"],
			"VALUE" => $actionKey,
			"ONCHANGE" => $action["action"]
		];
	}

	/**
	 * @param string $jsCallback
	 * @return array
	 */
	private function getApplyButtonCreationAction($jsCallback = "")
	{
		$action = $this->getDefaultApplyAction();
		if ($jsCallback != '')
		{
			$action["JS"] = $jsCallback;
		}
		return [
			"ACTION" => Panel\Actions::CREATE,
			"DATA" => [
				$this->gridSnippets->getApplyButton(
					[
						"ONCHANGE" => [
							[
								"ACTION" => Panel\Actions::CALLBACK,
								"DATA" => [
									$action
								]
							]
						]
					]
				)
			]
		];
	}

	/**
	 * @param $array
	 * @return bool
	 */
	private function isAssociativeArray($array)
	{
		if (!is_array($array) || empty($array))
			return false;
		return array_keys($array) !== range(0, count($array) - 1);
	}

	/**
	 * Prepare action data before add in action list.
	 *
	 * @param array &$action	Action description.
	 * return void
	 */
	private static function prepareAction(array &$action)
	{
		$action["type"] = (!empty($action["type"])? mb_strtolower($action["type"]) : "base");
		if ($action["type"] == "select")
		{
			if (!isset($action["controlName"]) && isset($action["name"]))
			{
				$action["controlName"] = $action["name"];
				unset($action["name"]);
			}
			if (!isset($action["controlId"]) && isset($action["controlName"]))
			{
				$action["controlId"] = $action["controlName"];
			}
		}
		if (!isset($action["name"]))
		{
			if (isset($action["lable"]))
			{
				$action["name"] = $action["lable"];
				unset($action["lable"]);
			}
			if (isset($action["label"]))
			{
				$action["name"] = $action["label"];
				unset($action["label"]);
			}
		}
	}
}

class CAdminUiListRow extends CAdminListRow
{
	public const LINK_TYPE_URL = 'url';
	public const DEFAULT_ACTION_TYPE_FIELD = 'DEFAULT_ACTION_TYPE';
	public const SKIP_URL_MODIFY_FIELD = 'SKIP_URL_MODIFICATION';
	public const LINK_TYPE_SLIDER = 'slider';

	/**
	 * @return array
	 */
	public function getPreparedActions()
	{
		$result = [];
		foreach ($this->aActions as $action)
		{
			if (isset($action["SEPARATOR"]))
				continue;

			if (empty($action["ACTION"]) && !empty($action["ONCLICK"]))
			{
				$action["ACTION"] = $action["ONCLICK"];
			}

			if (!empty($action["LINK"]) && empty($action["ACTION"]))
			{
				$action["href"] = $action["LINK"];
			}
			else
			{
				if (preg_match("/BX.adminPanel.Redirect/", $action["ACTION"]))
				{
					$explode = explode("'", $action["ACTION"]);
					if (!empty($explode[1]))
						$action["href"] = $explode[1];
				}
				else
				{
					$action["ONCLICK"] = $action["ACTION"];
				}
			}

			if ($this->isPublicMode)
			{
				if (!empty($action["href"]) &&
					!preg_match("/bitrix\/admin/i", $action["href"]) && preg_match("/\.php/i", $action["href"]))
				{
					$action["href"] = "/bitrix/admin/".$action["href"];
				}
			}

			$result[] = $action;
		}
		unset($action);

		return $result;
	}
}

class CAdminUiResult extends CAdminResult
{
	protected static $navParams = [
		"totalCount" => 0,
		"totalPages" => 1,
		"pagen" => 1
	];

	private $componentParams = array();

	/**
	 * @param string $tableId
	 * @param string $className Bitrix\Main\Entity\DataManager class name.
	 * @param array $getListParams
	 */
	public static function setNavParams($tableId, $className, &$getListParams)
	{
		if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "excel")
		{
			return;
		}

		$navyParams = CAdminUiResult::getNavParams(CAdminUiResult::getNavSize($tableId));
		if ($navyParams["SHOW_ALL"])
		{
			return;
		}
		else
		{
			$navyParams["PAGEN"] = (int)$navyParams["PAGEN"];
			$navyParams["SIZEN"] = (int)$navyParams["SIZEN"];
		}

		try
		{
			if (class_exists($className))
			{
				/**
				 * @var Bitrix\Main\Entity\DataManager $className
				 */
				$countQuery = new Bitrix\Main\Entity\Query($className::getEntity());
				$countQuery->addSelect(new Bitrix\Main\Entity\ExpressionField("CNT", "COUNT(1)"));
				$countQuery->setFilter($getListParams["filter"]);
				$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
				unset($countQuery);
				$totalCount = (int)$totalCount["CNT"];
				$totalPages = 1;

				$navyParams = CAdminUiResult::getNavParams(CAdminUiResult::getNavSize($tableId));
				if ($totalCount > 0)
				{
					$totalPages = ceil($totalCount/ $navyParams["SIZEN"]);
					if ($navyParams["PAGEN"] > $totalPages)
					{
						$navyParams["PAGEN"] = $totalPages;
					}
				}
				else
				{
					$navyParams["PAGEN"] = 1;
				}

				self::$navParams["totalCount"] = $totalCount;
				self::$navParams["totalPages"] = $totalPages;
				self::$navParams["pagen"] = $navyParams["PAGEN"];
			}
		}
		catch (Exception $exception)
		{
			$getListParams["limit"] = $navyParams["SIZEN"];
			$getListParams["offset"] = $navyParams["SIZEN"] * ($navyParams["PAGEN"] - 1);
		}

		$getListParams["limit"] = $navyParams["SIZEN"];
		$getListParams["offset"] = $navyParams["SIZEN"] * ($navyParams["PAGEN"] - 1);
	}

	public function NavStart($nPageSize=20, $bShowAll=true, $iNumPage=false)
	{
		$nSize = $this->GetNavSize($this->table_id, $nPageSize);

		if(!is_array($nPageSize))
			$nPageSize = array();

		$nPageSize["nPageSize"] = $nSize;
		if($_REQUEST["mode"] == "excel")
			$nPageSize["NavShowAll"] = true;

		$this->nInitialSize = $nPageSize["nPageSize"];

		$this->parentNavStart($nPageSize, $bShowAll, $iNumPage);

		if ((!isset($_REQUEST["mode"]) || $_REQUEST["mode"] != "excel") && !empty(self::$navParams["totalCount"]))
		{
			$this->NavRecordCount = self::$navParams["totalCount"];
			$this->NavPageCount = self::$navParams["totalPages"];
			$this->NavPageNomer = self::$navParams["pagen"];
		}
	}

	public function GetNavPrint($title, $show_allways=true, $StyleText="", $template_path=false, $arDeleteParam=false)
	{
		$componentObject = null;
		$this->bShowAll = false;
		return $this->getPageNavStringEx(
			$componentObject,
			"",
			"grid",
			false,
			null,
			$this->componentParams
		);
	}

	public static function GetNavSize($table_id = false, $nPageSize = 20, $listUrl = '')
	{
		$gridOptions = new Bitrix\Main\Grid\Options($table_id);
		$navParams = $gridOptions->getNavParams();
		return $navParams["nPageSize"];
	}

	public function setNavigationParams(array $params)
	{
		$gridOptions = new Bitrix\Main\Grid\Options($this->table_id);
		$this->componentParams = array_merge($params, $gridOptions->getNavParams());
	}
}

class CAdminUiContextMenu extends CAdminContextMenu
{
	private $isShownFilterContext = false;

	public function setFilterContextParam($bool)
	{
		$this->isShownFilterContext = $bool;
	}

	public function Show()
	{
		foreach (GetModuleEvents("main", "OnAdminContextMenuShow", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(&$this->items, &$this->additional_items));
		}

		if (empty($this->items) && empty($this->additional_items))
		{
			return;
		}

		\Bitrix\Main\UI\Extension::load(["ui.buttons", "ui.buttons.icons"]);

		if ($this->isPublicMode)
		{
			global $APPLICATION;
			ob_start();
			?><div
				class="pagetitle-container pagetitle-align-right-container"
				style="margin-right: 12px"
			><?php
				$this->showBaseButton();
			?></div><?php
			if (!$this->isShownFilterContext)
			{
				?><div class="pagetitle-container pagetitle-flexible-space"></div><?php
			}
			$APPLICATION->AddViewContent("inside_pagetitle", ob_get_clean());

			ob_start();
			?><div class="pagetitle-container pagetitle-align-right-container"><?php
				$this->showActionButton();
			?></div><?php
			$APPLICATION->AddViewContent("inside_pagetitle", ob_get_clean(), 700);
		}
		elseif ($this->isShownFilterContext)
		{
			?><div class="adm-toolbar-panel-align-right"><?php
				$this->showActionButton();
				$this->showBaseButton();
			?></div><?php
		}
		else
		{
			?><div class="adm-toolbar-panel-container">
				<div class="adm-toolbar-panel-flexible-space"></div>
				<div class="adm-toolbar-panel-align-right"><?php
					$this->showActionButton();
					$this->showBaseButton();
				?></div>
			</div><?php
		}
	}

	private function showActionButton()
	{
		if (!empty($this->additional_items))
		{
			if ($this->isPublicMode)
			{
				$menuUrl = "BX.adminList.showPublicMenu(this, ".HtmlFilter::encode(
					CAdminPopup::PhpToJavaScript($this->additional_items)).");";
			}
			else
			{
				$menuUrl = "BX.adminList.ShowMenu(this, ".HtmlFilter::encode(
					CAdminPopup::PhpToJavaScript($this->additional_items)).");";
			}

			?>
			<button class="ui-btn ui-btn-light-border ui-btn-themes ui-btn-icon-setting" onclick="
				<?=$menuUrl?>"></button>
			<?
		}
	}

	private function showBaseButton()
	{
		if (!empty($this->items))
		{
			$items = $this->items;
			$firstItem = array_shift($items);
			if (!empty($firstItem["MENU"]))
			{
				$items = array_merge($items, $firstItem["MENU"]);
			}
			if ($this->isPublicMode)
			{
				$menuUrl = "BX.adminList.showPublicMenu(this, ".HtmlFilter::encode(
					CAdminPopup::PhpToJavaScript($items)).");";
			}
			else
			{
				$menuUrl = "BX.adminList.ShowMenu(this, ".HtmlFilter::encode(
					CAdminPopup::PhpToJavaScript($items)).");";
			}
			$buttonId = !empty($firstItem["ID"]) ? "id=\"" . $firstItem["ID"] . "\"" : "";
			if (!empty($items)):?>
				<? if (!empty($firstItem["ONCLICK"])): ?>
					<div class="ui-btn-split ui-btn-primary">
						<button <?=$buttonId?> onclick="<?=HtmlFilter::encode($firstItem["ONCLICK"])?>" class="ui-btn-main">
							<?=HtmlFilter::encode($firstItem["TEXT"])?>
						</button>
						<button onclick="<?=$menuUrl?>" class="ui-btn-extra"></button>
					</div>
				<? else: ?>
					<? if (isset($firstItem["DISABLE"])): ?>
						<div class="ui-btn-split ui-btn-primary">
							<button <?=$buttonId?> onclick="<?=$menuUrl?>" class="ui-btn-main">
								<?=HtmlFilter::encode($firstItem["TEXT"])?>
							</button>
							<button onclick="<?=$menuUrl?>" class="ui-btn-extra"></button>
						</div>
					<? else: ?>
						<div class="ui-btn-split ui-btn-primary">
							<a <?=$buttonId?> href="<?=HtmlFilter::encode($firstItem["LINK"])?>" class="ui-btn-main">
								<?=HtmlFilter::encode($firstItem["TEXT"])?>
							</a>
							<button onclick="<?=$menuUrl?>" class="ui-btn-extra"></button>
						</div>
					<? endif; ?>
				<? endif; ?>
			<? else:?>
				<? if (!empty($firstItem["ONCLICK"])): ?>
					<button <?=$buttonId?> class="ui-btn ui-btn-primary" onclick="<?=HtmlFilter::encode($firstItem["ONCLICK"])?>">
						<?=HtmlFilter::encode($firstItem["TEXT"])?>
					</button>
				<? else: ?>
					<a <?=$buttonId?> class="ui-btn ui-btn-primary" href="<?=HtmlFilter::encode($firstItem["LINK"])?>">
						<?=HtmlFilter::encode($firstItem["TEXT"])?>
					</a>
				<? endif; ?>
			<?endif;
		}
	}
}

class CAdminUiSorting extends CAdminSorting
{
	/**
	 * @return array
	 */
	protected function getUserSorting()
	{
		$result = [
			'by' => null,
			'order' => null
		];
		$gridOptions = new Grid\Options($this->table_id);
		$sorting = $gridOptions->getSorting();
		if (!empty($sorting['sort']))
		{
			$order = reset($sorting['sort']);
			$result['by'] = key($sorting['sort']);
			$result['order'] = mb_strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
		}

		return $result;
	}
}
