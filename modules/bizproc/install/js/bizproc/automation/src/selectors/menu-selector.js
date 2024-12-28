import { Dom, Text, Event, Type, Tag } from 'main.core';
import { MenuManager } from 'main.popup';
import { SelectorContext } from 'bizproc.automation';
import { EventEmitter } from 'main.core.events';

const MENU_ITEM_CLASS_ACTIVE = 'menu-popup-item-accept';
const MENU_ITEM_CLASS_INACTIVE = 'menu-popup-item-none';

type Item = {
	title: string;
	value: mixed;
}

type MenuItem = {
	id: string;
	className: string;
	html: string;
	onclick: Function;
}
export class MenuSelector extends EventEmitter
{
	#selectedValues: Set = new Set();
	#items: Item[];
	#menuPopup: ?Menu = null;
	#openMenuButton: Element;
	#targetInput: Element;
	#name: string;
	#multiple: boolean;
	#fieldName: string;
	#hiddenSelect: ?Element;
	constructor(props: {
		context: SelectorContext,
	})
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation.Selector');
		this.context = props.context;
	}

	#parseTargetProperties()
	{
		const config = JSON.parse(this.#targetInput.getAttribute('data-config'));
		this.#name = config.name ?? 'Notification';
		this.#fieldName = config.fieldName ?? '';
		this.#items = config.options ? this.prepareItems(config.options) : [];
		this.#multiple = config.multiple ?? false;
		if (!Type.isNil(config.selected))
		{
			this.#setValues(config.selected);
		}
	}

	renderTo(targetInput: Element)
	{
		this.#targetInput = targetInput;

		this.#parseTargetProperties();
		this.#openMenuButton = Tag.render`<a class="bizproc-automation-popup-settings-button">${Text.encode(this.#name)}</a>`;
		Event.bind(this.#openMenuButton, 'click', this.#onShowPopup.bind(this));

		Dom.append(this.#openMenuButton, this.#targetInput);
	}

	prepareItems(options): Item[]
	{
		return Object.entries(options).map(([key, value]) => ({
			title: value,
			value: key,
		}));
	}

	#getPreparedMenuItems(): MenuItem[]
	{
		return this.#items.map((item: Item) => this.#getPreparedMenuItem(item));
	}

	#getPreparedMenuItem(item: Item): MenuItem
	{
		return {
			id: `menu-selector-menu-id-${item.value}`,
			className: this.#isValueSelected(item.value) ? MENU_ITEM_CLASS_ACTIVE : MENU_ITEM_CLASS_INACTIVE,
			onclick: this.#onMenuItemClick.bind(this, item.value),
			html: Text.encode(item.title),
		};
	}

	#isValueSelected(value: number | string): boolean
	{
		return this.#selectedValues.has(value);
	}

	#onMenuItemClick(value: number | string, event: PointerEvent, item)
	{
		if (this.#isValueSelected(value))
		{
			this.#removeValue(value);
			Dom.removeClass(item.getContainer(), MENU_ITEM_CLASS_ACTIVE);
			Dom.addClass(item.getContainer(), MENU_ITEM_CLASS_INACTIVE);
		}
		else
		{
			if (this.#multiple)
			{
				this.#addValue(value);
			}
			else
			{
				for (const menuItem of this.#menuPopup.menuItems)
				{
					Dom.removeClass(menuItem.getContainer(), MENU_ITEM_CLASS_ACTIVE);
				}
				this.#setValues([value]);
			}

			Dom.removeClass(item.getContainer(), MENU_ITEM_CLASS_INACTIVE);
			Dom.addClass(item.getContainer(), MENU_ITEM_CLASS_ACTIVE);
		}
	}

	#removeValue(value)
	{
		this.#selectedValues.delete(value);
	}

	#addValue(value)
	{
		this.#selectedValues.add(value);
	}

	#onShowPopup()
	{
		if (!this.#menuPopup)
		{
			const menuItems = this.#getPreparedMenuItems();
			const menuParams = {
				closeByEsc: true,
				autoHide: true,
				cacheable: true,
			};
			this.#menuPopup = MenuManager.create(Text.getRandom(), this.#openMenuButton, menuItems, menuParams);
		}
		this.#menuPopup.show();
	}

	onBeforeSave()
	{
		const hiddenSelect = Tag.render`
			<select
				name="${this.#fieldName + (this.#multiple ? '[]' : '')}"
				${this.#multiple ? 'multiple' : ''}
				hidden
			>
			</select>
		`;
		for (const value of this.#selectedValues.values())
		{
			if (Type.isNil(value))
			{
				continue;
			}
			const hiddenOption = Tag.render`
				<option value="${value}"></option>
			`;
			hiddenOption.selected = true;
			Dom.append(hiddenOption, hiddenSelect);
		}

		if (this.#hiddenSelect)
		{
			Dom.replace(this.#hiddenSelect, hiddenSelect);
		}
		else
		{
			Dom.append(hiddenSelect, this.#targetInput);
		}
		this.#hiddenSelect = hiddenSelect;
	}

	#setValues(values: Array): void
	{
		this.#clearAll();

		values.forEach((value: string) => {
			this.#addValue(value);
		});
	}

	#clearAll(): void
	{
		if (this.#selectedValues.size === 0)
		{
			return;
		}
		this.#selectedValues = new Set();
	}

	destroy()
	{
		this.#menuPopup?.close();
	}
}
