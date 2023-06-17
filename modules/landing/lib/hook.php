<?php
namespace Bitrix\Landing;

use Bitrix\Landing\Hook\Page;
use \Bitrix\Landing\Internals\HookDataTable as HookData;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;

class Hook
{
	/**
	 * If true, hook work in edit mode (form settings).
	 * @var boolean
	 */
	protected static $editMode = false;

	/**
	 * Entity type site.
	 */
	const ENTITY_TYPE_SITE = 'S';

	/**
	 * Entity type landing.
	 */
	const ENTITY_TYPE_LANDING = 'L';

	/**
	 * Dir of repository of common hooks.
	 */
	const HOOKS_PAGE_DIR = '/bitrix/modules/landing/lib/hook/page';

	/**
	 * Namespace of repoitory of common hooks (relative current).
	 */
	const HOOKS_NAMESPACE = '\\Hook\\Page\\';

	/**
	 * Handler for copy event
	 */
	protected const HOOKS_ON_COPY_HANDLER = 'onCopy';

	/**
	 * Hook codes which contains file ids.
	 */
	const HOOKS_CODES_FILES = [
		'METAOG_IMAGE',
		'BACKGROUND_PICTURE'
	];

	/**
	 * Get classes from dir.
	 * @param string $dir Relative dir.
	 * @return array
	 */
	protected static function getClassesFromDir($dir)
	{
		$classes = array();

		$path = Manager::getDocRoot() . $dir;
		if (($handle = opendir($path)))
		{
			while ((($entry = readdir($handle)) !== false))
			{
				if ($entry != '.' && $entry != '..')
				{
					$classes[] = mb_strtoupper(pathinfo($entry, PATHINFO_FILENAME));
				}
			}
		}

		return $classes;
	}

	/**
	 * Get data by entity id ant type.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @param boolean $asIs Return row as is.
	 * @return array
	 */
	public static function getData($id, $type, $asIs = false): array
	{
		$data = [];
		$id = (int)$id;

		if (!is_string($type))
		{
			return $data;
		}

		$res = HookData::getList([
			'select' => [
				'ID', 'HOOK', 'CODE', 'VALUE'
			],
			'filter' => [
				'ENTITY_ID' => $id,
				'=ENTITY_TYPE' => $type,
				'=PUBLIC' => self::$editMode ? 'N' : 'Y'
			],
			'order' => [
				'ID' => 'asc'
			]
		]);
		while ($row = $res->fetch())
		{
			if (!isset($data[$row['HOOK']]))
			{
				$data[$row['HOOK']] = [];
			}
			if (mb_strpos($row['VALUE'], 'serialized#') === 0)
			{
				$row['VALUE'] = unserialize(mb_substr($row['VALUE'], 11), ['allowed_classes' => false]);
			}
			$data[$row['HOOK']][$row['CODE']] = $asIs ? $row : $row['VALUE'];
		}

		return $data;
	}

	/**
	 * Get available hooks for this landing.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @param array $data Data array (optional).
	 * @return Page[]
	 */
	protected static function getList($id, $type, array $data = array())
	{
		$hooks = array();
		$classDir = self::HOOKS_PAGE_DIR;
		$classNamespace = self::HOOKS_NAMESPACE;
		$excludedHooks = \Bitrix\Landing\Site\Type::getExcludedHooks();

		// first read all hooks in base dir
		foreach (self::getClassesFromDir($classDir) as $class)
		{
			if (in_array($class, $excludedHooks))
			{
				continue;
			}
			$classFull = __NAMESPACE__  . $classNamespace . $class;
			if (class_exists($classFull))
			{
				$hooks[$class] = new $classFull(
					self::$editMode,
					!($type == self::ENTITY_TYPE_SITE)
				);
			}
		}

		// sort hooks
		uasort($hooks, function($a, $b)
		{
			if ($a->getSort() == $b->getSort())
			{
				return 0;
			}
			return ($a->getSort() < $b->getSort()) ? -1 : 1;
		});

		// check custom exec
		$event = new Event('landing', 'onHookExec');
		$event->send();
		foreach ($event->getResults() as $result)
		{
			if ($result->getType() != EventResult::ERROR)
			{
				if ($customExec = $result->getModified())
				{
					foreach ((array)$customExec as $code => $itemExec)
					{
						$code = mb_strtoupper($code);
						if (isset($hooks[$code]) && is_callable($itemExec))
						{
							$hooks[$code]->setCustomExec($itemExec);
						}
					}
					unset($code, $itemExec);
				}
				unset($customExec);
			}
		}
		unset($event, $result);

		// then fill hook with data
		if (!empty($hooks) && $id > 0)
		{
			if (empty($data))
			{
				$data = self::getData($id, $type);
			}
			foreach ($hooks as $code => $hook)
			{
				if (isset($data[$code]))
				{
					$hook->setData($data[$code]);
				}
			}
		}

		return $hooks;
	}

