import { Dom, Event, Loc, Tag } from 'main.core';
import { DefaultFooter, Dialog, Item } from 'ui.entity-selector';

export class Footer extends DefaultFooter
{
	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.selectAllButton = Tag.render`<div class="ui-selector-footer-link">${
			Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_SELECT_LABEL')
		}</div>`;
		Dom.hide(this.selectAllButton);
		Event.bind(this.selectAllButton, 'click', this.#selectAll.bind(this));

		this.deselectAllButton = Tag.render`<div class="ui-selector-footer-link">${
			Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_DESELECT_LABEL')
		}</div>`;
		Dom.hide(this.deselectAllButton);
		Event.bind(this.deselectAllButton, 'click', this.#deselectAll.bind(this));

		this.getDialog().subscribe('Item:onSelect', this.#onItemStatusChange.bind(this));
		this.getDialog().subscribe('Item:onDeselect', this.#onItemStatusChange.bind(this));
	}

	getContent(): HTMLElement | HTMLElement[] | string | null
	{
		this.#toggleSelectButtons();

		return [this.selectAllButton, this.deselectAllButton];
	}

	#toggleSelectButtons(): void
	{
		if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length)
		{
			Dom.hide(this.selectAllButton);
			Dom.show(this.deselectAllButton);
		}
		else
		{
			Dom.show(this.selectAllButton);
			Dom.hide(this.deselectAllButton);
		}
	}

	#selectAll(): void
	{
		this
			.getDialog()
			.getItems()
			.forEach((item: Item) => {
				item.select();
			})
		;
	}

	#deselectAll(): void
	{
		this
			.getDialog()
			.getSelectedItems()
			.forEach((item: Item) => {
				item.deselect();
			})
		;
	}

	#onItemStatusChange(): void
	{
		this.#toggleSelectButtons();
	}
}
