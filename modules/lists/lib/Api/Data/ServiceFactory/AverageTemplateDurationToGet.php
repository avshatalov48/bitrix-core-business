<?php

namespace Bitrix\Lists\Api\Data\ServiceFactory;

use Bitrix\Lists\Api\Data\Data;
use Bitrix\Lists\Api\Request\ServiceFactory\GetAverageIBlockTemplateDurationRequest;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Loader;

final class AverageTemplateDurationToGet extends Data
{
	private int $iBlockId;
	private int $autoExecuteType;

	private function __construct(
		int $iBlockId,
		int $autoExecuteType
	)
	{
		$this->iBlockId = $iBlockId;
		$this->autoExecuteType = $autoExecuteType;
	}

	/**
	 * @param GetAverageIBlockTemplateDurationRequest $request
	 *
	 * @return self
	 * @throws ArgumentOutOfRangeException
	 * @throws ArgumentException
	 */
	public static function createFromRequest($request): self
	{
		$iBlockId = self::validateId($request->iBlockId);
		if ($iBlockId === null || $iBlockId === 0)
		{
			throw new ArgumentOutOfRangeException('iBlockId', 1, null);
		}

		$autoExecuteType = $request->autoExecuteType;
		if (!Loader::includeModule('bizproc') || \CBPDocumentEventType::out($autoExecuteType) === '')
		{
			throw new ArgumentException('Invalid value for $autoExecuteType', 'autoExecuteType');
		}

		return new self($iBlockId, $autoExecuteType);
	}

	/**
	 * @return int
	 */
	public function getIBlockId(): int
	{
		return $this->iBlockId;
	}

	/**
	 * @return int
	 */
	public function getAutoExecuteType(): int
	{
		return $this->autoExecuteType;
	}
}
