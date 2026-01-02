<?php

namespace Bitrix\UI\AccessRights\V2\Options;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;
use JsonSerializable;

class RightSection implements JsonSerializable, Arrayable
{
	protected ?string $subTitle = null;
	protected ?string $code = null;
	protected ?string $hint = null;
	protected ?string $iconCode = null;
	protected ?string $iconBgColor = null;

	/** @var RightItem[] */
	protected array $rightItems = [];

	public function __construct(
		protected string $title,
	)
	{
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): static
	{
		$this->title = $title;

		return $this;
	}

	public function getSubTitle(): ?string
	{
		return $this->subTitle;
	}

	public function setSubTitle(?string $subTitle): static
	{
		$this->subTitle = $subTitle;

		return $this;
	}

	public function getCode(): ?string
	{
		return $this->code;
	}

	public function setCode(?string $code): static
	{
		$this->code = $code;

		return $this;
	}

	public function getHint(): ?string
	{
		return $this->hint;
	}

	public function setHint(?string $hint): static
	{
		$this->hint = $hint;

		return $this;
	}

	public function getIconCode(): ?string
	{
		return $this->iconCode;
	}

	public function setIconCode(?string $iconCode): static
	{
		$this->iconCode = $iconCode;

		return $this;
	}

	public function getIconBgColor(): ?string
	{
		return $this->iconBgColor;
	}

	public function setIconBgColor(?string $iconBgColor): static
	{
		$this->iconBgColor = $iconBgColor;

		return $this;
	}

	public function getRightItems(): array
	{
		return $this->rightItems;
	}

	public function setRightItems(array $rightItems): static
	{
		$this->rightItems = $rightItems;

		return $this;
	}

	public function addRight(RightItem $right): static
	{
		$this->rightItems[] = $right;

		return $this;
	}

	private function iconArrayOrNull(): ?array
	{
		if ($this->iconCode === null && $this->iconBgColor === null)
		{
			return null;
		}

		return [
			'type' => $this->iconCode,
			'bgColor' => $this->iconBgColor,
		];
	}

	public function toArray(): array
	{
		return [
			'sectionTitle' => $this->getTitle(),
			'sectionSubTitle' => $this->getSubTitle(),
			'sectionCode' => $this->getCode(),
			'sectionHint' => $this->getHint(),
			'sectionIcon' => $this->iconArrayOrNull(),
			'rights' => array_map(static fn (RightItem $right) => $right->toArray(), $this->rightItems),
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
