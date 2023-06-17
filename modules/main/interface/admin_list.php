<?php

use Bitrix\Main;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Type\Collection;

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
class CAdminList
{
	public const MODE_PAGE = 'normal';
	public const MODE_LIST = 'list';
	public const MODE_ACTION = 'frame';
	public const MODE_EXPORT = 'excel';
	public const MODE_CONFIG = 'settings';

	protected const MODE_FIELD_NAME = 'mode';

	var $table_id;
	/** @var CAdminSorting */
	var $sort;
	var $aHeaders = array();
	var $aVisibleHeaders = array();
	/** @var CAdminListRow[] */
	var $aRows = array();
	var $aHeader = array();
	var $arVisibleColumns = array();
	var $aFooter = array();
	var $sNavText = '';
	var $arFilterErrors = Array();
	var $arUpdateErrors = array();
	var $arUpdateErrorIDs = Array();
	var $arGroupErrors = array();
	var $arGroupErrorIDs = Array();
	var $arActionSuccess = array();
	var $bEditMode = false;
	var $bMultipart = false;
	var $bCanBeEdited = false;
	var $bCanBeDeleted = false;
	var $arActions = Array();
	var $arActionsParams = Array();
	/** @var CAdminContextMenuList */
	var $context = false;
	var $sContent = false, $sPrologContent = '', $sEpilogContent = '';
	var $bShowActions;
	var $onLoadScript;
	var $arEditedRows;
	var $isPublicMode;

	private $filter;

	/** @var string */
	protected $mode = null;
	/** @var  Main\HttpRequest */
	protected $request;
	/** @var \Bitrix\Main\Session\SessionInterface */
	protected $session;

	/**
	 * @param string $table_id
	 * @param CAdminSorting|bool $sort
	 */
	public function __construct($table_id, $sort = false)
	{
		$this->request = Main\Context::getCurrent()->getRequest();
		$this->session = Main\Application::getInstance()->getSession();

		$this->table_id = preg_replace('/[^a-z0-9_]/i', '', $table_id);
		$this->sort = $sort;

		$this->setPublicModeState(defined('PUBLIC_MODE') && PUBLIC_MODE == 1);

		$this->initMode();
	}

	public function setPublicModeState(bool $mode): void
	{
		$this->isPublicMode = $mode;
		foreach (array_keys($this->aRows) as $index)
		{
			$this->aRows[$index]->setPublicModeState($mode);
		}
	}

	public function getPublicModeState(): bool
	{
		return $this->isPublicMode;
	}

	public function getFilter()
	{
		return $this->filter;
	}

	//id, name, content, sort, default
	public function AddHeaders($aParams)
	{
		$showAll = $this->request->get('showallcol');
		if ($showAll !== null && $showAll !== '')
		{
			$this->session['SHALL'] = $showAll === 'Y';
		}
		$showAll = isset($this->session['SHALL']) && $this->session['SHALL'];

		$aOptions = CUserOptions::GetOption("list", $this->table_id, array());

		$aColsTmp = explode(",", $aOptions["columns"] ?? '');
		$aCols = array();
		$userColumns = array();
		foreach ($aColsTmp as $col)
		{
			$col = trim($col);
			if ($col <> "")
			{
				$aCols[] = $col;
				$userColumns[$col] = true;
			}
		}

		$bEmptyCols = empty($aCols);
		$userVisibleColumns = array();
		foreach ($aParams as $param)
		{
			$param['default'] = (bool)($param['default'] ?? false);
			$param["__sort"] = -1;
			$this->aHeaders[$param["id"]] = $param;
			if (
				$showAll
				|| ($bEmptyCols && $param["default"])
				|| isset($userColumns[$param["id"]])
			)
			{
				$this->arVisibleColumns[] = $param["id"];
				$userVisibleColumns[$param["id"]] = true;
			}
		}
		unset($userColumns);

		$aAllCols = ($this->isConfigMode() ? $this->aHeaders : null);

		if (!$bEmptyCols)
		{
			foreach ($aCols as $i => $col)
				if (isset($this->aHeaders[$col]))
					$this->aHeaders[$col]["__sort"] = $i;

			Collection::sortByColumn($this->aHeaders, ['__sort' => SORT_ASC], '', null, true);
		}

		foreach($this->aHeaders as $id=>$arHeader)
		{
			if (isset($userVisibleColumns[$id]))
				$this->aVisibleHeaders[$id] = $arHeader;
		}
		unset($userVisibleColumns);

		if ($this->isConfigMode())
		{
			$this->ShowSettings($aAllCols, $aCols, $aOptions);
		}
	}