	/**
	 * Set edit mode to true.
	 * @param bool $mode Edit mode (true by default).
	 * @return void
	 */
	public static function setEditMode(bool $mode = true): void
	{
		self::$editMode = $mode;
	}

	/**
	 * Returns edit mode state.
	 * @return bool
	 */
	public static function getEditMode(): bool
	{
		return self::$editMode;
	}

	/**
	 * Get hooks for site.
	 * @param int $id Site id.
	 * @return Page[]
	 */
	public static function getForSite($id)
	{
		if (!Landing::getEditMode())
		{
			static $hooks = [];
		}
		else
		{
			$hooks = [];
		}

		if (!array_key_exists($id, $hooks))
		{
			$hooks[$id] = self::getList($id, self::ENTITY_TYPE_SITE);
		}

		return $hooks[$id];
	}

	/**
	 * Get hooks for landing.
	 * @param int $id Landing id.
	 * @return Page[]
	 */
	public static function getForLanding($id)
	{
		if (!Landing::getEditMode())
		{
			static $hooks = [];
		}
		else
		{
			$hooks = [];
		}

		if (!array_key_exists($id, $hooks))
		{
			$hooks[$id] = self::getList($id, self::ENTITY_TYPE_LANDING);
		}

		return $hooks[$id];
	}

	/**
	 * Get row hooks for landing.
	 * @param int $id Landing id.
	 * @return array
	 */
	public static function getForLandingRow($id)
	{
		return self::getData($id, self::ENTITY_TYPE_LANDING);
	}

	/**
	 * Copy data for entity.
	 * @param int $from From entity id.
	 * @param int $to To entity id.
	 * @param string $type Entity type.
	 * @param bool $publication It's not copy, but publication.
	 * @return void
	 */
	protected static function copy($from, $to, $type, $publication = false)
	{
		$from = (int)$from;
		$to = (int)$to;
		$data = self::getData($from, $type);
		$existData = [];

		$classDir = self::HOOKS_PAGE_DIR;
		$classNamespace = self::HOOKS_NAMESPACE;
		$excludedHooks = \Bitrix\Landing\Site\Type::getExcludedHooks();

		// first read all hooks in base dir
		foreach (self::getClassesFromDir($classDir) as $class)
		{
			if (in_array($class, $excludedHooks, true))
			{
				continue;
			}
			$classFull = __NAMESPACE__  . $classNamespace . $class;
			if (
				isset($data[$class])
				&& method_exists($classFull, self::HOOKS_ON_COPY_HANDLER)
			)
			{
				$handler = self::HOOKS_ON_COPY_HANDLER;
				if ($preparedData = $classFull::$handler($data[$class], $from, $type, $publication))
				{
					$data[$class] = $preparedData;
				}
			}
		}

		// collect exist data
		if ($data)
		{
			$res = HookData::getList([
				'select' => [
					'ID', 'HOOK', 'CODE'
				],
				'filter' => [
					'ENTITY_ID' => $to,
					'=ENTITY_TYPE' => $type,
					'=PUBLIC' => $publication ? 'Y' : 'N'
				]
			]);
			while ($row = $res->fetch())
			{
				$existData[$row['HOOK'] . '_' . $row['CODE']] = $row['ID'];
			}
		}

		// update existing keys or add new
		foreach ($data as $hookCode => $items)
		{
			foreach ($items as $code => $value)
			{
				$existKey = $hookCode . '_' . $code;
				if (is_array($value))
				{
					$value = 'serialized#' . serialize($value);
				}
				if (array_key_exists($existKey, $existData))
				{
					HookData::update($existData[$existKey], [
						'VALUE' => $value
					]);
					unset($existData[$existKey]);
				}
				else
				{
					HookData::add([
						'ENTITY_ID' => $to,
						'ENTITY_TYPE' => $type,
						'HOOK' => $hookCode,
						'CODE' => $code,
						'VALUE' => $value,
						'PUBLIC' => $publication ? 'Y' : 'N'
					]);
				}
			}
		}

		// delete unused data
		if ($existData)
		{
			foreach ($existData as $delId)
			{
				HookData::delete($delId);
			}
		}
	}

