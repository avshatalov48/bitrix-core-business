<?

function Tab1($adminForm)
{
	$adminForm->BeginCustomField("NAME", GetMessage("SUP_NAME"), false); 
	?>
	<tr class="adm-detail-required-field"> 
		<td width="40%" align="right"><? echo $adminForm->GetCustomLabelHTML()?>:</td>
		<td width="60%"><input type="text" maxlength="255" name="NAME" size="50" value="<? echo CSupportPage::$timeTableFields->getFieldForOutput("NAME", CSupportTableFields::ATTRIBUTE); ?>"></td>
	</tr>
	<?
	$adminForm->EndCustomField("NAME");
		
	$adminForm->BeginCustomField("DESCRIPTION", GetMessage("SUP_DESCRIPTION"), false);
	?>
	<tr class="heading">
		<td colspan="2"><? echo $adminForm->GetCustomLabelHTML(); ?>:</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><textarea style="width:60%; height:150px;" name="DESCRIPTION" wrap="VIRTUAL"><? echo CSupportPage::$timeTableFields->getFieldForOutput("DESCRIPTION", CSupportTableFields::ATTRIBUTE); ?></textarea></td>
	</tr>
	<? 
	$adminForm->EndCustomField("DESCRIPTION");
}

function Tab2_JS()
{
	?>
	<script type="text/javascript">
	<!--
		function HideRC(ob, tableId)
		{
			h = "hidden";
			if(ob.value == 'CUSTOM')
			{
				h = "visible";
			}
			document.getElementById(tableId).style.visibility = h;
		}

		function Copy(i, j)
		{
			nextFieldF = document.getElementById("MINUTE_FROM_"+i+"_"+(j+1));
			if(nextFieldF==null)
			{
				j++;
				window.supportMinuteI = i;
				window.supportMinuteJ = j;
				var ajaxUrl = "<? echo basename(__FILE__); ?>";
				var tbl = document.getElementById("table"+i);
				var cnt = tbl.rows.length;
				var oRow = tbl.insertRow(-1);
				var currRowID = "td_" + Math.random();
				oRow.id = currRowID;

				/*vF = document.getElementById("MINUTE_FROM_"+i+"_"+(j-1)).value;
				oCellF = oRow.insertCell(0);
				oCellF.innerHTML = '<input id="MINUTE_FROM_'+i+'_'+j +'" type="text" title="" size="4" value="'+vF+'" name="ArrShedule['+i+'][CUSTOM_TIME]['+j+'][MINUTE_FROM]">';

				oCellD = oRow.insertCell(1);
				oCellD.innerHTML = '<td valign="middle" nowrap="" align="center"><nobr> - </nobr></td>';

				vT = document.getElementById("MINUTE_TILL_"+i+"_"+(j-1)).value;
				oCellT = oRow.insertCell(2);
				oCellT.innerHTML = '<input id="MINUTE_TILL_'+i+'_'+j+'" type="text" title="" size="4" value="'+vT+'" name="ArrShedule['+i+'][CUSTOM_TIME]['+j+'][MINUTE_TILL]">';

				oCellC = oRow.insertCell(3);
				oCellC.innerHTML = '<a title="<? echo GetMessage("MAIN_ADMIN_MENU_COPY"); ?>" href="javascript: Copy('+i+','+j+')">' +
				'<img src="/bitrix/images/support/copy.gif" style="vertical-align:middle" width="15" height="15" border=0 hspace="2" alt="<? echo GetMessage("MAIN_ADMIN_MENU_COPY"); ?>"></a>';*/

				vF = document.getElementById("MINUTE_TILL_"+i+"_"+(j-1)).value;
				vT = "23:59"/*document.getElementById("MINUTE_TILL_"+i+"_"+(j-1)).value;*/
				oCellF = oRow.insertCell(0);
				oCellF.style.align="left";
				/*id="time_tab_row_' . $j . '"*/
				oCellF.innerHTML = '<input id="MINUTE_FROM_'+i+'_'+j +'" type="text" title="" size="4" value="'+vF+'" name="ArrShedule['+i+'][CUSTOM_TIME]['+j+'][MINUTE_FROM]">' +
						'<td valign="middle" nowrap="" align="center"><nobr> - </nobr></td>' +
						'<input id="MINUTE_TILL_'+i+'_'+j+'" type="text" title="" size="4" value="'+vT+'" name="ArrShedule['+i+'][CUSTOM_TIME]['+j+'][MINUTE_TILL]">' +
						'<a title="<? echo GetMessage("MAIN_ADMIN_MENU_COPY"); ?>" href="javascript: Copy('+i+','+j+')">' +
						'<img src="/bitrix/images/support/copy.gif" style="vertical-align:middle" width="15" height="15" border=0 hspace="2" alt="<? echo GetMessage("SUP_ADMIN_ROW_COPY"); ?>">' +
						'<a href="javascript: DeleteTabRow(\'' + oRow.id +'\')"><img src="/bitrix/images/support/cross.png" style="vertical-align:middle" width="15" height="15" border=0 hspace="2" alt="<? echo GetMessage("SUP_ADMIN_ROW_DELETE"); ?>"></a>' +
						'</a>';

				/*
				var data = {
					'MY_AJAX' : 'CClockAJAX',
					'CClockAJAXData' : {
						'i' : i,
						'j' : j
					}
				};


				BX.ajax.post(
					ajaxUrl,
					data,
					function(datum) {

						arrQ = JSON.parse(datum);
						for (var i = 0; i < arrQ.length; i++)
						{
							oRowQ = document.getElementById(currRowID);
							oCellQ = oRowQ.insertCell(i);
							oCellQ.innerHTML = arrQ[i];
						}

						i0 = window.supportMinuteI;
						j0 = window.supportMinuteJ;
						document.getElementById("MINUTE_FROM_"+i0+"_"+j0).value = document.getElementById("MINUTE_FROM_"+i0+"_"+(j0-1)).value;
						document.getElementById("MINUTE_TILL_"+i0+"_"+j0).value = document.getElementById("MINUTE_TILL_"+i0+"_"+(j0-1)).value;
					} 
				);*/
			}
			else
			{
				nextFieldT = document.getElementById("MINUTE_TILL_"+i+"_"+(j+1));
				nextFieldF.value = document.getElementById("MINUTE_FROM_"+i+"_"+j).value;
				nextFieldT.value = document.getElementById("MINUTE_TILL_"+i+"_"+j).value;
			}
		}

		function DeleteTabRow(rowID)
		{
			var timeTabRow = document.getElementById(rowID);
			timeTabRow.parentNode.removeChild(timeTabRow);
		}
	//-->
	</script>
	<?
}

