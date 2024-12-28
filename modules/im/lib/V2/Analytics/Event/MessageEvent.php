<?php

namespace Bitrix\Im\V2\Analytics\Event;

use Bitrix\Disk\TypeFile;
use Bitrix\Im\V2\Chat\ChannelChat;
use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Entity\File\FileCollection;

class MessageEvent extends ChatEvent
{
	protected const MULTI_TYPE = 'any';
	protected const MEDIA_TYPE = 'media';
	protected const FILE_TYPES = [
		TypeFile::IMAGE => 'image',
		TypeFile::VIDEO => 'video',
		TypeFile::DOCUMENT => 'document',
		TypeFile::ARCHIVE => 'archive',
		TypeFile::SCRIPT => 'script',
		TypeFile::UNKNOWN => 'unknown',
		TypeFile::PDF => 'pdf',
		TypeFile::AUDIO => 'audio',
		TypeFile::KNOWN => 'known',
		TypeFile::VECTOR_IMAGE => 'vector-image',
	];

	public function setFilesType(FileCollection $files): self
	{
		$fileMap = [];
		foreach ($files as $file)
		{
			$typeId = $file->getDiskFile()?->getTypeFile() ?? 0;
			$fileMap[(int)$typeId] = (int)$typeId;
		}

		$typeCount = count($fileMap);

		if (
			$typeCount === 2
			&& isset($fileMap[TypeFile::IMAGE])
			&& isset($fileMap[TypeFile::VIDEO])
		)
		{
			$this->type = self::MEDIA_TYPE;

			return $this;
		}

		if ($typeCount > 1)
		{
			$this->type = self::MULTI_TYPE;

			return $this;
		}

		$this->type = self::FILE_TYPES[array_shift($fileMap)] ?? self::FILE_TYPES[TypeFile::UNKNOWN];

		return $this;
	}

	public function setFileP3(int $count): self
	{
		$this->p3 = 'filesCount_' . $count;

		return $this;
	}
}
