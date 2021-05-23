<?

namespace Bitrix\Seo\Marketing\Services;

use Bitrix\Seo\Marketing\PostList;

class PostListFacebook extends PostList
{
	const TYPE_CODE = 'facebook';

	public function getList($params)
	{
		$parameters = [
			'accountId' => $params['accountId'],
		];

		if(isset($params['limit']))
		{
			$parameters['limit'] = $params['limit'];
		}

		if(isset($params['last']))
		{
			$parameters['last'] = $params['last'];
		}

		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.post.list',
			'parameters' => $parameters
		));

		if ($response->isSuccess())
		{
			return $response->getData();
		}

		return null;
	}
}
