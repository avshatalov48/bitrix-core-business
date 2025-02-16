<?php

namespace Bitrix\Mail\Integration\UI\EntitySelector;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Tab;

class MailCrmRecipientProvider extends BaseProvider
{
	public const PROVIDER_ENTITY_ID = 'mail_crm_recipient';
	public const ITEMS_LIMIT = 6;

	public function __construct(array $options = [])
	{
		parent::__construct();
	}

	private static function getTabIcon(): string
	{
		return "data:image/svg+xml,%3Csvg%20width%3D%2228%22%20height%3D%2228%22%20viewBox%3D%220%200%2028%2028%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd%22%20d%3D%22M4.98376%207.25293H23.0169C23.4823%207.25293%2023.8596%207.61329%2023.8596%208.05782C23.8596%208.14792%2023.8438%208.23738%2023.8127%208.32248L23.2679%209.81728C23.15%2010.1407%2022.8307%2010.3575%2022.4721%2010.3575H5.52422C5.16485%2010.3575%204.84503%2010.1398%204.72772%209.81536L4.18726%208.32056C4.03534%207.90039%204.26879%207.44213%204.70868%207.29702C4.79717%207.26783%204.89014%207.25293%204.98376%207.25293ZM7.95392%2012.9917H20.0468C20.5122%2012.9917%2020.8894%2013.3521%2020.8894%2013.7966C20.8894%2013.8931%2020.8713%2013.9889%2020.8358%2014.0792L20.249%2015.574C20.1257%2015.8882%2019.8113%2016.0963%2019.46%2016.0963H8.5095C8.15279%2016.0963%207.83473%2015.8818%207.71539%2015.5607L7.15982%2014.0659C7.00412%2013.647%207.23344%2013.1868%207.67201%2013.0381C7.76252%2013.0074%207.85787%2012.9917%207.95392%2012.9917ZM11.7083%2018.7486H16.2924C16.7578%2018.7486%2017.135%2019.1089%2017.135%2019.5534C17.135%2019.6318%2017.1231%2019.7096%2017.0995%2019.7847L16.6302%2021.2795C16.5233%2021.6199%2016.1952%2021.8531%2015.8231%2021.8531H12.2285C11.8655%2021.8531%2011.5433%2021.6311%2011.4289%2021.3021L10.9087%2019.8073C10.7619%2019.3855%2011.0009%2018.9299%2011.4425%2018.7896C11.5282%2018.7624%2011.618%2018.7486%2011.7083%2018.7486Z%22%20fill%3D%22%23959CA4%22%2F%3E%3C%2Fsvg%3E";
	}

	private static function addTemplatesTab($dialog): void
	{
		$dialog->addTab(new Tab([
			'id' => self::PROVIDER_ENTITY_ID,
			'title' => Loc::getMessage("MAIL_CRM_RECIPIENT_PROVIDER_TAB_TITLE"),
			'header' => Loc::getMessage("MAIL_CRM_RECIPIENT_PROVIDER_TAB_HEADER"),
			'icon' => [
				'default' => self::getTabIcon(),
				'selected' => str_replace('959CA4', 'FFF', self::getTabIcon()),
			],
		]));
	}

	public function isAvailable(): bool
	{
		return Loader::includeModule('crm');
	}

	public function getItems(array $ids): array
	{
		return [];
	}

	public function fillDialog(Dialog $dialog): void
	{
		self::addTemplatesTab($dialog);
	}
}