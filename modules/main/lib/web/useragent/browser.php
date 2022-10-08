<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Web\UserAgent;

use Bitrix\Main;
use Bitrix\Main\DI;

class Browser
{
	protected $name;
	protected $platform;
	protected $deviceType = DeviceType::UNKNOWN;
	protected $userAgent;

	/**
	 * @param string|null $name
	 * @param string|null $platform
	 * @param int|null $deviceType
	 */
	public function __construct(?string $name = null, ?string $platform = null, ?int $deviceType = null)
	{
		$this->name = $name;
		$this->platform = $platform;
		if ($deviceType !== null)
		{
			$this->deviceType = $deviceType;
		}
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string|null $name
	 * @return Browser
	 */
	public function setName(?string $name): Browser
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPlatform(): ?string
	{
		return $this->platform;
	}

	/**
	 * @param string|null $platform
	 * @return Browser
	 */
	public function setPlatform(?string $platform): Browser
	{
		$this->platform = $platform;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getDeviceType(): int
	{
		return $this->deviceType;
	}

	/**
	 * @param int $deviceType
	 * @return Browser
	 */
	public function setDeviceType(int $deviceType): Browser
	{
		$this->deviceType = $deviceType;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getUserAgent(): ?string
	{
		return $this->userAgent;
	}

	/**
	 * @param string|null $userAgent
	 * @return Browser
	 */
	public function setUserAgent(?string $userAgent)
	{
		$this->userAgent = $userAgent;
		return $this;
	}

	/**
	 * @param string|null $userAgent
	 * @return Browser
	 */
	public static function detect(?string $userAgent = null): Browser
	{
		$serviceLocator = DI\ServiceLocator::getInstance();

		if($serviceLocator->has('main.browserDetector'))
		{
			$detector = $serviceLocator->get('main.browserDetector');
		}
		else
		{
			$detector = new Detector();
		}

		if ($userAgent === null)
		{
			$userAgent = Main\Context::getCurrent()->getServer()->getUserAgent();
		}

		return $detector->detectBrowser($userAgent);
	}
}
