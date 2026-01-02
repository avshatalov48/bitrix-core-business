<?php
namespace Bitrix\Report\VisualConstructor\Helper;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Crm\Service\Container;

/**
 * Class Analytic
 */
class Analytic
{
	/**
	 * @TODO maybe need to add some logic of access for different analytic pages
	 *
	 * @return bool
	 */
	public static function isEnable()
	{
		if (Loader::includeModule('crm'))
		{
			return Container::getInstance()->getUserPermissions()->entityType()->canReadSomeItemsInCrm();
		}
		else
		{
			return false;
		}
	}
}
