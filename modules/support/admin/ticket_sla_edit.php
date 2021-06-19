<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$ID = intval($ID);

//CTicket::GetRoles($isDemo, $isSupportClient, $isSupportTeam, $isAdmin, $isAccess, $USER_ID, $CHECK_RIGHTS);
//if(!$isAdmin && !$isDemo) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";

if($bAdmin!="Y" && $bDemo!="Y")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$LIST_URL = "/bitrix/admin/ticket_sla_list.php";
$TABLE_NAME = "b_ticket_sla";
$EDIT_URL = $APPLICATION->GetCurPage();


function CheckTime($minute_from, $minute_till)
{
	if (mb_strlen($_POST["MINUTE_FROM_".$i."_".$j]) < 0 && mb_strlen($_POST["MINUTE_TILL_".$i."_".$j]) < 0)
		return false;
		
	$arFrom = explode(':', $minute_from);
	$minute_from = intval($arFrom[0]*60 + $arFrom[1]);

	$arTill = explode(':', $minute_till);
	$minute_till = intval($arTill[0]*60 + $arTill[1]);
	
	if ($minute_from >= $minute_till)
		return false;
		
	if ($minute_from > 1440 || $minute_till > 1440 )
		return false;
	
	return true;
}

function CheckShedule($shedule)
{
	$iMaxMinute = 0;
	foreach ($shedule as $key => $value)
	{
		$arFrom = explode(':', $value['MINUTE_FROM']);
		$minute_from = intval($arFrom[0]*60 + $arFrom[1]);

		$arTill = explode(':', $value['MINUTE_TILL']);
		$minute_till = intval($arTill[0]*60 + $arTill[1]);
		
		if ($iMaxMinute < $minute_from)
			echo $iMaxMinute = $minute_till;
		else 
			return false;
	}
	
	return true;
}

/***************************************************************************
						Обработка GET | POST
****************************************************************************/

$arrUSER = array();
$message = false;

if (($save <> '' || $apply <> '') && $bAdmin=="Y" && $_SERVER["REQUEST_METHOD"]=="POST" && check_bitrix_sessid())
{
	$arFields = array("arSITES"=>Array(), "arGROUPS"=>Array(), "arMARKS"=>Array(), "arCATEGORIES"=>Array(), "arCRITICALITIES"=>Array());

	foreach ($_POST as $key => $value)
	{
		$arFields[$key] = $value;
	}

	/*
	for($i=0; $i<=6; $i++)
	{
		if (strlen($_POST["OPEN_TIME_".$i])>0)
		{
			$arFields["arSHEDULE"][$i] = array("OPEN_TIME" => $_POST["OPEN_TIME_".$i]);
			if ($_POST["OPEN_TIME_".$i]=="CUSTOM")
			{
				if (intval(${"custom_time_".$i."_counter"})>0)
				{
					$bExist = false;
					for($j=0; $j<=${"custom_time_".$i."_counter"}; $j++)
					{
						// проверяем, чтобы время окончания было позже начала
						if(CheckTime($_POST["MINUTE_FROM_".$i."_".$j], $_POST["MINUTE_TILL_".$i."_".$j]))
						{
							$bExist = true;
							$arFields["arSHEDULE"][$i]["CUSTOM_TIME"][] = array(
								"MINUTE_FROM"	=> $_POST["MINUTE_FROM_".$i."_".$j],
								"MINUTE_TILL"	=> $_POST["MINUTE_TILL_".$i."_".$j],
							);
						}
					}
					if ($bExist) 
					{
						// проверяем корректно ли заданы промежутки времени
						if (!CheckShedule($arFields["arSHEDULE"][$i]["CUSTOM_TIME"])) 
						{
							unset($arFields["arSHEDULE"][$i]["CUSTOM_TIME"]);
							$bExist = false;
						}
					}
					if (!$bExist) $arFields["arSHEDULE"][$i]["OPEN_TIME"] = "CLOSED";
				}
			}
		}
	}*/

	$ID = CTicketSLA::Set($arFields, $ID);
	if (intval($ID) <= 0)
	{
		if($e = $APPLICATION->GetException())
		{
			$message = new CAdminMessage(GetMessage("SUP_ERROR"), $e);
		}
		else
		{
		//if($obException = $APPLICATION->GetException()) $strError = $obException->GetString()."<br>";
		//if (strlen($strError)<=0)
		//{
		}
	}
	else
	{
		if ($save <> '')
		{
			LocalRedirect($LIST_URL."?lang=".LANGUAGE_ID);
		}
		else
		{
			LocalRedirect($EDIT_URL."?ID=".$ID."&lang=".LANGUAGE_ID."&tabControl_active_tab=".urlencode($tabControl_active_tab));
		}
	}
	$DB->PrepareFields($TABLE_NAME);
}

