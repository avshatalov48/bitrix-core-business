<?

namespace Bitrix\UI\EntitySelector;

use Bitrix\Main\Type\Dictionary;

class Item implements \JsonSerializable
{
	protected $id = '';
	protected $entityId = '';
	protected $entityType;
	protected $tabs = [];

	protected $title = '';
	protected $subtitle;
	protected $supertitle;
	protected $caption;
	protected $avatar;
	protected $link;
	protected $linkTitle;
	protected $badges;

	protected $searchable = true;
	protected $saveable = true;
	protected $deselectable = true;
	protected $hidden = false;

	protected $children;
	protected $nodeOptions;
	protected $tagOptions;
	protected $customData;

	protected $sort;
	protected $contextSort;
	protected $globalSort;

	protected $dialog;
	protected $availableInRecentTab = true;

	public function __construct(array $options)
	{
		if (!empty($options['id']) && (is_string($options['id']) || is_int($options['id'])))
		{
			$this->id = $options['id'];
		}

		if (!empty($options['entityId']) && is_string($options['entityId']))
		{
			$this->entityId = strtolower($options['entityId']);
		}

		if (!empty($options['entityType']) && is_string($options['entityType']))
		{
			$this->entityType = $options['entityType'];
		}

		$this->addTab($options['tabs'] ?? null);

		if (isset($options['title']) && is_string($options['title']))
		{
			$this->setTitle($options['title']);
		}

		if (isset($options['subtitle']) && is_string($options['subtitle']))
		{
			$this->setSubtitle($options['subtitle']);
		}

		if (isset($options['supertitle']) && is_string($options['supertitle']))
		{
			$this->setSupertitle($options['supertitle']);
		}

		if (isset($options['caption']) && is_string($options['caption']))
		{
			$this->setCaption($options['caption']);
		}

		if (isset($options['avatar']) && is_string($options['avatar']))
		{
			$this->setAvatar($options['avatar']);
		}

		if (isset($options['link']) && is_string($options['link']))
		{
			$this->setLink($options['link']);
		}

		if (isset($options['linkTitle']) && is_string($options['linkTitle']))
		{
			$this->setLinkTitle($options['linkTitle']);
		}

		if (isset($options['badges']) && is_array($options['badges']))
		{
			$this->addBadges($options['badges']);
		}

		if (isset($options['searchable']) && is_bool($options['searchable']))
		{
			$this->setSearchable($options['searchable']);
		}

		if (isset($options['saveable']) && is_bool($options['saveable']))
		{
			$this->setSaveable($options['saveable']);
		}

		if (isset($options['deselectable']) && is_bool($options['deselectable']))
		{
			$this->setDeselectable($options['deselectable']);
		}

		if (isset($options['hidden']) && is_bool($options['hidden']))
		{
			$this->setHidden($options['hidden']);
		}

		if (isset($options['sort']) && is_int($options['sort']))
		{
			$this->setSort($options['sort']);
		}

		if (isset($options['availableInRecentTab']) && is_bool($options['availableInRecentTab']))
		{
			$this->setAvailableInRecentTab($options['availableInRecentTab']);
		}

		if (isset($options['customData']) && is_array($options['customData']))
		{
			$this->setCustomData($options['customData']);
		}

		if (isset($options['nodeOptions']) && is_array($options['nodeOptions']))
		{
			$this->setNodeOptions($options['nodeOptions']);
		}

		if (isset($options['tagOptions']) && is_array($options['tagOptions']))
		{
			$this->setTagOptions($options['tagOptions']);
		}

		if (!empty($options['children']) && is_array($options['children']))
		{
			$this->addChildren($options['children']);
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getEntityId(): string
	{
		return $this->entityId;
	}

	public function getEntityType(): ?string
	{
		return $this->entityType;
	}

	public function setEntityType(string $type): self
	{
		if (is_string($type) || $type === null)
		{
			$this->entityType = $type;
		}

		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): self
	{
		if (is_string($title))
		{
			$this->title = $title;
		}

		return $this;
	}

	public function getSubtitle(): ?string
	{
		return $this->subtitle;
	}

	public function setSubtitle(?string $subtitle): self
	{
		if (is_string($subtitle) || $subtitle === null)
		{
			$this->subtitle = $subtitle;
		}

		return $this;
	}

	public function getSupertitle(): ?string
	{
		return $this->supertitle;
	}

	public function setSupertitle(?string $supertitle): self
	{
		if (is_string($supertitle) || $supertitle === null)
		{
			$this->supertitle = $supertitle;
		}

		return $this;
	}

	public function getCaption(): ?string
	{
		return $this->caption;
	}

	public function setCaption(?string $caption): self
	{
		if (is_string($caption) || $caption === null)
		{
			$this->caption = $caption;
		}

		return $this;
	}

	public function getAvatar(): ?string
	{
		return $this->avatar;
	}

	public function setAvatar(?string $avatar): self
	{
		if (is_string($avatar) || $avatar === null)
		{
			$this->avatar = $avatar;
		}

		return $this;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}

	public function setLink(?string $link): self
	{
		if (is_string($link) || $link === null)
		{
			$this->link = $link;
		}

		return $this;
	}

	public function getLinkTitle(): ?string
	{
		return $this->linkTitle;
	}

	public function setLinkTitle(?string $linkTitle): self
	{
		if (is_string($linkTitle) || $linkTitle === null)
		{
			$this->linkTitle = $linkTitle;
		}

		return $this;
	}

	public function getBadges(): ?array
	{
		return $this->badges;
	}

	public function addBadges(array $badges): self
	{
		foreach ($badges as $badge)
		{
			if (is_array($badge) && !empty($badge))
			{
				$this->badges[] = $badge;
			}
		}

		return $this;
	}

	public function setBadges(array $badges): self
	{
		$this->badges = [];
		$this->addBadges($badges);

		return $this;
	}

	public function getTabs(): array
	{
		return $this->tabs;
	}

	public function addTab($tabId): self
	{
		if (is_string($tabId) && !empty($tabId))
		{
			$this->tabs[] = $tabId;
		}
		else if (is_array($tabId))
		{
			$this->tabs = array_merge($this->tabs, $tabId);
		}

		return $this;
	}

	public function getChildren(): ItemCollection
	{
		if ($this->children === null)
		{
			$this->children = new ItemCollection();
		}

		return $this->children;
	}

	public function addChildren(array $children)
	{
		foreach ($children as $childOptions)
		{
			unset($childOptions['tabs']);

			$child = new Item($childOptions);
			$this->addChild($child);
		}
	}

	public function addChild(Item $item)
	{
		$success = $this->getChildren()->add($item);
		if ($success && $this->getDialog())
		{
			$this->getDialog()->handleItemAdd($item);
		}
	}

	public function setNodeOptions(array $nodeOptions)
	{
		$this->getNodeOptions()->setValues($nodeOptions);
	}

	public function getNodeOptions()
	{
		if ($this->nodeOptions === null)
		{
			$this->nodeOptions = new Dictionary();
		}

		return $this->nodeOptions;
	}

	public function setTagOptions(array $nodeOptions)
	{
		$this->getTagOptions()->setValues($nodeOptions);
	}

	public function getTagOptions()
	{
		if ($this->tagOptions === null)
		{
			$this->tagOptions = new Dictionary();
		}

		return $this->tagOptions;
	}

	public function isSearchable(): bool
	{
		return $this->searchable;
	}

	public function setSearchable(bool $flag = true): self
	{
		$this->searchable = $flag;

		return $this;
	}

	public function isSaveable(): bool
	{
		return $this->saveable;
	}

	public function setSaveable(bool $flag = true): self
	{
		$this->saveable = $flag;

		return $this;
	}

	public function isDeselectable(): bool
	{
		return $this->deselectable;
	}

	public function setDeselectable(bool $flag = true): self
	{
		$this->deselectable = $flag;

		return $this;
	}

	public function isHidden(): bool
	{
		return $this->hidden;
	}

	public function setHidden(bool $flag = true): self
	{
		$this->hidden = $flag;

		return $this;
	}

	public function isAvailableInRecentTab(): bool
	{
		return $this->availableInRecentTab;
	}

	public function setAvailableInRecentTab(bool $flag = true): self
	{
		$this->availableInRecentTab = $flag;

		return $this;
	}

	public function setCustomData(array $customData)
	{
		$this->getCustomData()->setValues($customData);
	}

	/**
	 * @return Dictionary
	 */
	public function getCustomData(): Dictionary
	{
		if ($this->customData === null)
		{
			$this->customData = new Dictionary();
		}

		return $this->customData;
	}

	public function setSort(?int $sort)
	{
		$this->sort = $sort;
	}

	public function getSort(): ?int
	{
		return $this->sort;
	}

	public function setContextSort(?int $sort)
	{
		$this->contextSort = $sort;
	}

	public function getContextSort(): ?int
	{
		return $this->contextSort;
	}

	public function setGlobalSort(?int $sort)
	{
		$this->globalSort = $sort;
	}

	public function getGlobalSort(): ?int
	{
		return $this->globalSort;
	}

	public function setDialog(Dialog $dialog)
	{
		$this->dialog = $dialog;
	}

	public function getDialog(): ?Dialog
	{
		return $this->dialog;
	}

	public function jsonSerialize()
	{
		$json = [
			'id' => $this->getId(),
			'entityId' => $this->getEntityId(),
			'title' => $this->getTitle(),
		];

		if (!$this->isSearchable())
		{
			$json['searchable'] = false;
		}

		if (!$this->isSaveable())
		{
			$json['saveable'] = false;
		}

		if (!$this->isDeselectable())
		{
			$json['deselectable'] = false;
		}

		if ($this->isHidden())
		{
			$json['hidden'] = true;
		}

		if ($this->customData !== null && $this->getCustomData()->count() > 0)
		{
			$json['customData'] = $this->getCustomData()->getValues();
		}

		if ($this->nodeOptions !== null && $this->getNodeOptions()->count() > 0)
		{
			$json['nodeOptions'] = $this->getNodeOptions()->getValues();
		}

		if ($this->tagOptions !== null && $this->getTagOptions()->count() > 0)
		{
			$json['tagOptions'] = $this->getTagOptions()->getValues();
		}

		if ($this->children !== null && $this->getChildren()->count() > 0)
		{
			$json['children'] = $this->getChildren();
		}

		if (!empty($this->getTabs()))
		{
			$json['tabs'] = $this->getTabs();
		}

		if (!empty($this->getBadges()))
		{
			$json['badges'] = $this->getBadges();
		}

		foreach ([
			'entityType',
			'avatar',
			'subtitle',
			'supertitle',
			'caption',
			'link',
			'linkTitle',
			'contextSort',
			'globalSort',
			'sort'
		] as $field)
		{
			if ($this->{'get'.$field}() !== null)
			{
				$json[$field] = $this->{'get'.$field}();
			}
		}

		return $json;
	}
}