<?php
namespace Bitrix\Landing\Transfer\Import;

use Bitrix\Landing\Hook\Page\Copyright;
use Bitrix\Landing\Hook\Page\B24button;
use Bitrix\Landing\Rights;
use \Bitrix\Landing\Site as SiteCore;
use \Bitrix\Landing\Landing as LandingCore;
use Bitrix\Landing\Site\Type;
use \Bitrix\Landing\Transfer\AppConfiguration;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Template;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Rest\Marketplace;
use \Bitrix\Rest\Configuration;
use \Bitrix\Main\Event;
use \Bitrix\Main\ORM\Data\AddResult;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

/**
 * Import site from rest
 */
class Site
{
	/**
	 * Returns export url for the site.
	 * @param string $type Site type.
	 * @return string
	 */
	public static function getUrl(string $type): string
	{
		if (!\Bitrix\Main\Loader::includeModule('rest'))
		{
			return '';
		}
		return Marketplace\Url::getConfigurationImportManifestUrl(
			AppConfiguration::PREFIX_CODE . strtolower($type)
		);
	}

	/**
	 * Returns prepare manifest settings for export.
	 * @param Event $event Event instance.
	 * @return array|null
	 */
	public static function getInitManifest(Event $event): ?array
	{
		return [
			'NEXT' => false
		];
	}

