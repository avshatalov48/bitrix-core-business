<?php

namespace Bitrix\Socialnetwork\Controller\Filter;

class UserToGroup extends \Bitrix\Main\Controller\Filter\Base
{
	public function getListAction($filterId, $componentName, $signedParameters)
	{
		$filterId = trim($filterId);
		$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);

		return $this->getList(\Bitrix\Socialnetwork\UserToGroupTable::getUfId(), [
			'ID' => $filterId !== '' ? $filterId : 'SOCIALNETWORK_WORKGROUP_USER_LIST',
		]);
	}

	public function getFieldAction($filterId, $id, $componentName, $signedParameters)
	{
		$filterId = trim($filterId);
		$id = trim($id);
		$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);

		return $this->getField(\Bitrix\Socialnetwork\UserToGroupTable::getUfId(), [
			'ID' => $filterId !== '' ? $filterId : 'SOCIALNETWORK_WORKGROUP_USER_LIST',
		], $id);
	}
}

