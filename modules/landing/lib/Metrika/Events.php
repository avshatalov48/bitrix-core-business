<?php
declare(strict_types=1);

namespace Bitrix\Landing\Metrika;

/**
 * Enum representing all possible Metrika events.
 */
enum Events: string
{
	case open = 'open';
	case save = 'save';
	case cancel = 'cancel';
	case start = 'start';
	case select = 'select';
	case openStartPage = 'open_start_page';
	case openSettingsMain = 'open_settings_main';
	case openMarket = 'open_market';
	case previewTemplate = 'preview_template';
	case createTemplate = 'create_template';
	case createTemplateApi = 'create_template_api';
	case replaceTemplate = 'replace_template';
	case openEditor = 'open_editor';
	case publishSite = 'publish_site';
	case unpublishSite = 'unpublish_site';
	case addWidget = 'add_widget';
	case deleteWidget = 'delete_widget';
	case clickOnButton = 'click_on_button';
	case dataGeneration = 'data_generation';
	case textsGeneration = 'texts_generation';
	case imagesGeneration = 'images_generation';
	case addFavourite = 'add_favourite';
	case deleteFavourite = 'delete_favourite';
	case unknown = 'unknown';
}
