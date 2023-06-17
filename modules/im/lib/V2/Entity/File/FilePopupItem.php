<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Im\V2\Rest\PopupDataItem;

class FilePopupItem implements PopupDataItem
{
	private FileCollection $files;

	public function __construct($files = null)
	{
		if (!$files instanceof FileCollection)
		{
			$this->files = new FileCollection();
		}
		else
		{
			$this->files = $files;
		}

		if ($files instanceof FileItem)
		{
			if ($this->files->getById($files->getId()) === null)
			{
				$this->files[] = $files;
			}
		}
	}

	public function merge(PopupDataItem $item): self
	{
		if ($item instanceof self)
		{
			foreach ($item->files as $file)
			{
				if ($this->files->getById($file->getId()) === null)
				{
					$this->files[] = $file;
				}
			}
		}

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return FileCollection::getRestEntityName();
	}

	public function toRestFormat(array $option = []): array
	{
		return $this->files->getUnique()->toRestFormat($option);
	}
}