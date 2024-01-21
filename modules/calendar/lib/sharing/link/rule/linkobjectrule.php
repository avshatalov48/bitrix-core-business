<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

abstract class LinkObjectRule
{
	protected ?int $linkId = null;

	public function __construct(protected int $objectId) {}

	public function getObjectId(): ?int
	{
		return $this->objectId;
	}

	public function getLinkId(): ?int
	{
		return $this->linkId;
	}

	public function setLinkId(?int $linkId): self
	{
		$this->linkId = $linkId;

		return $this;
	}

	abstract public function getObjectType(): string;
}