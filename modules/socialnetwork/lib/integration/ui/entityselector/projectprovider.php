<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Intranet\Settings\Tools\ToolsManager;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;
use Bitrix\Socialnetwork\Collab\Integration\IM;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\EO_Workgroup_Collection;
use Bitrix\Socialnetwork\FeaturePermTable;
use Bitrix\Socialnetwork\FeatureTable;
use Bitrix\Socialnetwork\Helper\Feature;
use Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\WorkgroupViewTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\RecentItem;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Collab\User\User;

class ProjectProvider extends BaseProvider
{
	protected const ENTITY_ID = 'project';
	protected const MAX_PROJECTS_IN_RECENT_TAB = 30;
	protected const SEARCH_LIMIT = 100;

	private static array $groupDialogIds = [];

	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['project']) && is_bool($options['project']))
		{
			$this->options['project'] = $options['project'];
		}

		if (!empty($options['!type']) && is_array($options['!type']))
		{
			$this->options['!type'] = $options['!type'];
		}

		if (!empty($options['type']) && is_array($options['type']))
		{
			$this->options['type'] = $options['type'];
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

		$this->options['checkFeatureForCreate'] = false;
		if (isset($options['checkFeatureForCreate']) && is_bool($options['checkFeatureForCreate']))
		{
			$this->options['checkFeatureForCreate'] = $options['checkFeatureForCreate'];
		}

		$this->options['fillRecentTab'] = null; // auto
		if (isset($options['fillRecentTab']) && is_bool($options['fillRecentTab']))
		{
			$this->options['fillRecentTab'] = $options['fillRecentTab'];
		}

		$this->options['createProjectLink'] = null; // auto
		if (isset($options['createProjectLink']) && is_bool($options['createProjectLink']))
		{
			$this->options['createProjectLink'] = $options['createProjectLink'];
		}

		$this->options['lockProjectLinkFeatureId'] = '';
		if (isset($options['lockProjectLinkFeatureId']) && is_string($options['lockProjectLinkFeatureId']))
		{
			$this->options['lockProjectLinkFeatureId'] = $options['lockProjectLinkFeatureId'];
		}

		$this->options['lockProjectLink'] = false;
		if (isset($options['lockProjectLink']) && is_bool($options['lockProjectLink']))
		{
			$this->options['lockProjectLink'] = $options['lockProjectLink'];
		}

		if (isset($options['projectId']))
		{
			if (is_array($options['projectId']))
			{
				$this->options['projectId'] = $options['projectId'];
			}
			elseif (is_string($options['projectId']) || is_int($options['projectId']))
			{
				$this->options['projectId'] = (int)$options['projectId'];
			}
		}
		elseif (isset($options['!projectId']))
		{
			if (is_array($options['!projectId']))
			{
				$this->options['!projectId'] = $options['!projectId'];
			}
			elseif (is_string($options['!projectId']) || is_int($options['!projectId']))
			{
				$this->options['!projectId'] = (int)$options['!projectId'];
			}
		}

		$this->options['maxProjectsInRecentTab'] = static::MAX_PROJECTS_IN_RECENT_TAB;
		if (isset($options['maxProjectsInRecentTab']) && is_int($options['maxProjectsInRecentTab']))
		{
			$this->options['maxProjectsInRecentTab'] = max(
				1,
				min($options['maxProjectsInRecentTab'], static::MAX_PROJECTS_IN_RECENT_TAB)
			);
		}

		$this->options['searchLimit'] = static::SEARCH_LIMIT;
		if (isset($options['searchLimit']) && is_int($options['searchLimit']))
		{
			$this->options['searchLimit'] = max(1, min($options['searchLimit'], static::SEARCH_LIMIT));
		}

		$this->options['shouldSelectProjectDates'] = false;
		if (isset($options['shouldSelectProjectDates']) && is_bool($options['shouldSelectProjectDates']))
		{
			$this->options['shouldSelectProjectDates'] = (bool)$options['shouldSelectProjectDates'];
		}

		$this->options['shouldSelectDialogId'] = false;
		if (isset($options['shouldSelectDialogId']) && is_bool($options['shouldSelectDialogId']))
		{
			$this->options['shouldSelectDialogId'] = $options['shouldSelectDialogId'];
		}

		$this->options['addProjectMetaUsers'] = false;
		if (isset($options['addProjectMetaUsers']) && is_bool($options['addProjectMetaUsers']))
		{
			$this->options['addProjectMetaUsers'] = (bool)$options['addProjectMetaUsers'];
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

	public function getPreselectedItems(array $ids): array
	{
		return $this->getProjectItems([
			'projectId' => $ids,
			'myProjectsOnly' => false,
		]);
	}

	public function fillDialog(Dialog $dialog): void
	{
		$limit = 100;
		$projects = $this->getProjectCollection(['limit' => $limit]);
		$dialog->addItems($this->makeProjectItems($projects, ['tabs' => 'projects']));
		$currentUserId = UserProvider::getCurrentUserId();
		$userService = new User($currentUserId);
		$isCollaber = $userService->isCollaber();
/*
		if ($projects->count() < $limit)
		{
			$entity = $dialog->getEntity('project');
			if ($entity)
			{
				$entity->setDynamicSearch(false);
			}
		}
*/
		if ($isCollaber)
		{
			$dialog->addTab(new Tab([
				'id' => 'projects',
				'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_COLLAB_TAB_TITLE'),
				'stub' => true,
				'icon' => [
					'default' => '/bitrix/js/socialnetwork/entity-selector/src/images/collab-tab-icon.svg',
					'selected' => '/bitrix/js/socialnetwork/entity-selector/src/images/collab-tab-icon-selected.svg'
				]
			]));
		}
		else
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
			'id' => 'projects',
			'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECTS_TAB_TITLE'),
			'stub' => true,
			'icon' => [
				'default' => $icon,
				'selected' => str_replace('ABB1B8', 'fff', $icon),
				//'default' => '/bitrix/js/socialnetwork/entity-selector/images/project-tab-icon.svg',
				//'selected' => '/bitrix/js/socialnetwork/entity-selector/images/project-tab-icon-selected.svg'
			]
		]));
		}

		$onlyProjectsMode = count($dialog->getEntities()) === 1;

		$fillRecentTab = (
			$this->options['fillRecentTab'] === true ||
			($this->options['fillRecentTab'] !== false && $onlyProjectsMode)
		);

		if ($fillRecentTab)
		{
			$this->fillRecentTab($dialog, $projects);
		}

		$createProjectLink =
			$this->options['createProjectLink'] === true ||
			($this->options['createProjectLink'] !== false && $onlyProjectsMode)
		;

		if (
			$this->options['checkFeatureForCreate']
			&& !Feature::isFeatureEnabled(Feature::PROJECTS_GROUPS)
		)
		{
			$createProjectLink = false;
		}

		if ($createProjectLink && self::canCreateProject())
		{
			$footerOptions = [];
			if ($dialog->getFooter() === 'BX.SocialNetwork.EntitySelector.Footer')
			{
				// Footer could be set from UserProvider
				$footerOptions = $dialog->getFooterOptions() ?? [];
			}

			if ($this->options['lockProjectLink'])
			{
				$footerOptions['lockProjectLink'] = true;
				$footerOptions['lockProjectLinkFeatureId'] = $this->options['lockProjectLinkFeatureId'] ?? '';
			}

			$footerOptions['createProjectLink'] = self::getCreateProjectUrl(UserProvider::getCurrentUserId());
			$dialog->setFooter('BX.SocialNetwork.EntitySelector.Footer', $footerOptions);
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

		return static::getProjects($options);
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
		$isProjectsEnabled = true;
		$isScrumEnabled = true;
		if (Loader::includeModule('intranet'))
		{
			$toolsManager = ToolsManager::getInstance();
			$isProjectsEnabled = $toolsManager->checkAvailabilityByToolId('projects');
			$isScrumEnabled = $toolsManager->checkAvailabilityByToolId('scrum');
			if (!$isProjectsEnabled && !$isScrumEnabled)
			{
				return new EO_Workgroup_Collection();
			}
		}

		$query = WorkgroupTable::query();
		$selectFields = [
			'ID',
			'NAME',
			'ACTIVE',
			'PROJECT',
			'CLOSED',
			'VISIBLE',
			'OPENED',
			'IMAGE_ID',
			'AVATAR_TYPE',
			'LANDING',
			'TYPE',
		];

		if (
			isset($options['shouldSelectProjectDates'])
			&& is_bool(isset($options['shouldSelectProjectDates']))
			&& $options['shouldSelectProjectDates']
		)
		{
			$selectFields[] = 'PROJECT_DATE_START';
			$selectFields[] = 'PROJECT_DATE_FINISH';
		}
		$query->setSelect($selectFields);

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

		if (!empty($options['!type']) && is_array($options['!type']))
		{
			$filter = Query::filter()
				->logic('or')
				->whereNotIn('TYPE', $options['!type'])
				->whereNull('TYPE');

			$query->where($filter);
		}

		if (!empty($options['type']) && is_array($options['type']))
		{
			$query->whereIn('TYPE', $options['type']);
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
				'PROJECT_SITE',
				WorkgroupSiteTable::class,
				Join::on('this.ID', 'ref.GROUP_ID'),
				['join_type' => 'INNER']
			)
		);

		$siteId = !empty($options['siteId']) && is_string($options['siteId']) ? $options['siteId'] : SITE_ID;
		$query->where('PROJECT_SITE.SITE_ID', $siteId);

		$options['myProjectsOnly'] ??= true;
		$notOnlyMyProjects = ($options['myProjectsOnly'] === false);

		$currentUserModuleAdmin = \CSocNetUser::isCurrentUserModuleAdmin();
		if (!$currentUserModuleAdmin)
		{
			$query->registerRuntimeField(
				new Reference(
					'MY_PROJECT',
					UserToGroupTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')
						->where('ref.USER_ID', $currentUserId)
						->where(
							'ref.ROLE',
							'<=',
							UserToGroupTable::ROLE_USER
						),
					['join_type' => $notOnlyMyProjects ? Join::TYPE_LEFT : Join::TYPE_INNER]
				)
			);
			$query->addSelect('MY_PROJECT.ROLE', 'PROJECT_ROLE');
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

		$extranetSiteId = Option::get('extranet', 'extranet_site');
		$extranetSiteId = (
			$extranetSiteId
			&& ModuleManager::isModuleInstalled('extranet') ? $extranetSiteId : false
		);
		if ($extranetSiteId)
		{
			$query->registerRuntimeField(
				new Reference(
					'EXTRANET_PROJECT',
					WorkgroupSiteTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')->where('ref.SITE_ID', $extranetSiteId),
					['join_type' => 'LEFT']
				)
			);

			$query->registerRuntimeField(
				new ExpressionField(
					'IS_EXTRANET', 'CASE WHEN %s IS NOT NULL THEN \'Y\' ELSE \'N\' END', ['EXTRANET_PROJECT.GROUP_ID']
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
					$projectIds[] = (int)$id;
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
			else if (!is_array($options[$projectFilter]) && (int)$options[$projectFilter] > 0)
			{
				if ($projectFilter === 'projectId')
				{
					$query->where('ID', (int)$options[$projectFilter]);
				}
				else
				{
					$query->whereNot('ID', (int)$options[$projectFilter]);
				}
			}
		}

		if (
			$projectFilter === 'projectId'
			&& empty($options['order'])
			&& ($projectsCount = count($projectIds)) > 1
		)
		{
			$helper = Application::getConnection()->getSqlHelper();
			$field = new ExpressionField(
				'ID_SEQUENCE',
				$helper->getOrderByIntField('%s', $projectIds, false),
				array_fill(0, $projectsCount, 'ID')
			);

			$query
				->registerRuntimeField($field)
				->setOrder($field->getName());
		}
		elseif (!empty($options['order']) && is_array($options['order']))
		{
			$query->setOrder($options['order']);
		}
		else
		{
			$query->setOrder(['NAME' => 'asc']);
		}

		$isUserModuleAdmin = \CSocNetUser::isUserModuleAdmin($currentUserId, $siteId);

		if (
			isset($options['features'])
			&& is_array($options['features'])
			&& !empty($options['features'])
		)
		{
			foreach (array_keys($options['features']) as $feature)
			{
				if (!self::isAllowedFeatures($feature))
				{
					return new EO_Workgroup_Collection();
				}

				$featureField = new Reference(
					"BF_{$feature}",
					FeatureTable::class,
					Join::on('this.ID', 'ref.ENTITY_ID')
						->where('ref.ENTITY_TYPE', FeatureTable::FEATURE_ENTITY_TYPE_GROUP)
						->where('ref.FEATURE', $feature)
						->where('ref.ACTIVE', 'N'),
					['join_type' => 'LEFT']
				);
				$query->registerRuntimeField($featureField);

				$query->whereNull("BF_{$feature}.ENTITY_ID");
			}

			if (!$isUserModuleAdmin)
			{
				$featuresPermissionsQuery = self::getFeaturesPermissionsQuery(
					$currentUserId,
					$options['features']
				);
				if ($featuresPermissionsQuery)
				{
					$query->whereIn('ID', $featuresPermissionsQuery);
				}
			}
		}

		if (!$isProjectsEnabled)
		{
			$query->whereNotNull('SCRUM_MASTER_ID');
		}

		if (!$isScrumEnabled)
		{
			$query->whereNull('SCRUM_MASTER_ID');
		}

		if (isset($options['limit']) && is_int($options['limit']))
		{
			$query->setLimit($options['limit']);
		}
		elseif ($projectFilter !== 'projectId' || empty($projectIds))
		{
			$query->setLimit($options['searchLimit'] ?? null);
		}

		$eoWorkgroups = $query->exec()->fetchCollection();

		if ($currentUserModuleAdmin)
		{
			return $eoWorkgroups;
		}

		$workgroups = new EO_Workgroup_Collection();
		foreach ($eoWorkgroups as $eoWorkgroup)
		{
			$isMember = $query->getEntity()->hasField('MY_PROJECT') && !empty($eoWorkgroup->get('MY_PROJECT'));
			$notSecretGroup = $eoWorkgroup->getVisible() === true;
			if ($isMember || ($notOnlyMyProjects && $notSecretGroup))
			{
				$workgroups->add($eoWorkgroup);
			}
		}

		if ($options['shouldSelectDialogId'] ?? false)
		{
			$chatData = Workgroup::getChatData([
				'group_id' => array_diff($workgroups->getIdList(), array_keys(static::$groupDialogIds)),
				'skipAvailabilityCheck' => true,
			]);

			foreach ($workgroups as $workgroup)
			{
				$id = $workgroup->getId();
				static::$groupDialogIds[$id] = IM\Dialog::getDialogId($chatData[$id] ?? 0);
			}
		}

		return $workgroups;
	}

	private static function isAllowedFeatures($feature = ''): bool
	{
		static $globalFeatures = null;

		if ($globalFeatures === null)
		{
			$globalFeatures = \CSocNetAllowed::getAllowedFeatures();
		}

		if (
			!isset($globalFeatures[$feature]['allowed'])
			|| !is_array($globalFeatures[$feature]['allowed'])
			|| !in_array(SONET_ENTITY_GROUP, $globalFeatures[$feature]['allowed'], true)
			|| mb_strlen($feature) <= 0
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * @deprecated
	 */
	public static function getFeatureQuery($alias, $feature = '')
	{
		if (!self::isAllowedFeatures($feature))
		{
			return false;
		}

		$subQuery = FeatureTable::query();
		$subQuery->where('ENTITY_TYPE', FeatureTable::FEATURE_ENTITY_TYPE_GROUP);
		$subQuery->where('FEATURE', $feature);
		$subQuery->where('ACTIVE', 'N');
		$subQuery->registerRuntimeField(
			new ExpressionField(
				'IS_INACTIVE_IN_GROUP', "CASE WHEN {$alias}.ID = %s THEN 1 ELSE 0 END", 'ENTITY_ID'
			)
		);
		$subQuery->where('IS_INACTIVE_IN_GROUP', 1);

		return $subQuery;
	}

	/**
	 * @deprecated
	 */
	public static function getFeaturesPermissionsQuery($currentUserId, $featuresList = [])
	{
		$helper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
		$globalFeatures = \CSocNetAllowed::getAllowedFeatures();

		$workWithClosedGroups = (Option::get('socialnetwork', 'work_with_closed_groups', 'N') === 'Y');

		$query = new \Bitrix\Main\Entity\Query(WorkgroupTable::getEntity());
		$query->addSelect('ID');

		if ($currentUserId > 0)
		{
			$query->registerRuntimeField(new Reference(
				'UG',
				UserToGroupTable::getEntity(),
				Join::on('this.ID', 'ref.GROUP_ID')
					->where('ref.USER_ID', $currentUserId),
				[ 'join_type' => 'INNER' ]
			));
		}

		$hasFilter = false;

		$featureEntity = clone FeatureTable::getEntity();

		foreach ($featuresList as $feature => $operationsList)
		{
			if (empty($operationsList))
			{
				continue;
			}

			$hasFilter = true;

			$defaultPerm = 'A';
			foreach ($globalFeatures[$feature]['operations'] as $operation => $perms)
			{
				if (
					!in_array($operation, $operationsList, true)
					|| !is_set($perms[FeatureTable::FEATURE_ENTITY_TYPE_GROUP])
				)
				{
					continue;
				}

				if ($perms[FeatureTable::FEATURE_ENTITY_TYPE_GROUP] > $defaultPerm)
				{
					$defaultPerm = $perms[FeatureTable::FEATURE_ENTITY_TYPE_GROUP];
				}
			}

			$query->registerRuntimeField(new Reference(
				"F_{$feature}",
				$featureEntity,
				Join::on('this.ID', 'ref.ENTITY_ID')
					->where('ref.ENTITY_TYPE', FeatureTable::FEATURE_ENTITY_TYPE_GROUP)
					->where('ref.FEATURE', $feature),
				[ 'join_type' => 'LEFT' ]
			));

			$featureEntity->addField(new Reference(
				"FP_{$feature}",
				FeaturePermTable::class,
				Join::on('this.ID', 'ref.FEATURE_ID'),
				[ 'join_type' => 'LEFT' ]
			));

			$query->where(\Bitrix\Main\Entity\Query::filter()
				->logic('or')
				->whereIn("F_{$feature}.FP_{$feature}.OPERATION_ID", $operationsList)
				->whereNull("F_{$feature}.FP_{$feature}.OPERATION_ID")
			);

			if ($currentUserId > 0)
			{
				$minOperationsList = ($globalFeatures[$feature]['minoperation'] ?? []);
				if (!is_array($minOperationsList))
				{
					$minOperationsList = [ $minOperationsList ];
				}

				$conditionsList = [];
				$substitutes = [];

				if (!$workWithClosedGroups && !empty($minOperationsList))
				{
					$minOperations = implode(', ', array_map(static function($operation) use ($helper) { return "'" . $helper->forSql($operation) . "'"; }, $minOperationsList));
					$conditionsList[] = "WHEN %s = 'Y' AND %s NOT IN ({$minOperations}) THEN 'A'";
					$substitutes[] = 'CLOSED';
					$substitutes[] = "F_{$feature}.FP_{$feature}.OPERATION_ID";
				}

				$conditionsList[] = "WHEN %s = 'N' AND %s IN ('N', 'L') THEN 'K'";
				$substitutes[] = 'VISIBLE';
				$substitutes[] = "F_{$feature}.FP_{$feature}.ROLE";

				$conditionsList[] = 'WHEN %s IS NOT NULL THEN %s';
				$substitutes[] = "F_{$feature}.FP_{$feature}.ROLE";
				$substitutes[] = "F_{$feature}.FP_{$feature}.ROLE";

				$conditions = implode(' ', $conditionsList);

				$query->registerRuntimeField(new ExpressionField(
					"MIN_PERMISSION_{$feature}",
					"CASE {$conditions} ELSE '{$defaultPerm}' END",
					$substitutes
				));

				$query->registerRuntimeField(
					new ExpressionField(
						"HAS_ACCESS_{$feature}",
						'CASE WHEN %s <= %s THEN 1 ELSE 0 END',
						[
							'UG.ROLE',
							"MIN_PERMISSION_{$feature}",
						]
					)
				);
				$query->where("HAS_ACCESS_{$feature}", 1);
			}
			else
			{
				$query->registerRuntimeField(new ExpressionField(
					"MIN_PERMISSION_{$feature}",
					"CASE WHEN %s IS NOT NULL THEN %s ELSE '{$defaultPerm}' END",
					[
						"F_{$feature}.FP_{$feature}.ROLE",
						"F_{$feature}.FP_{$feature}.ROLE"
					]
				));

				$query->where("MIN_PERMISSION_{$feature}", 'N');
			}
		}

		return ($hasFilter ? $query : false);
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
				return new EO_Workgroup_Collection();
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
				if (empty($ids))
				{
					break;
				}

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
			$availableIds = (
				count($availableIdsByFeature) > 1
					? call_user_func_array('array_intersect', $availableIdsByFeature)
					: $availableIdsByFeature[0]
			);
		}

		if (empty($availableIds))
		{
			return new EO_Workgroup_Collection();
		}

		$wrongIds = array_diff($projects->getIdList(), $availableIds);
		foreach ($wrongIds as $wrongId)
		{
			$projects->removeByPrimary($wrongId);
		}

		return $projects;
	}

	public static function makeItems(EO_Workgroup_Collection $projects, $options = [])
	{
		$result = [];
		foreach ($projects as $project)
		{
			$result[] = static::makeItem($project, $options);
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
		$extranetSiteId = Option::get('extranet', 'extranet_site');
		$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ? $extranetSiteId : false);
		$isExtranet = ($extranetSiteId && $project->get('IS_EXTRANET') === 'Y');
		$isCollab = $project->getType() === Type::Collab->value;

		$item = new Item([
			'id' => $project->getId(),
			'entityId' => static::ENTITY_ID,
			'entityType' => (
			$isCollab
				? 'collab'
				: ($isExtranet ? 'extranet' : 'project')
			),
			'title' => $project->getName(),
			'avatar' => self::makeProjectAvatar($project),
			'customData' => [
				'landing' => $project->getLanding(),
				'active' => $project->getActive(),
				'visible' => $project->getVisible(),
				'closed' => $project->getClosed(),
				'open' => $project->getOpened(),
				'project' => $project->getProject(),
				'isCollab' => $isCollab,
				'isExtranet' => $isExtranet,
				'datePlan' => [
					'dateStart' => $project->getProjectDateStart()?->getTimestamp(),
					'dateFinish' => $project->getProjectDateFinish()?->getTimestamp(),
				],
			],
		]);

		if ($options['shouldSelectDialogId'] ?? false)
		{
			$item->getCustomData()->set('dialogId', static::$groupDialogIds[$project->getId()]);
		}

		if (!empty($options['tabs']))
		{
			$item->addTab($options['tabs']);
		}

		if (isset($options['addProjectMetaUsers']) && $options['addProjectMetaUsers'] === true)
		{
			$item->addChild(new Item([
				'id' => $project->getId() . ':A',
				'title' => $project->getName() . '. ' . Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECTS_METAUSER_OWNER'),
				'entityId' => static::ENTITY_ID,
				'entityType' => 'project_metauser',
				'nodeOptions' => [
					'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECTS_METAUSER_OWNER'),
					'renderMode' => 'override',
				],
				'customData' => [
					'projectId' => $project->getId(),
					'metauser' => 'owner'
				]
			]));

			$item->addChild(new Item([
				'id' => $project->getId() . ':E',
				'title' => $project->getName() . '. ' . Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECTS_METAUSER_MODERATOR'),
				'entityType' => 'project_metauser',
				'entityId' => static::ENTITY_ID,
				'nodeOptions' => [
					'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECTS_METAUSER_MODERATOR'),
					'renderMode' => 'override',
				],
				'customData' => [
					'projectId' => $project->getId(),
					'metauser' => 'moderator'
				]
			]));

			$item->addChild(new Item([
				'id' => $project->getId() . ':K',
				'title' => $project->getName() . '. ' . Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECTS_METAUSER_ALL'),
				'entityId' => static::ENTITY_ID,
				'entityType' => 'project_metauser',
				'nodeOptions' => [
					'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECTS_METAUSER_ALL'),
					'renderMode' => 'override',
				],
				'customData' => [
					'projectId' => $project->getId(),
					'metauser' => 'all'
				]
			]));
		}

		return $item;
	}

	public static function makeProjectAvatar(EO_Workgroup $project): ?string
	{
		if (!empty($project->getImageId()))
		{
			$avatar = \CFile::resizeImageGet(
				$project->getImageId(),
				['width' => 100, 'height' => 100],
				BX_RESIZE_IMAGE_EXACT,
				false
			);

			return !empty($avatar['src']) ? $avatar['src'] : null;
		}

		if (!empty($project->getAvatarType()))
		{
			$url = \Bitrix\Socialnetwork\Helper\Workgroup::getAvatarEntitySelectorUrl($project->getAvatarType());
			return !empty($url) ? $url : null;
		}

		return null;
	}

	public static function getProjectUrl(?int $projectId = null, ?int $currentUserId = null): string
	{
		if (UserProvider::isExtranetUser($currentUserId))
		{
			$extranetSiteId = Option::get('extranet', 'extranet_site');
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
				$projectPage = SITE_DIR.'workgroups/';
			}
		}

		return $projectPage.'group/'.($projectId !== null ? $projectId : '#id#').'/';
	}

	public static function getCreateProjectUrl(?int $currentUserId = null): string
	{
		$userPage =
			UserProvider::isExtranetUser($currentUserId)
				? UserProvider::getExtranetUserUrl($currentUserId)
				: UserProvider::getIntranetUserUrl($currentUserId)
		;

		return $userPage . 'groups/create/';
	}

	public static function canCreateProject(): bool
	{
		return \Bitrix\Socialnetwork\Helper\Workgroup\Access::canCreate();
	}

	private function fillRecentTab(Dialog $dialog, EO_Workgroup_Collection $projects): void
	{
		$recentItems = $dialog->getRecentItems()->getEntityItems(static::ENTITY_ID);

		if (count($recentItems) < $this->options['maxProjectsInRecentTab'])
		{
			$limit = $this->options['maxProjectsInRecentTab'] - count($recentItems);
			$recentGlobalItems = $dialog->getGlobalRecentItems()->getEntityItems(static::ENTITY_ID);
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

			$recentItems = $dialog->getRecentItems()->getEntityItems(static::ENTITY_ID);
		}

		if (count($recentItems) < $this->options['maxProjectsInRecentTab'])
		{
			$recentIds = array_map('intval', array_keys($recentItems));

			$dialog->addRecentItems(
				$this->getProjectItems([
					'!projectId' => $recentIds,
					'viewed' => true,
					'order' => ['VIEWED_PROJECT.DATE_VIEW' => 'desc'],
					'limit' => $this->options['maxProjectsInRecentTab'] - count($recentItems)
				])
			);

			$recentItems = $dialog->getRecentItems()->getEntityItems(static::ENTITY_ID);
		}

		if (count($recentItems) < $this->options['maxProjectsInRecentTab'])
		{
			$limit = $this->options['maxProjectsInRecentTab'] - count($recentItems);
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

				$dialog->getRecentItems()->add(
					new RecentItem([
						'id' => $project->getId(),
						'entityId' => static::ENTITY_ID,
						'loaded' => true,
					])
				);

				$limit--;
			}
		}
	}
}
