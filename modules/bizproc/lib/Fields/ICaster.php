<?php

namespace Bitrix\Bizproc\Fields;

interface ICaster
{
	public function internalize(array $values): array;
	public function externalize(array $values): array;
}