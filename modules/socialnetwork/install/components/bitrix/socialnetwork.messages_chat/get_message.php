<?
define("NO_KEEP_STATISTIC", true);
define("NO_LANG_FILES", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/en/get_message.php");
@include_once($path);
$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/lang/".LANGUAGE_ID."/get_message.php");
@include_once($path);

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if (CModule::IncludeModule("socialnetwork"))
{
	$userId = IntVal($_REQUEST["user_id"]);

	$mptr = Trim($_REQUEST["mptr"]);
	$replyMessId = intval($_REQUEST["message_id"]);

	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		echo "*\r\n";
	}
	else
	{
		//messages from *all* users
		$bFirst = true;
		$currUserId = $GLOBALS["USER"]->GetID();
		
		$mptr_ts = MakeTimeStamp($mptr, "YYYY-MM-DD HH:MI:SS") - CTimeZone::GetOffset();
		$mptr = date("Y-m-d H:i:s", $mptr_ts);

		$dbMessages = CSocNetMessages::GetMessagesForChat($currUserId, 0, $mptr, false, $replyMessId);
		if ($dbMessages)
		{
			$parser = new CSocNetTextParser(LANGUAGE_ID, "/bitrix/images/socialnetwork/smile/");

			while ($arMessages = $dbMessages->GetNext())
			{
				if($arMessages["WHO"] <> "IN")
				{
					if($userId > 0)
					{
						if($userId != $arMessages["USER_ID"])
							continue;
					}
					else
					{
						if($bFirst)
							$bFirst = false;
						else
							continue;
					}
				}
				
				if($userId > 0 && $userId == $arMessages["USER_ID"] && StrLen($arMessages["DATE_VIEW"]) <= 0 && $arMessages["WHO"] == "IN")
					CSocNetMessages::Update($arMessages["ID"], array("=DATE_VIEW" => $DB->CurrentTimeFunction()));

				echo "m".$arMessages["USER_ID"]."\r\n";
				echo $arMessages["DATE_CREATE_FMT"]."\r\n";
				echo $arMessages["WHO"].$arMessages["ID"]."\r\n";
				echo $parser->convert($arMessages["~MESSAGE"],
					false,
					array(),
					array(
						"HTML" => "N",
						"ANCHOR" => "Y",
						"BIU" => "Y",
						"IMG" => "Y",
						"LIST" => "Y",
						"QUOTE" => "Y",
						"CODE" => "Y",
						"FONT" => "Y",
						"SMILES" => "Y",
						"UPLOAD" => "N",
						"NL2BR" => "N"
					)
				);
				echo "\r\n";
			}
		}

		//online status
		$db = CUser::GetList($by, $order, array("LAST_ACTIVITY"=>120));
		while($dba = $db->Fetch())
			if($dba['ID'] <> $currUserId)
				echo "+".$dba['ID']."\r\n";
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>