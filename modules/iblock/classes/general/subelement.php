<?php

/** @global \CMain $APPLICATION */
use Bitrix\Main;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
IncludeModuleLangFile(__FILE__);

class CAdminSubSorting extends CAdminSorting
{
	public $list_url;

	public function __construct($table_id, $by_initial = false, $order_initial = false, $by_name = "by", $ord_name = "order", $list_url = '')
	{
		global $APPLICATION;

		parent::__construct($table_id, $by_initial, $order_initial, $by_name, $ord_name);
		$this->list_url = trim((string)$list_url);
		if ($this->list_url === '')
			$this->list_url = $APPLICATION->GetCurPage();
	}

	public function Show($text, $sort_by, $alt_title = false, $baseCssClass = "")
	{
		$ord = "asc";
		$class = "";
		$title = GetMessage("admin_lib_sort_title")." ".($alt_title?$alt_title:$text);
		if(mb_strtolower($this->field) == mb_strtolower($sort_by))
		{
			if(mb_strtolower($this->order) == "desc")
			{
				$class = "-down";
				$title .= " ".GetMessage("admin_lib_sort_down");
			}
			else
			{
				$class = "-up";
				$title .= " ".GetMessage("admin_lib_sort_up");
				$ord = "desc";
			}
		}

		$path = $this->list_url;
		$sep = (false === mb_strpos($path, '?') ? '?' : '&');
		$url = $path.$sep.$this->by_name."=".$sort_by."&".$this->ord_name."=".($class <> ""? $ord:"");

		return 'class="'.$baseCssClass.' adm-list-table-cell-sort'.$class.'" onclick="'.$this->table_id.'.Sort(\''.htmlspecialcharsbx(CUtil::addslashes($url)).'\', '.($class <> ""? "false" : "true").', arguments);" title="'.$title.'"';
	}
}

class CAdminSubList extends CAdminList
{
/*
 *	list_url - string with params or array:
 *		LINK
 *		PARAMS (array key => value)
 */

	public $strListUrl = '';	// add
	public $strListUrlParams = ''; // add
	public $arListUrlParams = array(); // add
	public $boolNew = false; // add
	public $arFieldNames = array(); // add
	public $arHideHeaders = array(); // add

	protected $bPublicMode = false;

	protected $dialogParams = array();
	protected $requiredDialogParams = array(
		'bxpublic' => 'Y'
	);
	protected $dialogButtons = array(
		'BX.CAdminDialog.btnSave', 'BX.CAdminDialog.btnCancel'
	);

	/**
	 * @param string $table_id
	 * @param bool|CAdminSubSorting $sort
	 * @param string|array $list_url
	 * @param bool|array $arHideHeaders
	 */

	public function __construct($table_id, $sort = false, $list_url = '', $arHideHeaders = false)
	{
		global $APPLICATION;

		$this->bPublicMode = defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1;
		$this->dialogParams['from_module'] = 'iblock';
		$this->requiredDialogParams['sessid'] = bitrix_sessid();
		if (!$this->bPublicMode)
		{
			$this->dialogParams['bxsku'] = 'Y';
			$this->requiredDialogParams['bxshowsettings'] = 'Y';
		}

		$arJSDescr = array(
			'js' => '/bitrix/js/iblock/subelement.js',
			'rel' => array('admin_interface')
		);
		if ($this->bPublicMode)
			$arJSDescr['css'] = '/bitrix/panel/iblock/sub-public.css';
		else
			$arJSDescr['css'] = '/bitrix/panel/iblock/sub-admin.css';

		CJSCore::RegisterExt('subelement', $arJSDescr);
		CJSCore::Init(array("subelement"));

		$this->strListUrlParams = '';
		$this->arListUrlParams = array();

		if (is_array($list_url) && isset($list_url['LINK']))
		{
			$this->strListUrl = $list_url['LINK'];
			$this->__ParseListUrl(true);
			if (isset($list_url['PARAMS']))
				$this->__SetListUrlParams($list_url['PARAMS']);
		}
		else
		{
			$this->strListUrl = $list_url;
			$this->__ParseListUrl(true);
		}
		if ('' == $this->strListUrl)
		{
			$this->strListUrl = $APPLICATION->GetCurPageParam();
			$this->__ParseListUrl(true);
		}
		if ($this->bPublicMode)
			$this->__AddListUrlParams('bxpublic', 'Y');

		if ($sort === false)
			$sort = new CAdminSubSorting($table_id, 'ID', 'ASC', 'by', 'order', $this->GetListUrl(true));
		else
			$sort->list_url = $this->GetListUrl(true);

		parent::__construct($table_id, $sort);

		$this->SetBaseFieldNames();
		if (!empty($arHideHeaders) && is_array($arHideHeaders))
			$this->arHideHeaders = $arHideHeaders;
	}

	function GetListUrl($boolFull = false)
	{
		$boolFull = ($boolFull === true);
		return $this->strListUrl.($boolFull && '' != $this->strListUrlParams ? '?'.$this->strListUrlParams : '');
	}

	function __UpdateListUrlParams()
	{
		$this->strListUrlParams = '';
		if (!empty($this->arListUrlParams))
		{
			foreach ($this->arListUrlParams as $key => $value)
				$this->strListUrlParams .= $key.'='.$value.'&';
			unset($key, $value);
			$this->strListUrlParams = mb_substr($this->strListUrlParams, 0, -1);
		}
	}

	function __ClearListUrlParams()
	{
		$this->arListUrlParams = array();
		$this->strListUrlParams = '';
	}

	function __AddListUrlParams($strKey, $strValue)
	{
		if ('' != $strKey)
		{
			$this->arListUrlParams[$strKey] = $strValue;
			$this->__UpdateListUrlParams();
		}
	}

	function __DeleteListUrlParams($mxKey)
	{
		if (is_array($mxKey))
		{
			foreach ($mxKey as $value)
				if ('' != $value && array_key_exists($value,$this->arListUrlParams))
					unset($this->arListUrlParams[$value]);
		}
		elseif ('' != $mxKey && array_key_exists($mxKey,$this->arListUrlParams))
		{
			unset($this->arListUrlParams[$mxKey]);
		}
		$this->__UpdateListUrlParams();
	}

	function __SetListUrlParams($mxParams,$boolClear = false)
	{
		$boolClear = ($boolClear === true);
		if ($boolClear)
			$this->arListUrlParams = array();
		if (!is_array($mxParams))
		{
			$arParams = array();
			parse_str($mxParams, $arParams);
			$mxParams = (is_array($arParams) ? $arParams : array());
		}
		foreach ($mxParams as $key => $value)
			if ('' != $key)
				$this->arListUrlParams[$key] = $value;

		$this->__UpdateListUrlParams();
	}

	function __ParseListUrl($boolClear = false)
	{
		$mxPos = mb_strpos($this->strListUrl, '?');
		if (false !== $mxPos)
		{
			$this->__SetListUrlParams(mb_substr($this->strListUrl, $mxPos + 1), $boolClear);
			$this->strListUrl = mb_substr($this->strListUrl, 0, $mxPos);
		}
	}

