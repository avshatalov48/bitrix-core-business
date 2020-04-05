<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global string $by
 * @global string $order
 */

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "users/user_admin.php");
$entity_id = "USER";

if(!($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type\DateTime;

IncludeModuleLangFile(__FILE__);

//authorize as user
if($_REQUEST["action"] == "authorize" && check_bitrix_sessid() && $USER->CanDoOperation('edit_php'))
{
	$USER->Logout();
	$USER->Authorize(intval($_REQUEST["ID"]));
	$USER->CheckAuthActions();
	LocalRedirect("user_admin.php?lang=".LANGUAGE_ID);
}

$sTableID = "tbl_user";

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$bIntranetEdition = IsModuleInstalled("intranet");//(defined("INTRANET_EDITION") && INTRANET_EDITION == "Y");

$arFilterFields = Array(
	"find",
	"find_type",
	"find_id",
	"find_timestamp_1",
	"find_timestamp_2",
	"find_last_login_1",
	"find_last_login_2",
	"find_active",
	"find_login",
	"find_name",
	"find_email",
	"find_keywords",
	"find_group_id"
);
if ($bIntranetEdition)
	$arFilterFields[] = "find_intranet_users";
$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $arFilterFields);

$lAdmin->InitFilter($arFilterFields);

function CheckFilter($FilterArr)
{
	global $strError;
	foreach($FilterArr as $f)
		global ${$f};

	$str = "";
	if(strlen(trim($find_timestamp_1))>0 || strlen(trim($find_timestamp_2))>0)
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_timestamp_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_timestamp_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if (!$date1_stm && strlen(trim($find_timestamp_1))>0)
			$str.= GetMessage("MAIN_WRONG_TIMESTAMP_FROM")."<br>";
		else $date_1_ok = true;
		if (!$date2_stm && strlen(trim($find_timestamp_2))>0)
			$str.= GetMessage("MAIN_WRONG_TIMESTAMP_TILL")."<br>";
		elseif ($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
			$str.= GetMessage("MAIN_FROM_TILL_TIMESTAMP")."<br>";
	}

	if(strlen(trim($find_last_login_1))>0 || strlen(trim($find_last_login_2))>0)
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_last_login_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_last_login_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if(!$date1_stm && strlen(trim($find_last_login_1))>0)
			$str.= GetMessage("MAIN_WRONG_LAST_LOGIN_FROM")."<br>";
		else
			$date_1_ok = true;
		if(!$date2_stm && strlen(trim($find_last_login_2))>0)
			$str.= GetMessage("MAIN_WRONG_LAST_LOGIN_TILL")."<br>";
		elseif($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
			$str.= GetMessage("MAIN_FROM_TILL_LAST_LOGIN")."<br>";
	}

	$strError .= $str;
	if(strlen($str)>0)
	{
		global $lAdmin;
		$lAdmin->AddFilterError($str);
		return false;
	}

	return true;
}

$arFilter = Array();
if(CheckFilter($arFilterFields))
{
	$arFilter = Array(
		"ID" => $find_id,
		"TIMESTAMP_1" => $find_timestamp_1,
		"TIMESTAMP_2" => $find_timestamp_2,
		"LAST_LOGIN_1" => $find_last_login_1,
		"LAST_LOGIN_2" => $find_last_login_2,
		"ACTIVE" => $find_active,
		"LOGIN" => ($find!='' && $find_type == "login"? $find: $find_login),
		"NAME" => ($find!='' && $find_type == "name"? $find: $find_name),
		"EMAIL" => ($find!='' && $find_type == "email"? $find: $find_email),
		"KEYWORDS" => $find_keywords,
		"GROUPS_ID" => $find_group_id
		);
	if ($bIntranetEdition)
		$arFilter["INTRANET_USERS"] = $find_intranet_users;
	$USER_FIELD_MANAGER->AdminListAddFilter($entity_id, $arFilter);
}

/* Prepare data for new filter */
$queryObject = CGroup::GetDropDownList("AND ID!=2");
$listGroup = array();
while($group = $queryObject->fetch())
	$listGroup[$group["REFERENCE_ID"]] = $group["REFERENCE"];
$filterFields = array(
	array(
		"id" => "ID",
		"name" => GetMessage("MAIN_USER_ADMIN_FIELD_ID"),
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "TIMESTAMP_1",
		"name" => GetMessage("MAIN_F_TIMESTAMP"),
		"type" => "date",
	),
	array(
		"id" => "LAST_LOGIN_1",
		"name" => GetMessage("MAIN_F_LAST_LOGIN"),
		"type" => "date",
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("F_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("MAIN_YES"),
			"N" => GetMessage("MAIN_NO")
		),
		"filterable" => ""
	),
	array(
		"id" => "LOGIN",
		"name" => GetMessage("F_LOGIN"),
		"filterable" => "%",
		"default" => true
	),
	array(
		"id" => "EMAIL",
		"name" => GetMessage("MAIN_F_EMAIL"),
		"filterable" => "%",
		"default" => true
	),
	array(
		"id" => "NAME",
		"name" => GetMessage("F_NAME"),
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "KEYWORDS",
		"name" => GetMessage("MAIN_F_KEYWORDS"),
		"filterable" => ""
	),
	array(
		"id" => "GROUPS_ID",
		"name" => GetMessage("F_GROUP"),
		"type" => "list",
		"items" => $listGroup,
		"params" => array("multiple" => "Y"),
		"filterable" => ""
	),
);
if ($bIntranetEdition)
{
	$filterFields[] = array(
		"id" => "INTRANET_USERS",
		"name" => GetMessage("F_FIND_INTRANET_USERS"),
		"type" => "list",
		"items" => array(
			"" => GetMessage("MAIN_ALL"),
			"Y" => GetMessage("MAIN_YES")
		),
		"filterable" => ""
	);
}
$USER_FIELD_MANAGER->AdminListAddFilterFieldsV2($entity_id, $filterFields);
$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

$USER_FIELD_MANAGER->AdminListAddFilterV2($entity_id, $arFilter, $sTableID, $filterFields);

$arUserSubordinateGroups = array();
if(!$USER->CanDoOperation('edit_all_users') && !$USER->CanDoOperation('view_all_users'))
{
	$arUserGroups = CUser::GetUserGroup($USER->GetID());
	for ($j = 0, $len = count($arUserGroups); $j < $len; $j++)
	{
		$arSubordinateGroups = CGroup::GetSubordinateGroups($arUserGroups[$j]);
		$arUserSubordinateGroups = array_merge ($arUserSubordinateGroups, $arSubordinateGroups);
	}
	$arUserSubordinateGroups = array_unique($arUserSubordinateGroups);

	$arFilter["CHECK_SUBORDINATE"] = $arUserSubordinateGroups;

	if($USER->CanDoOperation('edit_own_profile'))
		$arFilter["CHECK_SUBORDINATE_AND_OWN"] = $USER->GetID();
}

if (!$USER->CanDoOperation('edit_php'))
{
	$arFilter["NOT_ADMIN"] = true;
}

if($lAdmin->EditAction())
{
	$editableFields = array(
		"ACTIVE"=>1, "LOGIN"=>1, "TITLE"=>1, "NAME"=>1, "LAST_NAME"=>1, "SECOND_NAME"=>1, "EMAIL"=>1, "PERSONAL_PROFESSION"=>1,
		"PERSONAL_WWW"=>1, "PERSONAL_ICQ"=>1, "PERSONAL_GENDER"=>1, "PERSONAL_PHONE"=>1, "PERSONAL_MOBILE"=>1,
		"PERSONAL_CITY"=>1, "PERSONAL_STREET"=>1, "WORK_COMPANY"=>1, "WORK_DEPARTMENT"=>1, "WORK_POSITION"=>1,
		"WORK_WWW"=>1, "WORK_PHONE"=>1, "WORK_CITY"=>1, "XML_ID"=>1,
	);

	foreach($_POST["FIELDS"] as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$USER->IsAdmin())
		{
			$UGroups = CUser::GetUserGroup($ID);
			if(in_array(1, $UGroups)) // not admin can't edit admins
			{
				continue;
			}
			elseif($USER->CanDoOperation('edit_subordinate_users'))
			{
				if(count(array_diff($UGroups, $arUserSubordinateGroups)) > 0)
					continue;
			}
			elseif($USER->CanDoOperation('edit_own_profile'))
			{
				if($USER->GetParam("USER_ID") != $ID)
					continue;
			}
			else
			{
				continue;
			}
		}

		if(!$lAdmin->IsUpdated($ID))
			continue;

		foreach($arFields as $key => $field)
		{
			if(!isset($editableFields[$key]) && strpos($key, "UF_") !== 0)
			{
				unset($arFields[$key]);
			}
		}

		$USER_FIELD_MANAGER->AdminListPrepareFields($entity_id, $arFields);

		$DB->StartTransaction();

		$ob = new CUser;
		if(!$ob->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$ob->LAST_ERROR, $ID);
			$DB->Rollback();
		}

		$DB->Commit();
	}
}

