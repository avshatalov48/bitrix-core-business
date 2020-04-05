<?php
namespace Bitrix\Im;

class Revision
{
	/**
	 * Web Api revision
	 *
	 * @filesource synchronize im/install/js/im/im.js:17
	 */
	const WEB = 120;
	/**
	 * Mobile Api revision
	 *
	 * @filesource synchronize mobile/install/components/bitrix/mobile.jscomponent/jscomponents/im.recent/component.js:6
	 * @filesource synchronize mobile/install/components/bitrix/mobile.webcomponent/webcomponents/im.dialog/bundle/component/js/component.js:3
	 * @filesource synchronize mobile/install/components/bitrix/mobile.webcomponent/webcomponents/im.dialog/bundle/component/js/mobile_dialog.js:22
	 */
	const MOBILE = 19;

	/**
	 * Rest Api revision
	 */
	const REST = 18;

	public static function getWeb()
	{
		return static::WEB;
	}

	public static function getMobile()
	{
		return static::MOBILE;
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
		];
	}
}