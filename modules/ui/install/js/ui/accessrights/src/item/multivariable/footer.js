import {DefaultFooter, Dialog, Item} from 'ui.entity-selector';
import {Dom, Loc, Tag, Event} from 'main.core';

export default class Footer extends DefaultFooter
{
	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.selectAllButton = Tag.render`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_SELECT_LABEL')}</div>`;
		Event.bind(this.selectAllButton, 'click', this.selectAll.bind(this));
		this.deselectAllButton = Tag.render`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_DESELECT_LABEL')}</div>`;
		Event.bind(this.deselectAllButton, 'click', this.deselectAll.bind(this));

		this.getDialog().subscribe('Item:onSelect', this.onItemStatusChange.bind(this));
		this.getDialog().subscribe('Item:onDeselect', this.onItemStatusChange.bind(this));
	}

	getContent(): HTMLElement
	{
		this.toggleSelectButtons();

		return Tag.render`
			<div class="ui-selector-search-footer-box">
				${this.selectAllButton}
				${this.deselectAllButton}
			</div>
		`;
	}

	toggleSelectButtons(): void
	{
		if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length)
		{
			if (Dom.hasClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide'))
			{
				Dom.addClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
				Dom.removeClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
			}
		}
		else if (Dom.hasClass(this.selectAllButton, 'ui-selector-search-footer-label--hide'))
		{
			Dom.addClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
			Dom.removeClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
		}
	}

	selectAll(): void
	{
		if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length)
		{
			return;
		}

		this
			.getDialog()
			.getItems()
			.forEach((item: Item) => {
				item.select();
			})
		;
	}

	deselectAll(): void
	{
		this
			.getDialog()
			.getSelectedItems()
			.forEach((item: Item) => {
				item.deselect();
			})
		;
	}

	onItemStatusChange(): void
	{
		this.toggleSelectButtons();
	}
}