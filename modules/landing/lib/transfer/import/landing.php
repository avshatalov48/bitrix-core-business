<?php
namespace Bitrix\Landing\Transfer\Import;

use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\Site as SiteCore;
use \Bitrix\Landing\Transfer\AppConfiguration;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Folder;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Repo;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\Node;
use \Bitrix\Main\Event;
use \Bitrix\Rest\AppTable;
use \Bitrix\Rest\Configuration;

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
					'ID', 'APP_CODE', 'XML_ID'
				]
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
						'CONTENT' => $repoInfo['CONTENT'] ?? null
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
						'INITIATOR_APP_CODE' => $block['repo_block']['app_code'] ?? null
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
			'INITIATOR_APP_CODE' => $block['repo_block']['app_code'] ?? null
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
		$structure = new Configuration\Structure($contextUser);
		$return = [
			'RATIO' => $ratio[$code] ?? [],
			'ERROR_MESSAGES' => []
		];

		if (!isset($content['~DATA']))
		{
			return null;
		}

		if (isset($return['RATIO']['TYPE']))
		{
			\Bitrix\Landing\Site\Type::setScope(
				$return['RATIO']['TYPE']
			);
		}

		$data = $content['~DATA'];
		$oldId = $data['ID'] ?? null;

		if (isset($ratio[$code]['SITE_ID']) && (int)$ratio[$code]['SITE_ID'] > 0)
		{
			$data['SITE_ID'] = (int)$ratio[$code]['SITE_ID'];
		}
		elseif ($additional && (int)$additional['siteId'] > 0)
		{
			$data['SITE_ID'] = (int)$additional['siteId'];
			$return['RATIO']['SITE_ID'] = (int)$additional['siteId'];
		}

		if (($additional['siteId'] ?? 0) > 0)
		{
			LandingCore::enableCheckUniqueAddress();
		}

		// clear old keys
		$notAllowedKeys = [
			'ID', 'VIEWS', 'DATE_CREATE', 'DATE_MODIFY',
			'DATE_PUBLIC', 'CREATED_BY_ID', 'MODIFIED_BY_ID'
		];
		foreach ($notAllowedKeys as $key)
		{
			if (isset($data[$key]))
			{
				unset($data[$key]);
			}
		}

		// files
		$files = [];
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
					$files[] = $data['ADDITIONAL_FIELDS'][$hookCode] = AppConfiguration::saveFile(
						$unpackFile
					);
				}
				else
				{
					unset($data['ADDITIONAL_FIELDS'][$hookCode]);
				}
			}
		}

		// folders' old format
		$convertFolderOldFormat = false;
		$return['RATIO']['FOLDERS_REF'] = $return['RATIO']['FOLDERS_REF'] ?? [];
		if ($data['FOLDER'] === 'Y')
		{
			$convertFolderOldFormat = true;
			$data['FOLDER'] = 'N';
			$res = SiteCore::addFolder($ratio[$code]['SITE_ID'], [
				'TITLE' => $data['TITLE'],
				'CODE' => $data['CODE']
			]);
			if ($res->isSuccess())
			{
				$data['FOLDER_ID'] = $res->getId();
				$return['RATIO']['FOLDERS_REF'][$oldId] = $data['FOLDER_ID'];
			}
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
				$previousTplCode = $data['TPL_CODE'];
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
				// @fix wrapper classes from original
				$newTplCode = $previousTplCode ?? $data['TPL_CODE'];
				$delobotAppCode = 'local.5eea949386cd05.00160385';
				$kraytAppCode = 'local.5f11a19f813b13.97126836';
				if (strpos($newTplCode, $delobotAppCode) !== false || strpos($newTplCode, $kraytAppCode) !== false )
				{
					$wrapperClasses = [];
					$http = new \Bitrix\Main\Web\HttpClient;
					$resPreview = $http->get('https://preview.bitrix24.site/tools/blocks.php?tplCode=' . $newTplCode);
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
				//fix, delete copyright block
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

			// save files to landing
			foreach ($files as $fileId)
			{
				File::addToLanding($res->getId(), $fileId);
			}

			$landing = LandingCore::createInstance($res->getId());
			// store old id and other references
			if ($oldId)
			{
				$return['RATIO']['LANDINGS'][$oldId] = $res->getId();
			}
			if (isset($data['TPL_ID']) && $data['TPL_ID'])
			{
				$return['RATIO']['TEMPLATE_LINKING'][$res->getId()] = [
					'TPL_ID' => (int) $data['TPL_ID'],
					'TEMPLATE_REF' => isset($data['TEMPLATE_REF'])
									? (array) $data['TEMPLATE_REF']
									: []
				];
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
	 * Prepare hooks and settings by additional fields
	 * @param array $data - base params
	 * @param array $additional - additional data
	 * @param array|null $ratio - previously import data. If empty - it is single page in existing site
	 * @return array
	 */
	protected static function prepareAdditionalFields(array $data, array $additional, array $ratio = null): array
	{
		$data['ADDITIONAL_FIELDS']['THEME_USE'] = 'N';
		if ($additional['theme'] || $additional['theme_use_site'])
		{
			$color = $additional['theme_use_site'] ?: $additional['theme'];
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

		// todo: how detecd mainpage?
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

		return $data;
	}
}
