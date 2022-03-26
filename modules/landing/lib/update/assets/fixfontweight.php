<?php

namespace Bitrix\Landing\Update\Assets;

use Bitrix\Landing\Internals\HookDataTable;
use Bitrix\Main\Update\Stepper;

final class FixFontWeight extends Stepper
{
	protected const FONT_PARAMS = ':wght@300;400;500;600;700;900';
	protected const FONT_NEW_PARAMS = ':wght@100;200;300;400;500;600;700;800;900';
	protected const STEP_PORTION = 10;
	protected const CONTINUE_EXECUTING = true;
	protected const STOP_EXECUTING = false;

	/**
	 * Execute
	 * @param array $result
	 * @return bool
	 */
	public function execute(array &$result): bool
	{
		$res = HookDataTable::getList(
			[
				'select' => [
					'ID', 'VALUE'
				],
				'filter' => [
					'HOOK' => 'FONTS',
				],
			]
		);

		if (!$result['checkedRows'])
		{
			$result['checkedRows'] = 0;
		}

		$countFixedRows = 0;
		$countRows = 0;
		while ($row = $res->fetch())
		{
			if ($countRows > $result['checkedRows'])
			{
				$result['checkedRows']++;
				$search = self::FONT_PARAMS;
				$replace = self::FONT_NEW_PARAMS;
				$value = str_replace($search, $replace, $row['VALUE']);
				HookDataTable::update(
					$row['ID'],
					['VALUE' => $value]
				);
				$countFixedRows++;
				if ($countFixedRows === self::STEP_PORTION)
				{
					$result['checkedRows'] = $countRows;
					return self::CONTINUE_EXECUTING;
				}
			}
			$countRows++;
		}

		return self::STOP_EXECUTING;
	}
}