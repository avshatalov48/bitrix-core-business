<?php
namespace Bitrix\Landing;

use \Bitrix\Landing\Internals\HookDataTable as HookData;

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
					$classes[] = strtoupper(pathinfo($entry, PATHINFO_FILENAME));
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
	public static function getData($id, $type, $asIs = false)
	{
		$data = array();
		$res = HookData::getList(array(
			'select' => array(
				'ID', 'HOOK', 'CODE', 'VALUE'
			),
			'filter' => array(
				'ENTITY_ID' => $id,
				'=ENTITY_TYPE' => $type
			)
		));
		while ($row = $res->fetch())
		{
			if (!isset($data[$row['HOOK']]))
			{
				$data[$row['HOOK']] = array();
			}
			if (strpos($row['VALUE'], 'serialized#') === 0)
			{
				$row['VALUE'] = unserialize(substr($row['VALUE'], 11));
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
	 * @return \Bitrix\Landing\Hook\Page[]
	 */
	protected static function getList($id, $type, array $data = array())
	{
		$hooks = array();
		$classDir = self::HOOKS_PAGE_DIR;
		$classNamespace = self::HOOKS_NAMESPACE;

		// first read all hooks in base dir
		foreach (self::getClassesFromDir($classDir) as $class)
		{
			$classFull = __NAMESPACE__  . $classNamespace . $class;
			if (class_exists($classFull))
			{
				$hooks[$class] = new $classFull(
					self::$editMode,
					!($type == self::ENTITY_TYPE_SITE)
				);
				if (!$hooks[$class]->active())
				{
					unset($hooks[$class]);
				}
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
	 * @return void
	 */
	public static function setEditMode()
	{
		self::$editMode = true;
	}

	/**
	 * Get hooks for site.
	 * @param int $id Site id.
	 * @return array
	 */
	public static function getForSite($id)
	{
		return self::getList($id, self::ENTITY_TYPE_SITE);
	}

	/**
	 * Get hooks for landing.
	 * @param int $id Landing id.
	 * @return array
	 */
	public static function getForLanding($id)
	{
		return self::getList($id, self::ENTITY_TYPE_LANDING);
	}

	/**
	 * Copy data for entity.
	 * @param int $from From entity id.
	 * @param int $to To entity id.
	 * @param string $type Entity type.
	 * @return void
	 */
	protected static function copy($from, $to, $type)
	{
		$data = self::getData($from, $type);
		foreach ($data as $hookCode => $items)
		{
			foreach ($items as $code => $value)
			{
				HookData::add(array(
					'ENTITY_ID' => $to,
					'ENTITY_TYPE' => $type,
					'HOOK' => $hookCode,
					'CODE' => $code,
					'VALUE' => $value
				));
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
		self::copy($from, $to, self::ENTITY_TYPE_SITE);
	}

	/**
	 * Copy data for landing.
	 * @param int $from From landing id.
	 * @param int $to To landing id.
	 * @return void
	 */
	public static function copyLanding($from, $to)
	{
		self::copy($from, $to, self::ENTITY_TYPE_LANDING);
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
			if (strpos($code, '_') !== false)
			{
				$codeHook = substr($code, 0, strpos($code, '_'));
				$codeVal = substr($code, strpos($code, '_') + 1);
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
		$enableHook = Manager::checkFeature(Manager::FEATURE_ENABLE_ALL_HOOKS);

		// get hooks with new new data (not saved yet)
		foreach ($hooks as $hook)
		{
			if (!$hook->isFree() && !$enableHook)
			{
				continue;
			}
			$codeHook = $hook->getCode();
			// modify $dataSave ...
			foreach ($hook->getFields() as $field)
			{
				$codeVal = $field->getCode();
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
	 * Set data hooks for site.
	 * @param int $id Site id.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveForSite($id, array $data)
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
			self::saveData($id, self::ENTITY_TYPE_SITE, $data);
		}
	}

	/**
	 * Get hooks for landing.
	 * @param int $id Landing id.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveForLanding($id, array $data)
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
			self::saveData($id, self::ENTITY_TYPE_LANDING, $data);
		}
	}

	/**
	 * Delete data hooks for entity.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @return void
	 */
	protected static function deleteData($id, $type)
	{
		foreach (self::getData($id, $type, true) as $row)
		{
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