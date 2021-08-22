<?php
namespace Bitrix\SocialNetwork\Integration\UI\EntitySelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\SocialNetwork\EO_WorkgroupTag ;
use Bitrix\SocialNetwork\EO_WorkgroupTag_Collection ;
use Bitrix\Socialnetwork\WorkgroupTagTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\RecentItem;
use Bitrix\UI\EntitySelector\Tab;

/**
 * Class ProjectTagProvider
 *
 * @package Bitrix\SocialNetwork\Integration\UI\EntitySelector
 */
class ProjectTagProvider extends BaseProvider
{
	private static $entityId = 'project-tag';
	private static $maxCount = 100;

	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options['groupId'] = $options['groupId'];
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function getItems(array $ids): array
	{
		return [];
	}

	public function getSelectedItems(array $ids): array
	{
		return [];
	}

	public function getTagItems(array $options = []): array
	{
		return $this->makeTagItems($this->getTagCollection($options), $options);
	}

	public function getTagCollection(array $options = []): EO_WorkgroupTag_Collection
	{
		$options = array_merge($this->getOptions(), $options);

		return self::getTags($options);
	}

	public static function getTags(array $options = []): EO_WorkgroupTag_Collection
	{
		$query = WorkgroupTagTable::query();
		$query->setSelect(['NAME', 'GROUP_ID']);

		if ($options['selected'] && $options['groupId'])
		{
			$query->where('GROUP_ID', $options['groupId']);
		}

		return $query->exec()->fetchCollection();
	}

	public function makeTagItems(EO_WorkgroupTag_Collection $tags, array $options = []): array
	{
		return self::makeItems($tags, array_merge($this->getOptions(), $options));
	}

	/**
	 * @param EO_WorkgroupTag_Collection $tags
	 * @param array $options
	 * @return array
	 */
	public static function makeItems(EO_WorkgroupTag_Collection $tags, array $options = []): array
	{
		$result = [];
		foreach ($tags as $tag)
		{
			$result[] = self::makeItem($tag, $options);
		}

		return $result;
	}

	/**
	 * @param EO_WorkgroupTag $tag
	 * @param array $options
	 *
	 * @return Item
	 */
	public static function makeItem(EO_WorkgroupTag $tag, array $options = []): Item
	{
		return new Item([
			'id' => $tag->getName(),
			'entityId' => self::$entityId,
			'title' => $tag->getName(),
			'selected' => (isset($options['selected']) && $options['selected']),
			'tabs' => ['all'],
		]);
	}

	public function fillDialog(Dialog $dialog): void
	{
		$dialog->addTab(
			new Tab([
				'id' => 'all',
				'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_TAG_TAB_TITLE'),
				'stub' => true,
			])
		);

		$dialog->addItems(
			$this->getTagItems(['selected' => true])
		);

		if ($dialog->getItemCollection()->count() < self::$maxCount)
		{
			$this->fillWithRecentTags($dialog);
		}
	}

	private function fillWithRecentTags(Dialog $dialog): void
	{
		$recentItems = $dialog->getRecentItems()->getAll();
		foreach ($recentItems as $item)
		{
			/** @var RecentItem $item */
			if ($dialog->getItemCollection()->get(self::$entityId, $item->getId()))
			{
				continue;
			}

			$name = (string)$item->getId();
			$dialog->addItem(
				new Item([
					'id' => $name,
					'entityId' => self::$entityId,
					'title' => $name,
					'selected' => false,
					'tabs' => ['all'],
				])
			);

			if ($dialog->getItemCollection()->count() >= self::$maxCount)
			{
				break;
			}
		}
	}
}