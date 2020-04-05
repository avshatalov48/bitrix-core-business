<?php
namespace Bitrix\Rest;

use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;

class Sqs
{
	const DATA_CHARSET = "utf-8";

	const CATEGORY_DEFAULT = "default";
	const CATEGORY_IMPORTANT = "important";
	const CATEGORY_BOT = "bot";
	const CATEGORY_CRM = "crm";
	const CATEGORY_BIZPROC = "bizproc";
	const CATEGORY_TELEPHONY = "telephony";

	public static function queryItem($clientId, $url, $data, array $authData = array(), array $additional = array())
	{
		return array(
			'client_id' => $clientId,
			'additional' => $additional,
			'auth' => $authData,
			'query' => array(
				'DOMAIN' => Context::getCurrent()->getRequest()->getHttpHost(),
				'QUERY_URL' => $url,
				'QUERY_DATA' => $data,
			),
		);
	}
}