$arrSites = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch()) 
	$arrSites[$ar["ID"]] = $ar;

$arCategory = $arMark = $arCriticality = array();
$rs = CTicketDictionary::GetList("s_dropdown", '', array("TYPE" => "C"));
while($ar = $rs->Fetch()) $arCategory[] = $ar;
$rs = CTicketDictionary::GetList("s_dropdown", '', array("TYPE" => "K"));
while($ar = $rs->Fetch()) $arCriticality[] = $ar;
$rs = CTicketDictionary::GetList("s_dropdown", '', array("TYPE" => "M"));
while($ar = $rs->Fetch()) $arMark[] = $ar;

$rs = CTicketSLA::GetByID($ID);
if (!$rs || !$rs->ExtractFields())
{
	$ID=0; 
	$str_PRIORITY = 100;
	//for($i=0;$i<=6;$i++) $arSHEDULE[$i]["OPEN_TIME"] = "24H";
}
else
{
	$arGROUPS = CTicketSLA::GetGroupArray($ID);
	//$arSHEDULE = CTicketSLA::GetSheduleArray($ID);
	$arSITES = CTicketSLA::GetSiteArray($ID);
	$arCATEGORIES = CTicketSLA::GetCategoryArray($ID);
	$arCRITICALITIES = CTicketSLA::GetCriticalityArray($ID);
	$arMARKS = CTicketSLA::GetMarkArray($ID);
}

