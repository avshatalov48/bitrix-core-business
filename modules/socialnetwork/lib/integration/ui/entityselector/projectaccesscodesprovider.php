<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Collection;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\RecentItem;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

final class ProjectAccessCodesProvider extends BaseProvider
{
	private const ENTITY_ID = 'project-access-codes';
	private const TAB_ID = self::ENTITY_ID;
	private const RECENT_LIMIT = 30;
	private const FILL_LIMIT = self::RECENT_LIMIT;
	private const SEARCH_LIMIT = 100;

	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options['fillAccessCodesTab'] = true;
		if (isset($options['fillAccessCodesTab']) && is_bool($options['fillAccessCodesTab']))
		{
			$this->options['fillAccessCodesTab'] = $options['fillAccessCodesTab'];
		}

		$this->options['fillRecentTab'] = null; // auto
		if (isset($options['fillRecentTab']) && is_bool($options['fillRecentTab']))
		{
			$this->options['fillRecentTab'] = $options['fillRecentTab'];
		}
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	/**
	 * @inheritDoc
	 */
	public function getItems(array $ids): array
	{
		$ids = array_map(strval(...), $ids);

		$projectIdsToFetch = $this->getProjectIds($ids);
		if (empty($projectIdsToFetch))
		{
			return [];
		}

		$projects = ProjectProvider::getProjects([
			'projectId' => $projectIdsToFetch,
		]);
		if ($projects->isEmpty())
		{
			return [];
		}

		$allProjectsRoles = [];
		foreach ($projects as $project)
		{
			foreach ($this->makeFlatItems($project) as $role)
			{
				$allProjectsRoles[$role->getId()] = $role;
			}
		}

		$items = [];
		foreach ($ids as $originalId)
		{
			if (isset($allProjectsRoles[$originalId]))
			{
				$items[] = $allProjectsRoles[$originalId];
			}
		}

		return $items;
	}

	private function getProjectIds(array $itemIds): array
	{
		$projectIds = [];
		foreach ($itemIds as $id)
		{
			$accessCode = new AccessCode($id);
			if ($accessCode->getEntityType() === AccessCode::TYPE_SOCNETGROUP)
			{
				$projectIds[] = $accessCode->getEntityId();
			}
		}

		Collection::normalizeArrayValuesByInt($projectIds);

		return $projectIds;
	}

	/**
	 * @return Item[]
	 */
	private function makeFlatItems(EO_Workgroup $project): array
	{
		$items = [];
		foreach ($this->getProjectRoles($project) as $role => $roleName)
		{
			$items[] = new Item([
				'id' => $this->makeId($project->getId(), $role),
				'entityId' => self::ENTITY_ID,
				'entityType' => ProjectProvider::makeProjectEntityType($project),
				'avatar' => ProjectProvider::makeProjectAvatar($project),
				'link' => $this->makeLink($project),
				'title' => $project->requireName() . '. ' . $roleName,
				'tabs' => [
					self::TAB_ID,
				],
			]);
		}

		return $items;
	}

	private function makeId(int $projectId, string $role): string
	{
		return 'SG' . $projectId . '_' . $role;
	}

	private function makeLink(EO_Workgroup $project): string
	{
		$url = \Bitrix\Socialnetwork\Site\GroupUrl::get($project->getId(), $project->getType());
		if ($project->requireType() !== Type::Collab->value)
		{
			$url .= 'card/';
		}

		return $url;
	}

	private function getProjectRoles(EO_Workgroup $project): array
	{
		$roles = $this->getProjectTypeToRolesMap()[$project->requireType()] ?? null;
		if ($roles === null)
		{
			return $this->getProjectTypeToRolesMap()[Type::getDefault()->value];
		}

		return $roles;
	}

	private function getProjectTypeToRolesMap(): array
	{
		static $rolesMapCache = null;

		// roles here are sorted from more specific to more generic, as last role could be used as root in a tree branch
		$rolesMapCache ??= [
			Type::Group->value => [
				SONET_ROLES_OWNER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_GROUP_OWNER'),
				SONET_ROLES_MODERATOR => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_GROUP_MODERATOR'),
				SONET_ROLES_USER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_GROUP_USER'),
			],
			Type::Project->value => [
				SONET_ROLES_OWNER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_PROJECT_OWNER'),
				SONET_ROLES_MODERATOR => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_PROJECT_MODERATOR'),
				SONET_ROLES_USER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_PROJECT_USER'),
			],
			Type::Scrum->value => [
				SONET_ROLES_OWNER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_SCRUM_OWNER'),
				SONET_ROLES_MODERATOR => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_SCRUM_MODERATOR'),
				SONET_ROLES_USER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_SCRUM_USER'),
			],
			Type::Collab->value => [
				SONET_ROLES_OWNER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_COLLAB_OWNER'),
				SONET_ROLES_MODERATOR => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_COLLAB_MODERATOR'),
				SONET_ROLES_USER => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_COLLAB_USER'),
			],
		];

		return $rolesMapCache;
	}

