<?php

namespace Bitrix\Im\V2\Entity\Url;

use Bitrix\Im\V2\Rest\RestConvertible;

class RichData implements RestConvertible
{
	public const TASKS_TYPE = 'TASKS'; // rich data from tasks module
	public const CALENDAR_TYPE = 'CALENDAR'; // rich data from calendar module
	public const LANDING_TYPE = 'LANDING'; // rich data from landing module
	public const POST_TYPE = 'POST'; // rich data from post from socialnetwork module
	public const LINK_TYPE = 'LINK'; // rich data for non-dynamic url
	public const DYNAMIC_TYPE = 'DYNAMIC'; // rich data for dynamic url without module

	protected ?int $id = null;
	protected ?string $type = null;
	protected ?string $name = null;
	protected ?string $description = null;
	protected ?string $previewUrl = null;
	protected ?string $link = null;
	protected ?array $allowedUsers = null;

	public static function initByAttach(?\CIMMessageParamAttach $attach): self
	{
		$rich = new static();

		if ($attach === null)
		{
			return $rich;
		}

		$arrayAttach = $attach->GetArray();
		$richLink = $arrayAttach['BLOCKS'][0]['RICH_LINK'][0];
		$rich->setType(RichData::LINK_TYPE)
			->setDescription($richLink['DESC'])
			->setName($richLink['NAME'])
			->setPreviewUrl($richLink['PREVIEW'])
		;

		return $rich;
	}

	//region Getters & setters

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(?int $id): RichData
	{
		$this->id = $id;
		return $this;
	}

	public function getType(): ?string
	{
		return $this->type;
	}

	public function setType(?string $type): RichData
	{
		$this->type = $type;
		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(?string $name): RichData
	{
		$this->name = $name;
		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): RichData
	{
		$this->description = $description;
		return $this;
	}

	public function getPreviewUrl(): ?string
	{
		return $this->previewUrl;
	}

	public function setPreviewUrl(?string $previewUrl): RichData
	{
		$this->previewUrl = $previewUrl;
		return $this;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}

	public function setLink(?string $link): RichData
	{
		$this->link = $link;
		return $this;
	}

	/**
	 * Returns an array of user ids who can access this rich data
	 * If null is returned, then there are no restrictions.
	 * @return array|null
	 */
	public function getAllowedUsers(): ?array
	{
		return $this->allowedUsers;
	}

	public function setAllowedUsers(array $allowedUsers): RichData
	{
		$this->allowedUsers = $allowedUsers;
		return $this;
	}

	//endregion

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getId(),
			'type' => $this->getType(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'previewUrl' => $this->getPreviewUrl(),
			'link' => $this->getLink(),
		];
	}

	public static function getRestEntityName(): string
	{
		return 'richData';
	}
}