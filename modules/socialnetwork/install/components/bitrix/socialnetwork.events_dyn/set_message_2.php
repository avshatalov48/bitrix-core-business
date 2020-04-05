<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_LANG_FILES", true);
define("PUBLIC_AJAX_MODE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/en/set_message.php");
@include_once($path);
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/".LANGUAGE_ID."/set_message.php");
@include_once($path);

if (CModule::IncludeModule("socialnetwork"))
{

	$userID = IntVal($_REQUEST["user_id"]);

	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		echo "*\r\n";
	}
	else
	{
		if (!check_bitrix_sessid())
		{
			echo GetMessage("SONET_C2_SECURITY_ERROR").".";
		}
		else
		{
			if ($_REQUEST["EventType"] == "FriendRequest" && IntVal($_REQUEST["eventID"]) > 0)
			{
				$errorMessage = "";

				if ($_REQUEST["action"] == "add")
				{
					$bAutoSubscribe = (array_key_exists("uas", $_REQUEST) && $_REQUEST["uas"] == "N" ? false : true);
					if (!CSocNetUserRelations::ConfirmRequestToBeFriend($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"]), $bAutoSubscribe))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}
				elseif ($_REQUEST["action"] == "reject")
				{
					if (!CSocNetUserRelations::RejectRequestToBeFriend($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if (strlen($errorMessage) > 0)
					echo $errorMessage;
			}
			elseif ($_REQUEST["EventType"] == "GroupRequest" && IntVal($_REQUEST["eventID"]) > 0)
			{
				$errorMessage = "";

				if ($_REQUEST["action"] == "add")
				{
					$bAutoSubscribe = (array_key_exists("uas", $_REQUEST) && $_REQUEST["uas"] == "N" ? false : true);
					if (!CSocNetUserToGroup::UserConfirmRequestToBeMember($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"]), $bAutoSubscribe))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}
				elseif ($_REQUEST["action"] == "reject")
				{
					if (!CSocNetUserToGroup::UserRejectRequestToBeMember($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if (strlen($errorMessage) > 0)
					echo $errorMessage;
			}
			elseif ($_REQUEST["EventType"] == "Message" && IntVal($_REQUEST["userID"]) > 0)
			{
				$errorMessage = "";

				if ($_REQUEST["action"] == "ban")
				{
					if (!CSocNetUserRelations::BanUser($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["userID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if (strlen($errorMessage) > 0)
					echo $errorMessage;
			}			
			elseif ($_REQUEST["EventType"] == "Message")
			{
				$errorMessage = "";

				if (($_REQUEST["action"] == "close" || $_REQUEST["action"] == "read") && IntVal($_REQUEST["eventID"]) > 0)
				{
					if (!CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"]), true))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
						{
							if ($e->GetID() != "ERROR_NO_MESSAGE")
								$errorMessage .= $e->GetString();
						}
					}
				}
				elseif ($_REQUEST["action"] == "unread" && IntVal($_REQUEST["eventID"]) > 0)
				{
					if (!CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"]), false))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
						{
							if ($e->GetID() != "ERROR_NO_MESSAGE")
								$errorMessage .= $e->GetString();
						}
					}
				}
				elseif ($_REQUEST["action"] == "setts" && IntVal($_REQUEST["ts"]) > 0)
					CUserOptions::SetOption('socialnetwork', 'SONET_EVENT_TIMESTAMP', $_REQUEST["ts"] - CTimeZone::GetOffset());

				if (strlen($errorMessage) > 0)
					echo $errorMessage;
			}
			elseif ($_REQUEST["EventType"] == "Dialog")
			{
				$errorMessage = "";

				if ($_REQUEST["action"] == "setpos" && IntVal($_REQUEST["top"]) > 0 && IntVal($_REQUEST["left"]) > 0)
					CUserOptions::SetOption('socialnetwork', 'SONET_EVENT_POS', array("left" => $_REQUEST["left"], "top" => $_REQUEST["top"]));

				if (strlen($errorMessage) > 0)
					echo $errorMessage;
			}
			
		}
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>