<?php
namespace Bitrix\Landing\Transfer\Import;

use Bitrix\Landing\History;
use Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Landing\Landing as LandingCore;
use Bitrix\Landing\Manager;
use \Bitrix\Landing\Site as SiteCore;
use Bitrix\Landing\Subtype\Form;
use \Bitrix\Landing\Transfer\AppConfiguration;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Folder;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Repo;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\Node;
use \Bitrix\Main\Event;
use Bitrix\Main\Entity;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use \Bitrix\Rest\AppTable;
use \Bitrix\Rest\Configuration;
use \Bitrix\Crm;

/**
 * Import landing from rest
 */
class Landing
{
	/**
	 * Local variable to force append REST blocks into the repository.
	 * @var bool
	 */
	protected static $forceAppendRestBlocks = true;

	/**
	 * Sets local variable to force append REST blocks into the repository.
	 * @param bool $mode Mode state.
	 * @return void
	 */
	public static function setForceAppendRestBlocks(bool $mode): void
	{
		self::$forceAppendRestBlocks = $mode;
	}

	/**
	 * Finds repository block and return it id.
	 * @param string $appCode Application code.
	 * @param string $xmlId External id application.
	 * @return int|null
	 */
	public static function getRepoId(string $appCode, string $xmlId): ?int
	{
		static $items = null;

		if ($items === null)
		{
			$items = [];
			$res = Repo::getList([
				'select' => [
					'ID', 'APP_CODE', 'XML_ID',
				],
			]);
			while ($row = $res->fetch())
			{
				$items[$row['APP_CODE'] . '@' . $row['XML_ID']] = $row['ID'];
			}
		}

		if (isset($items[$appCode . '@' . $xmlId]))
		{
			return $items[$appCode . '@' . $xmlId];
		}

		return null;
	}

	/**
	 * Checks files in update data and replace files paths.
	 * @param Block $block Block instance.
	 * @param array $data Data for passing to updateNodes.
	 * @param Configuration\Structure $structure Additional instance for unpacking files.
	 * @param bool $ignoreManifest Ignore manifest for detecting files.
	 * @return array
	 */
	protected static function addFilesToBlock(Block $block, array $data, Configuration\Structure $structure, bool $ignoreManifest = false): array
	{
		if (!$ignoreManifest)
		{
			$manifest = $block->getManifest();
		}

		foreach ($data as $selector => &$nodes)
		{
			if (!$ignoreManifest)
			{
				if (!isset($manifest['nodes'][$selector]))
				{
					continue;
				}
				if (
					$manifest['nodes'][$selector]['type'] !== Node\Type::IMAGE
					&& $manifest['nodes'][$selector]['type'] !== Node\Type::STYLE_IMAGE
				)
				{
					continue;
				}
			}
			foreach ($nodes as &$node)
			{
				foreach (['', '2x'] as $x)
				{
					if (isset($node['id' . $x]))
					{
						$unpackedFile = $structure->getUnpackFile($node['id' . $x]);
						if ($unpackedFile)
						{
							$newFileId = AppConfiguration::saveFile($unpackedFile);
							if ($newFileId)
							{
								$newFilePath = File::getFilePath($newFileId);
								if ($newFilePath)
								{
									File::addToBlock($block->getId(), $newFileId);
									$node['id' . $x] = $newFileId;
									$node['src' . $x] = $newFilePath;
								}
							}
						}
					}
				}
			}
			unset($node);
		}
		unset($nodes);

		return $data;
	}

