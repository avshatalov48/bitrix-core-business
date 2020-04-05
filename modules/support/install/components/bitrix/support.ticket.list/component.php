<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");

if (!CModule::IncludeModule("support"))
{
	ShowError(GetMessage("MODULE_NOT_INSTALL"));
	return;
}

//Permissions
if ( !($USER->IsAuthorized() && (CTicket::IsSupportClient() || CTicket::IsAdmin() || CTicket::IsSupportTeam() || CTicket::IsDemo())) )
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


$bADS = $bDemo == 'Y' || $bAdmin == 'Y' || $bSupportTeam == 'Y';

//TICKET_EDIT_TEMPLATE
$arParams["TICKET_EDIT_TEMPLATE"] = (strlen($arParams["TICKET_EDIT_TEMPLATE"]) > 0 ? $arParams["TICKET_EDIT_TEMPLATE"] : "ticket_edit.php?ID=#ID#");

//Get Tickets
CPageOption::SetOptionString("main", "nav_page_in_session", "N");

$UFA = array();
$UFAT = array();
global $USER_FIELD_MANAGER;
$arrUF = $USER_FIELD_MANAGER->GetUserFields( "SUPPORT", 0, LANGUAGE_ID );
if (isset($arParams["SET_SHOW_USER_FIELD"]) && is_array($arParams["SET_SHOW_USER_FIELD"]))
{
	foreach( $arParams["SET_SHOW_USER_FIELD"] as $k => $v )
	{
		if( strlen( trim( $v ) ) > 0 )
		{
			$UFAT[$v] = array(
							"NAME_C" => $arrUF[$v]["LIST_COLUMN_LABEL"],
							"NAME_F" => $arrUF[$v]["EDIT_FORM_LABEL"],
							"ALL" => $arrUF[$v],
			);
			$UFA[] = $v;
		}
	}
}
$arParams["SET_SHOW_USER_FIELD_T"] = $UFAT;

//Result array
$arResult = Array(
	"TICKETS" => Array(),
);

//Get Dictionary Array
$arTicketDictionary = CTicketDictionary::GetDropDownArray();

//Dictionary table
$arDictType = Array(
		"C" => "CATEGORY",
		"K" => "CRITICALITY",
		"S" => "STATUS",
		"M" => "MARK",
		"SR" => "SOURCE",
);

//Set Title
$arParams["SET_PAGE_TITLE"] = ($arParams["SET_PAGE_TITLE"] == "N" ? "N" : "Y" );

