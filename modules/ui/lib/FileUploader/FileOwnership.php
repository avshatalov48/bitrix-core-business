<?php

namespace Bitrix\UI\FileUploader;

class FileOwnership
{
	private int $id;
	private bool $own = false;

	public function __construct(int $id)
	{
		$this->id = $id;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function isOwn(): bool
	{
		return $this->own;
	}

	public function markAsOwn(bool $flag = true): void
	{
		$this->own = $flag;
	}
}