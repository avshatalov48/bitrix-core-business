<?php

namespace Bitrix\Landing\Update\Assets;

use Bitrix\Main\Update\Stepper;
use Bitrix\Main\FileTable;
use Bitrix\Landing;

final class WebpackClear extends Stepper
{
	protected const CONTINUE_EXECUTING = true;
	protected const STOP_EXECUTING = false;
	protected const STEP_PORTION = 100;
	protected const WEBPACK_NAME_MASK = 'landing_assets_webpack';
	protected const MODULE_ID = 'landing';

	/**
	 * IDs of files
	 * @var array
	 */
	protected $filesToDelete;

	protected static $moduleId = 'landing';

	/**
	 * Execute
	 * @param array $result
	 * @return bool
	 */
	public function execute(array &$result): bool
	{
		$countToStep = count($this->getFilesToDelete());

		if ($countToStep <= 0)
		{
			return self::STOP_EXECUTING;
		}

		$portionToDelete = array_slice($this->getFilesToDelete(), 0, self::STEP_PORTION);
		foreach ($portionToDelete as $fileId)
		{
			\CFile::Delete($fileId);
		}

		if ($countToStep <= self::STEP_PORTION)
		{
			return self::STOP_EXECUTING;
		}

		$result['count'] = $result['count'] ?: $countToStep;
		$result['steps'] = $result['steps'] ? ($result['steps'] + self::STEP_PORTION) : self::STEP_PORTION;

		return self::CONTINUE_EXECUTING;
	}

	protected function getFilesToDelete(): array
	{
		if (!$this->filesToDelete)
		{
			$this->findFilesToDelete();
		}

		return $this->filesToDelete;
	}

	/**
	 * Find files, than exist in b_file, but not attached to any landing
	 */
	protected function findFilesToDelete(): void
	{
		$fileIds = [];
		$dbFiles = FileTable::getList([
			'select' => ['ID'],
			'filter' => [
				'%ORIGINAL_NAME' => self::WEBPACK_NAME_MASK,
				'=MODULE_ID' => self::MODULE_ID,
			],
		]);
		while ($row = $dbFiles->fetch())
		{
			$fileIds[] = (int)$row['ID'];
		}

		$landingFileIds = [];
		$fileIdsInRecycleBin = array_map(
			function($i)
			{
				return $i * -1;
			},
			$fileIds
		);
		$dbLandingFiles = Landing\Internals\FileTable::getList([
			'select' => ['FILE_ID'],
			'filter' => [
				'FILE_ID' => array_merge($fileIds, $fileIdsInRecycleBin),
			],
		]);
		while ($row = $dbLandingFiles->fetch())
		{
			$landingFileIds[] = abs($row['FILE_ID']);
		}

		$this->filesToDelete = array_diff($fileIds, array_unique($landingFileIds));
	}

	/**
	 * In first version of webpack we not bind files to landing.
	 * Now we can remove them to free space.
	 *
	 * Util method, not for regular use
	 */
	public static function clearNotBindedFiles(): void
	{
		Stepper::bindClass('Bitrix\Landing\Update\Assets\WebpackClear', 'landing', 300);
	}
}