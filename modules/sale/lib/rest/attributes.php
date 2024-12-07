<?php

namespace Bitrix\Sale\Rest;

class Attributes
{
	public const Undefined = '';
	public const Hidden = 'HID';
	public const ReadOnly = 'R-O';
	public const Immutable = 'IM'; //User can define field value only on create
	public const Required = 'REQ';
	public const Multiple = 'MUL';
	public const Dynamic = 'DYN';
	public const Computable = 'COM';
	public const Deprecated = 'DEP';
}
