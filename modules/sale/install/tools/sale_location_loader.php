<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main;
use Bitrix\Sale\Location\Admin\LocationHelper as Helper;

Main\Loader::includeModule('sale');

$APPLICATION->ShowHeadStrings();
$APPLICATION->ShowCSS();

$properties = $_REQUEST['PROPERTIES'];

$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
	"ID" => intval($properties['ID']) ? intval($properties['ID']) : '',
	"CODE" => $properties['CODE'] <> ''? $properties['CODE'] : '',
	"INPUT_NAME" => $properties['INPUT_NAME'],
	"PROVIDE_LINK_BY" => $properties['PROVIDE_LINK_BY'] == 'id' ? 'id' : 'code',

	//"FILTER_BY_SITE" => isset($properties['FILTER_SITE_ID']) ? 'Y' : 'N', //???
	"FILTER_SITE_ID" => $properties['FILTER_SITE_ID'],

	"SHOW_DEFAULT_LOCATIONS" => 'Y',
	"SEARCH_BY_PRIMARY" => $properties['SEARCH_BY_PRIMARY'],

	"JS_CONTROL_GLOBAL_ID" => 'SALE_LOCATION_SELECTOR_RESOURCES',
	"USE_JS_SPAWN" => 'Y'
	//"INITIALIZE_BY_GLOBAL_EVENT" => 'sale-event-never-occur'
	),
	false,
	array('HIDE_ICONS' => 'Y')
);
