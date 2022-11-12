<?php
namespace Bitrix\UI\Integration\Rest;

use Bitrix\Main\Error;
use Bitrix\UI\Avatar;
use Bitrix\Rest;

class MaskImportApp extends ImportStep
{
	protected int $ownerId;
	private int $groupId;
	protected Avatar\Mask\Owner\DefaultOwner $owner;

	public function init($event): void
	{
		$this->ownerId = (int) $event->getParameter('APP_ID');
		if ($this->ownerId <= 0)
		{
			$this->errorCollection->setError(new Error('Application id is not set.'));
			return;
		}

		if (!($app = Rest\AppTable::getById($this->ownerId)->fetch()))
		{
			$this->errorCollection->setError(new Error('Application is not found.'));
			return;
		}

		$this->owner = new Avatar\Mask\Owner\RestApp($this->ownerId);

		if ($group = Avatar\Mask\GroupTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=OWNER_TYPE' => Avatar\Mask\Owner\RestApp::class,
				'=OWNER_ID' => $this->ownerId,
			]
		])->fetch())
		{
			$this->groupId = $group['ID'];
		}
		else
		{
			$this->groupId = Avatar\Mask\Group::createOrGet($this->owner, $app['APP_NAME'])->getId();
		}
	}

	public function makeAStep(): void
	{
		foreach ($this->data as $res)
		{
			$fileInfo = $this->structure->getUnpackFile((int)$res['FILE_ID']);
			$file = !empty($fileInfo['PATH']) ? \CFile::makeFileArray($fileInfo['PATH']) : null;
			$file['name'] = $fileInfo['NAME'];
			if ($file)
			{
				$result = Avatar\Mask\Item::create(
					$this->owner,
					$file,
					[
						'TITLE' => $res['TITLE'],
						'DESCRIPTION' => $res['DESCRIPTION'],
						'GROUP_ID' => $this->groupId,
						'ACCESS_CODE' => $this->owner->getDefaultAccess()
					]
				);
				if (!$result->isSuccess())
				{
					$this->errorCollection->add($result->getErrors());
				}
			}
		}
	}
}