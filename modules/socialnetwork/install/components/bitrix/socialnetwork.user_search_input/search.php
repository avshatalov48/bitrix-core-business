<?Define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (
	CModule::IncludeModule("socialnetwork")
	&& !IsModuleInstalled("b24network")
)
{
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		$bIntranet = IsModuleInstalled('intranet');

		if (!Function_Exists("__UnEscapeTmp"))
		{
			function __UnEscapeTmp(&$item, $key)
			{
				if (Is_Array($item))
					Array_Walk($item, '__UnEscapeTmp');
				else
				{
					if (mb_strpos($item, "%u") !== false)
						$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
				}
			}
		}

		Array_Walk($_REQUEST, '__UnEscapeTmp');
		$arParams = array();
		$params = Explode(",", $_REQUEST["params"]);
		foreach ($params as $param)
		{
			list($key, $val) = Explode(":", $param);
			$arParams[$key] = $val;
		}
		$arParams["pe"] = intval($arParams["pe"]);
		if ($arParams["pe"] <= 0 || $arParams["pe"] > 50)
			$arParams["pe"] = 10;
		$arParams["gf"] = intval($arParams["gf"]);

		$signer = new \Bitrix\Main\Security\Sign\Signer;

		try {
			$nt = $signer->unsign($arParams["nt"]);
			$arParams["NAME_TEMPLATE"] = str_replace(
				array("#EMAIL#", "#LOGIN#", "#NOBR#", "#/NOBR#", "#COMMA#"), 
				array(" ", " ", " ", " ", ","), 
				trim($nt)
			);
		}
		catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
			$arParams["NAME_TEMPLATE"] = str_replace("#COMMA#",",", CSite::GetNameFormat(false));
		}

		$arParams['NAME_TEMPLATE'] .= ($bIntranet ? ' <#EMAIL#>' : '');
		$arParams['NAME_TEMPLATE'] .= " [#ID#]";

		try {
			$sl = $signer->unsign($arParams["sl"]);
			$bUseLogin = (trim($sl) != "N");
		}
		catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
			$bUseLogin = false;
		}

		if (CModule::IncludeModule('extranet'))
		{
			if (CExtranet::IsIntranetUser($arParams["site"]))
			{
				$arUsersInMyGroupsID = CExtranet::GetMyGroupsUsers($arParams["site"]);
				$arIntranetUsersID = CExtranet::GetIntranetUsers();
				$arUsersToFilter = array_merge($arUsersInMyGroupsID, $arIntranetUsersID);
			}
			else
			{
				$arUsersInMyGroupsID = CExtranet::GetMyGroupsUsers($arParams["site"]);
				$arPublicUsersID = CExtranet::GetPublicUsers();
				$arUsersToFilter = array_merge($arUsersInMyGroupsID, $arPublicUsersID);
			}
		}

		$arResult = array();

		$dbUsers = CSocNetUser::SearchUsers($_REQUEST["search"], $arParams["gf"], $arParams["pe"]);
		if ($dbUsers && ($arUser = $dbUsers->Fetch()))
		{
			do
			{
				if (
					(
					is_array($arUsersToFilter)
					&& in_array($arUser["ID"], $arUsersToFilter)
					)
					|| !is_array($arUsersToFilter)
				)
				{
					$arResult[] = array("NAME" => CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin));
				}
			}
			while ($arUser = $dbUsers->Fetch());
		}
		?><?=CUtil::PhpToJSObject($arResult)?><?
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
		die();
	}
}
?>