<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights\Entity;

abstract class EntityBase
	implements AccessRightEntityInterface
{
	protected $id;
	protected $model;

	public function __construct(int $id)
	{
		$this->id = $id;
		$this->loadModel();
	}

	public function getId(): int
	{
		return $this->id;
	}

	abstract public function getType(): string;
	abstract public function getName(): string;
	abstract public function getUrl(): string;
	abstract public function getAvatar(int $width = 58, int $height = 58): ?string;

	public function getMetaData(): array
	{
		return [
			'type' 		=> $this->getType(),
			'id' 		=> $this->getId(),
			'name' 		=> $this->getName(),
			'url' 		=> $this->getUrl(),
			'avatar' 	=> $this->getAvatar()
		];
	}

	abstract protected function loadModel();
}