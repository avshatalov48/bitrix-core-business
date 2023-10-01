import {DefaultFooter, Dialog, Item} from 'ui.entity-selector';
import {Dom, Loc, Tag, Event} from 'main.core';

export class Footer extends DefaultFooter
{
	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.selectAllButton = Tag.render`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${Loc.getMessage('ENTITY_EDITOR_PRODUCT_SET_ALL_SELECT_LABEL')}</div>`;
		Event.bind(this.selectAllButton, 'click', this.selectAll.bind(this));
		this.deselectAllButton = Tag.render`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${Loc.getMessage('ENTITY_EDITOR_PRODUCT_SET_ALL_DESELECT_LABEL')}</div>`;
		Event.bind(this.deselectAllButton, 'click', this.deselectAll.bind(this));

		this.getDialog().subscribe('Item:onSelect', this.onItemStatusChange.bind(this));
		this.getDialog().subscribe('Item:onDeselect', this.onItemStatusChange.bind(this));

		this.getDialog().subscribe('onLoad', this.toggleSelectButtons.bind(this));
	}

	getContent(): HTMLElement
	{
		return Tag.render`
			<div class="ui-selector-search-footer-box">
				${this.selectAllButton}
				${this.deselectAllButton}
			</div>
		`;
	}

	toggleSelectButtons(): void
	{
		if (this.getAmountSelectedItems() === this.getAmountItems())
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
		if (this.getAmountSelectedItems() === this.getAmountItems())
		{
			return;
		}

		const activeTab = this.getDialog().getActiveTab();
		if (activeTab)
		{
			let children = activeTab.getRootNode().getChildren();
			if (children.items.length > 0)
			{
				children.forEach((child) => {
					child.getItem().select();
				});
			}
		}
		else
		{
			this
				.getDialog()
				.getItems()
				.forEach((item: Item) => {
					item.select();
				})
			;
		}
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

	getAmountSelectedItems(): number
	{
		let amount = 0;

		const activeTab = this.getDialog().getActiveTab();
		if (activeTab)
		{
			amount = activeTab.getRootNode().getChildren().items.filter(item => item.item.isSelected()).length;
		}
		else
		{
			amount = this.getDialog().getSelectedItems().length;
		}

		return amount;
	}

	getAmountItems(): number
	{
		let amount = 0;

		const activeTab = this.getDialog().getActiveTab();
		if (activeTab)
		{
			amount = activeTab.getRootNode().getChildren().items.length;
		}
		else
		{
			amount = this.getDialog().getItems().length;
		}

		return amount;
	}
}