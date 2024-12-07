<?php

namespace Bitrix\Location\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Entity\Source;
use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Model\StaticMapFileTable;
use Bitrix\Location\StaticMap\StaticMapResult;
use Bitrix\Main\Error;
use Bitrix\Main\Web\MimeType;
use InvalidArgumentException;

final class StaticMapService extends BaseService
{
	protected static $instance;
	private ?Source $source;

	protected function __construct(Container $config)
	{
		parent::__construct($config);

		$this->source = $config->get('source');
	}

	public function getStaticMap(
		float $latitude,
		float $longitude,
		int $zoom,
		int $width,
		int $height
	): StaticMapResult
	{
		if (!$this->source)
		{
			return (new StaticMapResult())->addError(new Error('Source is not specified'));
		}

		$validateErrors = $this->validate($latitude, $longitude, $zoom, $width, $height);
		if (!empty($validateErrors))
		{
			return (new StaticMapResult())->addErrors($validateErrors);
		}

		$point = $this->makePoint($latitude, $longitude);
		$hash = $this->getHash($point, $zoom, $width, $height);

		$existingFileRow = StaticMapFileTable::getRowById($hash);
		if ($existingFileRow)
		{
			$resultFromCache = $this->getResultFromCache((int)$existingFileRow['FILE_ID']);
			if ($resultFromCache)
			{
				return $resultFromCache;
			}
		}

		$staticMapService = $this->source->makeStaticMapService();
		if (!$staticMapService)
		{
			return (new StaticMapResult())->addError(
				new Error('Static map service is not supported by the source')
			);
		}

		$serviceResult = $staticMapService->getStaticMap($point, $zoom, $width, $height);
		if (!$serviceResult->isSuccess())
		{
			return $serviceResult;
		}

		$this->saveResultToCache($hash, $serviceResult);

		return $serviceResult;
	}

	private function validate(
		float $latitude,
		float $longitude,
		int $zoom,
		int $width,
		int $height
	): array
	{
		$result = [];

		try
		{
			$this->makePoint($latitude, $longitude);
		}
		catch (InvalidArgumentException $exception)
		{
			$result[] = new Error($exception->getMessage());
		}

		if ($zoom < 0 || $zoom > 18)
		{
			$result[] = new Error('zoom must be a positive number or zero between 0 and 18');
		}

		if ($width <= 0 || $width > 640)
		{
			$result[] = new Error('width must be a positive number between 0 and 640');
		}

		if ($height <= 0 || $height > 640)
		{
			$result[] = new Error('height must be a positive number 0 and 640');
		}

		return $result;
	}

	private function makePoint(
		float $latitude,
		float $longitude,
	): Point
	{
		return new Point($latitude, $longitude);
	}

	private function getHash(
		Point $point,
		int $zoom,
		int $width,
		int $height
	)
	{
		return sha1(
			implode(';', [
				$this->source->getCode(),
				$point->getLng(),
				$point->getLat(),
				$zoom,
				$width,
				$height
			])
		);
	}

	private function getResultFromCache(int $fileId): ?StaticMapResult
	{
		$file = \CFile::GetByID($fileId)->fetch();

		if ($file && isset($file['SRC']))
		{
			return (new StaticMapResult())
				->setMimeType($file['CONTENT_TYPE'])
				->setPath($file['SRC'])
			;
		}

		return null;
	}

	private function saveResultToCache(string $hash, StaticMapResult $result): void
	{
		$resultMimeType = $result->getMimeType();
		$resultContent = $result->getContent();

		$resultFileExtension = '';
		foreach (MimeType::getMimeTypeList() as $extension => $mimeType)
		{
			if ($resultMimeType === $mimeType)
			{
				$resultFileExtension = $extension;
				break;
			}
		}

		$fileId = (int)\CFile::SaveFile(
			[
				'name' => $hash . ($resultFileExtension ? ('.' . $resultFileExtension) : ''),
				'type' => $resultMimeType,
				'size' => mb_strlen($resultContent),
				'content' => $resultContent,
				'MODULE_ID' => 'location',
			],
			'location/static_map'
		);

		if ($fileId)
		{
			$filePath = \CFile::GetPath($fileId);
			if ($filePath)
			{
				$result->setPath($filePath);
			}

			StaticMapFileTable::merge(
				[
					'HASH' => $hash,
					'FILE_ID' => $fileId,
				],
				[
					'FILE_ID' => $fileId,
				],
				[
					'HASH',
				]
			);
		}
	}
}
