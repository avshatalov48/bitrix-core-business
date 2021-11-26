<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Item\UserToGroup;

Loader::includeModule('socialnetwork');

$socialnetworkModulePermissions = $APPLICATION->GetGroupRight("socialnetwork");
if ($socialnetworkModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/prolog.php");

$sTableID = "tbl_socnet_group";
$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SONET_GROUP_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"SUBJECT_ID", "content"=>GetMessage('SONET_GROUP_SUBJECT_ID'), "sort"=>"SUBJECT_ID", "default"=>true),
	array("id"=>"OWNER_ID", "content"=>GetMessage('SONET_GROUP_OWNER_ID'), "sort"=>"OWNER_ID", "default"=>true),
);

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_subject_id",
	"filter_name",
	"filter_owner_id",
	"filter_owner_user",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if ($filter_site_id <> '' && $filter_site_id != "NOT_REF")
	$arFilter["SITE_ID"] = $filter_site_id;
if ($filter_subject_id <> '' && $filter_subject_id != "NOT_REF")
	$arFilter["SUBJECT_ID"] = $filter_subject_id;
if ($filter_name <> '')
	$arFilter["%NAME"] = $filter_name;
if (intval($filter_owner_id) > 0)
	$arFilter["OWNER_ID"] = $filter_owner_id;
if ($filter_owner_user <> '')
	$arFilter["?OWNER_USER"] = $filter_owner_user;

if ($lAdmin->EditAction() && $socialnetworkModulePermissions >= "W")
{
	$arOwnerOld = array();
	$arGroupID = array_keys($FIELDS);
	if (
		is_array($arGroupID)
		&& !empty($arGroupID)
	)
	{
		$dbRelation = CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $arGroupID,
				"ROLE" => SONET_ROLES_OWNER
			),
			false,
			false,
			array("ID", "GROUP_ID", "USER_ID")
		);
		while ($arRelation = $dbRelation->Fetch())
		{
			$arOwnerOld[$arRelation["GROUP_ID"]] = array(
				"RELATION_ID" => $arRelation["ID"],
				"USER_ID" => $arRelation["USER_ID"]
			);
		}
	}

	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = intval($ID);
		$bError = false;

		if (!$lAdmin->IsUpdated($ID))
			continue;

		foreach ($arFields as $key => $value)
		{
			$bAllowed = false;
			foreach ($arHeaders as $header)
			{
				if ($header["id"] === $key)
					$bAllowed = true;
			}
			if (!$bAllowed)
				unset($arFields[$key]);
		}

		if (!CSocNetGroup::Update($ID, $arFields, false))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("SONET_ERROR_UPDATE"), $ID);

			$DB->Rollback();
		}
		elseif (
			!empty($arFields["OWNER_ID"])
			&& !empty($arOwnerOld[$ID])
		)
		{
			$arUpdateFields = array(
				"ROLE" => SONET_ROLES_USER,
				"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
				"INITIATED_BY_USER_ID" => $USER->GetID()
			);

			if (!CSocNetUserToGroup::Update($arOwnerOld[$ID]["RELATION_ID"], $arUpdateFields))
			{
				$bError = true;
				if ($ex = $APPLICATION->GetException())
				{
					$lAdmin->AddUpdateError($ex->GetString(), $ID);
				}
				else
				{
					$lAdmin->AddUpdateError(GetMessage("SONET_ERROR_UPDATE"), $ID);
				}
			}

			if (!$bError)
			{
				$dbRelation = CSocNetUserToGroup::GetList(array(), array("USER_ID" => intval($arFields["OWNER_ID"]), "GROUP_ID" => $ID), false, false, array("ID"));
				if ($arRelation = $dbRelation->Fetch())
				{
					$arUpdateFields = array(
						"ROLE" => SONET_ROLES_OWNER,
						"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
						"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
						"INITIATED_BY_USER_ID" => $USER->GetID(),
					);

					if (!CSocNetUserToGroup::Update($arRelation["ID"], $arUpdateFields))
					{
						$bError = true;
						if ($ex = $APPLICATION->GetException())
						{
							$lAdmin->AddUpdateError($ex->GetString(), $ID);
						}
						else
						{
							$lAdmin->AddUpdateError(GetMessage("SONET_ERROR_UPDATE"), $ID);
						}
					}
				}
				else
				{
					$arAddFields = array(
						"USER_ID" => intval($arFields["OWNER_ID"]),
						"GROUP_ID" => $ID,
						"ROLE" => SONET_ROLES_OWNER,
						"=DATE_CREATE" => $DB->CurrentTimeFunction(),
						"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
						"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
						"INITIATED_BY_USER_ID" => $USER->GetID(),
						"MESSAGE" => false,
					);

					if (!CSocNetUserToGroup::Add($arAddFields))
					{
						$bError = true;
						if ($ex = $APPLICATION->GetException())
						{
							$lAdmin->AddUpdateError($ex->GetString(), $ID);
						}
						else
						{
							$lAdmin->AddUpdateError(GetMessage("SONET_ERROR_UPDATE"), $ID);
						}
					}
					else
					{
						UserToGroup::addInfoToChat(array(
							'group_id' => $ID,
							'user_id' => $arFields["OWNER_ID"],
							'action' => UserToGroup::CHAT_ACTION_IN,
							'role' => \Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER
						));
					}
				}
			}

			if ($bError)
			{
				$DB->Rollback();
			}
		}

		$DB->Commit();
	}
}

