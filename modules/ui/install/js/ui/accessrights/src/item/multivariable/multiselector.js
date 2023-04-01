import ColumnItemOptions from "../../columnitem";
import {Event, Text, Tag, Dom, Type, Loc} from 'main.core';
import {Dialog, Item} from 'ui.entity-selector';

import Changer from "../changer";
import {EventEmitter} from "main.core.events";
import Footer from "./footer";
import 'ui.hint';

type VariableItem = {
	id: number,
	title: string,
}

export default class MultiSelector extends Changer
{
	static TYPE = 'multivariables';

	constructor(options: ColumnItemOptions)
	{
		super(options);
		this.variables = options.variables || [];
		this.enableSearch = options.enableSearch ?? false;
		this.placeholder = options.placeholder || '';
		this.hintTitle = options.hintTitle || '';
		this.allSelectedCode = Text.toNumber(options.allSelectedCode || -1);
		this.showAvatars = options.showAvatars ?? true;
		this.compactView = options.compactView ?? false;
		this.currentValue = Type.isArray(options.currentValue) ? options.currentValue : [];
		this.currentValue = this.currentValue.map(value => Text.toNumber(value));
		this.selectedValues = this.currentValue;

		this.variables = this.variables.map((item) => {
			item.entityId = item.entityId || 'editor-right-item';
			item.tabs = 'recents';
			return item;
		});

		this.selector = this.createSelector();
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.UI.AccessRights:reset', this.reset.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refresh.bind(this));
	}

	createSelector(): Dialog
	{
		return new Dialog({
			height: 300,
			id: this.getId(),
			context: 'editor-right-items',
			enableSearch: this.enableSearch,
			multiple: true,
			dropdownMode: true,
			compactView: this.compactView,
			showAvatars: this.showAvatars,
			selectedItems: this.getSelected(),
			searchOptions: {
				allowCreateItem: false
			},
			events: {
				'Item:onSelect': this.setSelectedInputs.bind(this),
				'Item:onDeselect': this.setSelectedInputs.bind(this),
			},
			entities: [
				{
					id: 'editor-right-item',
				}
			],
			items: this.variables,
			footer: Footer,
		});
	}

	render(): HTMLElement
	{
		let title = '';
		if (this.includesSelected(this.allSelectedCode))
		{
			title = Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_ACCEPTED');
		}
		else
		{
			const titles = [];
			this.getSelected()?.forEach((item) => {
				titles.push(item.title)
			});

			if (titles.length > 0 )
			{
				const firstItem = titles[0];
				title =
					titles.length - 1 > 0
						? Loc.getMessage(
							'JS_UI_ACCESSRIGHTS_HAS_SELECTED_ITEMS',
							{
								'#FIRST_ITEM_NAME#':
									firstItem.length > 10
										? firstItem.slice(0, 10) + '...'
										: firstItem
								,
								'#COUNT_REST_ITEMS#': titles.length - 1,
							}
						)
						: firstItem
				;
			}
			else
			{
				title = Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD');
			}
		}

		let hint = '';
		if (this.selector.getSelectedItems().length > 0)
		{
			const hintTitle =
				Type.isStringFilled(this.hintTitle)
					? this.hintTitle
					: Loc.getMessage('JS_UI_ACCESSRIGHTS_SELECTED_ITEMS_TITLE')
			;
			hint += '<p>' + hintTitle + ':</p>';
			hint += '<ul>';
			this.selector.getSelectedItems().forEach((item: Item) => hint += '<li>' + Text.encode(item.getTitle()))
			hint += '</ul>';
		}

		const variablesValue = Tag.render`
				<div class='ui-access-rights-column-item-text-link' data-hint-html data-hint-no-icon data-hint="${Text.encode(hint)}">
					${Text.encode(title)}
				</div>
			`;

		Event.bind(variablesValue, 'click', this.showSelector.bind(this));

		Dom.append(variablesValue, this.getChanger());

		BX.UI.Hint.init(this.getChanger());
		return this.getChanger();
	}

	refresh(): HTMLElement
	{
		if (this.isModify)
		{
			this.currentValue = this.selectedValues;
			this.reset();
		}
	}

	reset(): HTMLElement
	{
		if (this.isModify)
		{
			this.selectedValues = this.currentValue;
			this.selector = this.createSelector();
			this.getChanger().innerHTML = '';
			this.adjustChanger();
			this.render();
		}
	}

	getSelected(): ?VariableItem[]
	{
		if (this.includesSelected(this.allSelectedCode))
		{
			return this.variables;
		}

		return this.variables.filter(variable => this.includesSelected(variable.id));
	}

	includesSelected(item): boolean
	{
		return this.selectedValues.includes(Text.toNumber(item));
	}

	showSelector(event: Event): void
	{
		this.selector.show();
	}

	setSelectedInputs(): void
	{
		const selected = this.selector.getSelectedItems();
		this.selectedValues = [];
		if (selected.length === this.variables.length)
		{
			this.selectedValues.push(this.allSelectedCode);
		}
		else
		{
			selected.forEach((item) => {
				this.selectedValues.push(Text.toNumber(item.id));
			});
		}

		this.getChanger().innerHTML = '';
		if (!this.isModify)
		{
			this.adjustChanger();
		}

		this.render();

		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:selectAccessItems', this);
	}
}
