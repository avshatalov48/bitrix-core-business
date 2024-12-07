<?
/**
 * Form output class - templates management & final output
 *
 */
class CAllFormOutput extends CFormOutput_old
{
	var $__cache_path = "";
	var $__cache_file_header = "<?if(!defined(\"B_PROLOG_INCLUDED\") || B_PROLOG_INCLUDED!==true)die();?><?=\$FORM->ShowFormHeader();?>";
	var $__cache_file_footer = "<?=\$FORM->ShowFormFooter();?>";

	var $__admin;

	var $WEB_FORM_ID;
	var $WEB_FORM_NAME;

	var $arParams;
	var $arForm;
	var $arQuestions;
	var $arAnswers;
	var $arDropDown;
	var $arMultiSelect;

	var $arrRESULT_PERMISSION = array();

	var $arrVALUES;

	var $RESULT_ID;
	var $arResult;

	var $strFormNote;

	var $F_RIGHT;
	var $CAPTCHACode;

	var $bSimple;

	var $__error_msg = "";
	var $__form_validate_errors = "";
	var $__cache_file_name;

	var $__form_image_cache = "";
	var $__form_image_path_cache = "";
	var $__form_input_caption_image_cache = array();
	var $__form_input_caption_image_path_cache = array();

	var $comp2 = false;

	var $bIsFormValidateErrors = false;

	public function __construct()
	{
		$this->__cache_path = BX_PERSONAL_ROOT."/tmp/form";
	}

	function InitializeTemplate($arParams, $arResult)
	{
		$this->WEB_FORM_ID = $arParams["WEB_FORM_ID"];
		$this->RESULT_ID = $arParams["RESULT_ID"] ?? 0;

		$this->arParams 	= $arParams;
		$this->arForm 		= $arResult["arForm"];
		$this->arQuestions 	= $arResult["arQuestions"];
		$this->arAnswers 	= $arResult["arAnswers"];
		$this->arDropDown 	= $arResult["arDropDown"];
		$this->arMultiSelect = $arResult["arMultiSelect"];

		$this->arrVALUES = $arResult["arrVALUES"];

		$this->F_RIGHT = $arResult["F_RIGHT"];
		if ($this->RESULT_ID)
		{
			if ($this->isAccessFormResult($arResult['arResultData']))
			{
				$this->arrRESULT_PERMISSION = CFormResult::GetPermissions($this->RESULT_ID);
				$this->arResult = $arResult['arResultData'];
			}
		}

		$this->strFormNote = $arResult["FORM_NOTE"] ?? '';
		$this->__form_validate_errors = $arResult["FORM_ERRORS"] ?? '';
		$this->bIsFormValidateErrors = $arResult['isFormErrors'] == 'Y';

		$this->bSimple = (COption::GetOptionString("form", "SIMPLE", "Y") == "Y") ? true : false;

		$this->WEB_FORM_NAME = $arResult["arForm"]["SID"];

		if ($this->arForm["USE_CAPTCHA"] == "Y")
		{
			$this->CAPTCHACode = $arResult["CAPTCHACode"];
		}
	}

	function IncludeFormCustomTemplate()
	{
		if ($this->__check_form_cache())
		{
			$FORM =& $this; // create interface for template
			ob_start();
			eval('?>'.$this->__cache_tpl.'<?');
			$strReturn = ob_get_contents();
			ob_end_clean();

			return $strReturn;
		}
		else
		{
			return false;
		}
	}

	function IncludeFormTemplate()
	{
		global $APPLICATION;
		if ($this->__check_form_cache())
		{
			$APPLICATION->SetTemplateCSS("form/form.css");
			$FORM =& $this;
			eval($this->__cache_tpl);

			return true;
		}
		else
		{
			return false;
		}
	}

	function isStatisticIncluded()
	{
		return CModule::IncludeModule("statistic");
	}

	/**
	 * Private method used to check out for template and template cache file
	 * Returns true whether tpl file exists and puts its path to private
	 * property __cache_file_name. Otherwise returns false
	 *
	 * @return bool
	 */
	function __check_form_cache()
	{
		global $CACHE_MANAGER;

		// if no tpl at all - return false
		if ($this->arForm["FORM_TEMPLATE"] == '' || $this->arForm["USE_DEFAULT_TEMPLATE"] != "N")
		{
			$this->arForm["USE_DEFAULT_TEMPLATE"] = "Y";
			return false;
		}

		$this->__cache_tpl = '';

		$cache_dir = '/form/templates/'.$this->arForm['ID'];
		$cache_id = 'form|template|'.$this->arForm['ID'];

		$obCache = new CPHPCache();

		if ($obCache->InitCache(30*86400, $cache_id, $cache_dir))
		{
			$res = $obCache->GetVars();
			$this->__cache_tpl = $res['FORM_TEMPLATE'];
		}
		else
		{
			$obCache->StartDataCache();

			$CACHE_MANAGER->StartTagCache($cache_dir);

			$CACHE_MANAGER->RegisterTag('forms');
			$CACHE_MANAGER->RegisterTag('form_'.$this->arForm['ID']);

			$this->__cache_tpl = $res['FORM_TEMPLATE'] = $this->__cache_file_header.$this->arForm['FORM_TEMPLATE'].$this->__cache_file_footer;

			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache(array('FORM_TEMPLATE' => $this->__cache_tpl));
		}

		return true;
	}

