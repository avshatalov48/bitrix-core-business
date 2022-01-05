<?php

namespace Bitrix\Rest\Configuration\DataProvider;

use Bitrix\Main\SystemException;
use Bitrix\Rest\Configuration\DataProvider\Disk;
use Bitrix\Rest\Configuration\DataProvider\IO;

/**
 * Class Controller
 * @package Bitrix\Rest\Configuration\DataProvider
 */
class Controller
{
	public const CODE_DISK = 'disk';
	public const CODE_IO = 'io';
	private const ITEM_LIST = [
		self::CODE_DISK => Disk\Base::class,
		self::CODE_IO => IO\Base::class,
	];

	private $errorList = [];

	/** @var Controller|null  */
	private static $instance;

	/**
	 * @return Controller
	 */
	public static function getInstance(): Controller
	{
		if (self::$instance === null)
		{
			self::$instance = new Controller();
		}

		return self::$instance;
	}

	/**
	 * Returns data provider by code
	 *
	 * @param string $code
	 * @param array $setting
	 *
	 * @return ProviderBase|null
	 */
	public function get(string $code, array $setting = []): ?ProviderBase
	{
		$result = null;

		$class = self::ITEM_LIST[$code];
		if ($class)
		{
			$result = $this->getProvider($class, $setting);
		}

		return $result;
	}

	private function getProvider(string $class, array $setting = []): ?ProviderBase
	{
		$result = null;
		if ($class && class_exists($class))
		{
			$this->resetErrors();
			try
			{
				$object = new $class($setting);
				if ($object instanceof ProviderBase)
				{
					$result = $object;
				}
			}
			catch (SystemException $error)
			{
				$this->setError($error->getCode(), $error->getMessage());
			}
		}

		return $result;
	}

	private function __construct()
	{
	}

	private function __clone()
	{
	}

	private function setError($code, $message): void
	{
		$this->errorList[$code] = $message;
	}

	private function resetErrors(): bool
	{
		$this->errorList = [];

		return true;
	}

	public function listError(): array
	{
		return $this->errorList;
	}
}
