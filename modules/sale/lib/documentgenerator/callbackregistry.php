<?php

namespace Bitrix\Sale\DocumentGenerator;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class CallbackRegistry
 * @package Bitrix\Sale\DocumentGenerator
 */
final class CallbackRegistry
{
	/**
	 * @param array $data
	 * @return bool
	 * @throws Main\ObjectException
	 */
	public static function add(array $data)
	{
		$dbRes = Sale\Internals\CallbackRegistryTable::add([
			'DATE_INSERT' => new Main\Type\DateTime(),
			'DOCUMENT_ID' => $data['DOCUMENT_ID'],
			'MODULE_ID' => $data['MODULE_ID'],
			'CALLBACK_CLASS' => $data['CALLBACK_CLASS'],
			'CALLBACK_METHOD' => $data['CALLBACK_METHOD'],
		]);

		return $dbRes->isSuccess();
	}

	/**
	 * @param Main\Event $event
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function onDocumentGenerated(Main\Event $event)
	{
		$documentId = $event->getParameter('documentId');
		$data = $event->getParameter('data');

		$dbRes = Sale\Internals\CallbackRegistryTable::getList([
			'filter' => [
				'=DOCUMENT_ID' => $documentId
			]
		]);

		if ($result = $dbRes->fetch())
		{
			if (!Main\ModuleManager::isModuleInstalled($result['MODULE_ID']))
			{
				return;
			}

			Main\Loader::includeModule($result['MODULE_ID']);

			$class = $result['CALLBACK_CLASS'];
			$method = $result['CALLBACK_METHOD'];

			$class::$method($data['value']);

			Sale\Internals\CallbackRegistryTable::delete($result['ID']);
		}
	}
}