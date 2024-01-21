<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Main\Type\DateTime;

abstract class Link implements EntityInterface
{
	public const SHARING_PUBLIC_PATH = 'pub/calendar-sharing';

	protected int $id;
	protected int $objectId;
	protected string $hash;
	protected bool $active;
	protected ?DateTime $dateCreate = null;
	protected ?DateTime $dateExpire = null;
	protected ?int $frequentUse = null;

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id ?? null;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getObjectId(): int
	{
		return $this->objectId;
	}

	/**
	 * @param int $objectId
	 * @return $this
	 */
	public function setObjectId(int $objectId): self
	{
		$this->objectId = $objectId;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->active;
	}

	/**
	 * @param bool $active
	 * @return $this
	 */
	public function setActive(bool $active): self
	{
		$this->active = $active;

		return $this;
	}

	/**
	 * @return DateTime|null
	 */
	public function getDateCreate(): ?DateTime
	{
		return $this->dateCreate;
	}

	/**
	 * @param DateTime $dateCreate
	 * @return $this
	 */
	public function setDateCreate(DateTime $dateCreate): self
	{
		$this->dateCreate = $dateCreate;

		return $this;
	}

	/**
	 * @return DateTime|null
	 */
	public function getDateExpire(): ?DateTime
	{
		return $this->dateExpire;
	}

	/**
	 * @param DateTime|null $dateExpire
	 * @return $this
	 */
	public function setDateExpire(?DateTime $dateExpire): self
	{
		$this->dateExpire = $dateExpire;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getFrequentUse(): ?int
	{
		return $this->frequentUse;
	}

	/**
	 * @param int|null $frequentUse
	 * @return $this
	 */
	public function setFrequentUse(?int $frequentUse): self
	{
		$this->frequentUse = $frequentUse;

		return $this;
	}

	public function getHash(): string
	{
		if (empty($this->hash))
		{
			$this->hash = $this->generateHash();
		}

		return $this->hash;
	}

	public function setHash(string $hash): self
	{
		$this->hash = $hash;

		return $this;
	}

	public function getUrl(): string
	{
		$serverPath = \CCalendar::GetServerPath();
		$publicPath = self::SHARING_PUBLIC_PATH;
		$hash = $this->getHash();

		return "$serverPath/$publicPath/$hash/";
	}

	public function generateHash(): string
	{
		return hash('sha256', $this->objectId . $this->getObjectType() . microtime() . \CMain::getServerUniqID());
	}

	public function isJoint(): bool
	{
		return false;
	}

	abstract public function getObjectType(): string;
}