	/*
	function __clear_form_cache_files()
	{
		$path = $_SERVER['DOCUMENT_ROOT'].$this->__cache_path;
		$fname_mask = "form_".$this->WEB_FORM_ID;

		if ($dh = @opendir($path))
		{
			while (($fname = @readdir($dh)) !== false)
			{
				if (substr($fname, 0, strlen($fname_mask)) == $fname_mask) @unlink($path."/".$fname);
			}
			closedir($dh);
		}
	}
	*/

	/**
	 * Public method used to check whether there were some form validation errors
	 * Use: <?if($FORM->isFormErrors()):?>There're some errors!<?endif?>
	 *
	 * @return bool
	 */
	function isFormErrors()
	{
		if (is_array($this->__form_validate_errors))
			return count($this->__form_validate_errors) > 0;
		else
			return $this->__form_validate_errors <> '';
	}

	/**
	 * Public method used to show formatted form errors
	 * Use: <?=$FORM->ShowFormErrors()?>
	 *
	 * @return string
	 */
	function ShowFormErrors()
	{
		ob_start();

		if ($this->arParams['USE_EXTENDED_ERRORS'] == 'N')
			ShowError($this->__form_validate_errors);
		elseif (is_array($this->__form_validate_errors))
			ShowError(implode('<br />', $this->__form_validate_errors));

		$ret = ob_get_contents();
		ob_end_clean();

		return $ret;
	}

	/**
	 * Public method used to show unformatted form errors
	 * Use: <font color="red"><?=$FORM->ShowFormErrorsText()?></font>
	 *
	 * @return string
	 */
	function ShowFormErrorsText()
	{
		if ($this->arParams['USE_EXTENDED_ERRORS'] == 'N')
			return $this->__form_validate_errors;
		else
			return implode('<br />', $this->__form_validate_errors);
	}

	/**
	 * Public: shows form note formatted string if any (like 'Changes saved')
	 *
	 * @return string
	 */
	function ShowFormNote()
	{
		ob_start();
		ShowNote($this->strFormNote);
		$ob = ob_get_contents();
		ob_end_clean();
		return $ob;
	}

	/**
	 * Public: shows form note unformatted string if any (like 'Changes saved')
	 *
	 * @return string
	 */
	function ShowFormNoteText()
	{
		return $this->strFormNote;
	}

	/**
	 * Public: check whether form has note string (like 'Changes saved')
	 *
	 * @return bool
	 */
	function isFormNote()
	{
		return $this->strFormNote <> '';
	}

	/**
	 * Get current form runtime error code string
	 * use $MESS from lang file to customize error messages
	 *
	 * @return string
	 */
	function ShowErrorMsg()
	{
		return $this->__error_msg;
	}

	/**
	 * Public method used to put form header (<form> tag and hidden fields)
	 * Added to form template automatically
	 *
	 * @return string
	 */
	function ShowFormHeader()
	{
		global $APPLICATION;

		$res = sprintf(
			"<form name=\"%s\" action=\"%s\" method=\"%s\" enctype=\"multipart/form-data\">",
			$this->arForm["SID"],
			//$APPLICATION->GetCurPage(),
			POST_FORM_ACTION_URI,
			"POST"
		);

		$res .= bitrix_sessid_post();

		$arHiddenInputs["WEB_FORM_ID"] = $this->WEB_FORM_ID;
		if (!empty($this->RESULT_ID)) $arHiddenInputs["RESULT_ID"] = $this->RESULT_ID;
		$arHiddenInputs["lang"] = LANGUAGE_ID;

		foreach ($arHiddenInputs as $name => $value)
		{
			$res .= sprintf(
			"<input type=\"hidden\" name=\"%s\" value=\"%s\" />",
			$name, $value
			);
		}

		return $res;
	}

	/**
	 * Public method used to put form footer (end <form> tag)
	 * Added to form template automatically
	 *
	 * @return string
	 */
	function ShowFormFooter()
	{
		return "</form>";
	}

	function __admin_GetInputType($FIELD_SID)
	{
		if (is_array($this->arAnswers[$FIELD_SID]))
		{
			$type = "";
			foreach ($this->arAnswers[$FIELD_SID] as $key=>$arAnswer)
			{
				if ($type == "")
				{
					$type = $arAnswer["FIELD_TYPE"];
				}
				elseif ($type != $arAnswer["FIELD_TYPE"])
					return "multiple";
			}

			return $type;
		}
		else return "none";
	}