	/**
	 * Save new data to the block.
	 * @param Block $blockInstance Block instance.
	 * @param array $block Block data.
	 */
	public static function saveDataToBlock(Block $blockInstance, array $block): void
	{
		// update cards
		if (isset($block['cards']) && is_array($block['cards']))
		{
			$blockInstance->updateCards(
				$block['cards']
			);
		}
		// update style
		if (isset($block['style']) && is_array($block['style']))
		{
			foreach ($block['style'] as $selector => $styleData)
			{
				if ($selector === '#wrapper')
				{
					$selector = '#' . $blockInstance->getAnchor($blockInstance->getId());
				}
				$styleToSet = [];
				// compatibility for old style export
				if (!isset($styleData['classList']) && !isset($styleData['style']))
				{
					foreach ((array)$styleData as $clPos => $clVal)
					{
						// todo: check compatibility
						$selectorUpd = $selector . '@' . $clPos;
						$styleToSet[$selectorUpd]['classList'] = (array)$clVal;
					}
				}

				// new style export format (classList + style)
				if (isset($styleData['classList']))
				{
					foreach ($styleData['classList'] as $clPos => $class)
					{
						if ($class)
						{
							$selectorUpd = $selector . '@' . $clPos;
							$styleToSet[$selectorUpd]['classList'] = explode(' ', $class);
						}
					}
				}
				if (isset($styleData['style']))
				{
					foreach ($styleData['style'] as $stPos => $styles)
					{
						if (!empty($styles))
						{
							$selectorUpd = $selector . '@' . $stPos;
							$styleToSet[$selectorUpd]['style'] = $styles;
						}
					}
				}

				if (!empty($styleToSet))
				{
					$blockInstance->setClasses($styleToSet);
				}
			}
		}
		// update nodes
		if (isset($block['nodes']) && is_array($block['nodes']))
		{
			$blockInstance->updateNodes($block['nodes']);
		}
		// update menu
		if (isset($block['menu']) && !empty($block['menu']))
		{
			$blockInstance->updateNodes($block['menu']);
		}
		// update attrs
		if (isset($block['attrs']) && !empty($block['attrs']))
		{
			if (isset($block['attrs']['#wrapper']))
			{
				$attrCode = '#' . $blockInstance->getAnchor($blockInstance->getId());
				$block['attrs'][$attrCode] = $block['attrs']['#wrapper'];
				unset($block['attrs']['#wrapper']);
			}
			$blockInstance->setAttributes($block['attrs']);
		}
		// update dynamic source
		if (isset($block['dynamic']) && is_array($block['dynamic']))
		{
			$blockInstance->saveDynamicParams($block['dynamic']);
		}
	}

	/**
	 * Imports block in to the landing and returns it new id.
	 * @param LandingCore $landing Landing instance.
	 * @param array $block Block data.
	 * @param Configuration\Structure $structure Additional instance for unpacking files.
	 * @param bool &$pending This block in pending mode.
	 * @return int
	 */
	protected static function importBlock(LandingCore $landing, array $block, Configuration\Structure $structure, bool &$pending = false): int
	{
		static $sort = 0;
		static $appChecked = [];

		$blockId = 0;

		// if this is a REST block
		if (
			isset($block['repo_block']['app_code']) &&
			isset($block['repo_block']['xml_id']) &&
			is_string($block['repo_block']['app_code']) &&
			is_string($block['repo_block']['xml_id'])
		)
		{
			unset($block['code']);

			$repoId = self::getRepoId(
				$block['repo_block']['app_code'],
				$block['repo_block']['xml_id']
			);
			if ($repoId)
			{
				$block['code'] = 'repo_' . $repoId;
			}

			// force append REST blocks
			if (
				!isset($block['code']) &&
				!empty($block['repo_info']) &&
				self::$forceAppendRestBlocks
			)
			{
				$appCode = $block['repo_block']['app_code'];
				if (!array_key_exists($appCode, $appChecked))
				{
					$appChecked[$appCode] = \Bitrix\Landing\Repo::getAppByCode($appCode);
				}

				if ($appChecked[$appCode])
				{
					$repoInfo = $block['repo_info'];
					$res = Repo::add([
						'APP_CODE' => $block['repo_block']['app_code'],
						'XML_ID' => $block['repo_block']['xml_id'],
						'NAME' => $repoInfo['NAME'] ?? null,
						'DESCRIPTION' => $repoInfo['DESCRIPTION'] ?? null,
						'SECTIONS' => $repoInfo['SECTIONS'] ?? null,
						'PREVIEW' => $repoInfo['PREVIEW'] ?? null,
						'MANIFEST' => serialize(unserialize($repoInfo['MANIFEST'] ?? '', ['allowed_classes' => false])),
						'CONTENT' => $repoInfo['CONTENT'] ?? null,
					]);
					if ($res->isSuccess())
					{
						$block['code'] = 'repo_' . $res->getId();
					}
				}
			}

			if (!isset($block['code']))
			{
				$pending = true;
				$blockId = $landing->addBlock(
					AppConfiguration::SYSTEM_BLOCK_REST_PENDING,
					[
						'PUBLIC' => 'N',
						'SORT' => $sort,
						'ANCHOR' => $block['anchor'] ?? '',
						'INITIATOR_APP_CODE' => $block['repo_block']['app_code'] ?? null,
					]
				);
				if ($blockId)
				{
					$sort += 500;
					$blockInstance = $landing->getBlockById($blockId);
					if ($blockInstance)
					{
						if (isset($block['nodes']) && is_array($block['nodes']))
						{
							$block['nodes'] = self::addFilesToBlock(
								$blockInstance,
								$block['nodes'],
								$structure,
								true
							);
						}
						$blockInstance->updateNodes([
							AppConfiguration::SYSTEM_COMPONENT_REST_PENDING => [
								'BLOCK_ID' => $blockId,
								'DATA' => base64_encode(serialize($block)),
							],
						]);
						$blockInstance->save();
					}
				}
				return $blockId;
			}
		}

		if (!isset($block['code']))
		{
			return $blockId;
		}

		// add block to the landing
		$blockFields = [
			'PUBLIC' => 'N',
			'SORT' => $sort,
			'ANCHOR' => $block['anchor'] ?? '',
			'INITIATOR_APP_CODE' => $block['repo_block']['app_code'] ?? null,
		];
		if ($block['full_content'] ?? null)
		{
			$blockFields['CONTENT'] = str_replace(
				['<?', '?>'],
				['< ?', '? >'],
				$block['full_content']
			);
		}
		if ($block['designed'] ?? null)
		{
			$blockFields['DESIGNED'] = 'Y';
		}
		$blockId = $landing->addBlock(
			$block['code'],
			$blockFields
		);
		if ($blockId)
		{
			$sort += 500;
			$blockInstance = $landing->getBlockById($blockId);
			if (isset($block['nodes']) && is_array($block['nodes']))
			{
				$block['nodes'] = self::addFilesToBlock(
					$blockInstance,
					$block['nodes'],
					$structure
				);
			}
			if ($block['meta']['FAVORITE_META'] ?? [])
			{
				$favoriteMeta = $block['meta']['FAVORITE_META'];
				if ($block['repo_block']['app_code'] ?? null)
				{
					$favoriteMeta['tpl_code'] = $block['repo_block']['app_code'];
				}
				if (intval($favoriteMeta['preview'] ?? 0) > 0)
				{
					$unpackFile = $structure->getUnpackFile($favoriteMeta['preview']);
					if ($unpackFile)
					{
						$favoriteMeta['preview'] = AppConfiguration::saveFile($unpackFile);
						File::addToBlock($blockInstance->getId(), $favoriteMeta['preview']);
					}
					if (!$favoriteMeta['preview'])
					{
						unset($favoriteMeta['preview']);
					}
				}
				$blockInstance->changeFavoriteMeta($favoriteMeta);
				\Bitrix\Landing\Block::clearRepositoryCache();
			}
			if ($blockFields['CONTENT'] ?? null)
			{
				$blockInstance->saveContent($blockFields['CONTENT'], $block['designed'] ?? false);
			}
			self::saveDataToBlock($blockInstance, $block);
			$blockInstance->save();
			// if block is favorite
			if (intval($block['meta']['LID'] ?? -1) === 0)
			{
				$blockInstance->changeLanding(0);
			}
		}

		return $blockId;
	}

