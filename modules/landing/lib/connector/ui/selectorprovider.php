<?php
namespace Bitrix\Landing\Connector\Ui;

use Bitrix\Landing\Landing;
use Bitrix\Landing\Site;
use Bitrix\Landing\Folder;
use Bitrix\Landing\Site\Type;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class SelectorProvider extends \Bitrix\UI\EntitySelector\BaseProvider
{
	/**
	 * In search mode here is stored the search phrase.
	 * @var ?string
	 */
	protected static $searchPhrase = null;

	/**
	 * SelectorProvider constructor.
	 * @param array $options Options data.
	 * @return void
	 */
	public function __construct(array $options = [])
	{
		parent::__construct();
	}

	/**
	 * This provider is available for everyone (of course not because we have access checking on API level).
	 * @return bool
	 */
	public function isAvailable(): bool
	{
		return true;
	}

	/**
	 * Returns folders by Parent ID.
	 * @param int $siteId Site id.
	 * @param int|null $parentId Parent folder id (null for root dir).
	 * @return Item[]
	 */
	public static function getFolders(int $siteId, ?int $parentId = null): array
	{
		$data = [];
		$filter = [];

		if (self::$searchPhrase)
		{
			$filter['?TITLE'] = '%' . self::$searchPhrase . '%';
		}
		else
		{
			$filter['PARENT_ID'] = $parentId;
		}

		$folders = Site::getFolders($siteId, $filter);

		foreach ($folders as $folder)
		{
			$data[$folder['ID']] = new Item([
				'id' => $folder['ID'],
				'entityId' => 'landing',
				'entityType' => 'folder',
				'title' => $folder['TITLE'],
				'nodeOptions' => ['dynamic' => true]
			]);
		}

		return $data;
	}

	/**
	 * Returns landings by Parent ID.
	 * @param int $siteId Site id.
	 * @param int|null $landingId Landing id (for mark item as selected).
	 * @param int|null $parentId Parent folder id (null for root dir).
	 * @return Item[]
	 */
	public static function getLandings(int $siteId, ?int $landingId = null, ?int $parentId = null): array
	{
		$data = [];
		$landingFake = Landing::createInstance(0);
		$filter = ['SITE_ID' => $siteId];

		// search in folders first (if search mode)
		if (self::$searchPhrase)
		{
			$folders = Site::getFolders($siteId, [
				'?TITLE' => '%' . self::$searchPhrase . '%'
			]);
			if ($folders)
			{
				$filterSub = ['LOGIC' => 'OR'];
				$filterSub['?TITLE'] = '%' . self::$searchPhrase . '%';
				$filterSub['FOLDER_ID'] = [];
				foreach ($folders as $folder)
				{
					$filterSub['FOLDER_ID'][] = $folder['ID'];
				}
				$filter[] = $filterSub;
			}
			else
			{
				$filter['?TITLE'] = '%' . self::$searchPhrase . '%';
			}
		}
		else
		{
			$filter['FOLDER_ID'] = $parentId;
		}

		$rows = [];
		$res = Landing::getList([
			'select' => [
				'ID', 'TITLE', 'FOLDER_ID', 'SITE_ID',
				'DOMAIN_ID' => 'SITE.DOMAIN_ID'
			],
			'filter' => $filter,
			'order' => [
				'DATE_MODIFY' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			$rows[$row['ID']] = $row;
		}

		if ($rows)
		{
			$urls = $landingFake->getPublicUrl(array_keys($rows));
		}

		foreach ($rows as $row)
		{
			$subtitle = null;
			if (self::$searchPhrase && $row['FOLDER_ID'])
			{
				$subtitle = Folder::getBreadCrumbsString($row['FOLDER_ID'], ' / ', $row['SITE_ID']);
			}
			$data[$row['ID']] = new Item([
				'id' => $row['ID'],
				'selected' => $row['ID'] == $landingId,
				'entityId' => 'landing',
				'entityType' => 'landing',
				'title' => $row['TITLE'],
				'avatar' => $landingFake->getPreview($row['ID'], $row['DOMAIN_ID'] == 0, $urls[$row['ID']]),
				'subtitle' => $subtitle ?: ''
			]);
		}

		return $data;
	}

	/**
	 * Returns site id from dialog options.
	 * @param Dialog $dialog Main dialog instance.
	 * @return int|null
	 */
	protected function getSiteIdFromDialog(Dialog $dialog): ?int
	{
		$entity = $dialog->getEntities()['landing'] ?? null;
		if ($entity)
		{
			if ($siteId = $entity->getOptions()['siteId'] ?? null)
			{
				Type::setScope($entity->getOptions()['siteType']);
				return $siteId;
			}
		}

		return null;
	}

	/**
	 * Returns landing id from dialog options.
	 * @param Dialog $dialog Main dialog instance.
	 * @return int|null
	 */
	protected function getLandingIdFromDialog(Dialog $dialog): ?int
	{
		$entity = $dialog->getEntities()['landing'] ?? null;
		if ($entity)
		{
			if ($landingId = $entity->getOptions()['landingId'] ?? null)
			{
				return $landingId;
			}
		}

		return null;
	}

	/**
	 * Sets children items to Dialog.
	 * @param Item $parentItem Instance of clicked item.
	 * @param Dialog $dialog Main dialog instance.
	 * @return void
	 */
	public function getChildren(Item $parentItem, Dialog $dialog): void
	{
		$this::$searchPhrase = null;
		if ($siteId = $this->getSiteIdFromDialog($dialog))
		{
			$landingId = $this->getLandingIdFromDialog($dialog);
			$dialog->addItems($this::getFolders($siteId, $parentItem->getId()));
			$dialog->addItems($this::getLandings($siteId, $landingId, $parentItem->getId()));
		}
	}

	/**
	 * Sets search result to the dialog.
	 * @param SearchQuery $searchQuery Search query instance.
	 * @param Dialog $dialog Main dialog instance.
	 * @return void
	 */
	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$this::$searchPhrase = $searchQuery->getQuery();
		if ($siteId = $this->getSiteIdFromDialog($dialog))
		{
			$dialog->addItems($this::getLandings($siteId));
		}
		$this::$searchPhrase = null;
	}

	/**
	 * Not implemented yet.
	 * @param array $ids
	 * @return Item[]
	 */
	public function getItems(array $ids) : array
	{
		return [];
	}

	/**
	 * Not implemented yet.
	 * @param array $ids
	 * @return Item[]
	 */
	public function getSelectedItems(array $ids) : array
	{
		return [];
	}
}
