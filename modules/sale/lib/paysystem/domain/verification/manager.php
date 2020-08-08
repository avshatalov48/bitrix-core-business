<?php
namespace Bitrix\Sale\PaySystem\Domain\Verification;

use Bitrix\Main,
	Bitrix\Sale;

/**
 * Class Manager
 * @package Bitrix\Sale\PaySystem\Domain\Verification
 */
final class Manager extends Sale\Domain\Verification\BaseManager
{
	/** @var array */
	private static $entityList = [];

	/**
	 * @inheritDoc
	 */
	public static function getPathPrefix(): string
	{
		return "/.well-known/";
	}

	/**
	 * @inheritDoc
	 */
	protected static function getUrlRewritePath(): string
	{
		return "/bitrix/services/sale/domainverification.php";
	}

	/**
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\IO\FileNotFoundException
	 */
	protected static function getEntityList() : array
	{
		if (empty(self::$entityList))
		{
			foreach (Sale\PaySystem\Manager::getHandlerList() as $handlers)
			{
				foreach (array_keys($handlers) as $handler)
				{
					/** @var IVerificationable $className */
					[$className] = Sale\PaySystem\Manager::includeHandler($handler);
					if (is_subclass_of($className, IVerificationable::class))
					{
						if ($className::getModeList())
						{
							foreach ($className::getModeList() as $mode)
							{
								self::$entityList[] = $handler.$mode;
							}
						}
						else
						{
							self::$entityList[] = $handler;
						}
					}
				}
			}
		}

		return self::$entityList;
	}
}