<?php

use Bitrix\Main;

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */

class CAdminForm extends CAdminTabControl
{
	var $arParams = array();
	var $arFields = array();
	var $arForbiddenFields = array();
	var $group = "";
	var $group_ajax = false;
	var $arFieldValues = array();
	var $sPrologContent = "";
	var $sEpilogContent = "";
	var $arButtonsParams = false;
	var $sButtonsContent = "";

	var $arSavedTabs = array();
	var $arSystemTabs = array();
	var $arSystemFields = array();
	var $arReqiredTabs = array();

	var $arCustomLabels = array();
	var $bCustomFields = false;
	var $sCurrentLabel = "";
	var $bCurrentReq = false;

	var $bShowSettings = true;

	/** @var \Bitrix\Main\Session\SessionInterface */
	protected $session;

	public function __construct($name, $tabs, $bCanExpand = true, $bDenyAutosave = false)
	{
		if (is_array($tabs))
		{
			foreach (array_keys($tabs) as $index)
			{
				$tabs[$index]['required'] = $tabs[$index]['required'] ?? false;
			}
		}
		parent::__construct($name, $tabs, $bCanExpand, $bDenyAutosave);
		$this->session = Main\Application::getInstance()->getSession();

		$this->tabIndex = 0;
		foreach($this->tabs as $i => $arTab)
			$this->tabs[$i]["FIELDS"] = array();

		//Parse customized labels
		$this->arCustomLabels = array();

		$arDisabled = CUserOptions::GetOption("form", $this->name."_disabled", "N");
		if(!is_array($arDisabled) || $arDisabled["disabled"] !== "Y")
		{
			foreach (CAdminFormSettings::getTabsArray($this->name) as $arTab)
			{
				foreach ($arTab["FIELDS"] as $customID => $customName)
				{
					$this->arCustomLabels[$customID] = $customName;
				}
			}
		}
		ob_start();
	}

	function SetSelectedTab()
	{
		parent::SetSelectedTab();

		$arDisabled = CUserOptions::GetOption("form", $this->name."_disabled", "N");
		if(!is_array($arDisabled) || $arDisabled["disabled"] !== "Y")
		{
			if(isset($_REQUEST[$this->name."_active_tab"]))
			{
				$arCustomTabs = CAdminFormSettings::getTabsArray($this->name);
				foreach($arCustomTabs as $tab_id => $arTab)
				{
					if($tab_id == $_REQUEST[$this->name."_active_tab"])
					{
						$this->selectedTab = $_REQUEST[$this->name."_active_tab"];
						break;
					}
				}
			}
		}
	}

	function SetShowSettings($v)
	{
		$this->bShowSettings = $v;
	}

	function ShowSettings()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;

		$APPLICATION->RestartBuffer();

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

		require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/interface/settings_admin_form.php");

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");

