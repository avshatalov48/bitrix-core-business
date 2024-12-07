<?php

namespace Bitrix\Catalog\Controller;

class ErrorCode
{
	// section errors start from '2007003'
	public const SECTION_ENTITY_NOT_EXISTS = '200700300030';

	// offer errors start from '2000405'
	public const PRODUCT_OFFER_PARENT_NOT_FOUND = '200040500010';
	public const PRODUCT_OFFER_BAD_PARENT_TYPE = '200040500020';
	public const PRODUCT_OFFER_BAD_PARENT_IBLOCK_ID = '200040500020';

	// measure error start from '2006000'
	public const MEASURE_ENTITY_NOT_EXISTS = '200600000020';
}