function Tab2( $adminForm, $arShedule)
{
	$adminForm->BeginCustomField("HOURS", GetMessage("SUP_HOURS"), false);
	Tab2_JS();
	?>
		
	<tr>
		<td colspan="2" align="center">
			<table border="0" cellspacing="0" cellpadding="0" width="50%" class="internal">
	<?
	$arrSO = array(
		"24H" => "SUP_24H",
		"CLOSED" => "SUP_CLOSED",
		"CUSTOM" => "SUP_CUSTOM",
	);
	
	for($i=0; $i<=6; $i++)
	{
		?>
		<tr valign="top">
			<td class="heading"><b><? echo GetMessage("SUP_WEEKDAY_$i"); ?></b></td>
		<?
		foreach($arrSO as $v => $l) 
		{
			echo '<td align="center" nowrap>' . InputType("radio", "ArrShedule[$i][OPEN_TIME]", $v, $arShedule[$i]["OPEN_TIME"], false, '&nbsp;' . GetMessage($l), 'onClick="HideRC(this, \'table' . $i . '\')"') .  '</td>';
		}

		$styleV = " visibility: hidden;";
		if($arShedule[$i]["OPEN_TIME"] == "CUSTOM")
		{
			$styleV = "";
		}
		?>
			<td align="center" nowrap>
				<table border="0" cellspacing="0" cellpadding="0" style="margin-top:-6px;width:1%!important;<? echo $styleV; ?>" id="table<? echo $i; ?>">
		
		<?
			
			/*$arrREQ = array(
				"CClockAJAXData" => array(
					"i" => $i,
				),
			);*/
			$j = -1;
			$styleFirstRow = ' style="padding-top:2px !important;"';
			foreach($arShedule[$i]["CUSTOM_TIME"] as $k => $v)
			{
				$j++;
				/*$arrREQ["CClockAJAXData"]["j"] = $j;
				$arrREQ["CClockAJAXData"]["ValF"] = $v["MINUTE_FROM"];
				$arrREQ["CClockAJAXData"]["ValT"] = $v["MINUTE_TILL"];
				echo "<tr>" . CSupportPage::ShowClock($arrREQ) . "</tr>";*/
				$trID = "td_" . $i . $j;
				echo '<tr id="' . $trID . '">
				<td' . $styleFirstRow . ' align="left">'.
					'<input id="MINUTE_FROM_' . $i . '_' . $j . '" type="text" title="" size="4" value="' . $v["MINUTE_FROM"] . '" name="ArrShedule[' . $i . '][CUSTOM_TIME][' . $j . '][MINUTE_FROM]">' .
					'<nobr>&nbsp;-&nbsp;</nobr>' .
					'<input id="MINUTE_TILL_' . $i . '_' . $j . '" type="text" title="" size="4" value="' . $v["MINUTE_TILL"] .'" name="ArrShedule[' . $i . '][CUSTOM_TIME][' . $j . '][MINUTE_TILL]">' .
					'<a title="' . GetMessage("MAIN_ADMIN_MENU_COPY") . '" href="javascript: Copy(' . $i . ',' . $j . ')"><img src="/bitrix/images/support/copy.gif" style="vertical-align:middle" width="15" height="15" border=0 hspace="2" alt="' . GetMessage("SUP_ADMIN_ROW_COPY") . '"></a>' .
					($j > 0 ? '<a href="javascript: DeleteTabRow(\'' . $trID . '\')"><img src="/bitrix/images/support/cross.png" style="vertical-align:middle" width="15" height="15" border=0 hspace="2" alt="' . GetMessage("SUP_ADMIN_ROW_DELETE") . '"></a>' : '') .
				'</td></tr>';
				$styleFirstRow = "";
			}
		
		
		?>
				</table>
			</td>
		</tr>
		<? 
		
		
	}
	?>
			</table>
		</td>
	</tr>
				
	<?
	$adminForm->EndCustomField("HOURS");
}

