<?
namespace Bitrix\Socialnetwork\Controller\User;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\UserWelltoryTable;

class StressLevel extends \Bitrix\Socialnetwork\Controller\Base
{
	private function isCurrentUserAdmin()
	{
		Loader::includeModule('socialnetwork');

		return \CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false);
	}

	public function addAction(array $fields = [])
	{
		$value = (
			isset($fields['value'])
				? intval($fields['value'])
				: false
		);

		if ($value === false)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_ADD_NOSTRESS'), 'SONET_CONTROLLER_USER_STRESSLEVEL_ADD_NOSTRESS'));
			return null;
		}

		$userId = (
			isset($fields['userId'])
			&& $this->isCurrentUserAdmin()
				? intval($fields['userId'])
				: $this->getCurrentUser()->getId()
		);

		if (
			$userId != $this->getCurrentUser()->getId()
			&& !$this->isCurrentUserAdmin()
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_NO_PERMISSIONS'), 'SONET_CONTROLLER_USER_STRESSLEVEL_NO_PERMISSIONS'));
			return null;
		}

		Loader::includeModule('socialnetwork');

		$disclaimerData = $this->getDisclaimer([
			'userId' => $userId
		]);
		if (empty($disclaimerData))
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_NO_SIGNED_DISCLAIMER'), 'SONET_CONTROLLER_USER_STRESSLEVEL_NO_SIGNED_DISCLAIMER'));
			return null;
		}

		UserWelltoryTable::add([
			'USER_ID' => $userId,
			'STRESS' => $value,
			'STRESS_TYPE' => (isset($fields['type']) ? $fields['type'] : ''),
			'STRESS_COMMENT' => (isset($fields['comment']) ? $fields['comment'] : ''),
			'DATE_MEASURE' => new \Bitrix\Main\DB\SqlExpression(\Bitrix\Main\Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction()),
			'HASH' => (isset($fields['hash']) ? $fields['hash'] : '')
		]);

		return [
			'success' => true,
		];
	}

	public function getAction(array $fields = [])
	{
		$result = [];

		$userId = (
			isset($fields['userId'])
				? intval($fields['userId'])
				: $this->getCurrentUser()->getId()
		);

		if ($userId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'), 'SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'));
			return null;
		}

		if (
			$userId != $this->getCurrentUser()->getId()
			&& $this->getAccess([
				'userId' => $userId
			]) != 'Y'
		)
		{
			return $result;
		}

		$data = \Bitrix\Socialnetwork\Item\UserWelltory::getHistoricData([
			'userId' => $userId,
			'limit' => 1
		]);
		if (!empty($data))
		{
			$result = $data[0];
		}

		$parameters = $this->getUnsignedParameters();

		if (
			!empty($parameters)
			&& !empty($parameters['PATH_TO_USER_STRESSLEVEL'])
		)
		{
			$url = \CComponentEngine::makePathFromTemplate($parameters["PATH_TO_USER_STRESSLEVEL"], array("user_id" => $userId));

			$uri =  new \Bitrix\Main\Web\Uri($url);
			$uri->addParams([
				'page' => 'result'
			]);

			$result['url'] = [
				'check' => $url,
				'result' => $uri->getUri()
			];
		}

		return $result;
	}

	public function getAccess(array $fields = [])
	{
		$userId = (
			isset($fields['userId'])
				? intval($fields['userId'])
				: $this->getCurrentUser()->getId()
		);

		return \Bitrix\Socialnetwork\Item\UserWelltory::getAccess([
			'userId' => $userId
		]);
	}

	public function setAccess(array $fields = [])
	{
		$userId = (
			isset($fields['userId'])
				? intval($fields['userId'])
				: $this->getCurrentUser()->getId()
		);

		$value = (
			isset($fields['value'])
			&& $fields['value'] == 'Y'
				? 'Y'
				: 'N'
		);

		return \Bitrix\Socialnetwork\Item\UserWelltory::setAccess([
			'userId' => $userId,
			'value' => $value
		]);
	}

	public function getAccessAction(array $fields = [])
	{
		$userId = (
			isset($fields['userId'])
				? intval($fields['userId'])
				: false
			);

		if ($userId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'), 'SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'));
			return null;
		}

		if (
			$userId != $this->getCurrentUser()->getId()
			&& !$this->isCurrentUserAdmin()
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_NO_PERMISSIONS'), 'SONET_CONTROLLER_USER_STRESSLEVEL_NO_PERMISSIONS'));
			return null;
		}

		return [
			'value' => $this->getAccess([
				'userId' => $userId
			])
		];
	}

	public function setAccessAction(array $fields = [])
	{
		$userId = (
			isset($fields['userId'])
				? intval($fields['userId'])
				: false
		);

		$value = (
			isset($fields['value'])
			&& $fields['value'] == 'Y'
				? 'Y'
				: 'N'
		);

		if ($userId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'), 'SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'));
			return null;
		}

		if (
			$userId != $this->getCurrentUser()->getId()
			&& !$this->isCurrentUserAdmin()
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_NO_PERMISSIONS'), 'SONET_CONTROLLER_USER_STRESSLEVEL_NO_PERMISSIONS'));
			return null;
		}

		return [
			'value' => $this->setAccess([
				'userId' => $userId,
				'value' => $value
			])
		];
	}

	public function getValueDescriptionAction($type = '', $value = false)
	{
		if ($value !== false)
		{
			$value = intval($value);
		}
		else
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_ADD_NOSTRESS'), 'SONET_CONTROLLER_USER_STRESSLEVEL_ADD_NOSTRESS'));
			return null;
		}

		if (Loader::includeModule('intranet'))
		{
			$result = \Bitrix\Intranet\Component\UserProfile\StressLevel::getValueDescription($type, $value);
		}

		return [
			'description' => $result
		];
	}

	private function getDisclaimer(array $fields = [])
	{
		$result = [];
		$userId = (
			isset($fields['userId'])
				? intval($fields['userId'])
				: false
		);

		if ($userId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'), 'SONET_CONTROLLER_USER_STRESSLEVEL_GET_NOUSER_ID'));
			return null;
		}

		$res = \Bitrix\Socialnetwork\UserWelltoryDisclaimerTable::getList([
			'filter' => [
				'USER_ID' => $userId
			],
			'order' => [
				'ID' => 'ASC'
			],
			'select' => [ 'ID', 'DATE_SIGNED' ],
			'limit' => 1
		]);
		if ($disclaimerFields = $res->fetch())
		{
			$result = $disclaimerFields;
		}

		return $result;
	}

	public function setDisclaimerAction()
	{
		$userId = $this->getCurrentUser()->getId();

		$result = $this->getDisclaimer([
			'userId' => $userId
		]);
		if (!empty($result))
		{
			return $result;
		}

		if (\Bitrix\Socialnetwork\UserWelltoryDisclaimerTable::add([
			'USER_ID' => $this->getCurrentUser()->getId(),
			'DATE_SIGNED' => new \Bitrix\Main\DB\SqlExpression(\Bitrix\Main\Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction()),
		]))
		{
			$result = $this->getDisclaimer([
				'userId' => $userId
			]);
		}

		return $result;
	}

	public function getDisclaimerAction()
	{
		$result = [];
		$res = \Bitrix\Socialnetwork\UserWelltoryDisclaimerTable::getList([
			'filter' => [
				'USER_ID' => $this->getCurrentUser()->getId()
			],
			'order' => [
				'ID' => 'ASC'
			],
			'select' => [ 'ID', 'DATE_SIGNED' ],
			'limit' => 1
		]);

		if ($disclaimerFields = $res->fetch())
		{
			$result = $disclaimerFields;
		}

		return $result;
	}


}

