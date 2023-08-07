<?php
namespace Bitrix\Landing\Update\Block;

use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Update\Stepper;

Loc::loadMessages(__FILE__);

class DomainUa extends Stepper
{
	protected static $moduleId = 'landing';

	/**
	 * One step of converter.
	 * @param array &$result Result array.
	 * @return bool
	 */
	public function execute(array &$result)
	{
		$lastId = Option::get('landing', 'update_block_domainua', 0);

		if (!isset($result['steps']))
		{
			$result['steps'] = 0;
		}

		$finished = true;

		// gets common quantity
		$res = BlockTable::getList(array(
			'select' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			),
			'filter' => [
				'CONTENT' => '%cdn.bitrix24.ua%',
			]
		));
		if ($row = $res->fetch())
		{
			$result['count'] = $row['CNT'];
		}

		// gets group for update
		$res = BlockTable::getList(array(
			'select' => array(
				'ID', 'CONTENT'
			),
			'filter' => array(
				'>ID' => $lastId,
				'CONTENT' => '%cdn.bitrix24.ua%',
			),
			'order' => array(
				'ID' => 'ASC'
			),
			'limit' => 10
		));
		while ($row = $res->fetch())
		{
			$lastId = $row['ID'];
			$result['steps']++;

			BlockTable::update($row['ID'], [
				'CONTENT' => str_replace(
					'cdn.bitrix24.ua',
					'cdn.bitrix24.eu',
					$row['CONTENT']
				)
			]);

			$finished = false;
		}

		// add files from blocks
		if (!$finished)
		{
			Option::set('landing', 'update_block_domainua', $lastId);
			return true;
		}
		else
		{
			Option::delete('landing', array('name' => 'update_block_domainua'));
			return false;
		}
	}
}