class CSupportPage
{
	const AJAX_VAR_NAME = "MY_AJAX";
	const LIST_URL = "ticket_timetable_list.php";
	const SHOW_FORM_SETTINGS = true;
	const SHOW_USER_FIELDS = false;
	const DEFAULT_TIME = "00:00";
	const DEFAULT_TIME_INPUT_ROW = 1;
	
	static $needShowInterface = true;
	static $needSave = false;
	static $canNotRead = false;
	static $objCAdminForm = null; //$tabControl
	static $notSaved = true;
	static $isErrors = false;
	static $id = 0;
	
	static $timeTableFields = null;
	static $timeTableSheduleFields = null;
	static $postTimeTableFields = null;
	static $postTimeTableSheduleFields = null;
		
	static function ProcessAJAX()
	{
		if(isset($_REQUEST[self::AJAX_VAR_NAME]) && strlen($_REQUEST[self::AJAX_VAR_NAME]) > 0)
		{
			self::$needShowInterface = false;
			$type = $_REQUEST[self::AJAX_VAR_NAME];
			switch($type)
			{
				case "CClockAJAX":	
					//echo self::ShowClock($_REQUEST, true);
					return true;
			}
		}
		return false;
	}
	
	static function GetPost()
	{
		self::$postTimeTableFields = new CSupportTableFields(CSupportTimetable::$fieldsTypes);
		self::$postTimeTableSheduleFields = new CSupportTableFields(CSupportTimetable::$fieldsTypesShedule, CSupportTableFields::C_Table);
		$res = false;
		if(isset($_REQUEST["ID"]) && intval($_REQUEST["ID"]) > 0) self::$id = intval($_REQUEST["ID"]);
		
		if(check_bitrix_sessid() && $_SERVER["REQUEST_METHOD"] == "POST" )
		{
			// Get data from POST
			self::$postTimeTableFields->FromArray($_REQUEST);
			self::$id = self::$postTimeTableFields->ID;
			if(isset($_REQUEST["ArrShedule"]) && is_array($_REQUEST["ArrShedule"]) && count($_REQUEST["ArrShedule"]) > 0) self::ArrSheduleInObj($_REQUEST["ArrShedule"]);
			$res = true;
		}
		return $res;
	}
	
