<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Web\DOM;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Connector;
use \Bitrix\Landing\Controller;
use \Bitrix\Landing\Internals;
use \Bitrix\Landing\Assets;
use \Bitrix\Landing\Block\Cache;
use \Bitrix\Landing\Restriction;
use \Bitrix\Landing\Node\Type as NodeType;
use \Bitrix\Landing\Node;
use \Bitrix\Landing\PublicAction\Utils as UtilsAction;

Loc::loadMessages(__FILE__);

class Block extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Dir of repository of blocks.
	 */
	const BLOCKS_DIR = 'blocks';

	/**
	 * Tag for managed cache.
	 */
	const BLOCKS_TAG = 'landing_blocks';

	/**
	 * Block preview filename.
	 */
	const PREVIEW_FILE_NAME = 'preview.jpg';

	/**
	 * Local css filename.
	 */
	const CSS_FILE_NAME = 'style.css';

	/**
	 * Local js filename.
	 */
	const JS_FILE_NAME = 'script.js';

	/**
	 * Pattern for repo code.
	 */
	const REPO_MASK = '/^repo_([\d]+)$/';

	/**
	 * Life time for mark new block.
	 */
	const NEW_BLOCK_LT = 1209600;//86400 * 14

	/**
	 * Access level: any access denied to all blocks.
	 */
	const ACCESS_A = 'A';

	/**
	 * Access level: access denied.
	 */
	const ACCESS_D = 'D';

	/**
	 * Access level: edit only design.
	 */
	const ACCESS_V = 'V';

	/**
	 * Access level: edit content and design (not delete).
	 */
	const ACCESS_W = 'W';

	/**
	 * Access level: full access.
	 */
	const ACCESS_X = 'X';

	/**
	 * Symbolic code of card.
	 */
	const CARD_SYM_CODE = 'card';

	/**
	 * Symbolic code of preset.
	 */
	const PRESET_SYM_CODE = 'preset';

	/**
	 * Default setting for block wrapper style, if not set manifest[styles][block] section
	 */
	public const DEFAULT_WRAPPER_STYLE = ['block-default'];

	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'BlockTable';

	/**
	 * Id of current block.
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Id of landing.
	 * @var int
	 */
	protected $lid = 0;

	/**
	 * Parent id of block (public version id).
	 * @var int
	 */
	protected $parentId = 0;

	/**
	 * Id of site of landing.
	 * @var int
	 */
	protected $siteId = 0;

	/**
	 * Sort of current block.
	 * @var int
	 */
	protected $sort = 0;

	/**
	 * Is the rest block if > 0.
	 * @var int
	 */
	protected $repoId = 0;

	/**
	 * REST repository some info.
	 * @var array
	 */
	protected $repoInfo = [];

	/**
	 * Code of current block.
	 * @var string
	 */
	protected $code = '';

	/**
	 * Custom anchor of the block.
	 * @var string
	 */
	protected $anchor = '';

	/**
	 * Actually content of current block.
	 * @var string
	 */
	protected $content = '';

	/**
	 * Required user action just added.
	 * @var array
	 */
	protected $runtimeRequiredUserAction = [];

	/**
	 * Access for this block.
	 * @see ACCESS_* constants.
	 * @var string
	 */
	protected $access = 'X';

	/**
	 * Additional data of current block.
	 * @var array
	 */
	protected $metaData = array();

	/**
	 * Additional block assets.
	 * @var array
	 */
	protected $assets = array();

	/**
	 * Active or not current block.
	 * @var boolean
	 */
	protected $active = false;

	/**
	 * Active or not page of current block.
	 * @var boolean
	 */
	protected $landingActive = false;

	/**
	 * Deleted or not current block.
	 * @var boolean
	 */
	protected $deleted = false;

	/**
	 * Current block was designed.
	 * @var boolean
	 */
	protected $designed = false;

	/**
	 * Public or not current block.
	 * @var boolean
	 */
	protected $public = false;

	/**
	 * This block allowed or not by tariff.
	 * @var bool
	 */
	protected $allowedByTariff = true;

	/**
	 * Document root.
	 * @var string
	 */
	protected $docRoot = '';

	/**
	 * Instance of Error.
	 * @var Error
	 */
	protected $error = null;

	/**
	 * Dynamic params.
	 * @var array
	 */
	protected $dynamicParams = [];

	/**
	 * Allowed extensions for developers.
	 * @var array
	 */
	protected $allowedExtensions = [
		'landing_form',
		'landing_carousel',
		'landing_google_maps_new',
		'landing_map',
		'landing_countdown',
		'landing_gallery_cards',
		'landing_chat'
	];

	/**
	 * Constructor.
	 * @param int $id Block id.
	 * @param array $data Data row from BlockTable (by default get from DB).
	 * @param array $params Some additional params.
	 */
	public function __construct($id, $data = [], array $params = [])
	{
		if (empty($data) || !is_array($data))
		{
			$data = parent::getList(array(
				'select' => array(
					'*',
					'LANDING_TITLE' => 'LANDING.TITLE',
					'LANDING_ACTIVE' => 'LANDING.ACTIVE',
					'LANDING_TPL_CODE' => 'LANDING.TPL_CODE',
					'SITE_TPL_CODE' => 'LANDING.SITE.TPL_CODE',
					'SITE_TYPE' => 'LANDING.SITE.TYPE',
					'SITE_ID' => 'LANDING.SITE_ID'
				),
				'filter' => array(
					'ID' => (int)$id
				)
			))->fetch();
			if (!$data)
			{
				$id = 0;
			}
		}

		// if content is empty, fill from repository
		if (!isset($data['CONTENT']) || trim($data['CONTENT']) == '')
		{
			$data['CONTENT'] = '';
		}

		$this->id = intval($id);
		$this->lid = isset($data['LID']) ? intval($data['LID']) : 0;
		$this->parentId = isset($data['PARENT_ID']) ? intval($data['PARENT_ID']) : 0;
		$this->siteId = isset($data['SITE_ID']) ? intval($data['SITE_ID']) : 0;
		$this->sort = isset($data['SORT']) ? intval($data['SORT']) : '';
		$this->code = isset($data['CODE']) ? trim($data['CODE']) : '';
		$this->anchor = isset($data['ANCHOR']) ? trim($data['ANCHOR']) : '';
		$this->active = isset($data['ACTIVE']) && $data['ACTIVE'] == 'Y';
		$this->landingActive = isset($data['LANDING_ACTIVE']) && $data['LANDING_ACTIVE'] == 'Y';
		$this->deleted = isset($data['DELETED']) && $data['DELETED'] == 'Y';
		$this->designed = isset($data['DESIGNED']) && $data['DESIGNED'] == 'Y';
		$this->public = isset($data['PUBLIC']) && $data['PUBLIC'] == 'Y';
		$this->content = (!$this->deleted && isset($data['CONTENT'])) ? trim($data['CONTENT']) : '';

		// access
		if (isset($data['ACCESS']))
		{
			$this->access = $data['ACCESS'];
		}

		// assets
		if (isset($data['ASSETS']))
		{
			$this->assets = $data['ASSETS'];
		}

		// fill meta data
		$keys = [
			'LID', 'FAVORITE_META', 'CREATED_BY_ID', 'DATE_CREATE',
			'MODIFIED_BY_ID', 'DATE_MODIFY', 'SITE_TYPE'
		];
		foreach ($keys as $key)
		{
			if (isset($data[$key]))
			{
				$this->metaData[$key] = $data[$key];
			}
		}
		$this->metaData['LANDING_TITLE'] = isset($data['LANDING_TITLE']) ? $data['LANDING_TITLE'] : '';
		$this->metaData['LANDING_TPL_CODE'] = isset($data['LANDING_TPL_CODE']) ? $data['LANDING_TPL_CODE'] : '';
		$this->metaData['SITE_TPL_CODE'] = isset($data['SITE_TPL_CODE']) ? $data['SITE_TPL_CODE'] : '';
		$this->metaData['XML_ID'] = isset($data['XML_ID']) ? $data['XML_ID'] : '';
		$this->metaData['DESIGNER_MODE'] = isset($params['designer_mode']) && $params['designer_mode'] === true;

		// other data
		if (preg_match(self::REPO_MASK, $this->code, $matches))
		{
			$this->repoId = $matches[1];
		}
		if (!$this->content && !$this->deleted)
		{
			$this->content = self::getContentFromRepository($this->code);
		}
		$this->error = new Error;
		$this->docRoot = Manager::getDocRoot();

		// dynamic params
		if (isset($data['SOURCE_PARAMS']))
		{
			$this->dynamicParams = (array)$data['SOURCE_PARAMS'];
		}
	}

	/**
	 * Fill landing with blocks.
	 * @param Landing $landing Landing instance.
	 * @param int $limit Limit count for blocks.
	 * @param array $params Additional params.
	 * @return boolean
	 */
	public static function fillLanding(Landing $landing, $limit = 0, array $params = array())
	{
		if ($landing->exist())
		{
			$editMode = $landing->getEditMode() || $landing->getPreviewMode();
			$repo = array();
			$blocks = array();
			// get all blocks by filter
			$filter = array(
				'LID' => $landing->getId(),
				'=PUBLIC' => $editMode ? 'N' : 'Y',
				'=DELETED' => (isset($params['deleted']) && $params['deleted'] === true)
					? 'Y'
					: 'N'
			);
			if (isset($params['id']) && $params['id'])
			{
				$filter['ID'] = $params['id'];
			}
			$res = parent::getList(array(
				'select' => array(
					'*',
					'LANDING_ACTIVE' => 'LANDING.ACTIVE',
					'LANDING_TPL_CODE' => 'LANDING.TPL_CODE',
					'SITE_TPL_CODE' => 'LANDING.SITE.TPL_CODE',
					'SITE_TYPE' => 'LANDING.SITE.TYPE',
					'SITE_ID' => 'LANDING.SITE_ID'
				),
				'filter' => $filter,
				'order' => array(
					'SORT' => 'ASC',
					'ID' => 'ASC'
				),
				'limit' => $limit
			));
			while ($row = $res->fetch())
			{
				$blockParams = [];
				if (!$landing->canEdit())
				{
					$row['ACCESS'] = self::ACCESS_A;
				}
				$row['SITE_ID'] = $landing->getSiteId();
				$block = new self(
					$row['ID'],
					$row,
					$blockParams
				);
				if ($block->getRepoId())
				{
					$repo[] = $block->getRepoId();
				}
				$blocks[$row['ID']] = $block;
			}
			unset($row, $res);
			if (!empty($repo))
			{
				$repo = Repo::getAppInfo($repo);
			}
			// add blocks to landing
			foreach ($blocks as $block)
			{
				if (
					isset($repo[$block->getRepoId()]['PAYMENT_ALLOW']) &&
					$repo[$block->getRepoId()]['PAYMENT_ALLOW'] != 'Y'
				)
				{
					$allowedByTariff = false;
				}
				else
				{
					$allowedByTariff = true;
				}
				if ($editMode)
				{
					$block->setAllowedByTariff($allowedByTariff);
					$landing->addBlockToCollection($block);
				}
				elseif ($allowedByTariff)
				{
					$landing->addBlockToCollection($block);
				}
			}
			unset($blocks, $block, $repo);
			return true;
		}

		return false;
	}

	/**
	 * Create copy of blocks for draft version.
	 * @param \Bitrix\Landing\Landing $landing Landing instance.
	 * @return void
	 */
	public static function cloneForEdit(\Bitrix\Landing\Landing $landing)
	{
		if ($landing->exist())
		{
			$clone = true;
			$forClone = array();

			$res = parent::getList(array(
				'select' => array(
					'ID', 'LID', 'CODE', 'SORT', 'ACTIVE',
					'CONTENT', 'PUBLIC', 'ACCESS', 'ANCHOR',
					'DESIGNED'
				),
				'filter' => array(
					'LID' => $landing->getId()
				)
			));
			while ($row = $res->fetch())
			{
				if ($row['PUBLIC'] != 'Y')
				{
					$clone = false;
					break;
				}
				else
				{
					if (!$row['ANCHOR'])
					{
						$row['ANCHOR'] = 'b' . $row['ID'];
					}
					$row['PUBLIC'] = 'N';
					$row['PARENT_ID'] = $row['ID'];
					unset($row['ID']);
					$forClone[] = $row;
				}
			}

			if ($clone)
			{
				foreach ($forClone as $row)
				{
					parent::add($row);
				}
			}
		}
	}

	/**
	 * Publication blocks for landing.
	 * @param \Bitrix\Landing\Landing $landing Landing instance.
	 * @return void
	 */
	public static function publicationBlocks(\Bitrix\Landing\Landing $landing)
	{
		Mutator::blocksPublication($landing);
	}

	/**
	 * Recognize landing id by block id.
	 * @param int|array $id Block id (id array).
	 * @return int|array|false
	 */
	public static function getLandingIdByBlockId($id)
	{
		$data = array();
		$res = parent::getList(array(
			'select' => array(
				'ID', 'LID'
			),
			'filter' => array(
				'ID' => $id
			)
		));
		while ($row = $res->fetch())
		{
			$data[$row['ID']] = $row['LID'];
		}

		if (is_array($id))
		{
			return $data;
		}
		elseif (!empty($data))
		{
			return array_pop($data);
		}

		return false;
	}

	/**
	 * Gets row by block id.
	 * @param int|array $id Block id (id array).
	 * @param array $select Select row.
	 * @deprecated since 18.5.0
	 * @return int|array|false
	 */
	public static function getLandingRowByBlockId($id, array $select = array('ID'))
	{
		return self::getRowByBlockId($id, $select);
	}

	/**
	 * Gets landing row by block id.
	 * @param int|array $id Block id (id array).
	 * @param array $select Select row.
	 * @return int|array|false
	 */
	public static function getRowByBlockId($id, array $select = array('ID'))
	{
		$data = array();
		$res = parent::getList(array(
			'select' => $select,
			'filter' => array(
				'ID' => $id
			)
		));
		while ($row = $res->fetch())
		{
			$data[$row['ID']] = $row;
		}

		if (is_array($id))
		{
			return $data;
		}
		elseif (!empty($data))
		{
			return array_pop($data);
		}

		return false;
	}

	/**
	 * Returns normalized block data.
	 * @param string $code Block code.
	 * @return array|null
	 */
	protected static function getNormalizedBlock(string $code): ?array
	{
		static $cached = [];

		if (isset($cached[$code]))
		{
			return $cached[$code];
		}

		$codeOriginal = $code;
		[$code, $blockId] = explode('@', $code);
		$filter = [
			'LID' => 0,
			'=DELETED' => 'N',
			'=CODE' => $code
		];
		if ($blockId)
		{
			$filter['ID'] = $blockId;
		}
		$res = Internals\BlockTable::getList([
			'select' => [
				'ID', 'CODE', 'CONTENT', 'SOURCE_PARAMS', 'DESIGNED'
			],
			'filter' => $filter
		]);
		if ($row = $res->fetch())
		{
			$cached[$codeOriginal] = $row;
			$cached[$codeOriginal]['FILES'] = File::getFilesFromBlockContent($row['ID'], $row['CONTENT']);
		}

		return $cached[$codeOriginal] ?? null;
	}

	/**
	 * Get content from repository by code.
	 * @param string $code Block code.
	 * @param string|null $namespace Namespace (optional).
	 * @return string|null
	 */
	public static function getContentFromRepository(string $code, string $namespace = null): ?string
	{
		if (!is_string($code))
		{
			return null;
		}

		if (strpos($code, '@'))
		{
			$normalizedBlock = self::getNormalizedBlock($code);
			return $normalizedBlock['CONTENT'] ?? null;
		}

		$content = null;

		// local repo
		if (preg_match(self::REPO_MASK, $code, $matches))
		{
			$repo = Repo::getById($matches[1])->fetch();
			$content = $repo['CONTENT'];
		}
		// files storage
		elseif ($path = self::getBlockPath($code, $namespace))
		{
			$path = Manager::getDocRoot() . $path . '/block.php';
			if (file_exists($path))
			{
				$content = file_get_contents($path);
				if (preg_match('/MESS\[[^\]]+\]/', $content))
				{
					$mess = Loc::loadLanguageFile($path);
					if ($mess)
					{
						$replace = [];
						foreach ($mess as $key => $title)
						{
							$replace['MESS[' . $key . ']'] = $title;
						}
						$content = str_replace(
							array_keys($replace),
							array_values($replace),
							$content
						);
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Create instance by string code.
	 * @param Landing $landing Landing - owner for new block.
	 * @param string $code Code of block from repository.
	 * @param array $data Additional data array.
	 * @return Block|false
	 */
	public static function createFromRepository(Landing $landing, string $code, array $data = array())
	{
		// get content and manifest
		$filesFromContent = [];
		$sourceParams = [];
		$codeOriginal = null;
		$designed = 'N';
		$content = $data['CONTENT'] ?? self::getContentFromRepository($code);
		if (strpos($code, '@'))
		{
			$codeOriginal = $code;
			$normalizedBlock = self::getNormalizedBlock($code);
			$designed = $normalizedBlock['DESIGNED'] ?? 'N';
			$filesFromContent = $normalizedBlock['FILES'] ?? [];
			$sourceParams = $normalizedBlock['SOURCE_PARAMS'] ?? [];
			[$code, ] = explode('@', $code);
		}
		$manifest = self::getManifestFile($code);
		// version control
		if (
			isset($manifest['block']['version']) &&
			version_compare(Manager::getVersion(), $manifest['block']['version']) < 0
		)
		{
			$landing->getError()->addError(
				'BLOCK_WRONG_VERSION',
				Loc::getMessage('LANDING_BLOCK_WRONG_VERSION')
			);
			return false;
		}
		// check errors
		if (!$landing->exist())
		{
			$landing->getError()->addError(
				'LANDING_NOT_EXIST',
				Loc::getMessage('LANDING_BLOCK_LANDING_NOT_EXIST')
			);
			return false;
		}
		if ($content == '')
		{
			$landing->getError()->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
			return false;
		}
		// add
		$fields = array(
			'LID' => $landing->getId(),
			'CODE' => $code,
			'CODE_ORIGINAL' => $codeOriginal,
			'SOURCE_PARAMS' => $sourceParams,
			'CONTENT' => $content,
			'ACTIVE' => 'Y',
			'DESIGNED' => $designed
		);
		$availableReplace = array(
			'ACTIVE', 'PUBLIC', 'ACCESS', 'SORT',
			'CONTENT', 'ANCHOR', 'SOURCE_PARAMS',
			'INITIATOR_APP_CODE', 'XML_ID',
			'DESIGNED', 'FAVORITE_META'
		);
		foreach ($availableReplace as $replace)
		{
			if (isset($data[$replace]))
			{
				$fields[$replace] = $data[$replace];
			}
		}
		$res = parent::add($fields);
		if ($res->isSuccess())
		{
			$block = new self($res->getId());
			$manifest = $block->getManifest();
			if (!$block->getLocalAnchor())
			{
				$block->setAnchor('b' . $block->getId());
			}
			Assets\PreProcessing::blockAddProcessing($block);
			if (
				isset($manifest['callbacks']['afteradd']) &&
				is_callable($manifest['callbacks']['afteradd'])
			)
			{
				$manifest['callbacks']['afteradd']($block);
			}
			// calling class(es) of block
			foreach ($block->getClass() as $class)
			{
				$classBlock = $block->includeBlockClass($class);
				$classBlock->beforeAdd($block);
			}
			// for set filter
			if ($fields['SOURCE_PARAMS'])
			{
				$block->saveDynamicParams(
					$fields['SOURCE_PARAMS']
				);
			}
			if (isset($manifest['block']['app_code']))
			{
				$block->save([
					'INITIATOR_APP_CODE' => $manifest['block']['app_code']
			 	]);
			}
			else// index search only
			{
				$block->save();
			}
			// copy references to files from content to new block
			foreach ($filesFromContent as $fileId)
			{
				File::addToBlock($block->getId(), $fileId);
			}
			return $block;
		}
		else
		{
			$landing->getError()->addFromResult($res);
			return false;
		}
	}

	/**
	 * New or not the block.
	 * @param string $block Block code.
	 * @return boolean
	 */
	protected static function isNewBlock($block)
	{
		static $newBlocks = null;

		if (!is_string($block))
		{
			return false;
		}

		if ($newBlocks === null)
		{
			$newBlocks = unserialize(Manager::getOption('new_blocks'), ['allowed_classes' => false]);
			if (!is_array($newBlocks))
			{
				$newBlocks = array();
			}
			if (
				!isset($newBlocks['date']) ||
				(
					isset($newBlocks['date']) &&
					((time() - $newBlocks['date']) > self::NEW_BLOCK_LT)
				)
			)
			{
				$newBlocks = array();
			}
			if (isset($newBlocks['items']))
			{
				$newBlocks = $newBlocks['items'];
			}
		}

		return in_array($block, $newBlocks);
	}

	/**
	 * Gets general paths, where blocks can be found.
	 * @return array
	 */
	protected static function getGeneralPaths()
	{
		static $paths = null;

		if (!$paths)
		{
			$paths = [
				BX_ROOT . '/' . self::BLOCKS_DIR,
				\getLocalPath(self::BLOCKS_DIR)
			];
			if ($paths[0] == $paths[1])
			{
				unset($paths[1]);
			}
		}

		return $paths;
	}

	/**
	 * Clear cache repository.
	 * @return void
	 */
	public static function clearRepositoryCache()
	{
		if (Cache::isCaching())
		{
			Manager::getCacheManager()->clearByTag(self::BLOCKS_TAG);
		}
	}

	/**
	 * Gets all available namespaces.
	 * @return array
	 */
	protected static function getNamespaces()
	{
		static $namespaces = [];

		if ($namespaces)
		{
			return $namespaces;
		}

		$paths = self::getGeneralPaths();
		$disableNamespace = (array)Config::get('disable_namespace');
		$enableNamespace = Config::get('enable_namespace');
		$enableNamespace = $enableNamespace ? (array) $enableNamespace : array();

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
						else if (
							$entry != '.' && $entry != '..' &&
							is_dir($path . '/' . $entry) &&
							!in_array($entry, $disableNamespace)
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
	 * Get blocks from repository.
	 * @param bool $withManifest Get repo with manifest files of blocks.
	 * @return array
	 */
	public static function getRepository($withManifest = false)
	{
		static $blocksCats = array();

		// function for prepare return
		$returnFunc = function($blocksCats) use($withManifest)
		{
			$event = new \Bitrix\Main\Event('landing', 'onBlockGetRepository', array(
				'blocks' => $blocksCats,
				'withManifest' => $withManifest
			));
			$event->send();
			foreach ($event->getResults() as $result)
			{
				if ($result->getResultType() != \Bitrix\Main\EventResult::ERROR)
				{
					if (($modified = $result->getModified()))
					{
						if (isset($modified['blocks']))
						{
							$blocksCats = $modified['blocks'];
						}
					}
				}
			}
			return $blocksCats;
		};

		// static cache
		if (!$withManifest && !empty($blocksCats))
		{
			return $returnFunc($blocksCats);
		}

		// local function for fill last used blocks
		$fillLastUsed = function($blocksCats)
		{
			$blocksCats['last']['items'] = array();
			$lastUsed = self::getLastUsed();
			if ($lastUsed)
			{
				foreach ($lastUsed as $code)
				{
					$blocksCats['last']['items'][$code] = array();
				}
				foreach ($blocksCats as $catCode => &$cat)
				{
					foreach ($cat['items'] as $code => &$block)
					{
						if (
							in_array($code, $lastUsed) &&
							$catCode != 'last' &&
							!empty($block)
						)
						{
							$block['section'][] = 'last';
							$blocksCats['last']['items'][$code] = $block;
						}
					}
					unset($block);
				}
				unset($cat);
				// clear last-section
				foreach ($blocksCats['last']['items'] as $code => $block)
				{
					if (!$block)
					{
						unset($blocksCats['last']['items'][$code]);
					}
				}
			}
			return $blocksCats;
		};

		// config
		$disableNamespace = (array)Config::get('disable_namespace');
		$enableNamespace = Config::get('enable_namespace');
		$enableNamespace = $enableNamespace ? (array) $enableNamespace : array();

		// system cache begin
		$cache = new \CPHPCache();
		$cacheTime = 86400;
		$cacheStarted = false;
		$cacheId =  $withManifest ? 'blocks_manifest' : 'blocks';
		$cacheId .= LANGUAGE_ID;
		$cacheId .= 'user:' . Manager::getUserId();
		$cacheId .= 'disable:' . implode(',', $disableNamespace);
		$cacheId .= 'enable:' . implode(',', $enableNamespace);
		$cachePath = 'landing/blocks';
		if ($cache->initCache($cacheTime, $cacheId, $cachePath))
		{
			$blocksCats = $cache->getVars();
			if (is_array($blocksCats) && !empty($blocksCats))
			{
				$blocksCats = $fillLastUsed($blocksCats);
				return $returnFunc($blocksCats);
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
		$blocks = array();
		$sections = array();

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
					is_dir($path . '/' . $subdir) &&
					($handle = opendir($path . '/' . $subdir))
				)
				{
					// sections
					$sectionsPath = $path . '/' . $subdir . '/.sections.php';
					if (file_exists($sectionsPath))
					{
						$sections = array_merge(
							$sections,
							(array) include $sectionsPath
						);
					}
					if (!isset($sections['last']))
					{
						$sections['last'] = [
							'name' => Loc::getMessage('LD_BLOCK_SECTION_LAST')
						];
					}
					// blocks
					while ((($entry = readdir($handle)) !== false))
					{
						$descriptionPath = $path . '/' . $subdir . '/' . $entry . '/.description.php';
						$previewPathJpg = $path . '/' . $subdir . '/' . $entry . '/' . self::PREVIEW_FILE_NAME;
						if ($entry != '.' && $entry != '..' && file_exists($descriptionPath))
						{
							Loc::loadLanguageFile($descriptionPath);
							$description = include $descriptionPath;
							if (isset($description['block']['name']))
							{
								$previewFileName = Manager::getUrlFromFile(
									\getLocalPath(
										self::BLOCKS_DIR . '/' . $subdir . '/' . $entry . '/' . self::PREVIEW_FILE_NAME
									)
								);
								$blocks[$entry] = array(
									'id' => isset($description['block']['id'])
													? (string)$description['block']['id']
													: null,
									'name' => $description['block']['name'],
									'namespace' => $subdir,
									'new' => self::isNewBlock($entry),
									'version' => isset($description['block']['version'])
													? $description['block']['version']
													: null,
									'type' => isset($description['block']['type'])
													? $description['block']['type']
													: array(),
									'section' => isset($description['block']['section'])
													? $description['block']['section']
													: 'other',
									'description' => isset($description['block']['description'])
													? $description['block']['description']
													: '',
									'preview' => file_exists($previewPathJpg)
													? $previewFileName
													: '',
									'restricted' => false,
									'repo_id' => false,
									'app_code' => false,
									'only_for_license' => $description['block']['only_for_license'] ?? '',
								);
								if ($withManifest)
								{
									$blocks[$entry]['manifest'] = self::getManifestFile(
										$subdir . ':' . $entry
									);
									$blocks[$entry]['content'] = self::getContentFromRepository(
										$entry, $subdir
									);
									if (isset($blocks[$entry]['manifest']['block']))
									{
										$blocks[$entry]['manifest']['block']['preview'] = $blocks[$entry]['preview'];
									}
									// local assets to manifest's assets
									if (!isset($blocks[$entry]['manifest']['assets']))
									{
										$blocks[$entry]['manifest']['assets'] = array();
									}
									// if css exists
									if (file_exists($path . '/' . $subdir . '/' . $entry . '/style.min.css'))
									{
										if (!isset($blocks[$entry]['manifest']['assets']['css']))
										{
											$blocks[$entry]['manifest']['assets']['css'] = array();
										}
										$blocks[$entry]['manifest']['assets']['css'][] = Manager::getUrlFromFile(
											\getLocalPath(
												self::BLOCKS_DIR . '/' . $subdir . '/' . $entry . '/style.min.css'
											)
										);
									}
									// if js exists
									if (file_exists($path . '/' . $subdir . '/' . $entry . '/script.min.js' ))
									{
										if (!isset($blocks[$entry]['manifest']['assets']['js']))
										{
											$blocks[$entry]['manifest']['assets']['js'] = array();
										}
										$blocks[$entry]['manifest']['assets']['js'][] = Manager::getUrlFromFile(
											\getLocalPath(
												self::BLOCKS_DIR . '/' . $subdir . '/' . $entry . '/script.min.js'
											)
										);
									}
									if (empty($blocks[$entry]['manifest']['assets']))
									{
										unset($blocks[$entry]['manifest']['assets']);
									}
								}
							}
						}
					}
				}
			}
		}

		// rest repo
		$blocksRepo = Repo::getRepository();
		// get apps by blocks
		$apps = array();
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
					$block['app_code'] &&
					isset($apps[$block['app_code']]) &&
					$apps[$block['app_code']]['PAYMENT_ALLOW'] == 'N'
				)
				{
					$block['app_expired'] = true;
				}
			}
			unset($block);
		}
		$blocks += $blocksRepo;

		$currentUser = Manager::getUserId();
		// favorites block
		$res = Internals\BlockTable::getList([
			'select' => [
				'ID', 'CODE', 'FAVORITE_META', 'CREATED_BY_ID'
			],
			'filter' => [
				'LID' => 0,
				'=DELETED' => 'N'
			],
			'order' => [
				'ID' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
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
				if ($meta['preview'] > 0)
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
				$blocks = [$row['CODE'] . '@' . $row['ID'] => $blocks[$row['CODE']]] + $blocks;
				$blocks[$row['CODE'] . '@' . $row['ID']] = array_merge(
					$blocks[$row['CODE'] . '@' . $row['ID']],
					$meta
				);
			}
		}

		// create new section in repo
		$createNewSection = function($item)
		{
			return array(
				'name' => isset($item['name'])
						? (string) $item['name']
						: (string) $item,
				'new' => false,
				'type' => $item['type'] ?? null,
				'separator' => false,
				'app_code' => false,
				'items' => array()
			);
		};

		// set by sections
		$createdSects = [];
		foreach ($sections as $code => $item)
		{
			$title = $item['name'] ?? $item;
			$title = (string) $title;
			$title = trim($title);
			$blocksCats[$code] = $createNewSection($item);
			$createdSects[$title] = $code;
		}
		foreach ($blocks as $key => $block)
		{
			if (!is_array($block['section']))
			{
				$block['section'] = array($block['section']);
			}
			foreach ($block['section'] as $section)
			{
				$section = trim($section);
				if (!$section)
				{
					$section = 'other';
				}
				// adding new sections (actual for repo blocks)
				if (!isset($blocksCats[$section]))
				{
					if (isset($createdSects[$section]))
					{
						$section = $createdSects[$section];
					}
					else
					{
						$blocksCats[$section] = $createNewSection($section);
					}
				}
				$blocksCats[$section]['items'][$key] = $block;
				if ($block['new'])
				{
					$blocksCats[$section]['new'] = true;
				}
			}
		}

		// add apps sections
		if (!empty($blocksRepo) && !empty($apps))
		{
			$blocksCats['separator_apps'] = array(
				'name' => Loc::getMessage('LANDING_BLOCK_SEPARATOR_PARTNER_2'),
				'separator' => true,
				'items' => array()
			);
			foreach ($apps as $app)
			{
				$blocksCats[$app['CODE']] = array(
					'name' => $app['APP_NAME'],
					'new' => false,
					'separator' => false,
					'app_code' => $app['CODE'],
					'items' => array()
				);
			}
			// add blocks to the app sections
			foreach ($blocksRepo as $key => $block)
			{
				if ($block['app_code'])
				{
					$blocksCats[$block['app_code']]['items'][$key] = $block;
				}
			}
		}

		// sort by id
		foreach ($blocksCats as $codeCat => &$blocksCat)
		{
			$codeCat = mb_strtoupper($codeCat);
			uasort($blocksCat['items'], function($item1, $item2) use($codeCat)
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
					($item1['id'] && $item2['id']) &&
					mb_strpos($item1['id'], 'BX_'.$codeCat.'_') === 0 &&
					mb_strpos($item2['id'], 'BX_'.$codeCat.'_') === 0
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
			$cache->endDataCache($blocksCats);
			if (Cache::isCaching())
			{
				Manager::getCacheManager()->endTagCache();
			}
		}

		$blocksCats = $fillLastUsed($blocksCats);

		return $returnFunc($blocksCats);
	}

	/**
	 * Returns last used blocks by current user.
	 * @param int $count Count of blocks.
	 * @return array
	 */
	public static function getLastUsed(int $count = 15): array
	{
		$blocks = array();

		$res = Internals\BlockLastUsedTable::getList([
			'select' => [
				'CODE'
			],
			'filter' => [
				'USER_ID' => Manager::getUserId()
			],
			'order' => [
				'DATE_CREATE' => 'DESC'
			],
			'limit' => $count
		]);
		while ($row = $res->fetch())
		{
			$blocks[] = $row['CODE'];
		}

		return $blocks;
	}

	/**
	 * Stores block by code as last used.
	 * @param string $blockCode Block code.
	 * @return void
	 */
	public static function markAsUsed(string $blockCode): void
	{
		$res = Internals\BlockLastUsedTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'USER_ID' => Manager::getUserId(),
				'=CODE' => $blockCode
			],
			'limit' => 1
		]);
		if ($row = $res->fetch())
		{
			Internals\BlockLastUsedTable::update($row['ID'], [
				'DATE_CREATE' => new \Bitrix\Main\Type\DateTime
			]);
		}
		else
		{
			Internals\BlockLastUsedTable::add([
				'CODE' => $blockCode,
				'USER_ID' => Manager::getUserId(),
				'DATE_CREATE' => new \Bitrix\Main\Type\DateTime
			]);
		}
	}

	/**
	 * Removes block by code from last used.
	 * @param string $blockCode Block code.
	 * @return void
	 */
	public static function removeAsUsed(string $blockCode): void
	{
		$res = Internals\BlockLastUsedTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=CODE' => $blockCode
			]
		]);
		while ($row = $res->fetch())
		{
			Internals\BlockLastUsedTable::delete($row['ID']);
		}
	}

	/**
	 * Returns blocks style manifests from repository.
	 * @return array
	 */
	public static function getStyle(): array
	{
		return self::getSpecialManifest('style');
	}

	/**
	 * Returns blocks semantic manifests from repository.
	 * @return array
	 */
	public static function getSemantic(): array
	{
		return self::getSpecialManifest('semantic');
	}

	/**
     * Returns blocks style manifest from repository.
     * @return array
	*/
	protected static function getSpecialManifest(string $type): array
	{
		static $style = [];

		if (array_key_exists($type, $style))
		{
			return $style[$type];
		}

		$style[$type] = [];
		$paths = self::getGeneralPaths();

		// read all subdirs ($namespaces) in block dir
		foreach ($paths as $path)
		{
			$path = Manager::getDocRoot() . $path;
			if (($handle = opendir($path)))
			{
				while ((($entry = readdir($handle)) !== false))
				{
					if (
						$entry != '.' && $entry != '..' &&
						is_dir($path . '/' . $entry) &&
						file_exists($path . '/' . $entry . '/.' . $type . '.php')
					)
					{
						$style[$type][$entry] = include $path . '/' . $entry . '/.' . $type . '.php';
						if (!is_array($style[$type][$entry]))
						{
							unset($style[$type][$entry]);
						}
					}
				}
			}
		}

		return $style[$type];
	}

	/**
	 * Get block content array.
	 * @param int $id Block id.
	 * @param boolean $editMode Edit mode if true.
	 * @param array $params Some params.
	 * @return array
	 */
	public static function getBlockContent($id, $editMode = false, array $params = array())
	{
		if (!isset($params['wrapper_show']))
		{
			$params['wrapper_show'] = true;
		}
		if ($editMode)
		{
			$params['force_unactive'] = true;
		}
		$params['skip_system_script'] = true;

		ob_start();
		$id = intval($id);
		$block = new self($id);
		$extContent = '';
		if (($ext = $block->getExt()))
		{
			$extContent = \CUtil::initJSCore($ext, true);
			$extContent = preg_replace(
				'#<script type="text/javascript"(\sdata\-skip\-moving\="true")?>.*?</script>#is',
				'',
				$extContent
			);
		}
		$landing = Landing::createInstance(
			$block->getLandingId(),
			[
				'skip_blocks' => true
			]
		);
		if ($editMode)
		{
			Cache::disableCache();
		}
		$block->view(
			$editMode,
			$landing->exist() ? $landing : null,
			$params
		);
		if ($editMode)
		{
			Cache::enableCache();
		}
		$content = ob_get_contents();
		$content = self::replaceMetaMarkers($content);
		if ($landing->exist() && mb_strpos($content, '#crm') !== false)
		{
			$replace = Connector\Crm::getReplacesForContent($landing->getSiteId(), false);
			$content = str_replace(
				array_keys($replace),
				array_values($replace),
				$content
			);
		}
		ob_end_clean();
		if ($block->exist())
		{
			Manager::getApplication()->restartBuffer();
			$availableJS = !$editMode || !$block->getRepoId();
			$manifest = $block->getManifest();
			if (
				!isset($manifest['requiredUserAction']) &&
				$block->getRuntimeRequiredUserAction()
			)
			{
				$manifest['requiredUserAction'] = $block->getRuntimeRequiredUserAction();
			}
			$return = array(
				'id' => $id,
				'active' => $block->isActive(),
				'access' => $block->getAccess(),
				'anchor' => $block->getLocalAnchor(),
				'php' => mb_strpos($block->getContent(), '<?') !== false,
				'designed' => $block->isDesigned(),
				'repoId' => $block->repoId ? (int)$block->repoId : null,
				'content' => $content,
				'content_ext' => $extContent,
				'css' => $block->getCSS(),
				'js' => $availableJS ? $block->getJS() : array(),
				'manifest' => $manifest,
				'dynamicParams' => $block->dynamicParams
			);
			if (
				$editMode &&
				isset($return['manifest']['requiredUserAction'])
			)
			{
				$return['requiredUserAction'] = $return['manifest']['requiredUserAction'];
			}

			// add ajax initiated assets to output
			$ajaxAssets = self::getAjaxInitiatedAssets();
			$return['js'] = array_merge($return['js'], $ajaxAssets['js']);
			$return['css'] = array_merge($return['css'], $ajaxAssets['css']);
			// todo: what about strings, langs?
			// todo: what about core.js in strings. And etc relative extensions, which already init

			return $return;
		}
		else
		{
			return array();
		}
	}

	/**
	 * Get block anchor.
	 * @param int $id Block id.
	 * @return string
	 */
	public static function getAnchor($id)
	{
		return 'block' . (int)$id;
	}

	/**
	 * Get namespace for block.
	 * @param string $code Code of block.
	 * @return string
	 */
	protected static function getBlockNamespace($code)
	{
		static $paths = array();
		static $namespace = array();

		if (!is_string($code))
		{
			return '';
		}

		$code = trim($code);

		if (isset($paths[$code]))
		{
			return $paths[$code];
		}

		$paths[$code] = '';

		$namespaces = self::getNamespaces();
		$generalPaths = self::getGeneralPaths();

		// get first needed block from end
		foreach (array_reverse($namespaces) as $subdir)
		{
			foreach ($generalPaths as $path)
			{
				$path = Manager::getDocRoot() . $path;
				if (file_exists($path . '/' . $subdir . '/' . $code . '/.description.php'))
				{
					$paths[$code] = $subdir;
					break 2;
				}
			}
		}

		return $paths[$code];
	}

	/**
	 * Get local path for block.
	 * @param string $code Code of block.
	 * @param string $namespace Namespace (optional).
	 * @return string
	 */
	protected static function getBlockPath($code, $namespace = null)
	{
		if (!is_string($code))
		{
			return '';
		}

		if (strpos($code, '@'))
		{
			[$code, ] = explode('@', $code);
		}

		if (!$namespace)
		{
			$namespace = self::getBlockNamespace($code);
		}
		if ($namespace)
		{
			return \getLocalPath(
				self::BLOCKS_DIR . '/' . $namespace . '/' . $code
			);
		}

		return '';
	}

	/**
	 * Exist or not block in current instance.
	 * @return boolean
	 */
	public function exist()
	{
		return $this->id > 0;
	}

	/**
	 * Get id of the block.
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Gets landing id.
	 * @return int
	 */
	public function getLandingId()
	{
		return $this->lid;
	}

	/**
	 * Gets site id (of landing).
	 * @return int
	 */
	public function getSiteId()
	{
		return $this->siteId;
	}

	/**
	 * Get code of the block.
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Get anchor of the block.
	 * @return string
	 */
	public function getLocalAnchor()
	{
		return $this->anchor;
	}

	/**
	 * Get content of the block.
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Marks block as allowed or not by tariff.
	 * @param bool $mark Mark.
	 * @return void
	 */
	public function setAllowedByTariff(bool $mark): void
	{
		$this->allowedByTariff = $mark;
	}

	/**
	 * Reset content of current block.
	 * @return void
	 */
	public function resetContent()
	{
		$data = parent::getList([
			'select' => [
				'CONTENT'
			],
			'filter' => [
				'ID' => $this->id
			]
		])->fetch();
		if ($data)
		{
			$this->content = $data['CONTENT'];
		}
	}

	/**
	 * Active or not the block.
	 * @return boolean
	 */
	public function isActive()
	{
		return $this->active;
	}

	/**
	 * Public or not the block.
	 * @return boolean
	 */
	public function isPublic()
	{
		return $this->public;
	}

	/**
	 * Returns true if block was designed by user.
	 * @return bool
	 */
	public function isDesigned(): bool
	{
		return $this->designed;
	}

	/**
	 * Get current access.
	 * @return string
	 */
	public function getAccess()
	{
		return $this->access;
	}

	/**
	 * Set new access to the block.
	 * @param string $letter Access letter.
	 * @return void
	 */
	public function setAccess($letter)
	{
		if (is_string($letter))
		{
			$this->access = $letter;
		}
	}

	/**
	 * Set active to the block.
	 * @param boolean $active Bool: true or false.
	 * @return boolean
	 */
	public function setActive($active)
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}
		$this->active = (boolean) $active;
		return true;
	}

	/**
	 * Get repo id, if block from repo.
	 * @return int
	 */
	public function getRepoId()
	{
		return $this->repoId;
	}

	/**
	 * Gets site row.
	 * @return array
	 */
	public function getSite()
	{
		static $site = null;

		if (
			$site === null &&
			$this->siteId
		)
		{
			$site = Site::getList(array(
				'filter' => array(
					'ID' => $this->siteId
				)
		 	))->fetch();
		}

		return $site;
	}

	/**
	 * Get preview picture of the block.
	 * @return string
	 */
	public function getPreview()
	{
		$path = self::getBlockPath($this->code);
		if ($path && file_exists($this->docRoot . '/' . $path . '/' . self::PREVIEW_FILE_NAME))
		{
			return $path . '/' . self::PREVIEW_FILE_NAME;
		}
		return '';
	}

	/**
	 * Get error collection
	 * @return \Bitrix\Landing\Error
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Get class handler for type of node.
	 *
	 * @deprecated
	 * @see Node\Type::getClassName
	 *
	 * @param string $type Type.
	 * @return string
	 */
	protected function getTypeClass($type)
	{
		return Node\Type::getClassName($type);
	}

	/**
	 * Returns additional manifest nodes from content.
	 * @return array
	 */
	protected function parseManifest(): array
	{
		static $manifests = [];

		if (!$this->id || !$this->designed)
		{
			return [];
		}
		if (array_key_exists($this->id, $manifests))
		{
			return $manifests[$this->id];
		}

		$manifests[$this->id] = Block\Designer::parseManifest($this->content);

		return $manifests[$this->id];
	}

	/**
	 * Checks that current block are designed and adds new manifest parts.
	 * @param array $manifest Current manifest.
	 * @return array
	 */
	protected function checkDesignedManifest(array $manifest): array
	{
		if (isset($manifest['block']['name']))
		{
			$designerBlockManifest = $this->parseManifest();
			if (!empty($designerBlockManifest['nodes']))
			{
				foreach ($designerBlockManifest['nodes'] as $keyNode => $node)
				{
					if (isset($manifest['nodes'][$keyNode]))
					{
						continue;
					}
					$node['code'] = $keyNode;
					$class = Node\Type::getClassName($node['type']);
					if (isset($node['type']) && class_exists($class))
					{
						$node['handler'] = call_user_func(
							[
								$class,
								'getHandlerJS'
							]
						);
						$manifest['nodes'][$keyNode] = $node;
					}
				}
			}
			if (!empty($designerBlockManifest['style']))
			{
				$manifest['style']['nodes'] = array_merge(
					$designerBlockManifest['style'],
					$manifest['style']['nodes']
				);
			}
		}

		return $manifest;
	}

	/**
	 * Get manifest array from block.
	 * @param bool $extended Get extended manifest.
	 * @param bool $missCache Don't save in static cache.
	 * @param array $params Additional params.
	 * @return array
	 */
	public function getManifest($extended = false, $missCache = false, array $params = array())
	{
		static $manifestStore = array();

		if (
			!$missCache &&
			isset($manifestStore[$this->code])
		)
		{
			if (
				!isset($manifestStore[$this->code]['disableCache']) ||
				$manifestStore[$this->code]['disableCache'] !== true
			)
			{
				return $this->checkDesignedManifest($manifestStore[$this->code]);
			}
		}

		// manifest from market, files, or rest
		if ($this->repoId)
		{
			$manifest = Repo::getBlock($this->repoId);
		}
		else if ($path = self::getBlockPath($this->code))
		{
			//isolate variables from .description.php
			$includeDesc = function($path)
			{
				Loc::loadLanguageFile($path . '/.description.php');
				$manifest = include $path . '/.description.php';
				$manifest['timestamp'] = file_exists($path . '/block.php')
										? filectime($path . '/block.php')
										: time();
				return $manifest;
			};

			$manifest = $includeDesc($this->docRoot . $path);
		}

		// prepare manifest
		if (isset($manifest['block']['name']))
		{
			// prepare by subtype
			if (
				isset($manifest['block']['subtype'])
				&& (
					!isset($params['miss_subtype'])
					|| $params['miss_subtype'] !== true
				)
			)
			{
				$subtypes = $manifest['block']['subtype'];
				if (!is_array($subtypes))
				{
					$subtypes = [$subtypes];
				}

				foreach ($subtypes as $subtype)
				{
					$subtypeClass = '\\Bitrix\\Landing\\Subtype\\';
					$subtypeClass .= $subtype;
					if (class_exists($subtypeClass))
					{
						$manifest = $subtypeClass::prepareManifest(
							$manifest,
							$this,
							isset($manifest['block']['subtype_params'])
								? (array)$manifest['block']['subtype_params']
								: array()
						);
					}
				}
			}
			// set empty array if no exists
			foreach (['cards', 'nodes', 'attrs', 'menu'] as $code)
			{
				if (!isset($manifest[$code]) || !is_array($manifest[$code]))
				{
					$manifest[$code] = array();
				}
			}
			// prepare every node
			foreach ($manifest['nodes'] as $keyNode => &$node)
			{
				if (is_callable($node) && !$this->repoId)
				{
					$node = $node();
				}
				$node['code'] = $keyNode;
				$class = Node\Type::getClassName($node['type']);
				if (isset($node['type']) && class_exists($class))
				{
					$node['handler'] = call_user_func(array(
						$class,
						'getHandlerJS'
					));
					if (method_exists($class, 'prepareManifest'))
					{
						$node = call_user_func_array(array(
							$class,
							'prepareManifest'
						), array(
							$this,
							$node,
							&$manifest
						));
						if (!is_array($node))
						{
							unset($manifest['nodes'][$keyNode]);
						}
					}
				}
				else
				{
					unset($manifest['nodes'][$keyNode]);
				}
			}
			unset($node);
			// and attrs
			foreach ($manifest['attrs'] as $keyNode => &$node)
			{
				if (is_callable($node) && !$this->repoId)
				{
					$node = $node();
				}
			}
			unset($node);
			// callbacks
			if (isset($manifest['callbacks']) && is_array($manifest['callbacks']))
			{
				$callbacks = array();
				foreach ($manifest['callbacks'] as $code => $callback)
				{
					$callbacks[mb_strtolower($code)] = $callback;
				}
				$manifest['callbacks'] = $callbacks;
			}
			// prepare styles
			if (!isset($manifest['namespace']))
			{
				$manifest['namespace'] = $this->getBlockNamespace($this->code);
			}
			if (
				isset($manifest['style'])
				&& !(
					isset($manifest['style']['block'])
					&& isset($manifest['style']['nodes'])
					&& count($manifest['style']) == 2
				)
			)
			{
				$manifest['style'] = [
					'block' => ['type' => self::DEFAULT_WRAPPER_STYLE],
					'nodes' => is_array($manifest['style'])
						? $manifest['style']
						: []
				];
			}
			elseif (
				!isset($manifest['style'])
				|| !is_array($manifest['style'])
			)
			{
				$manifest['style'] = [
					'block' => ['type' => self::DEFAULT_WRAPPER_STYLE],
					'nodes' => []
				];
			}

			// fake nodes for images from style
			$styleNodes = [];
			foreach ($manifest['style']['nodes'] as $selector => $styleNode)
			{
				if (!isset($manifest['nodes'][$selector]))
				{
					$styleNodes[$selector] = is_array($styleNode['type']) ? $styleNode['type'] : [$styleNode['type']];
				}
			}
			$styleNodes['#wrapper'] = is_array($manifest['style']['block']['type'])
				? $manifest['style']['block']['type']
				: [$manifest['style']['block']['type']];

			foreach ($styleNodes as $selector => $type)
			{
				if (!empty(array_intersect($type, Node\StyleImg::STYLES_WITH_IMAGE)))
				{
					$manifest['nodes'][$selector] = [
						'type' => Node\Type::STYLE_IMAGE,
						'code' => $selector,
					];
				}
			}

			// other
			$manifest['code'] = $this->code;
		}
		else
		{
			$manifest = array();
		}

		$manifest['preview'] = $this->getPreview();

		if (!$missCache)
		{
			$manifestStore[$this->code] = $manifest;
		}

		// localization
		if (
			isset($manifest['lang']) &&
			isset($manifest['lang_original']) &&
			is_array($manifest['lang'])
		)
		{
			// detect translated messages
			$lang = null;
			$langPortal = LANGUAGE_ID;
			if (in_array($langPortal, ['ru', 'kz', 'by']))
			{
				$langPortal = 'ru';
			}
			$langArray = $manifest['lang'];
			$langOrig = $manifest['lang_original'];
			if (isset($langArray[$langPortal]))
			{
				$lang = $langArray[$langPortal];
			}
			else if (
				$langOrig != $langPortal &&
				isset($langArray['en'])
			)
			{
				$lang = $langArray['en'];
			}
			// replace all 'name' keys in manifest
			if ($lang)
			{
				$this->localizationManifest(
					$manifest,
					$lang
				);
			}
			unset($manifest['lang']);
		}

		return $this->checkDesignedManifest($manifest);
	}

	/**
	 * Localize manifest.
	 * @param array $manifest Manifest array.
	 * @param array $lang Lang array.
	 * @return void
	 */
	protected function localizationManifest(array &$manifest, array $lang)
	{
		foreach ($manifest as $key => &$value)
		{
			if (is_array($value))
			{
				$this->localizationManifest($value, $lang);
			}
			if (
				$key == 'name' &&
				isset($lang[$value])
			)
			{
				$value = $lang[$value];
			}
		}
	}

	/**
	 * Get manifest array as is from block.
	 * @param string $code Code name, format "namespace:code" or just "code".
	 * @return array
	 */
	public static function getManifestFile($code)
	{
		static $manifests = array();

		if (!is_string($code))
		{
			return [];
		}

		if (isset($manifests[$code]))
		{
			return $manifests[$code];
		}

		$manifests[$code] = array();
		$namespace = null;

		if (mb_strpos($code, ':') !== false)
		{
			[$namespace, $code] = explode(':', $code);
		}

		if ($path = self::getBlockPath($code ,$namespace))
		{
			$docRoot = Manager::getDocRoot();
			Loc::loadLanguageFile($docRoot . $path . '/.description.php');
			$manifests[$code] = include $docRoot . $path . '/.description.php';
		}

		return $manifests[$code];
	}

	/**
	 * Get some assets of block.
	 * @param string $type What return: css, js, ext, class.
	 * @return array
	 */
	public function getAsset($type = null)
	{
		static $asset = array();

		if (!is_string($type))
		{
			return [];
		}

		if (!isset($asset[$this->code]))
		{
			$asset[$this->code] = array(
				'css' => array(),
				'js' => array(),
				'ext' => array(),
				'class' => array()
			);

			// additional asset first
			if ($this->repoId)
			{
				$manifest = Repo::getBlock($this->repoId);
			}
			else if ($path = self::getBlockPath($this->code))
			{
				$manifest = include $this->docRoot . $path . '/.description.php';
				$manifest['timestamp'] = file_exists($this->docRoot . $path . '/.description.php')
										? filectime($this->docRoot . $path . '/.description.php')
										: time();
			}

			if (isset($manifest['block']['namespace']))
			{
				$classFile = self::BLOCKS_DIR;
				$classFile .= '/' . $manifest['block']['namespace'] . '/';
				$classFile .= $this->code . '/class.php';
				$classFile = \getLocalPath($classFile);
				if ($classFile)
				{
					$asset[$this->code]['class'][] = $this->docRoot . $classFile;
				}
			}

			// prepare by subtype
			if (
				isset($manifest['block']['subtype']) &&
				(
					!isset($params['miss_subtype']) ||
					$params['miss_subtype'] !== true
				)
			)
			{
				$subtypes = $manifest['block']['subtype'];
				if (!is_array($subtypes))
				{
					$subtypes = [$subtypes];
				}

				foreach ($subtypes as $subtype)
				{
					$subtypeClass = '\\Bitrix\\Landing\\Subtype\\';
					$subtypeClass .= $subtype;
					if (class_exists($subtypeClass))
					{
						$manifest = $subtypeClass::prepareManifest(
							$manifest,
							$this,
							isset($manifest['block']['subtype_params'])
								? (array)$manifest['block']['subtype_params']
								: array()
						);
					}
				}
			}

			foreach (array_keys($asset[$this->code]) as $ass)
			{
				if (
					isset($manifest['assets'][$ass]) &&
					!empty($manifest['assets'][$ass])
				)
				{
					foreach ($manifest['assets'][$ass] as $file)
					{
						if (!is_string($file))
						{
							continue;
						}
						if ($ass != 'ext')
						{
							$asset[$this->code][$ass][] = trim($file);
						}
						// for rest block allowed only this
						else if (
							!$this->repoId ||
							in_array($file, $this->allowedExtensions)
						)
						{
							$asset[$this->code][$ass][] = trim($file);
						}
					}
					$asset[$this->code][$ass] = array_unique($asset[$this->code][$ass]);
				}
			}

			// next is phis files
			if (isset($path) && $path)
			{
				// base files next
				$file = $path . '/' . ($this->metaData['DESIGNER_MODE'] ? 'design_' : '') . self::CSS_FILE_NAME;
				if (file_exists($this->docRoot . $file))
				{
					$asset[$this->code]['css'][] = $file;
				}
				$file = $path . '/' . self::JS_FILE_NAME;
				if (file_exists($this->docRoot . $file))
				{
					$asset[$this->code]['js'][] = $file;
				}
			}
		}

		$designerBlockManifest = $this->parseManifest();
		if (!empty($designerBlockManifest['assets']))
		{
			foreach ($designerBlockManifest['assets'] as $key => $assets)
			{
				$asset[$this->code][$key] = array_merge($asset[$this->code][$key], $assets);
				$asset[$this->code][$key] = array_unique($asset[$this->code][$key]);
			}
		}

		return $asset[$this->code][$type] ?? $asset[$this->code];
	}

	/**
	 * Get css file path, if exists.
	 * @return array
	 */
	public function getCSS()
	{
		return $this->getAsset('css');
	}

	/**
	 * Get js file path, if exists.
	 * @return array
	 */
	public function getJS()
	{
		return $this->getAsset('js');
	}

	/**
	 * Get extensions.
	 * @return array
	 */
	public function getExt()
	{
		return $this->getAsset('ext');
	}

	/**
	 * Get executable classes.
	 * @return array
	 */
	public function getClass()
	{
		return $this->getAsset('class');
	}

	/**
	 * Include class of block.
	 * @param string $path Path of block class.
	 * @return \Bitrix\Landing\LandingBlock
	 */
	protected function includeBlockClass($path)
	{
		static $classes = [];
		static $calledClasses = [];

		if (!isset($classes[$path]))
		{
			// include class
			$beforeClasses = get_declared_classes();
			$beforeClassesCount = count($beforeClasses);
			include_once($path);
			$afterClasses = get_declared_classes();
			$afterClassesCount = count($afterClasses);

			// ... and detect class name
			for ($i = $beforeClassesCount; $i < $afterClassesCount; $i++)
			{
				if (is_subclass_of($afterClasses[$i], '\\Bitrix\\Landing\\LandingBlock'))
				{
					$classes[$path] = $afterClasses[$i];
				}
			}
		}

		$landingId = $this->getLandingId();
		$landingPath = $path . '@' . $landingId;

		// call init method
		if (!isset($calledClasses[$landingPath]))
		{
			$calledClasses[$landingPath] = new $classes[$path];
			$calledClasses[$landingPath]->init([
				'site_id' => $this->getSiteId(),
				'landing_id' => $this->getLandingId()
			]);
		}

		return $calledClasses[$landingPath];
	}

	/**
	 * Gets message string.
	 * @param array $params Component's params.
	 * @param string $template Template name.
	 * @return string
	 */
	protected static function getMessageBlock($params, $template = '')
	{
		ob_start();
		Manager::getApplication()->includeComponent(
			'bitrix:landing.blocks.message',
			$template,
			$params,
			false
		);
		$blockMesage = ob_get_contents();
		ob_end_clean();

		return $blockMesage;
	}

	/**
	 * Out the block.
	 * @param boolean $edit Out block in edit mode.
	 * @param Landing|null $landing Landing of this block.
	 * @param array $params Some params.
	 * @return void
	 */
	public function view($edit = false, \Bitrix\Landing\Landing $landing = null, array $params = array())
	{
		global $APPLICATION;

		if ($this->dynamicParams)
		{
			$this->setDynamic($edit);
		}

		if (!isset($params['wrapper_show']))
		{
			$params['wrapper_show'] = true;
		}
		if (!isset($params['force_unactive']))
		{
			$params['force_unactive'] = false;
		}
		if (!isset($params['skip_system_script']))
		{
			$params['skip_system_script'] = false;
		}
		if (
			!$edit &&
			$params['wrapper_show'] &&
			!Config::get('public_wrapper_block')
		)
		{
			$params['wrapper_show'] = false;
		}

		if ($this->deleted)
		{
			return;
		}

		if ($edit || $this->active || $params['force_unactive'])
		{
			$assets = Assets\Manager::getInstance();
			if ($css = $this->getCSS())
			{
				$assets->addAsset($css, Assets\Location::LOCATION_TEMPLATE);
			}
			if ($ext = $this->getExt())
			{
				$assets->addAsset($ext, Assets\Location::LOCATION_TEMPLATE);
			}
			if (!$edit || !$this->repoId)
			{
				if ($js = $this->getJS())
				{
					$assets->addAsset($js, Assets\Location::LOCATION_AFTER_TEMPLATE);
				}
			}
			// calling class(es) of block
			foreach ($this->getClass() as $class)
			{
				$classBlock = $this->includeBlockClass($class);
				$classBlock->beforeView($this);
			}
		}

		// get manifest
		if ($edit && !$params['skip_system_script'])
		{
			$manifest = $this->getManifest();
		}

		// develop mode - rebuild and reset content
		if (
			$this->id > 0 &&
			!$params['skip_system_script'] &&
			defined('LANDING_DEVELOPER_MODE') &&
			LANDING_DEVELOPER_MODE === true
		)
		{
			if (!isset($manifest))
			{
				$manifest = $this->getManifest();
			}
			if (isset($this->metaData['DATE_MODIFY']))
			{
				$modifyTime = $this->metaData['DATE_MODIFY']->getTimeStamp();
			}
			else
			{
				$modifyTime = 0;
			}
			if ($modifyTime < $manifest['timestamp'])
			{
				$count = 0;
				$limit = 1;
				Update\Block::executeStep([
  					'ID' => $this->id
	  			], $count, $limit, $paramsUpdater = []);
				$this->resetContent();
				$this->content = $this->getContent();
			}
		}

		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			if (mb_strpos($this->content, '/upload/') !== false)
			{
				$this->content = preg_replace(
					'#"//[^\'^"]+/upload/#',
					'"/upload/',
					$this->content
				);
			}
			if (Manager::getOption('badpicture2x') == 'Y')
			{
				if (mb_strpos($this->content, 'srcset="') !== false)
				{
					$this->content = str_replace(
						'srcset="',
						'data-srcset-bad="',
						$this->content
					);
				}
				if (mb_strpos($this->content, '2x)') !== false)
				{
					$this->content = preg_replace(
						"#(, url\('[^'^\"]+'\) 2x)#",
						'',
						$this->content
					);
				}
			}
		}

		// show or not a wrapper of block
		if ($params['wrapper_show'])
		{
			if ($this->id > 0)
			{
				if ($edit)
				{
					$anchor = $this->getAnchor($this->id);
				}
				else
				{
					$anchor = $this->anchor
						? \htmlspecialcharsbx($this->anchor)
						: $this->getAnchor($this->id);
				}
			}
			else
			{
				$anchor = 'block' . rand(10000, 100000);
			}
			$classFromCode = 'block-' . $this->code;
			$classFromCode = preg_replace('/([^a-z0-9-])/i', '-', $classFromCode);
			$classFromCode = ' ' . $classFromCode;
			$content = '<div id="' . $anchor . '" ' .
							(($edit && isset($manifest['block']['subtype'])) ? 'data-subtype="' . $manifest['block']['subtype'] . '" ' : '') .
							'class="block-wrapper' .
								(!$this->active ? ' landing-block-deactive' : '') .
								($this->metaData['DESIGNER_MODE'] ? ' landing-designer-block-mode' : '') .
								$classFromCode .
						'">' .
								$this->content .
						'</div>';
		}
		else
		{
			$content = $this->content;
		}

		// @tmp bug with setInnerHTML save result
		$content = preg_replace('/&amp;([^\s]{1})/is', '&$1', $content);
		if ($edit)
		{
			if ($manifest)
			{
				if (
					!isset($manifest['requiredUserAction']) &&
					$this->runtimeRequiredUserAction
				)
				{
					$manifest['requiredUserAction'] = $this->runtimeRequiredUserAction;
				}
				$designerRepository = $this->metaData['DESIGNER_MODE'] ? \Bitrix\Landing\Block\Designer::getRepository() : [];
				$anchor = $this->anchor;
				if (!$anchor)
				{
					$anchor = $this->parentId
						? 'block' . $this->parentId
						: 'b' . $this->id;
				}
				echo '<script type="text/javascript">'
						. 'BX.ready(function(){'
							. 'if (typeof BX.Landing.Block !== "undefined")'
							. '{'
								. 'new BX.Landing.' . ($this->metaData['DESIGNER_MODE'] ? 'DesignerBlock' : 'Block') . '('
									. 'BX("block' . $this->id  . '"), '
									. '{'
										. 'id: ' . $this->id  . ', '
										. 'lid: ' . $this->lid  . ', '
										. 'code: "' . $this->code  . '", '
										. 'repoId: ' . ($this->repoId ? (int)$this->repoId : "null") . ', '
										. 'php: ' . (mb_strpos($content, '<?') !== false ? 'true' : 'false')  . ', '
										. 'designed: ' . ($this->designed ? 'true' : 'false')  . ', '
										. 'active: ' . ($this->active ? 'true' : 'false')  . ', '
										. 'allowedByTariff: ' . ($this->allowedByTariff ? 'true' : 'false')  . ', '
										. 'autoPublicationEnabled: ' . ((\CUserOptions::getOption('landing', 'auto_publication', 'Y') === 'Y') ? 'true' : 'false')  . ', '
										. 'anchor: ' . '"' . \CUtil::jsEscape($anchor) . '"' . ', '
										. 'access: ' . '"' . $this->access . '"' . ', '
					 					. 'dynamicParams: ' . Json::encode($this->dynamicParams) . ','
					 					. ($this->metaData['DESIGNER_MODE'] ? 'repository: ' . Json::encode($designerRepository) . ',' : '')
					 					. 'manifest: ' . Json::encode($manifest)
					 					. (
					 						isset($manifest['requiredUserAction'])
											? ', requiredUserAction: ' . Json::encode($manifest['requiredUserAction'])
											: ''
										)
									. '}'
								. ');'
							. '}'
						. '});'
					. '</script>';
			}
			$content = $this::replaceMetaMarkers($content);

			$event = new \Bitrix\Main\Event('landing', 'onBlockEditView', [
				'block' => $this,
				'outputContent' => $content
			]);
			$event->send();
			foreach ($event->getResults() as $result)
			{
				$content = $result->getParameters();
			}

			if ($this->repoId)
			{
				echo $content;
			}
			else
			{
				try
				{
					eval('?>' . $content . '<?');
				}
				catch (\ParseError $e)
				{
					$errMessage = $this::getMessageBlock([
						'MESSAGE' => Loc::getMessage('LANDING_BLOCK_MESSAGE_ERROR_EVAL')
				 	]);
					if ($params['wrapper_show'])
					{
						echo '<div id="' . $anchor . '" class="block-wrapper' .
							 	(!$this->active ? ' landing-block-deactive' : '') . '">' .
							 		$errMessage .
							 '</div>';
					}
					else
					{
						echo $errMessage;
					}
				}
			}
		}
		elseif ($this->active || $params['force_unactive'])
		{
			// @todo make better
			static $sysPages = null;

			if ($sysPages === null)
			{
				$sysPages = array();
				foreach (Syspage::get($this->siteId) as $syspage)
				{
					$sysPages['@#system_' . $syspage['TYPE'] . '@'] = $syspage['LANDING_ID'];
				}
				// for main page we should get current site main page
				if (!isset($sysPages['@#system_mainpage@']))
				{
					$currentSite = $this->getSite();
					if ($currentSite['LANDING_ID_INDEX'])
					{
						$sysPages['@#system_mainpage@'] = $currentSite['LANDING_ID_INDEX'];
					}
				}
				if (!empty($sysPages))
				{
					$urls = $landing->getPublicUrl($sysPages);
					foreach ($sysPages as $code => $lid)
					{
						if (isset($urls[$lid]))
						{
							$sysPages[$code] = \htmlspecialcharsbx($urls[$lid]);
						}
						else
						{
							unset($sysPages[$code]);
						}
					}
				}
			}

			$sysPages['@' . Connector\Disk::FILE_MASK_HREF . '@i'] = str_replace(
				'#fileId#', '$1',
				Controller\DiskFile::getDownloadLink($this->metaData['SITE_TYPE'], $this->id)
			);

			if (!empty($sysPages))
			{
				$content = preg_replace(
					array_keys($sysPages),
					array_values($sysPages),
					$content
				);
			}

			$event = new \Bitrix\Main\Event('landing', 'onBlockPublicView', [
				'block' => $this,
				'outputContent' => $content
			]);
			$event->send();
			foreach ($event->getResults() as $result)
			{
				$content = $result->getParameters();
			}

			if ($this->repoId)
			{
				echo $content;
			}
			else
			{
				try
				{
					eval('?>' . $content . '<?');
				}
				catch (\ParseError $e)
				{
				}
			}
		}
		Assets\PreProcessing::blockViewProcessing($this, $edit);
	}

	/**
	 * Save assets to the block.
	 * @param array $assets New assets array.
	 * @return void
	 */
	public function saveAssets(array $assets): void
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return;
		}

		foreach (['font', 'icon', 'ext'] as $assetCode)
		{
			if (isset($this->assets[$assetCode]) && !isset($assets[$assetCode]))
			{
				$assets[$assetCode] = $this->assets[$assetCode];
			}
			if (isset($assets[$assetCode]) && !$assets[$assetCode])
			{
				unset($assets[$assetCode]);
			}
		}

		$this->assets = $assets;
	}

	/**
	 * Returns the block assets.
	 * @return array
	 */
	public function getAssets(): array
	{
		return $this->assets;
	}

	/**
	 * Set new content.
	 * @param string $content New content.
	 * @param bool $designed Content was designed.
	 * @return void
	 */
	public function saveContent(string $content, $designed = false): void
	{
		if (!is_string($content))
		{
			return;
		}

		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return;
		}
		if ($designed)
		{
			$this->designed = true;
		}
		$this->content = trim($content);
		$this->getDom(true);
	}

	/**
	 * Save current block in DB.
	 * @param array $additionalFields Additional fields for saving.
	 * @return boolean
	 */
	public function save(array $additionalFields = [])
	{
		if ($this->access == $this::ACCESS_A)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$data = array(
			'SORT' => $this->sort,
			'ACTIVE' => $this->active ? 'Y' : 'N',
			'ANCHOR' => $this->anchor,
			'DELETED' => $this->deleted ? 'Y' : 'N',
			'DESIGNED' => $this->designed ? 'Y' : 'N',
			'ASSETS' => $this->assets ? $this->assets : null
		);
		if ($additionalFields)
		{
			$data = array_merge($data, $additionalFields);
		}
		if ($this->content)
		{
			$data['CONTENT'] = $this->content;
			$data['SEARCH_CONTENT'] = $this->getSearchContent();
		}
		Cache::clear($this->id);
		$res = parent::update($this->id, $data);
		$this->error->addFromResult($res);
		return $res->isSuccess();
	}

	/**
	 * Change landing of current block.
	 * @param int $lid New landing id.
	 * @return boolean
	 *
	 */
	public function changeLanding($lid)
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}
		$res = parent::update($this->id, array(
			'LID' => (int)$lid,
			'PARENT_ID' => null,
			'PUBLIC' => 'N'
		));
		$this->error->addFromResult($res);
		return $res->isSuccess();
	}

	/**
	 * Set meta information for favorite block.
	 * @param array $meta Meta information.
	 * @return bool
	 */
	public function changeFavoriteMeta(array $meta): bool
	{
		$res = parent::update($this->id, [
			'TPL_CODE' => $meta['tpl_code'] ?? null,
			'FAVORITE_META' => $meta
		]);
		$this->error->addFromResult($res);
		return $res->isSuccess();
	}

	/**
	 * Delete current block.
	 * @return boolean
	 */
	public function unlink()
	{
		if ($this->access < $this::ACCESS_X)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$manifest = $this->getManifest();
		$res = self::parentDelete($this->id);
		if (!$res->isSuccess())
		{
			$this->error->addFromResult($res);
		}
		return $res->isSuccess();
	}

	/**
	 * Mark delete or not current block.
	 * @param boolean $mark Mark.
	 * @return void
	 */
	public function markDeleted($mark)
	{
		if ($this->access < $this::ACCESS_X)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return;
		}
		$this->deleted = (boolean) $mark;
	}

	/**
	 * Set new sort to current block.
	 * @param int $sort New sort.
	 * @return void
	 */
	public function setSort($sort)
	{
		$this->sort = $sort;
	}

	/**
	 * Set new anchor to current block.
	 * @param string $anchor New anchor.
	 * @return boolean
	 */
	public function setAnchor($anchor)
	{
		if (!is_string($anchor))
		{
			return false;
		}
		$anchor = trim($anchor);
		$check = !$anchor || preg_match_all('/^[a-z]{1}[a-z0-9\-\_\.\:]+$/i', $anchor);
		if (!$check)
		{
			$this->error->addError(
				'BAD_ANCHOR',
				Loc::getMessage('LANDING_BLOCK_BAD_ANCHOR')
			);
			return false;
		}
		$this->anchor = $anchor;
		return true;
	}

	/**
	 * Save new sort to current block to DB.
	 * @param int $sort New sort.
	 * @return void
	 */
	public function saveSort($sort)
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return;
		}
		$sort = intval($sort);
		$this->sort = $sort;
		Internals\BlockTable::update($this->id, array(
			'SORT' => $sort
		));
	}

	/**
	 * Get sort of current block.
	 * @return int
	 */
	public function getSort()
	{
		return $this->sort;
	}

	/**
	 * Gets dynamic source params.
	 * @param int $id Not current block id.
	 * @return array
	 */
	public function getDynamicParams($id = null)
	{
		$params = [];

		if ($id !== null)
		{
			$id = intval($id);
			$res = parent::getList([
				'select' => [
					'SOURCE_PARAMS'
				],
				'filter' => [
					'ID' => $id
				]
			]);
			if ($row = $res->fetch())
			{
				$params = $row['SOURCE_PARAMS'];
			}
			unset($row, $res);
		}
		else
		{
			$params = $this->dynamicParams;
		}

		return $params;
	}

	/**
	 * @param array $data
	 * @param array $replace
	 * @return array
	 */
	private function dynamicLinkReplacer(array $data, array $replace)
	{
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$data[$key] = $this->dynamicLinkReplacer($value, $replace);
			}
			else
			{
				$data[$key] = str_replace(
					array_keys($replace),
					array_values($replace),
					$data[$key]
				);
			}
		}
		unset($key, $value);

		return $data;
	}

	/**
	 * Save dynamic params for the block.
	 * @param array $sourceParams Source params.
	 * @param array $params Additional params.
	 * @return void
	 */
	public function saveDynamicParams(array $sourceParams = [], array $params = [])
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return;
		}
		// replace old link to new in dynamic manifest
		if (
			isset($params['linkReplace']) &&
			is_array($params['linkReplace'])
		)
		{
			$sourceParams = $this->dynamicLinkReplacer(
				$sourceParams,
				$params['linkReplace']
			);
		}
		// save
		$this->dynamicParams = $sourceParams;
		Internals\BlockTable::update($this->id, [
			'SOURCE_PARAMS' => $sourceParams
		]);
		unset($sourceParams, $params);
	}

	/**
	 * Build dynamic content for the block.
	 * @param bool $edit Edit mode.
	 * @return void
	 */
	protected function setDynamic($edit)
	{
		static $sourceList = null;
		static $isDetailDynamic = null;
		static $dynamicElementId = null;
		static $dynamicFilter = null;

		$data = $this->dynamicParams;
		$caching = false;
		$cache = null;

		// check if is true dynamic
		if (!$this->active || !$this->content)
		{
			return;
		}
		if (!is_array($data) || empty($data))
		{
			return;
		}

		// check feature
		$availableFeature = Restriction\Manager::isAllowed(
			'limit_sites_dynamic_blocks',
			['targetBlockId' => $this->id]
		);
		if (!$availableFeature)
		{
			$this->saveContent($this::getMessageBlock([
				'HEADER' => Loc::getMessage('LANDING_BLOCK_MESSAGE_ERROR_DYNAMIC_LIMIT_TITLE'),
				'MESSAGE' => Restriction\Manager::getSystemErrorMessage('limit_sites_dynamic_blocks'),
				'BUTTON' => Loc::getMessage('LANDING_BLOCK_MESSAGE_ERROR_LIMIT_BUTTON'),
				'LINK' => Manager::BUY_LICENSE_PATH
		  	], 'locked'));
			return;
		}

		// if is detail page
		if ($isDetailDynamic === null)
		{
			$isDetailDynamic = Landing::isDynamicDetailPage();
		}
		if ($dynamicElementId === null)
		{
			$dynamicElementId = Landing::getDynamicElementId();
		}
		if ($dynamicFilter === null)
		{
			$dynamicFilter = Landing::getDynamicFilter();
		}

		if (!$edit && Cache::isCaching())
		{
			$cache = new \CPHPCache();
			$cacheTime = 3600;
			$cacheId = 'block_' . $this->id . '_' . $dynamicElementId . '_';
			$cacheId .= md5(serialize($dynamicFilter));
			$cachePath = '/landing/dynamic/' . $this->id;
			if ($cache->initCache($cacheTime, $cacheId, $cachePath))
			{
				$result = $cache->getVars();
				if ($result['title'])
				{
					Manager::setPageTitle($result['title'], true);
					Landing\Seo::changeValue('title', $result['title']);
				}
				$rememberAccess = $this->access;
				$this->access = $this::ACCESS_W;
				$this->saveContent($result['content']);
				$this->access = $rememberAccess;
				header('X-Bitrix24-Page: dynamic');
				return;
			}
			else
			{
				$caching = true;
				$cache->startDataCache($cacheTime, $cacheId, $cachePath);
				Manager::getCacheManager()->startTagCache($cachePath);
				Cache::register($this->id);
			}
		}

		$updated = false;
		// @todo: remove after refactoring
		$manifest = $this->getManifest();

		// build sources list
		if ($sourceList === null)
		{
			$sourceList = new Source\Selector();
		}

		// @todo: remove after refactoring
		$getDetailPage = function(array $detailPage, $filterId = 0, $elemId = 0)
		{
			$filterId = intval($filterId);
			$elemId = intval($elemId);
			$query = [];

			if (isset($detailPage['query']))
			{
				$query = (array) $detailPage['query'];
				unset($detailPage['query']);
			}

			// normalize the array
			$detailPage = array_merge(
				array_fill_keys(['text', 'href', 'target'], ''),
				$detailPage
			);
			\trimArr($detailPage, true);

			if ($filterId && $elemId && $detailPage['href'])
			{
				$detailPage['href'] = str_replace(
					'#landing',
					'#dynamic',
					$detailPage['href']
				);
				$detailPage['href'] .= '_' . $filterId;
				$detailPage['href'] .= '_' . $elemId;
			}
			else if ($filterId && $elemId)
			{
				$detailPage['href'] = '#';
			}

			if ($detailPage['href'] && $query)
			{
				$detailPage['query'] = http_build_query($query);
			}

			return $detailPage;
		};

		// apply for each selector dynamic data from source
		$disableUpdate = false;
		$pageTitle = '';
		foreach ($data as $cardSelector => $item)
		{
			$update = [];
			$itemDetail = $cardSelector == 'wrapper';
			if (
				!isset($item['source']) ||
				!isset($item['settings']) ||
				!isset($item['references'])
			)
			{
				continue;
			}
			// build start params
			$sourceId = $item['source'];
			$settings = $item['settings'];
			$references = (array)$item['references'];
			$filterId = isset($item['filterId'])
						? intval($item['filterId'])
						: 0;
			$detailPage = isset($settings['detailPage'])
						? (array)$settings['detailPage']
						: [];
			$pagesCount = (
							isset($settings['pagesCount']) &&
							$settings['pagesCount'] > 0
						)
						? (int)$settings['pagesCount']
						: 10;
			$filter = isset($settings['source']['filter'])
						? (array)$settings['source']['filter']
						: [];
			$order = isset($settings['source']['sort'])
						? (array)$settings['source']['sort']
						: [];
			$additional = isset($settings['source']['additional'])
						? (array)$settings['source']['additional']
						: [];
			$stubs = isset($item['stubs'])
						? (array)$item['stubs']
						: [];
			// load external filter, if we on detail
			if (
				$isDetailDynamic && $itemDetail &&
				$dynamicFilter['SOURCE_ID'] == $sourceId
			)
			{
				$filter = $dynamicFilter['FILTER'];
			}
			$sourceParameters = [
				'select' => array_values($references),
				'filter' => $filter,
				'order' => $order,
				'limit' => $pagesCount,
				'additional' => $additional
			];
			// gets list or singleton data
			$sourceData = [];
			$source = $sourceList->getDataLoader(
				$sourceId,
				$sourceParameters,
				[
					'context_filter' => [
						'SITE_ID' => $this->siteId,
						'LANDING_ID' => $this->lid,
						'LANDING_ACTIVE' => $this->landingActive ? 'Y' : ['Y', 'N']
					],
					'cache' => $cache,
					'block' => $this
				]
			);
			if (is_object($source))
			{
				// detail page
				if ($isDetailDynamic && $itemDetail)
				{
					$sourceData = $source->getElementData($dynamicElementId);
					if (!$sourceData)
					{
						$disableUpdate = true;
						continue;
					}
					$pageTitle = $source->getSeoTitle();
					Manager::setPageTitle($pageTitle, true);
					Landing\Seo::changeValue('title', $pageTitle);
				}
				// element list
				else
				{
					$sourceData = $source->getElementListData();
					$pagesCount = max(1, count($sourceData));
				}
			}
			// apply getting data in block
			if (!empty($sourceData) && is_array($sourceData))
			{
				// collect array for update html
				foreach ($references as $selector => $field)
				{
					if (empty($field) || !is_array($field))
					{
						continue;
					}
					if (empty($field['id']))
					{
						continue;
					}
					if (mb_strpos($selector, '@') !== false)
					{
						[$selector,] = explode('@', $selector);
					}
					if (!isset($update[$selector]))
					{
						$update[$selector] = [];
					}
					$fieldCode = $field['id'];
					$fieldType = isset($manifest['nodes'][$selector]['type'])
								? $manifest['nodes'][$selector]['type']
								: NodeType::TEXT;
					// fill ever selector with data, if data exist
					$detailPageData = [];
					foreach ($sourceData as $dataItem)
					{
						// set link to the card
						// @todo: need refactoring
						if (
							$fieldType == NodeType::LINK &&
							isset($field['action'])
						)
						{
							switch ($field['action'])
							{
								case 'detail':
									{
										$detailPage['text'] = isset($field['text'])
															? $field['text']
															: '';
										$update[$selector][] = $detailPageData[$selector][] = $getDetailPage(
											$detailPage,
											$filterId,
											$dataItem['ID']
										);
										break;
									}
								case 'link':
									{
										if (isset($field['link']))
										{
											$field['link'] = (array) $field['link'];
											if (isset($field['text']))
											{
												$field['link']['text'] = $field['text'];
											}
											$update[$selector][] = $getDetailPage(
												$field['link']
											);
										}
										break;
									}
								case 'landing':
									{
										if (isset($dataItem['LINK']))
										{
											$update[$selector][] = $detailPageData[$selector][] = $getDetailPage([
												'text' => isset($field['text'])
														? $field['text']
														: '',
												'href' => $dataItem['LINK'],
												'target' => '_self',
												'query' => isset($dataItem['_GET']) ? $dataItem['_GET'] : []
											]);
										}
									}
							}
						}
						else// if ($fieldType != NodeType::LINK)
						{
							$value = isset($dataItem[$fieldCode])
								? $dataItem[$fieldCode]
								: '';
							$update[$selector][] = $value;
							if ($detailPage)
							{
								$detailPageData[$selector][] = $getDetailPage(
									$detailPage,
									$filterId,
									$dataItem['ID']
								);;
							}
							else if (isset($dataItem['LINK']))
							{
								$detailPageData[$selector][] = $getDetailPage([
									'text' => isset($field['text'])
										? $field['text']
										: '',
									'href' => $dataItem['LINK'],
									'target' => '_self',
									'query' => isset($dataItem['_GET']) ? $dataItem['_GET'] : []
								]);
							}
						}
					}
					// not touch the selector, if there is no data
					if (!$update[$selector])
					{
						unset($update[$selector]);
					}
					// set detail url for nodes
					// @todo: refactor
					else if (
						isset($field['link']) &&
						(
							$fieldType == NodeType::IMAGE ||
							$fieldType == NodeType::TEXT
						)
					)
					{
						if (!isset($detailPageData[$selector]))
						{
							continue;
						}
						foreach ($update[$selector] as $i => &$value)
						{
							if ($fieldType == NodeType::IMAGE)
							{
								$value = (array) $value;
							}
							else
							{
								$value = [
									'text' => (string) $value
								];
							}
							if (
								$detailPageData[$selector][$i] &&
								UtilsAction::isTrue($field['link'])
							)
							{
								$detailPageData[$selector][$i]['enabled'] = true;
							}
							else
							{
								$detailPageData[$selector][$i]['enabled'] = false;
							}
							if ($detailPageData[$selector][$i]['enabled'])
							{
								$value['url'] = $detailPageData[$selector][$i];
							}
						}
						unset($value);
					}
				}
				if (!$itemDetail)
				{
					$rememberAccess = $this->access;
					$this->access = $this::ACCESS_W;
					$this->adjustCards(
						$cardSelector,
						$pagesCount
					);
					$this->access = $rememberAccess;
				}
			}

			// stubs (common content)
			if ($stubs)
			{
				foreach ($stubs as $selector => $stub)
				{
					if (mb_strpos($selector, '@') !== false)
					{
						[$selector,] = explode('@', $selector);
					}
					$update[$selector] = array_fill(0, $pagesCount, $stub);
				}
			}

			// update dynamic
			if ($update)
			{
				$updated = true;
				$rememberAccess = $this->access;
				$this->access = $this::ACCESS_W;
				$this->updateNodes(
					$update,
					[
						'sanitize' => false,
						'skipCheckAffected' => true
					]
				);
				if(!$edit)
				{
					Assets\PreProcessing::blockSetDynamicProcessing($this);
				}
				$this->access = $rememberAccess;

				header('X-Bitrix24-Page: dynamic');
				if ($caching)
				{
					$cache->endDataCache([
						'title' => $pageTitle,
						'content' => $this->content
 					]);
					Manager::getCacheManager()->endTagCache();
				}
			}
			else if (false)
			{
				$this->runtimeRequiredUserAction = [
					'header' => Loc::getMessage('LANDING_BLOCK_MESSAGE_ERROR_NO_DATA_TITLE'),
					'description' => Loc::getMessage('LANDING_BLOCK_MESSAGE_ERROR_NO_DATA_TEXT')
				];
			}
		}

		if (
			$disableUpdate ||
			(!$updated && !Landing::getEditMode())
		)
		{
			if ($cache)
			{
				$cache->abortDataCache();
			}
			$this->deleted = true;
		}
	}

	/**
	 * Make block not dynamic.
	 * @return void
	 */
	public function clearDynamic()
	{
		$this->saveDynamicParams();
	}

	/**
	 * Gets only runtime required actions.
	 * @return array
	 */
	public function getRuntimeRequiredUserAction(): array
	{
		return $this->runtimeRequiredUserAction;
	}

	/**
	 * Set only runtime required actions.
	 * @param array $action
	 */
	public function setRuntimeRequiredUserAction(array $action): void
	{
		$this->runtimeRequiredUserAction = $action;
	}

	/**
	 * Load current content in DOM html structure.
	 * @param bool $clear CLear static cache.
	 * @return DOM\Document
	 */
	public function getDom($clear = false)
	{
		static $doc = array();

		if (
			$clear &&
			isset($doc[$this->id])
		)
		{
			unset($doc[$this->id]);
		}

		if (!isset($doc[$this->id]))
		{
			$doc[$this->id] = new DOM\Document;
			try
			{
				$doc[$this->id]->loadHTML($this->content);
			}
			catch (\Exception $e) {}
		}

		return $doc[$this->id];
	}

	/**
	 * Get metadata of current block.
	 * @return array
	 */
	public function getMeta()
	{
		return $this->metaData;
	}

	/**
	 * Adjust cards count by selector.
	 * @param string $selector Selector.
	 * @param int $count Needed cards count.
	 * @param bool &$changed Changed.
	 * @return boolean Success or failure.
	 */
	public function adjustCards($selector, $count, &$changed = false)
	{
		if (!is_string($selector))
		{
			return false;
		}

		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$manifest = $this->getManifest();
		if (isset($manifest['cards'][$selector]))
		{
			$count = (int)$count;
			$doc = $this->getDom();
			$resultList = $doc->querySelectorAll($selector);
			$resultCount = count($resultList);
			if ($count > $resultCount)
			{
				for ($i = $resultCount; $i < $count; $i++)
				{
					$changed = true;
					$this->cloneCard($selector, $i - 1);
				}
			}
			elseif ($count < $resultCount)
			{
				for ($i = $resultCount; $i > $count; $i--)
				{
					$changed = true;
					$this->removeCard($selector, $i - 1);
				}
			}
			return true;
		}

		$this->error->addError(
			'CARD_NOT_FOUND',
			Loc::getMessage('LANDING_BLOCK_CARD_NOT_FOUND')
		);

		return false;
	}

	/**
	 * Clone one card in block by selector.
	 * @param string $selector Selector.
	 * @param int $position Card position.
	 * @param string $content New content for cloned card.
	 * @return boolean Success or failure.
	 */
	public function cloneCard($selector, $position, $content = '')
	{
		if (!is_string($selector))
		{
			return false;
		}

		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$manifest = $this->getManifest();
		if (isset($manifest['cards'][$selector]))
		{
			$position = intval($position);
			$position = max($position, -1);
			$realPosition = max($position, 0);
			$doc = $this->getDom();
			$resultList = $doc->querySelectorAll($selector);
			if (isset($resultList[$realPosition]))
			{
				$parentNode = $resultList[$realPosition]->getParentNode();
				$refChild = isset($resultList[$position + 1])
					? $resultList[$position + 1]
					: null;
				$haveChild = false;
				if ($refChild)
				{
					foreach ($parentNode->getChildNodes() as $child)
					{
						if ($child === $refChild)
						{
							$haveChild = true;
							break;
						}
					}
				}
				if ($parentNode && (!$refChild || $haveChild))
				{
					// some dance for set new content ;)
					if ($content)
					{
						$tmpCardName = mb_strtolower('tmpcard'.randString(10));
						$newChild = new DOM\Element($tmpCardName);
						$newChild->setOwnerDocument($doc);
						$newChild->setInnerHTML($content);
					}
					else
					{
						$newChild = $resultList[$realPosition];
					}
					$parentNode->insertBefore(
						$newChild,
						$refChild,
						false
					);
					// cleaning and save
					if (isset($tmpCardName))
					{
						$this->saveContent(
							str_replace(
								array('<' . $tmpCardName . '>', '</' . $tmpCardName . '>'),
								'',
								$doc->saveHTML()
							)
						);
					}
					else
					{
						$this->saveContent($doc->saveHTML());
					}
				}
				return true;
			}

		}

		$this->error->addError(
			'CARD_NOT_FOUND',
			Loc::getMessage('LANDING_BLOCK_CARD_NOT_FOUND')
		);
		return false;
	}

	/**
	 * Set card content from block by selector.
	 * @param string $selector Selector.
	 * @param int $position Card position.
	 * @param string $content New content.
	 * @return boolean Success or failure.
	 */
	public function setCardContent($selector, $position, $content)
	{
		if (!is_string($selector))
		{
			return false;
		}

		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$doc = $this->getDom();
		$position = intval($position);
		$resultList = $doc->querySelectorAll($selector);
		if (isset($resultList[$position]))
		{
			$resultList[$position]->setInnerHTML(
				$content
			);
			$this->saveContent($doc->saveHTML());
			return true;
		}

		$this->error->addError(
			'CARD_NOT_FOUND',
			Loc::getMessage('LANDING_BLOCK_CARD_NOT_FOUND')
		);
		return false;
	}

	/**
	 * Gets card content from block by selector.
	 * @param string $selector Selector.
	 * @param int $position Card position.
	 * @return string
	 */
	public function getCardContent($selector, $position)
	{
		if (!is_string($selector))
		{
			return '';
		}

		$doc = $this->getDom();
		$position = intval($position);
		$resultList = $doc->querySelectorAll($selector);
		if (isset($resultList[$position]))
		{
			return $resultList[$position]->getOuterHtml();
		}

		return null;
	}

	/**
	 * Gets count of cards from block by selector.
	 * @param string $selector Selector.
	 * @return int
	 */
	public function getCardCount($selector)
	{
		if (!is_string($selector))
		{
			return 0;
		}

		$doc = $this->getDom();
		$resultList = $doc->querySelectorAll($selector);
		return count($resultList);
	}

	/**
	 * Remove one card from block by selector.
	 * @param string $selector Selector.
	 * @param int $position Card position.
	 * @return boolean Success or failure.
	 */
	public function removeCard($selector, $position)
	{
		if (!is_string($selector))
		{
			return false;
		}

		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$manifest = $this->getManifest();
		$position = intval($position);
		if (isset($manifest['cards'][$selector]))
		{
			$doc = $this->getDom();
			$resultList = $doc->querySelectorAll($selector);
			if (isset($resultList[$position]))
			{
				$resultList[$position]->getParentNode()->removeChild(
					$resultList[$position]
				);
				$this->saveContent($doc->saveHTML());
				Assets\PreProcessing::blockUpdateNodeProcessing($this);
				return true;
			}
		}

		$this->error->addError(
			'CARD_NOT_FOUND',
			Loc::getMessage('LANDING_BLOCK_CARD_NOT_FOUND')
		);
		return false;
	}

	/**
	 * Set new names for nodes of block.
	 * @param array $data Nodes data array.
	 * @return boolean
	 */
	public function changeNodeName($data)
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$doc = $this->getDom();
		$manifest = $this->getManifest();
		// find available nodes by manifest from data
		foreach ($manifest['nodes'] as $selector => $node)
		{
			if (isset($data[$selector]))
			{
				$resultList = $doc->querySelectorAll($selector);

				foreach ($data[$selector] as $pos => $value)
				{
					$value = trim($value['tagName'] ?? $value);
					if (
						preg_match('/^[a-z0-9]+$/i', $value) &&
						isset($resultList[$pos]))
					{
						$resultList[$pos]->setNodeName($value);
					}
				}
			}
		}
		// save rebuild html as text
		$this->saveContent($doc->saveHTML());
		return true;
	}

	/**
	 * Set new content to nodes of block.
	 * @param array $data Nodes data array.
	 * @param array $additional Additional prams for save.
	 * @return boolean
	 */
	public function updateNodes($data, $additional = array())
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$affected = [];
		$doc = $this->getDom();
		$manifest = $this->getManifest();

		// find available nodes by manifest from data
		$manifest['nodes'] = $manifest['nodes'] ?? [];
		foreach ($manifest['nodes'] as $selector => $node)
		{
			if (isset($data[$selector]))
			{
				if (!is_array($data[$selector]))
				{
					$data[$selector] = array(
						$data[$selector]
					);
				}
				// and save content from frontend in DOM by handler-class
				$affected[$selector] = call_user_func_array(array(
					Node\Type::getClassName($node['type']),
					'saveNode'
				), array(
					$this,
					$selector,
					$data[$selector],
					$additional
				));
			}
		}

		// additional work with menu
		if (isset($additional['appendMenu']) && $additional['appendMenu'])
		{
			$export = $this->export();
		}
		else
		{
			$additional['appendMenu'] = false;
		}
		$manifest['menu'] = $manifest['menu'] ?? [];
		foreach ($manifest['menu'] as $selector => $node)
		{
			if (isset($data[$selector]) && is_array($data[$selector]))
			{
				if (isset($data[$selector][0][0]))
				{
					$data[$selector] = array_shift($data[$selector]);
				}
				if ($additional['appendMenu'] && isset($export['menu'][$selector]))
				{
					$data[$selector] = array_merge(
						$export['menu'][$selector],
						$data[$selector]
					);
				}

				$resultList = $doc->querySelectorAll($selector);
				foreach ($resultList as $pos => $resultNode)
				{
					$parentNode = $resultNode->getParentNode();
					if ($parentNode)
					{
						$parentNode->setInnerHtml(
							$this->getMenuHtml(
								$data[$selector],
								$node
							)
						);
					}
					break;// we need only first position
				}
			}
		}

		// save rebuild html as text
		$this->saveContent($doc->saveHTML());

		// check affected content in block's content
		if (!($additional['skipCheckAffected'] ?? false) && Manager::getOption('strict_verification_update') === 'Y')
		{
			$pos = 0;
			$domCorrect = true;
			$content = $this->content;

			foreach ($affected as $selector => $resultItem)
			{
				$selector = trim($selector, '.');

				// prepare content for search
				$content = str_replace('class="', 'class=" ', $content);
				$content = preg_replace_callback(
					'/class="[^"]*[\s]+(' . $selector . ')[\s"]+[^"]*"[^>]*>/s',
					function($match) use(&$pos)
					{
						return str_replace($match[1], $match[1] . '@' . ($pos++), $match[0]);
					},
					$content
				);

				if (is_array($resultItem))
				{
					foreach ($resultItem as $pos => $affectedItem)
					{
						if ($affectedItem['content'] ?? null)
						{
							$affectedItem['content'] = str_replace('/', '\/', $affectedItem['content']);
							am($affectedItem['content']);
							$mask = '/class="[^"]*[\s]+' . $selector . '@' . $pos . '[\s"]+[^"]*"[^>]*>' . $affectedItem['content'] . '<\//s';
							$domCorrect = preg_match_all($mask, $content);
							if (!$domCorrect)
							{
								break 2;
							}
						}

						if ($affectedItem['attrs'] ?? null)
						{
						//	am($affectedItem['attrs']);
						}
					}
				}
			}

			if (!$domCorrect)
			{
				$this->error->addError(
					'INCORRECT_AFFECTED',
					Loc::getMessage('LANDING_BLOCK_INCORRECT_AFFECTED')
				);
				return false;
			}
		}

		Assets\PreProcessing::blockUpdateNodeProcessing($this);

		return true;
	}

	/**
	 * Returns menu html with child submenu.
	 * @param array $data Data array.
	 * @param array $manifestNode Manifest node for current selector.
	 * @param string $level Level (root or children).
	 * @return string
	 */
	protected function getMenuHtml($data, $manifestNode, $level = 'root')
	{
		if (!is_array($data) || !isset($manifestNode[$level]))
		{
			return '';
		}

		$htmlContent = '';
		$rootSelector = $manifestNode[$level];

		if (
			isset($rootSelector['ulClassName']) &&
			isset($rootSelector['liClassName']) &&
			isset($rootSelector['aClassName']) &&
			is_string($rootSelector['ulClassName']) &&
			is_string($rootSelector['liClassName']) &&
			is_string($rootSelector['aClassName'])
		)
		{
			foreach ($data as $menuItem)
			{
				if (
					isset($menuItem['text']) && is_string($menuItem['text']) &&
					isset($menuItem['href']) && is_string($menuItem['href'])
				)
				{
					if ($menuItem['href'] == '#landing0')
					{
						$res = Landing::addByTemplate(
							$this->getSiteId(),
							Assets\PreProcessing\Theme::getNewPageTemplate($this->getSiteId()),
							[
								'TITLE' => $menuItem['text']
							]
						);
						if ($res->isSuccess())
						{
							$menuItem['href'] = '#landing' . $res->getId();
						}
					}
					if (isset($menuItem['target']) && is_string($menuItem['target']))
					{
						$target = $menuItem['target'];
					}
					else
					{
						$target = '_self';
					}
					$htmlContent .= '<li class="' . \htmlspecialcharsbx($rootSelector['liClassName']) . '">';
					$htmlContent .= 	'<a href="' . \htmlspecialcharsbx($menuItem['href']) . '" target="' . $target . '" 
											class="' . \htmlspecialcharsbx($rootSelector['aClassName']) . '">';
					$htmlContent .= 		\htmlspecialcharsbx($menuItem['text']);
					$htmlContent .= 	'</a>';
					if (isset($menuItem['children']))
					{
						$htmlContent .= $this->getMenuHtml(
							$menuItem['children'],
							$manifestNode,
							'children'
						);
					}
					$htmlContent .= '</li>';
				}
			}
			if ($htmlContent)
			{
				$htmlContent = '<ul class="' . \htmlspecialcharsbx($rootSelector['ulClassName']) . '">' .
							   		$htmlContent .
								'</ul>';
			}
			else if ($level == 'root')
			{
				$htmlContent = '<ul class="' . \htmlspecialcharsbx($rootSelector['ulClassName']) . '"></ul>';
			}
		}

		return $htmlContent;
	}

	/**
	 * Change cards multiple.
	 * @param array $data Array with cards.
	 * @return boolean
	 */
	public function updateCards(array $data = array())
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$manifest = $this->getManifest();

		foreach ($data as $selector => $item)
		{
			$cardManifest = $manifest['cards'][$selector];
			// first gets content of current cards
			$cardContent = array();
			$cardCount = $this->getCardCount($selector);
			for ($i = 0; $i < $cardCount; $i++)
			{
				$cardContent[$i] = $this->getCardContent(
					$selector,
					$i
				);
			}
			// then fill all cards by content from existing cards and presets
			if (
				isset($item['source']) &&
				is_array($item['source'])
			)
			{
				$newContent = array();
				foreach ($item['source'] as $i => $source)
				{
					$type = isset($source['type'])
						? $source['type']
						: self::CARD_SYM_CODE;
					$value = isset($source['value'])
						? $source['value']
						: 0;
					// clone card
					if (
						$type == self::CARD_SYM_CODE &&
						isset($cardContent[$value])
					)
					{
						$newContent[$i] = $cardContent[$value];
					}
					// clone preset
					else if (
						$type == 'preset' &&
						isset($cardManifest['presets'][$value]['html'])
					)
					{
						$newContent[$i] = $cardManifest['presets'][$value]['html'];
					}
					else
					{
						$newContent[$i] = '';
					}
				}
				$newContent = trim(implode('', $newContent));
				if ($newContent)
				{
					$dom = $this->getDom();
					$resultList = $dom->querySelectorAll($selector);
					if (isset($resultList[0]))
					{
						$resultList[0]->getParentNode()->setInnerHtml(
							$newContent
						);
					}
					$this->saveContent(
						$dom->saveHTML()
					);
				}
			}
			// and finally update content cards
			if (
				isset($item['values']) &&
				is_array($item['values'])
			)
			{
				$updNodes = array();
				foreach ($item['values'] as $upd)
				{
					if (is_array($upd))
					{
						foreach ($upd as $sel => $content)
						{
							if(mb_strpos($sel, '@'))
							{
								[$sel, $pos] = explode('@', $sel);
							}
							if (!isset($updNodes[$sel]))
							{
								$updNodes[$sel] = array();
							}
							$updNodes[$sel][$pos] = $content;
						}
					}
				}
				if (!empty($updNodes))
				{
					$this->updateNodes($updNodes);
				}
			}
		}

		return true;
	}

	/**
	 * Recursive styles remove in Node.
	 * @param \Bitrix\Main\Web\DOM\Node $node Node for clear.
	 * @param array $styleToRemove Array of styles to remove.
	 * @return \Bitrix\Main\Web\DOM\Node
	 */
	protected function removeStyle(DOM\Node $node, array $styleToRemove)
	{
		foreach ($node->getChildNodesArray() as $nodeChild)
		{
			if ($nodeChild instanceof DOM\Element)
			{
				$styles = DOM\StyleInliner::getStyle($nodeChild, false);
				if (!empty($styles))
				{
					foreach ($styleToRemove as $remove)
					{
						if (!is_array($remove))
						{
							$remove = [$remove => $remove];
						}
						$styles = array_diff_key($styles, $remove);
					}
					DOM\StyleInliner::setStyle($nodeChild, $styles);
				}
			}
			$node = $this->removeStyle($nodeChild, $styleToRemove);
		}

		return $node;
	}

	/**
	 * Set new classes to nodes of block.
	 * @param array $data Classes data array.
	 * @return boolean
	 */
	public function setClasses($data)
	{
		if ($this->access < $this::ACCESS_V)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$doc = $this->getDom();
		$manifest = $this->getManifest();

		// detects position
		$positions = [];
		foreach ((array)$data as $selector => $item)
		{
			if (mb_strpos($selector, '@') !== false)
			{
				[$selector, $position] = explode('@', $selector);
			}
			else
			{
				$position = -1;
			}
			if ($selector === '#wrapper')
			{
				$selector = '#block' . $this->id;
			}
			if ($position >= 0)
			{
				if (!isset($positions[$selector]))
				{
					$positions[$selector] = [];
				}
				$positions[$selector][] = (int)$position;
			}
			$data[$selector] = $item;
		}

		// wrapper (not realy exist)
		$wrapper = '#' . $this->getAnchor($this->id);

		// find available nodes by manifest from data
		$styles = array_merge(
			$manifest['style']['block'],
			$manifest['style']['nodes']
		);
		$styles[$wrapper] = [];
		foreach ($styles as $selector => $node)
		{
			if (isset($data[$selector]))
			{
				// prepare data
				if (!is_array($data[$selector]))
				{
					$data[$selector] = [
						$data[$selector]
					];
				}

				if (!isset($data[$selector]['classList']))
				{
					$data[$selector] = [
						'classList' => $data[$selector]
					];
				}
				if (!isset($data[$selector]['affect']))
				{
					$data[$selector]['affect'] = [];
				}
				// apply classes to the block
				if ($selector === $wrapper)
				{
					$nodesArray = $doc->getChildNodesArray();
					$resultList = [array_pop($nodesArray)];
				}
				// or by selector
				else
				{
					$resultList = $doc->querySelectorAll($selector);
				}
				foreach ($resultList as $pos => $resultNode)
				{
					$relativeSelector = $selector;
					if (isset($positions[$selector]))
					{
						if (!in_array($pos, $positions[$selector], true))
						{
							continue;
						}
						$relativeSelector .= '@' . $pos;
					}

					if ($resultNode)
					{
						if ((int)$resultNode->getNodeType() === $resultNode::ELEMENT_NODE)
						{
							$resultNode->setClassName(
								implode(' ', $data[$relativeSelector]['classList'])
							);
						}

						// affected styles
						if (!empty($data[$relativeSelector]['affect']))
						{
							$this->removeStyle(
								$resultNode,
								$data[$relativeSelector]['affect']
							);
						}

						// inline styles
						if (!empty($data[$relativeSelector]['style']))
						{
							$styles = DOM\StyleInliner::getStyle($resultNode, false);
							DOM\StyleInliner::setStyle($resultNode, array_merge($styles, $data[$relativeSelector]['style']));
						}
						else
						{
							$resultNode->removeAttribute('style');
						}
					}
				}
			}
		}
		// save rebuild html as text
		$this->saveContent($doc->saveHTML());
		Assets\PreProcessing::blockUpdateClassesProcessing($this);
		return true;
	}

	/**
	 * Collects and returns allowed attributes ([selector] => [data-test, data-test2]).
	 * @param string $selector Selector, if attr have't own selector.
	 * @param array &$allowed Array for collecting.
	 * @return void
	 */
	protected static function collectAllowedAttrs(array $mixed, array &$allowed, $selector = null)
	{
		foreach ($mixed as $itemSelector => $item)
		{
			if (!is_string($itemSelector))
			{
				$itemSelector = $selector;
			}
			if (
				isset($item['attrs']) &&
				is_array($item['attrs'])
			)
			{
				self::collectAllowedAttrs($item['attrs'], $allowed, $itemSelector);
			}
			else if (
				isset($item['additional']['attrs']) &&
				is_array($item['additional']['attrs'])
			)
			{
				self::collectAllowedAttrs($item['additional']['attrs'], $allowed, $itemSelector);
			}
			else if (
				isset($item['additional']) &&
				is_array($item['additional'])
			)
			{
				self::collectAllowedAttrs($item['additional'], $allowed, $itemSelector);
			}
			else if (
				isset($item['attribute']) &&
				is_string($item['attribute'])
			)
			{
				if (
					isset($item['selector']) &&
					is_string($item['selector'])
				)
				{
					$itemSelector = trim($item['selector']);
				}
				if ($itemSelector)
				{
					if (!$allowed[$itemSelector])
					{
						$allowed[$itemSelector] = [];
					}
					$allowed[$itemSelector][] = $item['attribute'];
				}
			}
			else if (is_array($item))
			{
				self::collectAllowedAttrs($item, $allowed, $itemSelector);
			}
		}
	}

	/**
	 * Set attributes to nodes of block.
	 * @param array $data Attrs data array.
	 * @return void
	 */
	public function setAttributes($data)
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return;
		}

		$doc = $this->getDom();
		$manifest = $this->getManifest();
		$wrapper = '#' . $this->getAnchor($this->id);

		// collect allowed attrs
		$allowedAttrs = [];
		self::collectAllowedAttrs($manifest['style']['nodes'], $allowedAttrs);
		self::collectAllowedAttrs($manifest['attrs'], $allowedAttrs);
		self::collectAllowedAttrs($manifest['cards'], $allowedAttrs);

		// update attrs
		if ($allowedAttrs)
		{
			// all allowed attrs from manifest with main selector ([selector] => [data-test, data-test2])
			foreach ($allowedAttrs as $selector => $allowed)
			{
				// it's not interesting for us, if there is no new data for this selector
				if (isset($data[$selector]) && is_array($data[$selector]))
				{
					// set attrs to the block
					if ($selector == $wrapper)
					{
						$nodesArray = $doc->getChildNodesArray();
						$resultList = [array_pop($nodesArray)];
					}
					// or by selector
					else
					{
						$resultList = $doc->querySelectorAll($selector);
					}
					// external data for changing in allowed attrs
					foreach ($data[$selector] as $attrKey => $attrData)
					{
						// if key without position (compatibility)
						if (!($attrKey == (string)(int)$attrKey))
						{
							$attrData = [$attrKey => $attrData];
							$attrKey = -1;
						}
						if (!is_array($attrData))
						{
							continue;
						}
						// attrs new data in each selector ([data-test] => value)
						foreach ($attrData as $key => $val)
						{
							if (!in_array($key, $allowed))
							{
								continue;
							}
							// result nodes by main selector
							foreach ($resultList as $pos => $resultNode)
							{
								// if position of node that we try to find
								if ($attrKey == -1 || $attrKey == $pos)
								{
									// update node
									$resultNode->setAttribute(
										\htmlspecialcharsbx($key),
										is_array($val)
											? json_encode($val)
											: $val
									);
								}
							}
						}
					}
				}
			}
		}

		// save result
		$this->saveContent($doc->saveHTML());
	}

	/**
	 * Replace title and breadcrumb marker in the block.
	 * @param string $content Some content.
	 * @return string
	 */
	protected static function replaceMetaMarkers($content)
	{
		if (mb_strpos($content, '#breadcrumb#') !== false)
		{
			ob_start();
			$arResult = array(
				array(
					'LINK' => '#',
					'TITLE' => ''
				),
				array(
					'LINK' => '#',
					'TITLE' => Loc::getMessage('LANDING_BLOCK_BR1')
				),
				array(
					'LINK' => '#',
					'TITLE' => Loc::getMessage('LANDING_BLOCK_BR2')
				),
				array(
					'LINK' => '#',
					'TITLE' => ''
				)
			);
			$tplId = Manager::getTemplateId(
				Manager::getMainSiteId()
			);
			$strChainTemplate = getLocalPath('templates/' . $tplId . '/chain_template.php');
			$strChainTemplate = Manager::getDocRoot() . $strChainTemplate;
			if (file_exists($strChainTemplate))
			{
				echo include $strChainTemplate;
			}
			$breadcrumb = ob_get_contents();
			ob_end_clean();
			$content = str_replace(
				'#breadcrumb#',
				$breadcrumb,
				$content
			);
		}

		if (mb_strpos($content, '#title#') !== false)
		{
			$content = str_replace(
				'#title#',
				Loc::getMessage('LANDING_BLOCK_TITLE'),
				$content
			);
		}

		return $content;
	}

	/**
	 * Delete all blocks from db by codes.
	 * @param array $code Array of codes to delete.
	 * @return void
	 */
	public static function deleteByCode($code)
	{
		if (!is_array($code))
		{
			$code = array($code);
		}
		$res = parent::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'=CODE' => $code
			)
		));
		while ($row = $res->fetch())
		{
			self::parentDelete($row['ID']);
		}
	}

	/**
	 * Delete block row.
	 * @param int $id Block id.
	 * @return \Bitrix\Main\Result
	 */
	private static function parentDelete($id)
	{
		return parent::delete($id);
	}

	/**
	 * Delete all blocks for the landing.
	 * @param int $lid Landing id.
	 * @return void
	 */
	public static function deleteAll($lid)
	{
		$res = parent::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'LID' => (int)$lid
			]
		]);
		while ($row = $res->fetch())
		{
			parent::delete($row['ID']);
		}
	}

	/**
	 * Returns search content for this block.
	 * @return string
	 */
	public function getSearchContent()
	{
		$manifest = $this->getManifest();
		$search = [];

		// get content nodes
		if (isset($manifest['nodes']))
		{
			foreach ($manifest['nodes'] as $selector => $node)
			{
				/** @var Node $class */
				$class = NodeType::getClassName($node['type']);
				if (is_callable([$class, 'getSearchableNode']))
				{
					$search = array_merge($search, $class::getSearchableNode($this, $selector));
				}
			}
		}

		return $search ? implode(' ', $search) : '';
	}

	/**
	 * Export nodes, style, attrs, etc. from block.
	 * @param array $params Some params.
	 * @return array
	 */
	public function export(array $params = [])
	{
		$manifest = $this->getManifest();
		$doc = $this->getDom();

		$cards = [];
		$nodes = [];
		$menu = [];
		$styles = [];
		$allAttrs = [];

		// prepare params
		if (!isset($params['clear_form']))
		{
			$params['clear_form'] = true;
		}

		// get actual cards content
		if (isset($manifest['cards']))
		{
			foreach ($manifest['cards'] as $selector => $node)
			{
				$cards[$selector] = [
					'source' => []
				];
				$resultList = $doc->querySelectorAll($selector);
				$resultListCnt = count($resultList);
				foreach ($resultList as $pos => $result)
				{
					$cards[$selector]['source'][$pos] = array(
						'value' => $result->getAttribute('data-card-preset'),
						'type' => Block::PRESET_SYM_CODE
					);
					if (!$cards[$selector]['source'][$pos]['value'])
					{
						//@tmp for menu first item
						if (mb_strpos($this->getCode(), 'menu') !== false)
						{
							$cards[$selector]['source'][$pos]['value'] = $resultListCnt > 0 ? 1 : 0;
						}
						else
						{
							$cards[$selector]['source'][$pos]['value'] = 0;
						}
						$cards[$selector]['source'][$pos]['type'] = Block::CARD_SYM_CODE;
					}
				}
				// attrs
				if (
					isset($node['additional']['attrs']) &&
					is_array($node['additional']['attrs'])
				)
				{
					foreach ($node['additional']['attrs'] as $attr)
					{
						if (isset($attr['attribute']))
						{
							if (!isset($allAttrs[$selector]))
							{
								$allAttrs[$selector] = [];
							}
							$allAttrs[$selector][] = $attr['attribute'];
						}
					}
				}
			}
		}
		// get content nodes
		if (isset($manifest['nodes']))
		{
			foreach ($manifest['nodes'] as $selector => $node)
			{
				/** @var Node $class */
				$class = NodeType::getClassName($node['type']);
				$nodes[$selector] = $class::getNode($this, $selector);
			}
		}
		if (isset($manifest['menu']))
		{
			// recursive getting menu
			$exportMenu = function($resultList) use(&$exportMenu)
			{
				if(!$resultList)
				{
					return [];
				}

				$menu = [];
				foreach ($resultList->getChildNodesArray() as $pos => $node)
				{
					$menu[$pos] = [];
					if ($node->getNodeName() == 'LI')
					{
						foreach ($node->getChildNodesArray() as $nodeInner)
						{
							if ($nodeInner->getNodeName() == 'A')
							{
								$menu[$pos]['text'] = trim($nodeInner->getTextContent());
								$menu[$pos]['href'] = trim($nodeInner->getAttribute('href'));
								$menu[$pos]['target'] = trim($nodeInner->getAttribute('target'));
							}
							else if ($nodeInner->getNodeName() == 'UL')
							{
								$menu[$pos]['children'] = $exportMenu($nodeInner);
							}
						}
					}
					if (!$menu[$pos])
					{
						unset($menu[$pos]);
					}
				}
				return array_values($menu);
			};
			foreach ($manifest['menu'] as $selector => $menuNode)
			{
				$menu[$selector] = $exportMenu($doc->querySelector($selector));
			}
		}
		// get actual css from nodes
		if (isset($manifest['style']['nodes']))
		{
			foreach ($manifest['style']['nodes'] as $selector => $node)
			{
				$nodeStyle = Node\Style::getStyle($this, $selector);
				if ($nodeStyle)
				{
					$styles[$selector] = $nodeStyle;
				}
				// attrs
				if (
					isset($node['additional']['attrs']) &&
					is_array($node['additional']['attrs'])
				)
				{
					foreach ($node['additional']['attrs'] as $attr)
					{
						if (isset($attr['attribute']))
						{
							if (!isset($allAttrs[$selector]))
							{
								$allAttrs[$selector] = [];
							}
							$allAttrs[$selector][] = $attr['attribute'];
						}
					}
				}
			}
		}
		// get actual css from block wrapper
		if (!empty($manifest['style']['block']))
		{
			$selector = '#wrapper';
			$wrapperStyle = Node\Style::getStyle($this, $selector);
			if ($wrapperStyle)
			{
				$styles[$selector] = $wrapperStyle;
			}
		}
		// attrs
		if (
			isset($manifest['style']['block']['additional']['attrs']) &&
			is_array($manifest['style']['block']['additional']['attrs'])
		)
		{
			$selector = '#wrapper';
			foreach ($manifest['style']['block']['additional']['attrs'] as $attr)
			{
				if (isset($attr['attribute']))
				{
					if (!isset($allAttrs[$selector]))
					{
						$allAttrs[$selector] = [];
					}
					$allAttrs[$selector][] = $attr['attribute'];
				}
			}
		}
		// get actual attrs from nodes
		if (isset($manifest['attrs']))
		{
			foreach ($manifest['attrs'] as $selector => $item)
			{
				if (isset($item['attribute']))
				{
					if (!isset($allAttrs[$selector]))
					{
						$allAttrs[$selector] = [];
					}
					$allAttrs[$selector][] = $item['attribute'];
				}
				else if (is_array($item))
				{
					foreach ($item as $itemAttr)
					{
						if (isset($itemAttr['attribute']))
						{
							if (!isset($allAttrs[$selector]))
							{
								$allAttrs[$selector] = [];
							}
							$allAttrs[$selector][] = $itemAttr['attribute'];
						}
					}
				}
			}
		}
		// remove some system attrs
		if (
			$params['clear_form'] &&
			isset($allAttrs['.bitrix24forms'])
		)
		{
			unset($allAttrs['.bitrix24forms']);
		}
		// collect attrs
		$allAttrsNew = [];
		if (isset($allAttrs['#wrapper']))
		{
			$allAttrsNew['#wrapper'] = [];
			$nodesArray = $doc->getChildNodesArray();
			$resultList = [array_pop($nodesArray)];
			foreach ($resultList as $pos => $result)
			{
				foreach ($allAttrs['#wrapper'] as $attrKey)
				{
					if (!isset($allAttrsNew['#wrapper'][$pos]))
					{
						$allAttrsNew['#wrapper'][$pos] = [];
					}
					$allAttrsNew['#wrapper'][$pos][$attrKey] = $result->getAttribute($attrKey);
				}
			}
			unset($allAttrs['#wrapper']);
		}
		foreach ($allAttrs as $selector => $attr)
		{
			$resultList = $doc->querySelectorAll($selector);
			foreach ($resultList as $pos => $result)
			{
				if (!isset($allAttrsNew[$selector]))
				{
					$allAttrsNew[$selector] = [];
				}
				if (!isset($allAttrsNew[$selector][$pos]))
				{
					$allAttrsNew[$selector][$pos] = [];
				}
				foreach ($attr as $attrKey)
				{
					$allAttrsNew[$selector][$pos][$attrKey] = $result->getAttribute($attrKey);
				}
				unset($attrVal);
			}
		}
		$allAttrs = $allAttrsNew;
		unset($allAttrsNew);

		return [
			'cards' => $cards,
			'nodes' => $nodes,
			'menu' => $menu,
			'style' => $styles,
			'attrs' => $allAttrs,
			'dynamic' => $this->dynamicParams
		];
	}

	/**
	 * Search in blocks.
	 * @param string $query Query string.
	 * @param array $filter Filter array.
	 * @param array $select Select fields.
	 * @param array $group Group fields.
	 * @return array
	 */
	public static function search($query, array $filter = [], array $select = ['LID'], array $group = ['LID'])
	{
		$result = [];

		$filter['*%SEARCH_CONTENT'] = $query;

		$res = Internals\BlockTable::getList([
			'select' => $select,
			'filter' => $filter,
			'group' => $group,
			'order' => ['SORT' => 'desc']
		]);
		while ($row = $res->fetch())
		{
			$result[] = $row;
		}

		return $result;
	}

	/**
	 * Add block row.
	 * @param array $fields Block data.
	 * @return \Bitrix\Main\Result
	 */
	public static function add($fields)
	{
		if (
			!defined('LANDING_MUTATOR_MODE') ||
			LANDING_MUTATOR_MODE !== true
		)
		{
			throw new \Bitrix\Main\SystemException(
				'Disabled for direct access.'
			);
		}
		else
		{
			return parent::add($fields);
		}
	}

	/**
	 * Update block row.
	 * @param int $id Primary key.
	 * @param array $fields Block data.
	 * @return \Bitrix\Main\Result
	 */
	public static function update($id, $fields = array())
	{
		if (
			!defined('LANDING_MUTATOR_MODE') ||
			LANDING_MUTATOR_MODE !== true
		)
		{
			throw new \Bitrix\Main\SystemException(
				'Disabled for direct access.'
			);
		}
		else
		{
			return parent::update($id, $fields);
		}
	}

	/**
	 * Delete block row.
	 * @param int $id Primary key.
	 * @return \Bitrix\Main\Result
	 */
	public static function delete($id)
	{
		if (
			!defined('LANDING_MUTATOR_MODE') ||
			LANDING_MUTATOR_MODE !== true
		)
		{
			throw new \Bitrix\Main\SystemException(
				'Disabled for direct access.'
			);
		}
		else
		{
			return parent::delete($id);
		}
	}

	/**
	 * Returns all favorites blocks.
	 * @param string|null $tplCode Page template code.
	 * @return array
	 */
	public static function getFavorites(?string $tplCode): array
	{
		return parent::getList([
			'filter' => [
				'LID' => 0,
				'=DELETED' => 'N',
				'=TPL_CODE' => $tplCode
			],
			'order' => [
				'ID' => 'asc'
			]
		])->fetchAll();
	}

	/**
	 * Gets block's rows.
	 * @param array $fields Block orm data.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList($fields = array())
	{
		if (
			!defined('LANDING_MUTATOR_MODE') ||
			LANDING_MUTATOR_MODE !== true
		)
		{
			throw new \Bitrix\Main\SystemException(
				'Disabled for direct access.'
			);
		}
		else
		{
			return parent::getList($fields);
		}
	}

	/**
	 * In ajax hit may be initiated some assets (JS extensions), but will not be added on page.
	 * We need get them all and add to output.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected static function getAjaxInitiatedAssets()
	{
		Asset::getInstance()->getJs();
		Asset::getInstance()->getCss();
		Asset::getInstance()->getStrings();

		$targetTypeList = array('JS', 'CSS');
		$CSSList = $JSList = $stringsList = [];

		foreach ($targetTypeList as $targetType)
		{
			$targetAssetList = Asset::getInstance()->getTargetList($targetType);

			foreach ($targetAssetList as $targetAsset)
			{
				$assetInfo = Asset::getInstance()->getAssetInfo($targetAsset['NAME'], \Bitrix\Main\Page\AssetMode::ALL);

				if (!empty($assetInfo['JS']))
				{
					$JSList = array_merge($JSList, $assetInfo['JS']);
				}

				if (!empty($assetInfo['CSS']))
				{
					$CSSList = array_merge($CSSList, $assetInfo['CSS']);
				}

				if (!empty($assetInfo['STRINGS']))
				{
					$stringsList = array_merge($stringsList, $assetInfo['STRINGS']);
				}
			}
		}

		return [
			'js' => array_unique($JSList),
			'css' => array_unique($CSSList),
			'strings' => array_unique($stringsList),
		];
	}

	/**
	 * Returns true if block's content contains needed string.
	 *
	 * @param int $blockId Block id.
	 * @param string $needed String for search.
	 * @return bool
	 */
	public static function isContains(int $blockId, string $needed): bool
	{
		$res = parent::getList([
			'select' => [
				'LID',
				'SITE_ID' => 'LANDING.SITE_ID'
			],
			'filter' => [
				'ID' => $blockId,
				'=ACTIVE' => 'Y',
				'=DELETED' => 'N',
				'CONTENT' => '%' . $needed . '%'
			]
		]);
		if ($row = $res->fetch())
		{
			$res = Landing::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'ID' => $row['LID']
				]
			]);
			if ($res->fetch())
			{
				return true;
			}

			if (\Bitrix\Landing\Site\Scope\Group::getGroupIdBySiteId($row['SITE_ID'], true))
			{
				return true;
			}
		}

		return false;
	}
}