		die();
	}

	function SetFieldsValues($bVarsFromForm, $db_record, $default_values)
	{
		foreach($default_values as $key=>$value)
			$this->SetFieldValue($key, $bVarsFromForm, $db_record, $value);
	}

	function SetFieldValue($field_name, $bVarsFromForm, $db_record, $default_value = false)
	{
		if($bVarsFromForm)
		{
			if(array_key_exists($field_name, $_REQUEST))
				$this->arFieldValues[$field_name] = $_REQUEST[$field_name];
			else
				$this->arFieldValues[$field_name] = $default_value;
		}
		else
		{
			if(is_array($db_record) && array_key_exists($field_name, $db_record) && isset($db_record[$field_name]))
				$this->arFieldValues[$field_name] = $db_record[$field_name];
			else
				$this->arFieldValues[$field_name] = $default_value;
		}
	}

	function GetFieldValue($field_name)
	{
		return $this->arFieldValues[$field_name];
	}

	function GetHTMLFieldValue($field_name)
	{
		return htmlspecialcharsbx($this->arFieldValues[$field_name]);
	}

	function GetHTMLFieldValueEx($field_name)
	{
		return htmlspecialcharsex($this->arFieldValues[$field_name]);
	}

	function GetFieldLabel($id)
	{
		return $this->arFields[$id]["content"];
	}

	function ShowTabButtons()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$s = '';
		if (!$this->bPublicMode)
		{
			if ($this->bShowSettings)
			{
				$link = DeleteParam(array("mode"));
				$link = $APPLICATION->GetCurPage()."?mode=settings".($link <> ""? "&".$link:"");

				$aAdditionalMenu = array();

				$aAdditionalMenu[] = array(
					"TEXT"=>GetMessage("admin_lib_menu_settings"),
					"TITLE"=>GetMessage("admin_lib_context_sett_title"),
					"ONCLICK"=>$this->name.".ShowSettings('".htmlspecialcharsbx(CUtil::JSEscape($link))."')",
					"GLOBAL_ICON"=>"adm-menu-setting"
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
							"ICON" => 'custom-fields-on',
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

				if (count($aAdditionalMenu) > 1)
				{
					$sMenuUrl = "BX.adminShowMenu(this, ".htmlspecialcharsbx(CAdminPopup::PhpToJavaScript($aAdditionalMenu)).", {active_class: 'bx-settings-btn-active'});";
					$s .= '<span id="'.$this->name.'_settings_btn" class="adm-detail-settings adm-detail-settings-arrow'.($nameExists ? '' : ' adm-detail-settings-active').'" onclick="'.$sMenuUrl.'"></span>';
				}
				else
				{
					$s .= '<a class="adm-detail-settings" href="javascript:void(0)" onclick="'.$aAdditionalMenu[0]['ONCLICK'].'"></a>';
				}
			}
		}

		return $s.parent::ShowTabButtons();
	}

	function Begin($arParams = array())
	{
		$this->tabIndex = -1;
		$this->arParams = (is_array($arParams) ? $arParams : []);

		$this->arParams['FORM_ATTRIBUTES'] = (string)($this->arParams['FORM_ATTRIBUTES'] ?? '');
	}

	function BeginNextFormTab()
	{
		if($this->tabIndex >= count($this->tabs))
			return;

		$this->tabIndex++;
		while(
			isset($this->tabs[$this->tabIndex])
			&& array_key_exists("CUSTOM", $this->tabs[$this->tabIndex])
			&& $this->tabs[$this->tabIndex]["CUSTOM"] == "Y"
		)
		{
			ob_start();
			$this->customTabber->ShowTab($this->tabs[$this->tabIndex]["DIV"]);
			$this->tabs[$this->tabIndex]["required"] = true;
			$this->tabs[$this->tabIndex]["CONTENT"] = ob_get_contents();
			ob_end_clean();
			$this->tabIndex++;
		}
	}

	function Show()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		//Save form defined tabs
		$this->arSavedTabs = $this->tabs;
		$this->arSystemTabs = array();
		$this->arReqiredTabs = array();

		foreach($this->tabs as $arTab)
		{
			$this->arSystemTabs[$arTab["DIV"]] = $arTab;

			if(is_array($arTab["FIELDS"]))
			{
				foreach($arTab["FIELDS"] as $arField)
					$this->arFields[$arField["id"]] = $arField;
			}

			if (
				$arTab["required"]
				&& (
					(
						isset($arTab["FIELDS"])
						&& is_array($arTab["FIELDS"])
					)
					|| isset($arTab["CONTENT"])
				)
			)
			{
				$this->arReqiredTabs[$arTab["DIV"]] = $arTab;
			}
		}
		//Save form defined fields
		$this->arSystemFields = $this->arFields;

		$arCustomTabs = CAdminFormSettings::getTabsArray($this->name);
		if (!empty($arCustomTabs))
		{
			$this->bCustomFields = true;
			$this->tabs = array();
			foreach($arCustomTabs as $tab_id => $arTab)
			{
				if(array_key_exists($tab_id, $this->arSystemTabs))
				{
					$arNewTab = $this->arSystemTabs[$tab_id];
					$arNewTab["TAB"] = $arTab["TAB"];
					$arNewTab["FIELDS"] = array();
				}
				else
				{
					$arNewTab = array(
						"DIV" => $tab_id,
						"TAB" => $arTab["TAB"],
						"ICON" => "main_user_edit",
						"TITLE" => "",
						"FIELDS" => array(),
					);
				}

				$bHasFields = false;
				foreach($arTab["FIELDS"] as $field_id => $content)
				{
					if(array_key_exists($field_id, $this->arSystemFields))
					{
						$arNewField = $this->arSystemFields[$field_id];
						$arNewField["content"] = $content;
						$bHasFields = true;
					}
					elseif(array_key_exists($field_id, $this->arForbiddenFields))
					{
						$arNewField = false;
					}
					elseif($content <> '')
					{
						$arNewField = array(
							"id" => $field_id,
							"content" => $content,
							"html" => '<td colspan="2">'.htmlspecialcharsex($content).'</td>',
							"delimiter" => true,
						);
					}
					else
					{
						$arNewField = false;
					}

					if(is_array($arNewField))
					{
						$this->arFields[$field_id] = $arNewField;
						$arNewTab["FIELDS"][] = $arNewField;
						foreach ($this->arReqiredTabs as $tab_id => $arReqTab)
						{
							if (is_array($arReqTab["FIELDS"]))
							{
								foreach ($arReqTab["FIELDS"] as $i => $arReqTabField)
								{
									if ($arReqTabField["id"] == $field_id)
										unset($this->arReqiredTabs[$tab_id]["FIELDS"][$i]);
								}
							}
						}
					}
				}

				if ($bHasFields)
					$this->tabs[] = $arNewTab;
			}

			foreach ($this->arReqiredTabs as $arReqTab)
			{
				if (!empty($arReqTab["FIELDS"]))
				{
					$this->tabs[] = $arReqTab;
					foreach ($arReqTab["FIELDS"] as $arReqTabField)
					{
						$this->arFields[$arReqTabField["id"]] = $arReqTabField;
					}
				}
				elseif (isset($arReqTab["CONTENT"]))
				{
					$this->tabs[] = $arReqTab;
				}
			}
		}

		if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] === "settings")
		{
			ob_end_clean();
			$this->ShowSettings();
			die();
		}
		else
		{
			ob_end_flush();
		}

		if (
			!isset($this->session["ADMIN_CUSTOM_FIELDS"])
			|| !is_array($this->session["ADMIN_CUSTOM_FIELDS"])
		)
		{
			$this->session["ADMIN_CUSTOM_FIELDS"] = [];
		}
		$arDisabled = CUserOptions::GetOption("form", $this->name."_disabled", "N");
		if(is_array($arDisabled) && $arDisabled["disabled"] === "Y")
		{
			$this->session["ADMIN_CUSTOM_FIELDS"][$this->name] = true;
			$this->tabs = $this->arSavedTabs;
			$this->arFields = $this->arSystemFields;
		}
		else
		{
			unset($this->session["ADMIN_CUSTOM_FIELDS"][$this->name]);
		}

		if(isset($_REQUEST[$this->name."_active_tab"]))
			$this->selectedTab = $_REQUEST[$this->name."_active_tab"];
		else
			$this->selectedTab = $this->tabs[0]["DIV"];

		foreach (array_keys($this->arFields) as $index)
		{
			$this->arFields[$index]['custom_html'] = (string)($this->arFields[$index]['custom_html'] ?? '');
			$this->arFields[$index]['delimiter'] = (bool)($this->arFields[$index]['delimiter'] ?? false);
			$this->arFields[$index]['valign'] = (string)($this->arFields[$index]['valign'] ?? '');
		}

		//To show
		$arHiddens = $this->arFields;
		echo $this->sPrologContent;
		if(array_key_exists("FORM_ACTION", $this->arParams))
			$action = htmlspecialcharsbx($this->arParams["FORM_ACTION"]);
		else
			$action = htmlspecialcharsbx($APPLICATION->GetCurPage());
		echo '<form method="POST" action="'.$action.'"  enctype="multipart/form-data" id="'.$this->name.'_form" name="'.$this->name.'_form"'.($this->arParams["FORM_ATTRIBUTES"] <> ''? ' '.$this->arParams["FORM_ATTRIBUTES"]:'').'>';

		$htmlGroup = "";
		if($this->group)
		{
			if (!empty($arCustomTabs))
			{
				foreach($this->tabs as $arTab)
				{
					if(is_array($arTab["FIELDS"]))
					{
						foreach($arTab["FIELDS"] as $arField)
						{
							if(
								($this->arFields[$arField["id"]]["custom_html"] !== '')
								|| ($this->arFields[$arField["id"]]["html"] !== '')
							)
							{
								$p = array_search($arField["id"], $this->arFields[$this->group]["group"]);
								if($p !== false)
									unset($this->arFields[$this->group]["group"][$p]);
							}
						}
					}
				}
			}

			if(!empty($this->arFields[$this->group]["group"]))
			{
				$htmlGroup .= '<tr class="heading" id="tr_'.$this->arFields[$this->group]["id"].'">'
					.$this->arFields[$this->group]["html"].'</tr>'
					."\n";
			}
		}

		$this->OnAdminTabControlBegin();
		$this->tabIndex = 0;
		while($this->tabIndex < count($this->tabs))
		{
			ob_start();//Start of the tab content
			$arTab = $this->tabs[$this->tabIndex];
			if(is_array($arTab["FIELDS"]))
			{
				foreach($arTab["FIELDS"] as $arField)
				{
					if(isset($this->arFields[$arField["id"]]["group"]))
					{
						if(!empty($this->arFields[$arField["id"]]["group"]))
						{
							echo $htmlGroup;
							foreach($this->arFields[$arField["id"]]["group"] as $p)
							{
								if($this->arFields[$p]["custom_html"])
									echo preg_replace("/^\\s*<tr/is", "<tr class=\"bx-in-group\"", $this->arFields[$p]["custom_html"]);
								elseif($this->arFields[$p]["html"] && !$this->arFields[$p]["delimiter"])
									echo '<tr class="bx-in-group" '.($this->arFields[$p]["valign"] <> ''? ' valign="'.$this->arFields[$p]["valign"].'"':'').' id="tr_'.$p.'">', $this->arFields[$p]["html"], "</tr>\n";
								unset($arHiddens[$this->arFields[$p]["id"]]);
								$this->arFields[$p] = array();
							}
						}
					}
					elseif(!empty($this->arFields[$arField["id"]]["custom_html"]))
					{
						if($this->group_ajax)
							echo preg_replace("#<script[^>]*>.*?</script>#imu", "", $this->arFields[$arField["id"]]["custom_html"]);
						else
							echo $this->arFields[$arField["id"]]["custom_html"];
					}
					elseif(!empty($this->arFields[$arField["id"]]["html"]))
					{
						$rowClass = (
							array_key_exists("rowClass", $this->arFields[$arField["id"]])
							? ' class="'.$this->arFields[$arField["id"]]["rowClass"].'"'
							: ''
						);

						if($this->arFields[$arField["id"]]["delimiter"])
							echo '<tr class="heading" id="tr_'.$arField["id"].'"'.$rowClass.'>';
						else
							echo '<tr'.($this->arFields[$arField["id"]]["valign"] <> ''? ' valign="'.$this->arFields[$arField["id"]]["valign"].'"':'').' id="tr_'.$arField["id"].'"'.$rowClass.'>';
						echo $this->arFields[$arField["id"]]["html"].'</tr>'."\n";
					}
					unset($arHiddens[$arField["id"]]);
				}
			}
			elseif (isset($arTab["CONTENT"]))
			{
				echo $arTab["CONTENT"];
			}
			$tabContent = ob_get_contents();
			ob_end_clean(); //Dispose tab content

			if ($tabContent == "")
			{
				array_splice($this->tabs, $this->tabIndex, 1); // forget about tab
			}
			else
			{

				$this->tabs[$this->tabIndex]["CONTENT"] = $tabContent;
				$this->tabIndex++;
			}
		}

		//sometimes form settings are incorrect but we must show required fields
		$requiredFields = '';
		foreach($arHiddens as $arField)
		{
			if(isset($arField["required"]) && $arField["required"])
			{
				if(!empty($this->arFields[$arField["id"]]["custom_html"]))
				{
					$requiredFields .= $this->arFields[$arField["id"]]["custom_html"];
				}
				elseif(!empty($this->arFields[$arField["id"]]["html"]))
				{
					if($this->arFields[$arField["id"]]["delimiter"])
						$requiredFields .= '<tr class="heading">';
					else
						$requiredFields .= '<tr>';
					$requiredFields .= $this->arFields[$arField["id"]]["html"].'</tr>';
				}
				unset($arHiddens[$arField["id"]]);
			}
		}
		if($requiredFields <> '')
		{
			$this->tabs[] = array(
				"CONTENT" => $requiredFields,
				"DIV" => "bx_req",
				"TAB" => GetMessage("admin_lib_required"),
				"TITLE" => GetMessage("admin_lib_required"),
			);
		}

		parent::Begin();

		while($this->tabIndex < count($this->tabs))
		{
			$this->BeginNextTab();
			if (isset($this->tabs[$this->tabIndex])) // last item not exists, because tabIndex starts from 0
			{
				echo $this->tabs[$this->tabIndex]["CONTENT"];
			}
		}

		parent::Buttons($this->arButtonsParams);
		echo $this->sButtonsContent;

		$this->End();
		echo $this->sEpilogContent;

		echo '<span class="bx-fields-hidden">';
		foreach($arHiddens as $arField)
		{
			echo $arField["hidden"] ?? '';
		}
		echo '</span>';

		echo '</form>';
	}

	function GetName()
	{
		return $this->name;
	}

	function GetFormName()
	{
		return $this->name."_form";
	}

	function GetCustomLabel($id, $content)
	{
		$bColumnNeeded = str_ends_with($content, ":");

		if($id === false)
			return $this->sCurrentLabel;
		elseif(array_key_exists($id, $this->arCustomLabels))
			return $this->arCustomLabels[$id].($bColumnNeeded? ":": "");
		else
			return $content;
	}

	function GetCustomLabelHTML($id = false, $content = "")
	{
		$bColumnNeeded = false;
		if ($content !== '')
		{
			$bColumnNeeded = str_ends_with($content, ":");
		}

		if ($id === false)
		{
			return (
				$this->bCurrentReq
					? '<span class="adm-required-field">' . htmlspecialcharsex($this->sCurrentLabel) . '</span>'
					: htmlspecialcharsex($this->sCurrentLabel)
			);
		}
		elseif (array_key_exists($id, $this->arCustomLabels))
		{
			return (
				isset($this->arFields[$id]['required']) && $this->arFields[$id]['required']
					? '<span class="adm-required-field">'
						. htmlspecialcharsex($this->arCustomLabels[$id])
						. ($bColumnNeeded ? ":" : "")
						. '</span>'
					: htmlspecialcharsex($this->arCustomLabels[$id]) . ($bColumnNeeded ? ":" : "")
			);
		}
		else
		{
			return (
				isset($this->tabs[$this->tabIndex]["FIELDS"][$id]["required"]) && $this->tabs[$this->tabIndex]["FIELDS"][$id]["required"]
					? '<span class="adm-required-field">' . htmlspecialcharsex($content) . '</span>'
					: htmlspecialcharsex($content)
			);
		}
	}

	function ShowWarnings($form, $messages, $aFields=false)
	{
		parent::ShowWarnings($this->name.'_form', $messages, $aFields);
	}

	function BeginPrologContent()
	{
		ob_start();
	}

	function EndPrologContent()
	{
		$this->sPrologContent = ob_get_contents();
		ob_end_clean();
	}

	function BeginEpilogContent()
	{
		ob_start();
	}

	function EndEpilogContent()
	{
		$this->sEpilogContent = ob_get_contents();
		ob_end_clean();
	}

	function AddFieldGroup($id, $content, $arFields, $bAjax = false)
	{
		$this->group = $id;
		$this->group_ajax = $bAjax;
		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"content" => $content,
			"group" => $arFields,
			"html" => '<td colspan="2">'.$this->GetCustomLabelHTML($id, $content).'</td>',
		);
	}

	function HideField($id)
	{
		$this->arForbiddenFields[$id] = true;
	}

	function AddSection($id, $content, $required = false)
	{
		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"delimiter" => true,
			"content" => $content,
			"html" => '<td colspan="2">'.($required? '<span class="adm-required-field">'.$this->GetCustomLabelHTML($id, $content).'</span>': $this->GetCustomLabelHTML($id, $content)).'</td>',
		);
	}

	function AddViewField($id, $content, $html, $required=false)
	{
		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $content,
			"html" => ($html <> ''? '<td width="40%">'.$this->GetCustomLabelHTML($id, $content).'</td><td>'.$html.'</td>' : ''),
		);
	}

	function AddDropDownField($id, $content, $required, $arSelect, $value=false, $arParams=array())
	{
		if($value === false)
			$value = $this->arFieldValues[$id];

		$html = '<select name="'.$id.'"';
		foreach($arParams as $param)
			$html .= ' '.$param;
		$html .= '>';

		foreach($arSelect as $key => $val)
			$html .= '<option value="'.htmlspecialcharsbx($key).'"'.($value == $key? ' selected': '').'>'.htmlspecialcharsex($val).'</option>';
		$html .= '</select>';

		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $content,
			"html" => '<td width="40%">'.($required? '<span class="adm-required-field">'.$this->GetCustomLabelHTML($id, $content).'</span>': $this->GetCustomLabelHTML($id, $content)).'</td><td>'.$html.'</td>',
			"hidden" => '<input type="hidden" name="'.$id.'" value="'.htmlspecialcharsbx($value).'">',
		);
	}

	function AddEditField($id, $content, $required, $arParams = array(), $value = false)
	{
		$arParams['id'] = (string)($arParams['id'] ?? '');
		$arParams['size'] = (int)($arParams['size'] ?? 0);
		$arParams['maxlength'] = (int)($arParams['maxlength'] ?? 0);
		if($value === false)
			$value = htmlspecialcharsbx($this->arFieldValues[$id]);
		else
			$value = htmlspecialcharsbx(htmlspecialcharsback($value));

		$html = '<input type="text" name="'.$id.'" value="'.$value.'"';
		if ($arParams['size'] > 0)
		{
			$html .= ' size="' . $arParams['size'] . '"';
		}
		if ($arParams['maxlength'] > 0)
		{
			$html .= ' maxlength="' . $arParams['maxlength'] . '"';
		}
		if ($arParams["id"] !== '')
		{
			$html .= ' id="' . htmlspecialcharsbx($arParams["id"]) . '"';
		}
		$html .= '>';

		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $content,
			"html" => '<td width="40%">'.($required? '<span class="adm-required-field">'.$this->GetCustomLabelHTML($id, $content).'</span>': $this->GetCustomLabelHTML($id, $content)).'</td><td>'.$html.'</td>',
			"hidden" => '<input type="hidden" name="'.$id.'" value="'.$value.'">',
		);
	}

	function AddTextField($id, $label, $value, $arParams=array(), $required=false)
	{
		$value = htmlspecialcharsbx(htmlspecialcharsback($value));

		$html = '<textarea name="'.$id.'"';
		if(intval($arParams["cols"]) > 0)
			$html .= ' cols="'.intval($arParams["cols"]).'"';
		if(intval($arParams["rows"]) > 0)
			$html .= ' rows="'.intval($arParams["rows"]).'"';
		$html .= '>'.$value.'</textarea>';

		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $label,
			"html" => '<td width="40%">'.($required? '<span class="adm-required-field">'.$this->GetCustomLabelHTML($id, $label).'</span>': $this->GetCustomLabelHTML($id, $label)).'</td><td>'.$html.'</td>',
			"hidden" => '<input type="hidden" name="'.$id.'" value="'.$value.'">',
			"valign" => "top",
		);
	}

	function AddCalendarField($id, $label, $value, $required=false)
	{
		$html = CalendarDate($id, $value, $this->GetFormName());

		$value = htmlspecialcharsbx(htmlspecialcharsback($value));

		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $label,
			"html" => '<td width="40%">'.($required? '<span class="adm-required-field">'.$this->GetCustomLabelHTML($id, $label).'</span>': $this->GetCustomLabelHTML($id, $label)).'</td><td>'.$html.'</td>',
			"hidden" => '<input type="hidden" name="'.$id.'" value="'.$value.'">',
		);
	}

	function AddCheckBoxField($id, $content, $required, $value, $checked, $arParams=array())
	{
		if (is_array($value))
		{
			$html = '<input type="hidden" name="'.$id.'" value="'.htmlspecialcharsbx($value[1]).'">
				<input type="checkbox" name="'.$id.'" value="'.htmlspecialcharsbx($value[0]).'"'.($checked? ' checked': '');
			$hidden = '<input type="hidden" name="'.$id.'" value="'.htmlspecialcharsbx($checked? $value[0]: $value[1]).'">';
		}
		else
		{
			$html = '<input type="checkbox" name="'.$id.'" value="'.htmlspecialcharsbx($value).'"'.($checked? ' checked': '');
			$hidden = '<input type="hidden" name="'.$id.'" value="'.htmlspecialcharsbx($value).'">';
		}

		foreach($arParams as $param)
			$html .= ' '.$param;
		$html .= '>';

		$label = $this->GetCustomLabelHTML($id, $content);
		if ($required)
		{
			$label = '<span class="adm-required-field">'.$label.'</span>';
		}

		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $content,
			"html" => '<td width="40%">'.$label.'</td><td>'.$html.'</td>',
			"hidden" => $hidden,
		);
	}

	function AddFileField($id, $label, $value, $arParams=array(), $required=false)
	{
		$arDefParams = array("iMaxW"=>150, "iMaxH"=>150, "sParams"=>"border=0", "strImageUrl"=>"", "bPopup"=>true, "sPopupTitle"=>false);
		foreach($arDefParams as $key=>$val)
			if(!array_key_exists($key, $arParams))
				$arParams[$key] = $val;

		$html = CFile::InputFile($id, 20, $value);
		if($value <> '')
			$html .= '<div class="adm-detail-file-image">'.CFile::ShowImage($value, $arParams["iMaxW"], $arParams["iMaxH"], $arParams["sParams"], $arParams["strImageUrl"], $arParams["bPopup"], $arParams["sPopupTitle"])."</div>";

		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $label,
			"html" => '<td width="40%">'.($required? '<span class="adm-required-field">'.$this->GetCustomLabelHTML($id, $label).'</span>': $this->GetCustomLabelHTML($id, $label)).'</td><td>'.$html.'</td>',
			"valign" => "top",
			"rowClass" => "adm-detail-file-row"
		);
	}

	function BeginCustomField($id, $content, $required = false)
	{
		$this->sCurrentLabel = $this->GetCustomLabel($id, $content);
		$this->bCurrentReq = $required;
		$this->tabs[$this->tabIndex]["FIELDS"][$id] = array(
			"id" => $id,
			"required" => $required,
			"content" => $content,
		);

		ob_start();
	}

	function EndCustomField($id, $hidden = "")
	{
		$html = ob_get_contents();
		ob_end_clean();

		$this->tabs[$this->tabIndex]["FIELDS"][$id]["custom_html"] = $html;
		$this->tabs[$this->tabIndex]["FIELDS"][$id]["hidden"] = $hidden;
	}

	function ShowUserFields($PROPERTY_ID, $ID, $bVarsFromForm)
	{
		/**
		 * @global CMain $APPLICATION
		 * @global CUserTypeManager $USER_FIELD_MANAGER
		 */
		global $USER_FIELD_MANAGER, $APPLICATION;

		if($USER_FIELD_MANAGER->GetRights($PROPERTY_ID) >= "W")
		{
			$this->BeginCustomField("USER_FIELDS_ADD", GetMessage("admin_lib_add_user_field"));
			?>
				<tr>
					<td colspan="2" align="left">
						<a href="/bitrix/admin/userfield_edit.php?lang=<?echo LANGUAGE_ID?>&amp;ENTITY_ID=<?echo urlencode($PROPERTY_ID)?>&amp;back_url=<?echo urlencode($APPLICATION->GetCurPageParam($this->name.'_active_tab=user_fields_tab', array($this->name.'_active_tab')))?>"><?echo $this->GetCustomLabelHTML()?></a>
					</td>
				</tr>
			<?
			$this->EndCustomField("USER_FIELDS_ADD", '');
		}

		$arUserFields = $USER_FIELD_MANAGER->GetUserFields($PROPERTY_ID, $ID, LANGUAGE_ID);
		foreach($arUserFields as $FIELD_NAME => $arUserField)
		{
			$arUserField["VALUE_ID"] = intval($ID);
			if(array_key_exists($FIELD_NAME, $this->arCustomLabels))
				$strLabel = $this->arCustomLabels[$FIELD_NAME];
			else
				$strLabel = $arUserField["EDIT_FORM_LABEL"]? $arUserField["EDIT_FORM_LABEL"]: $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = $strLabel;

			$this->BeginCustomField($FIELD_NAME, $strLabel, $arUserField["MANDATORY"]=="Y");

			if(isset($_REQUEST['def_'.$FIELD_NAME]))
				$arUserField['SETTINGS']['DEFAULT_VALUE'] = $_REQUEST['def_'.$FIELD_NAME];

			echo $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME] ?? '', $arUserField);

			$form_value = $GLOBALS[$FIELD_NAME] ?? '';
			if(!$bVarsFromForm)
				$form_value = $arUserField["VALUE"];
			elseif($arUserField["USER_TYPE"]["BASE_TYPE"]=="file")
				$form_value = $GLOBALS[$arUserField["FIELD_NAME"]."_old_id"];

			$hidden = "";
			if(is_array($form_value))
			{
				foreach($form_value as $value)
					$hidden .= '<input type="hidden" name="'.$FIELD_NAME.'[]" value="'.htmlspecialcharsbx($value).'">';
			}
			else
			{
				$hidden .= '<input type="hidden" name="'.$FIELD_NAME.'" value="'.htmlspecialcharsbx($form_value).'">';
			}
			$this->EndCustomField($FIELD_NAME, $hidden);
		}
	}

	function ShowUserFieldsWithReadyData($PROPERTY_ID, $readyData, $bVarsFromForm, $primaryIdName = 'VALUE_ID')
	{
		/**
		 * @global CMain $APPLICATION
		 * @global CUserTypeManager $USER_FIELD_MANAGER
		 */
		global $USER_FIELD_MANAGER, $APPLICATION;

		if($USER_FIELD_MANAGER->GetRights($PROPERTY_ID) >= "W")
		{
			$this->BeginCustomField("USER_FIELDS_ADD", GetMessage("admin_lib_add_user_field"));
			?>
			<tr>
				<td colspan="2" align="left">
					<a href="/bitrix/admin/userfield_edit.php?lang=<?echo LANGUAGE_ID?>&amp;ENTITY_ID=<?echo urlencode($PROPERTY_ID)?>&amp;back_url=<?echo urlencode($APPLICATION->GetCurPageParam($this->name.'_active_tab=user_fields_tab', array($this->name.'_active_tab')))?>"><?echo $this->GetCustomLabelHTML()?></a>
				</td>
			</tr>
			<?
			$this->EndCustomField("USER_FIELDS_ADD", '');
		}

		$arUserFields = $USER_FIELD_MANAGER->getUserFieldsWithReadyData($PROPERTY_ID, $readyData, LANGUAGE_ID, false, $primaryIdName);

		foreach($arUserFields as $FIELD_NAME => $arUserField)
		{
			$arUserField["VALUE_ID"] = (int)($readyData[$primaryIdName] ?? null);
			if(array_key_exists($FIELD_NAME, $this->arCustomLabels))
				$strLabel = $this->arCustomLabels[$FIELD_NAME];
			else
				$strLabel = $arUserField["EDIT_FORM_LABEL"]? $arUserField["EDIT_FORM_LABEL"]: $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = $strLabel;

			$this->BeginCustomField($FIELD_NAME, $strLabel, $arUserField["MANDATORY"]=="Y");

			if(isset($_REQUEST['def_'.$FIELD_NAME]))
				$arUserField['SETTINGS']['DEFAULT_VALUE'] = $_REQUEST['def_'.$FIELD_NAME];

			echo $USER_FIELD_MANAGER->GetEditFormHTML(
				$bVarsFromForm,
				$GLOBALS[$FIELD_NAME] ?? null,
				$arUserField
			);

			$form_value = $GLOBALS[$FIELD_NAME] ?? null;
			if(!$bVarsFromForm)
				$form_value = $arUserField["VALUE"];
			elseif($arUserField["USER_TYPE"]["BASE_TYPE"]=="file")
				$form_value = $GLOBALS[$arUserField["FIELD_NAME"]."_old_id"];

			$hidden = "";
			if(is_array($form_value))
			{
				foreach($form_value as $value)
					$hidden .= '<input type="hidden" name="'.$FIELD_NAME.'[]" value="'.htmlspecialcharsbx($value).'">';
			}
			else
			{
				$hidden .= '<input type="hidden" name="'.$FIELD_NAME.'" value="'.htmlspecialcharsbx($form_value).'">';
			}
			$this->EndCustomField($FIELD_NAME, $hidden);
		}
	}

	function Buttons($aParams=false, $additional_html="")
	{
		if($aParams === false)
			$this->arButtonsParams = false;
		else
			$this->arButtonsParams = $aParams;
		$this->sButtonsContent = $additional_html;

		while($this->tabIndex < count($this->tabs))
			$this->BeginNextFormTab();
	}

	/**
	 * @param bool|array $arJSButtons
	 * @return void
	 */
	function ButtonsPublic($arJSButtons = false)
	{
		if ($this->bPublicMode)
		{
			if (!empty($_REQUEST['from_module']))
			{
				$this->sButtonsContent .= '<input type="hidden" name="from_module" value="'.htmlspecialcharsbx($_REQUEST['from_module']).'" />';
			}

			ob_start();
			if ($arJSButtons === false)
			{
				echo '
<input type="hidden" name="bxpublic" value="Y" /><input type="hidden" name="save" value="Y" />
<script>'.$this->publicObject.'.SetButtons(['.$this->publicObject.'.btnSave, '.$this->publicObject.'.btnCancel]);</script>
';
			}
			elseif (is_array($arJSButtons))
			{
				$arJSButtons = array_values($arJSButtons);
				echo '
<input type="hidden" name="bxpublic" value="Y" />
<script>'.$this->publicObject.'.SetButtons([
';
				foreach ($arJSButtons as $key => $btn)
				{
					if (str_starts_with($btn, '.'))
						$btn = $this->publicObject.$btn;
					echo $key ? ',' : '', $btn, "\r\n"; // NO JSESCAPE HERE! string must contain valid js object
				}
				echo '
]);</script>
';
			}
			$this->sButtonsContent .= ob_get_clean();
		}
	}
}

