<?

namespace Bitrix\UI\EntitySelector;

use Bitrix\Main\Type\Dictionary;

class Item implements \JsonSerializable
{
	protected $id = '';
	protected $entityId = '';
	protected $entityType;
	protected $tabs = [];

	/** @var TextNode */
	protected $title;

	/** @var TextNode */
	protected $subtitle;

	/** @var TextNode */
	protected $supertitle;

	/** @var TextNode */
	protected $caption;

	/** @var string */
	protected $avatar;

	/** @var string */
	protected $textColor;

	/** @var string */
	protected $link;

	/** @var TextNode */
	protected $linkTitle;
	protected $badges;

	protected $selected = false;
	protected $searchable = true;
	protected $saveable = true;
	protected $deselectable = true;
	protected $hidden = false;

	protected $children;
	protected $nodeOptions;
	protected $tagOptions;
	protected $customData;
	protected $captionOptions;
	protected $badgesOptions;
	protected $avatarOptions;

	protected $sort;
	protected $contextSort;
	protected $globalSort;

	protected $dialog;
	protected $availableInRecentTab = true;

	public function __construct(array $options)
	{
		$id = $options['id'] ?? null;
		if ((is_string($id) && $id !== '') || is_int($id))
		{
			$this->id = $id;
		}

		$entityId = $options['entityId'] ?? null;
		if (is_string($entityId) && $entityId !== '')
		{
			$this->entityId = strtolower($entityId);
		}

		$entityType = $options['entityType'] ?? null;
		if (is_string($entityType) && $entityType !== '')
		{
			$this->entityType = $entityType;
		}

		$this->addTab($options['tabs'] ?? null);

		$this->setTitle($options['title'] ?? null);
		$this->setSubtitle($options['subtitle'] ?? null);
		$this->setSupertitle($options['supertitle'] ?? null);
		$this->setCaption($options['caption'] ?? null);

		if (isset($options['captionOptions']) && is_array($options['captionOptions']))
		{
			$this->setCaptionOptions($options['captionOptions']);
		}

		if (isset($options['avatar']) && is_string($options['avatar']))
		{
			$this->setAvatar($options['avatar']);
		}

		if (isset($options['avatarOptions']) && is_array($options['avatarOptions']))
		{
			$this->setAvatarOptions($options['avatarOptions']);
		}

		if (isset($options['textColor']) && is_string($options['textColor']))
		{
			$this->setTextColor($options['textColor']);
		}

		if (isset($options['link']) && is_string($options['link']))
		{
			$this->setLink($options['link']);
		}

		$this->setLinkTitle($options['linkTitle'] ?? null);

		if (isset($options['badges']) && is_array($options['badges']))
		{
			$this->addBadges($options['badges']);
		}

		if (isset($options['badgesOptions']) && is_array($options['badgesOptions']))
		{
			$this->setBadgesOptions($options['badgesOptions']);
		}

		if (isset($options['searchable']) && is_bool($options['searchable']))
		{
			$this->setSearchable($options['searchable']);
		}

		if (isset($options['selected']) && is_bool($options['selected']))
		{
			$this->setSelected($options['selected']);
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
		return $this->getTitleNode() && !$this->getTitleNode()->isNullable() ? $this->getTitleNode()->getText() : '';
	}

	public function getTitleNode(): ?TextNode
	{
		return $this->title;
	}

	public function setTitle($title): self
	{
		if (TextNode::isValidText($title) || $title === null)
		{
			$this->title = $title === null ? null : new TextNode($title);
		}

		return $this;
	}

	public function getSubtitle(): ?string
	{
		return $this->getSubtitleNode() ? $this->getSubtitleNode()->getText() : null;
	}

	public function getSubtitleNode(): ?TextNode
	{
		return $this->subtitle;
	}

	public function setSubtitle($subtitle): self
	{
		if (TextNode::isValidText($subtitle) || $subtitle === null)
		{
			$this->subtitle = $subtitle === null ? null : new TextNode($subtitle);
		}

		return $this;
	}

	public function getSupertitle(): ?string
	{
		return $this->getSupertitleNode() ? $this->getSupertitleNode()->getText() : null;
	}

	public function getSupertitleNode(): ?TextNode
	{
		return $this->supertitle;
	}

	public function setSupertitle($supertitle): self
	{
		if (TextNode::isValidText($supertitle) || $supertitle === null)
		{
			$this->supertitle = $supertitle === null ? null : new TextNode($supertitle);
		}

		return $this;
	}

	public function getCaption(): ?string
	{
		return $this->getCaptionNode() ? $this->getCaptionNode()->getText() : null;
	}

	public function getCaptionNode(): ?TextNode
	{
		return $this->caption;
	}

	public function setCaption($caption): self
	{
		if (TextNode::isValidText($caption) || $caption === null)
		{
			$this->caption = $caption === null ? null : new TextNode($caption);
		}

		return $this;
	}

	public function setCaptionOptions(array $captionOptions): self
	{
		$this->getCaptionOptions()->setValues($captionOptions);

		return $this;
	}

	/**
	 * @return Dictionary
	 */
	public function getCaptionOptions(): Dictionary
	{
		if ($this->captionOptions === null)
		{
			$this->captionOptions = new Dictionary();
		}

		return $this->captionOptions;
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

	public function setAvatarOptions(array $avatarOptions): self
	{
		$this->getAvatarOptions()->setValues($avatarOptions);

		return $this;
	}

	/**
	 * @return Dictionary
	 */
	public function getAvatarOptions(): Dictionary
	{
		if ($this->avatarOptions === null)
		{
			$this->avatarOptions = new Dictionary();
		}

		return $this->avatarOptions;
	}

	public function getTextColor(): ?string
	{
		return $this->textColor;
	}

	public function setTextColor(?string $textColor): self
	{
		if (is_string($textColor) || $textColor === null)
		{
			$this->textColor = $textColor;
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
		return $this->getLinkTitleNode() ? $this->getLinkTitleNode()->getText() : null;
	}

	public function getLinkTitleNode(): ?TextNode
	{
		return $this->linkTitle;
	}

	public function setLinkTitle($linkTitle): self
	{
		if (TextNode::isValidText($linkTitle) || $linkTitle === null)
		{
			$this->linkTitle = $linkTitle === null ? null : new TextNode($linkTitle);
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

	public function setBadgesOptions(array $badgesOptions): self
	{
		$this->getBadgesOptions()->setValues($badgesOptions);

		return $this;
	}

	/**
	 * @return Dictionary
	 */
	public function getBadgesOptions(): Dictionary
	{
		if ($this->badgesOptions === null)
		{
			$this->badgesOptions = new Dictionary();
		}

		return $this->badgesOptions;
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

	public function addChildren(array $children): self
	{
		foreach ($children as $childOptions)
		{
			unset($childOptions['tabs']);

			$child = new Item($childOptions);
			$this->addChild($child);
		}

		return $this;
	}

	public function addChild(Item $item): self
	{
		$success = $this->getChildren()->add($item);
		if ($success && $this->getDialog())
		{
			$this->getDialog()->handleItemAdd($item);
		}

		return $this;
	}

	public function setNodeOptions(array $nodeOptions): self
	{
		$this->getNodeOptions()->setValues($nodeOptions);

		return $this;
	}

	public function getNodeOptions(): Dictionary
	{
		if ($this->nodeOptions === null)
		{
			$this->nodeOptions = new Dictionary();
		}

		return $this->nodeOptions;
	}

	public function setTagOptions(array $nodeOptions): self
	{
		$this->getTagOptions()->setValues($nodeOptions);

		return $this;
	}

	public function getTagOptions(): Dictionary
	{
		if ($this->tagOptions === null)
		{
			$this->tagOptions = new Dictionary();
		}

		return $this->tagOptions;
	}

	public function isSelected(): bool
	{
		return $this->selected;
	}

	public function setSelected(bool $flag = true): self
	{
		$this->selected = $flag;

		return $this;
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

	public function setCustomData(array $customData): self
	{
		$this->getCustomData()->setValues($customData);

		return $this;
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

	public function setSort(?int $sort): self
	{
		$this->sort = $sort;

		return $this;
	}

	public function getSort(): ?int
	{
		return $this->sort;
	}

	public function setContextSort(?int $sort): self
	{
		$this->contextSort = $sort;

		return $this;
	}

	public function getContextSort(): ?int
	{
		return $this->contextSort;
	}

	public function setGlobalSort(?int $sort): self
	{
		$this->globalSort = $sort;

		return $this;
	}

	public function getGlobalSort(): ?int
	{
		return $this->globalSort;
	}

	public function setDialog(Dialog $dialog): self
	{
		$this->dialog = $dialog;

		return $this;
	}

	public function getDialog(): ?Dialog
	{
		return $this->dialog;
	}

	public function toArray(): array
	{
		return $this->serializeRecursive($this);
	}

	private function serializeRecursive($data)
	{
		if ($data instanceof \JsonSerializable)
		{
			$data = $data->jsonSerialize();
		}

		if (is_array($data) || $data instanceof \Traversable)
		{
			foreach ($data as $key => $item)
			{
				$data[$key] = $this->serializeRecursive($item);
			}
		}

		return $data;
	}

	public function jsonSerialize()
	{
		$json = [
			'id' => $this->getId(),
			'entityId' => $this->getEntityId(),
			'title' => $this->getTitleNode() !== null ? $this->getTitleNode()->jsonSerialize() : '',
		];

		if ($this->getSubtitleNode() !== null)
		{
			$json['subtitle'] = $this->getSubtitleNode()->jsonSerialize();
		}

		if ($this->getSupertitleNode() !== null)
		{
			$json['supertitle'] = $this->getSupertitleNode()->jsonSerialize();
		}

		if ($this->getCaptionNode() !== null)
		{
			$json['caption'] = $this->getCaptionNode()->jsonSerialize();
		}

		if ($this->getLinkTitleNode() !== null)
		{
			$json['linkTitle'] = $this->getLinkTitleNode()->jsonSerialize();
		}

		if ($this->isSelected())
		{
			$json['selected'] = true;
		}

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

		if ($this->avatarOptions !== null && $this->getAvatarOptions()->count() > 0)
		{
			$json['avatarOptions'] = $this->getAvatarOptions()->getValues();
		}

		if ($this->captionOptions !== null && $this->getCaptionOptions()->count() > 0)
		{
			$json['captionOptions'] = $this->getCaptionOptions()->getValues();
		}

		if ($this->badgesOptions !== null && $this->getBadgesOptions()->count() > 0)
		{
			$json['badgesOptions'] = $this->getBadgesOptions()->getValues();
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
			'textColor',
			'link',
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