	function AddHideHeader($strID)
	{
		$strID = trim($strID);
		if ('' != $strID)
		{
			if (!in_array($strID, $this->arHideHeaders))
				$this->arHideHeaders[] = $strID;
		}
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

		$hiddenColumns = (!empty($this->arHideHeaders) ? array_fill_keys($this->arHideHeaders, true) : array());

		$aOptions = CUserOptions::GetOption("list", $this->table_id, array());
		if (!is_array($aOptions))
		{
			$aOptions = [];
		}

		$aColsTmp = explode(",", $aOptions["columns"] ?? '');
		$aCols = array();
		$userColumns = array();

		foreach($aColsTmp as $col)
		{
			$col = trim($col);
			if ($col != '' && !isset($hiddenColumns[$col]))
			{
				$aCols[] = $col;
				$userColumns[$col] = true;
			}
		}

		$bEmptyCols = empty($aCols);
		$userVisibleColumns = array();
		foreach ($aParams as $param)
		{
			$param["__sort"] = -1;
			$param['default'] ??= false;
			if (!isset($hiddenColumns[$param["id"]]))
			{
				$this->aHeaders[$param["id"]] = $param;
				if (
					$showAll
					|| ($bEmptyCols && ($param["default"] === true))
					|| isset($userColumns[$param["id"]])
				)
				{
					$this->arVisibleColumns[] = $param["id"];
					$userVisibleColumns[$param["id"]] = true;
				}
			}
		}
		unset($userColumns);

		$aAllCols = null;
		if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "subsettings")
			$aAllCols = $this->aHeaders;

		if(!$bEmptyCols)
		{
			foreach ($aCols as $i => $col)
				if (isset($this->aHeaders[$col]))
					$this->aHeaders[$col]["__sort"] = $i;
			Main\Type\Collection::sortByColumn($this->aHeaders, array('__sort' => SORT_ASC), '', null, true);
		}

		foreach($this->aHeaders as $id=>$arHeader)
		{
			if (isset($userVisibleColumns[$id]) && !isset($hiddenColumns[$id]))
				$this->aVisibleHeaders[$id] = $arHeader;
		}
		unset($userVisibleColumns, $hiddenColumns);

