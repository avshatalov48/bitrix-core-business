<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\OneC\ConverterFactory;
use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\DocumentImportFactory;

trait BaseTrait
{

	/**
	 * @param $typeId
	 * @return DocumentBase
	 */
	protected function documentFactoryCreate($typeId)
	{
		return DocumentImportFactory::create($typeId);
	}

	/**
	 * @param $typeId
	 * @return IConverter
	 */
	protected function converterFactoryCreate($typeId)
	{
		return ConverterFactory::create($typeId);
	}
}