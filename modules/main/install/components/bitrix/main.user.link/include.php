<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Page\AssetMode;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!function_exists('MULChangeOnlineStatus'))
{
	function MULChangeOnlineStatus($USER_ID = false, $HTML_ID = false)
	{
		static $arUserList, $arUserListID, $arUserListHTML_ID;
		static $bNotFirstCall;

		if (!$bNotFirstCall)
		{
			AddEventHandler("main", "OnBeforeEndBufferContent", "MULChangeOnlineStatus");
			$bNotFirstCall = true;
		}

		if (intval($USER_ID) > 0)
		{
			if (!$HTML_ID)
				$HTML_ID = "main_".$USER_ID;

			$arUserListID[] = $USER_ID;
			$arUserListHTML_ID[] = "'".$HTML_ID."'";
			$arUserList[] = array("USER_ID" => $USER_ID, "HTML_ID" => $HTML_ID);
		}
		else
		{
			$arUserListIDUnique = array_unique($arUserListID);
			$strUserListID = implode("|", $arUserListIDUnique);
			$rsUser = CUser::GetList("id", "desc", array("ID" => $strUserListID));

			$arUserListOnlineHTML_ID = array();
			while($arUser = $rsUser->Fetch())
			{
				if ((time() - intval(MakeTimeStamp($arUser["LAST_ACTIVITY_DATE"], "YYYY-MM-DD HH-MI-SS"))) < CUser::GetSecondsForLimitOnline()) // TODO change to use CUser::GetOnlineStatus see more in docs.bx
				{
					foreach($arUserList as $arTmp)
						if ($arUser["ID"] == $arTmp["USER_ID"])
							$arUserListOnlineHTML_ID[] = "'".$arTmp["HTML_ID"]."'";
				}
			}


			$js = '<script>

			BX.ready(function() {
				var arMULUserList = ['.implode(",", $arUserListHTML_ID).'];
				var arMULUserListOnline = ['.implode(",", $arUserListOnlineHTML_ID).'];

				for(var i=0; i<arMULUserList.length; i++)
				{
					var elOnline = document.getElementById(arMULUserList[i]);
					if(elOnline)
					{
						if (BX.util.in_array(arMULUserList[i], arMULUserListOnline))
						{
							elOnline.className = "bx-user-info-online";
							elOnline.title = "'.GetMessageJS("MAIN_UL_ONLINE").'";
						}
						else
						{
							elOnline.className = "bx-user-info-offline";
							elOnline.title = "";
						}

					}

				}
			});
			</script>';

			Asset::getInstance()->addString(
				$js,
				false,
				AssetLocation::AFTER_JS,
				AssetMode::ALL
			);
		}
	}
}

?>