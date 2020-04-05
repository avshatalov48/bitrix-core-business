<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Config;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Yandex') => 'handlers/paysystem/yandex/handler.php'));

class YandexReferrerHandler extends YandexHandler
{
	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return array('BX_HANDLER' => 'YANDEX_REFERRER');
	}
}