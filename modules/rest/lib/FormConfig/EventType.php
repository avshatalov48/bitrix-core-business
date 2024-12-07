<?php

namespace Bitrix\Rest\FormConfig;

enum EventType: string
{
	case Install = 'OnAppSettingsInstall';
	case Change = 'OnAppSettingsChange';
	case Display = 'OnAppSettingsDisplay';
}