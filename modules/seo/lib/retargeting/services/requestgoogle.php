<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\ProxyRequest;

class RequestGoogle extends ProxyRequest
{
	const TYPE_CODE = 'google';
	const REST_METHOD_PREFIX = 'seo.client.ads.google';
}