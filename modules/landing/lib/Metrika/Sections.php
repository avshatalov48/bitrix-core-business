<?php
declare(strict_types=1);

namespace Bitrix\Landing\Metrika;

enum Sections: string
{
	case site = 'site';
	case page = 'page';
	case blockStyle = 'block_style';
	case siteEditor = 'site_editor';
	case siteDesigner = 'site_designer';
	case siteSettings = 'site_settings';
	case marketMain = 'market_main';
	case marketCollection = 'market_collection';
}