class CAdminFormSettings
{
	public static function getTabsArray($formId)
	{
		$arCustomTabs = array();
		$customTabs = CUserOptions::GetOption("form", $formId);
		if($customTabs && $customTabs["tabs"])
		{
			$arTabs = explode("--;--", $customTabs["tabs"]);
			foreach($arTabs as $customFields)
			{
				if ($customFields == "")
					continue;

				$arCustomFields = explode("--,--", $customFields);
				$arCustomTabID = "";
				foreach($arCustomFields as $customField)
				{
					if($arCustomTabID == "")
					{
						list($arCustomTabID, $arCustomTabName) = explode("--#--", $customField);
						$arCustomTabs[$arCustomTabID] = array(
							"TAB" => $arCustomTabName,
							"FIELDS" => array(),
						);
					}
					else
					{
						list($arCustomFieldID, $arCustomFieldName) = explode("--#--", $customField);
						$arCustomFieldName = ltrim($arCustomFieldName, "* -\xa0\xc2");
						$arCustomTabs[$arCustomTabID]["FIELDS"][$arCustomFieldID] = $arCustomFieldName;
					}
				}
			}
		}
		return $arCustomTabs;
	}

	public static function setTabsArray($formId, $arCustomTabs, $common = false, $userID = false)
	{
		$option = "";
		if (is_array($arCustomTabs))
		{
			foreach($arCustomTabs as $arCustomTabID => $arTab)
			{
				if (is_array($arTab) && isset($arTab["TAB"]))
				{
					$option .= $arCustomTabID.'--#--'.$arTab["TAB"];
					if (isset($arTab["FIELDS"]) && is_array($arTab["FIELDS"]))
					{
						foreach ($arTab["FIELDS"] as $arCustomFieldID => $arCustomFieldName)
						{
							$option .= '--,--'.$arCustomFieldID.'--#--'.$arCustomFieldName;
						}
					}
				}
				$option .= '--;--';
			}
		}
		CUserOptions::SetOption("form", $formId, array("tabs" => $option), $common, $userID);
	}
}
