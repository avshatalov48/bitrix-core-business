<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

set_time_limit(0);

$errorMessage = "";
$okMessage = "";
$bVarsFromForm = false;

$arBaseLangCurrencies = array();

$arPossibleActions = array(
	"U" => GetMessage("SAC_ACTION_CALC_PAY"),
	"P" => GetMessage("SAC_ACTION_CALC_MARK"),
	"F" => GetMessage("SAC_ACTION_CALC")
);

if ($REQUEST_METHOD=="GET" && $Update <> '' && $saleModulePermissions>="W" && check_bitrix_sessid())
{
	if ($SUM_TODO == '' || !array_key_exists($SUM_TODO, $arPossibleActions))
		$errorMessage = GetMessage("SAC_ERROR_NO_ACTION");

	if ($errorMessage == '')
	{
		if ($curLoadSessID == '')
			$curLoadSessID = "CLS".time();

		$max_execution_time = intval($max_execution_time);
		$numAffiliatesCalc = intval($numAffiliatesCalc);
		$numAffiliatesPay = intval($numAffiliatesPay);

		$arFilter = array(
			"ACTIVE" => "Y",
		);

		$arAffiliateID = array();
		if (isset($OID) && is_array($OID))
		{
			$countOid = count($OID);
			for ($i = 0; $i < $countOid; $i++)
			{
				$OID[$i] = intval($OID[$i]);
				if ($OID[$i] > 0)
					$arAffiliateID[] = $OID[$i];
			}

			if (count($arAffiliateID) > 0)
				$arFilter["@ID"] = $arAffiliateID;
		}

		if (!isset($affiliates_calculated))
			$affiliates_calculated = 0;
		$affiliates_calculated = intval($affiliates_calculated);

		$bAllAffiliatesCalc = True;

		if ($affiliates_calculated <= 0)
		{
			$arFilterTmp = $arFilter;

			$arFilterTmp["ORDER_ALLOW_DELIVERY"] = "Y";
			if ($DATE_CALC_FROM <> '')
				$arFilterTmp[">=ORDER_DATE_ALLOW_DELIVERY"] = $DATE_CALC_FROM;

			if ($DATE_CALC_TO == '')
				$DATE_CALC_TO = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset());
			if ($DATE_CALC_TO <> '')
				$arFilterTmp["<ORDER_DATE_ALLOW_DELIVERY"] = $DATE_CALC_TO;
			if ($DATE_PLAN_TO == '')
				$DATE_PLAN_TO = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset());

			$LAST_AFFILIATE_ID = intval($LAST_AFFILIATE_ID);
			if ($LAST_AFFILIATE_ID > 0)
				$arFilterTmp[">ID"] = $LAST_AFFILIATE_ID;

			$dbAffiliates = CSaleAffiliate::GetList(
				array("ID" => "ASC"),
				$arFilterTmp,
				array(
					"ID",
					"SITE_ID",
					"USER_ID",
					"AFFILIATE_ID",
					"PLAN_ID",
					"ACTIVE",
					"TIMESTAMP_X",
					"DATE_CREATE",
					"PAID_SUM",
					"APPROVED_SUM",
					"PENDING_SUM",
					"ITEMS_NUMBER",
					"ITEMS_SUM",
					"LAST_CALCULATE",
					"MAX" => "ORDER_ID",
					"FIX_PLAN"
				)
			);
			while ($arAffiliates = $dbAffiliates->Fetch())
			{
				$errorMessageTmp = "";
				if (!CSaleAffiliate::CalculateAffiliate($arAffiliates, $DATE_CALC_FROM, $DATE_CALC_TO, $DATE_PLAN_FROM, $DATE_PLAN_TO))
				{
					$errorMessageTmp .= str_replace("#ID#", $arAffiliates["ID"], GetMessage("SAC_AFFILIATE_N"));
					if ($ex = $APPLICATION->GetException())
						$errorMessageTmp .= $ex->GetString();
					else
						$errorMessageTmp .= GetMessage("SAC_ERROR_CALC_AFFILIATE");
					$errorMessageTmp .= "<br>";
				}

				$LAST_AFFILIATE_ID = $arAffiliates["ID"];
				$numAffiliatesCalc++;

				if ($errorMessageTmp <> '')
					$errorMessage .= $errorMessageTmp;

				if ($max_execution_time > 0 && (getmicrotime()-START_EXEC_TIME) > $max_execution_time)
				{
					$bAllAffiliatesCalc = False;
					break;
				}
			}
		}

		if ($bAllAffiliatesCalc)
			$affiliates_calculated = 1;

		$bAllAffiliatesPay = True;

		if ($affiliates_calculated > 0 && $SUM_TODO != "F")
		{
			$arFilterTmp = $arFilter;
			$LAST_AFFILIATE_ID1 = intval($LAST_AFFILIATE_ID1);
			if ($LAST_AFFILIATE_ID1 > 0)
				$arFilterTmp[">ID"] = $LAST_AFFILIATE_ID1;

			$dbAffiliates = CSaleAffiliate::GetList(
				array("ID" => "ASC"),
				$arFilterTmp,
				false,
				false,
				array(
					"ID",
					"SITE_ID",
					"USER_ID",
					"AFFILIATE_ID",
					"PLAN_ID",
					"ACTIVE",
					"TIMESTAMP_X",
					"DATE_CREATE",
					"PAID_SUM",
					"APPROVED_SUM",
					"PENDING_SUM",
					"ITEMS_NUMBER",
					"ITEMS_SUM",
					"LAST_CALCULATE"
				)
			);
			while ($arAffiliates = $dbAffiliates->Fetch())
			{
				$errorMessageTmp = "";

				$paySum = 0;
				if (!CSaleAffiliate::PayAffiliate($arAffiliates["ID"], $SUM_TODO, $paySum))
				{
					$errorMessageTmp .= str_replace("#ID#", $arAffiliates["ID"], GetMessage("SAC_AFFILIATE_N"));
					//if ($ex = $APPLICATION->GetException())
					//	$errorMessageTmp .= $ex->GetString()."<br>";
					//else
					$errorMessageTmp .= GetMessage("SAC_ERROR_PAY_AFFILIATE")."<br>";
				}

				$LAST_AFFILIATE_ID1 = $arAffiliates["ID"];
				if ($paySum > 0)
					$numAffiliatesPay++;

				if ($errorMessageTmp <> '')
					$errorMessage .= $errorMessageTmp;

				if ($max_execution_time > 0 && (getmicrotime()-START_EXEC_TIME) > $max_execution_time)
				{
					$bAllAffiliatesPay = False;
					break;
				}
			}
		}

		if (!$bAllAffiliatesCalc || !$bAllAffiliatesPay)
		{
			$_SESSION[$curLoadSessID]["ERROR_MESSAGE"] .= $errorMessage;

			$urlParams = "Update=".UrlEncode($Update)."&affiliates_calculated=".UrlEncode($affiliates_calculated)."&DATE_CALC_FROM=".UrlEncode($DATE_CALC_FROM)."&DATE_CALC_TO=".UrlEncode($DATE_CALC_TO)."&DATE_PLAN_FROM=".UrlEncode($DATE_PLAN_FROM)."&DATE_PLAN_TO=".UrlEncode($DATE_PLAN_TO)."&SUM_TODO=".UrlEncode($SUM_TODO)."&LAST_AFFILIATE_ID=".$LAST_AFFILIATE_ID."&LAST_AFFILIATE_ID1=".$LAST_AFFILIATE_ID1."&max_execution_time=".$max_execution_time."&numAffiliatesCalc=".$numAffiliatesCalc."&numAffiliatesPay=".$numAffiliatesPay."&curLoadSessID=".UrlEncode($curLoadSessID)."&".bitrix_sessid_get();
			$countarAffiliate = count($arAffiliateID);
			for ($i = 0; $i < $countarAffiliate; $i++)
				$urlParams .= "&OID[]=".$arAffiliateID[$i];
			?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
			<html>
			<head>
				<title><?echo GetMessage("SAC_STEP_TITLE")?></title>
			</head>
			<body>
				<?echo GetMessage("SAC_STEP_AUTO_HINT")?><br>
				<?echo GetMessage("SAC_STEP_AUTO_HINT1")?>
				<a href="<?echo $APPLICATION->GetCurPage() ?>?lang=<?echo LANG; ?>&<?echo $urlParams ?>"><?echo GetMessage("SAC_STEP_AUTO_HINT2")?></a><br>
				<script language="JavaScript" type="text/javascript">
				<!--
				function DoNext()
				{
					window.location="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&<?echo $urlParams ?>";
				}
				setTimeout('DoNext()', 2000);
				//-->
				</script>
			</body>
			</html><?

			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");

			die();
		}
		else
		{
			$okMessage .= str_replace("#NUM#", $numAffiliatesCalc, GetMessage("SAC_SUCCESS1"));
			$okMessage .= str_replace("#NUM#", $numAffiliatesPay, GetMessage("SAC_SUCCESS2"));
		}

		$_SESSION[$curLoadSessID]["OK_MESSAGE"] .= $okMessage;
		$_SESSION[$curLoadSessID]["ERROR_MESSAGE"] .= $errorMessage;

		$urlParams = "DATE_CALC_FROM=".UrlEncode($DATE_CALC_FROM)."&DATE_CALC_TO=".UrlEncode($DATE_CALC_TO)."&DATE_PLAN_FROM=".UrlEncode($DATE_PLAN_FROM)."&DATE_PLAN_TO=".UrlEncode($DATE_PLAN_TO)."&SUM_TODO=".UrlEncode($SUM_TODO)."&max_execution_time=".$max_execution_time."&numAffiliatesCalc=".$numAffiliatesCalc."&numAffiliatesPay=".$numAffiliatesPay."&curLoadSessID=".UrlEncode($curLoadSessID);
		$countArAffiliate = count($arAffiliateID);
		for ($i = 0; $i < $countArAffiliate; $i++)
			$urlParams .= "&OID[]=".$arAffiliateID[$i];

		LocalRedirect("/bitrix/admin/sale_affiliate_calc.php?lang=".LANG."&".$urlParams);
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SAC_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("SAC_AFFILIATE_LIST"),
				"LINK" => "/bitrix/admin/sale_affiliate.php?lang=".LANG.GetFilterParams("filter_"),
				"ICON" => "btn_list"
			)
	);

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if ($curLoadSessID <> '' && array_key_exists($curLoadSessID, $_SESSION) && is_array($_SESSION[$curLoadSessID]) && array_key_exists("ERROR_MESSAGE", $_SESSION[$curLoadSessID]))
	$errorMessage = $_SESSION[$curLoadSessID]["ERROR_MESSAGE"].$errorMessage;