	function __admin_GetInputAnswersStructure($FIELD_SID)
	{
		if (is_array($this->arAnswers[$FIELD_SID]))
		{
			$out = array();
			$csort_max = 0;
			foreach ($this->arAnswers[$FIELD_SID] as $key => $arAnswer)
			{
				$last = $arAnswer;
				if ($csort_max < $arAnswer["C_SORT"]) $csort_max = $arAnswer["C_SORT"];
				$ans = array();
				foreach ($arAnswer as $key=>$value)
				{
					$ans[] = $key.":'".CUtil::JSEscape($value)."'";
				}

				$ans[] = "ANS_NEW:false";

				$out[] = "{".implode(",", $ans)."}";
			}

			$imax = 0;
			if (in_array($last['FIELD_TYPE'], array('checkbox', 'dropdown', 'multiselect', 'radio'))) $imax = 5;
			for ($i=0; $i<$imax; $i++)
			{
				$ans = array();
				$csort_max += 100;

				foreach ($last as $key=>$value)
				{
					if ($key == "ACTIVE")
						$ans[] = $key.":'Y'";
					elseif ($key == "C_SORT")
						$ans[] = $key.":'".$csort_max."'";
					else
						$ans[] = $key.":'".(in_array($key, array('FIELD_TYPE', 'FIELD_ID', 'QUESTION_ID')) ? CUtil::JSEscape($value) : "")."'";
				}

				$ans[] = "ANS_NEW:true";

				$out[] = "{".implode(",", $ans)."}";
			}

			return "[".implode(",", $out)."]";
		}
		else
			return "[]";
	}

	/**
	 * Public method used to put input field title to template
	 * Use: <?=$FORM->ShowInputCaption('MYFIELD_5')?>
	 *
	 * @param string $FIELD_SID
	 * @param string $caption_css_class
	 * @return string
	 */
	function ShowInputCaption($FIELD_SID, $css_style = "")
	{
		$ret = "";
		if (empty($this->arQuestions[$FIELD_SID])) $ret = "";
		else
		{
			if ($this->arQuestions[$FIELD_SID]["TITLE_TYPE"]=="html")
			{
				$ret = $this->arQuestions[$FIELD_SID]["TITLE"].CForm::ShowRequired($this->arQuestions[$FIELD_SID]["REQUIRED"]);
			}
			else
			{
				if ($this->arQuestions[$FIELD_SID]["ADDITIONAL"]=="Y")
				{
					$ret = "<b>".$this->arQuestions[$FIELD_SID]["TITLE"]."</b>".CForm::ShowRequired($this->arQuestions[$FIELD_SID]["REQUIRED"]);
				}
				else
				{
					$ret = htmlspecialcharsbx($this->arQuestions[$FIELD_SID]["TITLE"]).CForm::ShowRequired($this->arQuestions[$FIELD_SID]["REQUIRED"]);
				}
			}
		}

		if ($css_style <> '') $ret = "<span class=\"".$css_style."\">".$ret."</span>";

		if (is_array($this->__form_validate_errors) && array_key_exists($FIELD_SID, $this->__form_validate_errors))
			$ret = '<span class="form-error-fld" title="'.htmlspecialcharsbx($this->__form_validate_errors[$FIELD_SID]).'"></span>'."\r\n".$ret;

		return $ret;
	}


	function __admin_ShowInputCaption($FIELD_SID, $caption_css_class = "", $unform = false)
	{
		if (empty($this->arQuestions[$FIELD_SID])) return "";
		if ($unform) return $this->arQuestions[$FIELD_SID]["TITLE"];
		if ($this->arQuestions[$FIELD_SID]["TITLE_TYPE"]=="html")
		{
			return $this->arQuestions[$FIELD_SID]["TITLE"]. CForm::ShowRequired($this->arQuestions[$FIELD_SID]["REQUIRED"]);
		}
		else
		{
			if ($this->arQuestions[$FIELD_SID]["ADDITIONAL"]=="Y")
			{
				return "<span class=\"".$caption_css_class."\"><b>".$this->arQuestions[$FIELD_SID]["TITLE"]."</b></span>".CForm::ShowRequired($this->arQuestions[$FIELD_SID]["REQUIRED"]);
			}
			else
			{
				return "<span class=\"".$caption_css_class."\">".$this->arQuestions[$FIELD_SID]["TITLE"]."</span>". CForm::ShowRequired($this->arQuestions[$FIELD_SID]["REQUIRED"]);
			}
		}
	}


	/**
	 * Public method used to put question image if exists onto form
	 * Use: <?=$FORM->ShowInputCaptionImage('MYFIELD_5', 50, 50, "hspace=\"0\" vspace=\"0\" align=\"left\" border=\"0\"", "", true, GetMessage("FORM_ENLARGE"))?>
	 * params like CFile::ShowImage()
	 * Returns image code if image exists and empty string otherwise
	 *
	 * @param string $FIELD_SID
	 * @param int $iMaxW
	 * @param int $iMaxH
	 * @param string $sParams
	 * @param string $strImageUrl
	 * @param bool $bPopup
	 * @param string $strPopupTitle

	 * @return string
	 */
	function ShowInputCaptionImage($FIELD_SID, $sAlign = "", $iMaxW="", $iMaxH="", $bPopup="N", $strPopupTitle="", $sHSpace = "", $sVSpace = "", $sBorder = "")
	{
		if ($this->isInputCaptionImage($FIELD_SID))
		{
			$arImageParams = array();

			if ($sAlign <> '') $arImageParams[] = sprintf("align=\"%s\"", $sAlign);
			if ($sHSpace <> '') $arImageParams[] = sprintf("hspace=\"%s\"", $sHSpace);
			if ($sVSpace <> '') $arImageParams[] = sprintf("vspace=\"%s\"", $sVSpace);
			if ($sBorder <> '') $arImageParams[] = sprintf("border=\"%s\"", $sBorder);
			else $arImageParams[] = "border=\"0\"";

			if ($strPopupTitle == '') $strPopupTitle = false;

			if (empty($this->__form_input_caption_image_cache[$FIELD_SID]))
			{
				$this->__form_input_caption_image_cache[$FIELD_SID] = CFile::ShowImage($this->arQuestions[$FIELD_SID]["IMAGE_ID"], $iMaxW, $iMaxH, implode(" ", $arImageParams), $strImageUrl, $bPopup == "Y", $strPopupTitle);
			}

			$ret = $this->__form_input_caption_image_cache[$FIELD_SID];

			if (mb_strtoupper($sAlign) == "CENTER") $ret = "<div align=\"center\">".$ret."</div>";

			return $ret;
		}
		else
		{
			return "";
		}
	}