if (($arID = $lAdmin->GroupAction()) && $socialnetworkModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSocNetGroup::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSocNetGroup::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SONET_DELETE_ERROR"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CSocNetGroup::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "SUBJECT_ID", "NAME", "SITE_ID", "OWNER_ID")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->AddHeaders($arHeaders);

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SONET_GROUP_NAV")));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSubjects = array();
$arSubjectsBySite = array();

$dbSitesList = CSite::GetList();
while ($arSite = $dbSitesList->Fetch())
{
	$dbSubjectsList = CSocNetGroupSubject::GetList(
		Array("SORT" => "ASC", "ID" => "DESC"),
		Array("SITE_ID" => $arSite["LID"])
	);
	while ($arSubject = $dbSubjectsList->Fetch())
	{
		$str = "[".$arSite["LID"]."] ".$arSubject["NAME"];
		$arSubjectsBySite[$arSite["LID"]][$arSubject["ID"]] = $str;
		if (!array_key_exists($arSubject["ID"], $arSubjects))
		{
			$arSubjects[$arSubject["ID"]] = $str;
		}
	}
}

while ($arGroup = $dbResultList->NavNext(true, "f_"))
{
	if (!empty($arGroup['NAME']))
	{
		$arGroup['NAME'] = \Bitrix\Main\Text\Emoji::decode($arGroup['NAME']);
	}

	$arMembers = array();

	$arResult["Users"] = false;
	$dbRequests = CSocNetUserToGroup::GetList(
		array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC"),
		array(
			"GROUP_ID" => $arGroup["ID"],
			"<=ROLE" => SONET_ROLES_USER,
			"USER_ACTIVE" => "Y"
		),
		false,
		false,
		array("ID", "USER_ID", "ROLE", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN")
	);
	while ($arRequests = $dbRequests->Fetch())
	{
		$arTmpUser = array(
			"ID" => $arRequests["USER_ID"],
			"NAME" => $arRequests["USER_NAME"],
			"LAST_NAME" => $arRequests["USER_LAST_NAME"],
			"LOGIN" => $arRequests["USER_LOGIN"]
		);
		$arMembers[$arRequests["USER_ID"]] = CUser::FormatName(GetMessage("USER_NAME_TEMPLATE"), $arTmpUser, true, false);
	}

	$row =& $lAdmin->AddRow($f_ID, $arGroup);

	$row->AddField("ID", $f_ID);
	$row->AddInputField("NAME", array("size" => "35"));

	foreach($arSubjectsBySite as $key => $arSubjectsTmp)
	{
		if (array_key_exists($arGroup["SUBJECT_ID"], $arSubjectsTmp))
		{
			$subjectSiteID = $key;
			break;
		}
	}

	$row->AddSelectField("SUBJECT_ID", $arSubjectsBySite[$subjectSiteID], array());
	$row->AddSelectField("OWNER_ID", $arMembers, array());

	$arActions = Array();
	if ($socialnetworkModulePermissions >= "U")
	{
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SONET_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('SONET_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SONET_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SONET_FILTER_SUBJECT_ID"),
		GetMessage("SONET_GROUP_NAME"),
		GetMessage("SONET_OWNER_USER"),
		GetMessage("SONET_OWNER_ID"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SONET_FILTER_SITE_ID")?></td>
		<td><?echo CSite::SelectBox("filter_site_id", $filter_site_id, GetMessage("SONET_SPT_ALL")) ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SONET_FILTER_SUBJECT_ID")?>:</td>
		<td>
			<select name="filter_subject_id">
				<option value="NOT_REF"><?= htmlspecialcharsex(GetMessage("SONET_SPT_ALL")); ?></option>
				<?
				foreach ($arSubjectsBySite as $subj_site_id => $arSiteSubjects)
				{
					foreach ($arSiteSubjects as $subject_id=>$sSubjectName)
					{
						?><option value="<?= $subject_id ?>"<?if ($filter_subject_id == $subject_id) echo " selected"?>><?= htmlspecialcharsex($sSubjectName) ?></option><?
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SONET_GROUP_NAME")?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SONET_OWNER_USER")?>:</td>
		<td>
			<input type="text" name="filter_owner_user" size="50" value="<?= htmlspecialcharsEx($filter_owner_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SONET_OWNER_ID")?>:</td>
		<td>
			<input type="text" name="filter_owner_id" size="5" value="<?= htmlspecialcharsEx($filter_owner_id) ?>">
		</td>
	</tr>


<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
