<?

require_once __DIR__.'/autoload.php';


class CRestEventHandlers
{
	public static function OnBeforeProlog()
	{
		if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
		{
			$p = COption::GetOptionString("rest", "server_path", "/rest")."/";
			if(mb_substr(mb_strtolower($_SERVER['REQUEST_URI']), 0, mb_strlen($p)) === $p)
			{
				if(!defined('BX24_REST_SKIP_SEND_HEADERS'))
				{
					CRestUtil::sendHeaders();
				}

				die();
			}
		}
	}
}

CJSCore::registerExt('marketplace', array(
	'js' => '/bitrix/js/rest/marketplace.js',
	'css' => '/bitrix/js/rest/css/marketplace.css',
	'lang' => BX_ROOT.'/modules/rest/lang/'.LANGUAGE_ID.'/jsmarketplace.php',
	'lang_additional' => array(
		'REST_MARKETPLACE_CATEGORY_URL' => \Bitrix\Rest\Marketplace\Url::getCategoryUrl(),
		'REST_BUY_SUBSCRIPTION_URL' => \Bitrix\Rest\Marketplace\Url::getSubscriptionBuyUrl(),
		'CAN_BUY_SUBSCRIPTION' => \Bitrix\Rest\Marketplace\Client::canBuySubscription() ? 'Y' : 'N',
		'CAN_ACTIVATE_DEMO_SUBSCRIPTION' => \Bitrix\Rest\Marketplace\Client::isSubscriptionDemoAvailable() ? 'Y' : 'N',
	),
	'rel' => array('ajax', 'popup', 'access', 'sidepanel', 'ui.notification'),
));

CJSCore::registerExt('applayout', array(
	'js' => '/bitrix/js/rest/applayout.js',
	'css' => '/bitrix/js/rest/css/applayout.css',
	'lang' => BX_ROOT.'/modules/rest/lang/'.LANGUAGE_ID.'/jsapplayout.php',
	'lang_additional' => array(
		'REST_APPLICATION_URL' => \Bitrix\Rest\Marketplace\Url::getApplicationUrl(),
		'REST_APPLICATION_VIEW_URL' => \Bitrix\Rest\Marketplace\Url::getApplicationPlacementViewUrl(),
		'REST_PLACEMENT_URL' => \Bitrix\Rest\Marketplace\Url::getApplicationPlacementUrl()
	),
	'rel' => array('ajax', 'popup', 'sidepanel'),
));

CJSCore::registerExt('appplacement', array(
	'js' => '/bitrix/js/rest/appplacement.js',
	'rel' => array('ajax', 'applayout'),
));

CJSCore::registerExt('restclient', array(
	'skip_core' => true,
	'rel' => array('rest.client'),
));

CJSCore::registerExt('rest_userfield', array(
	'js' => '/bitrix/js/rest/userfield.js',
	'rel' => array('applayout'),
));

CJSCore::registerExt(
	'rest.integration',
	[
		'js' => '/bitrix/js/rest/integration.js',
		'lang' => BX_ROOT.'/modules/rest/jsintegration.php',
		'rel' => [
			'ajax',
			'ui.notification',
		],
	]
);
?>