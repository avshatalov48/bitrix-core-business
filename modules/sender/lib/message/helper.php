<?php

namespace Bitrix\Sender\Message;

use Bitrix\Sender\Integration\Crm\Connectors;
use Bitrix\Sender\PostingRecipientTable;

/**
 *
 */
class Helper
{
	/**
	 * @return array
	 */
	public static function getTemplateOptionSelector(): array
	{
		return array_map(
			function ($item) {
				return [
					'id' => '#' . $item['CODE'] . '#',
					'text' => $item['NAME'],
					'title' => $item['DESC'],
					'items' => $item['ITEMS'] ? array_map(
						function ($item) {
							return [
								'id' => '#' . $item['CODE'] . '#',
								'text' => $item['NAME'],
								'title' => $item['DESC'],
							];
						}, $item['ITEMS']
					) : [],
				];
			},
			array_merge(
				Connectors\Helper::getPersonalizeFieldsFromConnectors(),
				PostingRecipientTable::getPersonalizeList()
			)
		);
	}

}
