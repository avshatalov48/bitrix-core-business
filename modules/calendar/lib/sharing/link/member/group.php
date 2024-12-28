<?php

namespace Bitrix\Calendar\Sharing\Link\Member;

use Bitrix\Calendar\Integration\SocialNetwork\Collab\Collabs;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Item\Workgroup;

final class Group implements Arrayable
{
	private int $id;
	private string $name;
	private int $imageId;

	public function __construct(Workgroup $group)
	{
		$this->id = $group->getId();
		$this->name = $group->getName();
		$this->imageId = $group->getImageId();
	}

	private function getAvatarLink(): ?string
	{
		return Collabs::getInstance()->getCollabImagePath($this->imageId);
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'lastName' => null,
			'avatar' => $this->getAvatarLink(),
		];
	}
}
