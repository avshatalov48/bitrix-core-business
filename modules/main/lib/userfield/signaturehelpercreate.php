<?php
namespace Bitrix\Main\UserField;

class SignatureHelperCreate extends SignatureHelper
{
	protected static function getSignatureParam(array $fieldParam)
	{
		return serialize(array(
			'ENTITY_ID' => strval($fieldParam['ENTITY_ID']),
		));
	}
}