if(($arID = $lAdmin->GroupAction()) && ($USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users')))
{
	if (!empty($_REQUEST["action_all_rows_".$sTableID]) && $_REQUEST["action_all_rows_".$sTableID] === "Y")
	{
		$arID = array();
		$rsData = CUser::GetList($by, $order, $arFilter, array("FIELDS" => array("ID")));
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	$gr_id = intval($_REQUEST['groups']);
	$struct_id = intval($_REQUEST['UF_DEPARTMENT']);

	foreach($arID as $ID)
	{
		$ID = intval($ID);
		if($ID <= 1)
			continue;

		$arGroups = array();
		$res = CUser::GetUserGroupList($ID);
		while($res_arr = $res->Fetch())
			$arGroups[intval($res_arr["GROUP_ID"])] = array("GROUP_ID"=>$res_arr["GROUP_ID"], "DATE_ACTIVE_FROM"=>$res_arr["DATE_ACTIVE_FROM"], "DATE_ACTIVE_TO"=>$res_arr["DATE_ACTIVE_TO"]);

		if(isset($arGroups[1]) && !$USER->CanDoOperation('edit_php')) // not admin can't edit admins
			continue;

		if(!$USER->CanDoOperation('edit_all_users') && $USER->CanDoOperation('edit_subordinate_users') && count(array_diff(array_keys($arGroups), $arUserSubordinateGroups))>0)
			continue;

		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if(!CUser::Delete($ID))
				{
					$DB->Rollback();
					$err = '';
					if($ex = $APPLICATION->GetException())
						$err = '<br>'.$ex->GetString();
					$lAdmin->AddGroupError(GetMessage("DELETE_ERROR").$err, $ID);
				}
				$DB->Commit();
				break;
			case "activate":
			case "deactivate":
				$ob = new CUser();
				$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
				if(!$ob->Update($ID, $arFields))
					$lAdmin->AddGroupError(GetMessage("MAIN_EDIT_ERROR").$ob->LAST_ERROR, $ID);
				break;
			case "add_group":
			case "remove_group":
				if($gr_id <= 0)
					break;
				if($gr_id == 1 && !$USER->CanDoOperation('edit_php')) // not admin can't edit admins
					break;
				if ($USER->CanDoOperation('edit_subordinate_users') && !$USER->CanDoOperation('edit_all_users') && !in_array($gr_id, $arUserSubordinateGroups))
					break;
				if($_REQUEST['action'] == "add_group")
					$arGroups[$gr_id] = array("GROUP_ID" => $gr_id);
				else
					unset($arGroups[$gr_id]);
				CUser::SetUserGroup($ID, $arGroups);
				break;
			case "add_structure":
			case "remove_structure":
				if($struct_id <= 0)
					break;

				$dbUser = CUser::GetByID($ID);
				$arUser = $dbUser->Fetch();
				$arDep = $arUser['UF_DEPARTMENT'];
				if(!is_array($arDep))
					$arDep = array();

				if($_REQUEST['action']=="add_structure")
					$arDep[] = $struct_id;
				else
					$arDep = array_diff($arDep, array($struct_id));

				$ob = new CUser();
				$arFields = Array("UF_DEPARTMENT"=>$arDep);
				if(!$ob->Update($ID, $arFields))
					$lAdmin->AddGroupError(GetMessage("MAIN_EDIT_ERROR").$ob->LAST_ERROR, $ID);

				break;
			case "intranet_deactivate":
				$ob = new CUser();
				$arFields = Array("LAST_LOGIN"=>false);
				if(!$ob->Update($ID, $arFields))
					$lAdmin->AddGroupError(GetMessage("MAIN_EDIT_ERROR").$ob->LAST_ERROR, $ID);
				break;
		}
	}

	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}
setHeaderColumn($lAdmin);

$nav = $lAdmin->getPageNavigation("pages-user-admin");

$totalCountRequest = $lAdmin->isTotalCountRequest();

$userQuery = new Query(UserTable::getEntity());
$listSelectFields = ($totalCountRequest ? [] : $lAdmin->getVisibleHeaderColumns());
if (!in_array("ID", $listSelectFields))
	$listSelectFields[] = "ID";

$listRatingColumn = preg_grep('/^RATING_(\d+)$/i', $listSelectFields);
if (!empty($listRatingColumn))
	$listSelectFields = array_diff($listSelectFields, $listRatingColumn);

$userQuery->setSelect($listSelectFields);
$sortBy = strtoupper($by);
if(!UserTable::getEntity()->hasField($sortBy))
{
	$sortBy = "ID";
}
$sortOrder = strtoupper($order);
if($sortOrder <> "DESC" && $sortOrder <> "ASC")
{
	$sortOrder = "DESC";
}
$userQuery->setOrder(array($sortBy => $sortOrder));
if ($totalCountRequest)
{
	$userQuery->countTotal(true);
}
$userQuery->setOffset($nav->getOffset());
if ($_REQUEST["mode"] !== "excel")
	$userQuery->setLimit($nav->getLimit() + 1);

$filterOption = new Bitrix\Main\UI\Filter\Options($sTableID);
$filterData = $filterOption->getFilter($filterFields);
if (!empty($filterData["FIND"]))
{
	$userQuery->setFilter(\Bitrix\Main\UserUtils::getAdminSearchFilter(array("FIND" => $filterData["FIND"])));
}

foreach ($listRatingColumn as $ratingColumn)
{
	if (preg_match('/^RATING_(\d+)$/i', $ratingColumn, $matches))
	{
		$ratingId = intval($matches[1]);
		$userQuery->registerRuntimeField("RR".$ratingId, array(
			"data_type" => "Bitrix\Main\Rating\ResultsTable",
			"reference" => array(
				"=this.ID" => "ref.ENTITY_ID",
				"ref.ENTITY_TYPE_ID" => new SqlExpression("'USER'"),
				"ref.RATING_ID" => new SqlExpression('?i', $ratingId)
			),
			"join_type" => "LEFT"
		));
		$userQuery->addSelect("RR".$ratingId.".CURRENT_VALUE", "RATING_".$ratingId);
	}
}

if (isset($arFilter["NAME"]))
{
	$listFields = array("NAME", "LAST_NAME", "SECOND_NAME");
	$nameWords = $arFilter["NAME"];
	$filterQueryObject = new CFilterQuery("and", "yes", "N", array(), "N", "Y", "N");
	$nameWords = $filterQueryObject->CutKav($nameWords);
	$nameWords = $filterQueryObject->ParseQ($nameWords);
	if (strlen($nameWords) > 0 && $nameWords !== "( )")
		$parsedNameWords = preg_split('/[&&(||)]/',  $nameWords, -1, PREG_SPLIT_NO_EMPTY);

	$filterOr = Query::filter()->logic("or");
	foreach ($listFields as $fieldId)
	{
		foreach ($parsedNameWords as $nameWord)
		{
			$nameWord = trim($nameWord);
			if ($nameWord)
			{
				$filterOr->where(Query::filter()
					->whereLike($fieldId, "%".$nameWord."%")
				);
			}
		}
	}
	$userQuery->where($filterOr);
}
if (isset($arFilter["CHECK_SUBORDINATE"]) && is_array($arFilter["CHECK_SUBORDINATE"]))
{
	$strSubord = "0";
	foreach($arFilter["CHECK_SUBORDINATE"] as $grp)
		$strSubord .= ",".intval($grp);

	$userGroupQuery = UserGroupTable::query();
	$userGroupQuery->whereNotIn("GROUP_ID", new SqlExpression($strSubord));
	$userGroupQuery->where("USER_ID", new SqlExpression("%s"));

	$userQuery->registerRuntimeField(
		new ExpressionField("UGS", "EXISTS(".$userGroupQuery->getQuery().")", "ID"));

	if ($arFilter["CHECK_SUBORDINATE_AND_OWN"] > 0)
	{
		$userQuery->where(Query::filter()->logic("or")
			->where("ID", $arFilter["CHECK_SUBORDINATE_AND_OWN"])->whereNot("UGS"));
	}
	else
	{
		$userQuery->whereNot("UGS");
	}
}
if ($arFilter["NOT_ADMIN"])
{
	$userGroupQuery = UserGroupTable::query();
	$userGroupQuery->addSelect("USER_ID");
	$userGroupQuery->setGroup(["USER_ID"]);
	$userGroupQuery = \Bitrix\Main\ORM\Entity::getInstanceByQuery($userGroupQuery);
	$userQuery->registerRuntimeField("",
		(new Reference("UGNA", $userGroupQuery, Join::on("this.ID", "ref.USER_ID")))->configureJoinType("inner")
	);
}
if ($arFilter["INTRANET_USERS"] === "Y")
{
	$userQuery->where("ACTIVE", "Y");
	$userQuery->whereNotNull("LAST_LOGIN");
	$userQuery->where("UF_DEPARTMENT_SINGLE", ">", "0");
	$userQuery->disableDataDoubling();
}
if (isset($arFilter["TIMESTAMP_1"]))
{
	$userQuery->where("TIMESTAMP_X", ">=", new DateTime($arFilter["TIMESTAMP_1"]));
}
if (isset($arFilter["TIMESTAMP_2"]))
{
	$userQuery->where("TIMESTAMP_X", "<=", new DateTime($arFilter["TIMESTAMP_2"]));
}
if (isset($arFilter["LAST_LOGIN_1"]))
{
	$userQuery->where("LAST_LOGIN", ">=", new DateTime($arFilter["LAST_LOGIN_1"]));
}
if (isset($arFilter["LAST_LOGIN_2"]))
{
	$userQuery->where("LAST_LOGIN", "<=", new DateTime($arFilter["LAST_LOGIN_2"]));
}
if (isset($arFilter["GROUPS_ID"]))
{
	if (is_numeric($arFilter["GROUPS_ID"]) && intval($arFilter["GROUPS_ID"]) > 0)
		$arFilter["GROUPS_ID"] = array($arFilter["GROUPS_ID"]);
	$listGroupId = array();
	foreach ($arFilter["GROUPS_ID"] as $groupId)
		$listGroupId[intval($groupId)] = intval($groupId);

	$userGroupQuery = UserGroupTable::query();
	$userGroupQuery->addSelect("USER_ID");
	$userGroupQuery->whereIn("GROUP_ID", $listGroupId);
	$nowTimeExpression = new SqlExpression(
		$userGroupQuery->getEntity()->getConnection()->getSqlHelper()->getCurrentDateTimeFunction());
	$userGroupQuery->where(Query::filter()->logic("or")
		->whereNull("DATE_ACTIVE_FROM")
		->where("DATE_ACTIVE_FROM", "<=", $nowTimeExpression)
	);
	$userGroupQuery->where(Query::filter()->logic("or")
		->whereNull("DATE_ACTIVE_TO")
		->where("DATE_ACTIVE_TO", ">=", $nowTimeExpression)
	);
	$userGroupQuery->setGroup(["USER_ID"]);
	$userGroupQuery = \Bitrix\Main\ORM\Entity::getInstanceByQuery($userGroupQuery);
	$userQuery->registerRuntimeField("",
		(new Reference("UG", $userGroupQuery, Join::on("this.ID", "ref.USER_ID")))->configureJoinType("inner")
	);
}
if (!empty($arFilter["KEYWORDS"]))
{
	$listFields = array(
		"PERSONAL_PROFESSION", "PERSONAL_WWW", "PERSONAL_ICQ", "PERSONAL_GENDER",
		"PERSONAL_PHONE", "PERSONAL_FAX", "PERSONAL_MOBILE", "PERSONAL_PAGER", "PERSONAL_STREET", "PERSONAL_MAILBOX",
		"PERSONAL_CITY", "PERSONAL_STATE", "PERSONAL_ZIP", "PERSONAL_COUNTRY", "PERSONAL_NOTES", "WORK_COMPANY",
		"WORK_DEPARTMENT", "WORK_POSITION", "WORK_WWW", "WORK_PHONE", "WORK_FAX", "WORK_PAGER", "WORK_STREET",
		"WORK_MAILBOX", "WORK_CITY", "WORK_STATE", "WORK_ZIP", "WORK_COUNTRY", "WORK_PROFILE", "WORK_NOTES",
		"ADMIN_NOTES", "XML_ID", "LAST_NAME", "SECOND_NAME", "EXTERNAL_AUTH_ID", "CONFIRM_CODE",
		"PASSWORD", "LID", "LANGUAGE_ID", "TITLE"
	);
	$keyWords = $arFilter["KEYWORDS"];
	$filterQueryObject = new CFilterQuery("and", "yes", "N", array(), "N", "Y", "N");
	$keyWords = $filterQueryObject->CutKav($keyWords);
	$keyWords = $filterQueryObject->ParseQ($keyWords);
	if (strlen($keyWords) > 0 && $keyWords !== "( )")
		$parsedKeyWords = preg_split('/[&&(||)]/',  $keyWords, -1, PREG_SPLIT_NO_EMPTY);
	$filterOr = Query::filter()->logic("or");
	foreach ($listFields as $fieldId)
	{
		foreach ($parsedKeyWords as $keyWord)
		{
			$keyWord = trim($keyWord);
			if ($keyWord)
			{
				$filterOr->where(Query::filter()
					->whereNotNull($fieldId)
					->whereLike($fieldId, "%".$keyWord."%")
				);
			}
		}
	}
	$userQuery->where($filterOr);
}

$ignoreKey = array("NAME", "CHECK_SUBORDINATE", "CHECK_SUBORDINATE_AND_OWN", "NOT_ADMIN", "INTRANET_USERS", "GROUPS_ID",
	"KEYWORDS", "TIMESTAMP_1", "TIMESTAMP_2", "LAST_LOGIN_1", "LAST_LOGIN_2"
);
foreach ($arFilter as $filterKey => $filterValue)
{
	if (!in_array($filterKey, $ignoreKey))
	{
		$userQuery->addFilter($filterKey, $filterValue);
	}
}

$result = $userQuery->exec();

if ($totalCountRequest)
{
	$lAdmin->sendTotalCountResponse($result->getCount());
}

$n = 0;
$pageSize = $lAdmin->getNavSize();
while ($userData = $result->fetch())
{
	$n++;
	if ($n > $pageSize)
	{
		break;
	}

	$userId = $userData["ID"];
	$userEditUrl = "user_edit.php?lang=".LANGUAGE_ID."&ID=".$userId;
	$row =& $lAdmin->addRow($userId, $userData, $userEditUrl);
	$USER_FIELD_MANAGER->addUserFields($entity_id, $userData, $row);
	$row->addViewField("ID", "<a href='".$userEditUrl."' title='".GetMessage("MAIN_EDIT_TITLE")."'>".$userId."</a>");
	$own_edit = ($USER->canDoOperation('edit_own_profile') && ($USER->getParam("USER_ID") == $userId));
	$edit = ($USER->canDoOperation('edit_subordinate_users') || $USER->canDoOperation('edit_all_users'));
	$can_edit = (IntVal($userId) > 1 && ($own_edit || $edit));
	if ($userId == 1 || $own_edit || !$can_edit)
		$row->addCheckField("ACTIVE", false);
	else
		$row->addCheckField("ACTIVE");

	if ($can_edit && $edit)
	{
		$row->addField("LOGIN", "<a href='user_edit.php?lang=".LANGUAGE_ID."&ID=".$userId.
			"' title='".GetMessage("MAIN_EDIT_TITLE")."'>".HtmlFilter::encode($userData["LOGIN"])."</a>", true);
		$row->addInputField("TITLE");
		$row->addInputField("NAME");
		$row->addInputField("LAST_NAME");
		$row->addInputField("SECOND_NAME");
		$row->addViewField("EMAIL", TxtToHtml($userData["EMAIL"]));
		$row->addInputField("EMAIL");
		$row->addInputField("PERSONAL_PROFESSION");
		$row->addViewField("PERSONAL_WWW", TxtToHtml($userData["PERSONAL_WWW"]));
		$row->addInputField("PERSONAL_WWW");
		$row->addInputField("PERSONAL_ICQ");
		$row->addSelectField("PERSONAL_GENDER", array("" => GetMessage("USER_DONT_KNOW"),
			"M" => GetMessage("USER_MALE"), "F" => GetMessage("USER_FEMALE")));
		$row->addInputField("PERSONAL_PHONE");
		$row->addInputField("PERSONAL_MOBILE");
		$row->addInputField("PERSONAL_CITY");
		$row->addInputField("PERSONAL_STREET");
		$row->addInputField("WORK_COMPANY");
		$row->addInputField("WORK_DEPARTMENT");
		$row->addInputField("WORK_POSITION");
		$row->addViewField("WORK_WWW", TxtToHtml($userData["WORK_WWW"]));
		$row->addInputField("WORK_WWW");
		$row->addInputField("WORK_PHONE");
		$row->addInputField("WORK_CITY");
		$row->addInputField("XML_ID");
	}
	else
	{
		$row->addViewField("LOGIN", "<a href='user_edit.php?lang=".LANGUAGE_ID."&ID=".$userId.
			"' title='".GetMessage("MAIN_EDIT_TITLE")."'>".HtmlFilter::encode($userData["LOGIN"])."</a>");
		$row->addViewField("EMAIL", TxtToHtml($userData["EMAIL"]));
		$row->addViewField("PERSONAL_WWW", TxtToHtml($userData["PERSONAL_WWW"]));
		$row->addViewField("WORK_WWW", TxtToHtml($userData["WORK_WWW"]));
	}

	$arActions = array();
	$arActions[] = array(
		"ICON" => $can_edit ? "edit" : "view",
		"TEXT" => GetMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
		"LINK" => "user_edit.php?lang=".LANGUAGE_ID."&ID=".$userId, "DEFAULT" => true
	);
	if ($can_edit && $edit)
	{
		$arActions[] = array(
			"ICON" => "copy",
			"TEXT" => GetMessage("MAIN_ADMIN_ADD_COPY"),
			"LINK" => "user_edit.php?lang=".LANGUAGE_ID."&COPY_ID=".$userId
		);
		if (!$own_edit)
		{
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
				"ACTION" => "if(confirm('".GetMessage('CONFIRM_DEL_USER')."')) ".$lAdmin->actionDoGroup($userId, "delete")
			);
		}
	}
	if($USER->CanDoOperation('edit_php'))
	{
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array(
			"ICON" => "",
			"TEXT" => GetMessage("MAIN_ADMIN_AUTH"),
			"TITLE" => GetMessage("MAIN_ADMIN_AUTH_TITLE"),
			"LINK" => "user_admin.php?lang=".LANGUAGE_ID."&ID=".$userId."&action=authorize&".bitrix_sessid_get()
		);
	}

	$row->addActions($arActions);
}

$nav->setRecordCount($nav->getOffset() + $n);
$lAdmin->setNavigation($nav, GetMessage("MAIN_USER_ADMIN_PAGES"), false);

$aContext = Array();

if ($USER->CanDoOperation('edit_subordinate_users') || $USER->CanDoOperation('edit_all_users'))
{
	$sGr = array();
	foreach($listGroup as $referenceId => $reference)
		$sGr[] = array("NAME" => $reference, "VALUE" => $referenceId);

	$ar = Array(
		"edit" => true,
		"delete" => true,
		"for_all" => true,
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"add_group" => array(
			"lable" => GetMessage("MAIN_ADMIN_LIST_ADD_GROUP"),
			"type" => "select",
			"name" => "groups",
			"items" => $sGr
		),
		"remove_group"=>array(
			"lable" => GetMessage("MAIN_ADMIN_LIST_REM_GROUP"),
			"type" => "select",
			"name" => "groups",
			"items" => $sGr
		)
	);

	//for Intranet editions: structure group operations and last authorization time
	if($bIntranetEdition)
	{
		$arUserFields = $USER_FIELD_MANAGER->GetUserFields('USER', 0, LANGUAGE_ID);
		$arUserField = $arUserFields['UF_DEPARTMENT'];
		if(is_array($arUserField))
		{
			$arUserField['MULTIPLE'] = 'N';
			$arUserField['SETTINGS']['LIST_HEIGHT'] = 1;

			$sStruct = call_user_func_array(
				array($arUserField["USER_TYPE"]["CLASS_NAME"], "GetGroupActionData"),
				array(
					$arUserField,
					array(
						"NAME" => $arUserField["FIELD_NAME"],
						"VALUE" => "",
					),
				)
			);
			$ar["add_structure"] = array(
				"lable" => GetMessage("MAIN_ADMIN_LIST_ADD_STRUCT"),
				"type" => "select",
				"name" => "UF_DEPARTMENT",
				"items" => $sStruct
			);
			$ar["remove_structure"] = array(
				"lable" => GetMessage("MAIN_ADMIN_LIST_REM_STRUCT"),
				"type" => "select",
				"name" => "UF_DEPARTMENT",
				"items" => $sStruct
			);
		}
		$ar["intranet_deactivate"] = GetMessage("MAIN_ADMIN_LIST_INTRANET_DEACTIVATE");
	}

	$arParams = array("select_onchange"=>"document.getElementById('bx_user_groups').style.display = (this.value == 'add_group' || this.value == 'remove_group'? 'block':'none');".(isset($ar["structure"])? "document.getElementById('bx_user_structure').style.display = (this.value == 'add_structure' || this.value == 'remove_structure'? 'block':'none');":""));

	$lAdmin->AddGroupActionTable($ar, $arParams);

	$aContext[] = array(
		"TEXT"	=> GetMessage("MAIN_ADD_USER"),
		"LINK"	=> "user_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_ADD_USER_TITLE"),
		"ICON"	=> "btn_new"
	);
}
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList(["SHOW_COUNT_HTML" => true]);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

