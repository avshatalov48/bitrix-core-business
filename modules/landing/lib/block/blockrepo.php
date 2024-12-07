<?php

namespace Bitrix\Landing\Block;

use Bitrix\Landing\Site;
use Bitrix\Landing\Block;
use Bitrix\Landing\Config;
use Bitrix\Landing\File;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Repo;
use Bitrix\Landing\Internals;
use Bitrix\Landing\Site\Type;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/**
 * Load sections and blocks. Manage, filtred.
 */
class BlockRepo
{
	/**
	 * Dir of repository of blocks.
	 */
	public const BLOCKS_DIR = 'blocks';

	/**
	 * Maximum allowed number of favorite blocks
	 */
	public const FAVOURITE_BLOCKS_LIMIT = 5000;

	/**
	 * Maximum allowed number of favorite blocks with preview image
	 */
	public const FAVOURITE_BLOCKS_LIMIT_WITH_PREVIEW = 1000;

	/**
	 * Life time for mark new block.
	 */
	public const NEW_BLOCK_LT = 1209600;//86400 * 14

	/**
	 * Sections with special conditions
	 */
	private const SECTION_LAST = 'last';

	/**
	 * Section or block type with special conditions
	 */
	private const TYPE_STORE = 'STORE';

	/**
	 * If site type not set or incorrect - use default
	 */
	private const SITE_TYPE_DEFAULT = 'PAGE';

	/**
	 * Tag for managed cache.
	 */
	private const BLOCKS_TAG = 'landing_blocks';

	/**
	 * Repo can be filtered by different ways. Filters can be enabled (by default)
	 * or disabled, see methods disableFilter and enableFilter.
	 * Filters will be apply to getRepository() result
	 */
	public const FILTER_DEFAULTS = 'default';
	public const FILTER_SKIP_COMMON_BLOCKS = 'skip_common_blocks';
	public const FILTER_SKIP_SYSTEM_BLOCKS = 'skip_system_blocks';
	public const FILTER_SKIP_HIDDEN_BLOCKS = 'skip_hidden_blocks';
	private const AVAILABLE_FILTERS = [
		self::FILTER_SKIP_COMMON_BLOCKS,
		self::FILTER_SKIP_SYSTEM_BLOCKS,
		self::FILTER_SKIP_HIDDEN_BLOCKS,
	];
	private const DEFAULT_ACTIVE_FILTERS = [
		self::FILTER_SKIP_SYSTEM_BLOCKS,
		self::FILTER_SKIP_HIDDEN_BLOCKS,
	];

	/** @var array active repository filters */
	private array $filters = self::DEFAULT_ACTIVE_FILTERS;

	/**
	 * List of sections with blocks
	 * @var array
	 */
	private array $repository = [];

	private ?string $siteType;

	public function __construct()
	{
		if (Type::getCurrentScopeId())
		{
			$this->siteType = Type::getCurrentScopeId();
		}
		else
		{
			$this->setSiteType(Landing::getSiteType() ?: self::SITE_TYPE_DEFAULT);
		}

		$this->sendSetFiltersEvent();
	}

	/**
	 * Force set site type if it does not match the landing site type
	 * @param string $type
	 * @return BlockRepo
	 */
	public function setSiteType(string $type): static
	{
		$this->siteType = $type;
		if (!in_array($this->siteType, array_keys(Site::getTypes())))
		{
			$this->siteType = self::SITE_TYPE_DEFAULT;
		}

		Type::setScope($this->siteType);

		return $this;
	}

	private function sendSetFiltersEvent(): void
	{
		$event = new Event('landing', 'onBlockRepoSetFilters');
		$event->send();

		$enable = [];
		$disable = [];

		foreach ($event->getResults() as $result)
		{
			if ($result->getType() !== EventResult::ERROR)
			{
				$modified = $result->getModified();

				$enable = array_merge($enable, (array)($modified['ENABLE'] ?? []));
				$disable = array_merge($disable, (array)($modified['DISABLE'] ?? []));
			}
		}

		foreach (array_unique($enable) as $filter)
		{
			$this->enableFilter($filter);
		}
		foreach (array_unique($disable) as $filter)
		{
			$this->disableFilter($filter);
		}
	}

