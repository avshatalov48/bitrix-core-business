<?php

namespace Bitrix\Seo\BusinessSuite\DTO;

class Profile implements \JsonSerializable
{
	private $name;

	private $id;

	private $link;

	private $picture;

	private $type;

	public function setName(?string $name)
	{
		$this->name = $name;
		return $this;
	}

	public function setId(?string $id)
	{
		$this->id = $id;
		return $this;
	}

	public function setLink(?string $link)
	{
		$this->link = $link;
		return $this;
	}

	public function setPicture(?string $picture)
	{
		$this->picture = $picture;
		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getLink()
	{
		return $this->link;
	}

	public function getPicture()
	{
		return $this->picture;
	}

	public function getType()
	{
		return $this->type;
	}


	public function toArray() : array
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize()
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'link' => $this->link,
			'picture' => $this->picture,
		];
	}
}