		if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "subsettings")
			$this->ShowSettings($aAllCols, $aCols, $aOptions);
	}

	public function AddVisibleHeaderColumn($id)
	{
		if (!in_array($id, $this->arVisibleColumns) && !in_array($id,$this->arHideHeaders))
			$this->arVisibleColumns[] = $id;
	}

	protected function GetSystemContextMenu(array $config = []): array
	{
		$result = [];

		if (isset($config['settings']))
		{
			$this->__AddListUrlParams('mode','subsettings');
			$result[] = [
				"TEXT" => GetMessage("admin_lib_context_sett"),
				"TITLE" => GetMessage("admin_lib_context_sett_title"),
				"ONCLICK" => $this->table_id.".ShowSettings('".CUtil::JSEscape($this->GetListUrl(true))."')",
				"ICON" => "btn_sub_settings",
			];
			$this->__DeleteListUrlParams('mode');
		}
		if (isset($config['excel']))
		{
			$this->__AddListUrlParams('mode','excel');
			$result[] = [
				"TEXT" => "Excel",
				"TITLE" => GetMessage("admin_lib_excel"),
				"ONCLICK" => "location.href='".htmlspecialcharsbx($this->GetListUrl(true))."'",
				"ICON" => "btn_sub_excel",
			];
			$this->__DeleteListUrlParams('mode');
		}
		return $result;
	}

	protected function InitContextMenu(array $menu = [], array $additional = []): void
	{
		if (!empty($menu) || !empty($additional))
		{
			$this->context = new CAdminSubContextMenuList($menu, $additional);
		}
	}

	/**
	 * Returns true if the user has set the flag "To all" in the list.
	 *
	 * @return bool
	 */
	public function IsGroupActionToAll()
	{
		return (isset($_REQUEST['action_sub_target']) && $_REQUEST['action_sub_target'] === 'selected');
	}

	/**
	 * @return array|null
	 */
	protected function GetGroupIds()
	{
		$result = null;
		if (isset($_REQUEST['SUB_ID']))
		{
			$result = (!is_array($_REQUEST['SUB_ID']) ? array($_REQUEST['SUB_ID']) : $_REQUEST['SUB_ID']);
		}
		return $result;
	}

	public function ActionPost($url = false, $action_name = false, $action_value = 'Y')
	{
		return $this->table_id.".FormSubmit();";
	}

	public function ActionDoGroup($id, $action_id, $add_params='')
	{
		$strParams = "SUB_ID=".urlencode($id)
			."&action=".urlencode($action_id)
			."&lang=".urlencode(LANGUAGE_ID)
			."&".bitrix_sessid_get()
			.($add_params<>""? "&".$add_params: "")
		;
		$strUrl = $this->GetListUrl(true).('' != $this->strListUrlParams ? '&' : '?').$strParams;
		return $this->table_id.".GetAdminList('".CUtil::JSEscape($strUrl)."');";
	}

	public function &AddRow($id = false, $arRes = array(), $link = false, $title = false, $boolBX = false)
	{
		$row = new CAdminSubListRow($this->aHeaders, $this->table_id);
		$row->id = $id;
		$row->arRes = $arRes;
		$row->link = $link;
		$row->title = $title;
		$row->pList = &$this;
		$row->boolBX = $boolBX;

		if($id)
		{
			if($this->bEditMode && in_array($id, $this->arEditedRows))
				$row->bEditMode = true;
			elseif(!empty($this->arUpdateErrorIDs) && in_array($id, $this->arUpdateErrorIDs))
				$row->bEditMode = true;
		}

		$this->aRows[] = &$row;
		return $row;
	}

	public function Display()
	{
		foreach(GetModuleEvents("main", "OnAdminSubListDisplay", true) as $arEvent)
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

		echo '<div id="form_'.$this->table_id.'" class="adm-sublist">';

		if($this->bEditMode && !$this->bCanBeEdited)
			$this->bEditMode = false;

		$boolCloseMessage = true;
		$errmsg = '';
		foreach ($this->arFilterErrors as $err)
			$errmsg .= ($errmsg<>''? '<br>': '').$err;
		foreach ($this->arUpdateErrors as $err)
			$errmsg .= ($errmsg<>''? '<br>': '').$err[0];
		foreach ($this->arGroupErrors as $err)
			$errmsg .= ($errmsg<>''? '<br>': '').$err[0];
		if($errmsg<>'')
		{
			CAdminSubMessage::ShowMessage(array("MESSAGE"=>GetMessage("admin_lib_error"), "DETAILS"=>$errmsg, "TYPE"=>"ERROR"));
			$boolCloseMessage = false;
		}

		$successMessage = '';
		for ($i = 0, $cnt = count($this->arActionSuccess); $i < $cnt; $i++)
			$successMessage .= ($successMessage != '' ? '<br>' : '').$this->arActionSuccess[$i];
		if ($successMessage != '')
		{
			CAdminSubMessage::ShowMessage(array("MESSAGE" => GetMessage("admin_lib_success"), "DETAILS" => $successMessage, "TYPE" => "OK"));
			$boolCloseMessage = false;
		}

		if ($this->bPublicMode && $boolCloseMessage)
		{
			echo '<script type="text/javascript">top.BX.WindowManager.Get().hideNotify();</script>';
		}

		echo $this->sPrologContent;

		if($this->sContent===false)
		{
			echo '<div class="adm-list-table-wrap'.($this->context ? '' : ' adm-list-table-without-header').(empty($this->arActions) && !$this->bCanBeEdited ? ' adm-list-table-without-footer' : '').'">';
		}

		if ($this->context)
			$this->context->Show();

		//!!! insert filter's hiddens
		echo bitrix_sessid_post();

		if($this->sContent!==false)
		{
			echo $this->sContent;
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

		$colSpan = 0;

echo '<table class="adm-list-table" id="'.$this->table_id.'">
	<thead>
		<tr class="adm-list-table-header">';

		if($bShowSelectAll)
		{
			echo '<td class="adm-list-table-cell adm-list-table-checkbox" onclick="this.firstChild.firstChild.click(); return BX.PreventDefault(event);"><div class="adm-list-table-cell-inner"><input class="adm-checkbox adm-designed-checkbox" type="checkbox" id="'.$this->table_id.'_check_all" '.($this->bEditMode ? 'disabled' : 'onclick="'.$this->table_id.'.SelectAllRows(this); return BX.eventCancelBubble(event);"').' title="'.GetMessage("admin_lib_list_check_all").'" /><label for="'.$this->table_id.'_check_all" class="adm-designed-checkbox-label"></label></div></td>';
			$colSpan++;
		}
		if($this->bShowActions)
		{
			echo '<td class="adm-list-table-cell adm-list-table-popup-block" title="'.GetMessage("admin_lib_list_act").'"><div class="adm-list-table-cell-inner"></div></td>';
			$colSpan++;
		}
		foreach($this->aHeaders as $column_id=>$header)
		{
			if(!in_array($column_id, $this->arVisibleColumns))
				continue;

			$header['title'] = (string)($header['title'] ?? '');
			$bSort = $this->sort && !empty($header["sort"]);

			if ($bSort)
			{
				$attrs = $this->sort->Show(
					$header["content"],
					$header["sort"],
					$header["title"],
					"adm-list-table-cell"
				);
			}
			else
			{
				$attrs = 'class="adm-list-table-cell"';
			}


			echo '<td '.$attrs.'>
				<div class="adm-list-table-cell-inner">'.$header["content"].'</div>'.($bSort ? '<span class="adm-sub-sort"></span>' : '').'
			</td>';

			$colSpan++;
		}
		echo '</tr></thead><tbody>';

		if (!empty($this->aRows))
		{
			foreach ($this->aRows as &$row)
				$row->Display();
			unset($row);
		}
		elseif (!empty($this->aHeaders))
		{
			echo '<tr><td colspan="'.$colSpan.'" class="adm-list-table-cell adm-list-table-empty">'.GetMessage("admin_lib_no_data").'</td></tr>';
		}

		echo '</tbody></table>';

		$this->ShowActionTable();

		echo $this->sEpilogContent;
		echo '</div>';
		echo $this->sNavText;
	}

	public function ShowActionTable()
	{
		if (empty($this->arActions) && !$this->bCanBeEdited)
			return;
?>
<div class="adm-list-table-footer" id="<?=$this->table_id?>_footer<?=$this->bEditMode || !empty($this->arUpdateErrorIDs) ? '_edit' : ''?>">
	<input type="hidden" name="action_button" id="<?=$this->table_id; ?>_action_button" value="" />
<?
		if($this->bEditMode || !empty($this->arUpdateErrorIDs))
		{
			$this->DisplayEditButtons();
		}
		else
		{
			$showAll = true;
			if (isset($this->arActionsParams["disable_action_target"]) && $this->arActionsParams["disable_action_target"] === true)
				$showAll = false;
			elseif (isset($this->arActionsParams["disable_action_sub_target"]) && $this->arActionsParams["disable_action_sub_target"] === true)
				$showAll = false;
			if ($showAll)
			{
?>
	<span class="adm-selectall-wrap"><input type="checkbox" class="adm-checkbox adm-designed-checkbox" name="action_sub_target" id="<?=$this->table_id;?>_action_sub_target" value="selected" onclick="if(this.checked && !confirm('<?=CUtil::JSEscape(GetMessage("admin_lib_list_edit_for_all_warn"));?>')) {this.checked=false;} <?=$this->table_id;?>.EnableActions();" title="<?=GetMessage("admin_lib_list_edit_for_all");?>" /><label title="<?=GetMessage("admin_lib_list_edit_for_all");?>" for="action_sub_target" class="adm-checkbox-label"><?=GetMessage("admin_lib_list_for_all");?></label></span>
<?
			}
			$this->bCanBeDeleted = array_key_exists("delete", $this->arActions);

			if ($this->bCanBeEdited || $this->bCanBeDeleted)
			{
				echo '
	<span class="adm-table-item-edit-wrap'.(!$this->bCanBeEdited || !$this->bCanBeDeleted ? ' adm-table-item-edit-single' : '').'">';
				if($this->bCanBeEdited):
					echo '<a href="javascript:void(0)" class="adm-table-btn-edit adm-edit-disable" hidefocus="true" onclick="this.blur();if('.$this->table_id.'.IsActionEnabled(\'edit\')){BX(\''.$this->table_id.'_action_button\').value=\'edit\'; '.htmlspecialcharsbx($this->ActionPost()).'}" title="'.GetMessage("admin_lib_list_edit").'" id="'.$this->table_id.'_action_edit_button"></a>';
				endif;
				if($this->bCanBeDeleted):
					echo '<a href="javascript:void(0);" class="adm-table-btn-delete adm-edit-disable" hidefocus="true" onclick="this.blur();if('.$this->table_id.'.IsActionEnabled() && confirm((BX(\'action_sub_target\') && BX(\'action_sub_target\').checked ? \''.GetMessage("admin_lib_list_del").'\':\''.GetMessage("admin_lib_list_del_sel").'\'))) {BX(\''.$this->table_id.'_action_button\').value=\'delete\'; '.htmlspecialcharsbx($this->ActionPost()).'}" title="'.GetMessage("admin_lib_list_del_title").'" class="context-button icon action-delete-button-dis" id="'.$this->table_id.'_action_delete_button"></a>';
				endif;
				echo '</span>';
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
			$blockMap = [];

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
				'html' => true,
				'multicontrol' => true
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
								$buttons .= '<input type="button" name="" value="'.htmlspecialcharsbx($v['name']).'" onclick="'.(!empty($v["action"])? htmlspecialcharsbx($v['action']) : 'document.getElementById(\''.$this->table_id.'_action_button\').value=\''.htmlspecialcharsbx($v["value"]).'\'; '.htmlspecialcharsbx($this->ActionPost()).'').'" title="'.htmlspecialcharsbx($v["title"]).'" />';
								break;
							case 'html':
								$html .= '<span class="adm-list-footer-ext">'.$v["value"].'</span>';
								break;
							case 'multicontrol':
								$data = $this->prepareGroupMultiControl($k, $v);
								if (!empty($data))
								{
									$list .= $data['ITEM'];
									if (isset($data['BLOCK']))
									{
										$html .= '<span class="adm-list-footer-ext">'.$data['BLOCK'].'</span>';
									}
									if (isset($data['ACTION']))
									{
										$blockMap[] = $data['ACTION'];
									}
								}
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
		<select name="action" id="<?=$this->table_id.'_action'; ?>" class="adm-select"<?=($onchange != '' ? ' onchange="'.htmlspecialcharsbx($onchange).'"':'')?> <?
		if (!empty($blockMap))
		{
?>
			data-actions="<?=htmlspecialcharsbx(Main\Web\Json::encode($blockMap)); ?>" data-use-actions="Y"
<?
		}
		?>>
			<option value=""><?=GetMessage("admin_lib_list_actions")?></option>
<?=$list?>
		</select>
	</span>
<?
				if ($html != '')
					echo $html;
?>
	<input type="button" name="apply_sub" id="<?=$this->table_id;?>_apply_sub_button" value="<?=GetMessage("admin_lib_list_apply");?>" onclick="<?=$this->table_id;?>.ExecuteFormAction('ACTION_BUTTON');" disabled="disabled" class="adm-table-action-button" />
<?
			endif;
?>
	<span class="adm-table-counter" id="<?=$this->table_id?>_selected_count"><?=GetMessage('admin_lib_checked')?>: <span>0</span></span>
<?
		}
?>
</div>
<?
	}

	public function DisplayList($arParams = array())
	{
		$menu = new CAdminPopup($this->table_id."_menu", $this->table_id."_menu",false,array('zIndex' => 4000));
		$menu->Show();

		$tbl = CUtil::JSEscape($this->table_id);
		$aUserOpt = CUserOptions::GetOption("global", "settings");
		if (!is_array($aUserOpt))
		{
			$aUserOpt = [];
		}
		$aUserOpt['context_ctrl'] = (string)($aUserOpt['context_ctrl'] ?? 'N');
		echo '
<script type="text/javascript">
var '.$this->table_id.'= new BX.adminSubList("'.$tbl.'", {context_ctrl: '.($aUserOpt["context_ctrl"] === "Y"? "true":"false").'}, "'.$this->GetListUrl(true).'");
function ReloadSubList()
{
	'.$this->ActionAjaxReload($this->GetListUrl(true)).'
}
function ReloadOffers()
{
	ReloadSubList();
}
</script>
';
		echo '<div id="'.$this->table_id.'_result_div">';
		$this->Display();
		echo '</div>';
	}

	public function CheckListMode()
	{
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
				echo '<html><head></head><body>
<div id="'.$this->table_id.'_result_frame_div">'.$string.'</div>
<script type="text/javascript">
';
				if($this->bEditMode || count($this->arUpdateErrorIDs)>0)
					echo $this->table_id.'._DeActivateMainForm();';
				else
					echo $this->table_id.'._ActivateMainForm();';

				if($this->onLoadScript)
					echo 'w.eval(\''.CUtil::JSEscape($this->onLoadScript).'\');';
				echo '</script></body></html>';
			}
			else
			{
				if($this->onLoadScript)
					echo '<script type="text/javascript">'.$this->onLoadScript.'</script>';
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

	function SetBaseFieldNames()
	{
		$this->arFieldNames = array(
			array(
				'NAME' => 'SESSID',
				'TYPE' => 'HIDDEN',
			),
		);
	}

	function AddListFieldNames()
	{
		$this->arFieldNames[] = array(
			'NAME' => 'ACTION_BUTTON',
			'TYPE' => 'HIDDEN',
		);
		$this->arFieldNames[] = array(
			'NAME' => 'SUB_ID[]',
			'TYPE' => 'CHECKBOX',
		);
	}

	function SetListFieldNames($boolClear = true)
	{
		$boolClear = ($boolClear === true);
		if ($boolClear)
			$this->SetBaseFieldNames();
		$this->AddListFieldNames();
	}

	function DeleteFieldNames($arList = array())
	{
		if (false == is_array($arList))
			$arList = array();
		if (false == empty($arList))
		{
			$arTempo = array();
			foreach ($this->arFieldNames as $arName)
			{
				if (!in_array($arName['NAME'],$arList))
					$arTempo[] = $arName;
			}
			$this->arFieldNames = $arTempo;
			unset($arTempo);
		}
	}

	function GetListFieldNames()
	{
		return $this->arFieldNames;
	}

	function AddFieldNames($strFieldName, $strFieldType)
	{

	}

	public function setDialogParams($params)
	{
		if (!is_array($params))
			return;
		$this->dialogParams = $params;
	}

	public function getDialogParams($withRequired = false)
	{
		$withRequired = ($withRequired === true);
		$result = $this->dialogParams;
		if ($withRequired)
		{
			foreach ($this->requiredDialogParams as $key => $value)
				$result[$key] = $value;
			unset($key, $value);
		}
		return $result;
	}

	public function setDialogButtons($buttons)
	{
		if (!is_array($buttons))
			return;
		$this->dialogButtons = $buttons;
	}

	public function setReadDialogButtons(): void
	{
		$this->setDialogButtons(['BX.CAdminDialog.btnCancel']);
	}

	public function getDialogButtons($jsFormat = false)
	{
		$jsFormat = ($jsFormat === true);
		if ($jsFormat)
		{
			return '['.implode(", \r\n", $this->dialogButtons).']';
		}
		else
		{
			return $this->dialogButtons;
		}
	}

	public function getRowAction($url)
	{
		return "(new BX.CAdminDialog({
			'content_url': '".$url."',
			'content_post': ".CUtil::PhpToJSObject($this->getDialogParams(true), false, true, true).",
			'draggable': true,
			'resizable': true,
			'width': 900,
			'height': 600,
			'buttons': ".$this->getDialogButtons(true)."
		})).Show();";
	}

	protected function DisplayEditButtons(): void
	{
?>
		<input type="button" name="save_sub" id="<?=$this->table_id;?>_save_sub_button" value="<?=GetMessage("admin_lib_list_edit_save");?>." title="<?=GetMessage("admin_lib_list_edit_save_title");?>" onclick="<?=$this->table_id;?>.ExecuteFormAction('SAVE_BUTTON');" />
		<input type="button" name="cancel_sub" id="<?=$this->table_id;?>_cancel_sub_button" value="<?=GetMessage("admin_lib_list_edit_cancel");?>" title="<?=GetMessage("admin_lib_list_edit_cancel_title");?>" onclick="<?=$this->ActionAjaxReload($this->GetListUrl(true));?>"/>
<?
	}

	private function prepareGroupMultiControl(string $id, array $action)
	{
		$result = null;

		if (empty($action['action']) || !is_array($action['action']))
			return $result;

		$subBlocks = $this->compileActions($action['action']);

		$result = [
			'ITEM' => '<option value="'.htmlspecialcharsbx($id).'">'.htmlspecialcharsex($action['name']).'</option>'
		];
		if (!empty($subBlocks))
		{
			$actionBlockId = $id.'_block';

			$result['BLOCK'] = '<div id="'.htmlspecialcharsbx($actionBlockId).'" style="display: none;">'.
				implode('', $subBlocks).
				'</div>';

			$result['ACTION'] = [
				'VALUE' => $id,
				'BLOCK' => $actionBlockId
			];
		}

		return $result;
	}

	private function createGroupSubControl(array $data)
	{
		$result = [];

		foreach ($data as $row)
		{
			if (empty($row) || !is_array($row) || !isset($row['TYPE']))
				continue;
			$item = null;
			switch ($row['TYPE'])
			{
				case Main\Grid\Panel\Types::DROPDOWN:
					$item = $this->createGroupSubControlDropdown($row);
					break;
				case Main\Grid\Panel\Types::TEXT:
					$item = $this->createGroupSubControlText($row);
					break;
			}
			if (!empty($item))
				$result[] = $item;
			unset($item);
		}

		return (!empty($result) ? $result: null);
	}

	/**
	 * @param array $actions
	 * @return array
	 */
	private function compileActions(array $actions)
	{
		$result = [];
		foreach ($actions as $row)
		{
			if (empty($row) || !is_array($row))
				continue;
			if (!isset($row['ACTION']))
				continue;
			switch ($row['ACTION'])
			{
				case Main\Grid\Panel\Actions::CREATE:
					if (!empty($row['DATA']) && is_array($row['DATA']))
					{
						$subControl = $this->createGroupSubControl($row['DATA']);
						if (!empty($subControl))
						{
							$result = array_merge($result, $subControl);
						}
						unset($subControl);
					}
					break;
			}
		}
		unset($row);
		return $result;
	}

	private function createGroupSubControlDropdown(array $data)
	{
		$result = null;
		if (!isset($data['ID']) || !isset($data['NAME']))
			return $result;
		if (empty($data['ITEMS']) || !is_array($data['ITEMS']))
			return $result;

		$items = [];
		$subBlocks = [];
		$blockMap = [];
		$first = true;

		foreach ($data['ITEMS'] as $row)
		{
			if (!isset($row['VALUE']) || !isset($row['NAME']))
				continue;

			if (!empty($row['ONCHANGE']) && is_array($row['ONCHANGE']))
			{
				$itemBlocks = $this->compileActions($row['ONCHANGE']);
				if (!empty($itemBlocks))
				{
					$itemBlockId = $data['ID'].'_'.$row['VALUE'].'_block';
					$subBlocks[] = '<span class="adm-list-footer-ext"><div id="'.htmlspecialcharsbx($itemBlockId).'" style="display: '.($first ? 'inline-block' : 'none').';">'.
						implode('', $itemBlocks).
						'</div></span>';
					$blockMap[] = [
						'VALUE' => $row['VALUE'],
						'BLOCK' => $itemBlockId
					];
				}
				unset($itemBlocks);
			}

			$items[] = '<option value="'.htmlspecialcharsbx($row['VALUE']).'">'.
				htmlspecialcharsex($row['NAME']).
				'</option>';

			$first = false;
		}
		unset($row);

		if (!empty($items))
		{
			$result = '<select id="'.htmlspecialcharsbx($data['ID']).'" '.
				'name="'.htmlspecialcharsbx($data['NAME']).'"'.
				(!empty($blockMap) ? ' data-actions="'.htmlspecialcharsbx(Main\Web\Json::encode($blockMap)).'" data-use-actions="Y"' : '').
				'data-action-item="Y" '.
				'>';
			$result .= implode('', $items);
			$result .= '</select>';
			if (!empty($subBlocks))
			{
				$result .= implode('', $subBlocks);
			}
		}
		unset($items);

		return $result;
	}

	/**
	 * @param array $data
	 * @return string|null
	 */
	private function createGroupSubControlText(array $data)
	{
		if (!isset($data['ID']) || !isset($data['NAME']))
			return null;
		return '<span class="adm-input-text-wrap"><input type="text" id="'.htmlspecialcharsbx($data['ID']).'" '.
			'name="'.htmlspecialcharsbx($data['NAME']).'" '.
			'data-action-item="Y" '.
			'value=""></span>';
	}
}

