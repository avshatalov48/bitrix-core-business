import ColumnItemOptions from '../../columnitem';
import { Event, Text, Tag, Dom, Type, Loc } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Dialog, Item } from 'ui.entity-selector';

import Changer from '../changer';
import { ChangerOpts } from '../base';
import { EventEmitter } from 'main.core.events';
import Footer from './footer';
import 'ui.hint';

type VariableItem = {
	id: number,
	title: string,
}

interface MultiSelectorChangerOpts extends ChangerOpts {
	disableSelectAll?: boolean;
	useSelectedActions?: boolean;
}

export default class MultiSelector extends Changer
{
	static TYPE = 'multivariables';

	changerOptions: MultiSelectorChangerOpts;

	constructor(options: ColumnItemOptions)
	{
		super(options);
		this.variables = options.variables || [];
		this.enableSearch = options.enableSearch ?? false;
		this.placeholder = options.placeholder || '';
		this.hintTitle = options.hintTitle || '';
		this.allSelectedCode = String(options.allSelectedCode || -1);
		this.showAvatars = options.showAvatars ?? true;
		this.compactView = options.compactView ?? false;
		this.currentValue = Type.isArray(options.currentValue) ? options.currentValue.map((item) => String(item)) : [];

		this.selectedValues = this.currentValue.filter((val) => Boolean(val));

		this.variables = this.variables.map((item) => {
			item.entityId = item.entityId || 'editor-right-item';
			item.tabs = 'recents';
			if (item.selectedAction)
			{
				item.customData = { ...item.customData, selectedAction: item.selectedAction };
			}

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
				allowCreateItem: false,
			},
			events: {
				'Item:onSelect': this.#obSelectItem.bind(this),
				'Item:onDeselect': this.#onDeselectItem.bind(this),
			},
			entities: [
				{
					id: 'editor-right-item',
				}
			],
			items: this.variables,
			footer: this.#getDialogFooter(),
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
					: Loc.getMessage('JS_UI_ACCESSRIGHTS_SELECTED_ITEMS_TITLE_MSGVER_1')
			;
			hint += '<p>' + hintTitle + '</p>';
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

		this.selector.setTargetNode(this.getChanger());

		return this.getChanger();
	}

	refresh(): HTMLElement
	{
		if (this.isModify)
		{
			this.currentValue = [...this.selectedValues];
			this.reset();
		}
	}

	reset(): HTMLElement
	{
		if (this.isModify)
		{
			this.selectedValues = [...this.currentValue];
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

	includesSelected(itemId): boolean
	{
		return this.selectedValues.some((id) => String(id) === String(itemId));
	}

	showSelector(event: Event): void
	{
		this.selector.show();
	}

	#obSelectItem(event: BaseEvent): void
	{
		const addedItem = event.getData().item;
		const addedId = String(addedItem.id);

		if (this.changerOptions.useSelectedActions)
		{
			this.#useSelectedActionLogic(addedItem);
		}

		if (!this.selectedValues.includes(addedId))
		{
			this.selectedValues.push(addedId);
		}

		if (this.selectedValues.length === this.variables.length)
		{
			this.selectedValues = [this.allSelectedCode];
		}

		this.#afterSetupItems();
	}

	#onDeselectItem(event: BaseEvent): void
	{
		const removedItem = event.getData().item;
		const removedId = String(removedItem.id);

		if (this.selectedValues.includes(this.allSelectedCode))
		{
			const allWithoutRemoved = this.variables
				.map((variable) => String(variable.id))
				.filter((id) => id !== removedId)
			;

			this.selectedValues = allWithoutRemoved;
		}
		else
		{
			this.selectedValues = this.selectedValues.filter((id) => id !== removedId);
		}

		this.#afterSetupItems();
	}

	#afterSetupItems(): void
	{
		this.isModify = !this.#isArraysEqual(this.selectedValues, this.currentValue);

		this.getChanger().innerHTML = '';
		if (this.isModify)
		{
			this.addChangerHtmlClass();
		}
		else
		{
			this.removeChangerHtmlClass();
		}

		this.render();

		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:selectAccessItems', this);
	}

	#getDialogFooter(): ?Footer
	{
		if (this.changerOptions.disableSelectAll)
		{
			return null;
		}

		return Footer;
	}

	#useSelectedActionLogic(addedItem: Item): void
	{
		const selectedAction = addedItem.customData.get('selectedAction', null);

		if (selectedAction === 'clear-other')
		{
			const selected = this.selector.getSelectedItems();
			for (const item of selected)
			{
				if (addedItem.id === item.id)
				{
					continue;
				}

				item.deselect();
			}
		}
		else
		{
			const selected = this.selector.getSelectedItems();
			for (const item of selected)
			{
				if (addedItem.id === item.id)
				{
					continue;
				}

				const currSelectedAction = item.customData.get('selectedAction', null);
				if (currSelectedAction)
				{
					item.deselect();
				}
			}
		}
	}

	#isArraysEqual(a, b: ?string[]): boolean
	{
		if (a === b)
		{
			return true;
		}

		if (a === null || b === null)
		{
			return false;
		}

		if (a.length !== b.length)
		{
			return false;
		}

		const aClone = [...a];
		const bClone = [...b];
		aClone.sort();
		bClone.sort();

		for (let i = 0; i < a.length; i++)
		{
			if (aClone[i] !== bClone[i])
			{
				return false;
			}
		}

		return true;
	}
}