	/** @noinspection PhpUnusedParameterInspection  */
	public function ShowSettings($aAllCols, $aCols, $aOptions)
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $USER;

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
		require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/interface/settings_admin_list.php");
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
		die();
	}

	public function AddVisibleHeaderColumn($id)
	{
		if (isset($this->aHeaders[$id]) && !isset($this->aVisibleHeaders[$id]))
		{
			$this->arVisibleColumns[] = $id;
			$this->aVisibleHeaders[$id] = $this->aHeaders[$id];
		}
	}

	public function GetVisibleHeaderColumns()
	{
		return $this->arVisibleColumns;
	}

	public function AddAdminContextMenu($aContext=array(), $bShowExcel=true, $bShowSettings=true)
	{
		$config = [];
		if ($bShowSettings)
		{
			$config['settings'] = true;
		}
		if ($bShowExcel)
		{
			$config['excel'] = true;
		}
		$this->SetContextMenu($aContext, [], $config);
	}

	public function SetContextMenu(array $menu = [], array $additional = [], array $config = []): void
	{
		$this->InitContextMenu(
			$menu,
			array_merge($additional, $this->GetSystemContextMenu($config))
		);
	}

	protected function GetSystemContextMenu(array $config = []): array
	{
		$result = [];
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$queryString = DeleteParam([self::MODE_FIELD_NAME]);
		if ($queryString !== '')
		{
			$queryString = '&' . $queryString;
		}
		$link = $APPLICATION->GetCurPage();
		if (isset($config['settings']))
		{
			$result[] = [
				"TEXT" => GetMessage("admin_lib_context_sett"),
				"TITLE" => GetMessage("admin_lib_context_sett_title"),
				"ONCLICK" => $this->table_id . ".ShowSettings('" . CUtil::JSEscape(
					$link . "?" . static::getModeConfigUrlParam() . $queryString
				) . "')",
				"GLOBAL_ICON" => "adm-menu-setting",
			];
		}
		if (isset($config['excel']))
		{
			$result[] = [
				"TEXT" => "Excel",
				"TITLE" => GetMessage("admin_lib_excel"),
				"ONCLICK"=>"location.href='" . htmlspecialcharsbx(
					$link . "?" . static::getModeExportUrlParam() . $queryString
				) . "'",
				"GLOBAL_ICON"=>"adm-menu-excel",
			];
		}
		return $result;
	}

	protected function InitContextMenu(array $menu = [], array $additional = []): void
	{
		if (!empty($menu) || !empty($additional))
		{
			$this->context = new CAdminContextMenuList($menu, $additional);
		}
	}

	/**
	 * @param string|int $ID
	 * @return bool
	 */
	public function IsUpdated($ID)
	{
		$f = $_REQUEST['FIELDS'][$ID];
		$f_old = $_REQUEST['FIELDS_OLD'][$ID];

		if(!is_array($f) || !is_array($f_old))
			return true;

		foreach($f as $k=>$v)
		{
			if(is_array($v))
			{
				if(!is_array($f_old[$k]))
					return true;
				else
				{
					foreach($v as $k2 => $v2)
					{
						if($f_old[$k][$k2] !== $v2)
							return true;
						unset($f_old[$k][$k2]);
					}
					if(!empty($f_old[$k]))
						return true;
				}
			}
			else
			{
				if(isset($f_old[$k]) && is_array($f_old[$k]))
				{
					return true;
				}
				elseif(!isset($f_old[$k]) || $f_old[$k] !== $v)
				{
					return true;
				}
			}
			unset($f_old[$k]);
		}
		if(!empty($f_old))
			return true;

		return false;
	}

	/**
	 * @return bool
	 */
	public function EditAction()
	{
		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_REQUEST['save'])  && check_bitrix_sessid())
		{
			$arrays = array(&$_POST, &$_REQUEST);
			foreach($arrays as $i => $array)
			{
				if(is_array($array["FIELDS"]))
				{
					foreach($array["FIELDS"] as $id=>$fields)
					{
						if(is_array($fields))
						{
							$keys = array_keys($fields);
							foreach($keys as $key)
							{
								if(($c = substr($key,0,1)) == '~' || $c == '=')
								{
									unset($arrays[$i]["FIELDS"][$id][$key]);
								}
							}
						}
					}
				}
			}
			if (is_array($GLOBALS["FIELDS"]))
			{
				foreach ($GLOBALS["FIELDS"] as $id => $fields)
				{
					if (is_array($fields))
					{
						$keys = array_keys($fields);
						foreach ($keys as $key)
						{
							if (($c = substr($key,0,1)) == '~' || $c == '=')
							{
								unset($GLOBALS["FIELDS"][$id][$key]);
							}
						}
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Returns field values in for inline edit grid mode.
	 *
	 * @return array
	 */
	public function GetEditFields(): array
	{
		$result = [];
		if (isset($_REQUEST['FIELDS']) && is_array($_REQUEST['FIELDS']))
		{
			foreach (array_keys($_REQUEST['FIELDS']) as $id)
			{
				if (empty($id) || !$this->IsUpdated($id))
				{
					continue;
				}
				$result[$id] = $_REQUEST['FIELDS'][$id];
			}
		}
		return $result;
	}

	/**
	 * Copy files from uploader to edit fields.
	 *
	 * @return void
	 */
	public function ConvertFilesToEditFields(): void
	{
		if (!empty($_FILES['FIELDS']) && is_array($_FILES['FIELDS']))
		{
			CFile::ConvertFilesToPost($_FILES['FIELDS'], $_REQUEST['FIELDS']);
		}
	}

	/**
	 * @return array|false
	 */
	public function GroupAction()
	{
		$this->PrepareAction();

		if (!check_bitrix_sessid())
		{
			return false;
		}

		$action = $this->GetAction();
		if ($action === null)
		{
			return false;
		}

		if($action=="edit")
		{
			$arID = $this->GetGroupIds();
			if ($arID !== null)
			{
				$this->arEditedRows = $arID;
				$this->bEditMode = true;
			}
			return false;
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
			$arID = array('');
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
		return (isset($_REQUEST['action_target']) && $_REQUEST['action_target'] === 'selected');
	}

	/**
	 * @return void
	 */
	protected function PrepareAction()
	{
		if (!empty($_REQUEST['action_button']))
		{
			$_REQUEST['action'] = $_REQUEST['action_button'];
		}
	}

	/**
	 * @return string|null
	 */
	public function GetAction()
	{
		return ($_REQUEST['action'] ?? null);
	}

	/**
	 * @return array|null
	 */
	protected function GetGroupIds()
	{
		$result = null;
		if (isset($_REQUEST['ID']))
		{
			$result = (!is_array($_REQUEST['ID']) ? array($_REQUEST['ID']) : $_REQUEST['ID']);
		}
		return $result;
	}

	/**
	 * @return void
	 */
	protected function initMode(): void
	{
		$this->mode = self::MODE_PAGE;
		$mode = $this->request->get(self::MODE_FIELD_NAME);
		if (
			is_string($mode)
			&& (in_array(
				$mode,
				[self::MODE_LIST, self::MODE_ACTION, self::MODE_EXPORT, self::MODE_CONFIG]
			))
		)
		{
			$this->mode = $mode;
		}
	}

	/**
	 * @return string
	 */
	public function getCurrentMode(): string
	{
		return $this->mode;
	}

	/**
	 * @return bool
	 */
	public function isPageMode(): bool
	{
		return $this->getCurrentMode() === self::MODE_PAGE;
	}

	/**
	 * @return bool
	 */
	public function isExportMode(): bool
	{
		return $this->getCurrentMode() === self::MODE_EXPORT;
	}

	/**
	 * @return bool
	 */
	public function isAjaxMode(): bool
	{
		$mode = $this->getCurrentMode();
		return ($mode === self::MODE_LIST || $mode === self::MODE_ACTION);
	}

	/**
	 * @return bool
	 */
	public function isConfigMode(): bool
	{
		return $this->getCurrentMode() === self::MODE_CONFIG;
	}

	/**
	 * @return bool
	 */
	public function isActionMode(): bool
	{
		return $this->getCurrentMode() === self::MODE_ACTION;
	}

	/**
	 * @return bool
	 */
	public function isListMode(): bool
	{
		return $this->getCurrentMode() === self::MODE_LIST;
	}

	public function ActionRedirect($url)
	{
		$url = (string)$url;
		if ($this->isPublicMode)
		{
			$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
			if (strpos($url,$selfFolderUrl) === false)
			{
				$url = $selfFolderUrl.$url;
			}
		}

		return "BX.adminPanel.Redirect([], '".static::getUrlWithLanguage($url)."', event);";
	}

	public function ActionAjaxReload($url)
	{
		return $this->table_id.".GetAdminList('".static::getUrlWithLanguage((string)$url)."');";
	}

	public function ActionPost($url = false, $action_name = false, $action_value = 'Y')
	{
		$res = '';
		if ($url)
		{
			$url = static::getUrlWithLanguage((string)$url, false);

			if (strpos($url,self::MODE_FIELD_NAME . '=') === false)
			{
				$url .= '&' . static::getModeActionUrlParam();
			}

			$res = 'BX(\'form_'.$this->table_id.'\').action=\''.CUtil::AddSlashes($url).'\';';
		}

		if ($action_name)
			return $res.'; BX.submit(document.forms.form_'.$this->table_id.', \''.CUtil::JSEscape($action_name).'\', \''.CUtil::JSEscape($action_value).'\');';
		else
			return $res.'; BX.submit(document.forms.form_'.$this->table_id.');';
	}

	public function ActionDoGroup($id, $action_id, $add_params='')
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		return $this->table_id.".GetAdminList('".CUtil::AddSlashes($APPLICATION->GetCurPage())."?ID=".CUtil::AddSlashes($id)."&action_button=".CUtil::AddSlashes($action_id)."&lang=".LANGUAGE_ID."&".bitrix_sessid_get().($add_params<>""?"&".CUtil::AddSlashes($add_params):"")."');";
	}

	public function InitFilter($arFilterFields)
	{
		//Filter by link from favorites. Extract fields.
		if(isset($_REQUEST['adm_filter_applied']) && intval($_REQUEST['adm_filter_applied']) > 0)
		{
			$dbRes = \CAdminFilter::GetList(array(), array('ID' => intval($_REQUEST['adm_filter_applied'])));

			if($row = $dbRes->Fetch())
			{
				$fields = unserialize($row['FIELDS'], ['allowed_classes' => false]);

				if(is_array($fields) && !empty($fields))
				{
					foreach($fields as $field => $params)
					{
						if(isset($params['value']))
						{
							if(!isset($params['hidden']) || $params['hidden'] != 'true')
							{
								$GLOBALS[$field] = $params['value'];

								if($GLOBALS['set_filter'] != 'Y')
									$GLOBALS['set_filter'] = 'Y';
							}
						}
					}
				}
			}
		}

		$sTableID = $this->table_id;
		global $del_filter, $set_filter, $save_filter;
		if($del_filter <> "")
			DelFilterEx($arFilterFields, $sTableID);
		elseif($set_filter <> "")
		{
			CAdminFilter::UnEscape($arFilterFields);
			InitFilterEx($arFilterFields, $sTableID, "set");
		}
		elseif($save_filter <> "")
		{
			CAdminFilter::UnEscape($arFilterFields);
		}
		else
			InitFilterEx($arFilterFields, $sTableID, "get");

		foreach ($arFilterFields as $f)
		{
			$fperiod = $f."_FILTER_PERIOD";
			$fdirection = $f."_FILTER_DIRECTION";
			$fbdays = $f."_DAYS_TO_BACK";

			global $$f, $$fperiod, $$fdirection, $$fbdays;
			if (isset($$f))
				$this->filter[$f] = $$f;
			if (isset($$fperiod))
				$this->filter[$fperiod] = $$fperiod;
			if (isset($$fdirection))
				$this->filter[$fdirection] = $$fdirection;
			if (isset($$fbdays))
				$this->filter[$fbdays] = $$fbdays;
		}

		return $this->filter;
	}

	public function IsDefaultFilter()
	{
		global $set_default;
		$sTableID = $this->table_id;
		return $set_default=="Y"
			&& (
				!isset($this->session["SESS_ADMIN"][$sTableID])
				|| empty($this->session["SESS_ADMIN"][$sTableID])
			)
		;
	}

	public function &AddRow($id = false, $arRes = Array(), $link = false, $title = false)
	{
		$row = new CAdminListRow($this->aHeaders, $this->table_id);
		$row->id = $id;
		$row->arRes = $arRes;
		$row->link = $link;
		$row->title = $title;
		$row->pList = &$this;

		if($id)
		{
			if($this->bEditMode && in_array($id, $this->arEditedRows))
				$row->bEditMode = true;
			elseif(!empty($this->arUpdateErrorIDs) && in_array($id, $this->arUpdateErrorIDs))
				$row->bEditMode = true;
		}

		$row->setPublicModeState($this->getPublicModeState());

		$this->aRows[] = &$row;
		return $row;
	}

	public function AddFooter($aFooter)
	{
		$this->aFooter = $aFooter;
	}

	public function NavText($sNavText)
	{
		$this->sNavText = $sNavText;
	}

	/**
	 * @param \Bitrix\Main\UI\PageNavigation $nav
	 * @param string $title
	 * @param bool $showAllways
	 * @param bool $post
	 */
	public function setNavigation(\Bitrix\Main\UI\PageNavigation $nav, $title, $showAllways = true, $post = false)
	{
		global $APPLICATION;

		ob_start();

		$APPLICATION->IncludeComponent(
			"bitrix:main.pagenavigation",
			"admin",
			array(
				"NAV_OBJECT" => $nav,
				"TITLE" => $title,
				"PAGE_WINDOW" => 10,
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

	public function Display()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		foreach(GetModuleEvents("main", "OnAdminListDisplay", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$this));

		// Check after event handlers
		if (!is_array($this->arActions))
		{
			$this->arActions = [];
		}
		if (!is_array($this->arActionsParams))
		{
			$this->arActionsParams = [];
		}

		$errmsg = '';
		foreach ($this->arFilterErrors as $err)
			$errmsg .= ($errmsg<>''? '<br>': '').$err;
		foreach ($this->arUpdateErrors as $err)
			$errmsg .= ($errmsg<>''? '<br>': '').$err[0];
		foreach ($this->arGroupErrors as $err)
			$errmsg .= ($errmsg<>''? '<br>': '').$err[0];
		if($errmsg<>'')
			CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("admin_lib_error"), "DETAILS"=>$errmsg, "TYPE"=>"ERROR"));

		$successMessage = '';
		for ($i = 0, $cnt = count($this->arActionSuccess); $i < $cnt; $i++)
			$successMessage .= ($successMessage != '' ? '<br>' : '').$this->arActionSuccess[$i];
		if ($successMessage != '')
			CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("admin_lib_success"), "DETAILS" => $successMessage, "TYPE" => "OK"));

		echo $this->sPrologContent;

		if($this->sContent===false)
		{
?>
<div class="adm-list-table-wrap<?=$this->context ? '' : ' adm-list-table-without-header'?><?= empty($this->arActions) && !$this->bCanBeEdited ? ' adm-list-table-without-footer' : ''?>">
<?
		}

		if($this->context)
			$this->context->Show();

		if ($this->isAjaxDebug())
		{
			echo '<form method="POST" '
				.($this->bMultipart?' enctype="multipart/form-data" ':'')
				.' onsubmit="CheckWin();ShowWaitWindow();" target="frame_debug" id="form_'.$this->table_id.'" name="form_'.$this->table_id.'" '
				.'action="'.htmlspecialcharsbx($APPLICATION->GetCurPageParam(
					static::getModeActionUrlParam(),
					[self::MODE_FIELD_NAME]
				)).'">'
			;
		}
		else
		{
			echo '<form method="POST" '
				.($this->bMultipart?' enctype="multipart/form-data" ':'')
				.' onsubmit="return BX.ajax.submitComponentForm(this, \''.$this->table_id.'_result_div\', true);" '
				.'id="form_'.$this->table_id.'" name="form_'.$this->table_id.'" '
				.'action="'.htmlspecialcharsbx($APPLICATION->GetCurPageParam(
					static::getModeActionUrlParam(),
					[self::MODE_FIELD_NAME, "action", "action_button"]
				)).'">'
			;
		}

		if($this->bEditMode && !$this->bCanBeEdited)
			$this->bEditMode = false;

		if($this->sContent!==false)
		{
			echo $this->sContent;
			echo '</form>';
			return;
		}

		$bShowSelectAll = (!empty($this->arActions) || $this->bCanBeEdited);
		$this->bShowActions = false;
		foreach($this->aRows as $row)
		{
			if(!empty($row->aActions))
			{
				$this->bShowActions = true;
				break;
			}
		}

		//!!! insert filter's hiddens
		echo bitrix_sessid_post();
		//echo $this->sNavText;

		$colSpan = 0;
?>
<table class="adm-list-table" id="<?=$this->table_id;?>">
	<thead>
		<tr class="adm-list-table-header">
<?
		if($bShowSelectAll):
?>
			<td class="adm-list-table-cell adm-list-table-checkbox" onclick="this.firstChild.firstChild.click(); return BX.PreventDefault(event);"><div class="adm-list-table-cell-inner"><input class="adm-checkbox adm-designed-checkbox" type="checkbox" id="<?=$this->table_id?>_check_all" onclick="<?=$this->table_id?>.SelectAllRows(this); return BX.eventCancelBubble(event);" title="<?=GetMessage("admin_lib_list_check_all")?>" /><label for="<?=$this->table_id?>_check_all" class="adm-designed-checkbox-label"></label></div></td>
<?
			$colSpan++;
		endif;

		if($this->bShowActions):
?>
			<td class="adm-list-table-cell adm-list-table-popup-block" title="<?=GetMessage("admin_lib_list_act")?>"><div class="adm-list-table-cell-inner"></div></td>
<?
			$colSpan++;
		endif;

		foreach($this->aVisibleHeaders as $header):
			$bSort = $this->sort && !empty($header["sort"]);
			$header['title'] = (string)($header['title'] ?? '');

			if ($bSort)
				$attrs = $this->sort->Show($header["content"], $header["sort"], $header["title"], "adm-list-table-cell");
			else
				$attrs = 'class="adm-list-table-cell"';

?>
			<td <?=$attrs?>>
				<div class="adm-list-table-cell-inner"><?=$header["content"]?></div>
			</td>
<?
			$colSpan++;
		endforeach;
?>
		</tr>
	</thead>
	<tbody>
<?
		if(!empty($this->aRows)):
			foreach($this->aRows as $row)
			{
				$row->Display();
			}
		elseif(!empty($this->aHeaders)):
?>
		<tr><td colspan="<?=$colSpan?>" class="adm-list-table-cell adm-list-table-empty"><?=GetMessage("admin_lib_no_data")?></td></tr>
<?
		endif;
?>
	</tbody>
</table>
<?
		$this->ShowActionTable();

// close form and div.adm-list-table-wrap

		echo $this->sEpilogContent;
		echo '
	</form>
</div>
';
		echo $this->sNavText;
	}

	public function DisplayExcel()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		echo '
		<html>
		<head>
		<title>'.$APPLICATION->GetTitle().'</title>
		<meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'">
		<style>
			td {mso-number-format:\@;}
			.number0 {mso-number-format:0;}
			.number2 {mso-number-format:Fixed;}
		</style>
		</head>
		<body>';

		echo "<table border=\"1\">";
		echo "<tr>";

		foreach($this->aVisibleHeaders as $header)
		{
			echo '<td>';
			echo $header["content"];
			echo '</td>';
		}
		echo "</tr>";


		foreach($this->aRows as $row)
		{
			echo "<tr>";
			foreach($this->aVisibleHeaders as $id=>$header_props)
			{
				$field = $row->aFields[$id];
				if(!is_array($row->arRes[$id]))
					$val = trim($row->arRes[$id]);
				else
					$val = $row->arRes[$id];

				switch($field["view"]["type"])
				{
					case "checkbox":
						if($val=='Y')
							$val = htmlspecialcharsex(GetMessage("admin_lib_list_yes"));
						else
							$val = htmlspecialcharsex(GetMessage("admin_lib_list_no"));
						break;
					case "select":
						if (isset($field["edit"]["values"][$val]))
						{
							$val = htmlspecialcharsex($field["edit"]["values"][$val]);
						}
						elseif (isset($field["view"]["values"][$val]))
						{
							$val = htmlspecialcharsex($field["view"]["values"][$val]);
						}
						else
						{
							$val = htmlspecialcharsex($val);
						}
						break;
					case "file":
						$arFile = CFile::GetFileArray($val);
						if(is_array($arFile))
							$val = htmlspecialcharsex((new Uri($arFile["SRC"]))->toAbsolute()->getUri());
						else
							$val = "";
						break;
					case "html":
						$val = trim(strip_tags($field["view"]['value'], "<br>"));
						break;
					default:
						$val = htmlspecialcharsex($val);
						break;
				}

				echo '<td';
				if ($header_props['align'])
					echo ' align="'.$header_props['align'].'"';
				if ($header_props['valign'])
					echo ' valign="'.$header_props['valign'].'"';
				if ($header_props['align'] === "right" && preg_match("/^([1-9][0-9]*|[1-9][0-9]*[.,][0-9]+)\$/", $val))
					echo ' class="number0"';
				echo '>';
				echo ($val<>""? $val: '&nbsp;');
				echo '</td>';
			}
			echo "</tr>";
		}

		echo "</table>";
		echo '</body></html>';
	}

	public function AddGroupActionTable($arActions, $arParams=array())
	{
		if (!is_array($arActions))
		{
			$arActions = [];
		}
		if (!is_array($arParams))
		{
			$arParams = [];
		}
		//array("action"=>"text", ...)
		//OR array(array("action" => "custom JS", "value" => "action", "type" => "button", "title" => "", "name" => ""), ...)
		$this->arActions = $arActions;
		//array("disable_action_target"=>true, "select_onchange"=>"custom JS")
		$this->arActionsParams = $arParams;
	}

	public function ShowActionTable()
	{
		if (empty($this->arActions) && !$this->bCanBeEdited)
			return;
?>
<div class="adm-list-table-footer" id="<?=$this->table_id?>_footer<?=$this->bEditMode || !empty($this->arUpdateErrorIDs) ? '_edit' : ''?>">
	<input type="hidden" name="action_button" id="<?=$this->table_id; ?>_action_button" value="" />
<?
		if($this->bEditMode || !empty($this->arUpdateErrorIDs)):
			$this->DisplayEditButtons();
		else: //($this->bEditMode || count($this->arUpdateErrorIDs)>0)
			if (!isset($this->arActionsParams["disable_action_target"]) || $this->arActionsParams["disable_action_target"] !== true):
?>
	<span class="adm-selectall-wrap"><input type="checkbox" class="adm-checkbox adm-designed-checkbox" name="action_target" value="selected" id="action_target" onclick="if(this.checked && !confirm('<?=CUtil::JSEscape(GetMessage("admin_lib_list_edit_for_all_warn"))?>')) {this.checked=false;} <?=$this->table_id?>.EnableActions();" title="<?=GetMessage("admin_lib_list_edit_for_all")?>" /><label for="action_target" class="adm-checkbox adm-designed-checkbox-label"></label><label title="<?=GetMessage("admin_lib_list_edit_for_all")?>" for="action_target" class="adm-checkbox-label"><?=GetMessage("admin_lib_list_for_all");?></label></span>
<?
			endif;

			$this->bCanBeDeleted = array_key_exists("delete", $this->arActions);

			if ($this->bCanBeEdited || $this->bCanBeDeleted)
			{
				echo '
	<span class="adm-table-item-edit-wrap'.(!$this->bCanBeEdited || !$this->bCanBeDeleted ? ' adm-table-item-edit-single' : '').'">
';
				if($this->bCanBeEdited)
				{
					echo '<a href="javascript:void(0)" class="adm-table-btn-edit adm-edit-disable" hidefocus="true" onclick="this.blur();if('.$this->table_id.'.IsActionEnabled(\'edit\')){document.forms[\'form_'.$this->table_id.'\'].elements[\'action_button\'].value=\'edit\'; '.
						htmlspecialcharsbx($this->ActionPost(false, 'action_button', 'edit')).'}" title="'.GetMessage("admin_lib_list_edit").'" id="action_edit_button"></a>';
				}
				if($this->bCanBeDeleted)
				{
					echo '<a href="javascript:void(0);" class="adm-table-btn-delete adm-edit-disable" hidefocus="true" onclick="this.blur();if('.$this->table_id.'.IsActionEnabled() && confirm((document.getElementById(\'action_target\') && document.getElementById(\'action_target\').checked? \''.GetMessage("admin_lib_list_del").'\':\''.GetMessage("admin_lib_list_del_sel").'\'))) {document.forms[\'form_'.$this->table_id.'\'].elements[\'action_button\'].value=\'delete\'; '.
						htmlspecialcharsbx($this->ActionPost(false, 'action_button', 'delete')).'}" title="'.GetMessage("admin_lib_list_del_title").'" class="context-button icon action-delete-button-dis" id="action_delete_button"></a>';
				}
				echo '
	</span>
';
			}

			$onchange = '';
			if (isset($this->arActionsParams["select_onchange"]))
			{
				if (is_array($this->arActionsParams["select_onchange"]))
				{
					$onchange = implode(' ', $this->arActionsParams["select_onchange"]);
				}
				elseif (is_string($this->arActionsParams["select_onchange"]))
				{
					$onchange = $this->arActionsParams["select_onchange"];
				}
			}

			$list = '';
			$html = '';
			$buttons = '';
			$actionList = array_filter($this->arActions);
			if (isset($actionList['delete']))
			{
				unset($actionList['delete']);
			}

			$allowedTypes = [
				'button' => true,
				'html' => true
			];

			foreach($actionList as $k=>$v)
			{
				if(is_array($v))
				{
					if (isset($v['type']) && isset($allowedTypes[$v['type']]))
					{
						switch ($v["type"])
						{
							case 'button':
								$buttons .= '<input type="button" name="" value="'.htmlspecialcharsbx($v['name']).'" onclick="'.(!empty($v["action"])? htmlspecialcharsbx($v['action']) : 'document.forms[\'form_'.$this->table_id.'\'].elements[\'action_button\'].value=\''.htmlspecialcharsbx($v["value"]).'\'; '.htmlspecialcharsbx($this->ActionPost()).'').'" title="'.htmlspecialcharsbx($v["title"]).'" />';
								break;
							case 'html':
								$html .= '<span class="adm-list-footer-ext">'.$v["value"].'</span>';
								break;
						}
					}
					else
					{
						$list .= '<option value="'.htmlspecialcharsbx($v['value']).'"'.($v['action']?' custom_action="'.htmlspecialcharsbx($v['action']).'"':'').'>'.htmlspecialcharsex($v['name']).'</option>';
					}
				}
				else
				{
					$list .= '<option value="'.htmlspecialcharsbx($k).'">'.htmlspecialcharsex($v).'</option>';
				}
			}
			unset($actionList, $k, $v);
			unset($allowedTypes);

			if ($buttons != '')
				echo '<span class="adm-list-footer-ext">'.$buttons.'</span>';

			if ($list != ''):
?>
	<span class="adm-select-wrap">
		<select name="action" id="<?=$this->table_id.'_action'; ?>" class="adm-select"<?=($onchange != '' ? ' onchange="'.htmlspecialcharsbx($onchange).'"':'')?>>
			<option value=""><?=GetMessage("admin_lib_list_actions")?></option>
<?=$list?>
		</select>
	</span>
<?
				if ($html != '')
					echo $html;
?>
	<input type="submit" name="apply" value="<?=GetMessage("admin_lib_list_apply")?>" onclick="if(this.form.action[this.form.action.selectedIndex].getAttribute('custom_action')){eval(this.form.action[this.form.action.selectedIndex].getAttribute('custom_action'));return false;}" disabled="disabled" class="adm-table-action-button" />
<?
			endif; //(strlen($list) > 0)
?>
	<span class="adm-table-counter" id="<?=$this->table_id?>_selected_count"><?=GetMessage('admin_lib_checked')?>: <span>0</span></span>
<?
		endif; // ($this->bEditMode || count($this->arUpdateErrorIDs)>0):
?>
</div>
<?
	}

	public function DisplayList($arParams = array())
	{
		$menu = new CAdminPopup($this->table_id."_menu", $this->table_id."_menu");
		$menu->Show();

		if ($this->isAjaxDebug())
		{
			echo '<script>
				function CheckWin()
				{
					window.open("about:blank", "frame_debug");
				}
				</script>';
		}
		else
		{
			echo '<iframe src="javascript:\'\'" id="frame_'.$this->table_id.'" name="frame_'.$this->table_id.'" style="width:1px; height:1px; border:0px; position:absolute; left:-10px; top:-10px; z-index:0;"></iframe>';
		}

		$aUserOpt = CUserOptions::GetOption("global", "settings");
		if (!is_array($aUserOpt))
		{
			$aUserOpt = [];
		}
		$aUserOpt['context_ctrl'] = (string)($aUserOpt['context_ctrl'] ?? 'N');
		$aUserOpt['context_menu'] = (string)($aUserOpt['context_menu'] ?? 'Y');

		if (!is_array($arParams))
			$arParams = array();

		if (!isset($arParams['FIX_HEADER']))
			$arParams['FIX_HEADER'] = true;
		if (!isset($arParams['FIX_FOOTER']))
			$arParams['FIX_FOOTER'] = true;
		if (!isset($arParams['context_ctrl']))
			$arParams['context_ctrl'] = ($aUserOpt["context_ctrl"] === "Y");
		if (!isset($arParams['context_menu']))
			$arParams['context_menu'] = ($aUserOpt["context_menu"] !== "N");

		$tbl = CUtil::JSEscape($this->table_id);
?>
<script type="text/javascript">
window['<?=$tbl?>'] = new BX.adminList('<?=$tbl?>', <?=CUtil::PhpToJsObject($arParams)?>);
BX.adminChain.addItems("<?=$tbl?>_navchain_div");
</script>
<?

		echo '<div id="'.$this->table_id.'_result_div" class="adm-list-table-layout">';
		$this->Display();
		echo '</div>';
	}

	public function AddUpdateError($strError, $id = false)
	{
		$this->arUpdateErrors[] = Array($strError, $id);
		$this->arUpdateErrorIDs[] = $id;
	}

	public function AddGroupError($strError, $id = false)
	{
		$this->arGroupErrors[] = Array($strError, $id);
		$this->arGroupErrorIDs[] = $id;
	}

	public function AddActionSuccessMessage($strMessage)
	{
		$this->arActionSuccess[] = $strMessage;
	}

	public function AddFilterError($strError)
	{
		$this->arFilterErrors[] = $strError;
	}

	public function BeginPrologContent()
	{
		ob_start();
	}

	public function EndPrologContent()
	{
		$this->sPrologContent .= ob_get_contents();
		ob_end_clean();
	}

	public function BeginEpilogContent()
	{
		ob_start();
	}

	public function EndEpilogContent()
	{
		$this->sEpilogContent .= ob_get_contents();
		ob_end_clean();
	}

	public function BeginCustomContent()
	{
		ob_start();
	}

	public function EndCustomContent()
	{
		$this->sContent = ob_get_contents();
		ob_end_clean();
	}

	public function CreateChain()
	{
		return new CAdminChain($this->table_id."_navchain_div", false);
	}

	/**
	 * @param CAdminChain $chain
	 */
	public function ShowChain($chain)
	{
		$this->BeginPrologContent();
		$chain->Show();
		$this->EndPrologContent();
	}

	public function CheckListMode()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if ($this->isPageMode())
		{
			return;
		}

		if ($this->isAjaxMode())
		{
			ob_start();
			$this->Display();
			$string = ob_get_contents();
			ob_end_clean();

			if ($this->isActionMode())
			{
?>
<html><head></head><body><?=$string?><script type="text/javascript">
	var topWindow = (window.BX||window.parent.BX).PageObject.getRootWindow();
	topWindow.bxcompajaxframeonload = function() {
	topWindow.BX.adminPanel.closeWait();
	topWindow.<?=$this->table_id?>.Destroy(false);
	topWindow.<?=$this->table_id?>.Init();
<?
				if(isset($this->onLoadScript)):
?>
	topWindow.BX.evalGlobal('<?=CUtil::JSEscape($this->onLoadScript)?>');
<?
				endif;
?>
};
topWindow.BX.ajax.UpdatePageData({});
</script></body></html>
<?
			}
			else
			{
				if(isset($this->onLoadScript)):
?>
<script type="text/javascript"><?=$this->onLoadScript?></script>
<?
				endif;

				echo $string;
			}
			define("ADMIN_AJAX_MODE", true);
			require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
			die();
		}
		elseif ($this->isExportMode())
		{
			$fname = basename($APPLICATION->GetCurPage(), ".php");
			// http response splitting defence
			$fname = str_replace(array("\r", "\n"), "", $fname);

			header("Content-Type: application/vnd.ms-excel");
			header("Content-Disposition: filename=".$fname.".xls");
			$APPLICATION->EndBufferContentMan();
			$this->DisplayExcel();
			require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
			die();
		}
	}

	protected function DisplayEditButtons(): void
	{
?>
		<input type="hidden" name="save" id="<?=$this->table_id?>_hidden_save" value="Y">
		<input type="submit" class="adm-btn-save" name="save" value="<?=GetMessage("admin_lib_list_edit_save")?>" title="<?=GetMessage("admin_lib_list_edit_save_title")?>" />
		<input type="button" onclick="BX('<?=$this->table_id?>_hidden_save').name='cancel'; <?=htmlspecialcharsbx($this->ActionPost(false, 'action_button', ''))?> " name="cancel" value="<?=GetMessage("admin_lib_list_edit_cancel")?>" title="<?=GetMessage("admin_lib_list_edit_cancel_title")?>" />
<?
	}

	protected function isAjaxDebug(): bool
	{
		return
			$this->request->get('ajax_debugx') === 'Y'
			|| (isset($this->session['AJAX_DEBUGX']) && $this->session['AJAX_DEBUGX'])
		;
	}

	protected static function getUrlWithLanguage(string $url, bool $safeMode = true): string
	{
		if (strpos($url, 'lang=') === false)
		{
			$url .= (strpos($url,'?') === false ? '?' : '&')
				.'lang='.LANGUAGE_ID
			;
		}

		return $safeMode
			? CUtil::addslashes($url)
			: $url
		;
	}

	protected static function getModeUrlParam(string $mode): string
	{
		return self::MODE_FIELD_NAME . '=' . $mode;
	}

	protected static function getModeActionUrlParam(): string
	{
		return static::getModeUrlParam(self::MODE_ACTION);
	}

	protected static function getModeConfigUrlParam(): string
	{
		return static::getModeUrlParam(self::MODE_CONFIG);
	}

	protected static function getModeExportUrlParam(): string
	{
		return static::getModeUrlParam(self::MODE_EXPORT);
	}

	protected static function getModeParam(string $mode): array
	{
		return [self::MODE_FIELD_NAME => $mode];
	}

	protected static function getModeExportParam(): array
	{
		return static::getModeParam(self::MODE_EXPORT);
	}
}

