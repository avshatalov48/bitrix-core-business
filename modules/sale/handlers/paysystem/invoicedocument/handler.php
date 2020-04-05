<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;
use Bitrix\Crm\Integration;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses(
	'sale',
	[
		PaySystem\Manager::getClassNameFromPath('orderdocument') => 'handlers/paysystem/orderdocument/handler.php'
	]
);

/**
 * Class InvoiceDocumentHandler
 * @package Sale\Handlers\PaySystem
 */
class InvoiceDocumentHandler extends OrderDocumentHandler
{
	/**
	 * @return string
	 */
	protected static function getDataProviderClass()
	{
		return Integration\DocumentGenerator\DataProvider\Invoice::class;
	}
}