	/**
	 * Copy data for site.
	 * @param int $from From site id.
	 * @param int $to To site id.
	 * @return void
	 */
	public static function copySite($from, $to)
	{
		$originalEditMode = self::$editMode;
		if (!self::$editMode)
		{
			self::$editMode = true;
		}
		self::copy($from, $to, self::ENTITY_TYPE_SITE);
		self::$editMode = $originalEditMode;
	}

	/**
	 * Copy data for landing.
	 * @param int $from From landing id.
	 * @param int $to To landing id.
	 * @return void
	 */
	public static function copyLanding($from, $to)
	{
		$originalEditMode = self::$editMode;
		if (!self::$editMode)
		{
			self::$editMode = true;
		}
		self::copy($from, $to, self::ENTITY_TYPE_LANDING);
		self::$editMode = $originalEditMode;
	}

	/**
	 * Publication data for site.
	 * @param int $siteId Site id.
	 * @return void
	 */
	public static function publicationSite($siteId)
	{
		self::copy($siteId, $siteId, self::ENTITY_TYPE_SITE, true);
	}

	/**
	 * Publication data for landing.
	 * @param int $lid Landing id.
	 * @return void
	 */
	public static function publicationLanding($lid)
	{
		self::copy($lid, $lid, self::ENTITY_TYPE_LANDING, true);
	}

	/**
	 * In disable autobulication option we must skip hooks, then required page publication.
	 * @param $siteId
	 * @return void
	 */
	public static function publicationSiteWithSkipNeededPublication($siteId): void
	{
		self::publicationWithSkipNeededPublication($siteId, self::ENTITY_TYPE_SITE);
	}

	/**
	 * In disable autobulication option we must skip hooks, then required page publication.
	 * @param $landingId
	 * @return void
	 */
	public static function publicationLandingWithSkipNeededPublication($landingId): void
	{
		self::publicationWithSkipNeededPublication($landingId, self::ENTITY_TYPE_LANDING);
	}

