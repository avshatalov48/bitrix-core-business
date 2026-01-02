<?php

namespace Bitrix\Im\V2\Entity\File;

final class FileLinkConfig
{
	public readonly string $action;
	public readonly ?int $size;
	public readonly bool $exact;
	public readonly ?string $forceFileName;

	public function __construct(
		string $action,
		?int $size = FileItem::MAX_PREVIEW_IMAGE_SIZE,
		bool $exact = false,
		?string $forceFileName = null,
	)
	{
		$this->action = $action;
		$this->size = $size;
		$this->exact = $exact;
		$this->forceFileName = $forceFileName;
	}
}
