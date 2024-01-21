<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
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
			if (ini_get('browscap') != '')
			{
				$info = get_browser($userAgent);

				if ($info)
				{
					$deviceType = $this->getDeviceType($info);
					$browser = new Browser($info->browser, $info->platform, $deviceType);
				}
			}
		}

		if ($browser === null)
		{
			$browser = new Browser();
		}

		$browser->setUserAgent($userAgent);

		return $browser;
	}

	protected function getKnownAgent(?string $userAgent)
	{
		if ($userAgent !== null)
		{
			if (str_contains($userAgent, 'Bitrix24.Disk'))
			{
				if (str_contains($userAgent, 'Windows'))
				{
					return new Browser('Bitrix24.Disk', 'Windows', DeviceType::DESKTOP);
				}
				return new Browser('Bitrix24.Disk', 'macOS', DeviceType::DESKTOP);
			}

			if (str_contains($userAgent, 'BitrixDesktop'))
			{
				if (str_contains($userAgent, 'Windows'))
				{
					return new Browser('Bitrix24.Desktop', 'Windows', DeviceType::DESKTOP);
				}
				if (str_contains($userAgent, 'Mac OS'))
				{
					return new Browser('Bitrix24.Desktop', 'macOS', DeviceType::DESKTOP);
				}
				return new Browser('Bitrix24.Desktop', 'Linux', DeviceType::DESKTOP);
			}

			if (str_contains($userAgent, 'BitrixMobile') || str_contains($userAgent, 'Bitrix24/'))
			{
				if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Darwin'))
				{
					$device = (str_contains($userAgent, 'iPad') ? DeviceType::TABLET : DeviceType::MOBILE_PHONE);
					return new Browser('Bitrix24.Mobile', 'iOS', $device);
				}

				$device = (str_contains($userAgent, 'Tablet') ? DeviceType::TABLET : DeviceType::MOBILE_PHONE);
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
