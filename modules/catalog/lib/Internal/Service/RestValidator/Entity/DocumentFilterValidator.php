<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\Internal\Service\RestValidator\Format;
use Bitrix\Catalog\RestView\Document;

class DocumentFilterValidator extends EntityValidator
{
	public function getViewClassName(): string
	{
		return Document::class;
	}

	public function getFormatValidators(): array
	{
		return [
			Format\DocumentFilterFieldValidator::getInstance(),
		];
	}
}
