<?
namespace Bitrix\UI\EntitySelector;

class Tab implements \JsonSerializable
{
	protected $id;

	/** @var TextNode */
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

	/** @var bool */
	protected $showDefaultFooter = true;

	/** @var bool | null */
	protected $showAvatars;

	public function __construct(array $options)
	{
		$id = $options['id'] ?? null;
		if (is_string($id) && $id !== '')
		{
			$this->id = $id;
		}

		$title = $options['title'] ?? null;
		if (is_string($title) || is_array($title))
		{
			$this->setTitle($options['title']);
		}

		if (isset($options['icon']))
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

		if (isset($options['textColor']))
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

		if (isset($options['bgColor']))
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

		if (isset($options['visible']) && is_bool($options['visible']))
		{
			$this->setVisible($options['visible']);
		}

		if (!empty($options['itemOrder']) && is_array($options['itemOrder']))
		{
			$this->setItemOrder($options['itemOrder']);
		}

		if (isset($options['itemMaxDepth']) && is_int($options['itemMaxDepth']))
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

		if (isset($options['showDefaultFooter']) && is_bool($options['showDefaultFooter']))
		{
			$this->showDefaultFooter = $options['showDefaultFooter'];
		}

		if (isset($options['showAvatars']) && is_bool($options['showAvatars']))
		{
			$this->setShowAvatars($options['showAvatars']);
		}
	}

	public function getId(): ?string
	{
		return $this->id;
	}

	public function getTitle(): string
	{
		return $this->getTitleNode() && !$this->getTitleNode()->isNullable() ? $this->getTitleNode()->getText() : '';
	}

	public function getTitleNode(): ?TextNode
	{
		return $this->title;
	}

	public function setTitle($title): self
	{
		if (is_string($title) || is_array($title) || $title === null)
		{
			$this->title = $title === null ? null : new TextNode($title);
		}

		return $this;
	}

	public function getIcon(): array
	{
		return $this->icon;
	}

	public function setIcon(array $icon): self
	{
		$this->icon = $icon;

		return $this;
	}

	public function getTextColor(): array
	{
		return $this->textColor;
	}

	public function setTextColor(array $textColor): self
	{
		$this->textColor = $textColor;

		return $this;
	}

	public function getBgColor(): array
	{
		return $this->bgColor;
	}

	public function setBgColor(array $bgColor): self
	{
		$this->bgColor = $bgColor;

		return $this;
	}

	public function setVisible(bool $flag): self
	{
		$this->visible = $flag;

		return $this;
	}

	public function isVisible(): bool
	{
		return $this->visible;
	}

	public function setItemOrder(array $order): self
	{
		$this->itemOrder = $order;

		return $this;
	}

	public function getItemOrder(): array
	{
		return $this->itemOrder;
	}

	public function setItemMaxDepth(int $depth): self
	{
		$this->itemMaxDepth = $depth;

		return $this;
	}

	public function getItemMaxDepth(): ?int
	{
		return $this->itemMaxDepth;
	}

	public function setStub($stub): self
	{
		if (is_bool($stub) || is_string($stub))
		{
			$this->stub = $stub;
		}

		return $this;
	}

	public function getStub()
	{
		return $this->stub;
	}

	public function setStubOptions(array $options): self
	{
		$this->stubOptions = $options;

		return $this;
	}

	public function getStubOptions(): ?array
	{
		return $this->stubOptions;
	}

	public function setFooter(string $footer, array $options = []): self
	{
		if (strlen($footer) > 0)
		{
			$this->footer = $footer;
			$this->footerOptions = $options;
		}

		return $this;
	}

	public function getFooter(): ?string
	{
		return $this->footer;
	}

	public function getFooterOptions(): ?array
	{
		return $this->footerOptions;
	}

	public function canShowDefaultFooter(): bool
	{
		return $this->showDefaultFooter;
	}

	public function enableDefaultFooter(): self
	{
		$this->showDefaultFooter = true;

		return $this;
	}

	public function disableDefaultFooter(): self
	{
		$this->showDefaultFooter = false;

		return $this;
	}

	public function setShowAvatars(bool $flag): self
	{
		$this->showAvatars = $flag;

		return $this;
	}

	public function getShowAvatars(): ?bool
	{
		return $this->showAvatars;
	}

	public function jsonSerialize()
	{
		$json = [
			'id' => $this->getId(),
			'title' => $this->getTitleNode(),
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

		if (!$this->canShowDefaultFooter())
		{
			$json['showDefaultFooter'] = false;
		}

		if ($this->getShowAvatars() !== null)
		{
			$json['showAvatars'] = $this->getShowAvatars();
		}

		return $json;
	}
}