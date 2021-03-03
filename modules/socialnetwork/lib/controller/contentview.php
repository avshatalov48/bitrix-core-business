<?
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Item\UserContentView;

class ContentView extends Base
{
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$configureActions['set'] = [
			'+prefilters' => [
				new \Bitrix\Main\Engine\ActionFilter\CloseSession(),
			]
		];

		return $configureActions;
	}

	public function setAction(array $params = [])
	{
		$xmlIdList = (
			isset($params["viewXMLIdList"])
			&& is_array($params["viewXMLIdList"])
				? $params["viewXMLIdList"]
				: []
		);

		$context = ($params['context'] ?? '');

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_CONTENTVIEW_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		UserContentView::set([
			'xmlIdList' => $xmlIdList,
			'context' => $context,
			'userId' => $this->getCurrentUser()->getId()
		]);

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

		if ($contentId == '')
		{
			$this->addError(new Error('Empty Content ID', 'SONET_CONTROLLER_CONTENTVIEW_EMPTY_CONTENT_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_CONTENTVIEW_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		$userList = UserContentView::getUserList([
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

