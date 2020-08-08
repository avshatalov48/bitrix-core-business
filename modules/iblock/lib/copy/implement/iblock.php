<?php
namespace Bitrix\Iblock\Copy\Implement;

use Bitrix\Iblock\Copy\Implement\Children\Child;
use Bitrix\Iblock\Copy\Implement\Children\Element as ElementChild;
use Bitrix\Iblock\Copy\Implement\Children\Field as FieldChild;
use Bitrix\Iblock\Copy\Implement\Children\Section as SectionChild;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Iblock extends CopyImplementer
{
	const IBLOCK_COPY_ERROR = "IBLOCK_COPIER_ERROR";

	private $targetIblockTypeId = "";
	private $targetSocnetGroupId = 0;

	/**
	 * @var \CCacheManager|null
	 */
	protected $cacheManager;

	private $child = [];

	/**
	 * Writes child implementer to the copy queue.
	 *
	 * @param Child $child Child implementer.
	 */
	public function setChild(Child $child)
	{
		$this->child[] = $child;
	}

	public function setTargetIblockTypeId($targetIblockTypeId)
	{
		$this->targetIblockTypeId = $targetIblockTypeId;
	}

	public function setTargetSocnetGroupId($targetSocnetGroupId)
	{
		$this->targetSocnetGroupId = $targetSocnetGroupId;
	}

	/**
	 * @param mixed $cacheManager
	 */
	public function setCacheManager(\CCacheManager $cacheManager): void
	{
		$this->cacheManager = $cacheManager;
	}

	/**
	 * Adds iblock.
	 *
	 * @param Container $container
	 * @param array $fields
	 * @return int|bool return iblock id or false.
	 */
	public function add(Container $container, array $fields)
	{
		$iblockObject = new \CIBlock;
		$iblockId = $iblockObject->add($fields);

		if ($iblockId)
		{
			$this->cleanCache($iblockId);
		}
		else
		{
			if ($iblockObject->LAST_ERROR)
			{
				$this->result->addError(new Error($iblockObject->LAST_ERROR, self::IBLOCK_COPY_ERROR));
			}
			else
			{
				$this->result->addError(new Error("Unknown error", self::IBLOCK_COPY_ERROR));
			}
		}

		return $iblockId;
	}

	/**
	 * Returns iblock fields.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @return array $fields
	 */
	public function getFields(Container $container, $entityId)
	{
		$query = \CIBlock::getList([], ["ID" => $entityId, "CHECK_PERMISSIONS" => "N"], true);
		$iblock = $query->fetch();
		if ($iblock)
		{
			$iblockMessage = \CIBlock::getMessages($entityId);
			$iblock = array_merge($iblock, $iblockMessage);
		}

		if ($this->targetIblockTypeId)
		{
			$iblock["IBLOCK_TYPE_ID"] = $this->targetIblockTypeId;
		}

		if ($this->targetSocnetGroupId)
		{
			$iblock["SOCNET_GROUP_ID"] = $this->targetSocnetGroupId;
		}

		if (!empty($iblock["PICTURE"]))
		{
			$iblock["PICTURE"] = \CFile::makeFileArray($iblock["PICTURE"]);
		}

		$iblock["RIGHTS"] = $this->getRights(
			$entityId,
			$iblock["RIGHTS_MODE"],
			$iblock["SOCNET_GROUP_ID"]
		);

		return $iblock;
	}

	/**
	 * Preparing data before creating a new iblock.
	 *
	 * @param Container $container
	 * @param array $fields List iblock fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		unset($fields["XML_ID"]);

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $entityId
	 * @param int $copiedEntityId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function copyChildren(Container $container, $entityId, $copiedEntityId)
	{
		$results = [];
		$sectionsRatio = [];
		$enumRatio = [];
		foreach ($this->child as $child)
		{
			if ($child instanceof ElementChild)
			{
				$child->setEnumRatio($enumRatio);
				$child->setSectionsRatio($sectionsRatio);
			}

			$results[] = $child->copy($entityId, $copiedEntityId);

			if ($child instanceof FieldChild)
			{
				$enumRatio = $child->getEnumRatio();
			}
			if ($child instanceof SectionChild)
			{
				$sectionsRatio = $child->getSectionsRatio();
			}
		}

		return $this->getResult($results);
	}

	protected function cleanCache(int $iblockId): void
	{
		if ($this->cacheManager)
		{
			$this->cacheManager->cleanDir("menu");
		}
	}

	protected function getSocnetPermission($iblockId, $socnetGroupId): array
	{
		return [];
	}

	private function getRights($iblockId, $rightMode, $socnetGroupId = 0)
	{
		$rights = [];

		if ($socnetGroupId)
		{
			$rights = $this->getSocnetPermission($iblockId, $socnetGroupId);
		}

		if ($rightMode == "E")
		{
			$rightObject = new \CIBlockRights($iblockId);
			foreach ($rightObject->getRights() as $right)
			{
				if (mb_strpos($right["GROUP_CODE"], "SG") !== 0)
				{
					$rights["n".(count($rights))] = [
						"GROUP_CODE" => $right["GROUP_CODE"],
						"DO_CLEAN" => "N",
						"TASK_ID" => $right["TASK_ID"],
					];
				}
			}
		}
		else
		{
			$groupPermissions = \CIBlock::getGroupPermissions($iblockId);
			foreach ($groupPermissions as $groupId => $permission)
			{
				if ($permission > "W")
				{
					$rights["n".(count($rights))] = [
						"GROUP_CODE" => "G".$groupId,
						"IS_INHERITED" => "N",
						"TASK_ID" => \CIBlockRights::letterToTask($permission),
					];
				}
			}
		}

		return $rights;
	}
}