<?php

namespace Bitrix\Location\Infrastructure;

use Bitrix\Location\Entity\Area;
use Bitrix\Location\Geometry\Converter\Manager;
use Bitrix\Location\Model\AreaTable;
use Bitrix\Location\Repository\AreaRepository;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Result;
use Bitrix\Main\IO;

class DataInstaller
{
	private const DEFAULT_DATA_PATH = '/bitrix/modules/location/data';

	/**
	 * @return string
	 */
	public static function installAreasAgent(): string
	{
		$agent = '\\Bitrix\\Location\\Infrastructure\\DataInstaller::installAreasAgent();';

		$connection = Application::getInstance()->getConnection();

		if (!$connection->isTableExists(AreaTable::getTableName()))
		{
			return $agent;
		}

		$result = self::installAreas();
		if (!$result->isSuccess())
		{
			return $agent;
		}

		return '';
	}

	/**
	 * @return Result
	 */
	private static function installAreas(): Result
	{
		$result = new Result();

		$dataPath = Application::getDocumentRoot() . self::DEFAULT_DATA_PATH;
		if (!IO\Directory::isDirectoryExists($dataPath))
		{
			return $result->addError(new Error('Default data directory not found'));
		}

		$areasFile = new IO\File(Path::combine($dataPath, 'areas.php'));
		if (!$areasFile->isExists())
		{
			return $result->addError(new Error('File with areas not found'));
		}

		$areas = include $areasFile->getPath();
		if (!is_array($areas))
		{
			return $result->addError(new Error('No data in areas file'));
		}

		$areaRepository = new AreaRepository();
		foreach ($areas as $area)
		{
			$existingArea = $areaRepository->findByTypeAndCode($area['TYPE'], $area['CODE']);
			if ($existingArea)
			{
				continue;
			}

			$areaFile = new IO\File(Path::combine($dataPath,  'areas', $area['FILE']));
			if (!$areaFile->isExists())
			{
				$result->addError(new Error('Area file not found'));
				continue;
			}

			$addResult = $areaRepository->store(
				(new Area())
					->setType($area['TYPE'])
					->setCode($area['CODE'])
					->setSort($area['SORT'])
					->setGeometry(
						Manager::makeConverter(Manager::FORMAT_GEOJSON)
							->read($areaFile->getContents())
					)
			);

			if (!$addResult->isSuccess())
			{
				$result->addErrors($addResult->getErrors());
			}
		}

		return $result;
	}
}
