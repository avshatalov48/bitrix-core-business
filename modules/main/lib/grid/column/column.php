<?php

namespace Bitrix\Main\Grid\Column;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Grid\Column\Editable\Config;
use Bitrix\Main\Grid\Column\Editable\Factory\ConfigFactory;
use Bitrix\Main\Grid\Column\UI\Hint;
use Bitrix\Main\Grid\Column\UI\Icon;

class Column
{
	use UI\ColumnFields;

	protected string $id;
	protected string $type;
	protected ?string $name;
	protected ?string $sort;
	protected bool $default = false;
	/**
	 * If `true`, the column is always included in the selection, even if it is not currently displayed in the grid.
	 *
	 * For example, the `ACTIVE` field for iblock elements should always be loaded so that you can determine
	 * which action to using in the row: `activation` or `deactivation'.
	 *
	 * @var bool
	 */
	protected bool $necessary = false;
	/**
	 * List of fields to select.
	 *
	 * By default it contains `id'.
	 *
	 * @var array
	 */
	protected array $select;
	protected bool $multiple = false;
	protected ?Config $editableConfig = null;

	/**
	 * @param string $id
	 * @param array $params params array. Recommended using `set*` methods.
	 */
	public function __construct(string $id, array $params = [])
	{
		$this->setId($id);

		$this
			->setType(
				empty($params['type']) ? Type::TEXT : $params['type']
			)
			->setName($params['name'] ?? null)
			->setSort($params['sort'] ?? null)
			->setAlign($params['align'] ?? null)
			->setTitle($params['title'] ?? null)
			->setWidth($params['width'] ?? null)
			->setSection($params['section_id'] ?? null)
			->setCssClassName($params['class'] ?? null)
		;

		$this->setSelect($params['select'] ?? [ $id ]);

		if (isset($params['editable']))
		{
			$this->setEditable($params['editable']);
		}

		if (isset($params['first_order']))
		{
			$this->setFirstOrder($params['first_order']);
		}

		if (isset($params['default']))
		{
			$this->setDefault($params['default']);
		}

		if (isset($params['necessary']))
		{
			$this->setNecessary($params['necessary']);
		}

		if (isset($params['multiple']))
		{
			$this->setMultiple($params['multiple']);
		}

		if (isset($params['resizeable']))
		{
			$this->setResizeable($params['resizeable']);
		}

		if (isset($params['prevent_default']))
		{
			$this->setPreventDefault($params['prevent_default']);
		}

		if (isset($params['sticked']))
		{
			$this->setSticked($params['sticked']);
		}

		if (isset($params['shift']))
		{
			$this->setShift($params['shift']);
		}

		if (isset($params['showname']))
		{
			$this->setShowname($params['showname']);
		}

		$color = $params['color'] ?? null;
		if (isset($color) && is_string($color))
		{
			if (self::isValidCssColorValue($color))
			{
				$this->setCssColorValue($color);
			}
			else
			{
				$this->setCssColorClassName($color);
			}
		}

		if (isset($params['iconUrl']))
		{
			$this->setIcon(
				new Icon(
					$params['iconUrl'],
					$params['iconTitle'] ?? null
				)
			);
		}

		if (isset($params['hint']))
		{
			$hint = new Hint($params['hint']);

			if (isset($params['hintHtml']) && is_bool($params['hintHtml']))
			{
				$hint->setHtml($params['hintHtml']);
			}

			if (isset($params['hintInteractivity']) && is_bool($params['hintInteractivity']))
			{
				$hint->setInteractivity($params['hintInteractivity']);
			}

			$this->setHint($hint);
		}
	}

	public function setId(string $id): self
	{
		$id = trim($id);
		if (empty($id))
		{
			throw new ArgumentNullException('id');
		}

		$this->id = $id;

		return $this;
	}

	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Set column's type.
	 *
	 * @see \Bitrix\Main\Grid\Column\Type
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType(string $type): self
	{
		$type = trim($type);
		if (empty($type))
		{
			throw new ArgumentNullException('type');
		}

		$this->type = $type;

		return $this;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setName(?string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setDefault(bool $default): self
	{
		$this->default = $default;

		return $this;
	}

	public function isDefault(): bool
	{
		return $this->default;
	}

	public function setNecessary(bool $necessary): self
	{
		$this->necessary = $necessary;

		return $this;
	}

	/**
	 * Column is always included in the selection, even if it is not currently displayed in the grid.
	 *
	 * @return bool
	 */
	public function isNecessary(): bool
	{
		return $this->necessary;
	}

	/**
	 * @param Config|bool $editable
	 *
	 * @return self
	 */
	public function setEditable($value): self
	{
		if (is_bool($value))
		{
			if ($value)
			{
				$this->editableConfig = (new ConfigFactory)->createFromColumn($this);
			}
			else
			{
				$this->editableConfig = null;
			}
		}
		elseif ($value instanceof Config)
		{
			$this->editableConfig = $value;
		}
		else
		{
			throw new ArgumentTypeException('editable', '\Bitrix\Main\Grid\Column\Editable\Config|bool');
		}

		return $this;
	}

	public function getEditable(): ?Config
	{
		return $this->editableConfig;
	}

	public function isEditable(): bool
	{
		return isset($this->editableConfig);
	}

	public function setMultiple(bool $multiple): self
	{
		$this->multiple = $multiple;

		return $this;
	}

	public function isMultiple(): bool
	{
		return $this->multiple;
	}

	public function setSort(?string $sort): self
	{
		$this->sort = $sort;

		return $this;
	}

	public function getSort(): ?string
	{
		return $this->sort;
	}

	public function setSelect(array $select): self
	{
		$this->select = $select;

		return $this;
	}

	public function getSelect(): array
	{
		return $this->select;
	}
}
