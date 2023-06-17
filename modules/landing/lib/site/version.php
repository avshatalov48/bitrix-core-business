<?php
namespace Bitrix\Landing\Site;

class Version
{
	private const VERSIONS = [
		0 => null,
		1 => \Bitrix\Landing\Site\Update\ChatSales::class,
		2 => \Bitrix\Landing\Site\Update\ChatSalesOrder::class,
	];

	protected static $process = false;

	/**
	 * Updates specific site if needed.
	 * @param int $siteId Site id.
	 * @param int|null $version Site version.
	 * @return void
	 */
	public static function update(int $siteId, ?int $version = 0): void
	{
		if (self::$process)
		{
			return;
		}
		self::$process = true;

		$version = intval($version);

		if ($version >= count(self::VERSIONS) - 1)
		{
			return;
		}

		\Bitrix\Landing\Rights::setGlobalOff();

		foreach (self::VERSIONS as $updateVersion => $updateClass)
		{
			if ($updateVersion <= $version)
			{
				continue;
			}

			if (!$updateClass || !class_exists($updateClass))
			{
				continue;
			}

			if ($updateClass::update($siteId))
			{
				$version = $updateVersion;
			}
			else
			{
				break;
			}
		}

		\Bitrix\Landing\Internals\SiteTable::update($siteId, [
			'VERSION' => $version
		]);

		\Bitrix\Landing\Rights::setGlobalOn();
		self::$process = false;
	}
}