	static function Save()
	{
		$presSave = (isset($_REQUEST["save"]) && strlen($_REQUEST["save"]) > 0);
		$presApply = (isset($_REQUEST["apply"]) && strlen($_REQUEST["apply"]) > 0);
		if($presSave || $presApply)
		{
			self::$id = intval(CSupportTimetable::Set(self::$postTimeTableFields, self::$postTimeTableSheduleFields));
			// ≈сли сохранить не удалось то self::$id будет равен 0 и read() не сработает данные возьмутс€ из POST без изменений
			if(self::$id > 0)
			{
				if(!$presApply)
				{
					LocalRedirect("/bitrix/admin/" . self::LIST_URL . "?lang=". LANG . GetFilterParams("filter_", false));
				}
				return true;
			}
		}
		return false;				
	}
	
	static function Read()
	{
		if(self::$id <= 0) return false;
		self::$timeTableFields = new CSupportTableFields(CSupportTimetable::$fieldsTypes);
		$rs = CSupportTimetable::GetList(array(), array('ID' => self::$id));
		if ($arResult = $rs->Fetch()) 
		{
			self::$timeTableFields->FromArray($arResult);
			self::$notSaved = false;
			self::$timeTableSheduleFields = CSupportTimetable::GetSheduleByID(self::$id, true);
		}
		else
		{
			self::$canNotRead = true;
			return false;
		}
		return true;
	}
	
	static function GetArrayOfTabs()
	{
		global $USER_FIELD_MANAGER;
		$res = array(
			array(
				"DIV" => "edit1",
				"ICON"=>"main_user_edit",
				"TAB" => GetMessage("SUP_ADMIN_TAB1"),
				"TITLE"=>GetMessage("SUP_ADMIN_TAB1")
			),
			array(
				"DIV" => "edit2",
				"ICON"=>"main_user_edit",
				"TAB" => GetMessage("SUP_ADMIN_TAB2"),
				"TITLE"=>GetMessage("SUP_ADMIN_TAB2")
			),
		);
		if(self::SHOW_USER_FIELDS) $res[] = $USER_FIELD_MANAGER->EditFormTab("LEARN_ATTEMPT");
		return $res;
	}
	
	static function DoActions()
	{
		global $APPLICATION;
		if(self::ProcessAJAX()) return;
		if(self::GetPost()) self::Save();
		if(!self::Read())
		{
		
			self::$timeTableFields = self::$postTimeTableFields;
			self::$timeTableSheduleFields = self::$postTimeTableSheduleFields;
		}	
		self::$objCAdminForm = new CAdminForm("supTabControl", self::GetArrayOfTabs());

		if (empty($_REQUEST['ID']))
		{
			$APPLICATION->SetTitle(GetMessage("SUP_ADMIN_TITLE_ADD"));
		}
		else
		{
			$APPLICATION->SetTitle(GetMessage("SUP_ADMIN_TITLE_EDIT", array('#NAME#' => self::$timeTableFields->NAME)));
		}
	}
	
	static function ShowErrors()
	{
		global $APPLICATION;
		if(self::$canNotRead)
		{
			$aContext = array(
				array(
					"ICON" =>	"btn_list",
					"TEXT" =>	GetMessage("SUP_BACK_TO_ADMIN"),
					"LINK" =>	(self::LIST_URL . "?lang=" . LANG),
					"TITLE" =>	GetMessage("SUP_BACK_TO_ADMIN")
				),
			);
			$context = new CAdminContextMenu($aContext);
			$context->Show();

			CAdminMessage::ShowMessage(GetMessage("SUP_TIMETABLE_NOT_FOUND"));
			return true;
		}
		
		if($e = $APPLICATION->GetException())
		{
			self::$isErrors = true;
			$errorMessage = new CAdminMessage(GetMessage("SUP_ERROR"), $e);
			echo $errorMessage->Show();
		}
		return false;
	}
	
