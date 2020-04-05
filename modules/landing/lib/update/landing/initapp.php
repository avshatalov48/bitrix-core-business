<?php
namespace Bitrix\Landing\Update\Landing;

use \Bitrix\Landing\Internals\LandingTable;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Landing\Demos;
use \Bitrix\Landing\Repo;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;

/**
 * Class InitApp
 * Set to all pages field 'INITIATOR_APP_CODE'.
 * @package Bitrix\Landing\Update\Landing
 */
class InitApp extends Stepper
{
	protected static $moduleId = 'landing';

	/**
	 * One step of converter.
	 * @param array &$result Result array.
	 * @return bool
	 */
	public function execute(array &$result)
	{
		$lastId = Option::get('landing', 'update_landing_app', 0);
		$blocksRepo = Repo::getRepository();
		\Bitrix\Landing\Rights::setGlobalOff();

		$finished = true;
		if (!isset($result['steps']))
		{
			$result['steps'] = 0;
		}

		// get all app in demo tables
		$demos = [];
		$res = Demos::getList([
			'select' => [
				'APP_CODE', 'XML_ID'
			]
		]);
		while ($row = $res->fetch())
		{
			$demos[$row['APP_CODE'] . '.' . $row['XML_ID']] = $row;
		}
		unset($res, $row);

		// calculate count of records, which we need
		$res = LandingTable::getList([
			'select' => [
				'CNT'
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			]
		]);
		if ($row = $res->fetch())
		{
			$result['count'] = $row['CNT'];
		}
		unset($res, $row);


		// one group for update
		$res = LandingTable::getList(array(
			'select' => array(
				'ID', 'TPL_CODE'
			),
			'filter' => array(
				'>ID' => $lastId
			),
			'order' => array(
				'ID' => 'ASC'
			),
			'limit' => 1
		));
		while ($row = $res->fetch())
		{
			$lastId = $row['ID'];
			$result['steps']++;
			$appCode = isset($demos[$row['TPL_CODE']]['APP_CODE'])
						? $demos[$row['TPL_CODE']]['APP_CODE']
						: null;

			// mark with this app all available blocks in current page
			$resBlock = BlockTable::getList([
				'select' => [
					'ID', 'CODE'
				],
				'filter' => [
					'LID' => $row['ID'],
					'=DELETED' => 'N'
				]
			]);
			while ($rowBlock = $resBlock->fetch())
			{
				$appCodeBlock = isset($blocksRepo[$rowBlock['CODE']])
								? $blocksRepo[$rowBlock['CODE']]['app_code']
								: null;
				if ($appCodeBlock != $appCode)
				{
					$appCodeBlock = null;
				}
				$resTmp = BlockTable::update($rowBlock['ID'], [
					'INITIATOR_APP_CODE' =>  $appCodeBlock
				]);
				$resTmp->isSuccess();
			}
			unset($resBlock, $rowBlock);

			// mark the page with this app
			$resTmp = LandingTable::update($row['ID'], [
				'INITIATOR_APP_CODE' => $appCode
			]);
			$resTmp->isSuccess();

			$finished = false;
		}
		unset($res, $row);

		\Bitrix\Landing\Rights::setGlobalOn();

		// set next step or finish work
		if (!$finished)
		{
			Option::set('landing', 'update_landing_app', $lastId);
			return true;
		}
		else
		{
			Option::delete('landing', array('name' => 'update_landing_app'));
			return false;
		}
	}
}