<?php
namespace Bitrix\Bizproc\Copy;

use Bitrix\Bizproc\Copy\Implement\Trigger as Implementer;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\Copyable;
use Bitrix\Main\Result;

class Trigger implements Copyable
{
	private $implementer;
	private $implementerName;

	private $result;

	public function __construct(Implementer $implementer)
	{
		$this->implementer = $implementer;
		$this->implementerName = get_class($implementer);

		$this->result = new Result();
	}

	/**
	 * Copies entity.
	 *
	 * @param ContainerCollection $containerCollection
	 * @return Result
	 */
	public function copy(ContainerCollection $containerCollection)
	{
		$result = [$this->implementerName => []];

		foreach ($containerCollection as $container)
		{
			$workflowTemplateId = $container->getEntityId();

			$fields = $this->implementer->getFields($workflowTemplateId);

			$fields = $this->implementer->prepareFieldsToCopy($fields);

			$copiedWorkflowTemplateId = $this->implementer->add($fields);

			$result[$this->implementerName][$workflowTemplateId] = $copiedWorkflowTemplateId;
		}

		$this->result->setData($result);

		return $this->result;
	}
}