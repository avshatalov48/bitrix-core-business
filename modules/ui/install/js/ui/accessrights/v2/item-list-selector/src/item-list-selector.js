import { Dom, Tag, Type, Text } from 'main.core';
import { Popup } from 'main.popup';
import { SaveButton, CancelButton, ButtonSize, AirButtonStyle } from 'ui.buttons';
import type { VueCreateAppResult } from 'ui.vue3';
import { BitrixVue } from 'ui.vue3';
import { Selector } from './components/selector';
import 'ui.icon-set';
import './item-list-selector.css';
import { ItemListSelectorOptions, Item } from './item-list-selector-options';

export class ItemListSelector
{
	#targetNode: HTMLElement;
	#content: HTMLElement;
	#items: Array<Item>;
	#app: VueCreateAppResult;
	#title: string;
	#subtitle: string;
	#events: Object;
	#maxSelected: number;
	#popup: Popup;
	#rootComponent: any;

	constructor(params: ItemListSelectorOptions)
	{
		if (!Type.isDomNode(params.targetNode))
		{
			throw new Error('Target DOM node not found');
		}

		this.#targetNode = params.targetNode;
		this.#items = params.items;
		this.#title = params.title;
		this.#subtitle = params.subtitle;
		this.#events = params.events;
		this.#maxSelected = Type.isInteger(params.maxSelected) ? params.maxSelected : null;
	}

	#getPopup(): Popup
	{
		if (this.#popup)
		{
			return this.#popup;
		}

		this.#popup = new Popup({
			width: 409,
			fixed: true,
			autoHide: true,
			closeByEsc: true,
			contentPadding: 0,
			padding: 24,
			className: 'ui-item-selector-popup',
			bindElement: this.#targetNode,
			titleBar: { content: this.#getHeader() },
			content: this.#getContent(),
			buttons: [
				new SaveButton({
					onclick: this.#save.bind(this),
					size: ButtonSize.LARGE,
					style: AirButtonStyle.FILLED,
					useAirDesign: true,
				}),
				new CancelButton({
					onclick: this.hide.bind(this),
					size: ButtonSize.LARGE,
					style: AirButtonStyle.OUTLINE,
					useAirDesign: true,
				}),
			],
			events: {
				onClose: this.hide.bind(this),
			},
		});

		return this.#popup;
	}

	#createSelector(): void
	{
		const rootProps = {
			items: this.#items,
			maxSelected: this.#maxSelected,
		};
		this.#app = BitrixVue.createApp(Selector, rootProps);
		this.#rootComponent = this.#app.mount(this.#getContent());
	}

	#getContent(): HTMLElement
	{
		if (!this.#content)
		{
			this.#content = Tag.render`
				<div class="ui-item-selector-content"></div>
			`;
		}

		return this.#content;
	}

	show(): void
	{
		this.#getPopup().show();
		this.#createSelector();
	}

	hide(): void
	{
		if (this.#app)
		{
			this.#app.unmount();
			this.#app = null;
		}

		if (this.#events.onHide)
		{
			this.#events.onHide();
		}

		this.#popup.destroy();
	}

	#save(): void
	{
		if (this.#events.onSave)
		{
			this.#events.onSave();
		}

		this.hide();
	}

	getSelectedItems(): Array<Item>
	{
		return this.getCurrentOrder().filter((item) => item.selected) ?? [];
	}

	getCurrentOrder(): Array<Item>
	{
		if (!this.#rootComponent)
		{
			return [];
		}
		const items = [...this.#rootComponent.getOrder().values()];
		items.sort((item1, item2) => {
			const sortA = item1.sort ?? Infinity;
			const sortB = item2.sort ?? Infinity;

			return sortA - sortB;
		});

		return items;
	}

	#getHeader(): HTMLElement
	{
		return Tag.render`
			<div class="ui-item-selector-header">
				<div class="ui-item-selector-header-title">${Text.encode(this.#title)}</div>
				<div class="ui-item-selector-header-subtitle">${Text.encode(this.#subtitle)}</div>
			</div>
		`;
	}
}
