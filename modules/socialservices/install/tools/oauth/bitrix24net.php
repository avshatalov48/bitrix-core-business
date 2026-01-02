<?
/*
This is callback page for Bitrix24.Net OAuth 2.0 authentication.
Bitrix24.Net redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on Bitrix24.Net.
*/

/**
 * Bitrix vars
 *
 * @global \CUser $USER
 *
 */

define("NOT_CHECK_PERMISSIONS", true);

$state = $_REQUEST['state'] ?? null;
if (is_string($state))
{
	$parts = explode('.', $state);
	if (count($parts) === 3)
	{
		if ($parts[1] === '1')
		{
			define('ADMIN_SECTION', true);
		}
		elseif (!empty($parts[0]) && preg_match('/^[a-z0-9_]{2}$/i', $parts[0], $m))
		{
			define('SITE_ID', (string)$m[0]);
		}
	}
}

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

if(isset($_REQUEST["update_broadcast"]))
{
	\Bitrix\Main\Config\Option::set("socialservices", "network_last_update", time());
}
else
{
	if(CModule::IncludeModule("socialservices"))
	{
		if(isset($_REQUEST['apcode']))
		{
			if($USER->IsAuthorized())
			{
				if(\Bitrix\Socialservices\ApManager::receive($USER->GetID(), $_REQUEST['apcode']))
				{
					$arState =
						is_string($state)
							? \Bitrix\Socialservices\OAuth\StateService::getInstance()->getPayload($state)
							: null
					;
					if(isset($arState['backurl']))
					{
						LocalRedirect($arState['backurl']);
					}
					elseif(defined('ADMIN_SECTION'))
					{
						LocalRedirect('/bitrix/admin/');
					}
					else
					{
						LocalRedirect('/');
					}
				}
			}
		}
		else
		{
			$oAuthManager = new CSocServAuthManager();
			$oAuthManager->Authorize("Bitrix24Net");
		}
	}
}

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");
?>