	protected static function publicationWithSkipNeededPublication($id, $type): void
	{
		$editModeBack = self::$editMode;
		self::$editMode = false;
		$publicData = self::getData($id, $type, true);
		self::$editMode = $editModeBack;

		if ($type === self::ENTITY_TYPE_SITE)
		{
			self::publicationSite($id);
		}
		if ($type === self::ENTITY_TYPE_LANDING)
		{
			self::publicationLanding($id);
		}
		$data = self::getData($id, $type, true);

		// return previously public values
		$needClearCache = false;
		foreach (self::getList($id, $type) as $hook)
		{
			if ($hook->isNeedPublication())
			{
				$fieldsToDelete = [];
				if (isset($publicData[$hook->getCode()]))
				{
					foreach ($data[$hook->getCode()] as $fieldCode => $field)
					{
						if (!isset($publicData[$hook->getCode()][$fieldCode]))
						{
							$fieldsToDelete[$fieldCode] = $field;
						}
						elseif ($publicData[$hook->getCode()][$fieldCode]['VALUE'] !== $field['VALUE'])
						{
							$needClearCache = true;
							HookData::update($field['ID'],
								[
									'VALUE' => $field['VALUE'],
								]
							);
						}
					}
				}
				else
				{
					$fieldsToDelete = $data[$hook->getCode()] ?? [];
				}

				// del if not exists in public
				if (!empty($fieldsToDelete))
				{
					$needClearCache = true;
					foreach ($fieldsToDelete as $fieldCode => $field)
					{
						$res = HookData::getList([
							'select' => ['ID'],
							'filter' => [
								'ENTITY_ID' => $id,
								'=ENTITY_TYPE' => $type,
								'=HOOK' => $hook->getCode(),
								'=CODE' => $fieldCode,
								'=PUBLIC' => 'Y'
							]
						]);
						if ($row = $res->fetch())
						{
							HookData::delete($row['ID']);
						}
					}
				}
			}
		}

		// drop public cache
		if ($needClearCache)
		{
			if ($type === self::ENTITY_TYPE_SITE)
			{
				$landings = Landing::getList([
					'select' => ['ID'],
					'filter' => [
						'SITE_ID' => $id,
						'=PUBLIC' => 'Y',
						'=DELETED' => 'N',
					],
				]);
				while ($landing = $landings->fetch())
				{
					Landing::update($landing['ID'], ['PUBLIC' => 'N']);
				}
			}
			if ($type === self::ENTITY_TYPE_LANDING)
			{
				Landing::update($id, ['PUBLIC' => 'N']);
			}
		}
	}

	/**
	 * Prepare data for save in hooks.
	 * @param array $data Input data.
	 * @return array
	 */
	protected static function prepareData(array $data)
	{
		$newData = array();

		foreach ($data as $code => $val)
		{
			if (mb_strpos($code, '_') !== false)
			{
				$codeHook = mb_substr($code, 0, mb_strpos($code, '_'));
				$codeVal = mb_substr($code, mb_strpos($code, '_') + 1);
				if (!isset($newData[$codeHook]))
				{
					$newData[$codeHook] = array();
				}
				$newData[$codeHook][$codeVal] = $val;
			}
		}

		return $newData;
	}

	/**
	 * Set data hooks for entity.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @param array $data Data array.
	 * @return void
	 */
	protected static function saveData($id, $type, array $data)
	{
		$data = self::prepareData($data);
		$hooks = self::getList($id, $type, $data);
		$dataSave = self::getData($id, $type, true);

		// get hooks with new new data (not saved yet)
		foreach ($hooks as $hook)
		{
			$hookLocked = $hook->isLocked();
			$codeHook = $hook->getCode();
			// modify $dataSave ...
			foreach ($hook->getFields() as $field)
			{
				$codeVal = $field->getCode();
				if ($hookLocked && !$field->isEmptyValue())
				{
					continue;
				}
				if (!isset($data[$codeHook][$codeVal]))
				{
					continue;
				}
				// ... for changed
				if (isset($dataSave[$codeHook][$codeVal]))
				{
					$dataSave[$codeHook][$codeVal]['CHANGED'] = true;
					$dataSave[$codeHook][$codeVal]['VALUE'] = $field->getValue();
				}
				// ... and new fields
				else
				{
					if (!isset($dataSave[$codeHook]))
					{
						$dataSave[$codeHook] = array();
					}
					$dataSave[$codeHook][$codeVal] = array(
						'HOOK' => $codeHook,
						'CODE' => $codeVal,
						'VALUE' => $field->getValue()
					);
				}
				if (is_array($dataSave[$codeHook][$codeVal]['VALUE']))
				{
					$dataSave[$codeHook][$codeVal]['VALUE'] = 'serialized#' . serialize(
						$dataSave[$codeHook][$codeVal]['VALUE']
					);
				}
			}
		}

		// now save the data
		foreach ($dataSave as $codeHook => $dataHook)
		{
			foreach ($dataHook as $code => $row)
			{
				if (
					is_array($row['VALUE']) && empty($row['VALUE'])
					||
					!is_array($row['VALUE']) && trim($row['VALUE']) == ''
				)
				{
					if (isset($row['ID']))
					{
						HookData::delete($row['ID']);
					}
				}
				else
				{
					if (!isset($row['ID']))
					{
						$row['ENTITY_ID'] = $id;
						$row['ENTITY_TYPE'] = $type;
						HookData::add($row);
					}
					elseif (isset($row['CHANGED']) && $row['CHANGED'])
					{
						$updId = $row['ID'];
						unset($row['ID'], $row['CHANGED']);
						HookData::update($updId, $row);
					}
				}
			}
		}
	}

