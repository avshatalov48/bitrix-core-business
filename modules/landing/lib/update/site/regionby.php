<?php

namespace Bitrix\Landing\Update\Site;

use Bitrix\Landing\Internals\SiteTable;
use Bitrix\Landing\Rights;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Update\Stepper;

class RegionBy extends Stepper
{
	public const STEP_LIMIT = 50;

	protected static $moduleId = 'landing';

	/**
	 * One step of publish.
	 * @return bool
	 */
	public function execute(array &$option): bool
	{
		$region = Application::getInstance()->getLicense()->getRegion();
		if ($region !== 'by')
		{
			return false;
		}

		Rights::setGlobalOff();
		$steps = $option['steps'] ?? 0;
		if (!isset($option['count']))
		{
			$siteCount = SiteTable::query()
				->addSelect(Query::expr()->count('ID'), 'CNT')
				->where('DELETED', '=', 'N')
				->where('LANG', '=', 'ru')
				->whereIn('TYPE', ['PAGE', 'STORE'])
				->exec()
				->fetch()
			;
			$option['count'] = (int)$siteCount['CNT'];
		}

		$sites = SiteTable::query()
			->setSelect(['ID'])
			->where('DELETED', '=', 'N')
			->where('LANG', '=', 'ru')
			->whereIn('TYPE', ['PAGE', 'STORE'])
			->setLimit(self::STEP_LIMIT)
			->setOffset($steps)
			->exec()
		;
		while($site = $sites->fetch())
		{
			SiteTable::update($site['ID'], [
				'LANG' => 'by',
			]);
			$steps++;
		}

		$option['steps'] = $steps;
		Rights::setGlobalOn();

		return $option['steps'] !== $option['count'];
	}
}