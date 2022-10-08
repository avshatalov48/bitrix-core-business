<?php

namespace Bitrix\Calendar\Core\Role;

use Bitrix\Calendar\Core\Base\BaseProperty;

abstract class BaseRole extends BaseProperty implements RoleEntityInterface
{
	public const TYPE = 'user';
	protected string $name = '';
	protected ?int $id = null;

	public static function createInstance(string $name): RoleEntityInterface
	{
		return new static($name);
	}

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->getFullName();
	}

	/**
	 * @return string[]
	 */
	public function getFields(): array
	{
		return [
			'name',
			'id',
		];
	}

	/**
	 * @return string
	 */
	public function getFullName(): string
	{
		return $this->name;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return static::TYPE;
	}

	/**
	 * @param string $name
	 * @return BaseRole
	 */
	public function setName(string $name): BaseRole
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @param int|null $id
	 * @return BaseRole
	 */
	public function setId(?int $id): BaseRole
	{
		$this->id = $id;

		return $this;
	}
}
