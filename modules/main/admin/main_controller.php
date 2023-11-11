<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

define("NOT_CHECK_PERMISSIONS", true);
define("SKIP_SITE_CLOSE", true);
require_once(__DIR__."/../include/prolog_before.php");
require_once(__DIR__."/../classes/general/controller_member.php");
IncludeModuleLangFile(__FILE__);

$skip_handler = false;
function __try_run()
{
	global $skip_handler, $oResponse;
	if($skip_handler)
		return;

	$res = ob_get_contents();

	if($oResponse->OK())
		return;

	$oResponse->status = "500 Execution Error";
	$oResponse->text = $res;
	return $oResponse->GetResponseBody(true);
}

ob_start("__try_run");
$oRequest = new CControllerClientRequestFrom();
$oResponse = new CControllerClientResponseTo($oRequest);

if($oRequest->operation == 'simple_register' && !$USER->IsAuthorized())
{
	$USER->Login($oRequest->arParameters['admin_login'], $oRequest->arParameters['admin_password']);

	if($USER->IsAdmin())
	{
		COption::SetOptionString("main", "controller_member_id", $oRequest->arParameters["member_id"]);
		COption::SetOptionString("main", "controller_member_secret_id", $oRequest->arParameters["member_secret_id"]);
		COption::SetOptionString("main", "controller_url", $oRequest->arParameters["controller_url"]);
		COption::SetOptionString("main", "~controller_disconnect_command", $oRequest->arParameters['disconnect_command']);
		eval($oRequest->arParameters['join_command']);
		$oResponse->status = "200 OK";
	}
	else
	{
		$oResponse->status = "472 Bad Request";
		$oResponse->text = GetMessage("MAIN_ADM_CONTROLLER_ERR8");
	}
}
elseif(!$oRequest->Check())
{
	$oResponse->status = "403 Access Denied";
	$oResponse->text = "Access Denied";
}
else
{
	switch($oRequest->operation)
	{
		case "ping":
			$oResponse->status = "200 OK";
			break;

		case "register":
			$ticket_id = COption::GetOptionString("main", "controller_ticket", "");
			list($ticket_created, $ticket_id, $controller_url) = explode("|", $ticket_id);
			if($ticket_id == $oRequest->arParameters["controller_ticket_id"])
			{
				if($controller_url <> '')
				{
					if($ticket_created>0 && $ticket_created+10*60>=time())
					{
						COption::SetOptionString("main", "~controller_disconnect_command", $oRequest->arParameters['disconnect_command']);
						eval($oRequest->arParameters['join_command']);
						$oResponse->status = "200 OK";
					}
					else
					{
						$oResponse->status = "412 Bad Request";
						$oResponse->text = GetMessage("MAIN_ADM_CONTROLLER_ERR2");
					}
				}
				else
				{
					$oResponse->status = "413 Bad Request";
					$oResponse->text = GetMessage("MAIN_ADM_CONTROLLER_ERR3");
				}
			}
			else
			{
				$oResponse->status = "417 Bad Request";
				$oResponse->text = GetMessage("MAIN_ADM_CONTROLLER_ERR4");
			}
			break;

		case "unregister":
			CControllerClient::Unlink();
			$oResponse->status = "200 OK";

			break;

		case "run":
			$arVars = Array(
					"command_id"=>$oRequest->arParameters["command_id"],
					);

			$oClientRequest = new CControllerClientRequestTo("query", $arVars);
			$oClientRequest->session_id = $oRequest->session_id;
			$oClientResponse = $oClientRequest->Send();
			if(is_object($oClientResponse) && $oClientResponse->Check())
			{
				if($oClientResponse->OK())
				{
					$command = $oClientResponse->arParameters['query'];

					if(CControllerClient::RunCommand($command, $oResponse, $oClientResponse) === false)
						$oResponse->status = "450 Execution error";
					else
						$oResponse->status = "200 OK";
				}
				else
				{
					$oResponse->status = $oClientResponse->status;
					$oResponse->text = $oClientResponse->text;
				}
			}
			else
			{
				$oResponse->status = "473 Access denied";
				$oResponse->text = GetMessage("MAIN_ADM_CONTROLLER_ERR5");
			}
			break;

		case "run_immediate":
			$command = $oRequest->arParameters["command"];
			if(CControllerClient::RunCommand($command, $oRequest, $oResponse) === false)
				$oResponse->status = "450 Execution error";
			else
				$oResponse->status = "200 OK";
			break;

		case 'sendfile':
			set_time_limit(1200);
			$arVars = Array(
					'command_id' => $oRequest->arParameters['command_id'],
					'sendfile' => 'Y',
					);
			$oClientRequest = new CControllerClientRequestTo('query', $arVars);
			$oClientRequest->session_id = $oRequest->session_id;
			$oClientResponse = $oClientRequest->Send();

			if(is_object($oClientResponse) && $oClientResponse->Check())
			{
				if($oClientResponse->OK())
				{
					if (CControllerTools::UnpackFileArchive($oClientResponse->arParameters['file'], $oClientResponse->arParameters['path_to']))
					{
						$oResponse->status = "200 OK";
						$command = $oClientResponse->arParameters['command'];
						if ($command <> '' && CControllerClient::RunCommand($command, $oResponse, $oClientResponse) === false)
						{
							$oResponse->status = "450 Execution error";
						}
					}
					else
					{
						$oResponse->status = "451 Copy File error";
						$e = $APPLICATION->GetException();
						if(is_object($e))
							$oResponse->text = $e->GetString();
					}

				}
				else
				{
					$oResponse->status = $oClientResponse->status;
					$oResponse->text = $oClientResponse->text;
				}
			}
			else
			{
				$oResponse->status = "473 Access denied";
				$oResponse->text = GetMessage("MAIN_ADM_CONTROLLER_ERR5");
			}

			break;
		case "check_auth":
			$dbUser = CUser::GetByLogin($oRequest->arParameters['login']);
			if(!($arUser = $dbUser->Fetch()))
			{
				$oResponse->status = "444 User is not found.";
				$oResponse->text = "User is not found.";
			}
			elseif($arUser["EXTERNAL_AUTH_ID"] <> '')
			{
				$oResponse->status = "445 External user.";
				$oResponse->text = "External user.";
			}
			else
			{
				if(mb_strlen($arUser["PASSWORD"]) > 32)
				{
					$salt = mb_substr($arUser["PASSWORD"], 0, mb_strlen($arUser["PASSWORD"]) - 32);
					$db_password = mb_substr($arUser["PASSWORD"], -32);
				}
				else
				{
					$salt = "";
					$db_password = $arUser["PASSWORD"];
				}


				if(
					$arUser['ACTIVE'] == 'Y'
					&& md5($db_password.'MySalt') == md5(md5($salt.$oRequest->arParameters['password']).'MySalt')
				)
				{
					$arSaveUser = CControllerClient::PrepareUserInfo($arUser);

					$arUserGroups = array();
					$dbUserGroups = CUser::GetUserGroupEx($arUser['ID']);
					while ($arG = $dbUserGroups->Fetch())
					{
						if ($arG["STRING_ID"] <> '')
							$arUserGroups[] = $arG["STRING_ID"];
						elseif ($arG["GROUP_ID"] == 1)
							$arUserGroups[] = "administrators";
						elseif ($arG["GROUP_ID"] == 2)
							$arUserGroups[] = "everyone";
					}
					$arSaveUser["GROUP_ID"] = $arUserGroups;

					if (CModule::IncludeModule("blog"))
					{
						$arBlogUser = CBlogUser::GetByID($arUser['ID'], BLOG_BY_USER_ID);
						if (is_array($arBlogUser) && $arBlogUser["AVATAR"] > 0)
							$arSaveUser["BLOG_AVATAR"] = CFile::GetPath($arBlogUser["AVATAR"]);
					}

					if (CModule::IncludeModule("forum"))
					{
						$arForumUser = CForumUser::GetByID($arUser['ID'], BLOG_BY_USER_ID);
						if (is_array($arForumUser) && $arForumUser["AVATAR"] > 0)
							$arSaveUser["FORUM_AVATAR"] = CFile::GetPath($arForumUser["AVATAR"]);
					}

					$oResponse->status = "200 OK";
					$oResponse->arParameters['USER_INFO'] = $arSaveUser;
					if (defined("FORMAT_DATE"))
						$oResponse->arParameters['FORMAT_DATE'] = FORMAT_DATE;
					if (defined("FORMAT_DATETIME"))
						$oResponse->arParameters['FORMAT_DATETIME'] = FORMAT_DATETIME;
				}
				else
				{
					$oResponse->status = "443 Bad password.";
					$oResponse->text = GetMessage("CTRLR_WS_ERR_BAD_PASSW");
				}
			}
			break;
		default:
			$oResponse->status = "401 Unsupported operation";
			$oResponse->text = GetMessage("MAIN_ADM_CONTROLLER_ERR6").' "'.$oRequest->operation.'"';
	}
}

$skip_handler = true;
$oResponse->text .= ob_get_contents();
ob_end_clean();

//ob_end_flush();

if($oRequest->Internal())
{
	$APPLICATION->RestartBuffer();
	$oResponse->Send();
	require_once(__DIR__."/../include/epilog_after.php");
}
else
{
	require_once(__DIR__."/../include/prolog_after.php");
	if($oResponse->OK())
	{
		echo $oResponse->text;
	}
	else
	{
		ShowError(GetMessage("MAIN_ADM_CONTROLLER_ERR7").' '.$oResponse->text.'. '.GetMessage("MAIN_ADM_CONTROLLER_ERR7_AGAIN"));
		if($_SERVER['HTTP_REFERER'] <> '')
			echo '<br>'.'<a href="'.htmlspecialcharsbx($_SERVER['HTTP_REFERER']).'">'.GetMessage("MAIN_ADM_CONTROLLER_BACK_URL").'</a>';
	}
	require_once(__DIR__."/../include/epilog.php");
}
