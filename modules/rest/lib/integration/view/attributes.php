<?php

namespace Bitrix\Rest\Integration\View;

class Attributes
{
	public const UNDEFINED = '';
	public const HIDDEN = 'HID';
	public const IMMUTABLE = 'IM';// User can define field value only on create
	public const READONLY = 'R-O';// attributes R-O + IM look like REQ_ADD for the update operation. But then the value of this field will not change for update
	public const REQUIRED = 'REQ';// attribute is the sum of attributes REQ_ADD + REQ_UPD
	public const REQUIRED_ADD = 'REQ_ADD';
	public const REQUIRED_UPDATE = 'REQ_UPD';
	public const MULTIPLE = 'MUL';
	public const DYNAMIC = 'DYN';
	public const COMPUTABLE = 'COM';
	public const DEPRECATED = 'DEP';
	public const DISABLED_FILTER = 'DIS_FLT'; // can't use field in filter
	public const DISABLED_ORDER = 'DIS_ORD'; // can't use field in order
	public const SELECT_ONLY = 'S-O'; // attribute is the sum of attributes DISABLED_FILTER and DISABLED_ORDER
}
