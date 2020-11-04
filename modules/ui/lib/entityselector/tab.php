<?
namespace Bitrix\UI\EntitySelector;

class Tab implements \JsonSerializable
{
	protected $id;
	protected $title = '';
	protected $visible = true;
	protected $itemOrder = [];
	protected $itemMaxDepth;
	protected $icon = [];
	protected $textColor = [];
	protected $bgColor = [];
	protected $stub;
	protected $stubOptions;

	/** @var string */
	protected $footer;

	/** @var array */
	protected $footerOptions;

	public function __construct(array $options)
	{
		if (!empty($options['id']) && is_string($options['id']))
		{
			$this->id = $options['id'];
		}

		if (!empty($options['title']) && is_string($options['title']))
		{
			$this->setTitle($options['title']);
		}

		if (!empty($options['icon']))
		{
			if (is_string($options['icon']))
			{
				$this->setIcon(['default' => $options['icon']]);
			}
			elseif (is_array($options['icon']))
			{
				$this->setIcon($options['icon']);
			}
		}

		if (!empty($options['textColor']))
		{
			if (is_string($options['textColor']))
			{
				$this->setTextColor(['default' => $options['textColor']]);
			}
			elseif (is_array($options['textColor']))
			{
				$this->setTextColor($options['textColor']);
			}
		}

		if (!empty($options['bgColor']))
		{
			if (is_string($options['bgColor']))
			{
				$this->setBgColor(['default' => $options['bgColor']]);
			}
			elseif (is_array($options['bgColor']))
			{
				$this->setBgColor($options['bgColor']);
			}
		}

		if (!empty($options['visible']) && is_bool($options['visible']))
		{
			$this->setVisible($options['visible']);
		}

		if (!empty($options['itemOrder']) && is_array($options['itemOrder']))
		{
			$this->setItemOrder($options['itemOrder']);
		}

		if (!empty($options['itemMaxDepth']) && is_int($options['itemMaxDepth']))
		{
			$this->setItemMaxDepth($options['itemMaxDepth']);
		}

		if (isset($options['stub']) && (is_bool($options['stub']) || is_string($options['stub'])))
		{
			$this->setStub($options['stub']);
		}

		if (!empty($options['stubOptions']) && is_array($options['stubOptions']))
		{
			$this->setStubOptions($options['stubOptions']);
		}

		if (isset($options['footer']) && is_string($options['footer']))
		{
			$footerOptions =
				isset($options['footerOptions']) && is_array($options['footerOptions'])
					? $options['footerOptions']
					: []
			;

			$this->setFooter($options['footer'], $footerOptions);
		}
	}

	public function getId(): ?string
	{
		return $this->id;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getIcon()
	{
		return $this->icon;
	}

	public function setIcon(array $icon): self
	{
		$this->icon = $icon;

		return $this;
	}

	public function getTextColor()
	{
		return $this->textColor;
	}

	public function setTextColor(array $textColor): self
	{
		$this->textColor = $textColor;

		return $this;
	}

	public function getBgColor()
	{
		return $this->bgColor;
	}

	public function setBgColor(array $bgColor): self
	{
		$this->bgColor = $bgColor;

		return $this;
	}

	public function setVisible(bool $flag)
	{
		$this->visible = $flag;
	}

	public function isVisible()
	{
		return $this->visible;
	}

	public function setItemOrder(array $order)
	{
		$this->itemOrder = $order;
	}

	public function getItemOrder(): array
	{
		return $this->itemOrder;
	}

	public function setItemMaxDepth(int $depth)
	{
		$this->itemMaxDepth = $depth;
	}

	public function getItemMaxDepth(): ?int
	{
		return $this->itemMaxDepth;
	}

	public function setStub($stub)
	{
		if (is_bool($stub) || is_string($stub))
		{
			$this->stub = $stub;
		}
	}

	public function getStub()
	{
		return $this->stub;
	}

	public function setStubOptions(array $options)
	{
		$this->stubOptions = $options;
	}

	public function getStubOptions(): ?array
	{
		return $this->stubOptions;
	}

	public function setFooter(string $footer, array $options = [])
	{
		if (strlen($footer) > 0)
		{
			$this->footer = $footer;
			$this->footerOptions = $options;
		}
	}

	public function getFooter(): ?string
	{
		return $this->footer;
	}

	public function getFooterOptions(): ?array
	{
		return $this->footerOptions;
	}

	public function jsonSerialize()
	{
		$json = [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'visible' => $this->isVisible(),
			'itemOrder' => $this->getItemOrder(),
			'itemMaxDepth' => $this->getItemMaxDepth(),
			'icon' => $this->getIcon(),
			'textColor' => $this->getTextColor(),
			'bgColor' => $this->getBgColor(),
		];

		if ($this->getStub() !== null)
		{
			$json['stub'] = $this->getStub();
		}

		if ($this->getStubOptions() !== null)
		{
			$json['stubOptions'] = $this->getStubOptions();
		}

		if ($this->getFooter())
		{
			$json['footer'] = $this->getFooter();
			$json['footerOptions'] = $this->getFooterOptions();
		}

		return $json;
	}
}