<?php

namespace Bitrix\Im\V2\Chat\InputAction;

enum Type: string
{
	case Writing = 'writing';
	case RecordingVoice = 'recordingVoice';
	case SendingPhoto = 'sendingPhoto';
	case SendingFile = 'sendingFile';
}
