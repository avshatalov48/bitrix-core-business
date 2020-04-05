<?php
namespace Bitrix\Landing\Update\Block;

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;

class SearchContent extends Stepper
{
	/**
	 * Option code for store
	 */
	const OPTION_CODE = 'update_block_search_content';

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

		$finished = true;

		// gets common quantity
		$res = BlockTable::getList([
			'select' => [
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			]
		]);
		if ($row = $res->fetch())
		{
			$result['count'] = $row['CNT'];
		}

		// gets group of blocks for update
		$res = BlockTable::getList([
			'select' => [
				'ID',
				'SORT',
				'CODE',
				'ANCHOR',
				'ACTIVE',
				'PUBLIC',
				'DELETED',
				'CONTENT',
				'LID',
				'SITE_ID' => 'LANDING.SITE_ID',
			],
			'filter' => [
				'>ID' => $lastId
			],
			'order' => [
				'ID' => 'ASC'
			],
			'limit' => 20
		]);
		while ($row = $res->fetch())
		{
			$lastId = $row['ID'];
			$result['steps']++;

			$block = new Block($row['ID'], $row);
			$searchContent = $block->getSearchContent();

			if ($searchContent)
			{
				BlockTable::update($row['ID'], [
					'SEARCH_CONTENT' => $searchContent
				]);
			}

			$finished = false;
		}

		if (!$finished)
		{
			Option::set('landing', self::OPTION_CODE, $lastId);
			return true;
		}
		else
		{
			Option::delete('landing', ['name' => self::OPTION_CODE]);
			return false;
		}
	}
}