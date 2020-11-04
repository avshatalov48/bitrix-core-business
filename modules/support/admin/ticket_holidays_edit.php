<?

function Tab1($adminForm)
{
	$adminForm->BeginCustomField("NAME", GetMessage("SUP_NAME"), false); 
	?>
	<tr class="adm-detail-required-field"> 
		<td width="40%" align="right"><? echo $adminForm->GetCustomLabelHTML()?>:</td>
		<td width="60%"><input type="text" maxlength="255" name="NAME" size="50" value="<? echo CSupportPage::$holidaysFields->getFieldForOutput("NAME", CSupportTableFields::ATTRIBUTE); ?>"></td>
	</tr>
	<?
	$adminForm->EndCustomField("NAME");
	
	$adminForm->BeginCustomField("OPEN_TIME", GetMessage("SUP_OPEN_TIME"), false); 
	?>
	<tr class="adm-detail-required-field"> 
		<td width="40%" align="right"><? echo $adminForm->GetCustomLabelHTML()?>:</td>
		<td width="60%">
			<select id="OPEN_TIME" name="OPEN_TIME" onchange="changeOpenTimeFormat()">
			<?
			$arr = CSupportHolidays::GetOpenTimeArray();
			foreach($arr as $v => $n)
			{
				$ss = mb_substr($v, 0, 3);
				if($ss == "GB_") echo '<optgroup label="' .  GetMessage($n) . '">';
				elseif($ss == "GE_") echo '</optgroup>';
				else echo '<option ' . ($v == CSupportPage::$holidaysFields->OPEN_TIME ? 'selected ' : '') . 'value="' . $v . '">' .  GetMessage($n) . '</option>';
			}		
		
			?>
			</select>
		</td>
	</tr>
	<script type="text/javascript">
		function changeOpenTimeFormat()
		{
			var inputFrom = BX.findChild(BX('supTabControl_form'), {attr:{name:'DATE_FROM'}}, true);
			var inputTill = BX.findChild(BX('supTabControl_form'), {attr:{name:'DATE_TILL'}}, true);

			inputFrom.value = BX.calendar.ValueToString(BX.parseDate(inputFrom.value), BX('OPEN_TIME').value.slice(-2) == '_H');
			inputTill.value = BX.calendar.ValueToString(BX.parseDate(inputTill.value), BX('OPEN_TIME').value.slice(-2) == '_H');
		}
	</script>
	<?
	$adminForm->EndCustomField("OPEN_TIME");
	
	$adminForm->BeginCustomField("DATE_FROM", GetMessage("SUP_DATE_FROM"), false);

	if (CSupportPage::$holidaysFields->OPEN_TIME == 'HOLIDAY_H' || CSupportPage::$holidaysFields->OPEN_TIME == 'WORKDAY_H')
	{
		$time = GetTime(CSupportPage::$holidaysFields->DATE_FROM, "FULL");
	}
	else
	{
		$time = GetTime(CSupportPage::$holidaysFields->DATE_FROM, "SHORT");
	}

	$dateControl = str_replace(
		array('bTime: true', 'bHideTime: false', 'BX.calendar({'),
		array('bTime: BX(\'OPEN_TIME\').value.slice(-2) == \'_H\'', 'bHideTime: BX(\'OPEN_TIME\').value.slice(-2) != \'_H\'',
			'BX.calendar({callback_after: function(param){this.params.field.value = BX.calendar.ValueToString(param, BX(\'OPEN_TIME\').value.slice(-2) == \'_H\')}, '
		),
		CalendarDate("DATE_FROM", $time, "supTabControl", "20")
	);

	?>
	<tr class="adm-detail-required-field"> 
		<td width="40%" align="right"><? echo $adminForm->GetCustomLabelHTML()?>:</td>
		<td width="60%"><? echo $dateControl ?></td>
	</tr>
	<?
	$adminForm->EndCustomField("DATE_FROM");
	
	$adminForm->BeginCustomField("DATE_TILL", GetMessage("SUP_DATE_TILL"), false);

	if (CSupportPage::$holidaysFields->OPEN_TIME == 'HOLIDAY_H' || CSupportPage::$holidaysFields->OPEN_TIME == 'WORKDAY_H')
	{
		$time = GetTime(CSupportPage::$holidaysFields->DATE_TILL, "FULL");
	}
	else
	{
		$time = GetTime(CSupportPage::$holidaysFields->DATE_TILL, "SHORT");
	}

	$dateControl = str_replace(
		array('bTime: true', 'bHideTime: false', 'BX.calendar({'),
		array('bTime: BX(\'OPEN_TIME\').value.slice(-2) == \'_H\'', 'bHideTime: BX(\'OPEN_TIME\').value.slice(-2) != \'_H\'',
			'BX.calendar({callback_after: function(param){this.params.field.value = BX.calendar.ValueToString(param, BX(\'OPEN_TIME\').value.slice(-2) == \'_H\')}, '
		),
		CalendarDate("DATE_TILL", $time, "supTabControl", "20")
	);

	?>
	<tr class="adm-detail-required-field"> 
		<td width="40%" align="right"><? echo $adminForm->GetCustomLabelHTML()?>:</td>
		<td width="60%"><? echo $dateControl ?></td>
	</tr>
	<?
	$adminForm->EndCustomField("DATE_TILL");
	
	$adminForm->BeginCustomField("SLA_ID", GetMessage("SUP_SLA_ID"), false);
	?>
	<tr valign="top"> 
		<td width="40%" align="right"><? echo $adminForm->GetCustomLabelHTML()?>:</td>
		<td width="60%">
			<?
			$arrSLA_ID = CSupportPage::$holidaysSlaFields->getColumn("SLA_ID");
			$arSort = array();
			$is_filtered = null;
			$ar = CTicketSLA::GetList($arSort, array(), $is_filtered);
			echo SelectBoxM('SLA_ID[]', $ar, $arrSLA_ID, false, 10);
			?>
		</td>
	</tr>
		
	<?
	$adminForm->EndCustomField("SLA_ID");
	
	$adminForm->BeginCustomField("DESCRIPTION", GetMessage("SUP_DESCRIPTION"), false);
	?>
	<tr class="heading">
		<td colspan="2"><? echo $adminForm->GetCustomLabelHTML(); ?>:</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><textarea style="width:60%; height:150px;" name="DESCRIPTION" wrap="VIRTUAL"><? echo CSupportPage::$holidaysFields->getFieldForOutput("DESCRIPTION", CSupportTableFields::ATTRIBUTE); ?></textarea></td>
	</tr>
	<? 
	$adminForm->EndCustomField("DESCRIPTION");
	
}

