import { Dom, Event, Tag, Type, Loc } from 'main.core';
import { Filter } from '../filter';
import { CalendarAddButtonMenu } from './calendar-add-button-menu';
import { CalendarSettings } from './calendar-settings';
import { Calendar } from './calendar';

type Params = {
	type: string;
	locationAccess: ?string;
	userId: number;
	ownerId: number;
	filterId: string,
	filterContainer: HTMLElement,
}

export class CalendarToolbar
{
	#calendar: Calendar;
	#addButtonMenu: CalendarAddButtonMenu;
	#settings: CalendarSettings;
	#filter: Filter;

	constructor(params: Params)
	{
		this.#calendar = new Calendar({
			type: params.type,
			// eslint-disable-next-line no-constant-binary-expression
			locationAccess: params.locationAccess === '1' ?? false,
			userId: params.userId,
			ownerId: params.ownerId,
		});

		this.#filter = new Filter({
			filterId: params.filterId,
			filterContainer: params.filterContainer,
		});
	}

	renderAddBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.CalendarToolbar: HTMLElement for add btn not found');
		}

		Dom.append(this.#renderAddBtn(), container);
	}

	renderSettingsBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.CalendarToolbar: HTMLElement for settings btn not found');
		}

		Dom.append(this.#renderSettingsBtn(), container);
	}

	#renderAddBtn(): HTMLElement
	{
		const { node, mainBtn, menuBtn } = Tag.render`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps" ref="node">
				<button class="ui-btn-main" data-id="spaces-calendar-add-main-btn" ref="mainBtn">
					${Loc.getMessage('SN_SPACES_CALENDAR_CREATE_MEETING')}
				</button>
				<button 
					class="ui-btn-menu" 
					id="spaces-calendar-toolbar-menu" 
					data-id="spaces-calendar-add-menu-btn"
					ref="menuBtn"
				>		
				</button>
			</div>
		`;

		Event.bind(mainBtn, 'click', this.#addMainClick.bind(this));
		Event.bind(menuBtn, 'click', this.#addMenuClick.bind(this));

		return node;
	}

	#renderSettingsBtn(): HTMLElement
	{
		const node = Tag.render`
			<button 
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes" 
				data-id="spaces-calendar-settings-btn"
			>
				<div class="ui-icon-set --more" style="--ui-icon-set__icon-color: white;"></div>
			</button>
		`;

		Event.bind(node, 'click', this.#settingsClick.bind(this));

		return node;
	}

	#createAddSettings(): void
	{
		this.#addButtonMenu = new CalendarAddButtonMenu({
			bindElement: document.getElementById('spaces-calendar-toolbar-menu'),
			calendar: this.#calendar,
		});
	}

	#addMainClick(): void
	{
		this.#calendar.addEvent();
	}

	#addMenuClick(): void
	{
		if (!this.#addButtonMenu)
		{
			this.#createAddSettings();
		}

		this.#addButtonMenu.show();
	}

	#settingsClick(event): void
	{
		if (!this.#settings)
		{
			this.#settings = new CalendarSettings({
				bindElement: event.target,
				calendar: this.#calendar,
			});
		}

		this.#settings.show();
	}
}
