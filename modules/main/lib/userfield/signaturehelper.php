<?php
namespace Bitrix\Main\UserField;

class SignatureHelper
{
	public static function getSignature(SignatureManager $signatureManager, array $fieldParam)
	{
		return $signatureManager->getSignature(
			static::getSignatureParam($fieldParam)
		);
	}

	public static function validateSignature(SignatureManager $signatureManager, array $fieldParam, $signature)
	{
		return $signatureManager->validateSignature(
			static::getSignatureParam($fieldParam),
			$signature
		);
	}

	protected static function getSignatureParam(array $fieldParam)
	{
		array_walk_recursive($fieldParam, function(&$item)
		{
			$item = strval($item);
		});

		$signatureParam = array(
			'ENTITY_ID' => $fieldParam['ENTITY_ID'],
			'FIELD' => $fieldParam['FIELD'],
		);

		if(!empty($fieldParam['VALUE']))
		{
			$fieldParam['VALUE'] = str_replace("\r\n", "\n", $fieldParam['VALUE']);
			$signatureParam['VALUE'] = $fieldParam['VALUE'];
		}

		if(!empty($fieldParam['CONTEXT']))
		{
			$signatureParam['CONTEXT'] = $fieldParam['CONTEXT'];
		}

		return serialize($signatureParam);
	}
}