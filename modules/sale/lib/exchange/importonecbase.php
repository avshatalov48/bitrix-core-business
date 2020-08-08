<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Entity\EntityImport;
use Bitrix\Sale\Exchange\Entity\ShipmentImport;
use Bitrix\Sale\Exchange\Entity\UserProfileImport;
use Bitrix\Sale\Exchange\OneC\ConverterFactory;
use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\DocumentType;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\Result;

abstract class ImportOneCBase extends ImportPattern
{
	use LoggerTrait;
	use BaseTrait;

	const EVENT_ON_EXCHANGE_CONFIGURE_IMPORTER = 'OnExchangeConfigureImporter';
	const DELIVERY_SERVICE_XMLID = 'ORDER_DELIVERY';

	/** @var  Fields */
	protected $fields;
	static protected $config;

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

			if($fields[$item::getFieldExternalId()] == '')
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

	static protected function setConfig($option='', $value=true)
	{
		if($value)
		{
			static::$config |= $option;
		}
		else
		{
			static::$config &= ~$option;
		}
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

		$converter = ConverterFactory::create($item->getOwnerTypeId());
		$converter::sanitizeFields($item->getEntity(), $fields, $item->getSettings());

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

			$document = $this->documentFactoryCreate($documentTypeId);

			$fields = $document::prepareFieldsData($raw);

			$document->setFields($fields);

			$list[] = $document;
		}

		$result->setData($list);

		return $result;
	}

	/**
	 * @param DocumentBase $document
	 * @return ImportBase
	 */
	protected function convertDocument(DocumentBase $document)
	{
		$entityTypeId = $this->resolveOwnerEntityTypeId($document->getTypeId());
		$settings = ManagerImport::getSettingsByType($entityTypeId);

		$convertor = $this->converterFactoryCreate($document->getTypeId());
		$convertor->init(
			$settings,
			$entityTypeId,
			$document->getTypeId()
		);

		$fields = $convertor->resolveParams($document);

		$loader = Entity\EntityImportLoaderFactory::create($entityTypeId);
		$loader->loadSettings($settings);

		if($document->getId() <> '')
			$fieldsEntity = $loader->getByNumber($document->getId());
		else
			$fieldsEntity = $loader->getByExternalId($document->getExternalId());

		if(!empty($fieldsEntity['ID']))
			$fields['TRAITS']['ID'] = $fieldsEntity['ID'];

		$entityImport = $this->entityFactoryCreate($entityTypeId);
		ManagerImport::configure($entityImport);

		$entityImport->setFields($fields);

		return $entityImport;
	}

	abstract protected function resolveOwnerEntityTypeId($typeId);

	/**
	 * @param array $fields
	 * @return int
	 */
	protected function resolveDocumentTypeId(array $fields)
	{
		return OneC\DocumentBase::resolveRawDocumentTypeId($fields);
	}

	/**
	 * @return array
	 */
	protected static function getMessage()
	{
		return Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/sale.export.1c/component.php');
	}

	/**
	 * @return string
	 */
	public function getDirectionType()
	{
		return ManagerImport::getDirectionType();
	}

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	protected function logger(array $items)
	{
		return $this->loggerEntities($items);
	}
}