class CSupportPage
{
	const AJAX_VAR_NAME = "MY_AJAX";
	const LIST_URL = "ticket_holidays_list.php";
	const SHOW_FORM_SETTINGS = true;
	const SHOW_USER_FIELDS = false;
	
	static $needShowInterface = true;
	static $needSave = false;
	static $canNotRead = false;
	static $objCAdminForm = null; //$tabControl
	static $notSaved = true;
	static $isErrors = false;
	static $id = 0;
	
	static $holidaysFields = null;
	static $holidaysSlaFields = null;
	static $postHolidaysFields = null;
	static $postHolidaysSlaFields = null;
		
	static function ProcessAJAX()
	{
		if(isset($_REQUEST[self::AJAX_VAR_NAME]) && $_REQUEST[self::AJAX_VAR_NAME] <> '')
		{
			self::$needShowInterface = false;
			$type = $_REQUEST[self::AJAX_VAR_NAME];
			/*
			switch($type)
			{
				case "qqqqq":
					return true;
			}
			*/
		}
		return false;
	}
	
	static function GetPost()
	{
		self::$postHolidaysFields = new CSupportTableFields(CSupportHolidays::$holidays);
		self::$postHolidaysFields->DATE_FROM = time() + CTimeZone::GetOffset();
		self::$postHolidaysFields->DATE_TILL = time() + CTimeZone::GetOffset();
		self::$postHolidaysSlaFields = new CSupportTableFields(CSupportHolidays::$sla2holidays, CSupportTableFields::C_Table);
		self::$postHolidaysSlaFields->RemoveExistingRows();
		$res = false;
		if(isset($_REQUEST["ID"]) && intval($_REQUEST["ID"]) > 0) self::$id = intval($_REQUEST["ID"]);
		
		if(check_bitrix_sessid() && $_SERVER["REQUEST_METHOD"] == "POST" )
		{
			// Get data from POST
			self::$postHolidaysFields->FromArray($_REQUEST);
			self::$id = self::$postHolidaysFields->ID;
			if(isset($_REQUEST["SLA_ID"]) && is_array($_REQUEST["SLA_ID"]) && count($_REQUEST["SLA_ID"]) > 0) self::ArrSLAinObj($_REQUEST["SLA_ID"]);
			$res = true;
		}
		return $res;
	}
	
