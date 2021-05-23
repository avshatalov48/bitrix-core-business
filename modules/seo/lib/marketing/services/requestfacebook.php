<?

namespace Bitrix\Seo\Marketing\Services;

use Bitrix\Seo\Retargeting\ProxyRequest;

class RequestFacebook extends ProxyRequest
{
	const TYPE_CODE = 'facebook';
	const REST_METHOD_PREFIX = 'seo.client.ads.facebook';
}