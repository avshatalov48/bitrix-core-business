<?php
namespace Bitrix\Landing\Update\Block;

use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FixSrcImg extends Stepper
{
	protected static $moduleId = 'landing';

	/**
	 * One step of converter.
	 * @param array &$result Result array.
	 * @return bool
	 */
	public function execute(array &$result)
	{
		$lastId = Option::get('landing', 'update_block_fixsrcimg', 0);

		$finished = true;

		// gets common quantity
		$res = BlockTable::getList(array(
			'select' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			),
			'filter' => [
				'LOGIC' => 'OR',
				'CONTENT' => [
					'%http:///%',
					'%https:///%',
				]
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
				[
					'LOGIC' => 'OR',
					'CONTENT' => [
						'%http:///%',
						'%https:///%',
					]
				]
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
					['http:///', 'https:///'],
					'/',
					$row['CONTENT']
				)
			]);

			$finished = false;
		}

		// add files from blocks
		if (!$finished)
		{
			Option::set('landing', 'update_block_fixsrcimg', $lastId);
			return true;
		}
		else
		{
			Option::delete('landing', array('name' => 'update_block_fixsrcimg'));
			return false;
		}
	}
}