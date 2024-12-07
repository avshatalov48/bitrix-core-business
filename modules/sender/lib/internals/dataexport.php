<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals;

use Bitrix\Main\DB\Result;

/**
 * Class DataExport
 * @package Bitrix\Sender\Internals
 */
class DataExport
{
	/**
	 * Export to csv.
	 *
	 * @param array $columns Columns.
	 * @param array|Result $list Data list.
	 * @param callable|null $rowCallback Row callback.
	 * @return void
	 */
	public static function toCsv(array $columns, $list, $rowCallback = null)
	{
		self::flushHeaders();

		$eol = "\n";
		$columnNames = [];
		$isFirstLinePrinted = false;

		echo chr(239) . chr(187) . chr(191);

		foreach ($list as $item)
		{
			if ($rowCallback)
			{
				$item = call_user_func_array($rowCallback, [$item]);
			}

			$row = [];
			foreach ($columns as $column)
			{
				if (!array_key_exists($column['id'], $item))
				{
					continue;
				}

				if (!$isFirstLinePrinted)
				{
					$columnNames[] = str_replace('"', '""', $column['name']);
				}

				$row[] = str_replace('"', '""', trim($item[$column['id']]));
			}

			if (!$isFirstLinePrinted)
			{
				echo '"' . implode('";"', $columnNames) . '"';
				$isFirstLinePrinted = true;
			}
			echo $eol;
			echo '"' . implode('";"', $row) . '"';
		}

		exit;
	}

	protected static function flushHeaders()
	{
		$GLOBALS['APPLICATION']->RestartBuffer();

		header('Content-Description: File Transfer');
		header('Content-Type: text/csv; charset='.LANG_CHARSET);
		header('Content-Disposition: attachment; filename=address_list.csv');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
	}
}