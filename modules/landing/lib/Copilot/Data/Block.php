<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data;

use Bitrix\Landing\Copilot\Data\Block\Collector;
use Bitrix\Landing\Copilot\Data\Node\Node;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;

class Block
{
	private int $id;
	private string $code;
	private string $section;

	/**
	 * Relation between Block and Site containing it
	 * @var Site
	 */
	private Site $site;

	// todo: move to enum
	private string $bgType; //transparent || primary || image

	private string $menuTitle;
	private array $wrapperClasses;

	/**
	 * @var Node[] array of nodes
	 */
	private array $nodes = [];
	private array $styles = [];

	/**
	 * Constructs a new Block object with the specified parameters.
	 *
	 * @param string $code The unique code identifying the block.
	 * @param string $section The section to which the block belongs.
	 * @param string $bgType The background type for the block.
	 */
	public function __construct(string $code, string $section, string $bgType)
	{
		$this->code = $code;
		$this->section = $section;
		$this->bgType = $bgType;
	}

	/**
	 * Set relation between Block and Site containing it
	 * @param Site $site
	 * @return $this
	 */
	public function setParentSite(Site $site): self
	{
		$this->site = $site;

		return $this;
	}

	/**
	 * Return Site object containing it Block
	 * @return Site
	 */
	public function getParentSite(): Site
	{
		return $this->site;
	}

	/**
	 * Retrieves the code of the block.
	 *
	 * @return string The code of the block.
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * Retrieves the section of the block.
	 *
	 * @return string The section of the block.
	 */
	public function getSection(): string
	{
		return $this->section;
	}

	/**
	 * Checks if the section of the block is a menu section.
	 *
	 * @return bool True if the section is a menu section, false otherwise.
	 */
	public function isMenu(): bool
	{
		return $this->section === Collector::SECTIONS['MENU'];
	}

	/**
	 * Checks if the section of the block is a separators section.
	 *
	 * @return bool True if the section is a separators section, false otherwise.
	 */
	public function isSeparator(): bool
	{
		return $this->section === Collector::SECTIONS['SEPARATORS'];
	}

	/**
	 * Checks if the section of the block is a cover section.
	 *
	 * @return bool True if the section is a cover section, false otherwise.
	 */
	public function isCover(): bool
	{
		return $this->section === Collector::SECTIONS['COVER'];
	}

	/**
	 * Checks if the section of the block is a title section.
	 *
	 * @return bool True if the section is a title section, false otherwise.
	 */
	public function isTitle(): bool
	{
		return $this->section === Collector::SECTIONS['TITLE'];
	}

	/**
	 * Retrieves the background type of the block.
	 *
	 * @return string The current background type of the block.
	 */
	// todo: move to enum
	public function getBgType(): string
	{
		return $this->bgType;
	}

	/**
	 * Sets the background type for the block.
	 *
	 * @param string $bgType The background type to be set for the block.
	 *
	 * @return self The updated Block object, enabling method chaining.
	 */
	public function setBgType(string $bgType): self
	{
		$this->bgType = $bgType;

		return $this;
	}

	/**
	 * Retrieves the menu title of the block.
	 *
	 * @return string The menu title of the block, or an empty string if not set.
	 */
	public function getMenuTitle(): string
	{
		return $this->menuTitle ?? '';
	}

	/**
	 * Sets the menu title for the block.
	 *
	 * @param string $menuTitle The menu title to be set for the block.
	 *
	 * @return self The updated Block object, enabling method chaining.
	 */
	public function setMenuTitle(string $menuTitle): self
	{
		$this->menuTitle = $menuTitle;

		return $this;
	}

	public function setStyles(array $styles): self
	{
		$this->styles = $styles;

		return $this;
	}

	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Retrieves the unique identifier of the block.
	 *
	 * @return int The unique identifier of the block, or 0 if not initialized.
	 */
	public function getId(): int
	{
		return $this->id ?? 0;
	}

	public static function getAnchor(int $id): string
	{
		return 'block' . $id;
	}

	/**
	 * Sets the unique identifier for the block.
	 *
	 * @param int $id The ID to be set for the block.
	 *
	 * @return self The updated Block object, enabling method chaining.
	 */
	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Adds a node to the block's collection of nodes.
	 *
	 * @param Node $node The Node object to be added to the block.
	 *
	 * @return self The updated Block object, enabling method chaining.
	 */
	public function addNode(Node $node): self
	{
		$node->setParentBlock($this);
		$this->nodes[] = $node;

		return $this;
	}

	/**
	 * Return node objects
	 * @param string[]|null $codes - optional. If pass array of code-string - return just nodes with this codes
	 * @return Node[] - may be empty
	 */
	public function getNodes(?array $codes = null): array
	{
		if (empty($codes))
		{
			return $this->nodes;
		}

		return array_filter($this->nodes, function (Node $node) use ($codes)
		{
			return in_array($node->getCode(), $codes, true);
		});
	}

	/**
	 * Retrieves the CSS classes set for the block's wrapper.
	 *
	 * @return array An array of CSS classes assigned to the block's wrapper.
	 */
	public function getWrapperClasses(): array
	{
		return $this->wrapperClasses ?? [];
	}

	/**
	 * Sets the CSS classes for the block's wrapper.
	 *
	 * @param array $classes The CSS classes to be set for the block's wrapper.
	 *
	 * @return self The updated Block object, enabling method chaining.
	 */
	public function setWrapperClasses(array $classes): self
	{
		$this->wrapperClasses = $classes;

		return $this;
	}

	/**
	 * Converts the Block object to an associative array representation.
	 *
	 * @return array An associative array representing the Block object and its nodes.
	 */
	public function toArray(): array
	{
		$data = [];
		$data['code'] = $this->code;
		$data['section'] = $this->section;
		$data['bgType'] = $this->bgType;
		$data['styles'] = $this->styles;

		if (isset($this->id))
		{
			$data['id'] = $this->id;
		}

		$data['nodes'] = [];
		foreach ($this->nodes as $node)
		{
			$data['nodes'][] = $node->toArray();
		}

		return $data;
	}

	/**
	 * Creates a Block object from an associative array of data.
	 *
	 * @param array $data An associative array containing block configuration data.
	 *
	 * @return self A Block object populated with the provided data.
	 */
	public static function fromArray(array $data): self
	{
		$code = $data['code'] ?? null;
		$section = $data['section'] ?? null;
		$bgType = $data['bgType'] ?? null;
		$styles = $data['styles'] ?? [];

		if (!$code || $section === null || $bgType === null)
		{
			throw new GenerationException(
				GenerationErrors::dataValidation,
				"Not exist key block data in array.",
			);
		}

		$blockData = new Block($code, $section, $bgType);

		$id = (int)($data['id'] ?? 0);
		if ($id)
		{
			$blockData->setId($id);
		}

		if ($styles)
		{
			$blockData->setStyles($styles);
		}

		foreach (($data['nodes'] ?? []) as $node)
		{
			$node = Node::fromArray($node);
			if (!$node)
			{
				break;
			}
			$blockData->addNode($node);
		}

		return $blockData;
	}
}