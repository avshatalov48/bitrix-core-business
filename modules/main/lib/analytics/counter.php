<?php
namespace Bitrix\Main\Analytics;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Text\JsExpression;

class Counter
{
	protected static $data = array();
	protected static $enabled = true;
	protected static $bufferRestarted = false;

	public static function enable()
	{
		static::$enabled = true;
	}

	public static function disable()
	{
		static::$enabled = false;
	}

	public static function getInjectedJs($stripTags = false)
	{
		$accountId = static::getAccountId();
		$params = static::injectDataParams();

		$host = Context::getCurrent()->getServer()->getHttpHost();
		$host = preg_replace("/:(80|443)$/", "", $host);
		$host = \CUtil::JSEscape($host);

		$js = <<<JS
			var _ba = _ba || []; _ba.push(["aid", "{$accountId}"]); _ba.push(["host", "{$host}"]); {$params}
			(function() {
				var ba = document.createElement("script"); ba.type = "text/javascript"; ba.async = true;
				ba.src = (document.location.protocol == "https:" ? "https://" : "http://") + "bitrix.info/ba.js";
				var s = document.getElementsByTagName("script")[0];
				s.parentNode.insertBefore(ba, s);
			})();
JS;

		$js = str_replace(array("\n", "\t"), "", $js);
		if ($stripTags === false)
		{
			return "<script type=\"text/javascript\">".$js."</script>";
		}
		else
		{
			return $js;
		}
	}

	public static function injectIntoPage()
	{
		Asset::getInstance()->addString(static::getInjectedJs(), false, AssetLocation::AFTER_JS);
	}

	public static function getAccountId()
	{
		$license = Application::getInstance()->getLicense();
		if (!$license->isDemoKey())
		{
			return $license->getPublicHashKey();
		}
		else
		{
			return "";
		}
	}

	public static function getPrivateKey()
	{
		$license = Application::getInstance()->getLicense();
		if (!$license->isDemoKey())
		{
			return $license->getHashLicenseKey();
		}
		else
		{
			return "";
		}
	}

	public static function onBeforeEndBufferContent()
	{
		$request = Context::getCurrent()->getRequest();
		$isAjaxRequest = $request->isAjaxRequest();
		$isAdminSection = defined("ADMIN_SECTION") && ADMIN_SECTION === true;
		if ($isAjaxRequest || $isAdminSection)
		{
			return;
		}

		$isSlider = $request->getQuery('IFRAME') === "Y";
		if (static::$bufferRestarted === true && !$isSlider)
		{
			return;
		}

		$settings = Configuration::getValue("analytics_counter");
		$forceEnabled = isset($settings["enabled"]) && $settings["enabled"] === true;

		if ($forceEnabled === false && SiteSpeed::isIntranetSite(SITE_ID))
		{
			return;
		}

		if (SiteSpeed::isOn() && static::$enabled === true)
		{
			Counter::injectIntoPage();
		}
	}

	public static function onBeforeRestartBuffer()
	{
		static::$bufferRestarted = true;
	}

	public static function sendData($id, array $arParams)
	{
		static::$data[$id] = $arParams;
	}

	private static function injectDataParams()
	{
		$result = "";
		foreach (static::$data as $index => $arItem)
		{
			foreach ($arItem as $key => $value)
			{
				if (is_array($value))
				{
					$jsValue = '"'.\CUtil::PhpToJSObject($value).'"';
				}
				elseif ($value instanceof JsExpression)
				{
					$jsValue = $value;
				}
				else
				{
					$jsValue = '"'.\CUtil::JSEscape($value).'"';
				}

				$result .= '_ba.push(["ad['.$index.']['.\CUtil::JSEscape($key).']", '.$jsValue.']);';
			}
		}

		return $result;
	}
}