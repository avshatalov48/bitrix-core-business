<?php

namespace Bitrix\Landing\Update\Assets;

use Bitrix\Landing\Internals\HookDataTable;
use Bitrix\Main\Update\Stepper;

final class FontFix extends Stepper
{
	protected const FONT_PARAMS = ':wght@300;400;500;600;700;900';
	protected const PART_FONT_PATH = 'css2';
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
		if (!$result['count'])
		{
			$result['count'] = count($res);
		}
		if (!$result['steps'])
		{
			$result['steps'] = 0;
		}

		if (!$result['checkedRows'])
		{
			$result['checkedRows'] = 0;
		}

		$countFixedRows = 0;
		$countRows = 0;
		while ($row = $res->fetch())
		{
			$result["steps"] = $result["steps"]++;
			if ($countRows > $result['checkedRows'])
			{
				$result['checkedRows']++;
				preg_match_all(
					'#(<noscript>.*?<style.*?data-id="([^"]+)"[^>]*>[^<]+</style>)#is',
					$row['VALUE'],
					$matches
				);
				$matchesNew = preg_replace(
					'/(href="[^"]*)(css)([?]family=)([\w+]+)[^"]*(")/i',
					'${1}'.self::PART_FONT_PATH.'$3$4'.self::FONT_PARAMS.'$5',
					$matches[1]
				);
				if ($matches[1] !== $matchesNew)
				{
					$countFixedRows++;
					$value = str_replace($matches[1], $matchesNew, $row['VALUE']);
					HookDataTable::update(
						$row['ID'],
						['VALUE' => $value]
					);
					if ($countFixedRows === self::STEP_PORTION)
					{
						$result['checkedRows'] = $countRows;
						return self::CONTINUE_EXECUTING;
					}
				}
			}
			$countRows++;
		}

		return self::STOP_EXECUTING;
	}
}