	static function ShowMenu()
	{	
		global $APPLICATION;
		$aContext = array(
			array(
				"ICON" =>	"btn_list",
				"TEXT" =>	GetMessage("MAIN_ADMIN_MENU_LIST"),
				"LINK" =>	self::LIST_URL . "?lang=". LANG . GetFilterParams("filter_"),
				"TITLE" =>	GetMessage("MAIN_ADMIN_MENU_LIST")
			),
		);
		
		if(!self::$notSaved)
		{
			$aContext[] = 	array(
				"ICON" =>	"btn_delete",
				"TEXT" =>	GetMessage("MAIN_ADMIN_MENU_DELETE"),
				"LINK" =>	"javascript:if(confirm('" . GetMessage("SUP_CONFIRM_DEL_MESSAGE") . "'))window.location='" . self::LIST_URL . "?lang=" . LANG .
							"&action=delete&ID=" . self::$timeTableFields->ID . "&" . bitrix_sessid_get() . urlencode(GetFilterParams("filter_", false)) . "';",
			);

		}
	
		if(self::SHOW_FORM_SETTINGS)
		{
			$link = DeleteParam(array("mode"));
			$link = $APPLICATION->GetCurPage() . "?mode=settings".($link <> "" ? "&" . $link : "");
			$aContext[] = array(
				"TEXT" =>	GetMessage("SUP_FORM_SETTINGS"),
				"TITLE" =>	GetMessage("SUP_FORM_SETTINGS_EX"),
				"LINK" =>	"javascript:". self::$objCAdminForm->GetName() . ".ShowSettings('" . urlencode($link) . "')",
				"ICON" =>	"btn_settings",
			);
		}
		
		$context = new CAdminContextMenu($aContext);
		$context->Show();
	}
			
	static function Show()
	{
		global $USER_FIELD_MANAGER, $APPLICATION;
		if(self::ShowErrors()) return;
		self::ShowMenu();
		
		self::$objCAdminForm->BeginEpilogContent();
		echo bitrix_sessid_post();
		GetFilterHiddens("filter_");
		echo '
		<input type="hidden" name="Update" value="Y">
		<input type="hidden" name="ID" value="' . self::$timeTableFields->ID . '">
		';
		self::$objCAdminForm->EndEpilogContent();
		
		self::$objCAdminForm->Begin();
		
		self::$objCAdminForm->BeginNextFormTab();
		Tab1(self::$objCAdminForm);
		
		
		self::$objCAdminForm->BeginNextFormTab();
		Tab2(self::$objCAdminForm, self::ObjInArrShedule());
					
		if(self::SHOW_USER_FIELDS)
		{
			self::$objCAdminForm->BeginNextFormTab();
			self::$objCAdminForm->BeginCustomField("USER_FIELDS", GetMessage("SUP_ADMIN_USER_FIELDS"), false);
			$USER_FIELD_MANAGER->EditFormShowTab("LEARN_ATTEMPT", self::$isErrors, self::$timeTableFields->ID);
			self::$objCAdminForm->EndCustomField("USER_FIELDS");
		}
		
		self::$objCAdminForm->Buttons(Array("back_url" => "ticket_timetable_list.php?lang=" . LANG.GetFilterParams("filter_", false)));
		self::$objCAdminForm->arParams["FORM_ACTION"] = $APPLICATION->GetCurPage() . "?lang=" . LANG . GetFilterParams("filter_");
		self::$objCAdminForm->Show();
		
	}
	
	static function TimeToStr($t)
	{
		if($t == 0) return self::DEFAULT_TIME;
		$m = intval(fmod ($t, 60));
		$h = ($t - $m) / 60;
		return date("H:i", mktime($h, $m, 0, 1, 1, 2000));
	}
	
