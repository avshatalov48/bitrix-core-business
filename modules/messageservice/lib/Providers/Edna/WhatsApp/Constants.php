<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

class Constants
{
	//region Shared
	public const
		ID = 'ednaru',
		API_ENDPOINT = 'https://app.edna.ru/api/',
		API_ENDPOINT_IO = 'https://app.edna.io/api/';

	public const
		CONTENT_TYPE_TEXT = 'TEXT',
		CONTENT_TYPE_IMAGE = 'IMAGE',
		CONTENT_TYPE_DOCUMENT = 'DOCUMENT',
		CONTENT_TYPE_VIDEO = 'VIDEO',
		CONTENT_TYPE_AUDIO = 'AUDIO';

	/* @see \Bitrix\Disk\TypeFile */
	public const CONTENT_TYPE_MAP = [
		2 => self::CONTENT_TYPE_IMAGE,
		3 => self::CONTENT_TYPE_VIDEO,
		4 => self::CONTENT_TYPE_DOCUMENT,
		5 => self::CONTENT_TYPE_DOCUMENT,
		8 => self::CONTENT_TYPE_DOCUMENT,
		9 => self::CONTENT_TYPE_AUDIO,
	];
}
