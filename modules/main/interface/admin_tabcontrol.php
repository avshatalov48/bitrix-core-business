<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */

use Bitrix\Main\Web\Uri;

/*Tab Control*/
class CAdminTabControl
{
	var $name, $unique_name;
	var $tabs = array();
	var $selectedTab;
	var $tabIndex = 0;
	var $bButtons = false;
	var $arButtonsParams = [];
	var $bCanExpand;
	var $bPublicModeBuffer = false;
	var $bShowSettings = false;
	var $publicModeBuffer_id;

	/** @var CAdminTabEngine */
	var $customTabber;

	var $bPublicMode = false;
	var $isPublicFrame = false;
	var $publicObject = 'BX.WindowManager.Get()';

	var $AUTOSAVE = null;
	protected $tabEvent = false;

	var $isSidePanel = false;
	var $publicSidePanel = false;
	var $isShownSidePanelFields = false;

	public function __construct($name, $tabs, $bCanExpand = true, $bDenyAutoSave = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		//array(array("DIV"=>"", "TAB"=>"", "ICON"=>, "TITLE"=>"", "ONSELECT"=>"javascript"), ...)
		if(is_array($tabs))
		{
			$this->tabs = $tabs;
		}
		$this->name = $name;
		$this->unique_name = $name."_".md5($APPLICATION->GetCurPage());

		global $adminSidePanelHelper;
		$this->publicSidePanel =  (is_object($adminSidePanelHelper) && $adminSidePanelHelper->isPublicSidePanel());
		$this->bPublicMode = defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1;
		$this->bCanExpand = !$this->bPublicMode && (bool)$bCanExpand;

		$this->SetSelectedTab();

		if (!$bDenyAutoSave && CAutoSave::Allowed())
		{
			$this->AUTOSAVE = new CAutoSave();
		}

		$this->isSidePanel = ((isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y") &&
			isset($_REQUEST["IFRAME_TYPE"]) && $_REQUEST["IFRAME_TYPE"] === "SIDE_SLIDER");
		$this->isPublicFrame = $this->isSidePanel && (isset($_REQUEST["IFRAME_TYPE"]) &&
			$_REQUEST["IFRAME_TYPE"] === "PUBLIC_FRAME");
	}

	function SetPublicMode($jsObject = false)
	{
		$this->bPublicMode = true;
		$this->bShowSettings = false;
		$this->bCanExpand = false;
		$this->bPublicModeBuffer = true;

		if ($jsObject)
			$this->publicObject = $jsObject;
	}

	/**
	 * @param CAdminTabEngine $customTabber
	 */
	function AddTabs(&$customTabber)
	{
		if (!$this->customTabber)
		{
			$this->customTabber = $customTabber;

			$arCustomTabs = $this->customTabber->GetTabs();
			if ($arCustomTabs && is_array($arCustomTabs))
			{
				$arTabs = array();
				$i = 0;
				foreach ($this->tabs as $value)
				{
					foreach ($arCustomTabs as $key1 => $value1)
					{
						if (array_key_exists("SORT", $value1) && intval($value1["SORT"]) == $i)
						{
							$arTabs[] = array_merge($value1, array("CUSTOM" => "Y"));
							unset($arCustomTabs[$key1]);
						}
					}

					$arTabs[] = $value;
					$i++;
				}

				foreach ($arCustomTabs as $value1)
					$arTabs[] = array_merge($value1, array("CUSTOM" => "Y"));

				$this->tabs = $arTabs;
				$this->SetSelectedTab();
			}
		}
	}

	function OnAdminTabControlBegin()
	{
		if (!$this->tabEvent)
		{
			foreach(GetModuleEvents("main", "OnAdminTabControlBegin", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array(&$this));
			$this->tabEvent = true;
		}
	}

	function SetSelectedTab()
	{
		$this->selectedTab = $this->tabs[0]["DIV"];
		if(isset($_REQUEST[$this->name."_active_tab"]))
		{
			foreach($this->tabs as $tab)
			{
				if($tab["DIV"] == $_REQUEST[$this->name."_active_tab"])
				{
					$this->selectedTab = $_REQUEST[$this->name."_active_tab"];
					break;
				}
			}
		}
	}

	function Begin()
	{
		$hkInst = CHotKeys::getInstance();

		$this->OnAdminTabControlBegin();
		$this->tabIndex = 0;

		$this->SetSelectedTab();

		if (!$this->bPublicMode)
		{
?>
<div class="adm-detail-block" id="<?=$this->name?>_layout">
	<div class="adm-detail-tabs-block<?=$this->bShowSettings?' adm-detail-tabs-block-settings':''?>" id="<?=$this->name?>_tabs">
<?
		}

		$len = count($this->tabs);
		$tabs_html = '';
		foreach($this->tabs as $key => $tab)
		{
			$bSelected = ($tab["DIV"] == $this->selectedTab);
			$tabs_html .= '<span title="'.($tab["TITLE"] ?? '').$hkInst->GetTitle("tab-container").'" '.
				'id="tab_cont_'.$tab["DIV"].'" '.
				'class="adm-detail-tab'.($bSelected ? ' adm-detail-tab-active':'').($key==$len-1? ' adm-detail-tab-last':'').'" '.
				'onclick="'.$this->name.'.SelectTab(\''.$tab["DIV"].'\');">'.htmlspecialcharsex($tab["TAB"]).'</span>';
		}

		$tabs_html .= $this->ShowTabButtons();

		if (!$this->bPublicMode)
		{
			echo $tabs_html;
?>
	</div>
	<div class="adm-detail-content-wrap">
<?
		}
		else
		{
			echo '
<script type="text/javascript">
'.$this->publicObject.'.SetHead(\''.CUtil::JSEscape($tabs_html).'\');
';
			if ($this->AUTOSAVE)
			{
				echo '
'.$this->publicObject.'.setAutosave();
';
			}
			echo '
</script>
';
			if ($this->bPublicModeBuffer)
			{
				$this->publicModeBuffer_id = 'bx_tab_control_'.RandString(6);
				echo '<div id="'.$this->publicModeBuffer_id.'" style="display: none;">';
			}
		}
	}

	function ShowTabButtons()
	{
		$s = '';
		if (!$this->bPublicMode)
		{
			if(count($this->tabs) > 1 && $this->bCanExpand/* || $this->AUTOSAVE*/)
			{
				$s .= '<div class="adm-detail-title-setting" onclick="'.$this->name.'.ToggleTabs();" title="'.GetMessage("admin_lib_expand_tabs").'" id="'.$this->name.'_expand_link"><span class="adm-detail-title-setting-btn adm-detail-title-expand"></span></div>';
			}
		}
		return $s;
	}

	function BeginNextTab($options = array())
	{
		if ($this->AUTOSAVE)
			$this->AUTOSAVE->Init();

		//end previous tab
		$this->EndTab();

		if($this->tabIndex >= count($this->tabs))
			return;

		$css = '';
		if ($this->tabs[$this->tabIndex]["DIV"] <> $this->selectedTab)
			$css .= 'display:none; ';

		echo '<div class="adm-detail-content'.(isset($options["className"]) ? " ".$options["className"] : "").'"
		 		id="'.$this->tabs[$this->tabIndex]["DIV"].'"'.($css != '' ? ' style="'.$css.'"' : '').'>';

		/*if($this->tabs[$this->tabIndex]["ICON"] <> "")
			echo '
		<td class="icon"><div id="'.$this->tabs[$this->tabIndex]["ICON"].'"></div></td>
		';*/

		if (!isset($options["showTitle"]) || $options["showTitle"] === true)
		{
			echo '<div class="adm-detail-title">'.($this->tabs[$this->tabIndex]["TITLE"] ?? '').'</div>';
		}

echo '
	<div class="adm-detail-content-item-block">
		<table class="adm-detail-content-table edit-table" id="'.$this->tabs[$this->tabIndex]["DIV"].'_edit_table">
			<tbody>
';
		if(array_key_exists("CUSTOM", $this->tabs[$this->tabIndex]) && $this->tabs[$this->tabIndex]["CUSTOM"] == "Y")
		{
			$this->customTabber->ShowTab($this->tabs[$this->tabIndex]["DIV"]);
			$this->tabIndex++;
			$this->BeginNextTab();
		}
		elseif(array_key_exists("CONTENT", $this->tabs[$this->tabIndex]))
		{
			echo $this->tabs[$this->tabIndex]["CONTENT"];
			$this->tabIndex++;
			$this->BeginNextTab();
		}
		else
		{
			$this->tabIndex++;
		}
	}

	function EndTab()
	{
		if(
			$this->tabIndex < 1
			|| $this->tabIndex > count($this->tabs)
			|| isset($this->tabs[$this->tabIndex-1]["_closed"]) && $this->tabs[$this->tabIndex-1]["_closed"] === true
		)
		{
			return;
		}

		echo '
			</tbody>
		</table>
	</div>
</div>
';

		$this->tabs[$this->tabIndex-1]["_closed"] = true;
	}

	/**
	 * @param bool|array $aParams
	 */
	function Buttons($aParams=false)
	{
		$hkInst = CHotKeys::getInstance();

		while($this->tabIndex < count($this->tabs))
			$this->BeginNextTab();

		$this->bButtons = true;
		if($aParams === false)
			$this->arButtonsParams = [];
		else
			$this->arButtonsParams = $aParams;

		//end previous tab
		$this->EndTab();

		if (!$this->bPublicMode)
		{
			echo '<div class="adm-detail-content-btns-wrap" id="'.$this->name.'_buttons_div"><div class="adm-detail-content-btns">';
		}

		if (isset($_REQUEST['subdialog']) && $_REQUEST['subdialog'])
		{
			echo '<input type="hidden" name="suffix" value="'.mb_substr($GLOBALS['obJSPopup']->suffix, 1).'" />';
			echo '<input type="hidden" name="subdialog" value="Y" />';
		}

		if($aParams !== false)
		{
			$aParams["ajaxMode"] = ($aParams["ajaxMode"] ?? true);

			if (!$this->isShownSidePanelFields)
			{
				$this->getSidePanelFields();
			}

			if ($this->bPublicMode)
			{
				if(isset($_REQUEST['from_module']) && $_REQUEST['from_module'] <> '')
				{
					echo '<input type="hidden" name="from_module" value="'.htmlspecialcharsbx($_REQUEST['from_module']).'" />';
				}

				if (isset($aParams['buttons']) && is_array($aParams['buttons']))
				{
					echo '
<input type="hidden" name="bxpublic" value="Y" />
<script type="text/javascript">'.$this->publicObject.'.SetButtons('.CUtil::PhpToJsObject($aParams['buttons']).');</script>
';
				}
				else
				{
					echo '
<input type="hidden" name="bxpublic" value="Y" /><input type="hidden" name="save" value="Y" />
<script type="text/javascript">'.$this->publicObject.'.SetButtons(['.$this->publicObject.'.btnSave, '.$this->publicObject.'.btnCancel]);</script>
';
				}
			}
			elseif($this->isSidePanel && $aParams["ajaxMode"])
			{
				$this->getAjaxButtons($aParams);
			}
			else
			{
				$aParams['btnSave'] = $aParams['btnSave'] ?? true;
				$aParams['btnApply'] = $aParams['btnApply'] ?? true;
				$aParams['btnCancel'] = $aParams['btnCancel'] ?? true;
				$aParams['btnSaveAndAdd'] = $aParams['btnSaveAndAdd'] ?? false;

				$disable = isset($aParams['disabled']) && $aParams['disabled'] === true
					? ' disabled data-btn-disabled="Y"'
					: ''
				;

				if($aParams["btnSave"] !== false)
				{
					echo '<input' . $disable .' type="submit" name="save" value="'.GetMessage("admin_lib_edit_save").'" title="'.GetMessage("admin_lib_edit_save_title").$hkInst->GetTitle("Edit_Save_Button").'" class="adm-btn-save" />';
					echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Save_Button"));
				}
				if($aParams["btnApply"] !== false)
				{
					echo '<input' . $disable .' type="submit" name="apply" value="'.GetMessage("admin_lib_edit_apply").'" title="'.GetMessage("admin_lib_edit_apply_title").$hkInst->GetTitle("Edit_Apply_Button").'" />';
					echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Apply_Button"));
				}
				if($aParams["btnCancel"] !== false && !empty($aParams["back_url"]) && !preg_match('/(javascript|data)[\s\0-\13]*:/i', $aParams["back_url"]))
				{
					echo '<input' . $disable . ' type="button" value="'.GetMessage("admin_lib_edit_cancel").'" name="cancel" onClick="top.window.location=\''.htmlspecialcharsbx(CUtil::addslashes($aParams["back_url"])).'\'" title="'.GetMessage("admin_lib_edit_cancel_title").$hkInst->GetTitle("Edit_Cancel_Button").'" />';
					echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Cancel_Button"));
				}
				if($aParams["btnSaveAndAdd"] === true)
				{
					echo '<input' . $disable . ' type="submit" name="save_and_add" value="'.GetMessage("admin_lib_edit_save_and_add").'" title="'.GetMessage("admin_lib_edit_save_and_add_title").$hkInst->GetTitle("Edit_Save_And_Add_Button").'" class="adm-btn-add" />';
					echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Save_And_Add_Button"));
				}
			}
		}
	}

	protected function getAjaxButtons(array $params)
	{
		$htmlAjaxButtons = '';

		$params['btnSave'] = $params['btnSave'] ?? true;
		$params['btnApply'] = $params['btnApply'] ?? true;
		$params['btnCancel'] = $params['btnCancel'] ?? true;
		$params['btnSaveAndAdd'] = $params['btnSaveAndAdd'] ?? false;

		$disable = isset($params['disabled']) && $params['disabled'] === true
			? ' disabled data-btn-disabled="Y"'
			: ''
		;

		if ($params["btnSave"] !== false)
		{
			$htmlAjaxButtons .= '<input' . $disable . ' type="button" name="save" value="' . GetMessage("admin_lib_edit_save").'" title="'.GetMessage("admin_lib_edit_save_title").'" class="adm-btn-save">';
		}
		if ($params["btnApply"] !== false)
		{
			$htmlAjaxButtons .= '<input' . $disable . ' type="button" name="apply" value="'.GetMessage("admin_lib_edit_apply").'" title="'.GetMessage("admin_lib_edit_apply_title").'">';
		}
		if ($params["btnCancel"] !== false)
		{
			$htmlAjaxButtons .= '<input' . $disable . ' type="button" name="cancel" value="'.GetMessage("admin_lib_edit_cancel").'" title="'.GetMessage("admin_lib_edit_cancel_title").'">';
		}
		if ($params["btnSaveAndAdd"] === true)
		{
			global $APPLICATION;
			$addUrl = (new Uri($APPLICATION->GetCurPage()))
				->addParams(["lang" => LANGUAGE_ID])
				->getUri()
			;
			if ($addUrl <> '' && !preg_match('/(javascript|data)[\s\0-\13]*:/i', $addUrl))
			{
				$htmlAjaxButtons .= '<input' . $disable . ' type="button" name="save_and_add" value="'.GetMessage("admin_lib_edit_save_and_add").'" title="'.GetMessage("admin_lib_edit_save_and_add_title").'" class="adm-btn-add"  data-url="'.htmlspecialcharsbx(CUtil::addslashes($addUrl)).'">';
			}
			else
			{
				$htmlAjaxButtons .= '<input' . $disable . ' type="button" name="save_and_add" value="'.GetMessage("admin_lib_edit_save_and_add").'" title="'.GetMessage("admin_lib_edit_save_and_add_title").'" class="adm-btn-add">';
			}
		}

		echo $htmlAjaxButtons;
	}

	public function getSidePanelFields()
	{
		if ($this->isSidePanel)
		{
			$this->isShownSidePanelFields = true;

			echo '<input type="hidden" name="IFRAME" value="Y">';
			echo '<input type="hidden" name="IFRAME_TYPE" value="SIDE_SLIDER">';
		}
	}

	/**
	 * @param bool|array $arJSButtons
	 */
	function ButtonsPublic($arJSButtons = false)
	{
		while ($this->tabIndex < count($this->tabs))
			$this->BeginNextTab();

		$this->bButtons = true;
		$this->EndTab();

		if ($this->bPublicMode)
		{
			if(isset($_REQUEST['from_module']) && $_REQUEST['from_module'] <> '')
			{
				echo '<input type="hidden" name="from_module" value="'.htmlspecialcharsbx($_REQUEST['from_module']).'" />';
			}

			if ($arJSButtons === false)
			{
				echo '
<input type="hidden" name="bxpublic" value="Y" /><input type="hidden" name="save" value="Y" />
<script type="text/javascript">'.$this->publicObject.'.SetButtons(['.$this->publicObject.'.btnSave, '.$this->publicObject.'.btnCancel]);</script>
';
			}
			elseif (is_array($arJSButtons))
			{
				$arJSButtons = array_values($arJSButtons);
				echo '
<input type="hidden" name="bxpublic" value="Y" />
<script type="text/javascript">'.$this->publicObject.'.SetButtons([
';
				foreach ($arJSButtons as $key => $btn)
				{
					if (mb_substr($btn, 0, 1) == '.')
						$btn = $this->publicObject.$btn;
					echo $key ? ',' : '', $btn, "\r\n"; // NO JSESCAPE HERE! string must contain valid js object
				}
				echo '
]);</script>
';
			}
		}
	}

	function End()
	{
		$hkInst = CHotKeys::getInstance();

		if(!$this->bButtons)
		{
			while ($this->tabIndex < count($this->tabs))
				$this->BeginNextTab();

			//end previous tab
			$this->EndTab();
			if (!$this->bPublicMode)
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

		$Execs = $hkInst->GetCodeByClassName("CAdminTabControl");
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
			(isset($tab["ONSELECT"]) && $tab["ONSELECT"] <> ""? ", 'ONSELECT': '".CUtil::JSEscape($tab["ONSELECT"])."'":"").
			"}";
		}
		$adminTabControlParams = array();
		if (isset($this->arButtonsParams["back_url"]) && $this->arButtonsParams["back_url"] <> '')
			$adminTabControlParams["backUrl"] = $this->arButtonsParams["back_url"];
		if ($this->isPublicFrame)
			$adminTabControlParams["isPublicFrame"] = "Y";
		if ($this->isSidePanel)
			$adminTabControlParams["isSidePanel"] = "Y";
		if ($this->publicSidePanel)
			$adminTabControlParams["publicSidePanel"] = "Y";
		echo '
if (!window.'.$this->name.' || !BX.is_subclass_of(window.'.$this->name.', BX.adminTabControl))
	window.'.$this->name.' = new BX.adminTabControl("'.$this->name.'", "'.$this->unique_name.
			'", ['.$s.'], '.CUtil::phpToJsObject($adminTabControlParams).');
else if(!!window.'.$this->name.')
	window.'.$this->name.'.PreInit(true);
';

		if (!$this->bPublicMode)
		{
			$aEditOpt = CUserOptions::GetOption("edit", $this->unique_name, array());
			$aTabOpt = CUserOptions::GetOption("edit", 'admin_tabs', array());

			if($this->bCanExpand && count($this->tabs) > 1)
			{
				if (isset($aEditOpt["expand"]) && $aEditOpt["expand"] == "on")
				{
					echo '
'.$this->name.'.ToggleTabs();';
				}
			}

			if (isset($aTabOpt["fix_top"]) && $aTabOpt["fix_top"] == "off" && $aEditOpt["expand"] != "on")
			{
				echo '
'.$this->name.'.ToggleFix(\'top\');';
			}

			if (isset($aTabOpt["fix_bottom"]) && $aTabOpt["fix_bottom"] == "off")
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

	function GetSelectedTab()
	{
		return $this->selectedTab;
	}

	function ActiveTabParam()
	{
		return $this->name."_active_tab=".urlencode($this->selectedTab);
	}

	// this method is temporarily disabled!
	//string, CAdminException, array("id"=>"name", ...)
	function ShowWarnings($form, $messages, $aFields=false)
	{
/*
		if(!$messages)
			return;
		$aMess = $messages->GetMessages();
		if(empty($aMess) || !is_array($aMess))
			return;
		$s = "";
		foreach($aMess as $msg)
		{
			$field_name = (is_array($aFields)? $aFields[$msg["id"]] : $msg["id"]);
			if(empty($field_name))
				continue;
			$s .= ($s <> ""? ", ":"")."{'name':'".CUtil::JSEscape($field_name)."', 'title':'".CUtil::JSEscape(htmlspecialcharsback($msg["text"]))."'}";
		}
		echo '
<script>
'.$this->name.'.ShowWarnings("'.CUtil::JSEscape($form).'", ['.$s.']);
</script>
';
*/
	}
}