	static function StrToTime($t)
	{
		//echo $t;
		$a = explode(":", $t);
		$res = (isset($a[0]) ? intval($a[0]) * 60 : 0);
		$res += (isset($a[1]) ? intval($a[1]) : 0);
		return $res;
	}
	
	static function ArrSheduleInObj($arr)
	{
	
		/*
		array["ArrShedule"] = array(
			0 => array(
				OPEN_TIME => "CUSTOM",
				//CUSTOM_TIME_NUM => 1 (0,1)
				CUSTOM_TIME => array(
					0 => array(
						MINUTE_FROM => "11:32"
						MINUTE_TILL => "12:32"
					)
				)
			),
			6 => ...
		)
		*/
		self::$postTimeTableSheduleFields->RemoveExistingRows();
		$arrTTS = array();
		foreach($arr as $DateWeekday => $arDay)
		{
			if(!isset($arDay["OPEN_TIME"]) || strlen($arDay["OPEN_TIME"]) <= 0) continue;
			if($arDay["OPEN_TIME"] == "CUSTOM" && !(isset($arDay["CUSTOM_TIME"]) && is_array($arDay["CUSTOM_TIME"]) && count($arDay["CUSTOM_TIME"]) > 0)) continue;
			
			$arrTTS["TIMETABLE_ID"] = self::$id;
			$arrTTS["WEEKDAY_NUMBER"] = $DateWeekday;
			$arrTTS["OPEN_TIME"] = $arDay["OPEN_TIME"];
			if($arDay["OPEN_TIME"] == "CUSTOM")
			{
				foreach($arDay["CUSTOM_TIME"] as $ar)
				{
					$presMF = (isset($ar["MINUTE_FROM"]) && strlen($ar["MINUTE_FROM"]) > 0);
					$presMT = (isset($ar["MINUTE_TILL"]) && strlen($ar["MINUTE_TILL"]) > 0);
					if($presMF || $presMT)
					{
						$minute_from = self::StrToTime(($presMF ? $ar["MINUTE_FROM"] : "00:00"));
						$minute_till = self::StrToTime(($presMT ? $ar["MINUTE_TILL"] : "23:59"));
						self::$postTimeTableSheduleFields->AddRow();
						self::$postTimeTableSheduleFields->FromArray($arrTTS);
						self::$postTimeTableSheduleFields->MINUTE_FROM = min($minute_from, $minute_till);
						self::$postTimeTableSheduleFields->MINUTE_TILL = max($minute_from, $minute_till);
					}
				}
			}
			else
			{
				self::$postTimeTableSheduleFields->AddRow();
				self::$postTimeTableSheduleFields->FromArray($arrTTS);
			}
		}
	}
		
	static function ObjInArrShedule()
	{
	
	/*
		ArrShedule = array(
			0 => array(
				OPEN_TIME => "CUSTOM",
				//CUSTOM_TIME_NUM => 1 (0,1)
				CUSTOM_TIME => array(
					0 => array(
						MINUTE_FROM => "11:32"
						MINUTE_TILL => "12:32"
					)
				)
			),
			6 => ...
		)
		*/
		
		$res = array();
		self::$timeTableSheduleFields->SortRow("WEEKDAY_NUMBER,MINUTE_FROM");
		self::$timeTableSheduleFields->ResetNext();
		while(self::$timeTableSheduleFields->Next())
		{
			$res[self::$timeTableSheduleFields->WEEKDAY_NUMBER]["OPEN_TIME"] = self::$timeTableSheduleFields->OPEN_TIME;
			$res[self::$timeTableSheduleFields->WEEKDAY_NUMBER]["CUSTOM_TIME"][] = array("MINUTE_FROM" => self::TimeToStr(self::$timeTableSheduleFields->MINUTE_FROM), "MINUTE_TILL" => self::TimeToStr(self::$timeTableSheduleFields->MINUTE_TILL));
				
		}
		// дополн€ем дл€ покза
		for($i=0; $i<=6; $i++)
		{
			if(!isset($res[$i]) || !is_array($res[$i]) || (count($res[$i]) <= 0))
			{
				$res[$i] = array("OPEN_TIME" => "24H");
			}
			if(!isset($res[$i]["CUSTOM_TIME"]) || !is_array($res[$i]["CUSTOM_TIME"]) || (count($res[$i]["CUSTOM_TIME"]) <= 0))
			{
				$res[$i]["CUSTOM_TIME"] = array();
			}
			$c = self::DEFAULT_TIME_INPUT_ROW - count($res[$i]["CUSTOM_TIME"]);
			for($j = 0; $j < $c; $j++) $res[$i]["CUSTOM_TIME"][] = array("MINUTE_FROM" => self::DEFAULT_TIME, "MINUTE_TILL" => self::DEFAULT_TIME);
		}
		return $res;
	}
	