class CAdminListRow
{
	var $aHeaders = array();
	var $aHeadersID = array();
	var $aFields = array();
	var $aActions = array();
	var $table_id;
	var $indexFields = 0;
	var $edit = false;
	var $id;
	var $bReadOnly = false;
	var $aFeatures = array();
	var $bEditMode = false;
	var $arRes;
	var $link;
	var $title;
	var $pList;
	var $isPublicMode;

	protected $config;

	/**
	* CAdminListRow constructor.
	* @param array &$aHeaders
	* @param string $table_id
	*/
	public function __construct(&$aHeaders, $table_id)
	{
		$this->aHeaders = $aHeaders;
		$this->aHeadersID = array_keys($aHeaders);
		$this->table_id = $table_id;

		$this->setPublicModeState(defined('PUBLIC_MODE') && PUBLIC_MODE == 1);

		$this->config = [];
	}

	public function setPublicModeState(bool $mode): void
	{
		$this->isPublicMode = $mode;
	}

	public function getPublicModeState(): bool
	{
		return $this->isPublicMode;
	}

	public function setConfig(array $config): void
	{
		$this->config = $config;
	}

	public function getConfig(): array
	{
		return $this->config;
	}

	public function getConfigValue(string $index)
	{
		return $this->config[$index] ?? null;
	}

