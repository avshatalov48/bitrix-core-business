<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/mail_events/messagetype_admin.php");

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$err_mess = "File: ".__FILE__."<br>Line: ";
$arFilter = array();
$error = false;

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

$sTableID = "tbl_event_type";
$oSort = new CAdminSorting($sTableID, "event_name", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find",
	"find_type",
	"find_type_id",
	"find_tmpl_id",
	);
$lAdmin->InitFilter($arFilterFields);
if (!empty($find) && in_array(strToUpper($find_type), array('EVENT_NAME', 'NAME', 'DESCRIPTION')))
{
	$arFilter["=%" . strToUpper($find_type)] = '%' . $find . '%';
}
if (!empty($find_type_id))
{
	$arFilter["ID"] = $find_type_id;
}
if (!empty($find_tmpl_id))
{
	$arFilter["MESSAGE_ID"] = $find_tmpl_id;
}
	
if(($arID = $lAdmin->GroupAction()) && $isAdmin && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CEventType::GetListEx(array($by => $order), $arFilter, array("type" => "none"));
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['EVENT_NAME'];
	}

	foreach($arID as $ID)
	{
		if(strLen($ID) <= 0)
			continue;
		switch($_REQUEST['action'])
		{
			case "delete":
			case "clean":
				$DB->StartTransaction();
				$ID = array("EVENT_NAME" => $ID);
				$db_res = CEventMessage::GetList($by, $order, $ID);
				if ($db_res && ($res = $db_res->Fetch()))
				{
					do 
					{
						if (!CEventMessage::Delete($res["ID"]))
						{
							$error = true;
							break;
						}
					} while ($res = $db_res-> Fetch());
				}
				
				if ($error || !CEventType::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(Loc::getMessage("DELETE_ERROR"), $ID);
				}
				else
					$DB->Commit();
			break;
		}
	}
}
$arLID = array();
$db_res = CLanguage::GetList(($by_="sort"), ($order_="asc"));
if ($db_res && $res = $db_res->GetNext())
{
	do 
	{
		$arLID[$res["LID"]] = $res["LID"];
	} while ($res = $db_res->GetNext());
}


$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "default"=>true),
	array("id"=>"LID", "content"=>Loc::getMessage("LANG"), "default"=>true),
	array("id"=>"EVENT_NAME", "content"=>Loc::getMessage("EVENT_TYPE"), "sort"=>"event_name", "default"=>true),
	array("id"=>"NAME", "content"=>Loc::getMessage("EVENT_NAME"), "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>Loc::getMessage("EVENT_DESCRIPTION"), "default"=>false),
	array("id"=>"TEMPLATES", "content"=>Loc::getMessage("EVENT_TEMPLATES"), "default"=>false))
);

$resultMessageByTypeList = array();
$resultMessageByTypeDb = \Bitrix\Main\Mail\Internal\EventMessageTable::getList(array('select' => array('ID', 'EVENT_NAME')));
while($messageByType = $resultMessageByTypeDb->fetch())
{
	$resultMessageByTypeList[$messageByType['EVENT_NAME']][] = $messageByType['ID'];
}

$resultList = array();
$runtimeList = array();
$arFilter['!EVENT_NAME'] = null;
if(isset($arFilter['MESSAGE_ID']))
{
	$runtimeList[] = new \Bitrix\Main\Entity\ReferenceField(
		'MESSAGE',
		'Bitrix\Main\Mail\Internal\EventMessageTable',
		array('=this.EVENT_NAME' => 'ref.EVENT_NAME')
	);
	$arFilter['MESSAGE.ID'] = $arFilter['MESSAGE_ID'];
	unset($arFilter['MESSAGE_ID']);
}
$resultDb = \Bitrix\Main\Mail\Internal\EventTypeTable::getList(array(
	'filter' => $arFilter,
	'runtime' => $runtimeList,
	'order' => array('EVENT_NAME' => (strtoupper($order) == 'DESC' ? 'DESC' : 'ASC'))
));
$resultTypeList = $resultDb->fetchAll();
foreach($resultTypeList as $type)
{
	if(!isset($resultList[$type['EVENT_NAME']]))
	{
		$typeTmp = $type;
		$typeTmp['BY_LANGUAGE'] = array();
		$typeTmp['ID'] = array($type['ID']);
		$typeTmp['LID'] = array($type['LID']);
		$resultList[$type['EVENT_NAME']] = $typeTmp;
	}
	else
	{
		$resultList[$type['EVENT_NAME']]['ID'][] = $type['ID'];
		$resultList[$type['EVENT_NAME']]['LID'][] = $type['LID'];
	}

	$resultList[$type['EVENT_NAME']]['BY_LANGUAGE'][$type['LID']] = $type;
}

