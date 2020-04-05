<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Web\DOM;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Internals;

Loc::loadMessages(__FILE__);

class Block extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Add images from new block to local storage.
	 */
	const ADD_FILES_TO_LOCAL_STORAGE = false;

	/**
	 * Dir of repoitory of blocks.
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
	 * Active or not current block.
	 * @var boolean
	 */
	protected $active = false;

	/**
	 * Deleted or not current block.
	 * @var boolean
	 */
	protected $deleted = false;

	/**
	 * Public or not current block.
	 * @var boolean
	 */
	protected $block = false;

	/**
	 * Manifest from database.
	 * @var array
	 */
	protected $manifestDB = null;

	/**
	 * Document root.
	 * @var string
	 */
	protected $docRoot = '';

	/**
	 * Instance of Error.
	 * @var \Bitrix\Landing\Error
	 */
	protected $error = null;

	/**
	 * Constructor.
	 * @param int $id Block id.
	 * @param array $data Data row from BlockTable (by default get from DB).
	 */
	public function __construct($id, $data = array())
	{
		if (empty($data) || !is_array($data))
		{
			$data = parent::getList(array(
				'select' => array(
					'*',
					'SITE_ID' => 'LANDING.SITE_ID',
					'MANIFEST' => 'MANIFEST_DB.MANIFEST'
				),
				'filter' => array(
					'ID' => $id
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
		$this->siteId = isset($data['SITE_ID']) ? intval($data['SITE_ID']) : 0;
		$this->sort = isset($data['SORT']) ? intval($data['SORT']) : '';
		$this->code = isset($data['CODE']) ? trim($data['CODE']) : '';
		$this->anchor = isset($data['ANCHOR']) ? trim($data['ANCHOR']) : '';
		$this->active = isset($data['ACTIVE']) && $data['ACTIVE'] == 'Y';
		$this->deleted = isset($data['DELETED']) && $data['DELETED'] == 'Y';
		$this->public = isset($data['PUBLIC']) && $data['PUBLIC'] == 'Y';
		$this->content = (!$this->deleted && isset($data['CONTENT'])) ? trim($data['CONTENT']) : '';

		if (isset($data['ACCESS']))
		{
			$this->access = $data['ACCESS'];
		}
		if (isset($data['MANIFEST']))
		{
			$this->manifestDB = $data['MANIFEST'];
		}

		// fill meta data
		$keys = ['CREATED_BY_ID', 'MODIFIED_BY_ID', 'DATE_CREATE', 'DATE_MODIFY'];
		foreach ($keys as $key)
		{
			if (isset($data[$key]))
			{
				$this->metaData[$key] = $data[$key];
			}
		}

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
	}

	/**
	 * Fill landing with blocks.
	 * @param \Bitrix\Landing\Landing $landing Landing instance.
	 * @param int $limit Limit count for blocks.
	 * @param array $params Additional params.
	 * @return boolean
	 */
	public static function fillLanding(\Bitrix\Landing\Landing $landing, $limit = 0, array $params = array())
	{
		if ($landing->exist())
		{
			$editMode = $landing->getEditMode() || $landing->getPreviewMode();
			$repo = array();
			$blocks = array();
			$rows = array();
			// get all blocks by filter
			$res = parent::getList(array(
				'select' => array(
					'*',
					'MANIFEST' => 'MANIFEST_DB.MANIFEST'
				),
				'filter' => array(
					'LID' => $landing->getId(),
					'=PUBLIC' => $editMode ? 'N' : 'Y',
					'=DELETED' => (isset($params['deleted']) && $params['deleted'] === true)
								? 'Y'
								: 'N'
				),
				'order' => array(
					'SORT' => 'ASC',
					'ID' => 'ASC'
				),
				'limit' => $limit
			));
			while ($row = $res->fetch())
			{
				$row['SITE_ID'] = $landing->getSiteId();
				$block = new self($row['ID'], $row);
				if (!$editMode && $block->getRepoId())
				{
					$repo[] = $block->getRepoId();
				}
				$blocks[$row['ID']] = $block;
			}
			if (!empty($repo))
			{
				$repo = Repo::getAppInfo($repo);
			}
			// add blocks to landing
			foreach ($blocks as $block)
			{
				$reposInfo = isset($repo[$block->getRepoId()])
							? $repo[$block->getRepoId()]
							: array();
				if ($editMode || !$reposInfo)
				{
					$landing->addBlockToCollection($block);
				}
				elseif (
					isset($reposInfo['PAYMENT_ALLOW']) &&
					$reposInfo['PAYMENT_ALLOW'] == 'Y'
				)
				{
					$landing->addBlockToCollection($block);
				}
			}
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
					'CONTENT', 'PUBLIC', 'ACCESS'
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
	 * Get content from repository by code.
	 * @param string $code Block code.
	 * @param string $namespace Namespace (optional).
	 * @return string
	 */
	public static function getContentFromRepository($code, $namespace = null)
	{
		$content = '';

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
			}
		}
		elseif ($manifest = Manifest::getByCode($code, true))
		{
			$content = $manifest['CONTENT'];
		}

		return $content;
	}

	/**
	 * Create instance by string code.
	 * @param \Bitrix\Landing\Landing $landing Landing - owner for new block.
	 * @param string $code Code of block from repository.
	 * @param array $data Additional data array.
	 * @return Block|false
	 */
	public static function createFromRepository(\Bitrix\Landing\Landing $landing, $code, $data = array())
	{
		// get content and mainfest
		$content = self::getContentFromRepository($code);
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
			'CONTENT' => $content,
			'ACTIVE' => 'Y'
		);
		$availableReplace = array(
			'ACTIVE', 'PUBLIC', 'ACCESS',
			'SORT', 'CONTENT', 'ANCHOR'
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
			if (
				isset($manifest['callbacks']['afteradd']) &&
				is_callable($manifest['callbacks']['afteradd'])
			)
			{
				$manifest['callbacks']['afteradd']($block);
			}
			// get all images from block to local storage
			if (self::ADD_FILES_TO_LOCAL_STORAGE)
			{
				foreach ($manifest['nodes'] as $selector => $node)
				{
					if (isset($node['type']) && $node['type'] == 'img')
					{
						$images = \Bitrix\Landing\Node\Img::getNode(
							$block, $selector
						);
						foreach ($images as &$img)
						{
							$file = Manager::savePicture($img['src']);
							if ($file)
							{
								File::addToBlock(
									$block->getId(),
									$file['ID']
								);
								$img['src'] = $file['SRC'];
								$img['id'] = $file['ID'];
							}
						}
						\Bitrix\Landing\Node\Img::saveNode(
							$block,
							$selector,
							$images
						);
					}
				}
				$block->saveContent(
					$block->getDom()->saveHTML()
				);
				$block->save();
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

		if ($newBlocks === null)
		{
			$newBlocks = unserialize(Manager::getOption('new_blocks'));
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
	 * Clear cache repository.
	 * @return void
	 */
	public static function clearRepositoryCache()
	{
		if (defined('BX_COMP_MANAGED_CACHE'))
		{
			Manager::getCacheManager()->clearByTag(self::BLOCKS_TAG);
		}
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
			$lastUsed = self::getLastUsed(50);
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
		$cacheId .= 'disable:' . implode(',', $disableNamespace);
		$cacheId .= 'enable:' . implode(',', $enableNamespace);
		$cachePath = 'landing';
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
			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				Manager::getCacheManager()->startTagCache($cachePath);
				Manager::getCacheManager()->registerTag(self::BLOCKS_TAG);
			}
		}

		// not in cache - init
		$blocks = array();
		$sections = array();

		// gets namespaces
		$namespaces = [];
		$paths = [
			BX_ROOT . '/' . self::BLOCKS_DIR,
			\getLocalPath(self::BLOCKS_DIR)
		];
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

		//get all blocks with description-file
		sort($namespaces);
		foreach ($namespaces as $subdir)
		{
			// get from cloud only if it not repo
			$restSrc = Manager::getOption('block_vendor_' . $subdir);
			if (
				(!defined('LANDING_IS_REPO') || LANDING_IS_REPO !== true) &&
				$restSrc
			)
			{
				$http = new HttpClient;
				try
				{
					$json = Json::decode($http->get(
						$restSrc . 'landing_cloud.cloud.getrepository' .
						'?user_lang=' . LANGUAGE_ID .
						'&version=' . Manager::getVersion()
					));
				}
				catch (\Exception $e)
				{
					if ($cacheStarted)
					{
						$cache->abortDataCache();
					}
				}
				if (
					isset($json['result']) &&
					is_array($json['result'])
				)
				{
					$insertCodes = array();
					foreach ($json['result'] as $sectionCode => $sectionItem)
					{
						$sections[$sectionCode] = $sectionItem['name'];
						if (
							isset($sectionItem['items']) &&
							is_array($sectionItem['items'])
						)
						{
							foreach ($sectionItem['items'] as $code => $item)
							{
								if (isset($item['manifest']))
								{
									if (!isset($item['content']))
									{
										$item['content'] = '';
									}
									if (!isset($insertCodes[$code]))
									{
										$insertCodes[$code] = true;
										Manifest::add(array(
											'CODE' => $code,
											'MANIFEST' => $item['manifest'],
											'CONTENT' => $item['content']
										));
									}
									unset($item['content']);
									unset($item['manifest']);
								}
								$blocks[$code] = $item;
							}
						}
					}
				}
			}
			else if (($handle = opendir($path . '/' . $subdir)))
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
					$sections['last'] = Loc::getMessage('LD_BLOCK_SECTION_LAST');
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
								'app_code' => false
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

		// create new section in repo
		$createNewSection = function($title)
		{
			return array(
				'name' => $title,
				'new' => false,
				'separator' => false,
				'app_code' => false,
				'items' => array()
			);
		};

		// set by sections
		$md5s = array();
		foreach ($sections as $code => $title)
		{
			$title = trim($title);
			$blocksCats[$code] = $createNewSection($title);
			$md5s[md5(strtolower($title))] = $code;
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
				$sectionMd5 = md5(strtolower($section));
				// adding new sections (actual for repo blocks)
				if (
					!isset($blocksCats[$section]) &&
					!isset($blocksCats[$sectionMd5])
				)
				{
					if (isset($md5s[$sectionMd5]))
					{
						$section = $md5s[$sectionMd5];
					}
					else
					{
						$blocksCats[$sectionMd5] = $createNewSection($section);
						$section = $sectionMd5;
					}
				}
				else if (isset($blocksCats[$sectionMd5]))
				{
					$section = $sectionMd5;
				}
				$blocksCats[$section]['items'][$key] = $block;
				if ($block['new'])
				{
					$blocksCats[$section]['new'] = true;
				}
			}
		}

		// add apps sections
		if (!empty($blocksRepo))
		{
			$blocksCats['separator_apps'] = array(
				'name' => Loc::getMessage('LANDING_BLOCK_SEPARATOR_PARTNER'),
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

		// system cache end
		if ($cacheStarted)
		{
			$cache->endDataCache($blocksCats);
			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				Manager::getCacheManager()->endTagCache();
			}
		}

		$blocksCats = $fillLastUsed($blocksCats);

		return $returnFunc($blocksCats);
	}

	/**
	 * Get last used blocks by current user.
	 * @param int $count Count of blocks.
	 * @return array
	 */
	public static function getLastUsed($count = 10)
	{
		$blocks = array();

		$c = 0;
		$res = parent::getList(array(
			'select' => array(
				'CODE'
			),
			'filter' => array(
				'CREATED_BY_ID' => Manager::getUserId(),
				'=PUBLIC' => 'N'
			),
			'order' => array(
				'DATE_CREATE' => 'DESC'
			)
		));
		while ($row = $res->fetch())
		{
			$blocks[$row['CODE']] = $row['CODE'];
			if (++$c >= $count)
			{
				break;
			}
		}

		return array_values($blocks);
	}


	/**
     * Get blocks style manifest from repository.
     * @return array
	*/
	public static function getStyle()
	{
		$style = array();

		// read all subdirs ($namespaces) in block dir
		$path = Manager::getDocRoot() . \getLocalPath(self::BLOCKS_DIR);
		if (($handle = opendir($path)))
		{
			while ((($entry = readdir($handle)) !== false))
			{
				if (
					$entry != '.' && $entry != '..' &&
					is_dir($path . '/' . $entry) &&
					file_exists($path . '/' . $entry . '/.style.php')
				)
				{
					$style[$entry] = include $path . '/' . $entry . '/.style.php';
				}
			}
		}

		return $style;
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

		ob_start();
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
				'blocks_limit' => 1
			]
		);
		$block->view(
			false,
			$landing->exist() ? $landing : null,
			$params
		);
		$content = ob_get_contents();
		$content = self::replaceMetaMarkers($content);
		ob_end_clean();
		if ($block->exist())
		{
			Manager::getApplication()->restartBuffer();
			$availableJS = !$editMode || !$block->getRepoId();
			$return = array(
				'id' => $id,
				'content' => $content,
				'content_ext' => $extContent,
				'css' => $block->getCSS(),
				'js' => $availableJS ? $block->getJS() : array(),
				'manifest' => $block->getManifest()
			);
			if (
				$editMode &&
				isset($return['manifest']['requiredUserAction'])
			)
			{
				$return['requiredUserAction'] = $return['manifest']['requiredUserAction'];
			}
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
		return 'block' . $id;
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

		$code = trim($code);

		if (isset($paths[$code]))
		{
			return $paths[$code];
		}

		$paths[$code] = '';
		$path = Manager::getDocRoot() . \getLocalPath(self::BLOCKS_DIR);

		// read all subdirs ($namespaces) in block dir
		if (empty($namespace))
		{
			if (($handle = opendir($path)))
			{
				while ((($entry = readdir($handle)) !== false))
				{
					if (
						is_dir($path . '/' . $entry) &&
						$entry != '.' && $entry != '..'
					)
					{
						$namespace[] = $entry;
					}
				}
			}
			sort($namespace);
		}

		// get first needed block from end
		foreach (array_reverse($namespace) as $subdir)
		{
			if (file_exists($path . '/' . $subdir . '/' . $code . '/.description.php'))
			{
				$paths[$code] = $subdir;
				break;
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
		if (!$namespace)
		{
			$namespace = self::getBlockNamespace($code);
		}
		if ($namespace)
		{
			$disabled = explode(',', Manager::getOption('disabled_namespaces', ''));
			if (!in_array($namespace, $disabled))
			{
				return \getLocalPath(
					self::BLOCKS_DIR . '/' . $namespace . '/' . $code
				);
			}
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
	 * Get current access.
	 * @return string
	 */
	public function getAccess()
	{
		return $this->access;
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
		elseif (isset($this->manifestDB['block']['preview']))
		{
			return $this->manifestDB['block']['preview'];
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
	 * @param string $type Type.
	 * @return string
	 */
	protected function getTypeClass($type)
	{
		static $classes = array();

		$type = strtolower($type);

		if (isset($classes[$type]))
		{
			return $classes[$type];
		}

		$class = __NAMESPACE__ . '\\Node\\' . $type;

		// check custom classes
		$event = new \Bitrix\Main\Event('landing', 'onGetNodeClass', array(
			'type' => $type,
		));
		$event->send();
		foreach ($event->getResults() as $result)
		{
			if ($result->getResultType() != \Bitrix\Main\EventResult::ERROR)
			{
				if (
					($modified = $result->getModified()) &&
					isset($modified['class']) &&
					is_subclass_of($modified['class'], '\\Bitrix\\Landing\\Node')
				)
				{
					$class = $modified['class'];
				}
			}
		}

		$classes[$type] = $class;

		return $classes[$type];
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
				return $manifestStore[$this->code];
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
				return $manifest;
			};

			$manifest = $includeDesc($this->docRoot . $path);
		}
		else
		{
			if ($this->manifestDB === null)
			{
				$this->manifestDB = Manifest::getByCode($this->code);
			}
			$manifest = $this->manifestDB;
		}

		// prepare manifest
		if (isset($manifest['block']['name']))
		{
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
			// set empty array if no exists
			foreach (array('cards', 'nodes', 'attrs') as $code)
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
				$class = $this->getTypeClass($node['type']);
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
					$callbacks[strtolower($code)] = $callback;
				}
				$manifest['callbacks'] = $callbacks;
			}
			// prepare styles
			if (!isset($manifest['namespace']))
			{
				$manifest['namespace'] = $this->getBlockNamespace($this->code);
			}
			if (
				isset($manifest['style']) &&
				!(
					isset($manifest['style']['block']) &&
					isset($manifest['style']['nodes']) &&
					count($manifest['style']) == 2
				)
			)
			{
				$manifest['style'] = array(
					'block' => array(),
					'nodes' => is_array($manifest['style'])
								? $manifest['style']
								: array()
				);
			}
			elseif (
				!isset($manifest['style']) ||
				!is_array($manifest['style'])
			)
			{
				$manifest['style'] = array(
					'block' => array(),
					'nodes' => array()
				);
			}
			// other
			$manifest['code'] = $this->code;
		}
		else
		{
			$manifest = array();
		}

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

		return $manifest;
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

		if (isset($manifests[$code]))
		{
			return $manifests[$code];
		}

		$manifests[$code] = array();
		$namespace = null;

		if (strpos($code, ':') !== false)
		{
			list($namespace, $code) = explode(':', $code);
		}

		if ($path = self::getBlockPath($code ,$namespace))
		{
			$docRoot = Manager::getDocRoot();
			Loc::loadLanguageFile($docRoot . $path . '/.description.php');
			$manifests[$code] = include $docRoot . $path . '/.description.php';
		}
		else
		{
			$manifests[$code] = Manifest::getByCode($code);
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
			}
			else
			{
				if ($this->manifestDB === null)
				{
					$this->manifestDB = Manifest::getByCode($this->code);
				}
				$manifest = $this->manifestDB;
			}

			if (isset($manifest['block']['namespace']))
			{
				$classFile = self::BLOCKS_DIR;
				$classFile .= '/' . $manifest['block']['namespace'] . '/';
				$classFile .= $this->code . '/class.php';
				$classFile = \getLocalPath($classFile);
				if ($classFile)
				{
					$asset[$this->code]['class'][] = Manager::getDocRoot() . $classFile;
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
							in_array($file, array('landing_form'))
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
				$file = $path . '/' . self::CSS_FILE_NAME;
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

		return isset($asset[$this->code][$type])
				? $asset[$this->code][$type]
				: $asset[$this->code];
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
	 * @param string $message Message for show in block.
	 * @return string
	 */
	protected function getMessageBlock($message)
	{
		ob_start();
		Manager::getApplication()->includeComponent(
			'bitrix:landing.blocks.message',
			'',
			[
				'MESSAGE' => $message
			],
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

		static $assetsPlaced = array();

		if (!isset($params['wrapper_show']))
		{
			$params['wrapper_show'] = true;
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

		if ($edit || $this->active)
		{
			foreach ($this->getCSS() as $css)
			{
				if ($this->repoId)
				{
					if (!in_array($css, $assetsPlaced))
					{
						$assetsPlaced[] = $css;
						Manager::setPageView(
							'BeforeHeadClose',
							'<link href="' . \htmlspecialcharsbx($css) . '" type="text/css" rel="stylesheet" />'
						);
					}
				}
				else
				{
					Asset::getInstance()->addCSS($css);
				}
			}
			if (($ext = $this->getExt()))
			{
				\CUtil::initJSCore($ext);
			}
			if (!$edit || !$this->repoId)
			{
				foreach ($this->getJS() as $js)
				{
					if ($this->repoId)
					{
						if (!in_array($js, $assetsPlaced))
						{
							$assetsPlaced[] = $js;
							Manager::setPageView(
								'FooterJS',
								'<script type="text/javascript" src="' . \htmlspecialcharsbx($js) . '"></script>'
							);
						}
					}
					else
					{
						Asset::getInstance()->addJS($js);
					}
				}
			}
			// calling class(es) of block
			foreach ($this->getClass() as $class)
			{
				$classBlock = $this->includeBlockClass($class);
				$classBlock->beforeView($this);
			}
		}

		if ($params['wrapper_show'])
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
			$classFromCode = 'block-' . $this->code;
			$classFromCode = preg_replace('/([^a-z0-9-])/i', '-', $classFromCode);
			$classFromCode = ' ' . $classFromCode;
			$content = '<div id="' . $anchor . '" class="block-wrapper' .
					   		(!$this->active ? ' landing-block-deactive' : '') .
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
			$manifest = $this->getManifest();
			if (!$manifest)
			{
				$manifest = array(
					'code' => $this->code
				);
			}
			if ($manifest)
			{
				echo '<script type="text/javascript">'
						. 'BX.ready(function(){'
							. 'if (typeof BX.Landing.Block !== "undefined")'
							. '{'
								. 'new BX.Landing.Block('
									. 'BX("block' . $this->id  . '"), '
									. '{'
										. 'id: ' . $this->id  . ', '
										. 'active: ' . ($this->active ? 'true' : 'false')  . ', '
										. 'anchor: ' . '"' . \CUtil::jsEscape($this->anchor) . '"' . ', '
										. 'access: ' . '"' . $this->access . '"' . ', '
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
					echo $this->getMessageBlock(
						Loc::getMessage('LANDING_BLOCK_MESSAGE_ERROR_EVAL')
					);
				}
			}
		}
		elseif ($this->active)
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
				// for compatibility, tmp
				if (isset($sysPages['@#system_mainpage@']))
				{
					unset($sysPages['@#system_mainpage@']);
				}
				if (!empty($sysPages))
				{
					$urls = Landing::getPublicUrl($sysPages);
					foreach ($sysPages as $code => $lid)
					{
						if (isset($urls[$lid]))
						{
							$sysPages[$code] = $urls[$lid];
						}
						else
						{
							unset($sysPages[$code]);
						}
					}
				}
			}
			if (!empty($sysPages))
			{
				$content = preg_replace(
					array_keys($sysPages),
					array_values($sysPages),
					$content
				);
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
	}

	/**
	 * Set new content.
	 * @param string $content New content.
	 * @return void
	 */
	public function saveContent($content)
	{
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return;
		}
		$this->content = trim($content);
		$this->getDom(true);
	}

	/**
	 * Save current block in DB.
	 * @return boolean
	 */
	public function save()
	{
		$data = array(
			'SORT' => $this->sort,
			'ACTIVE' => $this->active ? 'Y' : 'N',
			'ANCHOR' => $this->anchor,
			'DELETED' => $this->deleted ? 'Y' : 'N'
		);
		if ($this->content)
		{
			$data['CONTENT'] = $this->content;
		}
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
			'LID' => $lid,
			'PARENT_ID' => 0,
			'PUBLIC' => 'N'
		));
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
			$doc[$this->id]->loadHTML($this->content);
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
	 * @deprecated since 18.6.0.
	 * @return boolean Success or failure.
	 */
	public function adjustCards($selector, $count, &$changed = false)
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
						$tmpCardName = strtolower('tmpcard' . randString(10));
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
		if ($this->access < $this::ACCESS_W)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		$doc = $this->getDom();
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
		$doc = $this->getDom();
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
			$doc = $this->getDom();
			$resultList = $doc->querySelectorAll($selector);
			if (isset($resultList[$position]))
			{
				$resultList[$position]->getParentNode()->removeChild(
					$resultList[$position]
				);
				$this->saveContent($doc->saveHTML());
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
					$value = trim($value);
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

		$doc = $this->getDom();
		$manifest = $this->getManifest();
		// find available nodes by manifest from data
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
				call_user_func_array(array(
					$this->getTypeClass($node['type']),
					'saveNode'
				), array(
					&$this,
					$selector,
					$data[$selector],
					$additional
				));
			}
		}
		// save rebuild html as text
		$this->saveContent($doc->saveHTML());
		return true;
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
							if (strpos($sel, '@'))
							{
								list($sel, $pos) = explode('@', $sel);
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
						if (isset($styles[$remove]))
						{
							unset($styles[$remove]);
						}
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
		$positions = array();
		foreach ((array)$data as $selector => $item)
		{
			if (strpos($selector, '@') !== false)
			{
				list($selector, $position) = explode('@', $selector);
			}
			else
			{
				$position = -1;
			}
			if ($position >= 0)
			{
				if (!isset($positions[$selector]))
				{
					$positions[$selector] = array();
				}
				$positions[$selector][] = $position;
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
		$styles[$wrapper] = array(
			//
		);
		foreach ($styles as $selector => $node)
		{
			if (isset($data[$selector]))
			{
				// prepare data
				if (!is_array($data[$selector]))
				{
					$data[$selector] = array(
						$data[$selector]
					);
				}
				if (!isset($data[$selector]['classList']))
				{
					$data[$selector] = array(
						'classList' => $data[$selector]
					);
				}
				if (!isset($data[$selector]['affect']))
				{
					$data[$selector]['affect'] = array();
				}
				// apply classes to the block
				if ($selector == $wrapper)
				{
					$resultList = array(
						array_pop($doc->getChildNodesArray())
					);
				}
				// or by selector
				else
				{
					$resultList = $doc->querySelectorAll($selector);
				}
				foreach ($resultList as $pos => $resultNode)
				{
					if (
						isset($positions[$selector]) &&
						!in_array($pos, $positions[$selector])
					)
					{
						continue;
					}
					if ($resultNode)
					{
						if ($resultNode->getNodeType() == $resultNode::ELEMENT_NODE)
						{
							$resultNode->setClassName(
								implode(' ', $data[$selector]['classList'])
							);
						}
						// affected styles
						if (!empty($data[$selector]['affect']))
						{
							$this->removeStyle(
								$resultNode,
								$data[$selector]['affect']
							);
						}
					}
				}
			}
		}
		// save rebuild html as text
		$this->saveContent($doc->saveHTML());
		return true;
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

		// wrapper (not realy exist)
		$wrapper = '#' . $this->getAnchor($this->id);

		// find available nodes by manifest from data
		$attrs = $manifest['attrs'];
		$attrs[$wrapper] = array(
			//
		);

		// find attrs in style key
		if (isset($manifest['style']['nodes']))
		{
			foreach ($manifest['style']['nodes'] as $selector => $item)
			{
				if (
					isset($item['additional']['attrs']) &&
					is_array($item['additional']['attrs'])
				)
				{
					foreach ($item['additional']['attrs'] as $attr)
					{
						if (!isset($attrs[$selector]))
						{
							$attrs[$selector] = array();
						}
						$attrs[$selector][] = $attr;
					}
				}
			}
		}
		// and in block styles
		if (
			isset($manifest['style']['block']['additional']['attrs']) &&
			is_array($manifest['style']['block']['additional']['attrs'])
		)
		{
			foreach ($manifest['style']['block']['additional']['attrs'] as $attr)
			{
				if (!isset($attrs[$wrapper]))
				{
					$attrs[$wrapper] = array();
				}
				$attrs[$wrapper][] = $attr;
			}
		}

		// and in cards key
		if (isset($manifest['cards']))
		{
			foreach ($manifest['cards'] as $selector => $item)
			{
				if (
					isset($item['additional']['attrs']) &&
					is_array($item['additional']['attrs'])
				)
				{
					foreach ($item['additional']['attrs'] as $attr)
					{
						if (!isset($attrs[$selector]))
						{
							$attrs[$selector] = array();
						}
						$attrs[$selector][] = $attr;
					}
				}
			}
		}

		foreach ($attrs as $selector => $item)
		{
			if (isset($data[$selector]))
			{
				// not multi
				if (!isset($item[0]))
				{
					$item = array($item);
				}
				// prepare attrs (and group attrs)
				$attrItems = array();
				foreach ($item as $key => $val)
				{
					if (
						isset($val['attrs']) &&
						is_array($val['attrs'])
					)
					{
						foreach ($val['attrs'] as $groupAttr)
						{
							$item[] = $groupAttr;
						}
						unset($item[$key]);
					}
				}
				foreach ($item as $val)
				{
					if (!isset($val['attribute']))
					{
						continue;
					}
					if (!isset($attrItems[$val['attribute']]))
					{
						$attrItems[$val['attribute']] = array();
					}
					if (isset($data[$selector][$val['attribute']]))
					{
						$attrItems[$val['attribute']][-1] = $data[$selector][$val['attribute']];
					}
					// cards
					else if (is_array($data[$selector]))
					{
						foreach ($data[$selector] as $pos => $card)
						{
							if (isset($card[$val['attribute']]))
							{
								$attrItems[$val['attribute']][$pos] = $card[$val['attribute']];
							}
						}
					}
				}
				// set attrs to the block
				if ($selector == $wrapper)
				{
					$resultList = array(
						array_pop($doc->getChildNodesArray())
					);
				}
				// or by selector
				else
				{
					$resultList = $doc->querySelectorAll($selector);
				}
				foreach ($resultList as $pos => $resultNode)
				{
					foreach ($attrItems as $code => $val)
					{
						if (isset($val[-1]))
						{
							$val = $val[-1];
						}
						else if (isset($val[$pos]))
						{
							$val = $val[$pos];
						}
						else
						{
							continue;
						}
						$resultNode->setAttribute(
							\htmlspecialcharsbx($code),
							is_array($val)
							? json_encode($val)
							: $val
						);
					}
				}
			}
		}
		// save rebuild html as text
		$this->saveContent($doc->saveHTML());
	}

	/**
	 * Replace title and breadcrumb marker in the block.
	 * @param string $content Some content.
	 * @return string
	 */
	protected static function replaceMetaMarkers($content)
	{
		if (strpos($content, '#breadcrumb#') !== false)
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

		if (strpos($content, '#title#') !== false)
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
						if (strpos($this->getCode(), 'menu') !== false)
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
				$class = '\\Bitrix\\Landing\\Node\\' . $node['type'];
				$nodes[$selector] = $class::getNode($this, $selector);
			}
		}
		// get actual css from nodes
		if (isset($manifest['style']['nodes']))
		{
			foreach ($manifest['style']['nodes'] as $selector => $node)
			{
				$styles[$selector] = array();
				$resultList = $doc->querySelectorAll($selector);
				foreach ($resultList as $pos => $result)
				{
					if ($result->getNodeType() == $result::ELEMENT_NODE)
					{
						$styles[$selector][$pos] = trim($result->getClassName());
					}
				}
				if (empty($styles[$selector]))
				{
					unset($styles[$selector]);
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
		if (isset($manifest['style']['block']))
		{
			$resultList = array(
				array_pop($doc->getChildNodesArray())
			);
			foreach ($resultList as $pos => $result)
			{
				if ($result && $result->getNodeType() == $result::ELEMENT_NODE)
				{
					$styles['#wrapper'][$pos] = trim($result->getClassName());
				}
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
			$resultList = array(
				array_pop($doc->getChildNodesArray())
			);
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
			'style' => $styles,
			'attrs' => $allAttrs
		];
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
}