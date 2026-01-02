<?php

namespace Bitrix\Bizproc\Starter\Dto;

final class DocumentDto
{
	public function __construct(
		public readonly array $complexDocumentId,
		public readonly ?array $complexDocumentType = null,
		public readonly ?array $changedFieldNames = null,
	)
	{}
}
