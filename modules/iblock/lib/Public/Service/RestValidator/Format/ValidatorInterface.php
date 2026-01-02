<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Main\Result;

interface ValidatorInterface
{
	public function setFields(array $fields): static;

	public function getFields(): array;

	public function isExistsFieldAliases();

	public function setFieldAliases(array $aliases): static;

	public function getFieldAliases(): array;

	public function getAlias(string $fieldName): string;

	public function getRealAlias(string $fieldName): ?string;

	public function run(array $rawData): Result;
}
