<?
namespace Bitrix\Main\Controller\Filter;

class User extends Base
{
	public function getListAction($filterId, $componentName, $signedParameters)
	{
		$filterId = trim($filterId);
		$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);

		return $this->getList(\Bitrix\Main\UserTable::getUfId(), [
			'ID' => $filterId != '' ? $filterId : 'INTRANET_USER_LIST',
			'WHITE_LIST' => ($unsignedParameters['USER_PROPERTY_LIST'] ?? [])
		]);
	}

	public function getFieldAction($filterId, $id, $componentName, $signedParameters)
	{
		$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);

		$filterId = trim($filterId);
		$id = trim($id);

		return $this->getField(\Bitrix\Main\UserTable::getUfId(), [
			'ID' => $filterId != '' ? $filterId : 'INTRANET_USER_LIST',
			'WHITE_LIST' => ($unsignedParameters['USER_PROPERTY_LIST'] ?? [])
		], $id);
	}
}

