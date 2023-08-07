<?php


namespace Bitrix\Sale\Rest;


class Attributes
{
	const Undefined = '';
	const Hidden = 'HID';
	const ReadOnly = 'R-O';
	const Immutable = 'IM'; //User can define field value only on create
	const Required = 'REQ';
	const Multiple = 'MUL';
	const Dynamic = 'DYN';
	const Computable = 'COM';
	const Deprecated = 'DEP';
}