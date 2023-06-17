<?
define("PUBLIC_AJAX_MODE", true);
define("EXTRANET_NO_REDIRECT", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (
	!CModule::IncludeModule("socialnetwork")
	|| IsModuleInstalled("b24network")
)
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'MODULE_NOT_INSTALLED'));
	die();
}

if (check_bitrix_sessid())
{
	if (
		isset($_POST["nt"])
		&& !empty($_POST["nt"])
	)
	{
		preg_match_all("/(#NAME#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\\s|\\,/", urldecode($_REQUEST["nt"]), $matches);
		$nameTemplate = implode("", $matches[0]);
	}
	else
	{
		$nameTemplate = CSite::GetNameFormat(false);
	}

	if (isset($_POST['LD_SEARCH']) && $_POST['LD_SEARCH'] == 'Y')
	{
		CUtil::decodeURIComponent($_POST);

		$search = $_POST['SEARCH'];
		$searchConverted = (!empty($_POST['SEARCH_CONVERTED']) ? $_POST['SEARCH_CONVERTED'] : false);

		$searchResults = array();

		if (
			!isset($_POST['USER_SEARCH'])
			|| $_POST['USER_SEARCH'] != 'N'
		)
		{
			$searchResults['USERS'] = CSocNetLogDestination::SearchUsers(
				array(
					"SEARCH" => $search,
					"NAME_TEMPLATE" => $nameTemplate,
					"SELF" => true,
					"EMPLOYEES_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "I"),
					"EXTRANET_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "E"),
					"DEPARTAMENT_ID" => (
						isset($_POST['DEPARTMENT_ID'])
						&& intval($_POST['DEPARTMENT_ID']) > 0
							? intval($_POST['DEPARTMENT_ID'])
							: false
					),
					"EMAIL_USERS" => (isset($_POST['EMAIL_USERS']) && $_POST['EMAIL_USERS'] == 'Y'),
					"CHECK_ACTIVITY" => false
				),
				$searchModified
			);

			if (!empty($searchModified))
			{
				$searchResults['SEARCH'] = $searchModified;
			}

			if (
				empty($searchResults['USERS'])
				&& $searchConverted
				&& $search != $searchConverted
			)
			{
				$searchResults['USERS'] = CSocNetLogDestination::SearchUsers(
					array(
						"SEARCH" => $searchConverted,
						"NAME_TEMPLATE" => $nameTemplate,
						"SELF" => true,
						"EMPLOYEES_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "I"),
						"EXTRANET_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "E"),
						"DEPARTAMENT_ID" => (
							isset($_POST['DEPARTMENT_ID'])
							&& intval($_POST['DEPARTMENT_ID']) > 0
								? intval($_POST['DEPARTMENT_ID'])
								: false
						),
						"EMAIL_USERS" => (isset($_POST['EMAIL_USERS']) && $_POST['EMAIL_USERS'] == 'Y'),
						"CHECK_ACTIVITY" => false
					)
				);
				$searchResults['SEARCH'] = $searchConverted;
			}
		}

		echo CUtil::PhpToJsObject($searchResults);
	}
	elseif (isset($_POST['LD_DEPARTMENT_RELATION']) && $_POST['LD_DEPARTMENT_RELATION'] == 'Y')
	{
		echo CUtil::PhpToJsObject(Array(
			'USERS' => CSocNetLogDestination::GetUsers(Array('deportament_id' => $_POST['DEPARTMENT_ID'], "NAME_TEMPLATE" => $nameTemplate)),
		));
	}
	elseif (isset($_POST['LD_ALL']) && $_POST['LD_ALL'] == 'Y')
	{
		echo CUtil::PhpToJsObject(Array(
			'USERS' => CSocNetLogDestination::GetUsers(Array('all' => 'Y', "NAME_TEMPLATE" => $nameTemplate)),
		));
	}
	else
	{
		echo CUtil::PhpToJsObject(Array('ERROR' => 'UNKNOWN_ERROR'));
	}
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>