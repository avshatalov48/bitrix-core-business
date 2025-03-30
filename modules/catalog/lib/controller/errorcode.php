<?php

namespace Bitrix\Catalog\Controller;

class ErrorCode
{
	// permission errors start from '2000403'
	public const READ_PERMISSION_ACCESS_DENIED = '200040300010';
	public const MODIFY_PERMISSION_ACCESS_DENIED = '200040300020';

	// section errors start from '2007003'
	public const SECTION_ENTITY_NOT_EXISTS = '200700300030';

	// offer errors start from '2000405'
	public const PRODUCT_OFFER_PARENT_NOT_FOUND = '200040500010';
	public const PRODUCT_OFFER_BAD_PARENT_TYPE = '200040500020';
	public const PRODUCT_OFFER_BAD_PARENT_IBLOCK_ID = '200040500020';

	// measure error start from '2006000'
	public const MEASURE_ENTITY_NOT_EXISTS = '200600000020';

	// vat error start from '2008000'
	public const VAT_ENTITY_NOT_EXISTS = '200800000000';

	// rounding rule error start from '2009000'
	public const ROUNDING_RULE_ENTITY_NOT_EXISTS = '200900000000';

	// price type error start from '201000'
	public const PRICE_TYPE_ENTITY_NOT_EXISTS = '201000000000';

	// store error start from '201100'
	public const STORE_ENTITY_NOT_EXISTS = '201100000000';

	// store error start from '201100'
	public const PRICE_TYPE_LANG_ENTITY_NOT_EXISTS = '201200000000';
	public const PRICE_TYPE_LANG_LANGUAGE_NOT_EXISTS = '201200000010';

	// extra type error start from '202000'
	public const EXTRA_TYPE_ENTITY_NOT_EXISTS = '202000000000';
}