	static function Save()
	{
		$presSave = (isset($_REQUEST["save"]) && $_REQUEST["save"] <> '');
		$presApply = (isset($_REQUEST["apply"]) && $_REQUEST["apply"] <> '');
		if($presSave || $presApply)
		{
			self::$id = intval(CSupportHolidays::Set(self::$postHolidaysFields, self::$postHolidaysSlaFields));
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
		if(self::$id <= 0)
		{
			return false;
		}
		self::$holidaysFields = new CSupportTableFields(CSupportHolidays::$holidays);
		$rs = CSupportHolidays::GetList(array(), array('ID' => self::$id));
		if ($arResult = $rs->Fetch()) 
		{
			self::$holidaysFields->FromArray($arResult);
			self::$notSaved = false;
			self::$holidaysSlaFields = CSupportHolidays::GetSLAByID(self::$id, true);
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
		);
		if(self::SHOW_USER_FIELDS)
		{
			$res[] = $USER_FIELD_MANAGER->EditFormTab("LEARN_ATTEMPT");
		}
		return $res;
	}
	
	static function DoActions()
	{
		global $APPLICATION;
		if(self::ProcessAJAX())
		{
			return;
		}
		if(self::GetPost())
		{
			self::save();
		}
		if(!self::Read())
		{
			self::$holidaysFields = self::$postHolidaysFields;
			self::$holidaysSlaFields = self::$postHolidaysSlaFields;
		}	
		self::$objCAdminForm = new CAdminForm("supHolidaysTabControl", self::GetArrayOfTabs());
		$APPLICATION->SetTitle(GetMessage("SUP_ADMIN_TITLE"));
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
							"&action=delete&ID=" . self::$holidaysFields->ID . "&" . bitrix_sessid_get() . urlencode(GetFilterParams("filter_", false)) . "';",
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
		if(self::ShowErrors())
		{
			return;
		}
		self::ShowMenu();
		
		self::$objCAdminForm->BeginEpilogContent();
		echo bitrix_sessid_post();
		GetFilterHiddens("filter_");
		echo '
		<input type="hidden" name="Update" value="Y">
		<input type="hidden" name="ID" value="' . self::$holidaysFields->ID . '">
		';
		self::$objCAdminForm->EndEpilogContent();
		
		self::$objCAdminForm->Begin();
		
		self::$objCAdminForm->BeginNextFormTab();
		Tab1(self::$objCAdminForm);
							
		if(self::SHOW_USER_FIELDS)
		{
			self::$objCAdminForm->BeginNextFormTab();
			self::$objCAdminForm->BeginCustomField("USER_FIELDS", GetMessage("SUP_ADMIN_USER_FIELDS"), false);
			$USER_FIELD_MANAGER->EditFormShowTab("LEARN_ATTEMPT", self::$isErrors, self::$holidaysFields->ID);
			self::$objCAdminForm->EndCustomField("USER_FIELDS");
		}
		
		self::$objCAdminForm->Buttons(Array("back_url" => "ticket_holidays_list.php?lang=" . LANG.GetFilterParams("filter_", false)));
		self::$objCAdminForm->arParams["FORM_ACTION"] = $APPLICATION->GetCurPage() . "?lang=" . LANG . GetFilterParams("filter_");
		self::$objCAdminForm->Show();
	
	}
	
	static function ArrSLAinObj($arr)
	{
		//self::$postHolidaysSlaFields = new CSupportTableFields(CSupportHolidays::$sla2holidays, CSupportTableFields::C_Table);
		self::$postHolidaysSlaFields->RemoveExistingRows();
		foreach($arr as $k => $v)
		{
			self::$postHolidaysSlaFields->AddRow();
			self::$postHolidaysSlaFields->HOLIDAYS_ID = self::$id;
			self::$postHolidaysSlaFields->SLA_ID = $v;
		}
	}
	
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
IncludeModuleLangFile(__FILE__);

CSupportPage::doActions();

if(CSupportPage::$needShowInterface)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	CSupportPage::Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

?>
