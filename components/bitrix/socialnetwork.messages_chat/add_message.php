<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_LANG_FILES", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$path = str_replace(array("\\", "//"), "/", __DIR__."/lang/en/add_message.php");
@include_once($path);
$path = str_replace(array("\\", "//"), "/", __DIR__."/lang/".LANGUAGE_ID."/add_message.php");
@include_once($path);

if (CModule::IncludeModule("socialnetwork"))
{
	$aUserId = array();
	if (is_array($_REQUEST["user_id"] ?? null))
	{
		foreach($_REQUEST["user_id"] as $id)
		{
			if (intval($id) > 0)
			{
				$aUserId[] = intval($id);
			}
		}
	}
	elseif (
		isset($_REQUEST["user_id"])
		&& intval($_REQUEST["user_id"]) > 0
	)
	{
		$aUserId[] = intval($_REQUEST["user_id"]);
	}

	$aUserId = array_unique($aUserId);

	$mptr = Trim($_REQUEST["mptr"]);

	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		echo "*";
	}
	else
	{
		if (!check_bitrix_sessid())
		{
			echo GetMessage("SONET_C50_ERR_PERMS").".";
		}
		else
		{
			$message = $_REQUEST["data"];
			$message = Trim($message);
			if ($message == '')
			{
				echo GetMessage("SONET_C50_NO_TEXT").".";
			}
			else
			{
				if(empty($aUserId))
				{
					echo GetMessage("SONET_C50_NO_USER_ID").".";
				}
				else
				{
					foreach($aUserId as $userID)
					{
						if (!CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $userID, "message", CSocNetUser::IsCurrentUserModuleAdmin(false)))
						{
							echo GetMessage("SONET_C50_CANT_WRITE").".";
						}
						else
						{
							$errorMessage = "";
							if (!CSocNetMessages::CreateMessage($GLOBALS["USER"]->GetID(), $userID, $message))
							{
								if ($e = $GLOBALS["APPLICATION"]->GetException())
									$errorMessage .= $e->GetString();
							}
							if ($errorMessage <> '')
								echo $errorMessage;
						}
					}
				}
			}
		}
	}
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>