	/**
	 * Index hook's content for entities.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @return void
	 */
	protected static function indexContent($id, $type)
	{
		$id = intval($id);

		if ($type == self::ENTITY_TYPE_LANDING)
		{
			$class = '\Bitrix\Landing\Landing';
		}

		if (!isset($class))
		{
			return;
		}

		// base fields
		$searchContent = $class::getList([
			'select' => [
				'TITLE', 'DESCRIPTION'
			],
			'filter' => [
				'ID' => $id,
				'=DELETED' => ['Y', 'N'],
				'=SITE.DELETED' => ['Y', 'N']
			]
		])->fetch();
		if (!$searchContent)
		{
			return;
		}

		$searchContent = array_values($searchContent);

		// hook fields
		foreach (self::getList($id, $type) as $hook)
		{
			foreach ($hook->getFields() as $field)
			{
				if ($field->isSearchable())
				{
					$searchContent[] = $field->getValue();
				}
			}
		}

		$searchContent = array_unique($searchContent);
		$searchContent = $searchContent ? implode(' ', $searchContent) : '';
		$searchContent = trim($searchContent);

		if ($searchContent)
		{
			$res = $class::update($id, [
				'SEARCH_CONTENT' => $searchContent
			]);
			$res->isSuccess();
		}
	}

	/**
	 * Set data hooks for site.
	 * @param int $id Site id.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveForSite(int $id, array $data): void
	{
		$check = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ID' => $id
			]
		])->fetch();
		if ($check)
		{
			$editModeBack = self::$editMode;
			self::$editMode = true;
			self::saveData($id, self::ENTITY_TYPE_SITE, $data);
			if (Manager::getOption('public_hook_on_save') === 'Y')
			{
				self::publicationSiteWithSkipNeededPublication($id);
			}
			self::$editMode = $editModeBack;
		}
	}

	/**
	 * Get hooks for landing.
	 * @param int $id Landing id.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveForLanding(int $id, array $data): void
	{
		$check = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ID' => $id
			]
		])->fetch();
		if ($check)
		{
			$editModeBack = self::$editMode;
			self::$editMode = true;
			self::saveData($id, self::ENTITY_TYPE_LANDING, $data);
			self::indexContent($id, self::ENTITY_TYPE_LANDING);
			if (Manager::getOption('public_hook_on_save') === 'Y')
			{
				self::publicationLandingWithSkipNeededPublication($id);
			}
			self::$editMode = $editModeBack;
		}
	}

	/**
	 * Index hook's content for landing.
	 * @param int $id Landing id.
	 * @return void
	 */
	public static function indexLanding($id)
	{
		self::indexContent($id, self::ENTITY_TYPE_LANDING);
	}

	/**
	 * Delete data hooks for entity.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @return void
	 */
	protected static function deleteData($id, $type)
	{
		$id = intval($id);

		$res = HookData::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'ENTITY_ID' => $id,
				'=ENTITY_TYPE' => $type
			)
		));
		while ($row = $res->fetch())
		{
			HookData::delete($row['ID']);
		}
	}

	/**
	 * Delete data hooks for site.
	 * @param int $id Landing id.
	 * @return void
	 */
	public static function deleteForSite($id)
	{
		self::deleteData($id, self::ENTITY_TYPE_SITE);
	}

	/**
	 * Delete data hooks for landing.
	 * @param int $id Landing id.
	 * @return void
	 */
	public static function deleteForLanding($id)
	{
		self::deleteData($id, self::ENTITY_TYPE_LANDING);
	}
}