	/**
	 * Imports landing.
	 * @param Event $event Event instance.
	 * @return array|null
	 */
	public static function importLanding(Event $event): ?array
	{
		$code = $event->getParameter('CODE');
		$content = $event->getParameter('CONTENT');
		$ratio = $event->getParameter('RATIO');
		$contextUser = $event->getParameter('CONTEXT_USER');
		$additional = $event->getParameter('ADDITIONAL_OPTION');
		$appId = $event->getParameter('APP_ID');
		$isReplaceSiteLandings = ($ratio[$code]['REPLACE_SITE_ID'] ?? 0) > 0;
		$isMainPage = self::isMainpage($event);

		$structure = new Configuration\Structure($contextUser);
		$return = [
			'RATIO' => $ratio[$code] ?? [],
			'ERROR_MESSAGES' => [],
		];

		if (!isset($content['~DATA']))
		{
			return null;
		}

		if (isset($return['RATIO']['TYPE']))
		{
			SiteCore\Type::setScope(
				$return['RATIO']['TYPE']
			);
		}

		if (!self::isNeedImport($event))
		{
			return $return;
		}

		$data = $content['~DATA'];
		$oldLid = $data['ID'] ?? null;
		if (!$oldLid)
		{
			return $return;
		}

		$siteId = null;
		if (isset($ratio[$code]['SITE_ID']) && (int)$ratio[$code]['SITE_ID'] > 0)
		{
			$siteId = (int)$ratio[$code]['SITE_ID'];
		}
		elseif ($additional && (int)$additional['siteId'] > 0)
		{
			$siteId = (int)$additional['siteId'];
			$return['RATIO']['SITE_ID'] = (int)$additional['siteId'];
		}

		if (($additional['siteId'] ?? 0) > 0)
		{
			LandingCore::enableCheckUniqueAddress();
		}

		$data['SITE_ID'] = $siteId;
		$data = self::prepareData($data);
		if ($isReplaceSiteLandings && $isMainPage)
		{
			$additionalFieldSite = (array)($ratio[$code]['ADDITIONAL_FIELDS_SITE'] ?? []);
			$data = self::mergeAdditionalFieldsForReplace($data, $additionalFieldSite);
			$return['RATIO']['ADDITIONAL_FIELDS_SITE'] = $data['ADDITIONAL_FIELDS'];
		}
		$return['RATIO']['ADDITIONAL_FIELDS'][$oldLid] = $data['ADDITIONAL_FIELDS'];
		$data = self::prepareAdditionalFiles($data, $structure);

		// folders' old format
		$convertFolderOldFormat = false;
		$return['RATIO']['FOLDERS_REF'] = $return['RATIO']['FOLDERS_REF'] ?? [];
		if ($data['FOLDER'] === 'Y')
		{
			$convertFolderOldFormat = true;
			$data['FOLDER'] = 'N';
			$res = SiteCore::addFolder($ratio[$code]['SITE_ID'], [
				'TITLE' => $data['TITLE'],
				'CODE' => $data['CODE'],
			]);
			if ($res->isSuccess())
			{
				$data['FOLDER_ID'] = $res->getId();
				$return['RATIO']['FOLDERS_REF'][$oldLid] = $data['FOLDER_ID'];
			}
		}
		elseif ($additional && $additional['folderId'])
		{
			$data['FOLDER_ID'] = (int)$additional['folderId'];
		}

		// set external partners info
		$appCode = null;
		if ($appId)
		{
			$app = AppTable::getById($appId)->fetch();
			$appCode = $app['CODE'] ?? null;
			if ($appCode)
			{
				$data['XML_ID'] = $data['TITLE'] . '|' . $appCode;
				$data['PREVIOUS_TPL_CODE'] = $data['TPL_CODE'];
				$data['TPL_CODE'] = $appCode;
			}
		}

		// base adding
		$data['ACTIVE'] = 'N';
		$data['PUBLIC'] = 'N';
		$data['FOLDER_SKIP_CHECK'] = 'Y';
		$data['INITIATOR_APP_CODE'] = $appCode;
		unset($data['CODE']);
		if ($additional)
		{
			$data = self::prepareAdditionalFields($data, $additional, $ratio);
		}
		$res = LandingCore::add($data);
		if ($res->isSuccess())
		{
			if ($convertFolderOldFormat && ($data['FOLDER_ID'] ?? 0))
			{
				Folder::update($data['FOLDER_ID'], ['INDEX_ID' => $res->getId()]);
			}

			if (isset($data['BLOCKS']) && is_array($data['BLOCKS']))
			{
				$data = self::prepareBlocksData($data, $event);
			}

			self::saveAdditionalFilesToLanding($data, $res->getId());

			$landing = LandingCore::createInstance($res->getId());

			// store old id and other references
			if ($oldLid)
			{
				$return['RATIO']['LANDINGS'][$oldLid] = $res->getId();
			}

			if (isset($data['TPL_ID']) && $data['TPL_ID'])
			{
				$return['RATIO']['TEMPLATE_LINKING'][$res->getId()] = [
					'TPL_ID' => (int)$data['TPL_ID'],
					'TEMPLATE_REF' => (array)($data['TEMPLATE_REF'] ?? []),
				];
			}
			elseif ($isReplaceSiteLandings && $isMainPage && $siteId)
			{
				$siteTemplate = (array)($return['RATIO']['TEMPLATE_LINKING'][-1 * $siteId] ?? []);
				if (!empty($siteTemplate))
				{
					$return['RATIO']['TEMPLATE_LINKING'][$res->getId()] = $siteTemplate;
					unset($return['RATIO']['TEMPLATE_LINKING'][-1 * $siteId]);
				}
			}

			if (isset($data['BLOCKS']) && is_array($data['BLOCKS']))
			{
				foreach ($data['BLOCKS'] as $oldBlockId => $blockItem)
				{
					if (is_array($blockItem))
					{
						$pending = false;
						$newBlockId = self::importBlock(
							$landing,
							$blockItem,
							$structure,
							$pending
						);
						$return['RATIO']['BLOCKS'][$oldBlockId] = $newBlockId;
						if ($pending)
						{
							$return['RATIO']['BLOCKS_PENDING'][] = $newBlockId;
						}
					}
				}
			}
		}
		else
		{
			$return['ERROR_MESSAGES'] = $res->getErrorMessages();
		}

		return $return;
	}

