<?php

namespace Bitrix\Im\V2\Link\File;

use JsonSerializable;

enum SubtypeGroup: string implements JsonSerializable
{
	case Media = 'MEDIA';
	case Audio = 'AUDIO';
	case File = 'FILE';
	case Brief = 'BRIEF';

	public static function tryFromSubtype(Subtype $subtype): self
	{
		return match ($subtype)
		{
			Subtype::Media => self::Media,
			Subtype::Audio => self::Audio,
			Subtype::Brief => self::Brief,
			Subtype::Document, Subtype::Other => self::File,
		};
	}

	/**
	 * @param string[] $groups
	 * @return self[]
	 */
	public static function tryFromArray(array $groups): array
	{
		$result = [];

		foreach ($groups as $group)
		{
			$group = self::tryFrom($group);
			if (isset($group))
			{
				$result[$group->value] = $group;
			}
		}

		return array_values($result);
	}

	/**
	 * @return Subtype[]
	 */
	public function getSubtypes(): array
	{
		return match ($this)
		{
			self::Media => [Subtype::Media],
			self::Audio => [Subtype::Audio],
			self::Brief => [Subtype::Brief],
			self::File => [Subtype::Document, Subtype::Other],
		};
	}

	/**
	 * @param string|string[] $subtypeGroups
	 * @return string[]
	 */
	public static function getSubtypeFilter(string|array $subtypeGroups): array
	{
		$result = [];
		if (is_string($subtypeGroups))
		{
			$subtypeGroups = [$subtypeGroups];
		}

		$subtypeGroups = self::tryFromArray($subtypeGroups);

		foreach ($subtypeGroups as $subtypeGroup)
		{
			foreach ($subtypeGroup->getSubtypes() as $subtype)
			{
				$result[$subtype->value] = $subtype->value;
			}
		}

		return array_values($result);
	}

	public function jsonSerialize(): string
	{
		return mb_strtolower($this->value);
	}
}
