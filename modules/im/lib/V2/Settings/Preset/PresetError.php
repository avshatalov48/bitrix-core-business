<?php

namespace Bitrix\Im\V2\Settings\Preset;

use Bitrix\Im\V2\Error;

class PresetError extends Error
{
	public const NOT_FOUND = 'PRESET_NOT_FOUND';
	public const BINDINGS_NOT_FOUND = 'PRESET_USER_BINDINGS_NOT_FOUND';
	public const BINDING_NOT_SPECIFIED = 'PRESET_BINDING_NOT_SPECIFIED';
	public const LOADING_NOT_SPECIFIED = 'PRESET_LOADING_NOT_SPECIFIED';
}