	/**
	 * Import site data.
	 * @param array $data Site data.
	 * @param Configuration\Structure $structure Instance for getting files.
	 * @return AddResult
	 */
	protected static function importSite(array $data, Configuration\Structure $structure): AddResult
	{
		$code = isset($data['CODE']) ? $data['CODE'] : null;

		// clear old keys
		$notAllowedKeys = [
			'ID', 'DOMAIN_ID', 'DATE_CREATE', 'DATE_MODIFY',
			'CREATED_BY_ID', 'MODIFIED_BY_ID', 'CODE'
		];
		foreach ($notAllowedKeys as $key)
		{
			if (isset($data[$key]))
			{
				unset($data[$key]);
			}
		}

		// if site path are exist, create random one
		if ($code)
		{
			$check = SiteCore::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=CODE' => $code
				]
			]);
			if ($check->fetch())
			{
				$code = null;
			}
		}
		if (!$code)
		{
			$code = strtolower(\randString(10));
		}
		$data['CODE'] = $code;
		$data['ACTIVE'] = 'Y';

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

		$res = SiteCore::add($data);

		// save files to site
		if ($files && $res->isSuccess())
		{
			foreach ($files as $fileId)
			{
				File::addToSite($res->getId(), $fileId);
			}
		}

		return $res;
	}

	/**
	 * Process one export step.
	 * @param Event $event Event instance.
	 * @return array|null
	 */
	public static function nextStep(Event $event): ?array
	{
		$code = $event->getParameter('CODE');
		$content = $event->getParameter('CONTENT');
		$ratio = $event->getParameter('RATIO');
		$contextUser = $event->getParameter('CONTEXT_USER');
		$userId = $event->getParameter('USER_ID');
		$additional = $event->getParameter('ADDITIONAL_OPTION');
		$structure = new Configuration\Structure($contextUser);
		$return = [
			'RATIO' => isset($ratio[$code]) ? $ratio[$code] : [],
			'ERROR_EXCEPTION' => []
		];

		if (!isset($content['~DATA']))
		{
			return null;
		}

		if ($userId)
		{
			Rights::setContextUserId($userId);
		}

		// if all import - add page in current site
		$isPageImport = $return['RATIO']['IS_PAGE_IMPORT'] ?? false;
		// if only current step add page
		$isPageStep = false;
		// if replace landing, not import
		$isReplaceLanding = $additional && (int)($additional['replaceLid'] ?? 0) > 0;
		if (isset($ratio[$code]['SITE_ID']) && (int)$ratio[$code]['SITE_ID'] > 0)
		{
			$isPageStep = true;
		}
		elseif (
			$additional && (int)$additional['siteId'] > 0
		)
		{
			$return['RATIO']['SITE_ID'] = (int)$additional['siteId'];
			$isPageImport = $return['RATIO']['IS_PAGE_IMPORT'] ?? true;
		}

		if ($isReplaceLanding)
		{
			$return['RATIO']['REPLACE_ID'] = (int)$additional['replaceLid'];
		}

		// common ratio params
		$data = self::prepareData($content['~DATA']);
		$data = self::prepareAdditionalFields($data, $additional);
		if ($isReplaceLanding && !$isPageStep)
		{
			$return['RATIO']['ADDITIONAL_FIELDS_SITE'] = $data['ADDITIONAL_FIELDS'];
		}
		if (!isset($return['RATIO']['SPECIAL_PAGES']))
		{
			$return['RATIO']['SPECIAL_PAGES'] = [
				'LANDING_ID_INDEX' => isset($data['LANDING_ID_INDEX']) ? (int)$data['LANDING_ID_INDEX'] : 0,
				'LANDING_ID_404' => isset($data['LANDING_ID_404']) ? (int)$data['LANDING_ID_404'] : 0,
				'LANDING_ID_503' => isset($data['LANDING_ID_503']) ? (int)$data['LANDING_ID_503'] : 0
			];
		}
		$return['RATIO']['IS_PAGE_IMPORT'] = $isPageImport;
		$return['RATIO']['TYPE'] = $data['TYPE'];

		// site import
		if (!$isPageImport && !$isPageStep && !$isReplaceLanding)
		{
			Type::setScope($data['TYPE']);
			$res = self::importSite($data, $structure);
			if ($res->isSuccess())
			{
				$return['RATIO']['BLOCKS'] = [];
				$return['RATIO']['BLOCKS_PENDING'] = [];
				$return['RATIO']['LANDINGS'] = [];
				$return['RATIO']['TEMPLATES'] = [];
				$return['RATIO']['TEMPLATE_LINKING'] = [];
				$return['RATIO']['SITE_ID'] = $res->getId();
				$return['RATIO']['FOLDERS_NEW'] = $data['FOLDERS_NEW'] ?? [];
				$return['RATIO']['SYS_PAGES'] = $data['SYS_PAGES'];

				if (isset($data['TEMPLATES']) && is_array($data['TEMPLATES']))
				{
					$return['RATIO']['TEMPLATES'] = $data['TEMPLATES'];
				}
				if (isset($data['TPL_ID']) && $data['TPL_ID'])
				{
					$return['RATIO']['TEMPLATE_LINKING'][-1 * $res->getId()] = [
						'TPL_ID' => (int) $data['TPL_ID'],
						'TEMPLATE_REF' => isset($data['TEMPLATE_REF'])
										? (array) $data['TEMPLATE_REF']
										: []
					];
				}
				return $return;
			}

			$return['ERROR_EXCEPTION'] = $res->getErrorMessages();

			return $return;
		}

		// something went wrong, site was not created
		if (!isset($return['RATIO']['SITE_ID']))
		{
			$return['ERROR_EXCEPTION'][] = Loc::getMessage('LANDING_IMPORT_ERROR_SITE_ID_NOT_FOUND');
			return $return;
		}

		// skip import site step if import page in existing site
		if (!isset($data['SITE_ID']))
		{
			return $return;
		}

		// not site imports
		if (isset($return['RATIO']['REPLACE_ID']) && $return['RATIO']['REPLACE_ID'] > 0)
		{
			return Landing::replaceLanding($event);
		}

		return Landing::importLanding($event);
	}

	/**
	 * Prepare site data, set some fields to default values
	 * @param array $data
	 * @return array
	 */
	protected static function prepareData(array $data): array
	{
		if (!isset($data['TYPE']))
		{
			$data['TYPE'] = 'PAGE';
		}

		$data['LANG'] = Manager::getZone();

		return $data;
	}

	/**
	 * Prepare hooks and settings by additional fields
	 * @param array $data - base params
	 * @param array|null $additional - additional data
	 * @return array
	 */
	protected static function prepareAdditionalFields(array $data, ?array $additional): array
	{
		if ($additional)
		{
			if (isset($additional['theme']) && $additional['theme'])
			{
				$color = $additional['theme'];
				if ($color[0] !== '#')
				{
					$color = '#' . $color;
				}
				$data['ADDITIONAL_FIELDS']['THEME_COLOR'] = $color;
				unset($data['ADDITIONAL_FIELDS']['THEME_CODE']);
				$data['ADDITIONAL_FIELDS']['THEME_USE'] = 'Y';
			}

			if (isset($additional['title']) && $additional['title'])
			{
				$data['TITLE'] = $additional['title'];
			}
		}

		//default widget value
		$buttons = B24button::getButtons();
		$buttonKeys = array_keys($buttons);
		if (!empty($buttonKeys))
		{
			$data['ADDITIONAL_FIELDS']['B24BUTTON_CODE'] = $buttonKeys[0];
		}
		else
		{
			$data['ADDITIONAL_FIELDS']['B24BUTTON_CODE'] = 'N';
		}
		//default site boost
		$data['ADDITIONAL_FIELDS']['SPEED_USE_WEBPACK'] = 'Y';
		$data['ADDITIONAL_FIELDS']['SPEED_USE_LAZY'] = 'Y';
		//default powered by b24
		$data['ADDITIONAL_FIELDS']['COPYRIGHT_SHOW'] = 'Y';
		$data['ADDITIONAL_FIELDS']['COPYRIGHT_CODE'] = Copyright::getRandomPhraseId();
		//default cookie
		if (in_array(Manager::getZone(), ['es', 'de', 'fr', 'it', 'pl', 'uk']))
		{
			$data['ADDITIONAL_FIELDS']['COOKIES_USE'] = 'Y';
		}

		return $data;
	}

	/**
	 * Sets replace array to the pending blocks.
	 * @param array $pendingIds Pending block ids.
	 * @param array $replace Array for future linking.
	 * @return void
	 */
	protected static function linkingPendingBlocks(array $pendingIds, array $replace): void
	{
		$replaceEncoded = base64_encode(serialize($replace));
		$res = BlockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ID' => $pendingIds
			]
		]);
		while ($row = $res->fetch())
		{
			$blockInstance = new Block($row['ID']);
			if ($blockInstance->exist())
			{
				$blockInstance->updateNodes([
					AppConfiguration::SYSTEM_COMPONENT_REST_PENDING => [
						'REPLACE' => $replaceEncoded
					]
				]);
				$blockInstance->save();
			}
		}
	}

	/**
	 * Updates added pages on new folder ids.
	 * @param int $siteId Site id.
	 * @param array $folderMapIds References between old and new folders.
	 * @return void
	 */
	protected static function updateFolderIds(int $siteId, array $folderMapIds): void
	{
		$res = LandingCore::getList([
			'select' => [
				'ID', 'FOLDER_ID'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'FOLDER_ID' => array_keys($folderMapIds)
			]
		]);
		while ($row = $res->fetch())
		{
			if (isset($folderMapIds[$row['FOLDER_ID']]))
			{
				LandingCore::update($row['ID'], [
					'FOLDER_ID' => $folderMapIds[$row['FOLDER_ID']]
				]);
			}
		}
	}

	/**
	 * Add folders and move pages to the folders.
	 * @param int $siteId Site id.
	 * @param array $foldersNew Folders' array to add.
	 * @param array $landingMapIds Landing's map from old to new ids.
	 * @return void
	 */
	protected static function addFolders(int $siteId, array $foldersNew, array $landingMapIds): void
	{
		if (!$foldersNew)
		{
			return;
		}

		$folderMapIds = [];
		foreach ($foldersNew as $folderId => $folder)
		{
			$indexId = null;

			if (!$folder['PARENT_ID'])
			{
				unset($folder['PARENT_ID']);
			}

			if ($folder['INDEX_ID'] ?? null)
			{
				$indexId = $landingMapIds[$folder['INDEX_ID']] ?? null;
				unset($folder['INDEX_ID']);
			}

			$res = SiteCore::addFolder($siteId, $folder);
			if ($res->isSuccess())
			{
				if ($indexId)
				{
					$resLanding = LandingCore::update($indexId, [
						'FOLDER_ID' => $res->getId()
					]);
					if ($resLanding->isSuccess())
					{
						\Bitrix\Landing\Folder::update($res->getId(), [
							'INDEX_ID' => $indexId
						]);
					}
				}
				$folderMapIds[$folderId] = $res->getId();
			}
		}

		$newFolders = SiteCore::getFolders($siteId);
		foreach ($newFolders as $folder)
		{
			if ($folderMapIds[$folder['PARENT_ID']] ?? null)
			{
				\Bitrix\Landing\Folder::update($folder['ID'], [
					'PARENT_ID' => $folderMapIds[$folder['PARENT_ID']]
				]);
			}
		}

		self::updateFolderIds($siteId, $folderMapIds);
	}

	/**
	 * Final step.
	 * @param Event $event
	 * @return array
	 */
	public static function onFinish(Event $event): array
	{
		$ratio = $event->getParameter('RATIO');
		$userId = $event->getParameter('USER_ID');

		if ($userId)
		{
			Rights::setContextUserId($userId);
		}

		if (isset($ratio['LANDING']))
		{
			Rights::setGlobalOff();
			$siteType = $ratio['LANDING']['TYPE'];
			$siteId = $ratio['LANDING']['SITE_ID'] ?? null;
			$blocks = $ratio['LANDING']['BLOCKS'] ?? null;
			$landings = $ratio['LANDING']['LANDINGS'] ?? [];
			$blocksPending = $ratio['LANDING']['BLOCKS_PENDING'] ?? null;
			$foldersRef = $ratio['LANDING']['FOLDERS_REF'] ?? null;
			$templatesOld = $ratio['LANDING']['TEMPLATES'] ?? [];
			$templateLinking = $ratio['LANDING']['TEMPLATE_LINKING'] ?? [];
			$specialPages = $ratio['LANDING']['SPECIAL_PAGES'] ?? null;
			$sysPages = $ratio['LANDING']['SYS_PAGES'] ?? null;
			$foldersNew = $ratio['LANDING']['FOLDERS_NEW'] ?? [];
			$additional = $event->getParameter('ADDITIONAL_OPTION') ?? [];

			// if import just page in existing site
			$isPageImport = false;
			$isReplaceLanding = isset($additional['replaceLid']) && (int)$additional['replaceLid'] > 0;
			if (
				(isset($additional['siteId']) && (int)$additional['siteId'] > 0)
				|| $isReplaceLanding
			)
			{
				$isPageImport = true;
			}

			// index page for multipage, or just once - for sigle page import
			$mainPageId = null;
			if (!empty($landings))
			{
				if ($isPageImport)
				{
					$mainPageId = reset($landings);
				}
				elseif (
					$siteId
					&& $specialPages
					&& $specialPages['LANDING_ID_INDEX']
					&& $landings[$specialPages['LANDING_ID_INDEX']]
				)
				{
					$mainPageId = $landings[$specialPages['LANDING_ID_INDEX']];
				}
			}

			Type::setScope($siteType);
			if ($blocksPending)
			{
				self::linkingPendingBlocks($blocksPending, [
					'block' => $blocks,
					'landing' => $landings
				]);
			}
			// replace links in blocks content
			if ($blocks)
			{
				$replace = [];
				ksort($blocks);
				ksort($landings);
				$blocks = array_reverse($blocks, true);
				$landings = array_reverse($landings, true);
				foreach ($blocks as $oldId => $newId)
				{
					$replace['/#block' . $oldId . '([^\d]{1})/'] = '#block' . $newId . '$1';
				}
				foreach ($landings as $oldId => $newId)
				{
					$replace['/#landing' . $oldId . '([^\d]{1})/'] = '#landing' . $newId . '$1';
				}

				$res = BlockTable::getList([
					'select' => [
						'ID', 'CONTENT'
					],
					'filter' => [
						'ID' => array_values($blocks),
						'!ID' => $blocksPending
					]
				]);
				while ($row = $res->fetch())
				{
					$count = 0;
					$row['CONTENT'] = preg_replace(
						array_keys($replace),
						array_values($replace),
						$row['CONTENT'],
						-1,
						$count
					);
					if ($count)
					{
						BlockTable::update($row['ID'], [
							'CONTENT' => $row['CONTENT']
						]);
					}
				}
			}

			if (!$isPageImport)
			{
				// move pages to the folders if needed (backward compatibility)
				if ($foldersRef)
				{
					$res = LandingCore::getList([
						'select' => [
							'ID', 'FOLDER_ID',
						],
						'filter' => [
							'SITE_ID' => $siteId,
							'FOLDER_ID' => array_keys($foldersRef),
						],
					]);
					while ($row = $res->fetch())
					{
						LandingCore::update($row['ID'], [
							'FOLDER_ID' => $foldersRef[$row['FOLDER_ID']],
						]);
					}
				}
				// add folders and move pages (new format)
				self::addFolders($siteId, $foldersNew, $landings);
				// gets actual layouts
				$templatesNew = [];
				$templatesRefs = [];
				$res = Template::getList([
					'select' => [
						'ID', 'XML_ID',
					],
				]);
				while ($row = $res->fetch())
				{
					$templatesNew[$row['XML_ID']] = $row['ID'];
				}
				foreach ($templatesOld as $oldId => $oldXmlId)
				{
					if (is_string($oldXmlId) && isset($templatesNew[$oldXmlId]))
					{
						$templatesRefs[$oldId] = $templatesNew[$oldXmlId];
					}
				}

				// set layouts to site and landings
				foreach ($templateLinking as $entityId => $templateItem)
				{
					$tplId = $templateItem['TPL_ID'];
					$tplRefs = [];
					if (isset($templatesRefs[$tplId]))
					{
						$tplId = $templatesRefs[$tplId];
						foreach ($templateItem['TEMPLATE_REF'] as $areaId => $landingId)
						{
							if (intval($landingId) && isset($landings[$landingId]))
							{
								$tplRefs[$areaId] = $landings[$landingId];
							}
						}
						if ($entityId < 0)
						{
							SiteCore::update(-1 * $entityId, [
								'TPL_ID' => $tplId,
							]);
							TemplateRef::setForSite(-1 * $entityId, $tplRefs);
						}
						else
						{
							LandingCore::update($entityId, [
								'TPL_ID' => $tplId,
							]);
							TemplateRef::setForLanding($entityId, $tplRefs);
						}
					}
				}

				// replace special pages in site (503, 404)
				if ($specialPages && $siteId)
				{
					foreach ($specialPages as $code => $id)
					{
						$specialPages[$code] = isset($landings[$id]) ? $landings[$id] : 0;
					}
					SiteCore::update($siteId, $specialPages);
				}

				// system pages
				if (is_array($sysPages) && $siteId)
				{
					foreach ($sysPages as $sysPage)
					{
						if (isset($landings[$sysPage['LANDING_ID']]))
						{
							Syspage::set($siteId, $sysPage['TYPE'], $landings[$sysPage['LANDING_ID']]);
						}
					}
				}
			}

			//set default additional fields for page
			if ($mainPageId && !$isReplaceLanding)
			{
				self::setAdditionalPageFields($mainPageId, $additional);
			}

			Rights::setGlobalOn();

			// link for "go to site" button
			$linkAttrs = [
				'class' => 'ui-btn ui-btn-md ui-btn-success ui-btn-round',
				'data-is-site' => 'Y',
				'data-site-id' => $siteId,
				'href' => '#' . $siteId,
				'target' => '_top'
			];
			if ($mainPageId)
			{
				$linkAttrs['data-is-landing'] = 'Y';
				$linkAttrs['data-landing-id'] = $mainPageId;
			}
			$linkText = Loc::getMessage('LANDING_IMPORT_FINISH_GOTO_SITE');
			if ($siteType === 'KNOWLEDGE')
			{
				$linkAttrs['href'] = \Bitrix\Landing\Site::getPublicUrl($siteId);
			}
			elseif ($siteType === 'PAGE' && empty($additional))
			{
				$linkText = Loc::getMessage('LANDING_IMPORT_FINISH_GOTO_PAGE');
				$url = Manager::getOption('tmp_last_show_url', '');
				if ($url === '' && ModuleManager::isModuleInstalled('bitrix24'))
				{
					$linkAttrs['href'] = '/sites/';
				}
				elseif ($url !== '')
				{
					$linkAttrs['href'] = str_replace(
						[
							'#site_show#',
							'#landing_edit#',
						],
						[
							$siteId,
							$siteId,
						],
						$url
					);
				}
			}

			if ($isReplaceLanding)
			{
				$linkAttrs['data-replace-lid'] = (int)$additional['replaceLid'];
			}

			$domList = [
				[
					'TAG' => 'a',
					'DATA' => [
						'attrs' => $linkAttrs,
						'text' => $linkText,
					]
				]
			];

			if (mb_strpos($linkAttrs['href'], '#') !== 0)
			{
				$domList[] = [
					'TAG' => 'script',
					'DATA' => [
						'html' => "top.window.location.href='" . $linkAttrs['href'] . "'",
					]
				];
			}

			if (
				!empty($additional)
				&& array_key_exists('st_category', $additional)
				&& array_key_exists('st_event', $additional)
			)
			{
				$analyticData = [
					'category' => $additional['st_category'],
					'event' => $additional['st_event'],
					'status' => 'success',
				];
				if (array_key_exists('st_section', $additional))
				{
					$analyticData['c_section'] = $additional['st_section'];
				}
				if (array_key_exists('st_element', $additional))
				{
					$analyticData['c_element'] = $additional['st_element'];
				}
				if (array_key_exists('app_code', $additional))
				{
					$analyticData['params'] = [
						'appCode' => $additional['app_code'],
					];
				}

				$script = "if (typeof BX.Landing.Metrika !== 'undefined') {";
				$script  .= "const metrika = new BX.Landing.Metrika(true);";
				$script  .= "metrika.sendData(" . \CUtil::PhpToJSObject($analyticData) . ")";
				$script  .= "}";
				$domList[] = [
					'TAG' => 'script',
					'DATA' => [
						'html' => $script,
					]
				];
			}

			return [
				'CREATE_DOM_LIST' => $domList,
				'ADDITIONAL' => [
					'id' => $siteId,
					'publicUrl' => \Bitrix\Landing\Site::getPublicUrl($siteId),
					'imageUrl' => Manager::getUrlFromFile(\Bitrix\Landing\Site::getPreview($siteId)),
				]
			];
		}

		Rights::setGlobalOn();

		return [];
	}

	protected static function setAdditionalPageFields($landingId, array $additional): void
	{
		$additionalFields = [];

		// set Title and Description to mainpage
		if (!empty($additional))
		{
			if (isset($additional['title']))
			{
				$additionalFields['METAMAIN_TITLE'] = $additional['title'];
				$additionalFields['METAOG_TITLE'] = $additional['title'];

				LandingCore::update($landingId, [
					'TITLE' => $additional['title']
				]);
			}
			if (isset($additional['description']))
			{
				$additionalFields['METAMAIN_DESCRIPTION'] = $additional['description'];
				$additionalFields['METAOG_DESCRIPTION'] = $additional['description'];
			}
		}

		LandingCore::saveAdditionalFields($landingId, $additionalFields);
	}
}