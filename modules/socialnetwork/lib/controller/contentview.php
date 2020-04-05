<?
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Livefeed;

class ContentView extends \Bitrix\Main\Engine\Controller
{
	public function setAction(array $params = [])
	{
		$xmlIdList = (
			isset($params["viewXMLIdList"])
			&& is_array($params["viewXMLIdList"])
				? $params["viewXMLIdList"]
				: []
		);

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_CONTENTVIEW_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		if (!empty(!empty($xmlIdList)))
		{
			foreach($xmlIdList as $val)
			{
				$xmlId = $val['xmlId'];
				$save = (
					!isset($val['save'])
					|| $val['save'] != 'N'
				);

				$tmp = explode('-', $xmlId, 2);
				$entityType = trim($tmp[0]);
				$entityId = intval($tmp[1]);

				if (
					!empty($entityType)
					&& $entityId > 0
				)
				{
					$provider = Livefeed\Provider::init(array(
						'ENTITY_TYPE' => $entityType,
						'ENTITY_ID' => $entityId,
					));
					if ($provider)
					{
						$provider->setContentView(array(
							'save' => $save
						));
					}
				}
			}
		}

		return [
			'SUCCESS' => 'Y'
		];
	}

	public function getListAction(array $params = [])
	{
		$contentId = (
			isset($params['contentId'])
			&& is_string($params['contentId'])
				? trim($params['contentId'])
				: ''
		);

		$page = (
			isset($params['page'])
			&& intval($params['page']) > 0
				? intval($params['page'])
				: 1
		);

		$pathToUserProfile = (
			isset($params['pathToUserProfile'])
			&& is_string($params['pathToUserProfile'])
				? trim($params['pathToUserProfile'])
				: ''
		);

		if (strlen($contentId) <= 0)
		{
			$this->addError(new Error('Empty Content ID', 'SONET_CONTROLLER_CONTENTVIEW_EMPTY_CONTENT_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_CONTENTVIEW_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		$userList = \Bitrix\Socialnetwork\Item\UserContentView::getUserList([
			'contentId' => $contentId,
			'page' => $page,
			'pathToUserProfile' => $pathToUserProfile
		]);

		$result['items'] = $userList['items'];
		$result['itemsCount'] = count($result['items']);
		$result['hiddenCount'] = $userList['hiddenCount'];

		return $result;
	}
}

