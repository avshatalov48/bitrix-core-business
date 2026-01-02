<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Repository\Mapper;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\ProcessingStatus;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\PushId;
use Bitrix\Calendar\Synchronization\Internal\Model\EO_Push;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;

class PushMapper
{
	/**
	 * @param EO_Push $ormModel
	 *
	 * @return Push
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 */
	public function convertFromOrm(EO_Push $ormModel): Push
	{
		$push = new Push();

		$push
			->setId(
				new PushId($ormModel->getEntityType(), $ormModel->getEntityId())
			)
			->setChannelId($ormModel->getChannelId())
			->setResourceId($ormModel->getResourceId())
			->setExpireDate(new Date($ormModel->getExpires()))
			->setProcessingStatus(
				ProcessingStatus::tryFrom($ormModel->getNotProcessed()) ?? ProcessingStatus::Unblocked
			)
			->setFirstPushDate($ormModel->getFirstPushDate() ? new Date($ormModel->getFirstPushDate()) : null)
		;

		return $push;
	}
}
