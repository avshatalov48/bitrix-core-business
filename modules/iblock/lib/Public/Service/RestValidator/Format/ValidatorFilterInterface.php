<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

interface ValidatorFilterInterface extends ValidatorInterface
{
	public function setScalarFields(array $fields): static;

	public function getScalarFields(): array;

	public function isScalarField(string $fieldName): bool;
}
