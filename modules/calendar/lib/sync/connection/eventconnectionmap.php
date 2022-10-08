<?php

namespace Bitrix\Calendar\Sync\Connection;

use Bitrix\Calendar\Core\Base\EntityMap;

/**
 * @method self add(EventConnection $item, $key = null)
 * @method self addItems(array $items)
 * @method self updateItem(EventConnection $item, $key)
 * @method EventConnection getItem($key)
 * @method self getItemsByKeys(array $key)
 * @method EventConnection[] getCollection()
 * @method EventConnection fetch()
 */
class EventConnectionMap extends EntityMap
{
}
