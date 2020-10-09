<?

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Search\Content;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\EO_Workgroup_Collection;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\WorkgroupViewTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\RecentItem;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

class ProjectProvider extends BaseProvider
{
	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['project']) && is_bool($options['project']))
		{
			$this->options['project'] = $options['project'];
		}

		if (isset($options['extranet']) && is_bool($options['extranet']))
		{
			$this->options['extranet'] = $options['extranet'];
		}

		if (isset($options['landing']) && is_bool($options['landing']))
		{
			$this->options['landing'] = $options['landing'];
		}

		if (isset($options['features']) && is_array($options['features']))
		{
			$this->options['features'] = $options['features'];
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

	public function getItems(array $ids): array
	{
		return $this->getProjectItems([
			'projectId' => $ids
		]);
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getProjectItems([
			'projectId' => $ids
		]);
	}

	public function fillDialog(Dialog $dialog): void
	{
		$limit = 100;
		$projects = $this->getProjectCollection(['limit' => $limit]);
		$dialog->addItems($this->makeProjectItems($projects, ['tabs' => 'projects']));

		if ($projects->count() < $limit)
		{
			$entity = $dialog->getEntity('project');
			if ($entity)
			{
				$entity->setDynamicSearch(false);
			}
		}

		$icon =
			'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2217%22%20height%3D%2217%22%20'.
			'fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M8.'.
			'17.21a.7.7%200%2001.7%200l3.02%201.744a.7.7%200%2001.35.606v3.488a.7.7%200%2001-.35.60'.
			'6L8.87%208.398a.7.7%200%2001-.7%200L5.148%206.654a.7.7%200%2001-.35-.606V2.56a.7.7%200%2001'.
			'.35-.606L8.169.21zM3.403%208.38a.7.7%200%2001.7%200l3.02%201.745a.7.7%200%2001.35.606v3.488'.
			'a.7.7%200%2001-.35.606l-3.02%201.744a.7.7%200%2001-.7%200l-3.02-1.744a.7.7%200%2001-.35-.606'.
			'V10.73a.7.7%200%2001.35-.606l3.02-1.744zM16.829%2010.73a.7.7%200%2000-.35-.605l-3.02-1.744a.7'.
			'.7%200%2000-.7%200l-3.021%201.744a.7.7%200%2000-.35.606v3.488a.7.7%200%2000.35.606l3.02%201.'.
			'744a.7.7%200%2000.7%200l3.021-1.744a.7.7%200%2000.35-.606V10.73z%22%20fill%3D%22%23ACB2B8%'.
			'22/%3E%3C/svg%3E'
		;

		$dialog->addTab(new Tab([
			'id' => 'projects',
			'title' => Loc::getMessage("SOCNET_ENTITY_SELECTOR_PROJECTS_TAB_TITLE"),
			'stub' => true,
			'icon' => [
				'default' => $icon,
				'selected' => str_replace('ACB2B8', 'fff', $icon),
				//'default' => '/bitrix/js/socialnetwork/entity-selector/images/project-tab-icon.svg',
				//'selected' => '/bitrix/js/socialnetwork/entity-selector/images/project-tab-icon-selected.svg'
			]
		]));

		$fillRecentTab = (
			$this->options['fillRecentTab'] === true ||
			($this->options['fillRecentTab'] !== false && count($dialog->getEntities()) === 1)
		);

		if (!$fillRecentTab)
		{
			return;
		}

		$maxProjectsInRecentTab = 30;

		$recentItems = $dialog->getRecentItems()->getEntityItems('project');
		if (count($recentItems) < $maxProjectsInRecentTab)
		{
			$limit = $maxProjectsInRecentTab - count($recentItems);
			$recentGlobalItems = $dialog->getGlobalRecentItems()->getEntityItems('project');
			foreach ($recentGlobalItems as $recentGlobalItem)
			{
				if ($limit <= 0)
				{
					break;
				}

				if (!isset($recentItems[$recentGlobalItem->getId()]) && $recentGlobalItem->isLoaded())
				{
					$dialog->getRecentItems()->add($recentGlobalItem);
					$limit--;
				}
			}

			$recentItems = $dialog->getRecentItems()->getEntityItems('project');
		}

		if (count($recentItems) < $maxProjectsInRecentTab)
		{
			$recentIds = array_map('intval', array_keys($recentItems));

			$dialog->addRecentItems(
				$this->getProjectItems([
					'!projectId' => $recentIds,
					'viewed' => true,
					'order' => ['VIEWED_PROJECT.DATE_VIEW' => 'desc'],
					'limit' => $maxProjectsInRecentTab - count($recentItems)
				])
			);

			$recentItems = $dialog->getRecentItems()->getEntityItems('project');
		}

		if (count($recentItems) < $maxProjectsInRecentTab)
		{
			$limit = $maxProjectsInRecentTab - count($recentItems);
			foreach ($projects as $project)
			{
				if ($limit <= 0)
				{
					break;
				}

				if (isset($recentItems[$project->getId()]))
				{
					continue;
				}

				$dialog->getRecentItems()->add(new RecentItem([
					'id' => $project->getId(),
					'entityId' => 'project',
					'loaded' => true,
				]));

				$limit--;
			}
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$dialog->addItems(
			$this->getProjectItems(['searchQuery' => $searchQuery->getQuery()])
		);
	}

	public function getProjectCollection(array $options = []): EO_Workgroup_Collection
	{
		$options = array_merge($this->getOptions(), $options);

		return self::getProjects($options);
	}

	public function getProjectItems(array $options = []): array
	{
		return $this->makeProjectItems($this->getProjectCollection($options), $options);
	}

	public function makeProjectItems(EO_Workgroup_Collection $projects, array $options = []): array
	{
		return self::makeItems($projects, array_merge($this->getOptions(), $options));
	}

	public static function getProjects(array $options = []): EO_Workgroup_Collection
	{
		$query = WorkgroupTable::query();
		$query->setSelect(['ID', 'NAME', 'CLOSED', 'VISIBLE', 'OPENED', 'IMAGE_ID', 'LANDING']);

		if (isset($options['visible']) && is_bool(isset($options['visible'])))
		{
			$query->where('VISIBLE', $options['visible'] ? 'Y' : 'N');
		}

		if (isset($options['open']) && is_bool(isset($options['open'])))
		{
			$query->where('OPENED', $options['open'] ? 'Y' : 'N');
		}

		if (isset($options['closed']) && is_bool(isset($options['closed'])))
		{
			$query->where('CLOSED', $options['closed'] ? 'Y' : 'N');
		}

		if (isset($options['landing']) && is_bool(isset($options['landing'])))
		{
			$query->where('LANDING', $options['landing'] ? 'Y' : 'N');
		}

		if (isset($options['active']) && is_bool(isset($options['active'])))
		{
			$query->where('ACTIVE', $options['active'] ? 'Y' : 'N');
		}

		if (isset($options['project']) && is_bool(isset($options['project'])))
		{
			$query->where('PROJECT', $options['project'] ? 'Y' : 'N');
		}

		if (!empty($options['searchQuery']) && is_string($options['searchQuery']))
		{
			$query->whereMatch(
				'SEARCH_INDEX',
				Filter\Helper::matchAgainstWildcard(
					Content::prepareStringToken($options['searchQuery']),
					'*',
					1
				)
			);
		}

		$currentUserId = (!empty($options['currentUserId']) && is_int($options['currentUserId'])
			? $options['currentUserId'] : $GLOBALS['USER']->getId());

		$query->registerRuntimeField(
			new Reference(
				'PROJECT_SITE', WorkgroupSiteTable::class, Join::on('this.ID', 'ref.GROUP_ID'), ["join_type" => "INNER"]
			)
		);

		$siteId = !empty($options['siteId']) && is_string($options['siteId']) ? $options['siteId'] : SITE_ID;
		$query->where('PROJECT_SITE.SITE_ID', $siteId);

		if (!isset($options['myProjectsOnly']) || $options['myProjectsOnly'] === true)
		{
			$query->registerRuntimeField(
				new Reference(
					'MY_PROJECT',
					UserToGroupTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $currentUserId)->where(
							'ref.ROLE',
							'<=',
							UserToGroupTable::ROLE_USER
						),
					["join_type" => "INNER"]
				)
			);
		}

		if (isset($options['viewed']) && is_bool(isset($options['viewed'])))
		{
			$query->registerRuntimeField(
				new Reference(
					'VIEWED_PROJECT',
					WorkgroupViewTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $currentUserId),
					['join_type' => 'INNER']
				)
			);
		}

		$extranetSiteId = Option::get("extranet", "extranet_site");
		$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ? $extranetSiteId : false);
		if ($extranetSiteId)
		{
			$query->registerRuntimeField(
				new Reference(
					'EXTRANET_PROJECT',
					WorkgroupSiteTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')->where('ref.SITE_ID', $extranetSiteId),
					["join_type" => "LEFT"]
				)
			);

			$query->registerRuntimeField(
				new ExpressionField(
					'IS_EXTRANET', 'IF(%s IS NOT NULL, \'Y\', \'N\')', ['EXTRANET_PROJECT.GROUP_ID']
				)
			);

			$query->addSelect('IS_EXTRANET');

			if (isset($options['extranet']) && is_bool($options['extranet']))
			{
				if ($options['extranet'])
				{
					$query->whereNotNull('EXTRANET_PROJECT.GROUP_ID');
				}
				else
				{
					$query->whereNull('EXTRANET_PROJECT.GROUP_ID');
				}
			}
		}

		$projectIds = [];
		$projectFilter = (
			isset($options['projectId'])
			? 'projectId'
			: (isset($options['!projectId']) ? '!projectId' : null)
		);

		if (isset($options[$projectFilter]))
		{
			if (is_array($options[$projectFilter]) && !empty($options[$projectFilter]))
			{
				foreach ($options[$projectFilter] as $id)
				{
					$id = intval($id);
					if ($id > 0)
					{
						$projectIds[] = $id;
					}
				}

				$projectIds = array_unique($projectIds);

				if (!empty($projectIds))
				{
					if ($projectFilter === 'projectId')
					{
						$query->whereIn('ID', $projectIds);
					}
					else
					{
						$query->whereNotIn('ID', $projectIds);
					}
				}
			}
			else if (!is_array($options[$projectFilter]) && intval($options[$projectFilter]) > 0)
			{
				if ($projectFilter === 'projectId')
				{
					$query->where('ID', intval($options[$projectFilter]));
				}
				else
				{
					$query->whereNot('ID', intval($options[$projectFilter]));
				}
			}
		}

		if ($projectFilter === 'projectId' && count($projectIds) > 1 && empty($options['order']))
		{
			$query->registerRuntimeField(
				new ExpressionField(
					'ID_SEQUENCE', 'FIELD(%s, '.join(',', $projectIds).')', 'ID'
				)
			);

			$query->setOrder('ID_SEQUENCE');
		}
		elseif (!empty($options['order']) && is_array($options['order']))
		{
			$query->setOrder($options['order']);
		}
		else
		{
			$query->setOrder(['NAME' => 'asc']);
		}

		$query->setLimit(isset($options['limit']) && is_int($options['limit']) ? $options['limit'] : 100);

		//echo '<pre>'.$query->getQuery().'</pre>';

		$projects = $query->exec()->fetchCollection();

		if (isset($options['features']) && is_array($options['features']))
		{
			return self::filterByFeatures($projects, $options['features'], $currentUserId, $siteId);
		}

		return $projects;
	}

	public static function filterByFeatures(
		EO_Workgroup_Collection $projects, array $features, int $userId, string $siteId
	)
	{
		if (empty($features))
		{
			return $projects;
		}

		$projectIds = $projects->getIdList();
		foreach ($features as $feature => $operations)
		{
			$availableIds = \CSocNetFeatures::isActiveFeature(SONET_ENTITY_GROUP, $projectIds, $feature);
			if (!is_array($availableIds))
			{
				unset($features[$feature]); // bad feature
				continue;
			}

			$hasUnavailableId = false;
			foreach ($availableIds as $projectId => $isAvailable)
			{
				if (!$isAvailable)
				{
					$hasUnavailableId = true;
					$projects->removeByPrimary($projectId);
				}
			}

			if ($hasUnavailableId)
			{
				$projectIds = $projects->getIdList();
			}
		}

		$isUserModuleAdmin = \CSocNetUser::isUserModuleAdmin($userId, $siteId);
		$availableIdsByFeature = [];

		// Features have logic 'AND', whereas operations have logic 'OR'.
		foreach ($features as $feature => $operations)
		{
			if (!is_array($operations) || empty($operations))
			{
				$availableIdsByFeature[] = $projectIds;
				continue;
			}

			$availableFeatureIds = [];
			$ids = $projectIds;
			foreach ($operations as $operation)
			{
				$availableIds = \CSocNetFeaturesPerms::canPerformOperation(
					$userId,
					SONET_ENTITY_GROUP,
					$ids,
					$feature,
					$operation,
					$isUserModuleAdmin
				);

				if (!is_array($availableIds))
				{
					continue;
				}

				foreach ($availableIds as $projectId => $isAvailable)
				{
					if ($isAvailable)
					{
						$availableFeatureIds[] = $projectId;
					}
				}

				$ids = array_diff($ids, $availableFeatureIds);
			}

			$availableIdsByFeature[] = $availableFeatureIds;
		}

		$availableIds = [];
		if (!empty($availableIdsByFeature))
		{
			$availableIds = (count($availableIdsByFeature) > 1 ? call_user_func_array(
				'array_intersect',
				$availableIdsByFeature
			) : $availableIdsByFeature[0]);
		}

		if (empty($availableIds))
		{
			return new EO_Workgroup_Collection();
		}
		else
		{
			$wrongIds = array_diff($projects->getIdList(), $availableIds);
			foreach ($wrongIds as $wrongId)
			{
				$projects->removeByPrimary($wrongId);
			}
		}

		return $projects;
	}

	public static function makeItems(EO_Workgroup_Collection $projects, $options = [])
	{
		$result = [];
		foreach ($projects as $project)
		{
			$result[] = self::makeItem($project, $options);
		}

		return $result;
	}

	/**
	 * @param EO_Workgroup $project
	 * @param array $options
	 *
	 * @return Item
	 */
	public static function makeItem(EO_Workgroup $project, $options = []): Item
	{
		$extranetSiteId = Option::get("extranet", "extranet_site");
		$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ? $extranetSiteId : false);

		$entityType =
			$extranetSiteId && $project->get('IS_EXTRANET') === 'Y'
				? 'extranet'
				: 'project'
		;

		$item = new Item(
			[
				'id' => $project->getId(),
				'entityId' => 'project',
				'entityType' => $entityType,
				'title' => $project->getName(),
				'avatar' => self::makeUserAvatar($project),
				'customData' => [
					'landing' => $project->getLanding(),
					'active' => $project->getActive() === 'Y',
					'closed' => $project->getClosed() === 'Y',
					'open' => $project->getOpened() === 'Y',
					'project' => $project->getProject() === 'Y',
				]
			]
		);

		if (!empty($options['tabs']))
		{
			$item->addTab($options['tabs']);
		}

		return $item;
	}

	public static function makeUserAvatar(EO_Workgroup $project): ?string
	{
		if (empty($project->getImageId()))
		{
			return null;
		}

		$avatar = \CFile::resizeImageGet(
			$project->getImageId(),
			['width' => 100, 'height' => 100],
			BX_RESIZE_IMAGE_EXACT,
			false
		);

		return !empty($avatar['src']) ? $avatar['src'] : null;
	}

	public static function getProjectUrl(?int $projectId = null, ?int $currentUserId = null): string
	{
		if (UserProvider::isExtranetUser($currentUserId))
		{
			$extranetSiteId = Option::get("extranet", "extranet_site");
			$projectPage = Option::get('socialnetwork', 'workgroups_page', false, $extranetSiteId);
			if (!$projectPage)
			{
				$projectPage = '/extranet/workgroups/';
			}
		}
		else
		{
			$projectPage = Option::get('socialnetwork', 'workgroups_page', false, SITE_ID);
			if (!$projectPage)
			{
				$projectPage = SITE_DIR.'company/workgroups/';
			}
		}

		return $projectPage.'group/'.($projectId !== null ? $projectId : '#id#').'/';
	}
}