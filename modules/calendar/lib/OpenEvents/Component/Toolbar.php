<?php

namespace Bitrix\Calendar\OpenEvents\Component;

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Property\ColorHelper;
use Bitrix\Calendar\OpenEvents\Filter\Filter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\UI;

class Toolbar
{
	public function build(): void
	{
		$this->addCreateButton();
		$this->addFilter();
	}

	protected function addCreateButton(): void
	{
		$calendarType = Dictionary::CALENDAR_TYPE['open_event'];
		$userId = CurrentUser::get()->getId();

		$colors = \Bitrix\Main\Web\Json::encode(ColorHelper::OUR_COLORS);

		$addButton = new UI\Buttons\Button([
			'color' => UI\Buttons\Color::SUCCESS,
			'text' => Loc::getMessage('CALENDAR_OPEN_EVENTS_TOOLBAR_BUTTON_CREATE'),
			'click' => new UI\Buttons\JsCode("
				BX.Runtime.loadExtension('calendar.entry').then(({ EntryManager }) => {
					const colors = $colors;
					EntryManager.openEditSlider({
						type: '$calendarType',
						userId: $userId,
						formDataValue: {
							color: colors[Math.floor(Math.random()*colors.length)],
						},
					});
				});
			"),
			'events' => [
				'mouseover' => new UI\Buttons\JsCode("BX.Runtime.loadExtension('calendar.entry');"),
			],
		]);

		UI\Toolbar\Facade\Toolbar::addButton($addButton, UI\Toolbar\ButtonLocation::AFTER_TITLE);
	}

	protected function addFilter(): void
	{
		$filter = new Filter();

		(new Options($filter::getId()))->reset();

		UI\Toolbar\Facade\Toolbar::addFilter($filter->getOptions());
	}
}
