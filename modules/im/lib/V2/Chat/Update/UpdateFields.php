<?php

namespace Bitrix\Im\V2\Chat\Update;

use Bitrix\Im\V2\Entity\File\ChatAvatar;
use Bitrix\Im\V2\Integration\HumanResources\Structure;

class UpdateFields
{
	protected function __construct(
		protected ?string $title,
		protected ?string $description,
		protected ?int $avatar,
		protected ?int $ownerId,
		protected ?string $searchable,
		protected ?string $manageUI,
		protected ?string $manageUsersAdd,
		protected ?string $manageUsersDelete,
		protected ?string $manageMessages,
		protected array $addedUsers,
		protected ?bool $hideHistory,
		protected array $deletedUsers,
		protected array $addedDepartments,
		protected array $deletedDepartments,
		protected array $addedManagers,
		protected array $deletedManagers,
	){}

	public static function create(array $fields): self
	{
		[$addedUsers, $addedDepartments] = Structure::splitEntities($fields['ADDED_MEMBER_ENTITIES'] ?? []);
		[$deletedUsers, $deletedDepartments] = Structure::splitEntities($fields['DELETED_MEMBER_ENTITIES'] ?? []);

		return new self(
			$fields['TITLE'] ?? null,
			$fields['DESCRIPTION'] ?? null,
			self::prepareAvatar($fields['AVATAR'] ?? null),
			isset($fields['OWNER_ID']) ? (int)$fields['OWNER_ID'] : null,
			$fields['SEARCHABLE'] ?? null,
			$fields['MANAGE_UI'] ?? null,
			$fields['MANAGE_USERS_ADD'] ?? null,
			$fields['MANAGE_USERS_DELETE'] ?? null,
			$fields['MANAGE_MESSAGES'] ?? null,
			$addedUsers ?? [],
			self::prepareBool($fields['HIDE_HISTORY'] ?? null),
			$deletedUsers ?? [],
			$addedDepartments ?? [],
			$deletedDepartments ?? [],
			self::prepareArrayField($fields['ADDED_MANAGERS'] ?? []),
			self::prepareArrayField($fields['DELETED_MANAGERS'] ?? []),
		);
	}

	public function getSearchable(): ?string
	{
		return $this->searchable;
	}

	public function getAddedUsers(): array
	{
		return $this->addedUsers;
	}

	public function getDeletedUsers(): array
	{
		return $this->deletedUsers;
	}

	public function shouldHideHistory(): ?bool
	{
		return $this->hideHistory;
	}

	public function getDeletedDepartments(): array
	{
		return $this->deletedDepartments;
	}

	public function getAddedDepartments(): array
	{
		return $this->addedDepartments;
	}

	public function getAddedManagers(): array
	{
		return $this->addedManagers;
	}

	public function getDeletedManagers(): array
	{
		return $this->deletedManagers;
	}

	public function getOwnerId(): ?int
	{
		return $this->ownerId;
	}

	public function getAvatar(): ?int
	{
		return $this->avatar;
	}

	protected static function prepareArrayField(array $array): array
	{
		$result = [];
		foreach ($array as $item)
		{
			if (is_numeric($item) && (int)$item > 0)
			{
				$result[] = (int)$item;
			}
		}

		return $result;
	}

	protected static function prepareAvatar(mixed $avatar): ?int
	{
		if (!isset($avatar))
		{
			return null;
		}
		if (is_numeric($avatar))
		{
			return (int)$avatar;
		}

		return (ChatAvatar::saveAvatarByString((string)$avatar));
	}

	protected static function prepareBool(?string $value, ?bool $default = null): ?bool
	{
		if ($value === 'Y')
		{
			return true;
		}

		if ($value === 'N')
		{
			return false;
		}

		return $default;
	}

	public function getArrayToSave(): array
	{
		$array = [
			'TITLE' => $this->title,
			'DESCRIPTION' => $this->description,
			'AUTHOR_ID' => $this->ownerId,
			'MANAGE_UI' => $this->manageUI,
			'MANAGE_USERS_ADD' => $this->manageUsersAdd,
			'MANAGE_USERS_DELETE' => $this->manageUsersDelete,
			'MANAGE_MESSAGES' => $this-> manageMessages,
		];
		return array_filter($array, function ($value) {
			return $value !== null;
		});
	}
}
