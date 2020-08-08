<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies;

use Bitrix\Sale\Exchange\Integration\Rest\Cmd;

class Base
	implements ICmd
{
	protected $batchItemCollection;

	public function cmd($pageType, $fields)
	{
		$cmd = Cmd\Factory::create($pageType);
		$cmd
			->setFieldsValues($fields)
			->fill();

		if(!($cmd instanceof Cmd\CmdBase))
		{
			$cmd
				->setPageByType($pageType);
		}

		return $cmd;
	}
	public function batch($pageType, $list)
	{
		$batch = new Cmd\Batch();
		$batchItemCollection = new Cmd\Batch\ItemCollection();

		foreach ($list as $index=>$row)
		{
			$cmd = $this->cmd($pageType, $row);

			$batchItemCollection->addItem(
				Cmd\Batch\Item::create($cmd)
					->setInternalIndex($index));
		}

		$this->setBatchItemCollection($batchItemCollection);

		return $batch
			//->setDirectory(Cmd\Batch::DIRECTORY_PAGE)
			->setPage(Cmd\Batch::CMD_PAGE)
			->setField('cmd', $batchItemCollection->toArray())
			->fill();
	}

	protected function setBatchItemCollection($batchItemCollection)
	{
		$this->batchItemCollection = $batchItemCollection;
	}
}