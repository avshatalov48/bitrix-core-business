<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * @see \Bitrix\Main\ORM\Objectify\EntityObject
 */
class PathIndex
	extends Index\Internals\EO_PathIndex
{

	/**
	 *  Loads pathIndex by its path.
	 *
	 * @param string $path Path to search index.
	 *
	 * @return self|null
	 */
	public static function loadByPath($path): ?self
	{
		$path = '/'. \trim($path, '/');
		$path = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');

		/** @var Translate\Index\PathIndex $indexPath */
		$indexPath = Translate\Index\Internals\PathIndexTable::getList(['filter' => ['=PATH' => $path]])->fetchObject();

		// if it is lang folder when find the lang/#LANG_ID# folder
		if (
			$indexPath instanceof Translate\Index\PathIndex
			&& $indexPath->getIsLang()
			&& $indexPath->getName() === 'lang'
		)
		{
			$topPathRes = Translate\Index\Internals\PathIndexTable::getList([
				'filter' => [
					'=NAME' => '#LANG_ID#',
					'=DEPTH_LEVEL' => $indexPath->getDepthLevel() + 1,
					'=DESCENDANTS.PARENT_ID' => $indexPath->getId(),//ancestor
				],
			]);
			$indexPath = $topPathRes->fetchObject();
		}

		return $indexPath;
	}


	/**
	 * Detects and returns module id from a path.
	 *
	 * @return string|null
	 */
	public function detectModuleId(): ?string
	{
		$arr = \explode('/', $this->getPath());
		$pos = \array_search('modules', $arr);
		if ($pos !== false)
		{
			if ($arr[$pos - 1] === 'bitrix' && !empty($arr[$pos + 1]))
			{
				return $arr[$pos + 1];
			}
		}

		return null;
	}

	/**
	 * Detects assignment id from a path.
	 *
	 * @return string|null
	 */
	public function detectAssignment(): ?string
	{
		$path = $this->getPath();

		// /bitrix/mobileapp/[moduleName]
		// /bitrix/templates/[templateName]
		// /bitrix/components/bitrix/[componentName]
		// /bitrix/activities/bitrix/[activityName]
		// /bitrix/wizards/bitrix/[wizardsName]
		// /bitrix/gadgets/bitrix/[gadgetName]
		// /bitrix/js/[moduleName]/[smth]
		foreach (Translate\ASSIGNMENT_TYPES as $testEntry)
		{
			$testPath = '/bitrix/'. $testEntry;
			if (\mb_strpos($path, $testPath.'/') === 0 || $path == $testPath)
			{
				return $testEntry;
			}
		}

		$assignment = null;

		$moduleName = $this->detectModuleId();

		if ($moduleName !== null)
		{
			$assignment = 'modules';

			foreach (Translate\ASSIGNMENT_TYPES as $testEntry)
			{
				// /bitrix/modules/[moduleName]/install/mobileapp/[moduleName]
				// /bitrix/modules/[moduleName]/install/templates/[templateName]
				// /bitrix/modules/[moduleName]/install/components/bitrix/[componentName]
				// /bitrix/modules/[moduleName]/install/activities/bitrix/[activityName]
				// /bitrix/modules/[moduleName]/install/wizards/bitrix/[wizardsName]
				// /bitrix/modules/[moduleName]/install/gadgets/bitrix/[gadgetName]
				// /bitrix/modules/[moduleName]/install/js/[moduleName]/[smth]
				$testPath = '/bitrix/modules/'.$moduleName.'/install/'. $testEntry;
				if (\mb_strpos($path, $testPath.'/') === 0 || $path == $testPath)
				{
					return $testEntry;
				}
				if ($testEntry == 'templates')
				{
					// /bitrix/modules/[moduleName]/install/public/templates/[templateName]
					$testPath = '/bitrix/modules/'.$moduleName.'/install/public/'. $testEntry;
					if (\mb_strpos($path, $testPath.'/') === 0 || $path == $testPath)
					{
						return $testEntry;
					}
				}
				// /bitrix/modules/[moduleName]/install/bitrix/templates/[templateName]
				$testPath = '/bitrix/modules/'.$moduleName.'/install/bitrix/'. $testEntry;
				if (\mb_strpos($path, $testPath.'/') === 0 || $path == $testPath)
				{
					return $testEntry;
				}
				// /bitrix/modules/[moduleName]/install/public/templates/[templateName]
				/*$testPath = '/bitrix/modules/'.$moduleName.'/install/public/'. $testEntry;
				if (\mb_strpos($path, $testPath. '/') === 0 || $path == $testPath)
				{
					return $testEntry;
				}*/
				// /bitrix/modules/[moduleName]/lang/#LANG_ID#/[smth]
				$testPath = '/bitrix/modules/'.$moduleName.'/lang/#LANG_ID#/'. $testEntry;
				if (\mb_strpos($path, $testPath.'/') === 0 || $path == $testPath)
				{
					return $testEntry;
				}
				// /bitrix/modules/[moduleName]/lang/#LANG_ID#/install/[smth]
				$testPath = '/bitrix/modules/'.$moduleName.'/lang/#LANG_ID#/install/'. $testEntry;
				if (\mb_strpos($path, $testPath.'/') === 0 || $path == $testPath)
				{
					return $testEntry;
				}

				// /bitrix/modules/[moduleName]/handlers/delivery/[smth]
				// /bitrix/modules/[moduleName]/handlers/paysystem/[smth]
				$testPath = '/bitrix/modules/'.$moduleName.'/handlers/'. $testEntry;
				if (\mb_strpos($path, $testPath.'/') === 0 || $path == $testPath)
				{
					return $testEntry;
				}


				// /bitrix/modules/[moduleName]/payment/[paymentHandler]
			}
		}

		return $assignment;
	}
}
