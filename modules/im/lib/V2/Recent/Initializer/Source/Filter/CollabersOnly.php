<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source\Filter;

use Bitrix\Extranet\Enum\User\ExtranetRole;
use Bitrix\Extranet\Model\ExtranetUserTable;
use Bitrix\Im\V2\Recent\Initializer\Source\Filter;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;

class CollabersOnly implements Filter
{
	public function apply(Query $query, string $userIdFieldName): ?Query
	{
		if (!Loader::includeModule('extranet'))
		{
			return $query;
		}

		return $query
			->registerRuntimeField(
				'COLLABER_INFO',
				new Reference(
					'COLLABER_INFO',
					ExtranetUserTable::class,
					Join::on("this.{$userIdFieldName}", 'ref.USER_ID')
						->where('ref.ROLE', ExtranetRole::Collaber->value),
					['join_type' => Join::TYPE_INNER]
				)
			)
		;
	}
}