class CAdminSubListRow extends CAdminListRow
{
	/** @var \CAdminSubList pList */
	var $pList;

	public $arFieldNames = array(); //add
	public $boolBX = false; // add

	public function __construct($aHeaders,$table_id)
	{
		parent::__construct($aHeaders,$table_id);
	}

	public function Display()
	{
		$sDefAction = $sDefTitle = "";
		if (!$this->bEditMode)
		{
			if (!empty($this->link))
			{
				if (true == $this->boolBX)
					$sDefAction = $this->pList->getRowAction($this->link);
				else
					$sDefAction = "BX.adminPanel.Redirect([], '".CUtil::JSEscape($this->link)."', event);";
				$sDefTitle = $this->title;
			}
			else
			{
				foreach ($this->aActions as $action)
					if ($action["DEFAULT"] == true)
					{
						if (true == $this->boolBX)
							$sDefAction = $this->pList->getRowAction(CUtil::addslashes($action["ACTION"]));
						else
							$sDefAction = $action["ACTION"]
								? htmlspecialcharsbx($action["ACTION"])
								: "BX.adminPanel.Redirect([], '".CUtil::JSEscape($action["LINK"])."', event)"
							;
						$sDefTitle = (!empty($action["TITLE"])? $action["TITLE"]:$action["TEXT"]);
						break;
					}
			}

			$sDefAction = htmlspecialcharsbx($sDefAction);
			$sDefTitle = htmlspecialcharsbx($sDefTitle);
		}

		$sMenuItems = '';
		if(!empty($this->aActions))
			$sMenuItems = htmlspecialcharsbx(CAdminPopup::PhpToJavaScript($this->aActions));

?>
<tr class="adm-list-table-row<?=(isset($this->aFeatures["footer"]) && $this->aFeatures["footer"] == true? ' footer':'')?><?=$this->bEditMode?' adm-table-row-active' : ''?>"<?=($sMenuItems <> "" ? ' oncontextmenu="return '.$sMenuItems.';"':'');?><?=($sDefAction <> ""? ' ondblclick="'.$sDefAction.'"'.(!empty($sDefTitle)? ' title="'.GetMessage("admin_lib_list_double_click").' '.$sDefTitle.'"':''):'')?>>
<?

		if (!empty($this->pList->arActions) || $this->pList->bCanBeEdited):
			$check_id = RandString(5);
?>
	<td class="adm-list-table-cell adm-list-table-checkbox adm-list-table-checkbox-hover<?=$this->bReadOnly? ' adm-list-table-checkbox-disabled':''?>"><input type="checkbox" class="adm-checkbox adm-designed-checkbox" name="SUB_ID[]" id="<?=$this->table_id."_".$this->id."_".$check_id;?>" value="<?=$this->id?>" autocomplete="off" title="<?=GetMessage("admin_lib_list_check")?>"<?=$this->bReadOnly? ' disabled="disabled"':''?><?=$this->bEditMode ? ' checked="checked" disabled="disabled"' : ''?> /><label class="adm-designed-checkbox-label adm-checkbox" for="<?=$this->table_id."_".$this->id."_".$check_id;?>"></label></td>
<?
		endif;

		if($this->pList->bShowActions):
			if(!empty($this->aActions)):
?>
	<td class="adm-list-table-cell adm-list-table-popup-block" onclick="BX.adminSubList.ShowMenu(this.firstChild, this.parentNode.oncontextmenu(), this.parentNode);"><div class="adm-list-table-popup" title="<?=GetMessage("admin_lib_list_actions_title")?>"></div></td>
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
				$val = '';
				if (isset($this->arRes[$id]))
				{
					if(is_string($this->arRes[$id]))
						$val = trim($this->arRes[$id]);
					else
						$val = $this->arRes[$id];
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
							if($field["edit"]["values"][$val])
								$val = htmlspecialcharsex($field["edit"]["values"][$val]);
							else
								$val = htmlspecialcharsex($val);
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

	function AddFieldNames($strFieldName,$strFieldType = 'HIDDEN')
	{
		if ($strFieldName <> '')
		{
			if (false == isset($this->arFieldNames[$strFieldName]))
			{
				if ($strFieldType == '')
					$strFieldType = 'HIDDEN';
				$this->arFieldNames[$strFieldName] = ToUpper($strFieldType);
			}
		}
	}

	function GetFieldNames()
	{
		return $this->arFieldNames;
	}
}

class CAdminSubContextMenu extends CAdminContextMenu
{
	public function __construct($items, $additional_items = array())
	{
		parent::__construct($items, $additional_items);
	}

	function Show()
	{
		$hkInst = CHotKeys::GetInstance();

		foreach(GetModuleEvents("main", "OnAdminSubContextMenuShow", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(&$this->items, &$this->additional_items));
		}

		$bFirst = true;
		$bNeedSplitClosing = false;
		foreach($this->items as $item)
		{
			if(!empty($item["NEWBAR"]))
				$this->EndBar();

			if($bFirst || !empty($item["NEWBAR"]))
				$this->BeginBar();

			if(!empty($item["NEWBAR"]) || !empty($item['SEPARATOR']))
				continue;

			if ($item['ICON'] != 'btn_sub_list' && !$bNeedSplitClosing)
			{
				$this->BeginRightBar();
				$bNeedSplitClosing = true;
			}

			$this->Button($item, $hkInst);

			$bFirst = false;
		}

		if (!((defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)))
		{
			if (!empty($this->additional_items))
			{
				if($bFirst)
				{
					$this->BeginBar();
				}

				$this->Additional();
			}
		}

		if ($bNeedSplitClosing)
			$this->EndRightBar();

		$this->EndBar();
	}

	function GetClassByID($icon_id)
	{
		switch ($icon_id)
		{
			case 'btn_sub_new':
				return 'adm-btn-add';
			case 'btn_sub_copy':
				return 'adm-btn-copy';
			case 'btn_sub_delete':
				return 'adm-btn-delete';
			case 'btn_sub_active':
				return 'adm-btn-active';
		}

		return '';
	}

	function GetActiveClassByID($icon_id)
	{
		return 'adm-btn-active';
	}
}

class CAdminSubContextMenuList extends CAdminSubContextMenu
{
	function BeginBar()
	{
		echo '<div class="adm-list-table-top">';
	}
	function BeginRightBar() {}
	function EndRightBar() {}

	function GetClassByID($icon_id)
	{
		if (mb_substr($icon_id, 0, 7) == 'btn_sub_new')
			return 'adm-btn-save adm-btn-add';
		else
			return parent::GetClassByID($icon_id);
	}

	function GetActiveClassByID($icon_id)
	{
		if (mb_substr($icon_id, 0, 7) == 'btn_sub_new')
			return 'adm-btn-save-active';
		else
			return parent::GetActiveClassByID($icon_id);
	}
}

class CAdminSubForm extends CAdminForm
{
/*
 *	list_url - string with params or array:
 *		LINK
 *		PARAMS (array key => value)
 *		POST_PARAMS (array key => value)
 */
	var $strListUrl = '';	// add
	var $strListUrlParams = ''; // add
	var $arListUrlParams = array(); // add
	var $strListPostParams = '';
	var $arListPostParams = array();
	var $boolShowSettings = false;

	public function __construct($name, $tabs, $bCanExpand = true, $bDenyAutosave = false, $list_url = '', $boolShowSettings = false)
	{
				global $APPLICATION;

		$arJSDescr = array(
			'js' => '/bitrix/js/iblock/subelementdet.js',
			'css' => '/bitrix/panel/iblock/sub-detail-public.css',
			'rel' => array('admin_interface')
		);

		CJSCore::RegisterExt('subelementdet', $arJSDescr);

		CUtil::InitJSCore(array("subelementdet"));

		if (is_array($tabs))
		{
			foreach (array_keys($tabs) as $index)
			{
				$tabs[$index]['ONSELECT'] = (string)($tabs[$index]['ONSELECT'] ?? '');
			}
		}

		parent::__construct($name, $tabs, $bCanExpand, $bDenyAutosave);

		$this->boolShowSettings = ($boolShowSettings === true);
		$this->SetShowSettings($this->boolShowSettings);

		$this->strListUrlParams = '';
		$this->arListUrlParams = array();

		if (is_array($list_url) && isset($list_url['LINK']))
		{
			$this->strListUrl = $list_url['LINK'];
			$this->__ParseListUrl(true);
			if (true == isset($list_url['PARAMS']))
				$this->__SetListUrlParams($list_url['PARAMS']);
		}
		else
		{
			$this->strListUrl = $list_url;
			$this->__ParseListUrl(true);
		}
		if ('' == $this->strListUrl)
		{
			$this->strListUrl = $APPLICATION->GetCurPageParam();
			$this->__ParseListUrl(true);
		}

		if (is_array($list_url) && !empty($list_url['POST_PARAMS']))
			$this->__SetListPostParams($list_url['POST_PARAMS'],true);
	}

	function ShowSettings()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;

		$APPLICATION->RestartBuffer();

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

		/** @noinspection PhpUnusedLocalVariableInspection */
		$adminFormParams = array(
			'tabPrefix' => 'csubedit'
		);
		require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/interface/settings_admin_form.php");
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
		die();
	}

	public function GetListUrl($boolFull = false)
	{
		return $this->strListUrl.(true == $boolFull && '' != $this->strListUrlParams ? '?'.$this->strListUrlParams : '');
	}

	public function GetListPostParams($boolType = false, $boolJS = false)
	{
		$boolType = (true == $boolType ? true : false);
		if ($boolType)
		{
			$boolJS = (true == $boolJS ? true : false);
			return ($boolJS ? CUtil::PhpToJsObject($this->arListPostParams) : $this->arListPostParams);
		}
		else
		{
			return $this->strListPostParams;
		}
	}

	private function __UpdateListUrlParams()
	{
		$this->strListUrlParams = '';
		if (false == empty($this->arListUrlParams))
		{
			foreach ($this->arListUrlParams as $key => $value)
				$this->strListUrlParams .= $key.'='.$value.'&';
			$this->strListUrlParams = mb_substr($this->strListUrlParams, 0, -1);
		}
	}

	private function __ClearListUrlParams()
	{
		$this->arListUrlParams = array();
		$this->strListUrlParams = '';
	}

	private function __AddListUrlParams($strKey,$strValue)
	{
		if ('' != $strKey)
		{
			$this->arListUrlParams[$strKey] = $strValue;
			$this->__UpdateListUrlParams();
		}
	}

	private function __DeleteListUrlParams($mxKey)
	{
		if (true == is_array($mxKey))
		{
			foreach ($mxKey as $value)
				if (('' != $value) && (true == array_key_exists($value,$this->arListUrlParams)))
					unset($this->arListUrlParams[$value]);
		}
		elseif (('' != $mxKey) && (true == array_key_exists($mxKey,$this->arListUrlParams)))
		{
			unset($this->arListUrlParams[$mxKey]);
		}
		$this->__UpdateListUrlParams();
	}

	private function __SetListUrlParams($mxParams,$boolClear = false)
	{
		if (true == $boolClear)
			$this->arListUrlParams = array();

		if (!is_array($mxParams))
		{
			$arParams = array();
			parse_str($mxParams,$arParams);
			$mxParams = (true == is_array($arParams) ? $arParams : array());
		}
		foreach ($mxParams as $key => $value)
			if ('' != $key)
				$this->arListUrlParams[$key] = $value;

		$this->__UpdateListUrlParams();
	}

	private function __ParseListUrl($boolClear = false)
	{
		$mxPos = mb_strpos($this->strListUrl, '?');
		if (false !== $mxPos)
		{
			$this->__SetListUrlParams(mb_substr($this->strListUrl, $mxPos + 1), $boolClear);
			$this->strListUrl = mb_substr($this->strListUrl, 0, $mxPos);
		}
	}

	private function __UpdateListPostParams()
	{
		$this->strListPostParams = '';
		if (false == empty($this->arListPostParams))
		{
			foreach ($this->arListPostParams as $key => $value)
				$this->strListPostParams .= $key.'='.$value.'&';
			$this->strListPostParams = mb_substr($this->strListPostParams, 0, -1);
		}
	}

	private function __ClearListPostParams()
	{
		$this->arListPostParams = array();
		$this->strListPostParams = '';
	}

	private function __AddListPostParams($strKey,$strValue)
	{
		if ('' != $strKey)
		{
			$this->arListPostParams[$strKey] = $strValue;
			$this->__UpdateListPostParams();
		}
	}

	private function __DeleteListPostParams($mxKey)
	{
		if (true == is_array($mxKey))
		{
			foreach ($mxKey as $value)
				if (('' != $value) && (true == array_key_exists($value,$this->arListPostParams)))
					unset($this->arListPostParams[$value]);
		}
		elseif (('' != $mxKey) && (true == array_key_exists($mxKey,$this->arListPostParams)))
		{
			unset($this->arListPostParams[$mxKey]);
		}
		$this->__UpdateListPostParams();
	}

	private function __SetListPostParams($mxParams,$boolClear)
	{
		if (true == $boolClear)
			$this->arListPostParams = array();
		if (!is_array($mxParams))
		{
			$arParams = array();
			parse_str($mxParams,$arParams);
			$mxParams = $arParams;
		}
		foreach ($mxParams as $key => $value)
			if ('' != $key)
				$this->arListPostParams[$key] = $value;

		$this->__UpdateListPostParams();
	}

	function ShowTabButtons()
	{
		$s = '';
		if ($this->bShowSettings)
		{
			$aAdditionalMenu = array();

			$this->__AddListUrlParams('mode', 'settings');
			$strLink = $this->GetListUrl(true);
			$this->__DeleteListUrlParams('mode');

			$aAdditionalMenu[] = array(
				"TEXT"=>GetMessage("admin_lib_menu_settings"),
				"TITLE"=>GetMessage("admin_lib_context_sett_title"),
				"ONCLICK"=>$this->name.".ShowSettings('".htmlspecialcharsex(CUtil::JSEscape($strLink))."')",
				"ICON"=>"btn_settings",
			);

			$nameExists = isset($this->session["ADMIN_CUSTOM_FIELDS"])
				&& is_array($this->session["ADMIN_CUSTOM_FIELDS"])
				&& array_key_exists($this->name, $this->session["ADMIN_CUSTOM_FIELDS"])
			;
			if($this->bCustomFields)
			{
				if ($nameExists)
				{
					$aAdditionalMenu[] = array(
						"TEXT" => GetMessage("admin_lib_sett_sett_enable_text"),
						"TITLE" => GetMessage("admin_lib_sett_sett_enable"),
						"ONCLICK" => $this->name.'.EnableSettings();',
						"ICON" => 'custom-fields-on'
					);
				}
				else
				{
					$aAdditionalMenu[] = array(
						"TEXT" => GetMessage("admin_lib_sett_sett_disable_text"),
						"TITLE" => GetMessage("admin_lib_sett_sett_disable"),
						"ONCLICK" => $this->name.'.DisableSettings();',
						"ICON" => 'custom-fields-off'
					);
				}
			}
			$s .= '<span class="adm-detail-subsettings-cont">';
			if (count($aAdditionalMenu) > 1)
			{
				$sMenuUrl = "BX.adminShowMenu(this, ".htmlspecialcharsbx(CAdminPopupEx::PhpToJavaScript($aAdditionalMenu)).", {active_class: 'bx-settings-btn-active'});";
				$s .= '<span id="'.$this->name.'_settings_btn" class="adm-detail-subsettings adm-detail-subsettings-arrow'.($nameExists ? '' : ' adm-detail-subsettings-active').'" onclick="'.$sMenuUrl.'"></span>';
			}
			else
			{
				$s .= '<a class="adm-detail-subsettings" href="javascript:void(0)" onclick="'.$aAdditionalMenu[0]['ONCLICK'].';"></a>';
			}
			$s .= '</span>';
		}

		return $s.CAdminTabControl::ShowTabButtons();
	}

	function End()
	{
		$hkInst = CHotKeys::GetInstance();

		if(!$this->bButtons)
		{
			while ($this->tabIndex < count($this->tabs))
				$this->BeginNextTab();

			//end previous tab
			$this->EndTab();
			echo '<div class="adm-detail-content-btns-wrap"><div class="adm-detail-content-btns adm-detail-content-btns-empty"></div></div>';
		}
		elseif (!$this->bPublicMode)
		{
			echo '</div></div>';
		}

		if (!$this->bPublicMode)
		{
			echo '
</div></div>
';
		}

		$Execs = $hkInst->GetCodeByClassName("CAdminSubForm");
		echo $hkInst->PrintJSExecs($Execs, $this->name);

		echo '

<input type="hidden" id="'.$this->name.'_active_tab" name="'.$this->name.'_active_tab" value="'.htmlspecialcharsbx($this->selectedTab).'">

<script type="text/javascript">';
		$s = "";
		foreach($this->tabs as $tab)
		{
			$s .= ($s <> ""? ", ":"").
			"{".
			"'DIV': '".$tab["DIV"]."' ".
			($tab["ONSELECT"] <> ""? ", 'ONSELECT': '".CUtil::JSEscape($tab["ONSELECT"])."'":"").
			"}";
		}

		echo 'var '.$this->name.' = new BX.adminSubTabControl("'.$this->name.'", "'.$this->unique_name.'", ['.$s.'], "'.CUtil::JSEscape($this->GetListUrl(true)).'",'.$this->GetListPostParams(true,true).');';

		if (!$this->bPublicMode)
		{
			$aEditOpt = CUserOptions::GetOption("edit", $this->unique_name, array());
			$aTabOpt = CUserOptions::GetOption("edit", 'admin_tabs', array());

			if($this->bCanExpand && count($this->tabs) > 1)
			{
				if($aEditOpt["expand"] == "on")
				{
					echo '
'.$this->name.'.ToggleTabs();';
				}
			}

			if ($aTabOpt["fix_top"] == "off" && $aEditOpt["expand"] != "on")
			{
				echo '
'.$this->name.'.ToggleFix(\'top\');';
			}

			if ($aTabOpt["fix_bottom"] == "off")
			{
				echo '
'.$this->name.'.ToggleFix(\'bottom\');';
			}
		}
		else
		{
			echo 'window.'.$this->name.'.setPublicMode(true); ';
		}
echo '
</script>
';
		if ($this->bPublicModeBuffer)
		{
			echo '</div>';
			echo '<script type="text/javascript">BX.ready(function() {'.$this->publicObject.'.SwapContent(\''.$this->publicModeBuffer_id.'\');});</script>';
		}
	}

	public static function closeSubForm($reload = true, $closeWait = true)
	{
		$reload = ($reload !== false);
		$closeWait = ($closeWait !== false);
		$result = '<script type="text/javascript">';
		$result .= '
			var currentWindow = top.window;
			if (top.BX.SidePanel && top.BX.SidePanel.Instance && top.BX.SidePanel.Instance.getTopSlider())
			{
				currentWindow = top.BX.SidePanel.Instance.getTopSlider().getWindow();
			}
		';
		if ($closeWait)
			$result .= 'currentWindow.BX.closeWait(); ';
		$result .= 'currentWindow.BX.WindowManager.Get().AllowClose(); currentWindow.BX.WindowManager.Get().Close();';
		if ($reload)
			$result .= ' if (!!currentWindow.ReloadSubList) { currentWindow.ReloadSubList(); }';
		$result .= '</script>';
		echo $result;
		die();
	}
}

class CAdminSubResult extends CAdminResult
{
	var $list_url;
	var $list_url_params;

	public function __construct($res, $table_id, $list_url)
	{
		$this->list_url = $list_url;
		$this->list_url_params = '';
		$intPos = mb_strpos($this->list_url, '?');
		if (false !== $intPos)
		{
			$this->list_url_params = mb_substr($this->list_url, $intPos + 1);
			$this->list_url = mb_substr($this->list_url, 0, $intPos);
		}
		parent::__construct($res, $table_id);
	}

	public function NavStart($nPageSize=20, $bShowAll=true, $iNumPage=false)
	{
		$navResult = new CAdminSubResult(null, '', '');
		$nSize = $navResult->GetNavSize($this->table_id, $nPageSize, $this->list_url.('' != $this->list_url_params ? '?'.$this->list_url_params : ''));
		unset($navResult);

		if(!is_array($nPageSize))
			$nPageSize = array();

		$nPageSize["nPageSize"] = $nSize;
		if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] === "excel")
		{
			$nPageSize["NavShowAll"] = true;
		}

		$this->nInitialSize = $nPageSize["nPageSize"];

		parent::NavStart($nPageSize, $bShowAll, $iNumPage);
	}

	/**
	 * @param bool|string $table_id
	 * @param int|array $nPageSize
	 * @param string $list_url
	 * @return int
	 */
	public static function GetNavSize($table_id=false, $nPageSize=20, $list_url = '')
	{
		$list_url = (string)$list_url;
		if ($list_url != '')
		{
			if (!is_array($nPageSize))
				$nPageSize = array('nPageSize' => $nPageSize);
			$nPageSize['sNavID'] = $list_url;
		}
		return parent::GetNavSize($table_id, $nPageSize);
	}

	public function GetNavPrint($title, $show_allways=true, $StyleText="", $template_path=false, $arDeleteParam=false)
	{
		if($template_path === false)
			$template_path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin/templates/navigation.php";

		/** @noinspection PhpUnusedLocalVariableInspection */
		$add_anchor = $this->add_anchor;

		/** @noinspection PhpUnusedLocalVariableInspection */
		$sBegin = GetMessage("nav_begin");
		/** @noinspection PhpUnusedLocalVariableInspection */
		$sEnd = GetMessage("nav_end");
		/** @noinspection PhpUnusedLocalVariableInspection */
		$sNext = GetMessage("nav_next");
		/** @noinspection PhpUnusedLocalVariableInspection */
		$sPrev = GetMessage("nav_prev");
		/** @noinspection PhpUnusedLocalVariableInspection */
		$sAll = GetMessage("nav_all");
		/** @noinspection PhpUnusedLocalVariableInspection */
		$sPaged = GetMessage("nav_paged");

		$nPageWindow = $this->nPageWindow;

		if(!$show_allways)
		{
			if ($this->NavRecordCount == 0 || ($this->NavPageCount == 1 && $this->NavShowAll == false))
				return '';
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		$sUrlPath = $this->list_url;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$strNavQueryString = htmlspecialcharsbx($this->list_url_params);

		if($template_path!==false && !file_exists($template_path) && file_exists($_SERVER["DOCUMENT_ROOT"].$template_path))
			$template_path = $_SERVER["DOCUMENT_ROOT"].$template_path;

		if($this->bDescPageNumbering === true)
		{
			if($this->NavPageNomer + floor($nPageWindow/2) >= $this->NavPageCount)
				$nStartPage = $this->NavPageCount;
			else
			{
				if($this->NavPageNomer + floor($nPageWindow/2) >= $nPageWindow)
					$nStartPage = $this->NavPageNomer + floor($nPageWindow/2);
				else
				{
					if($this->NavPageCount >= $nPageWindow)
						$nStartPage = $nPageWindow;
					else
						$nStartPage = $this->NavPageCount;
				}
			}

			if($nStartPage - $nPageWindow >= 0)
				$nEndPage = $nStartPage - $nPageWindow + 1;
			else
				$nEndPage = 1;
		}
		else
		{
			if($this->NavPageNomer > floor($nPageWindow/2) + 1 && $this->NavPageCount > $nPageWindow)
				$nStartPage = $this->NavPageNomer - floor($nPageWindow/2);
			else
				$nStartPage = 1;

			if($this->NavPageNomer <= $this->NavPageCount - floor($nPageWindow/2) && $nStartPage + $nPageWindow-1 <= $this->NavPageCount)
				$nEndPage = $nStartPage + $nPageWindow - 1;
			else
			{
				$nEndPage = $this->NavPageCount;
				if($nEndPage - $nPageWindow + 1 >= 1)
					$nStartPage = $nEndPage - $nPageWindow + 1;
			}
		}

		$this->nStartPage = $nStartPage;
		$this->nEndPage = $nEndPage;

		if($template_path!==false && file_exists($template_path))
		{
			ob_start();
			/** @noinspection PhpIncludeInspection */
			include($template_path);
			$res = ob_get_contents();
			ob_end_clean();
			$this->bFirstPrintNav = false;
			return $res;
		}
		else
		{
			return '';
		}
	}
}

class CAdminSubMessage extends CAdminMessage
{
	public function __construct($message, $exception = false)
	{
		parent::__construct($message,$exception);
	}

	public function Show()
	{
		if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
		{
			return '<script type="text/javascript">top.BX.WindowManager.Get().ShowError(\''.CUtil::JSEscape(str_replace(array('<br>', '<br />', '<BR>', '<BR />'), "\r\n", htmlspecialcharsback($this->message['DETAILS']? $this->message['DETAILS'] : $this->message['MESSAGE']))).'\');</script>';
		}
		else
		{
			if($this->message["MESSAGE"])
				$title = '<div class="adm-info-message-title">'.$this->_formatHTML($this->message["MESSAGE"]).'</div>';
			else
				$title = '';

			if($this->message["DETAILS"])
				$details = $this->_formatHTML($this->message["DETAILS"]);
			else
				$details = '';

			if($this->message["TYPE"] == "OK")
			{
				$s = '
<div class="adm-info-message-wrap adm-info-message-green">
	<div class="adm-info-message">
		'.$title.'
		'.$details.'
		<div class="adm-info-message-icon"></div>
	</div>
</div>
';
			}
			elseif($this->message["TYPE"] == "PROGRESS")
			{
				if ($this->message['PROGRESS_ICON'])
					$title = '<div class="adm-info-message-icon-progress"></div>'.$title;

				$details = str_replace("#PROGRESS_BAR#", $this->_getProgressHtml(), $details);
				$s = '
<div class="adm-info-message-wrap adm-info-message-gray">
	<div class="adm-info-message">
		'.$title.'
		'.$details.'
		<div class="adm-info-message-buttons">'.$this->_getButtonsHtml().'</div>
	</div>
</div>
';
			}
			else
			{
				$s = '
<div class="adm-info-message-wrap adm-info-message-red">
	<div class="adm-info-message">
		'.$title.'
		'.$details.'
		<div class="adm-info-message-icon"></div>
	</div>
</div>
';
			}

			return $s;
		}
	}

	public static function ShowOldStyleError($message)
	{
		if(!empty($message))
		{
			$m = new CAdminSubMessage(array("MESSAGE"=>GetMessage("admin_lib_error"), "DETAILS"=>$message, "TYPE"=>"ERROR"));
			echo $m->Show();
		}
	}

	public static function ShowMessage($message)
	{
		if(!empty($message))
		{
			$m = new CAdminSubMessage($message);
			echo $m->Show();
		}
	}

	public static function ShowNote($message)
	{
		if(!empty($message))
		{
			$m = new CAdminSubMessage(array("MESSAGE"=>$message, "TYPE"=>"OK"));
			echo $m->Show();
		}
	}
}