	function SetFeatures($aFeatures)
	{
		//array("footer"=>true)
		$this->aFeatures = $aFeatures;
	}

	function AddField($id, $text, $edit=false, $isHtml = true)
	{
		$this->aFields[$id] = array();
		if ($edit !== false)
		{
			$this->aFields[$id]["edit"] = array("type" => "input", "value" => $edit);
			$this->pList->bCanBeEdited = true;
		}
		$type = $isHtml ? "html" : "text";
		$this->aFields[$id]["view"] = array("type" => $type, "value" => $text);
	}

	/**
	 * @param string $id
	 * @param array|boolean $arAttributes
	 * @return void
	 */
	function AddCheckField($id, $arAttributes = Array())
	{
		if($arAttributes!==false)
		{
			$this->aFields[$id]["edit"] = Array("type"=>"checkbox", "attributes"=>$arAttributes);
			$this->pList->bCanBeEdited = true;
		}
		$this->aFields[$id]["view"] = Array("type"=>"checkbox");
	}

	/**
	 * @param string $id
	 * @param array $arValues
	 * @param array|boolean $arAttributes
	 * @return void
	 */
	function AddSelectField($id, $arValues = Array(), $arAttributes = Array())
	{
		if($arAttributes!==false)
		{
			$this->aFields[$id]["edit"] = Array("type"=>"select", "values"=>$arValues, "attributes"=>$arAttributes);
			$this->pList->bCanBeEdited = true;
		}
		$this->aFields[$id]["view"] = Array("type"=>"select", "values"=>$arValues);
	}

