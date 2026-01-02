<?php

declare(strict_types=1);

namespace Bitrix\Main\Repository;

interface SoftDeletableRepositoryInterface
{
	public function softDelete(mixed $id): void;
}
