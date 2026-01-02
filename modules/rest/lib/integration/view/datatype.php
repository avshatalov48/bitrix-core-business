<?php

namespace Bitrix\Rest\Integration\View;

/**
 * Class DataType
 *
 * @package Bitrix\Rest\Integration\View
 */
class DataType
{
	public const TYPE_INT = 'integer';
	public const TYPE_FLOAT = 'double';
	public const TYPE_STRING = 'string';
	public const TYPE_CHAR = 'char';
	public const TYPE_BOOLEAN = 'boolean';
	public const TYPE_LIST = 'list';
	public const TYPE_TEXT = 'text';
	public const TYPE_FILE = 'file';
	public const TYPE_DATE = 'date';
	public const TYPE_DATETIME = 'datetime';
	public const TYPE_DATATYPE = 'datatype';
	/**
	 * @deprecated Deprecated since catalog 24.300.0 - a specific type for products only
	 * @see \Bitrix\Catalog\RestView\EntityFieldType::PRODUCT_PROPERTY
	 */
	public const TYPE_PRODUCT_PROPERTY = 'productproperty';
}
