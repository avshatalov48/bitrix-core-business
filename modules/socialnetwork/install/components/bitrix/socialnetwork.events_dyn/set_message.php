<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_LANG_FILES", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$path = str_replace(array("\\", "//"), "/", __DIR__."/lang/en/set_message.php");
@include_once($path);
$path = str_replace(array("\\", "//"), "/", __DIR__."/lang/".LANGUAGE_ID."/set_message.php");
@include_once($path);

if (CModule::IncludeModule("socialnetwork"))
{
	$userID = intval($_REQUEST["user_id"] ?? 0);

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
			if (
				isset($_REQUEST["EventType"])
				&& $_REQUEST["EventType"] == "FriendRequest"
				&& isset($_REQUEST["eventID"])
				&& intval($_REQUEST["eventID"]) > 0
			)
			{
				$errorMessage = "";

				if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "add")
				{
					if (!CSocNetUserRelations::ConfirmRequestToBeFriend($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}
				elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "reject")
				{
					if (!CSocNetUserRelations::RejectRequestToBeFriend($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if ($errorMessage <> '')
					echo $errorMessage;
			}
			elseif (
				isset($_REQUEST["EventType"])
				&& $_REQUEST["EventType"] == "GroupRequest"
				&& isset($_REQUEST["eventID"])
				&& intval($_REQUEST["eventID"]) > 0
			)
			{
				$errorMessage = "";

				if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "add")
				{
					if (!CSocNetUserToGroup::UserConfirmRequestToBeMember($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}
				elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "reject")
				{
					if (!CSocNetUserToGroup::UserRejectRequestToBeMember($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if ($errorMessage <> '')
					echo $errorMessage;
			}
			elseif (
				isset($_REQUEST["EventType"])
				&& $_REQUEST["EventType"] == "Message"
				&& isset($_REQUEST["eventID"])
				&& intval($_REQUEST["eventID"]) > 0
			)
			{
				$errorMessage = "";

				if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "close")
				{
					if (!CSocNetMessages::MarkMessageRead($GLOBALS["USER"]->GetID(), intval($_REQUEST["eventID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
						{
							if ($e->GetID() != "ERROR_NO_MESSAGE")
								$errorMessage .= $e->GetString();
						}
					}
				}

				if ($errorMessage <> '')
					echo $errorMessage;
			}
			elseif (
				isset($_REQUEST["EventType"])
				&& $_REQUEST["EventType"] == "Message"
				&& isset($_REQUEST["userID"])
				&& intval($_REQUEST["userID"]) > 0
			)
			{
				$errorMessage = "";

				if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "ban")
				{
					if (!CSocNetUserRelations::BanUser($GLOBALS["USER"]->GetID(), intval($_REQUEST["userID"])))
					{
						if ($e = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $e->GetString();
					}
				}

				if ($errorMessage <> '')
					echo $errorMessage;
			}
		}
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>