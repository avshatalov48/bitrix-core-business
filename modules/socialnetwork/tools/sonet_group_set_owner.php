<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$errorMessage = "";

if (check_bitrix_sessid())
{
	$GROUP_ID = intval($_REQUEST['GROUP_ID']);
	$USER_ID = intval($_REQUEST['USER_ID']);

	if ($GROUP_ID && $USER_ID && CModule::IncludeModule('socialnetwork'))
	{
		$arGroup = CSocNetGroup::GetByID($GROUP_ID);
		
		if (intval($arGroup["OWNER_ID"]) != $USER_ID)
		{
			if ($arGroup)
			{
				$CurrentUserPerms = CSocNetUserToGroup::InitUserPerms($GLOBALS["USER"]->GetID(), $arGroup, CSocNetUser::IsCurrentUserModuleAdmin($arGroup["SITE_ID"]));
				
				if ($CurrentUserPerms["UserCanModifyGroup"])
				{
					$res = CSocNetUserToGroup::SetOwner($USER_ID, $GROUP_ID, $arGroup);
					if (!$res && $e = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage = $e->GetString();

					if ($errorMessage == '')
						echo '<script>window.location.reload();</script>';					
					else
						echo '<script>alert(\''.CUtil::JSEscape($errorMessage).'\');</script>';
				}
				else
					echo '<script>alert(\'Access denied!\');</script>';
			}
			else
				echo '<script>alert(\'Group error!\');</script>';
		}
		else
		{
			// new owner is equal to old one
			echo '<script>window.location.reload();</script>';					
		}
	}
	else
		echo '<script>alert(\'Params error!\');</script>';
}
else
	echo '<script>alert(\'Session expired!\');</script>';
?>