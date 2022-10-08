<?php


namespace Bitrix\Rest\Integration\View;


class Attributes
{
	const UNDEFINED = '';
	const HIDDEN = 'HID';
	const IMMUTABLE = 'IM';// User can define field value only on create
	const READONLY = 'R-O';// attributes R-O + IM look like REQ_ADD for the update operation. But then the value of this field will not change for update
	const REQUIRED = 'REQ';// attribute is the sum of attributes REQ_ADD + REQ_UPD
	const REQUIRED_ADD = 'REQ_ADD';
	const REQUIRED_UPDATE = 'REQ_UPD';
	const MULTIPLE = 'MUL';
	const DYNAMIC = 'DYN';
	const COMPUTABLE = 'COM';
	const DEPRECATED = 'DEP';
}