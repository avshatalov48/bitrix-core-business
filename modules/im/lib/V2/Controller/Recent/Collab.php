<?php

namespace Bitrix\Im\V2\Controller\Recent;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Recent\RecentCollab;
use Bitrix\Im\V2\Recent\RecentError;
use Bitrix\Main\Type\DateTime;

class Collab extends BaseController
{

	/**
	 * @restMethod im.v2.Recent.Collab.tail
	 */
	public function tailAction(int $limit = 50, array $filter = []): ?array
	{
		if (isset($filter['lastMessageDate']))
		{
			if (!DateTime::isCorrect($filter['lastMessageDate'], \DateTimeInterface::RFC3339))
			{
				$this->addError(new RecentError(RecentError::WRONG_DATETIME_FORMAT));

				return null;
			}

			$filter['lastMessageDate'] = new DateTime($filter['lastMessageDate'], \DateTimeInterface::RFC3339);
		}

		$limit = $this->getLimit($limit);
		$recent = RecentCollab::getCollabs($limit, $filter['lastMessageDate'] ?? null);

		return $this->toRestFormatWithPaginationData([$recent], $limit, $recent->count());
	}
}
