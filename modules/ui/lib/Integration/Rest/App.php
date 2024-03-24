<?php

namespace Bitrix\UI\Integration\Rest;

use \Bitrix\Main;
use \Bitrix\Rest;
use \Bitrix\UI\Avatar\Mask;
use \Bitrix\UI\Avatar;

class App
{
	private const REST_STATISTIC_MASK_ENTITY_NAME = 'MASK';

	public static function onRestAppDelete($app)
	{
		if (isset($app['APP_ID'])
			&& $app['APP_ID']
			&& ($app = Rest\AppTable::getByClientId($app['APP_ID']))
		)
		{
			try
			{
				(new Mask\Owner\RestApp($app['ID']))->delete();
				if (!Avatar\Model\ItemTable::getList(['filter' => ['=OWNER_TYPE' => Mask\Owner\RestApp::class], 'limit' => 1])->fetch())
				{
					\CAgent::RemoveAgent(__CLASS__ . '::sendRestStatistic();', 'ui');
				}
			}
			catch (\Throwable $e)
			{
				// in case we do not
			}
		}
	}

	public static function OnRestAppInstall()
	{
		\CAgent::addAgent(
			__CLASS__.'::sendRestStatistic();',
			'ui',
			'N',
			86400,
			"",
			"Y",
			"",
			100,
			false,
			false
		);
	}

	public static function sendRestStatistic()
	{
		if (
			Main\Loader::includeModule('rest')
			&& is_callable(['\Bitrix\Rest\UsageStatTable', 'logUserInterface'])
		)
		{
			$dbRes = Avatar\Model\ItemToFileTable::getList([
				'select' => ['APP_ID' => 'ITEM.OWNER_ID', 'CNT'],
				'filter' => [
					'=ITEM.OWNER_TYPE' => Mask\Owner\RestApp::class,
				],
				'runtime' => [new Main\Entity\ExpressionField('CNT', 'COUNT(%s)', 'ID')],
				'group' => ['ITEM.OWNER_ID'],
			]);
			while ($res = $dbRes->fetch())
			{
				Rest\UsageStatTable::logUserInterface(
					$res['APP_ID'],
					static::REST_STATISTIC_MASK_ENTITY_NAME,
					$res['CNT']
				);
			}
			Rest\UsageStatTable::finalize();
		}

		return __CLASS__ . '::' . __FUNCTION__ . '();';
	}
}