	/*static function ShowClock($arrREQ, $jsonON = false)
	{
		global $APPLICATION;
		
		if(!isset($arrREQ["CClockAJAXData"]) || !is_array($arrREQ["CClockAJAXData"]) || (count($arrREQ["CClockAJAXData"]) <= 0) ||  !CSupportTools::array_keys_exists("i,j", $arrREQ["CClockAJAXData"]))
		{
			return false;
		}
		$arr = $arrREQ["CClockAJAXData"];
		$i = intval($arr["i"]);
		$j = intval($arr["j"]);
		$val = array(
			1 => (isset($arr["ValF"]) && strlen($arr["ValF"]) > 0) ? CUtil::JSEscape($arr["ValF"]) : self::DEFAULT_TIME,
			2 => (isset($arr["ValT"]) && strlen($arr["ValT"]) > 0) ? CUtil::JSEscape($arr["ValT"]) : self::DEFAULT_TIME,
		);
		$ft = array(
			1 => "FROM",
			2 => "TILL",
		);
		
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
		$clock = array();
		for($k = 1; $k < 3; $k++)
		{
			ob_start();
			CClock::Show(
				array(
					'inputId' => ("MINUTE_" . $ft[$k] . "_" .$i . "_" . $j),
					'inputName' => "ArrShedule[$i][CUSTOM_TIME][$j][MINUTE_" . $ft[$k] . "]",
					'view' => "label", //"inline","label","select",
					'showIcon' => true,
					'initTime' => $val[$k],
					'am_pm_mode' => false,
					//'step' => 5
				)
			);

			$clock[$k] = ob_get_contents();
			ob_end_clean();
		}
		
		if($jsonON)	
		{
			$res = array( 
				$clock[1],
				'<nobr>&nbsp;-&nbsp;</nobr>',
				$clock[2],
				'<a title="' . GetMessage("MAIN_ADMIN_MENU_COPY") . '" href="javascript: Copy(' . $i . ',' . $j . ')"><img src="/bitrix/images/support/copy.gif" width="15" height="15" border=0 hspace="2" alt="' . GetMessage("MAIN_ADMIN_MENU_COPY") . '"></a>'
			);
			
			if (ToUpper(SITE_CHARSET) !== 'UTF-8')
			{
				$res0 = array();
				foreach($res as $k => $v)
				{
					$res0[$k] = $APPLICATION->ConvertCharset($v, SITE_CHARSET, 'utf-8');
				}
				$res = $res0;
			}
			$res = json_encode($res);	
		}
		else
		{
			$res = '
				<td>' . $clock[1] . '</td>
				<td nowrap="" valign="middle" align="center"><nobr>&nbsp;-&nbsp;</nobr></td>
				<td>' . $clock[2] . '</td>
				<td>
					<a title="' . GetMessage("MAIN_ADMIN_MENU_COPY") . '" href="javascript: Copy(' . $i . ',' . $j . ')"><img src="/bitrix/images/support/copy.gif" width="15" height="15" border=0 hspace="2" alt="' . GetMessage("MAIN_ADMIN_MENU_COPY") . '"></a>
				</td>';
		}

		return $res;
	}*/
	
}


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
IncludeModuleLangFile(__FILE__);

CSupportPage::DoActions();

if(CSupportPage::$needShowInterface)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	CSupportPage::Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

?>