if ($message)
	$DB->InitTableVarsForEdit($TABLE_NAME, "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("SUP_PAGE_TITLE_EDIT_RECORD", array("#ID#"=>$ID)) : GetMessage("SUP_PAGE_TITLE_NEW_RECORD")));

/***************************************************************************
									HTML форма
****************************************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"ICON"	=> "btn_list",
		"TEXT"	=> GetMessage("SUP_RECORD_LIST"), 
		"LINK"	=> $LIST_URL."?lang=".LANGUAGE_ID
	)
);

if(intval($ID)>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("SUP_NEW_RECORD"),
		"LINK"	=> $EDIT_URL."?lang=".LANGUAGE_ID
	);

	if ($ID>1 && $bAdmin=="Y")
	{
		$aMenu[] = array(
		"ICON"	=> "btn_delete",
			"TEXT"	=> GetMessage("SUP_DELETE_RECORD"), 
			"LINK"	=> "javascript:if(confirm('".GetMessage("SUP_DELETE_RECORD_CONFIRM")."'))window.location='".$LIST_URL."?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		);
	}
}
//echo ShowSubMenu($aMenu);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
{
	echo $message->Show();
}
?>


<script type="text/javascript">
<!--
function OnSelectAll(all_checked, name)
{
	//var arCheckbox = document.form1.all(name);
	var arCheckbox = document.form1[name];
	var disabled;
	if(!arCheckbox) return;
	if (!all_checked) disabled = false; else disabled = true;
	if(arCheckbox.length>0)
	{
		for(i=0; i<arCheckbox.length; i++)
		{
			if( arCheckbox[i].getAttribute('value') != 0 && arCheckbox[i].getAttribute('value') != 'ALL')
			{
				arCheckbox[i].disabled = disabled;
			}
		}
	}
	else
	{
		arCheckbox.checked = disabled;
	}

	OnSiteClick();
}

function in_array(needle, haystack)
{
	for(k=0; k<haystack.length; k++) if (needle==haystack[k]) return true;
	return false;
}

var arCriticality = Array();
var arMark = Array();
var arCategory = Array();
<?
foreach ($arrSites as $sid => $arrS):
	
	$rs = CTicketDictionary::GetDropDown("C", $sid);
	$arr = array(); while($ar = $rs->Fetch()) $arr[] = $ar;
	if (count($arr)>0):
	?>
		arCategory["<?=$sid?>"]=Array(<?
			echo "0"; 
			foreach($arr as $ar) echo ",".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]));
			?>);
			<?
	endif;	
	
	$rs = CTicketDictionary::GetDropDown("M", $sid);
	$arr = array(); while($ar = $rs->Fetch()) $arr[] = $ar;
	if (count($arr)>0):
	?>
		arMark["<?=$sid?>"]=Array(<?
			echo "0";
			foreach($arr as $ar) echo ",".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]));
			?>);
			<?
	endif;

	$rs = CTicketDictionary::GetDropDown("K", $sid);
	$arr = array(); while($ar = $rs->Fetch()) $arr[] = $ar;
	if (count($arr)>0):
	?>
		arCriticality["<?=$sid?>"]=Array(<?
			echo "0";
			foreach($arr as $ar) echo ",".addslashes(htmlspecialcharsbx($ar["REFERENCE_ID"]));
			?>);
			<?
	endif;

endforeach;
?>

function OnSiteClick()
{
	var obSites = document.getElementsByName('arSITES[]');
	var obCategories = document.getElementsByName('arCATEGORIES[]');
	var obCriticalities = document.getElementsByName('arCRITICALITIES[]');
	var obMarks = document.getElementsByName('arMARKS[]');
	var arShow = [];
	var sid, obDiv, i, j;

	for(i=0;i<obSites.length;i++)
	{
		if (obSites[i].checked)
		{
			sid = obSites[i].value;
			for(j=0;j<obCategories.length;j++)
			{
				if (sid=="ALL" || (arCategory[sid] && in_array(obCategories[j].value, arCategory[sid])))
					if (!arShow || !in_array(obCategories[j].value, arShow))
						arShow[arShow.length] = obCategories[j].value;
			}
			for(j=0;j<obCriticalities.length;j++)
			{
				if (sid=="ALL" || (arCriticality[sid] && in_array(obCriticalities[j].value, arCriticality[sid])))
					if (!arShow || !in_array(obCriticalities[j].value, arShow))
						arShow[arShow.length] = obCriticalities[j].value;
			}
			for(j=0;j<obMarks.length;j++)
			{
				if (sid=="ALL" || (arMark[sid] && in_array(obMarks[j].value, arMark[sid])))
					if (!arShow || !in_array(obMarks[j].value, arShow))
						arShow[arShow.length] = obMarks[j].value;
			}
		}
	}
	for(j=0;j<obCategories.length;j++)
	{
		obDiv = document.getElementById("category_"+obCategories[j].value);
		if (arShow && in_array(obCategories[j].value, arShow)) obDiv.style.display = "block"; else obDiv.style.display = "none";
	}
	for(j=0;j<obCriticalities.length;j++)
	{
		obDiv = document.getElementById("criticality_"+obCriticalities[j].value);
		if (arShow && in_array(obCriticalities[j].value, arShow)) obDiv.style.display = "block"; else obDiv.style.display = "none";
	}
	for(j=0;j<obMarks.length;j++)
	{
		obDiv = document.getElementById("mark_"+obMarks[j].value);
		if (arShow && in_array(obMarks[j].value, arShow)) obDiv.style.display = "block"; else obDiv.style.display = "none";
	}
}
//-->
</script>

<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
	$aTabs = array();
	$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SUP_RECORD"), "ICON"=>"ticket_edit", "TITLE"=> GetMessage("SUP_RECORD_S"));
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("SUP_USER_GROUPS_S"), "ICON"=>"ticket_edit", "TITLE"=> GetMessage("SUP_USER_ACCESS"));
	//$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("SUP_SHEDULE_S"), "ICON"=>"ticket_edit", "TITLE"=> GetMessage("SUP_SHEDULE"));
	
	$OLD_FUNCTIONALITY = COption::GetOptionString( "support", "SUPPORT_OLD_FUNCTIONALITY", "Y" );
	if( $OLD_FUNCTIONALITY == "Y" ) $aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("SUP_WHAT_ACCESSIBLE_S"), "ICON"=>"ticket_edit", "TITLE"=> GetMessage("SUP_WHAT_ACCESSIBLE"));
	else $aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("SUP_WHAT_ACCESSIBLE_S_N"), "ICON"=>"ticket_edit", "TITLE"=> GetMessage("SUP_WHAT_ACCESSIBLE_N"));
	
	$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>

	<?if ($ID>0):?>
	<?if ($str_DATE_CREATE <> ''):?>
	<tr valign="top"> 
		<td align="right"><?=GetMessage("SUP_CREATED")?>:</td>
		<td><?=$str_DATE_CREATE_F?><?
		if (intval($str_CREATED_USER_ID)>0) :
			if (!in_array($str_CREATED_USER_ID, array_keys($arrUSER)))
			{
				$rsUser = CUser::GetByID($str_CREATED_USER_ID);
				if ($arUser = $rsUser->Fetch()) $arrUSER[$arUser["ID"]] = $arUser;
			}
			else $arUser = $arrUSER[$str_CREATED_USER_ID];
			echo "&nbsp;&nbsp;[<a title=\"".GetMessage("MAIN_ADMIN_MENU_EDIT")."\" href=\"/bitrix/admin/user_edit.php?ID=".$str_CREATED_USER_ID."\">".$str_CREATED_USER_ID."</a>]&nbsp;(".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
		endif;
		if (intval($str_CREATED_GUEST_ID)>0 && IsModuleInstalled("statistic")) :
			echo "&nbsp;[<a href=\"/bitrix/admin/guest_list.php?find_id=".$str_CREATED_GUEST_ID."&find_id_exact_match=Y&set_filter=Y\">".$str_CREATED_GUEST_ID."</a>]";
		endif;
		?></td>
	</tr>
	<?endif;?>
	<?if ($str_DATE_MODIFY <> ''):?>
	<tr valign="top"> 
		<td align="right"><?=GetMessage("SUP_MODIFIED")?>:</td>
		<td><?=$str_DATE_MODIFY_F?><?
		if (intval($str_MODIFIED_USER_ID)>0) :
			if (!in_array($str_MODIFIED_USER_ID, array_keys($arrUSER)))
			{
				$rsUser = CUser::GetByID($str_MODIFIED_USER_ID);
				if ($arUser = $rsUser->Fetch()) $arrUSER[$arUser["ID"]] = $arUser;
			}
			else $arUser = $arrUSER[$str_MODIFIED_USER_ID];
			echo "&nbsp;&nbsp;[<a title=\"".GetMessage("MAIN_ADMIN_MENU_EDIT")."\" href=\"/bitrix/admin/user_edit.php?ID=".$str_MODIFIED_USER_ID."\">".$str_MODIFIED_USER_ID."</a>]&nbsp;(".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
		endif;
		if (intval($str_MODIFIED_GUEST_ID)>0 && IsModuleInstalled("statistic")) :
			echo "&nbsp;[<a href=\"/bitrix/admin/guest_list.php?find_id=".$str_MODIFIED_GUEST_ID."&find_id_exact_match=Y&set_filter=Y\">".$str_MODIFIED_GUEST_ID."</a>]";
		endif;
		?></td>
	</tr>
	<?endif;?>
	<?endif;?>

	<tr class="adm-detail-required-field"> 
		<td width="40%" align="right"><?=GetMessage("SUP_NAME")?>:</td>
		<td width="60%"><input type="text" maxlength="255" name="NAME" size="50" value="<?=$str_NAME?>"></td>
	</tr>

	<tr valign="top"> 
		<td align="right"><?=GetMessage("SUP_SITE")?>:</td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input name="arSITES[]" type="checkbox" id="all_sites" value="ALL" onclick="OnSelectAll(this.checked, 'arSITES[]')" <?if ((is_array($arSITES) && (in_array('ALL', $arSITES) || count($arSITES) <= 0)) || !is_array($arSITES)) echo "checked"?>></div>
					<div class="adm-list-label"><label for="all_sites">(<?=GetMessage("SUP_ALL_CURRENT_FUTURE")?>)</label></div>
				</div>
			<?
			foreach ($arrSites as $sid => $arrS):
				$checked = ((is_array($arSITES) && in_array($sid, $arSITES)) || ($ID<=0 && $def_site_id==$sid)) ? "checked" : "";
				/*<?=$disabled?>*/
				?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input onClick="OnSiteClick()" type="checkbox" name="arSITES[]" value="<?=htmlspecialcharsex($sid)?>" id="<?=htmlspecialcharsex($sid)?>" class="typecheckbox" <?=$checked?>></div>
					<div class="adm-list-label"><label for="<?=htmlspecialcharsbx($sid)?>"><?echo '[<a title="'.GetMessage("MAIN_ADMIN_MENU_EDIT").'" href="/bitrix/admin/site_edit.php?LID='.htmlspecialcharsbx($sid).'&lang='.LANGUAGE_ID.'">'.htmlspecialcharsex($sid).'</a>]&nbsp;'.htmlspecialcharsex($arrS["NAME"])?></label></div>
				</div>
				<?
			endforeach;
			?></div>
		</td>
	</tr>

	<tr> 
		<td align="right"><?=GetMessage("SUP_PRIORITY")?>:</td>
		<td><input type="text" maxlength="18" name="PRIORITY" size="10" value="<?=$str_PRIORITY?>"></td>
	</tr>

	<tr>  
		<td align="right"><?=GetMessage("SUP_RESPONSE_TIME")?>:</td>
		<td><input type="text" maxlength="18" name="RESPONSE_TIME" size="10" value="<?=$str_RESPONSE_TIME?>">&nbsp;<?
			$arr = array(
				"reference"	=> array(
					GetMessage("SUP_DAYS"), 
					GetMessage("SUP_HOURS"), 
					GetMessage("SUP_MINUTES"), 
					), 
				"reference_id"	=> array("day","hour","minute")
				);
			echo SelectBoxFromArray("RESPONSE_TIME_UNIT", $arr, $str_RESPONSE_TIME_UNIT, "", "");		
		?></td>
	</tr>

	<tr> 
		<td align="right"><?=GetMessage("SUP_WARNING_TIME")?>:</td>
		<td><input type="text" maxlength="18" name="NOTICE_TIME" size="10" value="<?=$str_NOTICE_TIME?>">&nbsp;<?
			$arr = array(
				"reference"	=> array(
					GetMessage("SUP_DAYS"), 
					GetMessage("SUP_HOURS"), 
					GetMessage("SUP_MINUTES"), 
					), 
				"reference_id"	=> array("day","hour","minute")
				);
			echo SelectBoxFromArray("NOTICE_TIME_UNIT", $arr, $str_NOTICE_TIME_UNIT, "", "");?>
		</td>
	</tr>

	<tr> 
		<td align="right"><?=GetMessage("SUP_RESPONSIBLE")?>:</td>
		<td><?
			$arUserList["reference"][] = '';
			$arUserList["reference_id"][] = '';
			$dbTeam = CTicket::GetSupportTeamList();
			while ($arTeam = $dbTeam->Fetch())
			{
				if($arTeam["ACTIVE"] == "Y" || $arTeam["REFERENCE_ID"] == $str_RESPONSIBLE_USER_ID)
				{
					$arUserList["reference"][] = $arTeam["REFERENCE"];
					$arUserList["reference_id"][] = $arTeam["REFERENCE_ID"];
				}
			}
			echo SelectBoxFromArray("RESPONSIBLE_USER_ID", $arUserList, $str_RESPONSIBLE_USER_ID, "", "");?>
		</td>
	</tr>
	
	<tr>
		<td align="right"><?=GetMessage("SUP_SHEDULE_S")?>:</td>
		<td>
		<?
			$arrTimetableList["reference"][] = '';
			$arrTimetableList["reference_id"][] = '';
			$ar = CSupportTimetable::GetList( array(), array() );
			while ($arT = $ar->Fetch())
			{
				$arrTimetableList["reference"][] = $arT["NAME"];
				$arrTimetableList["reference_id"][] = $arT["ID"];
			}

			echo SelectBoxFromArray("TIMETABLE_ID", $arrTimetableList, $str_TIMETABLE_ID, "", "");
		?>
		</td>
	</tr>


	<tr>
		<td align="right"><?=GetMessage('SUP_DEADLINE_SOURCE_BY')?></td>
		<td>
			<?
			$arrDeadlineSourceList = array(
				'reference_id' => array(
					'',
					'DATE_CREATE'
				),
				'reference' => array(
					GetMessage('SUP_DEADLINE_SOURCE_BY_CLIENT_REPLY'),
					GetMessage('SUP_DEADLINE_SOURCE_BY_CREATE_DATE')
				)
			);

			echo SelectBoxFromArray('DEADLINE_SOURCE', $arrDeadlineSourceList, $str_DEADLINE_SOURCE, "", "");
			?>
		</td>
	</tr>
	

	<tr class="heading">
		<td colspan="2"><?=GetMessage("SUP_DESCRIPTION")?></td>
	</tr>

	<tr>
		<td colspan="2" align="center"><textarea style="width:60%; height:150px;" name="DESCRIPTION" wrap="VIRTUAL"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>

	<?$tabControl->BeginNextTab();?>

	<tr>
		<td align="right" valign="top" width="40%"><?=GetMessage("SUP_USER_GROUPS")?>:</td>
		<td width="60%">
			<div class="adm-list"><?
			$rs = CGroup::GetList("sort", "asc");
			$idR = 0;
			while($ar = $rs->Fetch()):
				$arRoles = $APPLICATION->GetUserRoles("support", array(intval($ar["ID"])), "Y", "N");
				if (in_array(CTicket::GetSupportClientRoleID(),$arRoles) || 
					in_array(CTicket::GetSupportTeamRoleID(),$arRoles) || 
					in_array(CTicket::GetDemoRoleID(),$arRoles) || 
					in_array(CTicket::GetAdminRoleID(),$arRoles))
				{
					$idR++;
					echo "<div class=\"adm-list-item\">";
					echo "<div class=\"adm-list-control\">".InputType("checkbox", "arGROUPS[]", $ar["ID"], $arGROUPS, false, "", "", $idR)."</div>".
					"<div class=\"adm-list-label\"><label for=\"$idR\">".htmlspecialcharsbx($ar["NAME"])."</label>".
					" [<a title=\"".GetMessage("MAIN_ADMIN_MENU_EDIT")."\" href=\"/bitrix/admin/group_edit.php?ID=".intval($ar["ID"])."&lang=".LANGUAGE_ID."\">".
					intval($ar["ID"]).
					"</a>]</div></div>";
				}
			endwhile;
			?></div></td>
	</tr>


<?
/*
$tabControl->BeginNextTab();?>


	<script type="text/javascript">
	<!--
	function Copy(i, j)
	{
		var counter = document.getElementById("custom_time_"+i+"_counter");

		if(counter.value==j)
		{
			j++;
			var tbl = document.getElementById("table"+i);
			var cnt = tbl.rows.length;
			var oRow = tbl.insertRow(-1);

			var oCell = oRow.insertCell(0);
			oCell.innerHTML = '<input type="text" size="5" id="MINUTE_FROM_'+i+'_'+j+'" name="MINUTE_FROM_'+i+'_'+j+'" value="'+document.getElementById("MINUTE_FROM_"+i+"_"+(j-1)).value+'">';

			var oCell = oRow.insertCell(1);
			oCell.noWrap = true;
			oCell.innerHTML = '<nobr>&nbsp;-&nbsp;</nobr>';

			var oCell = oRow.insertCell(2);
			oCell.innerHTML = '<input type="text" size="5" id="MINUTE_TILL_'+i+'_'+j+'" name="MINUTE_TILL_'+i+'_'+j+'" value="'+document.getElementById("MINUTE_TILL_"+i+"_"+(j-1)).value+'">';

			var oCell = oRow.insertCell(3);
			oCell.innerHTML = '<a title="<?=GetMessage("MAIN_ADMIN_MENU_COPY")?>" href="javascript: Copy('+i+', '+j+')"><img src="/bitrix/images/support/copy.gif" width="15" height="15" border=0 hspace="2" alt="<?=GetMessage("MAIN_ADMIN_MENU_COPY")?>"></a>';

			counter.value = j;
		}
		else
		{
			document.getElementById("MINUTE_FROM_"+i+"_"+(j+1)).value = document.getElementById("MINUTE_FROM_"+i+"_"+j).value;
			document.getElementById("MINUTE_TILL_"+i+"_"+(j+1)).value = document.getElementById("MINUTE_TILL_"+i+"_"+j).value;
		}
	}
	//-->
	</script>

	<tr>
		<td colspan="2" align="center">
			<table border="0" cellspacing="0" cellpadding="0" width="50%" class="internal">
				<?
				for($i=0; $i<=6; $i++):

					//$top_line = ($i==0) ? "tablelinetop" : "";

					if ($message)
					{
						$arSHEDULE[$i]["OPEN_TIME"] = $_POST["OPEN_TIME_".$i];
					}
					
				?>
				<tr valign="top">
					<td class="heading"><b><?=GetMessage("SUP_WEEKDAY_".$i)?></b></td>
					<td align="center" nowrap><?=InputType("radio", "OPEN_TIME_".$i, "24H", $arSHEDULE[$i]["OPEN_TIME"])?>&nbsp;<?=GetMessage("SUP_24H")?></td>
					<td align="center" nowrap><?=InputType("radio", "OPEN_TIME_".$i, "CLOSED", $arSHEDULE[$i]["OPEN_TIME"])?>&nbsp;<?=GetMessage("SUP_CLOSED")?></td>
					<td align="center" nowrap><?=InputType("radio", "OPEN_TIME_".$i, "CUSTOM", $arSHEDULE[$i]["OPEN_TIME"])?>&nbsp;<?=GetMessage("SUP_CUSTOM")?></td>
					<td align="center" nowrap>
						<table border="0" cellspacing="0" cellpadding="0" width="100%" id="table<?=$i?>">
							<?
							$j=0;
							$max_empty = 2;
							if ($arSHEDULE[$i]["OPEN_TIME"]=="CUSTOM" && is_array($arSHEDULE[$i]["CUSTOM_TIME"])):
								$max_empty = 1;
								foreach($arSHEDULE[$i]["CUSTOM_TIME"] as $arTime):
									if ($message)
									{
										$arTime["FROM"] = ${"MINUTE_FROM_".$i."_".$j};
										$arTime["TILL"] = ${"MINUTE_TILL_".$i."_".$j};
									}
							?>
							<tr>
								<td><input type="text" id="MINUTE_FROM_<?=$i?>_<?=$j?>" name="MINUTE_FROM_<?=$i?>_<?=$j?>" size="5" value="<?=htmlspecialcharsbx($arTime["FROM"])?>"></td>
								<td align="center" valign="middle" nowrap><nobr>&nbsp;-&nbsp;</nobr></td>
								<td><input type="text" name="MINUTE_TILL_<?=$i?>_<?=$j?>" id="MINUTE_TILL_<?=$i?>_<?=$j?>" size="5" value="<?=htmlspecialcharsbx($arTime["TILL"])?>"></td>
								<td><a title="<?=GetMessage("MAIN_ADMIN_MENU_COPY")?>" href="javascript: Copy(<?=$i?>, <?=$j?>)"><img src="/bitrix/images/support/copy.gif" width="15" height="15" border=0 hspace="2" alt="<?=GetMessage("MAIN_ADMIN_MENU_COPY")?>"></a></td>
							</tr>
							<?
									$j++;
								endforeach;
							endif;
							for ($p=0; $p<=($max_empty-1); $p++) : 
							?>
							<tr>
								<td><input type="text" name="MINUTE_FROM_<?=$i?>_<?=$j?>" id="MINUTE_FROM_<?=$i?>_<?=$j?>" size="5" value="<?=htmlspecialcharsbx(${"MINUTE_FROM_".$i."_".$j})?>"></td>
								<td align="center" valign="middle" nowrap><nobr>&nbsp;-&nbsp;</nobr></td>
								<td><input type="text" name="MINUTE_TILL_<?=$i?>_<?=$j?>" id="MINUTE_TILL_<?=$i?>_<?=$j?>" size="5" value="<?=htmlspecialcharsbx(${"MINUTE_TILL_".$i."_".$j})?>"></td>
								<td><a title="<?=GetMessage("MAIN_ADMIN_MENU_COPY")?>" href="javascript: Copy(<?=$i?>, <?=$j?>)"><img src="/bitrix/images/support/copy.gif" width="15" height="15" border=0 hspace="2" alt="<?=GetMessage("MAIN_ADMIN_MENU_COPY")?>"></a></td>
							</tr>
							<?
								$j++;
							endfor;
							?>
							<input type="hidden" name="custom_time_<?=$i?>_counter" id="custom_time_<?=$i?>_counter" value="<?=$j-1?>">
						</table>
					</td>
				</tr>
				<?endfor;?>
			</table>
		</td>
	</tr>


	<?*/
	
	$tabControl->BeginNextTab();
	?>

			<tr>
				<td valign="top" width="40%"><?=GetMessage("SUP_CATEGORY")?>:</td>
				<td width="60%" nowrap>
					<div class="adm-list">
						<div id="category_0" class="adm-list-item">
							<div class="adm-list-control"><input name="arCATEGORIES[]" type="checkbox" value="0" id="category_chbox_0" onclick="OnSelectAll(this.checked, 'arCATEGORIES[]')" <?if ((is_array($arCATEGORIES) && (in_array(0, $arCATEGORIES) || count($arCATEGORIES) <= 0)) || !is_array($arCATEGORIES)) echo "checked"?>></div>
							<div class="adm-list-label"><label for="category_chbox_0">(<?=GetMessage("SUP_ALL_CURRENT_FUTURE")?>)</label></div>
						</div><?
					foreach($arCategory as $ar)
					{
						$idR++;
						?><div class="adm-list-item" id="category_<?=$ar["ID"]?>"><?
							echo "<div class=\"adm-list-control\">".InputType("checkbox", "arCATEGORIES[]", $ar["ID"], $arCATEGORIES, false, "", "", "category_chbox_".$idR)."</div>".
								"<div class=\"adm-list-label\"><label for=\"category_chbox_".$idR."\">".htmlspecialcharsbx($ar["NAME"])."</label>".
								" [<a title=\"".GetMessage("MAIN_ADMIN_MENU_EDIT")."\" href=\"/bitrix/admin/ticket_dict_edit.php?ID=".intval($ar["ID"])."&find_type=C&lang=".LANGUAGE_ID."\">".
								intval($ar["ID"]).
								"</a>]</div>";
						?></div><?
					}?>
					</div>
				</td>
			</tr>
		<?
		if( $OLD_FUNCTIONALITY == "Y" )
		{
		?>
			<tr>
				<td valign="top"><?=GetMessage("SUP_CRITICALITY")?>:</td>
				<td nowrap>
					<div class="adm-list">
						<div class="adm-list-item" id="criticality_0">
							<div class="adm-list-control"><input name="arCRITICALITIES[]" type="checkbox" value="0" id="criticality_chbox_0" onclick="OnSelectAll(this.checked, 'arCRITICALITIES[]')" <?if ((is_array($arCRITICALITIES) && (in_array(0, $arCRITICALITIES) || count($arCRITICALITIES) <= 0)) || !is_array($arCRITICALITIES)) echo "checked"?>></div>
							<div class="adm-list-label"><label for="criticality_chbox_0">(<?=GetMessage("SUP_ALL_CURRENT_FUTURE")?>)</label></div>
						</div>
						<?
						foreach($arCriticality as $ar)
						{
							$idR++;
							?><div class="adm-list-item" id="criticality_<?=$ar["ID"]?>"><?
								echo "<div class=\"adm-list-control\">".InputType("checkbox", "arCRITICALITIES[]", $ar["ID"], $arCRITICALITIES, false, "", "", "criticality_chbox_".$idR)."</div>".
									"<div class=\"adm-list-label\"><label for=\"criticality_chbox_".$idR."\">".htmlspecialcharsbx($ar["NAME"])."</label>".
									" [<a title=\"".GetMessage("MAIN_ADMIN_MENU_EDIT")."\" href=\"/bitrix/admin/ticket_dict_edit.php?ID=".intval($ar["ID"])."&find_type=C&lang=".LANGUAGE_ID."\">".
									intval($ar["ID"]).
									"</a>]</div>";
							?></div><?
						}
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td valign="top"><?=GetMessage("SUP_MARK")?>:</td>
				<td nowrap>
					<div class="adm-list">
						<div class="adm-list-item" id="mark_0">
							<div class="adm-list-control"><input name="arMARKS[]" type="checkbox" value="0" id="mark_chbox_0" onclick="OnSelectAll(this.checked, 'arMARKS[]')" <?if ((is_array($arMARKS) && (in_array(0, $arMARKS) || count($arMARKS) <= 0)) || !is_array($arMARKS)) echo "checked"?>></div>
							<div class="adm-list-label"><label for="mark_chbox_0">(<?=GetMessage("SUP_ALL_CURRENT_FUTURE")?>)</label></div>
						</div>
						<?
						foreach($arMark as $ar)
						{
							$idR++;
							?><div class="adm-list-item" id="mark_<?=$ar["ID"]?>"><?
								echo "<div class=\"adm-list-control\">".InputType("checkbox", "arMARKS[]", $ar["ID"], $arMARKS, false, "", "", "mark_chbox_".$idR)."</div>".
									"<div class=\"adm-list-label\"><label for=\"mark_chbox_".$idR."\">".htmlspecialcharsbx($ar["NAME"])."</label>".
									" [<a title=\"".GetMessage("MAIN_ADMIN_MENU_EDIT")."\" href=\"/bitrix/admin/ticket_dict_edit.php?ID=".intval($ar["ID"])."&find_type=C&lang=".LANGUAGE_ID."\">".
									intval($ar["ID"]).
									"</a>]</div>";
							?></div><?
						}?>
					</div>
				</td>
			</tr>
		<? 
		} 
		?>
	<SCRIPT LANGUAGE="JavaScript">
	<!--
	OnSelectAll(<?echo ((is_array($arCATEGORIES) && (in_array(0, $arCATEGORIES) || count($arCATEGORIES) <= 0)) || !is_array($arCATEGORIES)) ? "true" : "false"?>, 'arCATEGORIES[]');
	OnSelectAll(<?echo ((is_array($arCRITICALITIES) && (in_array(0, $arCRITICALITIES) || count($arCRITICALITIES) <= 0)) || !is_array($arCRITICALITIES)) ? "true" : "false"?>, 'arCRITICALITIES[]');
	OnSelectAll(<?echo ((is_array($arMARKS) && (in_array(0, $arMARKS) || count($arMARKS) <= 0)) || !is_array($arMARKS)) ? "true" : "false"?>, 'arMARKS[]');
	OnSelectAll(<?echo ((is_array($arSITES) && (in_array('ALL', $arSITES) || count($arSITES) <= 0)) || !is_array($arSITES)) ? "true" : "false"?>, 'arSITES[]');
	OnSiteClick();
	//-->
	</SCRIPT>

	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>

<?
$tabControl->Buttons(array("disabled"=>$bAdmin != "Y","back_url"=>$LIST_URL."?lang=".LANG));
$tabControl->End();
?>

</form>
<?$tabControl->ShowWarnings("form1", $message);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
