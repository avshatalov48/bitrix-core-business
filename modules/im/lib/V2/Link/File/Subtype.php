<?php

namespace Bitrix\Im\V2\Link\File;

use Bitrix\Disk\TypeFile;
use JsonSerializable;

enum Subtype: string implements JsonSerializable
{
	case Media = 'MEDIA';
	case Audio = 'AUDIO';
	case Brief = 'BRIEF';
	case Document = 'DOCUMENT';
	case Other = 'OTHER';

	public static function tryFromDiskFileType(int $diskFileType): self
	{
		return match ($diskFileType)
		{
			TypeFile::IMAGE, TypeFile::VIDEO => self::Media,
			TypeFile::DOCUMENT, TypeFile::PDF => self::Document,
			TypeFile::AUDIO => self::Audio,
			default => self::Other,
		};
	}

	/**
	 * @param string|string[] $subtypes
	 * @return string[]
	 */
	public static function getSubtypeFilter(string|array $subtypes): array
	{
		$result = [];

		if (is_string($subtypes))
		{
			$subtypes = [$subtypes];
		}

		foreach ($subtypes as $subtype)
		{
			if (self::tryFrom($subtype) !== null)
			{
				$result[$subtype] = $subtype;
			}
		}

		return array_values($result);
	}

	public static function tryFromOrDefault(string $value): self
	{
		return self::tryFrom($value) ?? self::Other;
	}

	public function jsonSerialize(): string
	{
		return mb_strtolower($this->value);
	}
}
