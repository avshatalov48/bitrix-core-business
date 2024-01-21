<?php
namespace Bitrix\Im;

class Revision
{
	/**
	 * Web Api revision
	 *
	 * @filesource synchronize im/install/js/im/im.js:38
	 */
	const WEB = 130;

	/**
	 * Mobile Api revision
	 *
	 * @filesource synchronize immobile/install/mobileapp/immobile/components/im/im.recent/component.js:9
	 * @filesource synchronize immobile/install/mobileapp/immobile/components/im/messenger/component.js:1
	 * @filesource synchronize immobile/install/components/bitrix/immobile.webcomponent/webcomponents/im.dialog/bundle/component/src/component.js:3
	 * @filesource synchronize immobile/install/components/bitrix/immobile.webcomponent/webcomponents/im.dialog/bundle/component/src/mobile_dialog.js:22
	 */
	const MOBILE = 19;

	/**
	 * Desktop Api revision
	 */
	const DESKTOP = 5;

	/**
	 * Rest Api revision
	 */
	const REST = 32;

	public static function getWeb()
	{
		return static::WEB;
	}

	public static function getMobile()
	{
		return static::MOBILE;
	}

	public static function getDesktop()
	{
		return static::DESKTOP;
	}

	public static function getRest()
	{
		return static::REST;
	}

	public static function get()
	{
		return [
			'rest' => static::getRest(),
			'web' => static::getWeb(),
			'mobile' => static::getMobile(),
			'desktop' => static::getDesktop(),
		];
	}
}
