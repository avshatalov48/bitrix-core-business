<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Web\UserAgent;

class Detector implements DetectorInterface
{
	/**
	 * @inheritDoc
	 */
	public function detectBrowser(?string $userAgent): Browser
	{
		$browser = $this->getKnownAgent($userAgent);

		if ($browser === null)
		{
			$info = get_browser($userAgent);

			$deviceType = $this->getDeviceType($info);

			$browser = new Browser($info->browser, $info->platform, $deviceType);
		}

		$browser->setUserAgent($userAgent);

		return $browser;
	}

	protected function getKnownAgent(?string $userAgent)
	{
		if ($userAgent !== null)
		{
			if (strpos($userAgent, 'Bitrix24.Disk') !== false)
			{
				if (strpos($userAgent, 'Windows') !== false)
				{
					return new Browser('Bitrix24.Disk', 'Windows', DeviceType::DESKTOP);
				}
				return new Browser('Bitrix24.Disk', 'macOS', DeviceType::DESKTOP);
			}

			if (strpos($userAgent, 'BitrixDesktop') !== false)
			{
				if (strpos($userAgent, 'Windows') !== false)
				{
					return new Browser('Bitrix24.Desktop', 'Windows', DeviceType::DESKTOP);
				}
				if (strpos($userAgent, 'Mac OS') !== false)
				{
					return new Browser('Bitrix24.Desktop', 'macOS', DeviceType::DESKTOP);
				}
				return new Browser('Bitrix24.Desktop', 'Linux', DeviceType::DESKTOP);
			}

			if (strpos($userAgent, 'BitrixMobile') !== false || strpos($userAgent, 'Bitrix24/') !== false)
			{
				if (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false || strpos($userAgent, 'Darwin') !== false)
				{
					$device = (strpos($userAgent, 'iPad') !== false ? DeviceType::TABLET : DeviceType::MOBILE_PHONE);
					return new Browser('Bitrix24.Mobile', 'iOS', $device);
				}

				$device = (strpos($userAgent, 'Tablet') !== false ? DeviceType::TABLET : DeviceType::MOBILE_PHONE);
				return new Browser('Bitrix24.Mobile', 'Android', $device);
			}
		}
		return null;
	}

	protected function getDeviceType($info)
	{
		if ($info->istablet)
		{
			$deviceType = DeviceType::TABLET;
		}
		elseif ($info->ismobiledevice)
		{
			$deviceType = DeviceType::MOBILE_PHONE;
		}
		elseif ($info->device_type == 'TV Device')
		{
			$deviceType = DeviceType::TV;
		}
		elseif ($info->device_type == 'Desktop')
		{
			$deviceType = DeviceType::DESKTOP;
		}
		else
		{
			$deviceType = DeviceType::UNKNOWN;
		}

		return $deviceType;
	}
}