	/**
	 * No create new page, but replace blocks in current landing
	 * @param Event $event
	 * @return array|null
	 */
	public static function replaceLanding(Event $event): ?array
	{
		$code = $event->getParameter('CODE');
		$content = $event->getParameter('CONTENT');
		$ratio = $event->getParameter('RATIO');
		$contextUser = $event->getParameter('CONTEXT_USER');
		$structure = new Configuration\Structure($contextUser);

		if (!isset($content['~DATA']))
		{
			return null;
		}

		$return = [
			'RATIO' => $ratio[$code] ?? [],
			'ERROR_MESSAGES' => [],
		];

		if (
			!isset($ratio[$code]['REPLACE_LID'])
			|| (int)$ratio[$code]['REPLACE_LID'] <= 0
		)
		{
			$return['ERROR_MESSAGES'] = 'Not set landing ID for replace';

			return $return;
		}
		$replaceLid = (int)$ratio[$code]['REPLACE_LID'];

		if (isset($return['RATIO']['TYPE']))
		{
			SiteCore\Type::setScope($return['RATIO']['TYPE']);
		}
		LandingCore::setEditMode();
		$landing = LandingCore::createInstance($replaceLid);
		if (!$landing->exist())
		{
			$return['ERROR_MESSAGES'] = 'Raplaced landing is not exists';

			return $return;
		}

		// no landing imported
		$return['RATIO']['LANDINGS'][$replaceLid] = $replaceLid;

		if (!self::isNeedImport($event))
		{
			return $return;
		}

		$data = $content['~DATA'];
		$data = self::prepareData($data);

		$additionalFieldsBefore = self::getAdditionalFieldsForReplaceByLanding($replaceLid);
		if (is_array($ratio[$code]['ADDITIONAL_FIELDS_SITE']) && !empty($ratio[$code]['ADDITIONAL_FIELDS_SITE']))
		{
			$data = self::mergeAdditionalFieldsForReplace($data, $ratio[$code]['ADDITIONAL_FIELDS_SITE']);
			$data = self::prepareAdditionalFiles($data, $structure);
			self::saveAdditionalFieldsToLanding($data, $replaceLid);
			self::saveAdditionalFilesToLanding($data, $replaceLid);
		}

		if (isset($data['BLOCKS']) && is_array($data['BLOCKS']))
		{
			$data = self::prepareBlocksData($data, $event);
			$blocksBefore = [];
			$blocksAfter = [];

			History::deactivate();
			foreach ($landing->getBlocks() as $block)
			{
				$blockId = $block->getId();
				$block->setAccess(Block::ACCESS_X);
				if ($landing->markDeletedBlock($block->getId(), true))
				{
					$blocksBefore[] = $blockId;
				}
			}

			foreach ($data['BLOCKS'] as $oldBlockId => $block)
			{
				if (is_array($block) && !empty($block))
				{
					$pending = false;
					$newBlockId = self::importBlock(
						$landing,
						$block,
						$structure,
						$pending
					);
					$blocksAfter[] = $newBlockId;
					$return['RATIO']['BLOCKS'][$oldBlockId] = $newBlockId;
					if ($pending)
					{
						$return['RATIO']['BLOCKS_PENDING'][] = $newBlockId;
					}
				}
			}

			// find form block and replace form ID if need
			$meta = $landing->getMeta();
			$isCrmFormSite = null;
			if ($meta['SITE_SPECIAL'] === 'Y')
			{
				$isCrmFormSite =
					SiteCore\Type::getSiteSpecialType($meta['SITE_CODE']) === SiteCore\Type::PSEUDO_SCOPE_CODE_FORMS;
			}
			if ($isCrmFormSite && Loader::includeModule('crm'))
			{
				// find form
				$res = Crm\WebForm\Internals\LandingTable::getList([
					'select' => [
						'FORM_ID',
					],
					'filter' => [
						'=LANDING_ID' => $replaceLid,
					],
				]);
				$row = $res->fetch();
				$formId = $row ? $row['FORM_ID'] : null;
				if ($formId)
				{
					foreach ($landing->getBlocks() as $block)
					{
						$manifest = $block->getManifest();
						if (($manifest['block']['subtype'] ?? null) === 'form')
						{
							Form::setFormIdToBlock($block->getId(), $formId);
							if ($block->getAccess() > Block::ACCESS_W)
							{
								BlockTable::update($block->getId(), [
									'ACCESS' => Block::ACCESS_W,
								]);
							}
						}
					}
				}
			}

			if (Manager::isAutoPublicationEnabled())
			{
				$landing->publication();
			}

			History::activate();
			$history = new History($replaceLid, History::ENTITY_TYPE_LANDING);
			$history->push('REPLACE_LANDING', [
				'lid' => $replaceLid,
				'template' => $code,
				'blocksBefore' => $blocksBefore,
				'blocksAfter' => $blocksAfter,
				'additionalFieldsBefore' => $additionalFieldsBefore,
				'additionalFieldsAfter' => $data['ADDITIONAL_FIELDS'],
			]);
		}

		return $return;
	}

