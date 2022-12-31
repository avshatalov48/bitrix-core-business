<?php

namespace Bitrix\Socialnetwork\Controller\Filter;

use \Bitrix\Socialnetwork\Helper;

class Workgroup extends \Bitrix\Main\Controller\Filter\Base
{
	public function getListAction($filterId, $componentName, $signedParameters)
	{
		$filterId = trim($filterId);
		$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);

		$additionalParameters = null;
		if (
			is_array($unsignedParameters)
			&& isset($unsignedParameters['MODE'])
		)
		{
			$additionalParameters = [
				'MODE' => $unsignedParameters['MODE'],
				'CONTEXT_USER_ID' => ($unsignedParameters['USER_ID'] ?: Helper\User::getCurrentUserId()),
			];
		}

		$result = $this->getList(
			\Bitrix\Socialnetwork\WorkgroupTable::getUfId(),
			[ 'ID' => $filterId !== '' ? $filterId : 'SOCIALNETWORK_WORKGROUP_LIST' ],
			$additionalParameters,
		);

		return $result;
	}

	public function getFieldAction($filterId, $id, $componentName, $signedParameters)
	{
		$filterId = trim($filterId);
		$id = trim($id);
		$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);

		$additionalParameters = null;
		if (
			is_array($unsignedParameters)
			&& isset($unsignedParameters['MODE'])
		)
		{
			$additionalParameters = [
				'MODE' => $unsignedParameters['MODE'],
				'CONTEXT_USER_ID' => ($unsignedParameters['USER_ID'] ?: Helper\User::getCurrentUserId()),
			];
		}

		return $this->getField(
			\Bitrix\Socialnetwork\WorkgroupTable::getUfId(),
			[ 'ID' => $filterId !== '' ? $filterId : 'SOCIALNETWORK_WORKGROUP_LIST' ],
			$id,
			$additionalParameters
		);
	}
}

