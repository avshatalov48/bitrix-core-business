<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

class Constants
{

	//region Shared
	public const ID = 'ednaru';
	public const SENDER_ID_OPTION = 'sender_id';
	public const API_KEY_OPTION = 'api_key';
	public const EMOJI_DECODE = 'decode';
	public const EMOJI_ENCODE = 'encode';
	//endregion

	public const NEW_API_AVAILABLE = 'is_migrated_to_new_api';
	public const API_ENDPOINT = 'https://app.edna.ru/api/';

	// region Methods
	public const GET_SUBJECTS = 'channel-profile';
	public const GET_CASCADES = 'cascade/get-all/';
	public const SEND_MESSAGE = 'cascade/schedule';
	public const GET_TEMPLATES = 'message-matchers/get-by-request';
	// endregion

	//region Content Types
	public const CONTENT_TYPE_TEXT = 'TEXT';
	//endregion

}