	public function fillDialog(Dialog $dialog): void
	{
		$this->addAccessCodesTab($dialog);

		$dialogHasOnlyThisEntity = count($dialog->getEntities()) === 1;

		$fillRecentTab = (
			$this->options['fillRecentTab'] === true ||
			($this->options['fillRecentTab'] !== false && $dialogHasOnlyThisEntity)
		);

		if ($fillRecentTab)
		{
			$this->fillRecentTab($dialog);
		}

		if ($this->getOption('fillAccessCodesTab'))
		{
			$this->fillAccessCodesTab($dialog);
		}
	}

	private function addAccessCodesTab(Dialog $dialog): void
	{
		$icon =
			'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2223%22%20height%3D%2223%22%20'.
			'fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M11'.
			'.934%202.213a.719.719%200%2001.719%200l3.103%201.79c.222.13.36.367.36.623V8.21a.719.71'.
			'9%200%2001-.36.623l-3.103%201.791a.72.72%200%2001-.719%200L8.831%208.832a.719.719%200%'.
			'2001-.36-.623V4.627c0-.257.138-.495.36-.623l3.103-1.791zM7.038%2010.605a.719.719%200%2'.
			'001.719%200l3.103%201.792a.72.72%200%2001.359.622v3.583a.72.72%200%2001-.36.622l-3.102'.
			'%201.792a.719.719%200%2001-.72%200l-3.102-1.791a.72.72%200%2001-.36-.623v-3.583c0-.257'.
			'.138-.494.36-.622l3.103-1.792zM20.829%2013.02a.719.719%200%2000-.36-.623l-3.102-1.792a'.
			'.719.719%200%2000-.72%200l-3.102%201.792a.72.72%200%2000-.36.622v3.583a.72.72%200%2000'.
			'.36.622l3.103%201.792a.719.719%200%2000.719%200l3.102-1.791a.719.719%200%2000.36-.623v'.
			'-3.583z%22%20fill%3D%22%23ABB1B8%22/%3E%3C/svg%3E';

		$dialog->addTab(new Tab([
			'id' => self::TAB_ID,
			'title' => (string)Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_ACCESS_CODES_TAB'),
			'icon' => [
				'default' => $icon,
				'selected' => str_replace('ABB1B8', 'fff', $icon),
			]
		]));
	}

	private function fillRecentTab(Dialog $dialog): void
	{
		$howMuchLeftToFill = self::RECENT_LIMIT;
		$recentItems = $dialog->getRecentItems()->getEntityItems(self::ENTITY_ID);

		$howMuchLeftToFill -= count($recentItems);
		if ($howMuchLeftToFill <= 0)
		{
			return;
		}

		$globalRecentItems = $dialog->getGlobalRecentItems()->getEntityItems(self::ENTITY_ID);
		foreach ($globalRecentItems as $globalRecentItem)
		{
			if ($howMuchLeftToFill <= 0)
			{
				return;
			}

			$dialog->getRecentItems()->add($globalRecentItem);
			$howMuchLeftToFill--;
		}

		if ($howMuchLeftToFill <= 0)
		{
			return;
		}

		$projects = ProjectProvider::getProjects([
			'limit' => $howMuchLeftToFill,
		]);

		foreach ($projects as $project)
		{
			foreach (array_keys($this->getProjectRoles($project)) as $role)
			{
				$dialog->getRecentItems()->add(
					new RecentItem([
						'id' => $this->makeId($project->getId(), $role),
						'entityId' => self::ENTITY_ID,
					])
				);
			}
		}
	}

	private function fillAccessCodesTab(Dialog $dialog): void
	{
		$projects = ProjectProvider::getProjects([
			'limit' => self::FILL_LIMIT,
		]);

		foreach ($projects as $project)
		{
			$roles = $this->getProjectRoles($project);

			// select the most generic role possible, as it should include all the others as branches
			$root = array_key_last($roles);

			$rootItem = new Item([
				'id' => $this->makeId($project->getId(), $root),
				'entityId' => self::ENTITY_ID,
				'title' => $project->requireName(),
				'entityType' => ProjectProvider::makeProjectEntityType($project),
				'avatar' => ProjectProvider::makeProjectAvatar($project),
				'link' => $this->makeLink($project),
				'tabs' => [
					self::TAB_ID,
				],
			]);

			foreach ($roles as $role => $roleName)
			{
				// yes, we include root here as well

				$rootItem->addChild(new Item([
					'id' => $this->makeId($project->getId(), $role),
					'entityId' => self::ENTITY_ID,
					'entityType' => ProjectProvider::makeProjectEntityType($project),
					'title' => $project->requireName() . '. ' . $roleName,
					'avatar' => ProjectProvider::makeProjectAvatar($project),
					'link' => $this->makeLink($project),
					'nodeOptions' => [
						'title' => $roleName,
						'supertitle' => '',
						'avatar' => '',
						'link' => '',
					]
				]));
			}

			$dialog->addItem($rootItem);
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$projects = ProjectProvider::getProjects([
			'searchQuery' => $searchQuery->getQuery(),
			'searchLimit' => self::SEARCH_LIMIT,
		]);

		if ($projects->count() >= self::SEARCH_LIMIT)
		{
			$searchQuery->setCacheable(false);
		}

		$items = [];
		foreach ($projects as $project)
		{
			$items = [...$items, ...$this->makeFlatItems($project)];
		}

		$dialog->addItems($items);
	}
}
