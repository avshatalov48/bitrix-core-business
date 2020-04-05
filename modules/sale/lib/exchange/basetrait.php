<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\Entity\EntityImportFactory;
use Bitrix\Sale\Exchange\OneC\Converter;
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
	 * @return Converter
	 */
	protected function converterFactoryCreate($typeId)
	{
		return ConverterFactory::create($typeId);
	}

	/**
	 * @param $typeId
	 * @return ImportBase
	 */
	protected function entityFactoryCreate($typeId)
	{
		return EntityImportFactory::create($typeId);
	}
}