if ($arParams["SET_PAGE_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("SUP_DEFAULT_TITLE"));


// ------------------------------

$arResult["GRID_ID"] = "ticket_grid";


// rewrite old filter values
foreach ($_REQUEST as $k => $v)
{
	if ($k === 'find_title')
	{
		$_REQUEST['find_message'] = trim(trim($_REQUEST['find_message']) . ' ' . trim($_REQUEST['find_title']));
		unset($_REQUEST['find_title']);
	}
}

foreach ($_REQUEST as $k => $v)
{
	if (substr($k, 0 , 5) === 'find_')
	{
		$fName = strtoupper(substr($k, 5));
		$_REQUEST[$fName] = $v;
	}
}


$arResult["FILTER"] = array(
	array("id"=>"ID", "name"=>GetMessage('SUP_F_ID')),
	array("id"=>"LAMP", "name"=>GetMessage('SUP_F_LAMP'), "type"=>"list", "params"=>array("size"=>4, "multiple"=>"multiple"), "valign"=>"top", "items"=>array(
		'red' => GetMessage('SUP_RED'), 'green' => GetMessage('SUP_GREEN'), 'grey' => GetMessage('SUP_GREY')
	)),
	array("id"=>"CLOSE", "name"=>GetMessage('SUP_F_CLOSE'), "type" => "list", "items" => array(
		"" => GetMessage('SUP_ALL'), 'Y' => GetMessage('SUP_CLOSED'),'N' => GetMessage('SUP_OPENED')
	)),
	array("id"=>"MESSAGE", "name"=>GetMessage('SUP_F_MESSAGE_TITLE'))
);

$arParams["TICKETS_PER_PAGE"] = (intval($arParams["TICKETS_PER_PAGE"]) <= 0 ? 50 : intval($arParams["TICKETS_PER_PAGE"]));

$grid_options = new CGridOptions($arResult["GRID_ID"]);
$aSort = $grid_options->GetSorting(array("sort"=>array("id"=>"desc"), "vars"=>array("by"=>"by", "order"=>"order")));
$aNav = $grid_options->GetNavParams(array("nPageSize"=>$arParams["TICKETS_PER_PAGE"]));
$aSortArg = each($aSort["sort"]);
$aFilter = $grid_options->GetFilter($arResult["FILTER"]);

$aSortVal = $aSort['sort'];
$sort_order = current($aSortVal);
$sort_by = key($aSortVal);


if (strlen($arParams["SITE_ID"]) > 0)
	$aFilter["LID"] = $arParams["SITE_ID"];

$rsTickets = CTicket::GetList(
	$sort_by,
	$sort_order,
	$aFilter,
	$is_filtered,
	$check_rights = "Y",
	$get_user_name = "N",
	$get_dictionary_name = "N",
	false,
	array( "SELECT" => $UFA, 'NAV_PARAMS' => array('nPageSize' => $arParams["TICKETS_PER_PAGE"], 'bShowAll' => false) )
);

$arTickets = array();
$arRespUserIDs = array();
$arGuestIDs = array();
$arUsersPref = array("RESPONSIBLE", "OWNER", "MODIFIED", "CREATED");
$arGuestsPref = array("OWNER", "CREATED");

while ($arTicket = $rsTickets->GetNext())
{
	$arTickets[] = $arTicket;

	foreach($arUsersPref as $cup)
	{
		$arRespUserIDs[] = $arTicket[$cup . "_USER_ID"];
	}

	foreach($arGuestsPref as $cgp)
	{
		$arGuestIDs[] = $arTicket[$cgp . "_GUEST_ID"];
	}
}

$arStrUsersM = CTicket::GetUsersPropertiesArray($arRespUserIDs,$arGuestIDs);

// join userdata with tickets
foreach ($arTickets as $arTicket)
{
	$arUsersP = array();

	foreach($arUsersPref as $cup)
	{
		$cuid = intval($arTicket[$cup . "_USER_ID"]);
		$userGuest = "arUsers";

		if($cuid <= 0 && in_array($cup, $arGuestsPref))
		{
			$cuid = intval($arTicket[$cup . "_GUEST_ID"]);
			$userGuest = "arGuests";
			//array_key_exists("first", $search_array)
		}

		$cName = "";
		$cSName = "";
		$cLName = "";
		$cLogin = "";
		$cHtmlMameS = "";

		if($cuid > 0)
		{
			$cName = $arStrUsersM[$userGuest][$cuid]["NAME"];
			$cSName = $arStrUsersM[$userGuest][$cuid]["SECOND_NAME"];
			$cLName = $arStrUsersM[$userGuest][$cuid]["LAST_NAME"];
			$cLogin = $arStrUsersM[$userGuest][$cuid]["LOGIN"];
			$cHtmlMameS = $arStrUsersM[$userGuest][$cuid]["HTML_NAME_S"];
		}

		$arUsersP[$cup . "_NAME"] = $cName;
		$arUsersP[$cup . "_SECOND_NAME"] = $cSName;
		$arUsersP[$cup . "_LAST_NAME"] = $cLName;
		$arUsersP[$cup . "_LOGIN"] = $cLogin;
		$arUsersP[$cup . "_HTML_NAME_S"] = $cHtmlMameS;
	}


	$arDict = Array();

	foreach ($arDictType as $TYPE => $CODE)
	{
		$arDict += _GetDictionaryInfo($arTicket[$CODE."_ID"], $TYPE, $CODE, $arTicketDictionary);
	}


	$url = CComponentEngine::MakePathFromTemplate($arParams["TICKET_EDIT_TEMPLATE"], Array("ID" => $arTicket["ID"]));

	$arResult["TICKETS"][] = ($arTicket + $arUsersP + $arDict + Array("TICKET_EDIT_URL" => $url));
}


// make grid
foreach ($arResult["TICKETS"] as &$arTicket)
{
	$arTickets[] = $arTicket;

	foreach($arUsersPref as $cup)
	{
		$arRespUserIDs[] = $arTicket[$cup . "_USER_ID"];
	}
	foreach($arGuestsPref as $cgp)
	{
		$arGuestIDs[] = $arTicket[$cgp . "_GUEST_ID"];
	}

	if (strlen($arTicket["MODIFIED_MODULE_NAME"])<=0 || $arTicket["MODIFIED_MODULE_NAME"]=="support")
	{
		if(intval($arTicket["MODIFIED_USER_ID"]) > 0)
		{
			if(isset($arTicket["MODIFIED_HTML_NAME_S"]))
			{
				$arTicket['MODIFIED_BY'] = $arTicket["MODIFIED_HTML_NAME_S"];
			}
			else
			{
				$arTicket['MODIFIED_BY'] = ("[" . $arTicket["MODIFIED_USER_ID"] . "] (" . $arTicket["MODIFIED_LOGIN"] . ") " . $arTicket["MODIFIED_NAME"] . "  " . $arTicket["MODIFIED_LAST_NAME"]);
			}
		}
		elseif(intval($arTicket["OWNER_USER_ID"]) > 0)
		{
			if(isset($arTicket["OWNER_HTML_NAME_S"]))
			{
				$arTicket['MODIFIED_BY'] = $arTicket["OWNER_HTML_NAME_S"];
			}
			else
			{
				$arTicket['MODIFIED_BY'] = ("[" . $arTicket["OWNER_USER_ID"] . "] (" . $arTicket["OWNER_LOGIN"] . ") " . $arTicket["OWNER_NAME"] . "  " . $arTicket["OWNER_LAST_NAME"]);
			}
		}
		elseif(intval($arTicket["CREATED_USER_ID"]) > 0)
		{
			if(isset($arTicket["CREATED_HTML_NAME_S"]))
			{
				$arTicket['MODIFIED_BY'] = $arTicket["CREATED_HTML_NAME_S"];
			}
			else
			{
				$arTicket['MODIFIED_BY'] = ("[" . $arTicket["CREATED_USER_ID"] . "] (" . $arTicket["CREATED_LOGIN"] . ") " . $arTicket["CREATED_NAME"] . "  " . $arTicket["CREATED_LAST_NAME"]);
			}
		}
	}
	else
	{
		$arTicket['MODIFIED_BY'] = $arTicket["MODIFIED_MODULE_NAME"];
	}

	$aCols = array(
		'LAMP' => '<div class="support-lamp-'.str_replace("_","-",$arTicket["LAMP"]).'" title="'.GetMessage("SUP_".strtoupper($arTicket["LAMP"]).($bADS ? "_ALT_SUP" : "_ALT")).'"></div>',
		'TIMESTAMP_X' => FormatDate($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), MakeTimeStamp($arTicket["TIMESTAMP_X"]))
	);

	$url = CComponentEngine::MakePathFromTemplate($arParams["TICKET_EDIT_TEMPLATE"], Array("ID" => $arTicket["ID"]));

	$aActions = Array(
		array("ICONCLASS"=>"edit", "TEXT"=>GetMessage('SUP_EDIT'), "DEFAULT"=>true, "ONCLICK"=>
			"BX.ajax.AJAX_ID = '".$arParams['AJAX_ID']."'; var url = '".$url."'; if(BX.ajax.AJAX_ID != '') BX.ajax.insertToNode(url+(url.indexOf('?') == -1? '?':'&')+'bxajaxid='+BX.ajax.AJAX_ID, 'comp_'+BX.ajax.AJAX_ID); else jsUtils.Redirect(arguments, '".$url."');"
		),
	);

	$aRows[] = array("data"=>$arTicket, "actions"=>$aActions, "columns"=>$aCols);
}


$arResult["ROWS"] = $aRows;
$arResult["ROWS_COUNT"] = $rsTickets->SelectedRowsCount();
$arResult["TICKETS_COUNT"] = $rsTickets->SelectedRowsCount();
$arResult["NAV_STRING"] = $rsTickets->GetPageNavString(GetMessage("SUP_PAGES"));
$arResult["CURRENT_PAGE"] = htmlspecialcharsbx($APPLICATION->GetCurPage());
$arResult["NEW_TICKET_PAGE"] = htmlspecialcharsbx(CComponentEngine::MakePathFromTemplate($arParams["TICKET_EDIT_TEMPLATE"], Array("ID" => "0")));

$arResult["SORT"] = $aSort["sort"];
$arResult["SORT_VARS"] = $aSort["vars"];

$arResult["NAV_OBJECT"] = $rsTickets;

// rewrite for old templates
if (!empty($_SESSION["main.interface.grid"][$arResult["GRID_ID"]]["filter"]))
{
	foreach ($_SESSION["main.interface.grid"][$arResult["GRID_ID"]]["filter"] as $k => $v)
	{
		$_REQUEST['find_'.strtolower($k)] = $v;
	}
}



$this->IncludeComponentTemplate();
?>