<?php
namespace Bitrix\Landing\Update\Domain;

use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;

class Check extends Stepper
{
	/**
	 * Target module for stepper.
	 * @var string
	 */
	protected static $moduleId = 'landing';

	/**
	 * Items count for one step.
	 */
	const STEPPER_COUNT = 1;

	/**
	 * One step of converter.
	 * @param array &$result Result array.
	 * @return bool
	 */
	public function execute(array &$result)
	{
		$lastId = Option::get('landing', 'update_domain_check', 0);
		$result['count'] = 0;
		if (!isset($result['steps']))
		{
			$result['steps'] = 0;
		}
		$forUpdate = [];

		// gets all domains by condition
		$resDomain = \Bitrix\Landing\Domain::getList([
			'select' => [
				'ID', 'DOMAIN'
			],
			'order' => [
				'ID' => 'asc'
			]
		]);
		while ($domain = $resDomain->fetch())
		{
			$resSite = \Bitrix\Landing\Site::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'DOMAIN_ID' => $domain['ID'],
					'CHECK_PERMISSIONS' => 'N'
				]
 			]);
			if (!$resSite->fetch())
			{
				$result['count']++;
				if ($domain['ID'] > $lastId)
				{
					if (count($forUpdate) < self::STEPPER_COUNT)
					{
						$forUpdate[$domain['ID']] = $domain['DOMAIN'];
					}
				}
			}
		}

		if (!empty($forUpdate))
		{
			$class = \Bitrix\Landing\Manager::getExternalSiteController();
			foreach ($forUpdate as $id => $domain)
			{
				$lastId = $id;
				$result['steps']++;
				\Bitrix\Landing\Domain::delete($id);
				$class::deleteDomain($domain);
			}
			Option::set('landing', 'update_domain_check', $lastId);
			return true;
		}
		else
		{
			Option::delete('landing', array('name' => 'update_domain_check'));
			return false;
		}
	}

}