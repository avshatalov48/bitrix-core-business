import { Dom, Event } from 'main.core';
import { Menu, MenuManager } from 'main.popup';
import { Selector } from './selector';

export class SelectorMenu extends Selector
{
	#menu: Menu;

	constructor(params)
	{
		params.items = params.items.map((item) => ({
			value: item.id,
			name: item.text,
			...item,
			onclick: (event, item) => {
				this.getInputNode().value = item.value;
				this.#menu.close();
			},
		}));

		super(params);

		Dom.style(this.getInputNode(), 'pointer-events', 'none');
		Dom.style(this.getSelector(), 'cursor', 'pointer');
		Event.bind(this.getSelector(), 'click', this.#showMenu.bind(this));
	}

	prefixId(): string
	{
		return 'menu_';
	}

	#showMenu(): void
	{
		const handleScroll = () => {
			const popup = this.#menu.getPopupWindow();
			popup.adjustPosition();

			const popupRect = popup.bindElement.getBoundingClientRect();
			if (popupRect.top > window.innerHeight || popupRect.bottom < 0)
			{
				this.#menu.close();
			}
		};

		this.#menu = MenuManager.create({
			id: `ui-form-elements-menu${this.getId()}`,
			bindElement: this.getInputNode(),
			items: this.getItems().map((item) => {
				const selected = item.value === this.getValue() ? 'ui-form-elements-menu-item --selected' : '';

				return {
					...item,
					className: item.className ? `${item.className} ${selected}` : `menu-popup-no-icon ${selected}`,
				};
			}),
			events: {
				onShow: () => {
					const popup = this.#menu.getPopupWindow();
					const elementWidth = popup.bindElement.offsetWidth;

					popup.setOffset({ offsetLeft: 0, offsetTop: 5 });
					popup.setWidth(elementWidth);
					popup.adjustPosition();

					Event.bind(window, 'scroll', handleScroll, true);
				},
				onClose: () => {
					this.#menu.destroy();

					Event.unbind(window, 'scroll', handleScroll, true);
				},
			},
		});

		this.#menu.show();
	}
}