foreach($resultList as $eventName => $type)
{
	if(empty($type['BY_LANGUAGE'][LANGUAGE_ID]["NAME"]))
	{
		$type["NAME"] = $type['BY_LANGUAGE']["en"]["NAME"];
	}
	else
	{
		$type["NAME"] = $type['BY_LANGUAGE'][LANGUAGE_ID]["NAME"];
	}

	if(empty($type['BY_LANGUAGE'][LANGUAGE_ID]["SORT"]))
	{
		$type["SORT"] = $type['BY_LANGUAGE']["en"]["SORT"];
	}
	else
	{
		$type["SORT"] = $type['BY_LANGUAGE'][LANGUAGE_ID]["SORT"];
	}

	if(empty($type['BY_LANGUAGE'][LANGUAGE_ID]["DESCRIPTION"]))
	{
		$type["DESCRIPTION"] = $type['BY_LANGUAGE']["en"]["DESCRIPTION"];
	}
	else
	{
		$type["DESCRIPTION"] = $type['BY_LANGUAGE'][LANGUAGE_ID]["DESCRIPTION"];
	}

	$type["TEMPLATES"] = $resultMessageByTypeList[$eventName];


	unset($type['BY_LANGUAGE']);
	$resultList[$eventName] = $type;
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-event-type");
$nav->setRecordCount(count($resultList));
$lAdmin->setNavigation($nav, Loc::getMessage("PAGES"));
$iterateNumber = 0;
foreach($resultList as $resultItem)
{
	if($iterateNumber++ >= $nav->getOffset())
	{
		if($iterateNumber - $nav->getOffset() > $nav->getLimit())
		{
			break;
		}
	}
	else
	{
		continue;
	}

	$arr = $resultItem['ID'];
	$f_ID = htmlspecialcharsEx($resultItem['EVENT_NAME']);
	$row =& $lAdmin->AddRow($f_ID, $resultItem, "type_edit.php?EVENT_NAME=".$f_ID, Loc::getMessage("type_admin_edit_title"));
	$row->AddViewField("ID", implode("<br />", $arr));
	$row->AddViewField("LID", implode("<br />", array_intersect($arLID, $resultItem['LID'])));
	$row->AddViewField("EVENT_NAME", "<a href=\"type_edit.php?EVENT_NAME=".$f_ID."\">".$f_ID."</a>");
	$row->AddViewField("NAME", htmlspecialcharsEx($resultItem['NAME']));
	$row->AddViewField("DESCRIPTION", htmlspecialcharsEx($resultItem['DESCRIPTION']));
	$templates = array();
	if (is_array($resultItem['TEMPLATES']) && !empty($resultItem['TEMPLATES']))
	{
		$templates = array();
		foreach ($resultItem['TEMPLATES'] as $k)
		{
			$templates[$k] = "<a href=\"".BX_ROOT."/admin/message_edit.php?ID=".intVal($k)."&lang=".LANGUAGE_ID."\">".intVal($k)."</a>";
		}
	}
	$row->AddViewField("TEMPLATES", implode("<br />", $templates));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("MAIN_ADMIN_MENU_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("type_edit.php?EVENT_NAME=".$f_ID));
	if($isAdmin)
	{
		$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("MAIN_ADMIN_MENU_DELETE"), "ACTION"=>"if(confirm('".Loc::getMessage('CONFIRM_DEL_ALL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}
	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
));

$aContext = array(
	array(
		"TEXT" => Loc::getMessage("ADD_TYPE"),
		"LINK" => "type_edit.php?lang=".LANGUAGE_ID,
		"TITLE" => Loc::getMessage("ADD_TYPE_TITLE"),
		"ICON" => "btn_new"
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?><form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?"><?
$oFilter = new CAdminFilter($sTableID."_filter", array(
	Loc::getMessage('F_ID')." ".Loc::getMessage('F_TYPE'), 
	Loc::getMessage('F_ID')." ".Loc::getMessage('F_TMPL')));
$oFilter->Begin();
?><tr>
	<td><b><?=Loc::getMessage("F_SEARCH")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?=htmlspecialcharsbx($find)?>" title="<?=Loc::getMessage("F_SEARCH_TITLE")?>">
		<select name="find_type">
			<option value="event_name"<?if($find_type=="event_name") echo " selected"?>><?=Loc::getMessage('F_EVENT_NAME')?></option>
			<option value="name"<?if($find_type=="subject") echo " selected"?>><?=Loc::getMessage('F_NAME')?></option>
			<option value="description"<?if($find_type=="from") echo " selected"?>><?=Loc::getMessage('F_DESCRIPTION')?></option>
		</select>
	</td>
</tr>
<tr>
	<td>ID <?=Loc::getMessage('F_TYPE')?>:</td>
	<td><input type="text" name="find_type_id" size="47" value="<?=htmlspecialcharsbx($find_type_id)?>"></td>
</tr>
<tr>
	<td>ID <?=Loc::getMessage('F_TMPL')?>:</td>
	<td><input type="text" name="find_tmpl_id" size="47" value="<?=htmlspecialcharsbx($find_tmpl_id)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>