	/**
	 * @param string $id
	 * @param array|boolean $arAttributes
	 * @return void
	 */
	function AddInputField($id, $arAttributes = Array())
	{
		if ($arAttributes !== false)
		{
			$arAttributes['size'] = (int)($arAttributes['size'] ?? 0);
			$this->aFields[$id]["edit"] = Array("type"=>"input", "attributes"=>$arAttributes);
			$this->pList->bCanBeEdited = true;
		}
	}

	/**
	 * @param string $id
	 * @param array|boolean $arAttributes
	 * @param bool $useTime
	 * @return void
	 */
	function AddCalendarField($id, $arAttributes = Array(), $useTime = false)
	{
		if ($arAttributes!==false)
		{
			$arAttributes['size'] = (int)($arAttributes['size'] ?? 0);
			$this->aFields[$id]["edit"] = array("type"=>"calendar", "attributes"=>$arAttributes, "useTime" => $useTime);
			$this->pList->bCanBeEdited = true;
		}
	}

		/**
	 * @param string $id
	 * @param array|boolean $arAttributes
	 * @return void
	 */
	function AddMoneyField($id, $arAttributes = [])
	{
		if($arAttributes!==false)
		{
			$this->aFields[$id]["edit"] = Array("type"=>"money", "attributes"=>$arAttributes);
			$this->pList->bCanBeEdited = true;
		}
	}

