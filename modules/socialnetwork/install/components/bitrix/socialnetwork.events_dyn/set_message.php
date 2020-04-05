<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_LANG_FILES", true);
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
					if (!CSocNetUserRelations::ConfirmRequestToBeFriend($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"])))
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
					if (!CSocNetUserToGroup::UserConfirmRequestToBeMember($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"])))
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
			elseif ($_REQUEST["EventType"] == "Message" && IntVal($_REQUEST["eventID"]) > 0)
			{
				$errorMessage = "";

				if ($_REQUEST["action"] == "close")
				{
					if (!CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), IntVal($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
						{
							if ($e->GetID() != "ERROR_NO_MESSAGE")
								$errorMessage .= $e->GetString();
						}
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
		}
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>