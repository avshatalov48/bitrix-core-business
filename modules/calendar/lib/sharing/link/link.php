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

	public function getId(): ?int
	{
		return $this->id ?? null;
	}

	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getObjectId(): int
	{
		return $this->objectId;
	}

	public function setObjectId(int $objectId): self
	{
		$this->objectId = $objectId;

		return $this;
	}

	public function isActive(): bool
	{
		return $this->active;
	}

	public function setActive(bool $active): self
	{
		$this->active = $active;

		return $this;
	}

	public function getDateCreate(): ?DateTime
	{
		return $this->dateCreate;
	}

	public function setDateCreate(DateTime $dateCreate): self
	{
		$this->dateCreate = $dateCreate;

		return $this;
	}

	public function getDateExpire(): ?DateTime
	{
		return $this->dateExpire;
	}

	public function setDateExpire(?DateTime $dateExpire): self
	{
		$this->dateExpire = $dateExpire;

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

	abstract public function getObjectType(): string;
}