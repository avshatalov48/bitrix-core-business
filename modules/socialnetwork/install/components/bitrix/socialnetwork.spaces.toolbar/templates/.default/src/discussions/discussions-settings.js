import { ajax, Dom, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';

type Params = {
	bindElement: HTMLElement,
	isSmartTrackingMode: string,
	mainFilterId: string,
}

export class DiscussionsSettings extends EventEmitter
{
	#menu: Menu;
	#isSmartTrackingMode: string;
	#mainFilterId: string;

	static SWITCHER = 'smart_tracking';
	static SELECTED = 'menu-popup-item-accept';
	static DESELECTED = 'menu-popup-item-none';

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.DiscussionsSettings');

		this.#mainFilterId = params.mainFilterId;
		this.#isSmartTrackingMode = String(params.isSmartTrackingMode) === 'Y';
		this.#menu = this.#createMenu(params.bindElement);
	}

	show(): void
	{
		this.#menu.show();
	}

	#switch(): void
	{
		this.#switchMode()
			.then((result) => {
				this.#switchStyle(result.data.mode);
				this.#refresh();
			});
	}

	#createMenu(bindElement: HTMLElement): Menu
	{
		const menu = new Menu({
			id: 'spaces-discussions-settings',
			bindElement,
			closeByEsc: true,
		});

		menu.addMenuItem({
			id: DiscussionsSettings.SWITCHER,
			text: Loc.getMessage('SN_SPACES_DISCUSSIONS_SETTINGS_SMART_TRACKING'),
			className: this.#isSmartTrackingMode ? DiscussionsSettings.DESELECTED : DiscussionsSettings.SELECTED,
			onclick: this.#switch.bind(this),
		});

		menu.getMenuItem();

		return menu;
	}

	#getStyleFromMode(mode: string): string
	{
		if (mode === 'Y')
		{
			return DiscussionsSettings.DESELECTED;
		}

		return DiscussionsSettings.SELECTED;
	}

	#switchMode(): Promise
	{
		return ajax.runAction(
			'socialnetwork.api.livefeed.spaces.switcher.track',
			{
				data: {
					switcher: {
						type: DiscussionsSettings.SWITCHER,
						spaceId: 0,
					},
					space: 0,
				},
			},
		);
	}

	#switchStyle(mode: string): string
	{
		const item = this.#menu.getMenuItem(DiscussionsSettings.SWITCHER);
		Dom.removeClass(item.layout.item, DiscussionsSettings.SELECTED);
		Dom.removeClass(item.layout.item, DiscussionsSettings.DESELECTED);
		Dom.addClass(item.layout.item, this.#getStyleFromMode(mode));
	}

	#refresh()
	{
		const filter = BX.Main.filterManager.getById(this.#mainFilterId);

		if (filter instanceof BX.Main.Filter)
		{
			filter.getApi().apply();
		}
	}
}