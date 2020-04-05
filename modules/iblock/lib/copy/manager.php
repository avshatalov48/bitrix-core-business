<?php
namespace Bitrix\Iblock\Copy;

use Bitrix\Iblock\Copy\Implement\Children\Child;
use Bitrix\Iblock\Copy\Implement\Children\Element as ElementImplementer;
use Bitrix\Iblock\Copy\Implement\Children\Field as FieldImplementer;
use Bitrix\Iblock\Copy\Implement\Children\Section as SectionImplementer;
use Bitrix\Iblock\Copy\Implement\Children\Workflow as WorkflowImplementer;
use Bitrix\Iblock\Copy\Implement\Iblock as IblockImplementer;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Dictionary;

class Manager
{
	protected $iblockTypeId;
	protected $iblockIdsToCopy;
	protected $socnetGroupId;

	protected $targetIblockTypeId = "";
	protected $targetSocnetGroupId = 0;

	private $iblockImplementer;
	private $fieldImplementer;
	private $workflowImplementer;

	protected $features = [
		"field",
		"section",
		"element",
		"workflow"
	];

	private $mapIdsCopiedIblock = [];

	/**
	 * @var Dictionary
	 */
	private $dictionary;

	public function __construct($iblockTypeId, array $iblockIdsToCopy, $socnetGroupId = 0)
	{
		$this->iblockTypeId = $iblockTypeId;
		$this->iblockIdsToCopy = $iblockIdsToCopy;
		$this->socnetGroupId = $socnetGroupId;
	}

	/**
	 * Writes the entities id of the target place.
	 * This is necessary if you want to copy the lists to another type of information block or another group.
	 *
	 * @param string $targetIblockTypeId Id type of information block.
	 * @param int $targetSocnetGroupId Group id.
	 */
	public function setTargetLocation($targetIblockTypeId, $targetSocnetGroupId = 0)
	{
		$this->targetIblockTypeId = $targetIblockTypeId;
		$this->targetSocnetGroupId = $targetSocnetGroupId;
	}

	/**
	 * Removes feature from the copy queue.
	 *
	 * @param string $feature Feature name.
	 */
	public function removeFeature($feature)
	{
		if (($key = array_search($feature, $this->features)) !== false)
		{
			unset($this->features[$key]);
		}
	}

	/**
	 * @param Dictionary $dictionary
	 */
	public function setDictionary(Dictionary $dictionary): void
	{
		$this->dictionary = $dictionary;
	}

	public function setIblockImplementer(IblockImplementer $implementer)
	{
		$this->iblockImplementer = $implementer;
	}

	public function setFieldImplementer(Child $implementer)
	{
		$this->fieldImplementer = $implementer;
	}

	public function setWorkflowImplementer(Child $implementer)
	{
		$this->workflowImplementer = $implementer;
	}

	public function startCopy()
	{
		$containerCollection = $this->getContainerCollection();

		$iblockCopier = $this->getIblockCopier();

		$result = $iblockCopier->copy($containerCollection);

		$this->mapIdsCopiedIblock = $iblockCopier->getMapIdsCopiedEntity();

		return $result;
	}

	/**
	 * Returns the identifier map of the parent copied entity.
	 *
	 * @return array
	 */
	public function getMapIdsCopiedEntity()
	{
		return $this->mapIdsCopiedIblock;
	}

	private function getContainerCollection()
	{
		$containerCollection = new ContainerCollection();

		foreach ($this->iblockIdsToCopy as $iblockId)
		{
			$container = new Container($iblockId);
			if ($this->dictionary)
			{
				$container->setDictionary($this->dictionary);
			}
			$containerCollection[] = $container;
		}

		return $containerCollection;
	}

	private function getIblockCopier()
	{
		global $CACHE_MANAGER;

		$iblockImplementer = ($this->iblockImplementer ? $this->iblockImplementer : new IblockImplementer());

		$iblockImplementer->setTargetIblockTypeId($this->targetIblockTypeId);
		$iblockImplementer->setTargetSocnetGroupId($this->targetSocnetGroupId);
		if (is_object($CACHE_MANAGER))
		{
			$iblockImplementer->setCacheManager($CACHE_MANAGER);
		}

		$sectionImplementer = null;
		if (in_array("field", $this->features))
		{
			if (!$this->fieldImplementer)
			{
				$this->fieldImplementer = new FieldImplementer();
			}
			$iblockImplementer->setChild($this->fieldImplementer);
		}
		if (in_array("section", $this->features))
		{
			$sectionImplementer = new SectionImplementer();
			$iblockImplementer->setChild($sectionImplementer);
		}
		if (in_array("element", $this->features))
		{
			$elementImplementer = new ElementImplementer(ElementImplementer::IBLOCK_COPY_MODE);
			$iblockImplementer->setChild($elementImplementer);
		}
		if (in_array("workflow", $this->features) && Loader::includeModule("bizproc"))
		{
			if (!$this->workflowImplementer)
			{
				$this->workflowImplementer = new WorkflowImplementer($this->iblockTypeId);
			}
			$iblockImplementer->setChild($this->workflowImplementer);
		}

		return new EntityCopier($iblockImplementer);
	}
}