	function AddViewField($id, $sHTML)
	{
		$this->aFields[$id]["view"] = Array("type"=>"html", "value"=>$sHTML);
	}

	function AddEditField($id, $sHTML)
	{
		$this->aFields[$id]["edit"] = Array("type"=>"html", "value"=>$sHTML);
		$this->pList->bCanBeEdited = true;
	}

	/**
	 * @param string $id
	 * @param bool|array $showInfo
	 * @return void
	 */
	function AddViewFileField($id, $showInfo = false)
	{
		static $fileman = 0;
		if (!($fileman++))
			CModule::IncludeModule('fileman');

		$this->aFields[$id]["view"] = array(
			"type" => "file",
			"showInfo" => $showInfo,
			"inputs" => array(
				'upload' => false,
				'medialib' => false,
				'file_dialog' => false,
				'cloud' => false,
				'del' => false,
				'description' => false,
			),
		);
	}

	/**
	 * @param string $id
	 * @param bool|array $showInfo
	 * @param array $inputs
	 * @return void
	 */
	function AddFileField($id, $showInfo = false, $inputs = array())
	{
		$this->aFields[$id]["edit"] = array(
			"type" => "file",
			"showInfo" => $showInfo,
			"inputs" => $inputs,
		);
		$this->pList->bCanBeEdited = true;
		$this->AddViewFileField($id, $showInfo);
	}

