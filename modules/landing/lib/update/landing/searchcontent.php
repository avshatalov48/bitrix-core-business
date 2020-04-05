<?php
namespace Bitrix\Landing\Update\Landing;

use \Bitrix\Landing\Internals\LandingTable;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;

/**
 * Class SearchContent
 * Index hook data.
 * @package Bitrix\Landing\Update\Landing
 */
class SearchContent extends Stepper
{
	/**
	 * Option code for store
	 */
	const OPTION_CODE = 'update_landing_search_content';
	const OPTION_CODE_SCOPES = 'update_landing_search_content_scopes';

	/**
	 * Module id for parent class.
	 * @var string
	 */
	protected static $moduleId = 'landing';

	/**
	 * One step of converter.
	 * @param array &$result Result array.
	 * @return bool
	 */
	public function execute(array &$result)
	{
		$lastId = Option::get('landing', self::OPTION_CODE, 0);
		$scopes = Option::get('landing', self::OPTION_CODE_SCOPES, '');
		$scopes = unserialize($scopes);
		$finished = true;

		\Bitrix\Landing\Rights::setGlobalOff();

		if (!isset($result['steps']))
		{
			$result['steps'] = 0;
		}

		if (!is_array($scopes))
		{
			$scopes = [];
		}

		if (isset($scopes[0]))
		{
			\Bitrix\Landing\Site\Type::clearScope();
			\Bitrix\Landing\Site\Type::setScope($scopes[0]);
		}

		// calculate count of records, which we need
		$res = LandingTable::getList([
			'select' => [
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			],
			'filter' => [
				'=DELETED' => ['Y', 'N'],
				'=SITE.DELETED' => ['Y', 'N']
			]
		]);
		if ($row = $res->fetch())
		{
			$result['count'] = $row['CNT'];
		}

		// one group for update
		$res = LandingTable::getList([
			'select' => [
				'ID',
				'SITE_TYPE' => 'SITE.TYPE'
			],
			'filter' => [
				'>ID' => $lastId,
				'=DELETED' => ['Y', 'N'],
				'=SITE.DELETED' => ['Y', 'N']
			],
			'order' => [
				'ID' => 'ASC'
			],
			'limit' => 20
		]);
		while ($row = $res->fetch())
		{
			$finished = false;
			$lastId = $row['ID'];
			$result['steps']++;

			\Bitrix\Landing\Hook::indexLanding(
				$row['ID']
			);
		}

		if ($finished && $scopes)
		{
			array_shift($scopes);
			if ($scopes)
			{
				$finished = false;
				$lastId = 0;
				$result['steps'] = 0;
			}
		}

		\Bitrix\Landing\Rights::setGlobalOn();

		if (!$finished)
		{
			$scopes = array_values($scopes);
			Option::set('landing', self::OPTION_CODE, $lastId);
			Option::set('landing', self::OPTION_CODE_SCOPES, serialize($scopes));
			return true;
		}
		else
		{
			Option::delete('landing', ['name' => self::OPTION_CODE]);
			Option::delete('landing', ['name' => self::OPTION_CODE_SCOPES]);
			return false;
		}
	}
}