	/**
	 * Activate some filter for getRepository result
	 * @param string $filter - one of available filters (self::AVAILABLE_FILTERS)
	 * @return $this
	 */
	public function enableFilter(string $filter): BlockRepo
	{
		if ($filter === self::FILTER_DEFAULTS)
		{
			$this->filters = self::DEFAULT_ACTIVE_FILTERS;
		}
		elseif (in_array($filter, self::AVAILABLE_FILTERS))
		{
			$this->filters[] = $filter;
			$this->filters = array_unique($this->filters);
		}

		return $this;
	}

	/**
	 * Deactivate some filter for getRepository result
	 * @param string $filter one of available filters (self::AVAILABLE_FILTERS)
	 * @return $this
	 */
	public function disableFilter(string $filter): BlockRepo
	{
		if (in_array($filter, self::AVAILABLE_FILTERS))
		{
			$this->filters = array_filter(
				$this->filters,
				function ($currentFilter) use ($filter) {
					return $currentFilter !== $filter;
				}
			);
		}

		return $this;
	}

	/**
	 * Check is filter active
	 * @param string $filter one of available filters (self::AVAILABLE_FILTERS)
	 * @return bool
	 */
	public function isFilterActive(string $filter): bool
	{
		return in_array($filter, $this->filters);
	}