function setHeaderColumn(CAdminUiList $lAdmin)
{
	$arHeaders = array(
		array("id"=>"LOGIN", "content"=>GetMessage("LOGIN"), "sort"=>"login", "default"=>true),
		array("id"=>"ACTIVE", "content"=>GetMessage('ACTIVE'),	"sort"=>"active", "default"=>true, "align" => "center"),
		array("id"=>"TIMESTAMP_X", "content"=>GetMessage('TIMESTAMP'), "sort"=>"timestamp_x", "default"=>true),
		array("id"=>"TITLE", "content"=>GetMessage("USER_ADMIN_TITLE"), "sort"=>"title"),
		array("id"=>"NAME", "content"=>GetMessage("NAME"), "sort"=>"name",	"default"=>true),
		array("id"=>"LAST_NAME", "content"=>GetMessage("LAST_NAME"), "sort"=>"last_name", "default"=>true),
		array("id"=>"SECOND_NAME", "content"=>GetMessage("SECOND_NAME"), "sort"=>"second_name"),
		array("id"=>"EMAIL", "content"=>GetMessage('EMAIL'), "sort"=>"email", "default"=>true),
		array("id"=>"LAST_LOGIN", "content"=>GetMessage("LAST_LOGIN"), "sort"=>"last_login", "default"=>true),
		array("id"=>"DATE_REGISTER", "content"=>GetMessage("DATE_REGISTER"), "sort"=>"date_register"),
		array("id"=>"ID", "content"=>"ID", 	"sort"=>"id", "default"=>true, "align"=>"right"),
		array("id"=>"PERSONAL_BIRTHDAY", "content"=>GetMessage("PERSONAL_BIRTHDAY"), "sort"=>"personal_birthday"),
		array("id"=>"PERSONAL_PROFESSION", "content"=>GetMessage("PERSONAL_PROFESSION"), "sort"=>"personal_profession"),
		array("id"=>"PERSONAL_WWW", "content"=>GetMessage("PERSONAL_WWW"), "sort"=>"personal_www"),
		array("id"=>"PERSONAL_ICQ", "content"=>GetMessage("PERSONAL_ICQ"), "sort"=>"personal_icq"),
		array("id"=>"PERSONAL_GENDER", "content"=>GetMessage("PERSONAL_GENDER"), "sort"=>"personal_gender"),
		array("id"=>"PERSONAL_PHONE", "content"=>GetMessage("PERSONAL_PHONE"), "sort"=>"personal_phone"),
		array("id"=>"PERSONAL_MOBILE", "content"=>GetMessage("PERSONAL_MOBILE"), "sort"=>"personal_mobile"),
		array("id"=>"PERSONAL_CITY", "content"=>GetMessage("PERSONAL_CITY"), "sort"=>"personal_city"),
		array("id"=>"PERSONAL_STREET", "content"=>GetMessage("PERSONAL_STREET"), "sort"=>"personal_street"),
		array("id"=>"WORK_COMPANY", "content"=>GetMessage("WORK_COMPANY"), "sort"=>"work_company"),
		array("id"=>"WORK_DEPARTMENT", "content"=>GetMessage("WORK_DEPARTMENT"), "sort"=>"work_department"),
		array("id"=>"WORK_POSITION", "content"=>GetMessage("WORK_POSITION"), "sort"=>"work_position"),
		array("id"=>"WORK_WWW", "content"=>GetMessage("WORK_WWW"), "sort"=>"work_www"),
		array("id"=>"WORK_PHONE", "content"=>GetMessage("WORK_PHONE"), "sort"=>"work_phone"),
		array("id"=>"WORK_CITY", "content"=>GetMessage("WORK_CITY"), "sort"=>"work_city"),
		array("id"=>"XML_ID", "content"=>GetMessage("XML_ID"), "sort"=>"xml_id"),
		array("id"=>"EXTERNAL_AUTH_ID", "content"=>GetMessage("EXTERNAL_AUTH_ID")),
	);

	setRatingHeadersColumn($arHeaders);
	setUFHeadersColumn($arHeaders);

	$lAdmin->addHeaders($arHeaders);
}

function setRatingHeadersColumn(&$arHeaders)
{
	$rsRatings = CRatings::GetList(array('ID' => 'ASC'), array('ACTIVE' => 'Y', 'ENTITY_ID' => 'USER'));
	while ($arRatingsTmp = $rsRatings->GetNext())
	{
		$ratingId = $arRatingsTmp['ID'];
		$arHeaders[] = array(
			"id" => "RATING_".$ratingId,
			"content" => htmlspecialcharsbx($arRatingsTmp['NAME']),
			"sort" => "RATING_".$ratingId
		);
	}
}

function setUFHeadersColumn(&$arHeaders)
{
	global $USER_FIELD_MANAGER;
	$USER_FIELD_MANAGER->adminListAddHeaders("USER", $arHeaders);
}
?>