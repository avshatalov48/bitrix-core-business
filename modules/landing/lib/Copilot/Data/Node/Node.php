<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Node;

use Bitrix\Landing;
use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Copilot\Data\Type\NodeType;

abstract class Node
{
	protected const TYPE = null;

	private string $code;

	protected Data\Block $block;
	protected Landing\Block $blockInstance;
	protected ?array $placeholders;
	protected bool $isAvatarNode;
	protected ?array $promptTexts;

	protected function __construct(string $code, array $data)
	{
		$this->code = $code;

		$this->placeholders = $data['placeholders'] ?? [];
		$this->isAvatarNode = (bool)($data['isAvatarNode'] ?? false);
		$this->promptTexts = ($data['promptTexts'] ?? null);
	}

	/**
	 * Set relation between Node and Block containing it
	 * @param Data\Block $block
	 * @return $this
	 */
	public function setParentBlock(Data\Block $block): self
	{
		$this->block = $block;

		return $this;
	}

	/**
	 * Return Block object containing it Node
	 * @return Data\Block|null
	 */
	public function getParentBlock(): ?Data\Block
	{
		return $this->block ?? null;
	}

	/**
	 * Current node type
	 * @return NodeType|null
	 */
	public function getType(): ?NodeType
	{
		return static::TYPE;
	}

	/**
	 * @param NodeType $type
	 * @param string $code
	 * @param array $data
	 *
	 * @return Node|null
	 */
	public static function create(NodeType $type, string $code, array $data): ?Node
	{
		return match ($type)
		{
			NodeType::Img => new Img($code, $data),
			NodeType::Icon => new Icon($code, $data),
			NodeType::Text => new Text($code, $data),
			NodeType::Link => new Link($code, $data),
			default => null,
		};
	}

	abstract public function setData(array $data): void;

	// todo: move to toArray
	abstract public function getValues(?int $position = null): array;

	public function getCode(): string
	{
		return $this->code;
	}

	public function getPlaceholders(): ?array
	{
		return $this->placeholders;
	}

	public function isAvatarNode(): bool
	{
		return $this->isAvatarNode;
	}

	/**
	 * Set text for generate prompts to current node
	 * @param array $promptTexts
	 * @return $this
	 */
	public function addPromptTexts(array $promptTexts): static
	{
		if ($this->promptTexts === null)
		{
			$this->promptTexts = [];
		}
		$this->promptTexts = array_merge($this->promptTexts, $promptTexts);

		return $this;
	}

	public function getPromptTexts(): ?array
	{
		return $this->promptTexts;
	}

	public function setPromptTexts(array $promptTexts): void
	{
		$this->promptTexts = $promptTexts;
	}

	public function toArray(): array
	{
		$data = [];
		$data['code'] = $this->code;
		$data['type'] = $this->getType()->value;
		$data['placeholders'] = $this->getPlaceholders();
		if ($this->getPromptTexts())
		{
			$data['promptTexts'] = $this->getPromptTexts();
		}
		$data['values'] = $this->getValues();
		$data['isAvatarNode'] = $this->isAvatarNode();

		return $data;
	}

	/**
	 * @param array $data
	 *
	 * @return ?self
	 */
	public static function fromArray(array $data): ?self
	{
		if (!isset($data['type'], $data['code']))
		{
			return null;
		}

		$type = NodeType::from($data['type']);

		if (in_array($type, NodeType::cases(), true))
		{
			return self::create($type, $data['code'], $data);
		}

		return null;
	}

	/**
	 * Apply node data to real landing block
	 * @param int|null $position - if not set - apply all data, else - just one position
	 * @return bool
	 */
	public function toLanding(?int $position = null): bool
	{
		return $this->canApplyToLanding();
	}

	/**
	 * Check is can toLanding convert
	 * @return bool
	 */
	protected function canApplyToLanding(): bool
	{
		$isSiteExists = $this->block->getParentSite()?->getSiteId();
		$isLandingExists = $this->block->getParentSite()?->getLandingId();
		$isBlockExists = (bool)$this->getBlockInstance();

		if (
			!$isSiteExists
			|| !$isLandingExists
			|| !$isBlockExists
		)
		{
			return false;
		}

		return true;
	}

	protected function getBlockInstance(): ?Landing\Block
	{
		if (!isset($this->blockInstance))
		{
			$blockId = $this->block->getId();
			if (!$blockId)
			{
				return null;
			}

			$instance = new Landing\Block($blockId);
			if (!$instance->exist())
			{
				return null;
			}

			$this->blockInstance = $instance;
		}

		return $this->blockInstance;
	}
}