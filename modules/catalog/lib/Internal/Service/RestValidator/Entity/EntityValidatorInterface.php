<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Base;

interface EntityValidatorInterface
{
	public function getViewClassName(): string;

	public function getView(): Base;

	public function run(array $rawData): Result;

	public function getFormatValidators(): array;
}
