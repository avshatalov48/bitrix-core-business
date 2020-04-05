<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Entity\EntityImport;
use Bitrix\Sale\Exchange\Entity\OrderImport;
use Bitrix\Sale\Exchange\Entity\ShipmentImport;
use Bitrix\Sale\Exchange\Entity\UserProfileImport;
use Bitrix\Sale\Exchange\OneC\DocumentImport;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\Result;

abstract class ImportOneCBase extends ImportPattern
{
	const EVENT_ON_EXCHANGE_CONFIGURE_IMPORTER = 'OnExchangeConfigureImporter';

	const DELIVERY_SERVICE_XMLID = 'ORDER_DELIVERY';

	/** @var  Fields */
	protected $fields;
	/** @var  $rawData null */
	protected $rawData;

	/**
	 * @param array $values
	 * @internal param array $fields
	 */
	public function setFields(array $values)
	{
		foreach ($values as $key=>$value)
		{
			$this->setField($key, $value);
		}
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setField($name, $value)
	{
		$this->fields->set($name, $value);
	}

	/**
	 * @param $name
	 * @return null|string
	 */
	public function getField($name)
	{
		return $this->fields->get($name);
	}

	/**
	 * @param null $rawData
	 */
	public function setRawData($rawData)
	{
		$this->rawData = $rawData;
	}

	/**
	 * @return null
	 */
	public function getRawData()
	{
		return $this->rawData;
	}

	/**
	 * @param array $items
	 * @return Result
	 */
	protected function checkFields(array $items)
	{
		$result = new Result();

		foreach($items as $item)
		{
			$params = $item->getFieldValues();
			$fields = $params['TRAITS'];

			if(strlen($fields[$item::getFieldExternalId()])<= 0)
				$result->addErrors(array(new Error(" ".EntityType::getDescription($item->getOwnerTypeId()).": ".GetMessage("SALE_EXCHANGE_EXTERNAL_ID_NOT_FOUND"), 'SALE_EXCHANGE_EXTERNAL_ID_NOT_FOUND')));
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	static public function checkSettings()
	{
		return new Result();
	}

	static public function configuration()
	{
		$event = new Event('sale', static::EVENT_ON_EXCHANGE_CONFIGURE_IMPORTER);
		$event->send();
	}

	/**
	 * @param ImportBase $item
	 * @return Result
	 * @throws ArgumentException
	 * @internal
	 */
	protected function modifyEntity($item)
	{
		$result = new Result();

		if(!($item instanceof EntityImport) && !($item instanceof UserProfileImport))
			throw new ArgumentException("Item must be instanceof EntityImport or UserProfileImport");

		$params = $item->getFieldValues();

		$fieldsCriterion = $fields = &$params['TRAITS'];

		$converter = OneC\Converter::getInstance($item->getOwnerTypeId());
		$converter->loadSettings($item->getSettings());

		/** @var OneC\Converter $converter*/
		$converter->sanitizeFields($item->getEntity(), $fields);
		$item->refreshData($fields);

		$criterion = $item->getCurrentCriterion($item->getEntity());
		$collision = $item->getCurrentCollision($item->getOwnerTypeId());

		if($item instanceof ShipmentImport)
			$fieldsCriterion['ITEMS'] = $params['ITEMS'];

		if($criterion->equals($fieldsCriterion))
		{
			$collision->resolve($item);
		}

		if(!$criterion->equals($fieldsCriterion) ||
			($criterion->equals($fieldsCriterion) && !$item->hasCollisionErrors()))
		{
			$result = $item->import($params);
		}

		return $result;
	}

	/**
	 * @param array $rawFields
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function parse(array $rawFields)
	{
		$result = new Result();
		$list = array();

		foreach($rawFields as $raw)
		{
			$documentTypeId = $this->resolveDocumentTypeId($raw);

			$document = OneC\DocumentImportFactory::create($documentTypeId);

			$fields = $document::prepareFieldsData($raw);

			$document->setFields($fields);

			$list[] = $document;
		}

		$result->setData($list);

		return $result;
	}

	/**
	 * @param DocumentImport $document
	 * @return ImportBase
	 */
	protected function convertDocument(DocumentImport $document)
	{
		$settings = ManagerImport::getSettingsByType($document->getOwnerEntityTypeId());

		$convertor = OneC\Converter::getInstance($document->getOwnerEntityTypeId());
		$convertor->loadSettings($settings);
		$fields = $convertor->resolveParams($document);

		$loader = Entity\EntityImportLoaderFactory::create($document->getOwnerEntityTypeId());
		$loader->loadSettings($settings);

		if(strlen($document->getId())>0)
			$fieldsEntity = $loader->getByNumber($document->getId());
		else
			$fieldsEntity = $loader->getByExternalId($document->getExternalId());

		if(!empty($fieldsEntity['ID']))
			$fields['TRAITS']['ID'] = $fieldsEntity['ID'];

		$entityImport = ManagerImport::create($document->getOwnerEntityTypeId());
		$entityImport->setFields($fields);

		return $entityImport;
	}

	/**
	 * @param array $fields
	 * @return int
	 */
	protected function resolveDocumentTypeId(array $fields)
	{
		return OneC\DocumentImport::resolveDocumentTypeId($fields);
	}

	/**
	 * @return array
	 */
	protected static function getMessage()
	{
		return Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/sale.export.1c/component.php');
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	protected function logger(array $items)
	{
		$result = new Result();

		foreach ($items as $item)
		{
			if($item->hasLogging())
			{
				$logger = $item->getLogger();

				$logger->setField('ENTITY_ID', $item->getId());
				$logger->setField('ENTITY_TYPE_ID', $item->getOwnerTypeId());
				$logger->setField('XML_ID', $item->getExternalId());
				$logger->setField('DIRECTION', ManagerImport::getDirectionType());

				$logger->save();
			}
		}
		return $result;
	}
}