<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\Internal\Service\RestValidator\Format;
use Bitrix\Catalog\RestView;
use Bitrix\Iblock\Public\Service\RestValidator\Format\BaseValidator;
use Bitrix\Iblock\Public\Service\RestValidator\Format\Type\FileInterface;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

abstract class BaseProductValidator extends EntityValidator
{
	protected bool $iblockIncluded;

	protected ?int $iblockId = null;

	public function __construct(int $iblockId)
	{
		$this->iblockIncluded = Loader::includeModule('iblock');

		$this->setIblockId($iblockId);

		parent::__construct();
	}

	public function getViewClassName(): string
	{
		return RestView\Product::class;
	}

	protected function isIblockIncluded(): bool
	{
		return $this->iblockIncluded;
	}

	protected function setIblockId(int $iblockId): void
	{
		if ($iblockId <= 0)
		{
			$iblockId = null;
		}
		$this->iblockId = $iblockId;
	}

	protected function getIblockId(): ?int
	{
		return $this->iblockId;
	}

	protected function fillCanonicalNames(): void
	{
		/** @var RestView\Product $view */
		$view = $this->getView();
		$result = $view->getCanonicalNames();

		$iblockId = $this->getIblockId();
		if ($iblockId)
		{
			$result = array_merge(
				$result,
				$view->getIblockPropertyCanonicalNames([
					'IBLOCK_ID' =>$iblockId,
				])
			);
		}

		$this->setCanonicalNames($result);
	}

	protected function runValidator(BaseValidator $validator, array $rawData): Result
	{
		if (!$this->isIblockIncluded())
		{
			return new Result();
		}

		return parent::runValidator($validator, $rawData);
	}

	protected function getFileValidator(): FileInterface
	{
		return new Format\Type\CatalogFile();
	}
}