	function AddActions($aActions)
	{
		if (is_array($aActions))
			$this->aActions = $aActions;
	}

	function __AttrGen($attr)
	{
		$res = '';
		foreach($attr as $name=>$val)
			$res .= ' '.htmlspecialcharsbx($name).'="'.htmlspecialcharsbx($val).'"';

		return $res;
	}

	function VarsFromForm()
	{
		return ($this->bEditMode && is_array($this->pList->arUpdateErrorIDs) && in_array($this->id, $this->pList->arUpdateErrorIDs));
	}

	function Display()
	{
		// Check after grid event handlers
		if (!is_array($this->aActions))
		{
			$this->aActions = [];
		}

		$sDefAction = $sDefTitle = "";

		if(!$this->bEditMode)
		{
			if(!empty($this->link))
			{
				$sDefAction = $this->getActionLink($this->link);
				$sDefTitle = $this->title;
			}
			else
			{
				foreach($this->aActions as $action)
				{
					if (isset($action["DEFAULT"]) && $action["DEFAULT"] == true)
					{
						$sDefAction = $this->getActionsItemLink($action);
						$sDefTitle = (!empty($action["TITLE"])? $action["TITLE"] : $action["TEXT"]);
						break;
					}
				}
			}

			$sDefAction = htmlspecialcharsbx($sDefAction);
			$sDefTitle = htmlspecialcharsbx($sDefTitle);
		}

		$sMenuItems = "";
		if(!empty($this->aActions))
			$sMenuItems = htmlspecialcharsbx(CAdminPopup::PhpToJavaScript($this->aActions));
?>
<tr class="adm-list-table-row<?=(isset($this->aFeatures["footer"]) && $this->aFeatures["footer"] == true? ' footer':'')?><?=$this->bEditMode?' adm-table-row-active' : ''?>"<?=($sMenuItems <> ""? ' oncontextmenu="return '.$sMenuItems.';"':'');?><?=($sDefAction <> ""? ' ondblclick="'.$sDefAction.'"'.(!empty($sDefTitle)? ' title="'.GetMessage("admin_lib_list_double_click").' '.$sDefTitle.'"':''):'')?>>
<?

		if (!empty($this->pList->arActions) || $this->pList->bCanBeEdited):
			$check_id = RandString(5);
?>
	<td class="adm-list-table-cell adm-list-table-checkbox adm-list-table-checkbox-hover<?=$this->bReadOnly? ' adm-list-table-checkbox-disabled':''?>"><input type="checkbox" class="adm-checkbox adm-designed-checkbox" name="ID[]" id="<?=$this->table_id."_".$this->id."_".$check_id;?>" value="<?=$this->id?>" autocomplete="off" title="<?=GetMessage("admin_lib_list_check")?>"<?=$this->bReadOnly? ' disabled="disabled"':''?><?=$this->bEditMode ? ' checked="checked" disabled="disabled"' : ''?> /><label class="adm-designed-checkbox-label adm-checkbox" for="<?=$this->table_id."_".$this->id."_".$check_id;?>"></label></td>
<?
		endif;

		if($this->pList->bShowActions):
			if(!empty($this->aActions)):
?>
	<td class="adm-list-table-cell adm-list-table-popup-block" onclick="BX.adminList.ShowMenu(this.firstChild, this.parentNode.oncontextmenu(), this.parentNode);"><div class="adm-list-table-popup" title="<?=GetMessage("admin_lib_list_actions_title")?>"></div></td>
<?
			else:
?>
	<td class="adm-list-table-cell"></td>
<?
			endif;
		endif;

		end($this->pList->aVisibleHeaders);
		$last_id = key($this->pList->aVisibleHeaders);
		reset($this->pList->aVisibleHeaders);

		$bVarsFromForm = ($this->bEditMode && is_array($this->pList->arUpdateErrorIDs) && in_array($this->id, $this->pList->arUpdateErrorIDs));
		foreach($this->pList->aVisibleHeaders as $id=>$header_props)
		{
			$field = $this->aFields[$id] ?? [];
			if ($this->bEditMode && isset($field["edit"]))
			{
				if ($bVarsFromForm && isset($_REQUEST["FIELDS"]))
				{
					$val = $_REQUEST["FIELDS"][$this->id][$id] ?? '';
				}
				else
				{
					$val = $this->arRes[$id] ?? '';
				}

				$val_old = $this->arRes[$id] ?? '';

				echo '<td class="adm-list-table-cell',
					(isset($header_props['align']) && $header_props['align']? ' align-'.$header_props['align']: ''),
					(isset($header_props['valign']) && $header_props['valign']? ' valign-'.$header_props['valign']: ''),
					($id === $last_id? ' adm-list-table-cell-last': ''),
				'">';

				if(is_array($val_old))
				{
					foreach($val_old as $k=>$v)
						echo '<input type="hidden" name="FIELDS_OLD['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']['.htmlspecialcharsbx($k).']" value="'.htmlspecialcharsbx($v).'">';
				}
				else
				{
					echo '<input type="hidden" name="FIELDS_OLD['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']" value="'.htmlspecialcharsbx($val_old).'">';
				}
				switch($field["edit"]["type"])
				{
					case "checkbox":
						echo '<input type="hidden" name="FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']" value="N">';
						echo '<input type="checkbox" name="FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']" value="Y"'.($val=='Y' || $val === true?' checked':'').'>';
						break;
					case "select":
						echo '<select name="FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']"'.$this->__AttrGen($field["edit"]["attributes"]).'>';
						foreach($field["edit"]["values"] as $k=>$v)
							echo '<option value="'.htmlspecialcharsbx($k).'" '.($k==$val?' selected':'').'>'.htmlspecialcharsbx($v).'</option>';
						echo '</select>';
						break;
					case "input":
						if(!$field["edit"]["attributes"]["size"])
							$field["edit"]["attributes"]["size"] = "10";
						echo '<input type="text" '.$this->__AttrGen($field["edit"]["attributes"]).' name="FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']" value="'.htmlspecialcharsbx($val).'">';
						break;
					case "calendar":
						if(!$field["edit"]["attributes"]["size"])
							$field["edit"]["attributes"]["size"] = "10";
						echo '<span style="white-space:nowrap;"><input type="text" '.$this->__AttrGen($field["edit"]["attributes"]).' name="FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']" value="'.htmlspecialcharsbx($val).'">';
						echo CAdminCalendar::Calendar(
								'FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']',
								'',
								'',
								$field['edit']['useTime']
							).'</span>';
						break;
					case "file":
						echo CFileInput::Show(
							'FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']',
							$val,
							$field["edit"]["showInfo"],
							$field["edit"]["inputs"]
						);
						break;
					default:
						echo $field["edit"]['value'];
				}
				echo '</td>';
			}
			else
			{
				$val = $this->arRes[$id] ?? '';
				if (is_string($val))
				{
					$val = trim($val);
				}

				if(isset($field["view"]))
				{
					switch($field["view"]["type"])
					{
						case "checkbox":
							if($val == 'Y' || $val === true)
								$val = htmlspecialcharsex(GetMessage("admin_lib_list_yes"));
							else
								$val = htmlspecialcharsex(GetMessage("admin_lib_list_no"));
							break;
						case "select":
							if (isset($field["edit"]["values"][$val]))
							{
								$val = htmlspecialcharsex($field["edit"]["values"][$val]);
							}
							elseif (isset($field["view"]["values"][$val]))
							{
								$val = htmlspecialcharsex($field["view"]["values"][$val]);
							}
							else
							{
								$val = htmlspecialcharsex($val);
							}
							break;
						case "file":
							if ($val > 0)
								$val = CFileInput::Show(
									'NO_FIELDS['.htmlspecialcharsbx($this->id).']['.htmlspecialcharsbx($id).']',
									$val,
									$field["view"]["showInfo"],
									$field["view"]["inputs"]
								);
							else
								$val = '';
							break;
						case "html":
							$val = $field["view"]['value'];
							break;
						default:
							$val = htmlspecialcharsex($val);
							break;
					}
				}
				else
				{
					$val = htmlspecialcharsex($val);
				}

				echo '<td class="adm-list-table-cell',
					(isset($header_props['align']) && $header_props['align']? ' align-'.$header_props['align']: ''),
					(isset($header_props['valign']) && $header_props['valign']? ' valign-'.$header_props['valign']: ''),
					($id === $last_id? ' adm-list-table-cell-last': ''),
				'">';
				echo ((string)$val <> ""? $val: '&nbsp;');
				if(isset($field["edit"]) && $field["edit"]["type"] == "calendar")
					CAdminCalendar::ShowScript();
				echo '</td>';
			}
		}
?>
</tr>
<?
	}

	/**
	 * @param string $url
	 * @return string
	 */
	protected function getActionLink($url)
	{
		global $adminSidePanelHelper;
		if (is_object($adminSidePanelHelper) && $adminSidePanelHelper->isPublicSidePanel())
			return "BX.adminSidePanel.onOpenPage('".CUtil::JSEscape($url)."');";
		return "BX.adminPanel.Redirect([], '".CUtil::JSEscape($url)."', event);";
	}

	/**
	* @param array $item
	* @return bool
	*/
	protected function getActionsItemLink(array $item)
	{
		return (!empty($item["ACTION"])
			? $item["ACTION"]
			: $this->getActionLink($item["LINK"])
		);
	}
}