	/**
	 * Public method used to check wheter current question has image
	 * Use: <?=($FORM->isInputCaptionImage('MYFIELD_5') ? "image: ".$FORM->ShowInputCaptionImage('MYFIELD_5') : "no image")?>
	 *
	 * @param string $FIELD_SID
	 * @return bool
	 */
	function isInputCaptionImage($FIELD_SID)
	{
		return intval($this->arQuestions[$FIELD_SID]["IMAGE_ID"])>0;
	}

	/**
	 * Public method used to put input fields to template
	 * Use: <?=$FORM->ShowInput('MYFIELD_5')?>
	 *
	 * @param string $FIELD_SID
	 * @param string $caption_css_class
	 * @return string
	 */
	function ShowInput($FIELD_SID, $caption_css_class = '')
	{
		$arrVALUES = $this->arrVALUES;

		if (is_array($this->arAnswers[$FIELD_SID]))
		{
			$res = "";

			reset($this->arAnswers[$FIELD_SID]);
			if (isset($this->arDropDown[$FIELD_SID]) && is_array($this->arDropDown[$FIELD_SID]))
				reset($this->arDropDown[$FIELD_SID]);
			if (isset($this->arMutiSelect[$FIELD_SID]) && is_array($this->arMutiSelect[$FIELD_SID]))
				reset($this->arMutiSelect[$FIELD_SID]);

			foreach ($this->arAnswers[$FIELD_SID] as $key => $arAnswer)
			{
				if ($arAnswer["FIELD_TYPE"]=="dropdown" && $show_dropdown=="Y")
					continue;
				if ($arAnswer["FIELD_TYPE"]=="multiselect" && $show_multiselect=="Y")
					continue;

				if ($key > 0)
					$res .= "<br />";

				switch ($arAnswer["FIELD_TYPE"])
				{
					case "radio":
						$ans_id = "form_checkbox_".$FIELD_SID."_".$arAnswer['ID'];
						$arAnswer['FIELD_PARAM'] .= ' id="'.$ans_id.'"';

						$value = CForm::GetRadioValue($FIELD_SID, $arAnswer, $arrVALUES);
						$input = CForm::GetRadioField(
							$FIELD_SID,
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_PARAM"]
						);

						if ($ans_id <> '')
						{
							$res .= $input;
							$res .= "<label for=\"".$ans_id."\">";
							$res .= "<span class=\"".$caption_css_class."\">&nbsp;".$arAnswer["MESSAGE"]."</span></label>";
						}
						else
						{
							$res .= "<label>";
							$res .= $input;
							$res .= "<span class=\"".$caption_css_class."\">&nbsp;".$arAnswer["MESSAGE"]."</span></label>";
						}

						break;
					case "checkbox":

						$ans_id = "form_checkbox_".$FIELD_SID."_".$arAnswer['ID'];
						$arAnswer['FIELD_PARAM'] .= ' id="'.$ans_id.'"';

						$value = CForm::GetCheckBoxValue($FIELD_SID, $arAnswer, $arrVALUES);
						$input = CForm::GetCheckBoxField(
							$FIELD_SID,
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_PARAM"]
						);

						if ($ans_id <> '')
						{
							$res .= $input;
							$res .= "<label for=\"".$ans_id."\">";
							$res .= "<span class=\"".$caption_css_class."\">&nbsp;".$arAnswer["MESSAGE"]."</span></label>";
						}
						else
						{
							$res .= "<label>";
							$res .= $input;
							$res .= "<span class=\"".$caption_css_class."\">&nbsp;".$arAnswer["MESSAGE"]."</span></label>";
						}

						break;
					case "dropdown":
						if ($show_dropdown!="Y")
						{
							$value = CForm::GetDropDownValue($FIELD_SID, $this->arDropDown, $arrVALUES);
							$res .= CForm::GetDropDownField(
								$FIELD_SID,
								$this->arDropDown[$FIELD_SID],
								$value,
								$arAnswer["FIELD_PARAM"]);
							$show_dropdown = "Y";
						}
						break;
					case "multiselect":
						if ($show_multiselect!="Y")
						{
							$value = CForm::GetMultiSelectValue($FIELD_SID, $this->arMultiSelect, $arrVALUES);
							$res .= CForm::GetMultiSelectField(
								$FIELD_SID,
								$this->arMultiSelect[$FIELD_SID],
								$value,
								$arAnswer["FIELD_HEIGHT"],
								$arAnswer["FIELD_PARAM"]);
							$show_multiselect = "Y";
						}
						break;
					case "text":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}

						$value = CForm::GetTextValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$res .= CForm::GetTextField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]);
						break;

					case "hidden":
						/*
						if (strlen(trim($arAnswer["MESSAGE"]))>0)
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}
						*/

						$value = CForm::GetHiddenValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$res .= CForm::GetHiddenField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_PARAM"]);
						break;

					case "password":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}
						$value = CForm::GetPasswordValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$res .= CForm::GetPasswordField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]);
						break;
					case "email":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}
						$value = CForm::GetEmailValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$res .= CForm::GetEmailField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]);
						break;
					case "url":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}
						$value = CForm::GetUrlValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$res .= CForm::GetUrlField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]);
						break;
					case "textarea":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}
						$value = CForm::GetTextAreaValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$res .= CForm::GetTextAreaField(
							$arAnswer["ID"],
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_HEIGHT"],
							$arAnswer["FIELD_PARAM"],
							$value
							);
						break;
					case "date":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]." (".CSite::GetDateFormat("SHORT").")</span><br />";
						}
						$value = CForm::GetDateValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$res .= CForm::GetDateField(
							$arAnswer["ID"],
							$this->arForm["SID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]);
						break;
					case "image":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}

						if ($this->RESULT_ID)
						{
							if ($arFile = CFormResult::GetFileByAnswerID($this->RESULT_ID, $arAnswer["ID"]))
							{
								if (intval($arFile["USER_FILE_ID"])>0)
								{
									if ($arFile["USER_FILE_IS_IMAGE"]=="Y")
									{
										$res .= CFile::ShowImage($arFile["USER_FILE_ID"], 0, 0, "border=0", "", true);
										$res .= "<br />";
									} //endif;
								} //endif;
							} // endif
						} // endif

						$res .= CForm::GetFileField(
							$arAnswer["ID"],
							$arAnswer["FIELD_WIDTH"],
							"IMAGE",
							0,
							"",
							$arAnswer["FIELD_PARAM"]);
						break;
					case "file":
						if (trim($arAnswer["MESSAGE"]) <> '')
						{
							$res .= "<span class=\"".$caption_css_class."\">".$arAnswer["MESSAGE"]."</span><br />";
						}

						if ($this->RESULT_ID)
						{
							if ($arFile = CFormResult::GetFileByAnswerID($this->RESULT_ID, $arAnswer["ID"]))
							{
								if (intval($arFile["USER_FILE_ID"])>0)
								{
									$res .= "<a title=\"".GetMessage("FORM_VIEW_FILE")."\" target=\"_blank\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$this->RESULT_ID."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."\">".htmlspecialcharsbx($arFile["USER_FILE_NAME"])."</a>&nbsp;(";
									$res .= CFile::FormatSize($arFile["USER_FILE_SIZE"]);
									$res .= ")&nbsp;&nbsp;[&nbsp;<a title=\"".str_replace("#FILE_NAME#", $arFile["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))."\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$this->RESULT_ID."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."&action=download\">".GetMessage("FORM_DOWNLOAD")."</a>&nbsp;]";
									$res .= "<br /><br />";
								} //endif;
							} //endif;
						}

						$res .= CForm::GetFileField(
							$arAnswer["ID"],
							$arAnswer["FIELD_WIDTH"],
							"FILE",
							0,
							"",
							$arAnswer["FIELD_PARAM"]);
						break;
				} //endswitch;
			} //endwhile;

			return $res;
		} //endif(is_array($arAnswers[$FIELD_SID]));
		elseif (is_array($this->arQuestions[$FIELD_SID]) && $this->arQuestions[$FIELD_SID]["ADDITIONAL"] == "Y")
		{
			$res = "";
			switch ($this->arQuestions[$FIELD_SID]["FIELD_TYPE"])
			{
				case "text":
					$value = CForm::GetTextAreaValue("ADDITIONAL_".$this->arQuestions[$FIELD_SID]["ID"], array(), $this->arrVALUES);
					$res .= CForm::GetTextAreaField(
						"ADDITIONAL_".$this->arQuestions[$FIELD_SID]["ID"],
						"60",
						"5",
						"",
						$value
						);
					break;
				case "integer":
					$value = CForm::GetTextValue("ADDITIONAL_".$this->arQuestions[$FIELD_SID]["ID"], array(), $this->arrVALUES);
					$res .= CForm::GetTextField(
						"ADDITIONAL_".$this->arQuestions[$FIELD_SID]["ID"],
						$value);
					break;
				case "date":
					$value = CForm::GetDateValue("ADDITIONAL_".$this->arQuestions[$FIELD_SID]["ID"], array(), $this->arrVALUES);
					$res .= CForm::GetDateField(
						"ADDITIONAL_".$this->arQuestions[$FIELD_SID]["ID"],
						$arForm["SID"],
						$value);
					break;
			} //endswitch;

			return $res;
		}
		else return "";
	}

	/**
	 * Public method used to check whether current form uses captcha.
	 * Use: <?if($FORM->isUseCaptcha()):?>form uses CAPTCHA<?else:?>form doesnt use CAPTCHA<?endif;?>
	 *
	 * @return bool
	 */
	function isUseCaptcha()
	{
		return $this->arForm["USE_CAPTCHA"] == "Y" && $this->CAPTCHACode <> '';
	}

	/**
	 * Public method used to put CAPTCHA image onto form.
	 * Use: <?=$FORM->ShowCaptchaImage()?>
	 *
	 * @return string
	 */
	function ShowCaptchaImage()
	{

		if ($this->isUseCaptcha())
			return "<input type=\"hidden\" name=\"captcha_sid\" value=\"".htmlspecialcharsbx($this->CAPTCHACode)."\" /><img src=\"/bitrix/tools/captcha.php?captcha_sid=".htmlspecialcharsbx($this->CAPTCHACode)."\" width=\"180\" height=\"40\" />";
		else return "";
	}

	/**
	 * Public method used to put CAPTCHA input field onto form.
	 * Use: <?=$FORM->ShowCaptchaField()?>
	 *
	 * @return string
	 */
	function ShowCaptchaField()
	{
		if ($this->isUseCaptcha())
			return "<input type=\"text\" name=\"captcha_word\" size=\"30\" maxlength=\"50\" value=\"\" class=\"inputtext\" />";
		else return "";
	}

	/**
	 * Public: show both CAPTCHA fields with default formating
	 *
	 * @return string
	 */
	function ShowCaptcha()
	{
		return $this->ShowCaptchaImage()."<br />".$this->ShowCaptchaField();
	}

	/**
	 * Public method used to put submit button onto form.
	 * Use: <?=$FORM->ShowSubmitButton()?>
	 *
	 * @return string
	 */
	function ShowSubmitButton($caption = "", $css_style = "")
	{
		$button_value = trim($caption) <> '' ? trim($caption) : (trim($this->arForm["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $this->arForm["BUTTON"]);

		return "<input ".(intval($this->F_RIGHT)<10 ? "disabled" : "")." type=\"submit\" name=\"web_form_submit\" value=\"".htmlspecialcharsbx($button_value)."\"".(!empty($css_style) ? " class=\"".$css_style."\"" : "")." />";
	}

	/**
	 * Public method used to put apply button onto form.
	 * Use: <?=$FORM->ShowApplyButton()?>
	 *
	 * @return string
	 */
	function ShowApplyButton($caption = "", $css_style = "")
	{
		$button_value = trim($caption) <> '' ? trim($caption) : GetMessage("FORM_APPLY");

		return "<input type=\"hidden\" name=\"web_form_apply\" value=\"Y\" /><input ".((intval($this->F_RIGHT)<10) ? "disabled" : "")." type=\"submit\" name=\"web_form_apply\" value=\"".htmlspecialcharsbx($button_value)."\"".(!empty($css_style) ? " class=\"".$css_style."\"" : "")." />";
	}

	/**
	 * Public method used to put reset button onto form.
	 * Use: <?=$FORM->ShowResetButton()?>
	 *
	 * @return string
	 */
	function ShowResetButton($caption = "", $css_style = "")
	{
		$button_value = trim($caption) <> '' ? trim($caption) : GetMessage("FORM_RESET");

		return "<input type=\"reset\" value=\"".htmlspecialcharsbx($button_value)."\"".(!empty($css_style) ? " class=\"".$css_style."\"" : "")." />";
	}

	/**
	 * Public method used to put form description onto form page
	 * Use: <?=$FORM->ShowFormDescription()?>
	 *
	 * @return string
	 */
	function ShowFormDescription($css_style = "")
	{
		$ret = $this->arForm["DESCRIPTION_TYPE"] == "html" ? trim($this->arForm["DESCRIPTION"]) : nl2br(htmlspecialcharsbx(trim($this->arForm["DESCRIPTION"])));

		if ($css_style <> '') $ret = "<div class=\"".$css_style."\">".$ret."</div>";

		return $ret;
	}

	/**
	 * Public: check whether form has description
	 *
	 * @return bool
	 */
	function isFormDescription()
	{
		return trim($this->arForm["DESCRIPTION"]) <> '';
	}

	/**
	 * Public: shows form image; params like CFile::ShowImage()
	 * Use: <?=$FORM->ShowFormImage(250, 250, "hspace=\"0\" vspace=\"0\" align=\"left\" border=\"0\"", "", true, GetMessage("FORM_ENLARGE"))?>
	 * Returns image code if image exists and empty string otherwise
	 *
	 * @param int $iMaxW
	 * @param int $iMaxH
	 * @param string $sParams
	 * @param string $strImageUrl
	 * @param bool $bPopup
	 * @param mixed $strPopupTitle
	 * @return string
	 */
	//function ShowFormImage($iMaxW=0, $iMaxH=0, $sParams="border=\"0\"", $strImageUrl="", $bPopup=false, $strPopupTitle=false)
	function ShowFormImage($sAlign = "", $iMaxW="", $iMaxH="", $bPopup="N", $strPopupTitle="", $sHSpace = "", $sVSpace = "", $sBorder = "")
	{
		if ($this->isFormImage())
		{
			$arImageParams = array();

			if ($sAlign <> '') $arImageParams[] = sprintf("align=\"%s\"", $sAlign);
			if ($sHSpace <> '') $arImageParams[] = sprintf("hspace=\"%s\"", $sHSpace);
			if ($sVSpace <> '') $arImageParams[] = sprintf("vspace=\"%s\"", $sVSpace);
			if ($sBorder <> '') $arImageParams[] = sprintf("border=\"%s\"", $sBorder);
			else $arImageParams[] = "border=\"0\"";

			if ($strPopupTitle == '') $strPopupTitle = false;

			if ($this->__form_image_cache == '')
			{
				$this->__form_image_cache = CFile::ShowImage($this->arForm["IMAGE_ID"], $iMaxW, $iMaxH, implode(" ", $arImageParams), $strImageUrl, $bPopup == "Y", $strPopupTitle);
			}

			$ret = $this->__form_image_cache;

			if (mb_strtoupper($sAlign) == "CENTER") $ret = "<div align=\"center\">".$ret."</div>";

			$this->__form_image_cache = $ret;

			return $ret;
		}
	}

	/**
	 * Public: check if form has image
	 *
	 * @return bool
	 */
	function isFormImage()
	{
		return intval($this->arForm["IMAGE_ID"])>0;
	}

	/**
	 * Public: shows current form title
	 *
	 * @return string
	 */
	function ShowFormTitle($css_style = "")
	{
		$ret = trim(htmlspecialcharsbx($this->arForm["NAME"]));

		if ($css_style <> '') $ret = "<div class=\"".$css_style."\">".$ret."</div>";

		return $ret;
	}

	/**
	 * Public: check whether current form has title string
	 *
	 * @return bool
	 */
	function isFormTitle()
	{
		return trim($this->arForm["NAME"]) <> '';
	}

	function ShowResultStatusForm()
	{
		if ($this->isResultStatusChangeAccess())
		{
			return SelectBox("status_".$this->arForm["SID"], CFormStatus::GetDropdown($this->WEB_FORM_ID, array("MOVE"), $this->arResult["USER_ID"]), " ", "", "");
		}
		else
			return "";
	}

	function ShowResultStatus($bNotShowCSS = "N")
	{
		if (intval($this->RESULT_ID) <= 0) return "";
		if ($bNotShowCSS != "N")
		{
			return "<span class='".$this->arResult["STATUS_CSS"]."'>".$this->arResult["STATUS_TITLE"]."</span>";
		}
		else
		{
			return $this->arResult["STATUS_TITLE"];
		}
	}

	function ShowResultStatusText()
	{
		return $this->arResult["STATUS_TITLE"];
	}

	function GetResultStatusCSSClass()
	{
		return $this->arResult["STATUS_CSS"];
	}

	function isResultStatusChangeAccess()
	{
		return (!empty($this->RESULT_ID) && in_array("EDIT", $this->arrRESULT_PERMISSION));
	}

	function ShowDateFormat($css_style = "")
	{
		$format = CLang::GetDateFormat("SHORT");

		if ($css_style <> '') return '<span class="'.$css_style.'">'.$format.'</span>';
		else return $format;
	}

	/**
	 * Public method used to show "required" label (red '*')
	 * Use: <?=$FORM->ShowRequired()?>
	 *
	 * @return string
	 */
	public static function ShowRequired()
	{
		return CForm::ShowRequired("Y");
	}

	public static function CheckTemplate($FORM_TEMPLATE, &$arrFS)
	{
		if (is_array($arrFS) && !empty($arrFS))
		{
			$arFldSIDs = array();
			$arInactiveFldSIDs = array();
			$str = "";
			foreach ($arrFS as $key => $arField)
			{
				$cur_str = "";
				if (trim($arField["FIELD_SID"]) == '') $cur_str .= GetMessage("FORM_ERROR_FORGOT_SID")."<br>";
				elseif (preg_match("/[^A-Za-z_01-9]/",$arField["FIELD_SID"])) $cur_str .= GetMessage("FORM_ERROR_INCORRECT_SID")."<br>";
				elseif (in_array($arField['FIELD_SID'], $arFldSIDs))
				{
					$key = array_search($arField['FIELD_SID'], $arInactiveFldSIDs);
					if ($key)
					{
						unset($arrFS[$key]);
						unset($arInactiveFldSIDs[$key]);
						unset($arFldSIDs[$key]);
					}
					else
					{
						$s = str_replace("#TYPE#", GetMessage("FORM_TYPE_FIELD"), GetMessage("FORM_ERROR_WRONG_SID"));
						$s = str_replace("#ID#",$zr["ID"],$s);
						$cur_str .= $s."<br>";
					}
				}
				else
				{
					$arFldSIDs[$key] = $arField["FIELD_SID"];
					if (!CForm::isFieldInTemplate($arField["FIELD_SID"], $FORM_TEMPLATE))
						$arInactiveFldSIDs[$key] = $arField["FIELD_SID"];
				}

				if (!empty($cur_str))
				{
					$str .= $cur_str;
				}
			}

			if (!empty($str))
			{
				$GLOBALS["strError"] .= $str;
				return false;
			}
			else return true;
		}
		return true;
	}

	public static function PrepareFormData($arrFS)
	{
		$out = "";
		$i = 0;
		if (is_array($arrFS))
		{
			foreach($arrFS as $key=>$arField)
			{
				if ($arField['isNew'] == "Y") $arField["CAPTION"] = $arField["isHTMLCaption"] == "Y" ? $arField["CAPTION_UNFORM"] : "<span class=\"tablebodytext\">".$arField["CAPTION_UNFORM"]."</span>".($arField["isRequired"] ? CFormOutput::ShowRequired() : "");
?>
arrInputObjects[<?=$i++?>] = new CFormAnswer(
	'<?=$arField["FIELD_SID"]?>',
	'<?=CUtil::JSEscape($arField["CAPTION"])?>',
	'<?=$arField["isHTMLCaption"]?>',
	'<?=CUtil::JSEscape("'", "\\'", $arField["CAPTION_UNFORM"])?>',
	'<?=$arField["isRequired"]?>',
	'<?=$arField["type"]?>',
	[<?
				foreach ($arField["structure"] as $key=>$arQuestion)
				{
					$arr = array();
					$cnt = 0;
					foreach ($arQuestion as $q_key=>$value)
					{
						$arr[] = $q_key.":'".($q_key == "ANS_NEW" ? ($value == "Y" ? 'true' : 'false') : str_replace("'", "\\'", $value))."'";
						if ($q_key == "ANS_NEW" && $value) $cnt++;
					}

					if ($key != 0) echo ",";
					echo "{";
					echo implode(",", $arr);
					echo "}";
				}
	?>],
	<?=$arField["isNew"] == "Y" ? 'true' : 'false'?>,
	<?=$arField["ID"] ? $arField["ID"] : '_global_newinput_counter++'?>,
	'<?=$arField["inResultsTable"]?>',
	'<?=$arField["inExcelTable"]?>'
);

<?
				if ($cnt > 0) echo "_global_newanswer_counter += ".$cnt.";\n";
			}
		}
	}

	function setError($error)
	{
		$this->__error_msg = $error;
	}

	function isAccessFormParams()
	{
		return $this->F_RIGHT >= 25;
	}

	function isAccessForm()
	{
		return $this->F_RIGHT >= 10;
	}

	function isAccessFormResult($arrResult)
	{
		global $USER;

		return $this->F_RIGHT>=20 || ($this->F_RIGHT>=15 && $USER->GetID()==$arrResult["USER_ID"]);
	}

	function isAccessFormResultEdit()
	{
		return in_array("EDIT",$this->arrRESULT_PERMISSION);
	}

	function isAccessFormResultView()
	{
		return in_array("VIEW",$this->arrRESULT_PERMISSION);
	}

	function isAccessFormResultList()
	{
		return $this->F_RIGHT >= 15;
	}

	function getFormImagePath()
	{
		if (!$this->isFormImage()) return false;
		if (empty($this->__form_image_path_cache))
			$this->__form_image_path_cache = CFile::GetPath($this->arForm["IMAGE_ID"]);

		return $this->__form_image_path_cache;
	}

	function getInputCaptionImagePath($FIELD_SID)
	{
		if (!$this->isInputCaptionImage($FIELD_SID)) return false;
		if (empty($this->__form_input_caption_image_path_cache[$FIELD_SID]))
			$this->__form_input_caption_image_path_cache[$FIELD_SID] = CFile::GetPath($this->arQuestions[$FIELD_SID]["IMAGE_ID"]);

		return $this->__form_input_caption_image_path_cache[$FIELD_SID];
	}

	function setInputDefaultValue($FIELD_SID, $value, $ANSWER_ID = false)
	{
		if (is_array($this->arAnswers) && is_array($this->arAnswers[$FIELD_SID]))
		{
			$type = $this->__admin_GetInputType($FIELD_SID);
			if ($type == "multiple" || $type == "file" || $type == "image")
			{
				return;
			}

			if (intval($ANSWER_ID) == 0)
			{
				if ($type == "checkbox" || $type == "multiselect")
				{
					if (is_array($value)) $this->arrVALUES["form_".$type."_".$FIELD_SID] = $value;
				}
				elseif ($type == "radio" || $type == "dropdown")
				{
					if (!is_array($value)) $this->arrVALUES["form_".$type."_".$FIELD_SID] = $value;
				}
				else
				{
					$ANSWER_ID = $this->arAnswers[$FIELD_SID][0]["ID"];
					$this->arrVALUES["form_".$type."_".$ANSWER_ID] = $value;
				}
			}
			elseif (is_array($ANSWER_ID))
			{
				if ($type == "checkbox" || $type == "multiselect")
					$this->arrVALUES["form_".$type."_".$FIELD_SID] = $value == "N" ? array() : $ANSWER_ID;
			}
			else
			{
				if ($type == "radio" || $type == "dropdown")
					$this->arrVALUES["form_".$type."_".$FIELD_SID] = $value == "N" ? "" : $ANSWER_ID;
				else
					$this->arrVALUES["form_".$type."_".$ANSWER_ID] = $value;
			}
		}
	}
}
?>