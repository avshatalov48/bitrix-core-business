<?php
namespace Bitrix\Landing\Update\Block;

use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Update\Stepper;

class LastUsed extends Stepper
{
	const OPTION_CODE = 'update_landing_lastused';

	/**
	 * Module id for parent class.
	 * @var string
	 */
	protected static $moduleId = 'landing';

	/**
	 * Mass insert used blocks per user.
	 * @param int $userId User id.
	 * @return void
	 */
	private static function massInsertBlocks(int $userId): void
	{
		$sql = "DELETE FROM b_landing_block_last_used WHERE USER_ID={$userId};";
		Application::getConnection()->query($sql);

		$sql = "INSERT INTO b_landing_block_last_used (USER_ID, CODE, DATE_CREATE)
					SELECT
						CREATED_BY_ID as USER_ID, CODE, MAX(DATE_CREATE) DATE_CREATE
					FROM
						b_landing_block
					WHERE
						CREATED_BY_ID={$userId}
						AND PUBLIC='N'
						AND DELETED='N'
					GROUP BY
						CODE
					ORDER BY
						DATE_CREATE DESC
				;";
		Application::getConnection()->query($sql);
	}

	/**
	 * One step of converter.
	 * @param array &$option Result array.
	 * @return bool
	 */
	public function execute(array &$option): bool
	{
		$lastId = Option::get('landing', self::OPTION_CODE, 0);

		if (!isset($option['steps']))
		{
			$option['steps'] = 0;
		}

		// total counts
		$option['count'] = count(BlockTable::getList([
			'select' => [
				'CREATED_BY_ID',
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			],
			'filter' => [
				'=PUBLIC' => 'N'
			],
			'group' => [
				'CREATED_BY_ID'
			]
		])->fetchAll());

		// current step
		$finished = true;
		$resBlocks = BlockTable::getList([
			'select' => [
				'CREATED_BY_ID'
			],
			'filter' => [
				'=PUBLIC' => 'N',
				'>CREATED_BY_ID' => $option['steps']
			],
			'group' => [
				'CREATED_BY_ID'
			],
			'order' => [
				'CREATED_BY_ID' => 'asc'
			]
		]);
		if ($row = $resBlocks->fetch())
		{
			$finished = false;
			$option['steps']++;
			self::massInsertBlocks($row['CREATED_BY_ID']);
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