if ($curLoadSessID <> '' && array_key_exists($curLoadSessID, $_SESSION) && is_array($_SESSION[$curLoadSessID]) && array_key_exists("OK_MESSAGE", $_SESSION[$curLoadSessID]))
	$okMessage = $_SESSION[$curLoadSessID]["OK_MESSAGE"].$okMessage;

if ($errorMessage <> '')
{
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SAC_ERROR_TITLE"), "HTML"=>true));
}
elseif ($okMessage <> '')
{
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$okMessage, "TYPE"=>"OK", "MESSAGE"=>GetMessage("SAC_SUCCESS_TITLE"), "HTML"=>true));
}
?>

<form method="GET" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SAC_TITLE"), "ICON" => "sale", "TITLE" => GetMessage("SAC_CALC_SETUP")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td width="40%" valign="top"><?echo GetMessage("SAC_AFFILIATES")?></td>
		<td width="60%" valign="top">
			<?
			$bFilteredList = False;
			if (isset($OID) && is_array($OID))
			{
				$arAffiliateID = array();
				$countOid = count($OID);
				for ($i = 0; $i < $countOid; $i++)
				{
					$OID[$i] = intval($OID[$i]);
					if ($OID[$i] > 0)
						$arAffiliateID[] = $OID[$i];
				}

				if (count($arAffiliateID) > 0)
				{
					$dbAffiliates = CSaleAffiliate::GetList(
						array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC", "USER_LOGIN" => "ASC"),
						array("@ID" => $arAffiliateID),
						false,
						false,
						array("ID", "USER_ID", "SITE_ID", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME")
					);
					if ($arAffiliates = $dbAffiliates->Fetch())
					{
						$bFilteredList = True;
						?>
						<select name="OID[]" multiple size="5">
							<?
							do
							{
								?><option value="<?= intval($arAffiliates["ID"]) ?>" selected><?= htmlspecialcharsex("[".$arAffiliates["ID"]."] ".$arAffiliates["USER_NAME"]." ".$arAffiliates["USER_LAST_NAME"]." (".$arAffiliates["USER_LOGIN"].")") ?></option><?
							}
							while ($arAffiliates = $dbAffiliates->Fetch());
							?>
						</select>
						<?
					}
				}
			}

			if (!$bFilteredList)
			{
				echo GetMessage("SAC_ALL_AFFILIATES");
				echo "<br>";
				echo str_replace("#LINK2#", "</a>", str_replace("#LINK1#", "<a href=\"/bitrix/admin/sale_affiliate.php?lang=".LANG."\">", GetMessage("SAC_ALL_AFFILIATES_HINT")));
			}
			?>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("SAC_CALC_PERIOD")?></td>
		<td width="60%" valign="top">
			<?echo CalendarPeriod("DATE_CALC_FROM", $DATE_CALC_FROM, "DATE_CALC_TO", $DATE_CALC_TO, "form1", "N")?><br>
			<small><?echo GetMessage("SAC_CALC_PERIOD_HINT")?><br><?echo GetMessage("SAC_CALC_PERIOD_HINT1")?></small>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("SAC_PLAN_PERIOD_HINT1")?><br><small><?echo GetMessage("SAC_PLAN_PERIOD_HINT2")?></small></td>
		<td width="60%" valign="top">
			<?echo CalendarPeriod("DATE_PLAN_FROM", $DATE_PLAN_FROM, "DATE_PLAN_TO", $DATE_PLAN_TO, "form1", "N")?><br>
			<small><?echo GetMessage("SAC_PLAN_PERIOD_HINT3")?><br><?echo GetMessage("SAC_PLAN_PERIOD_HINT4")?></small>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("SAC_ACTION")?></td>
		<td width="60%" valign="top">
			<?
			foreach ($arPossibleActions as $key => $value)
			{
				?>
				<input type="radio" name="SUM_TODO" id="ID_SUM_TODO_<?= $key ?>" value="<?= $key ?>"<?if ($SUM_TODO == $key || $SUM_TODO == '' && $key == "U") echo " checked";?>>
				<label for="ID_SUM_TODO_<?= $key ?>"><?= $value ?></label><br>
				<?
			}
			?>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("SAC_STEP")?></td>
		<td width="60%" valign="top">
			<input type="text" name="max_execution_time" value="<?= intval($max_execution_time) ?>" size="5"> <?echo GetMessage("SAC_SEC")?><br>
			<small><?echo GetMessage("SAC_SEC_0")?></small>
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(false);
?>

	<input<?= ($saleModulePermissions >= "W") ? "" : " disabled" ?> type="submit" name="apply" value="<?echo GetMessage("SAC_DO_CALC")?>" title="<?echo GetMessage("SAC_DO_CALC_DESCR")?>" class="adm-btn-save">
	<input<?= ($saleModulePermissions >= "W") ? "" : " disabled" ?> type="reset" name="dontsave" value="<?echo GetMessage("SAC_RESET")?>" title="<?echo GetMessage("SAC_RESET")?>">

<?
$tabControl->End();
?>

</form>

<?echo BeginNote();?>
<?echo GetMessage("SAC_NOTE1")?><br><br>
<?echo GetMessage("SAC_NOTE2")?><br><br>
<?echo GetMessage("SAC_NOTE3")?><br><br>
<?echo GetMessage("SAC_NOTE4")?><br><br>
<?echo GetMessage("SAC_NOTE5")?>
<?echo EndNote();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>