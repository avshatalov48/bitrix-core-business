<?php


namespace Bitrix\Rest\Integration\View;


class Attributes
{
	const UNDEFINED = '';
	const HIDDEN = 'HID';
	const READONLY = 'R-O';
	const IMMUTABLE = 'IM'; //User can define field value only on create
	const REQUIRED = 'REQ';
	const MULTIPLE = 'MUL';
	const DYNAMIC = 'DYN';
	const COMPUTABLE = 'COM';
	const DEPRECATED = 'DEP';
}