	/**
	 * Check is block in filtered repo
	 * @param string $code
	 * @return bool
	 */
	public function isBlockInRepo(string $code): bool
	{
		$repo = $this->getRepository();
		foreach ($repo as $sectionCode => $category)
		{
			if ($sectionCode === self::SECTION_LAST)
			{
				continue;
			}

			$blocks = array_keys(($category['items'] ?? []));
			if (in_array($code, $blocks, true))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get blocks from repository.
	 * @return array
	 */
	public function getRepository(): array
	{
		// static cache
		if (!empty($this->repository))
		{
			return $this->getPreparedRepository();
		}

		return $this->loadRepositoryData()->getPreparedRepository();
	}

	private function loadRepositoryData(): static
	{
		// config
		$disableNamespace = (array)Config::get('disable_namespace');
		$enableNamespace = Config::get('enable_namespace');
		$enableNamespace = $enableNamespace ? (array)$enableNamespace : [];

		// system cache begin
		$cache = new \CPHPCache();
		$cacheTime = 86400;
		$cacheStarted = false;
		$cacheId = LANGUAGE_ID;
		$cacheId .= 'user:' . Manager::getUserId();
		$cacheId .= 'version:2';
		$cacheId .= 'disable:' . implode(',', $disableNamespace);
		$cacheId .= 'enable:' . implode(',', $enableNamespace);
		$cacheId .= 'specType:' . ($this->siteType ?? '');
		$cachePath = 'landing/blocks';
		if ($cache->initCache($cacheTime, $cacheId, $cachePath))
		{
			$this->repository = $cache->getVars();
			if (is_array($this->repository) && !empty($this->repository))
			{
				return $this->fillLastUsedBlocks();
			}
		}
		if ($cache->startDataCache($cacheTime, $cacheId, $cachePath))
		{
			$cacheStarted = true;
			if (Cache::isCaching())
			{
				Manager::getCacheManager()->startTagCache($cachePath);
				Manager::getCacheManager()->registerTag(self::BLOCKS_TAG);
			}
		}

		// not in cache - init
		$blocks = [];
		$sections = [];

		// general paths and namespaces
		$paths = self::getGeneralPaths();
		$namespaces = self::getNamespaces();

		//get all blocks with description-file
		sort($namespaces);
		foreach ($namespaces as $subdir)
		{
			foreach ($paths as $path)
			{
				$path = Manager::getDocRoot() . $path;
				if (
					is_dir($path . '/' . $subdir)
					&& ($handle = opendir($path . '/' . $subdir))
				)
				{
					// sections
					$sectionsPath = $path . '/' . $subdir . '/.sections.php';
					if (file_exists($sectionsPath))
					{
						$sections = array_merge(
							$sections,
							(array)include $sectionsPath
						);
					}
					if (!isset($sections[self::SECTION_LAST]))
					{
						$sections[self::SECTION_LAST] = [
							'name' => Loc::getMessage('LD_BLOCK_SECTION_LAST'),
						];
					}
					// blocks
					while ((($entry = readdir($handle)) !== false))
					{
						$descriptionPath = $path . '/' . $subdir . '/' . $entry . '/.description.php';
						$previewPathJpg = $path . '/' . $subdir . '/' . $entry . '/' . Block::PREVIEW_FILE_NAME;
						if ($entry !== '.' && $entry !== '..' && file_exists($descriptionPath))
						{
							Loc::loadLanguageFile($descriptionPath);
							$description = include $descriptionPath;
							if (isset($description['block']['name']))
							{
								$previewFileName = Manager::getUrlFromFile(
									\getLocalPath(
										self::BLOCKS_DIR . '/' . $subdir . '/' . $entry . '/' . Block::PREVIEW_FILE_NAME
									)
								);
								$blocks[$entry] = [
									'id' => isset($description['block']['id'])
										? (string)$description['block']['id']
										: null,
									'name' => $description['block']['name'],
									'namespace' => $subdir,
									'new' => self::isNewBlock($entry),
									'version' => $description['block']['version'] ?? null,
									'type' => $description['block']['type'] ?? [],
									'section' => $description['block']['section'] ?? 'other',
									'system' => (bool)($description['block']['system'] ?? false),
									'description' => $description['block']['description'] ?? '',
									'preview' => file_exists($previewPathJpg)
										? $previewFileName
										: '',
									'restricted' => false,
									'repo_id' => false,
									'app_code' => false,
									'only_for_license' => $description['block']['only_for_license'] ?? '',
								];
							}
						}
					}
				}
			}
		}

		// rest repo
		$blocksRepo = Repo::getRepository();
		// get apps by blocks
		$apps = [];
		foreach ($blocksRepo as $block)
		{
			if ($block['app_code'])
			{
				$apps[] = $block['app_code'];
			}
		}
		if ($apps)
		{
			$apps = array_unique($apps);
			$apps = Repo::getAppByCode($apps);
			// mark repo blocks expired
			foreach ($blocksRepo as &$block)
			{
				if (
					$block['app_code']
					&& isset($apps[$block['app_code']])
					&& $apps[$block['app_code']]['PAYMENT_ALLOW'] == 'N'
				)
				{
					$block['app_expired'] = true;
				}
			}
			unset($block);
		}
		$blocks += $blocksRepo;

		// favorites block
		$currentUser = Manager::getUserId();
		$favoriteBlocks = [];
		$favoriteMyBlocks = [];
		$res = Internals\BlockTable::getList([
			'select' => [
				'ID', 'CODE', 'FAVORITE_META', 'CREATED_BY_ID',
			],
			'filter' => [
				'LID' => 0,
				'=DELETED' => 'N',
			],
			'order' => [
				'ID' => 'desc',
			],
			'limit' => self::FAVOURITE_BLOCKS_LIMIT,
		]);
		$countFavoriteBlocks = 0;
		while ($row = $res->fetch())
		{
			$countFavoriteBlocks++;
			if (isset($blocks[$row['CODE']]))
			{
				if (!is_array($row['FAVORITE_META']))
				{
					continue;
				}
				$meta = $row['FAVORITE_META'];
				$meta['preview'] = $meta['preview'] ?? 0;
				$meta['favorite'] = true;
				$meta['favoriteMy'] = ((int)$row['CREATED_BY_ID'] === $currentUser);
				if ($meta['preview'] > 0 && $countFavoriteBlocks < self::FAVOURITE_BLOCKS_LIMIT_WITH_PREVIEW)
				{
					$meta['preview'] = File::getFilePath($meta['preview']);
				}
				else
				{
					unset($meta['preview']);
				}
				if (isset($meta['section']))
				{
					$meta['section'] = (array)$meta['section'];
				}

				$item = array_merge(
					$blocks[$row['CODE']],
					$meta
				);
				$code = $row['CODE'] . '@' . $row['ID'];
				if ($item['type'] === 'null')
				{
					$item['type'] = [];
				}

				$meta['favoriteMy']
					? ($favoriteMyBlocks[$code] = $item)
					: ($favoriteBlocks[$code] = $item);
			}
		}
		$blocks = $favoriteMyBlocks + $blocks + $favoriteBlocks;

		// create new section in repo
		$createNewSection = function ($item)
		{
			// todo: filter here?
			return [
				'name' => isset($item['name'])
					? (string)$item['name']
					: (string)$item,
				'meta' => $item['meta'] ?? [],
				'new' => false,
				'type' => $item['type'] ?? null,
				'specialType' => $item['specialType'] ?? null,
				'separator' => false,
				'app_code' => false,
				'items' => [],
			];
		};

		// set by sections
		$createdSects = [];
		foreach ($sections as $code => $item)
		{
			$title = $item['name'] ?? $item;
			$title = (string)$title;
			$title = trim($title);
			$this->repository[$code] = $createNewSection($item);
			$createdSects[$title] = $code;
		}
		foreach ($blocks as $key => $block)
		{
			if (!is_array($block['section']))
			{
				$block['section'] = [$block['section']];
			}
			foreach ($block['section'] as $section)
			{
				$section = trim($section);
				if (!$section)
				{
					$section = 'other';
				}
				// adding new sections (actual for repo blocks)
				// todo: not add new section if can't
				if (!isset($this->repository[$section]))
				{
					if (isset($createdSects[$section]))
					{
						$section = $createdSects[$section];
					}
					else
					{
						$this->repository[$section] = $createNewSection($section);
					}
				}
				$this->repository[$section]['items'][$key] = $block;
				if ($block['new'])
				{
					$this->repository[$section]['new'] = true;
				}
			}
		}

		// add apps sections
		if (!empty($blocksRepo) && !empty($apps))
		{
			$this->repository['separator_apps'] = [
				'name' => Loc::getMessage('LANDING_BLOCK_SEPARATOR_PARTNER_2'),
				'separator' => true,
				'items' => [],
			];
			foreach ($apps as $app)
			{
				$this->repository[$app['CODE']] = [
					'name' => $app['APP_NAME'],
					'new' => false,
					'separator' => false,
					'app_code' => $app['CODE'],
					'items' => [],
				];
			}
			// add blocks to the app sections
			foreach ($blocksRepo as $key => $block)
			{
				if ($block['app_code'])
				{
					$this->repository[$block['app_code']]['items'][$key] = $block;
				}
			}
		}

		// sort by id
		foreach ($this->repository as $codeCat => &$blocksCat)
		{
			$codeCat = mb_strtoupper($codeCat);
			uasort($blocksCat['items'], function ($item1, $item2) use ($codeCat)
			{
				if ($item1['repo_id'])
				{
					return 1;
				}
				if ($item2['repo_id'])
				{
					return 0;
				}
				if (
					($item1['id'] && $item2['id'])
					&& mb_strpos($item1['id'], 'BX_' . $codeCat . '_') === 0
					&& mb_strpos($item2['id'], 'BX_' . $codeCat . '_') === 0
				)
				{
					return ($item1['id'] > $item2['id']) ? 1 : -1;
				}

				return 0;
			});
		}
		unset($blocksCat);

		// system cache end
		if ($cacheStarted)
		{
			$cache->endDataCache($this->repository);
			if (Cache::isCaching())
			{
				Manager::getCacheManager()->endTagCache();
			}
		}

		return $this->fillLastUsedBlocks();
	}

	private function fillLastUsedBlocks(): static
	{
		$this->repository[self::SECTION_LAST]['items'] = [];
		$lastUsed = Block::getLastUsed();
		if ($lastUsed)
		{
			foreach ($lastUsed as $code)
			{
				$this->repository[self::SECTION_LAST]['items'][$code] = [];
			}
			foreach ($this->repository as $catCode => &$cat)
			{
				foreach ($cat['items'] as $code => &$block)
				{
					if (
						in_array($code, $lastUsed)
						&& $catCode != self::SECTION_LAST
						&& !empty($block)
					)
					{
						$block['section'][] = self::SECTION_LAST;
						$this->repository[self::SECTION_LAST]['items'][$code] = $block;
					}
				}
				unset($block);
			}
			unset($cat);

			// clear last-section
			foreach ($this->repository[self::SECTION_LAST]['items'] as $code => $block)
			{
				if (!$block)
				{
					unset($this->repository[self::SECTION_LAST]['items'][$code]);
				}
			}
		}

		return $this;
	}

	private function getPreparedRepository(): array
	{
		$prepared = $this->filterRepository($this->repository);

		$event = new Event('landing', 'onBlockGetRepository', [
			'blocks' => $prepared,
		]);
		$event->send();
		foreach ($event->getResults() as $result)
		{
			if ($result->getResultType() != EventResult::ERROR)
			{
				if (($modified = $result->getModified()))
				{
					if (isset($modified['blocks']))
					{
						$prepared = array_merge($prepared, $modified['blocks']);
					}
				}
			}
		}

		return $prepared;
	}

	/**
	 * Remove unnecessary sections and blocks
	 * @param array $repository
	 * @return array
	 */
	private function filterRepository(array $repository): array
	{
		/**
		 * Array with available types
		 * Empty array - available all types
		 * Null - non available (hidden block)
		 * @param string|array $item
		 * @return array|null
		 */
		$prepareType = function (string|array $item): ?array
		{
			$type = (array)$item;
			$type = array_map('strtoupper', $type);
			if (in_array('PAGE', $type))
			{
				$type[] = 'SMN';
			}
			if (
				in_array('NULL', $type)
				|| in_array('', $type)
			)
			{
				return null;
			}

			return $type;
		};

		$filtered = [];

		$isStoreEnabled = Manager::isStoreEnabled();
		$version = Manager::getVersion();
		$license = Loader::includeModule('bitrix24') ? \CBitrix24::getLicenseType() : null;

		foreach ($repository as $sectionCode => $section)
		{
			$sectionTypes = $prepareType($section['type'] ?? []);

			if (
				$this->isFilterActive(self::FILTER_SKIP_COMMON_BLOCKS)
				&& empty($sectionTypes)
				&& $sectionCode !== self::SECTION_LAST
			)
			{
				continue;
			}

			if (
				$this->isFilterActive(self::FILTER_SKIP_HIDDEN_BLOCKS)
				&& $sectionTypes === null
			)
			{
				continue;
			}

			if (
				is_array($sectionTypes)
				&& !empty($sectionTypes)
				&& !in_array($this->siteType, $sectionTypes, true))
			{
				continue;
			}

			if ($sectionTypes === [self::TYPE_STORE] && !$isStoreEnabled)
			{
				continue;
			}

			$filtered[$sectionCode] = $section;
			$filtered[$sectionCode]['items'] = [];

			foreach ($section["items"] ?? [] as $blockCode => $block)
			{
				$blockTypes = $prepareType($block['type'] ?? []);

				if (
					$this->isFilterActive(self::FILTER_SKIP_COMMON_BLOCKS)
					&& empty($blockTypes)
				)
				{
					continue;
				}

				if (
					$this->isFilterActive(self::FILTER_SKIP_HIDDEN_BLOCKS)
					&& $blockTypes === null
				)
				{
					continue;
				}

				if (
					is_array($blockTypes)
					&& !empty($blockTypes)
					&& !in_array($this->siteType, $blockTypes, true)
				)
				{
					continue;
				}

				if (!empty($block['only_for_license']) && $block['only_for_license'] !== $license)
				{
					continue;
				}

				if (
					$this->isFilterActive(self::FILTER_SKIP_SYSTEM_BLOCKS)
					&& isset($block['system'])
					&& $block['system'] === true
				)
				{
					continue;
				}

				$block['requires_updates'] =
					($block['version'] ?? null)
					&& version_compare($version, $block['version']) < 0;

				$filtered[$sectionCode]['items'][$blockCode] = $block;
			}

			if (empty($filtered[$sectionCode]['items']))
			{
				unset($filtered[$sectionCode]);
			}
		}

		return $filtered;
	}

	/**
	 * @return $this
	 */
	public function clearCache(): static
	{
		if (Cache::isCaching())
		{
			Manager::getCacheManager()->clearByTag(self::BLOCKS_TAG);
		}

		return $this;
	}

	/**
	 * Gets general paths, where blocks can be found.
	 * @return ?array
	 */
	public static function getGeneralPaths(): ?array
	{
		static $paths = null;

		if (!$paths)
		{
			$paths = [
				BX_ROOT . '/' . self::BLOCKS_DIR,
				\getLocalPath(self::BLOCKS_DIR),
			];
			if ($paths[0] == $paths[1])
			{
				unset($paths[1]);
			}
		}

		return $paths;
	}

	/**
	 * Gets all available namespaces.
	 * @return array
	 */
	public static function getNamespaces(): array
	{
		static $namespaces = [];

		if ($namespaces)
		{
			return $namespaces;
		}

		$paths = self::getGeneralPaths();
		$disableNamespace = (array)Config::get('disable_namespace');
		$enableNamespace = Config::get('enable_namespace');
		$enableNamespace = $enableNamespace ? (array)$enableNamespace : [];

		$namespaces = [];
		foreach ($paths as $path)
		{
			if ($path !== false)
			{
				$path = Manager::getDocRoot() . $path;
				// read all subdirs ($namespaces) in block dir
				if (($handle = opendir($path)))
				{
					while ((($entry = readdir($handle)) !== false))
					{
						if (!empty($enableNamespace))
						{
							if (in_array($entry, $enableNamespace))
							{
								$namespaces[] = $entry;
							}
						}
						elseif (
							$entry != '.' && $entry != '..'
							&& is_dir($path . '/' . $entry)
							&& !in_array($entry, $disableNamespace)
						)
						{
							$namespaces[] = $entry;
						}
					}
				}
			}
		}
		$namespaces = array_unique($namespaces);

		return $namespaces;
	}

	/**
	 * New or not the block.
	 * @param string $block Block code.
	 * @return boolean
	 */
	protected static function isNewBlock(string $block): bool
	{
		static $newBlocks = null;

		if ($newBlocks === null)
		{
			$newBlocks = unserialize(Manager::getOption('new_blocks'), ['allowed_classes' => false]);
			if (!is_array($newBlocks))
			{
				$newBlocks = [];
			}
			if (
				!isset($newBlocks['date'])
				|| ((time() - $newBlocks['date']) > self::NEW_BLOCK_LT)
			)
			{
				$newBlocks = [];
			}
			if (isset($newBlocks['items']))
			{
				$newBlocks = $newBlocks['items'];
			}
		}

		return in_array($block, $newBlocks);
	}
}
