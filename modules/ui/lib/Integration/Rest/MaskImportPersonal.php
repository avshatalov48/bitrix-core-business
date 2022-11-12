<?php
namespace Bitrix\UI\Integration\Rest;

use Bitrix\Main\Error;
use Bitrix\UI\Avatar;

class MaskImportPersonal extends ImportStep
{
	protected int $ownerId;
	protected Avatar\Mask\Owner\DefaultOwner $owner;

	public function init($event): void
	{
		$this->ownerId = (int) $event->getParameter('USER_ID');
		if ($this->ownerId <= 0)
		{
			$this->errorCollection->setError(new Error('User owner is not set'));
			return;
		}
		$this->owner = new Avatar\Mask\Owner\User($this->ownerId);
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