	/**
	 * In some cases we don't need import current landing.
	 * @param Event $event
	 * @return bool - if false - need skip current page import
	 */
	protected static function isNeedImport(Event $event): bool
	{
		$code = $event->getParameter('CODE');
		$content = $event->getParameter('CONTENT');
		$ratio = $event->getParameter('RATIO');

		if (($ratio[$code]['REPLACE_SITE_ID'] ?? 0) > 0)
		{
			return true;
		}

		if (
			$ratio[$code]['IS_PAGE_IMPORT']
			&& isset($ratio[$code]['SPECIAL_PAGES']['LANDING_ID_INDEX'])
			&& (int)$content['DATA']['ID'] !== $ratio[$code]['SPECIAL_PAGES']['LANDING_ID_INDEX']
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if current page is index page of site
	 * @param Event $event
	 * @return bool
	 */
	protected static function isMainpage(Event $event): bool
	{
		$code = $event->getParameter('CODE');
		$content = $event->getParameter('CONTENT');
		$ratio = $event->getParameter('RATIO');

		return
			isset($ratio[$code]['SPECIAL_PAGES']['LANDING_ID_INDEX'])
			&& (int)$content['DATA']['ID'] === $ratio[$code]['SPECIAL_PAGES']['LANDING_ID_INDEX']
		;
	}

	protected static function prepareData(array $data): array
	{
		// clear old keys
		$notAllowedKeys = [
			'ID', 'VIEWS', 'DATE_CREATE', 'DATE_MODIFY',
			'DATE_PUBLIC', 'CREATED_BY_ID', 'MODIFIED_BY_ID',
		];
		foreach ($notAllowedKeys as $key)
		{
			if (isset($data[$key]))
			{
				unset($data[$key]);
			}
		}

		return $data;
	}

	protected static function prepareBlocksData(array $data, Event $event): array
	{
		$data = self::fixWrapperClasses($data);
		$data = self::deleteCopyrightBlock($data, $event);
		$data = self::fixContactDataAndCountdown($data);

		self::enableHiddenBlocksForCreatingPage();

		return $data;
	}

	/**
	 * Pass filters to block repository for enable add blocks with type 'null' (hidden from list)
	 * @return void
	 */
	protected static function enableHiddenBlocksForCreatingPage(): void
	{
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('landing', 'onBlockRepoSetFilters',
			function(Event $event)
			{
				$result = new Entity\EventResult();
				$result->modifyFields([
					'DISABLE' => Block\BlockRepo::FILTER_SKIP_HIDDEN_BLOCKS,
				]);

				return $result;
			}
		);
	}

	/**
	 * Processing additional data, then contains files
	 * @param array $data
	 * @param Configuration\Structure $structure
	 * @return array
	 */
	protected static function prepareAdditionalFiles(array $data, Configuration\Structure $structure): array
	{
		foreach (Hook::HOOKS_CODES_FILES as $hookCode)
		{
			if (
				isset($data['ADDITIONAL_FIELDS'][$hookCode]) &&
				$data['ADDITIONAL_FIELDS'][$hookCode] > 0
			)
			{
				$unpackFile = $structure->getUnpackFile($data['ADDITIONAL_FIELDS'][$hookCode]);

				if ($unpackFile)
				{
					$data['ADDITIONAL_FIELDS'][$hookCode] = AppConfiguration::saveFile(
						$unpackFile
					);
				}
				else
				{
					unset($data['ADDITIONAL_FIELDS'][$hookCode]);
				}
			}
		}

		return $data;
	}

	/**
	 * Save hook files to landing
	 * @param array $data
	 * @param $landingId
	 * @return void
	 */
	protected static function saveAdditionalFilesToLanding(array $data, $landingId): void
	{
		foreach (Hook::HOOKS_CODES_FILES as $hookCode)
		{
			if (
				isset($data['ADDITIONAL_FIELDS'][$hookCode]) &&
				$data['ADDITIONAL_FIELDS'][$hookCode] > 0
			)
			{
				File::addToLanding($landingId, $data['ADDITIONAL_FIELDS'][$hookCode]);
			}
		}
	}

	/**
	 * Prepare hooks and settings by additional fields
	 * @param array $data - base params
	 * @param array $additional - additional data
	 * @param array|null $ratio - previously import data. If empty - it is single page in existing site
	 * @return array
	 */
	protected static function prepareAdditionalFields(array $data, array $additional, array $ratio = null): array
	{
		$data['ADDITIONAL_FIELDS']['THEME_USE'] = 'N';
		if (isset($additional['theme']) || isset($additional['theme_use_site']))
		{
			$color = $additional['theme_use_site'] ?? $additional['theme'];
			if ($color[0] !== '#')
			{
				$color = '#'.$color;
			}
			$data['ADDITIONAL_FIELDS']['THEME_COLOR'] = $color;
			unset($data['ADDITIONAL_FIELDS']['THEME_CODE']);

			// for variant if import only page in existing site
			$isSinglePage = !is_array($ratio) || empty($ratio);
			if ($isSinglePage && !$additional['theme_use_site'])
			{
				$data['ADDITIONAL_FIELDS']['THEME_USE'] = 'Y';
			}
		}

		// todo: move to isMainpage (need pass event)?
		$isMainpage = false;
		if ($additional['title'] && $isMainpage)
		{
			$data['ADDITIONAL_FIELDS']['METAOG_TITLE'] = $additional['title'];
			$data['ADDITIONAL_FIELDS']['METAMAIN_TITLE'] = $additional['title'];
		}

		if ($additional['description'] && $isMainpage)
		{
			$data['ADDITIONAL_FIELDS']['METAOG_DESCRIPTION'] = $additional['description'];
			$data['ADDITIONAL_FIELDS']['METAMAIN_DESCRIPTION'] = $additional['description'];
		}

		//default widget value
		$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
		$buttonKeys = array_keys($buttons);
		if (!empty($buttonKeys))
		{
			$data['ADDITIONAL_FIELDS']['B24BUTTON_CODE'] = $buttonKeys[0];
		}
		else
		{
			$data['ADDITIONAL_FIELDS']['B24BUTTON_CODE'] = 'N';
		}
		$data['ADDITIONAL_FIELDS']['B24BUTTON_USE'] = 'N';

		return $data;
	}

	/**
	 * Find current additional field by landing id, filter only fields for replace landing import
	 * @param int $lid
	 * @return array
	 */
	protected static function getAdditionalFieldsForReplaceByLanding(int $lid): array
	{
		$additionalFields = [];
		$hooks = Hook::getData($lid, Hook::ENTITY_TYPE_LANDING);
		foreach ($hooks as $hook => $fields)
		{
			foreach ($fields as $code => $field)
			{
				$additionalFields[$hook . '_' . $code] = $field;
			}
		}

		return self::getAdditionalFieldsForReplace($additionalFields);
	}

	/**
	 * Find current additional field by landing id, filter only fields for replace landing import
	 * @param int $siteId
	 * @return array
	 */
	public static function getAdditionalFieldsForReplaceBySite(int $siteId): array
	{
		$additionalFields = [];
		$hooks = Hook::getData($siteId, Hook::ENTITY_TYPE_SITE);
		foreach ($hooks as $hook => $fields)
		{
			foreach ($fields as $code => $field)
			{
				$additionalFields[$hook . '_' . $code] = $field;
			}
		}

		return self::getAdditionalFieldsForReplace($additionalFields);
	}


	/**
	 * If replace landing - need replace hooks for page too. And for design - get settings from site
	 * @param array $data
	 * @param array $additionalFieldsSite
	 * @return array
	 */
	protected static function mergeAdditionalFieldsForReplace(array $data, array $additionalFieldsSite): array
	{
		$additionalFields = $data['ADDITIONAL_FIELDS'] ?? [];
		foreach (self::getAdditionalFieldsForReplace($additionalFieldsSite) as $code => $field)
		{
			if (!isset($additionalFields[$code]))
			{
				$additionalFields[$code] = $field;
			}
		}
		$data['ADDITIONAL_FIELDS'] = $additionalFields;

		return $data;
	}

	/**
	 * Get additional fields, then need change when replace landing process
	 * @param array $additionalFields - common fields list
	 * @return array
	 */
	protected static function getAdditionalFieldsForReplace(array $additionalFields): array
	{
		$result = [];
		foreach (Hook::HOOKS_CODES_DESIGN as $hookCode)
		{
			$result[$hookCode] = $additionalFields[$hookCode] ?? '';
		}

		return $result;
	}

	protected static function saveAdditionalFieldsToLanding(array $data, int $landingId): void
	{
		if (is_array($data['ADDITIONAL_FIELDS']) && !empty($data['ADDITIONAL_FIELDS']))
		{
			LandingCore::saveAdditionalFields($landingId, $data['ADDITIONAL_FIELDS']);
		}
	}

	protected static function fixWrapperClasses(array $data): array
	{
		// @fix wrapper classes from original
		$appCode = $data['INITIATOR_APP_CODE'];
		$newTplCode = $data['PREVIOUS_TPL_CODE'] ?? $data['TPL_CODE'];
		$delobotAppCode = 'local.5eea949386cd05.00160385';
		$kraytAppCode = 'local.5f11a19f813b13.97126836';
		$bitrixAppCode = 'bitrix.';
		if (
			strpos($newTplCode, $delobotAppCode) !== false
			|| strpos($newTplCode, $kraytAppCode) !== false
			|| strpos($appCode, $bitrixAppCode) === 0
		)
		{
			$wrapperClasses = [];
			$http = new \Bitrix\Main\Web\HttpClient;
			$resPreview = $http->get(Manager::getPreviewHost() . '/tools/blocks.php?tplCode=' . $newTplCode);
			if ($resPreview)
			{
				try
				{
					$wrapperClasses = \Bitrix\Main\Web\Json::decode($resPreview);
				}
				catch (\Exception $e){}
			}

			if ($wrapperClasses)
			{
				$i = 0;
				foreach ($data['BLOCKS'] as &$blockData)
				{
					if (isset($wrapperClasses[$i]) && $wrapperClasses[$i]['code'] === $blockData['code'])
					{
						$blockData['style']['#wrapper'] = ['classList' => [$wrapperClasses[$i]['classList']]];
					}
					$i++;
				}
				unset($blockData);
			}
		}
		unset($delobotAppCode, $kraytAppCode);

		return $data;
	}

	protected static function deleteCopyrightBlock(array $data, Event $event): array
	{
		//fix, delete copyright block
		$appCode = $data['INITIATOR_APP_CODE'];
		$content = $event->getParameter('CONTENT');
		$templateDateCreate = strtotime($content['DATA']['DATE_CREATE']);
		$lastDate = strtotime('17.02.2022 00:00:00');
		if ($templateDateCreate < $lastDate)
		{
			$kraytCode = 'bitrix.krayt';
			$delobotCode = 'bitrix.delobot';
			if (strpos($appCode, $kraytCode) !== false || strpos($appCode, $delobotCode) !== false)
			{
				if (array_slice($data['BLOCKS'], -1)[0]['code'] === '17.copyright')
				{
					array_pop($data['BLOCKS']);
				}
			}
			unset($kraytCode, $delobotCode);
		}

		return $data;
	}

	protected static function fixContactDataAndCountdown(array $data): array
	{
		$appCode = $data['INITIATOR_APP_CODE'];
		$bitrixAppCode = 'bitrix.';

		foreach ($data['BLOCKS'] as &$block)
		{
			//fix contact data
			if (isset($block['nodes']) && strpos($appCode, $bitrixAppCode) === 0)
			{
				foreach ($block['nodes'] as &$node)
				{
					$countNodeItem = 0;
					foreach ($node as &$nodeItem)
					{
						if (isset($nodeItem['href']))
						{
							$setContactsBlockCode = [
								'14.1.contacts_4_cols',
								'14.2contacts_3_cols',
								'14.3contacts_2_cols',
							];
							if (preg_match('/^tel:.*$/i', $nodeItem['href']))
							{
								$nodeItem['href'] = 'tel:#crmPhone1';
								if (isset($nodeItem['text']))
								{
									$nodeItem['text'] = '#crmPhoneTitle1';
								}
								if (
									(isset($block['nodes']['.landing-block-node-linkcontact-text'])
										&&	in_array($block['code'], $setContactsBlockCode, true))
								)
								{
									$block['nodes']['.landing-block-node-linkcontact-text'][$countNodeItem] = '#crmPhoneTitle1';
								}
							}
							if (preg_match('/^mailto:.*$/i', $nodeItem['href']))
							{
								$nodeItem['href'] = 'mailto:#crmEmail1';
								if (isset($nodeItem['text']))
								{
									$nodeItem['text'] = '#crmEmailTitle1';
								}
								if (
									isset($block['nodes']['.landing-block-node-linkcontact-text'])
									&& (in_array($block['code'], $setContactsBlockCode, true))
								)
								{
									$block['nodes']['.landing-block-node-linkcontact-text'][$countNodeItem] = '#crmEmailTitle1';
								}
							}
						}
						$countNodeItem++;
					}
					unset($nodeItem);
				}
				unset($node);
			}
			//fix countdown until the next unexpired date
			if (isset($block['attrs']))
			{
				foreach ($block['attrs'] as &$attr)
				{
					foreach ($attr as &$attrItem)
					{
						if (array_key_exists('data-end-date', $attrItem))
						{
							$neededAttr = $attrItem['data-end-date'] / 1000;
							$currenDate = time();
							if ($neededAttr < $currenDate)
							{
								$m = date('m', $neededAttr);
								$d = date('d', $neededAttr);
								$currenDateY = (int)date('Y', $currenDate);
								$currenDateM = date('m', $currenDate);
								$currenDateD = date('d', $currenDate);
								if ($currenDateM > $m)
								{
									$y = $currenDateY + 1;
								}
								else if (($currenDateM === $m) && $currenDateD >= $d)
								{
									$y = $currenDateY + 1;
								}
								else
								{
									$y = $currenDateY;
								}
								$time = '10:00:00';
								$timestamp = strtotime($y . '-' . $m . '-' . $d . ' ' . $time) * 1000;
								$attrItem['data-end-date'] = (string)$timestamp;

								if (preg_match_all(
									'/data-end-date="\d+"/',
									$block['full_content'],
									$matches)
								)
								{
									$block['full_content'] = str_replace(
										$matches[0],
										'data-end-date="' . $attrItem['data-end-date'] . '"',
										$block['full_content']
									);
								}
							}
						}
					}
					unset($attrItem);
				}
				unset($attr);
			}
		}
		unset($block);

		return $data;
	}
}
