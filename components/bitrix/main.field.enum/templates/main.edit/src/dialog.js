import { Dom, Event, Runtime, Tag } from 'main.core';
import { BaseEvent } from 'main.core.events';
import type { DialogOptions } from 'ui.entity-selector';
import { Dialog as EntitySelector, Item, TagItem, TagSelector } from 'ui.entity-selector';

type Element = {
	VALUE: string,
	NAME: string,
	IS_SELECTED?: boolean,
};

type Params = {
	targetNodeId: string,
	fieldName: string,
	fieldNameForEvent: string,
	isMultiple: string,
	items: [],
	emptyValueTitle: ?string,
	fieldTitle: string,
	context: string,
	messages: string[],
}

type DialogItem = {
	id: string,
	entityId: string,
	title: string,
	tabs: string,
}

export class Dialog
{
	targetNode: HTMLElement = null;
	wrapper: HTMLElement = null;
	valuesWrapper: HTMLElement = null;
	input: HTMLElement = null;
	fieldName: string;
	fieldNameForEvent: string;
	context: string;
	emptyValueTitle: ?string;
	fieldTitle: string;
	isMultiple: boolean;
	dialogSelector: EntitySelector = null;
	tagSelector: TagSelector = null;
	selectedItems: Set<DialogItem> = new Set();
	items: Set<DialogItem> = new Set();
	messages: string[] = [];

	constructor(params: Params): void
	{
		this.targetNode = document.getElementById(params.targetNodeId);

		if (this.targetNode === null)
		{
			throw new Error(`Target node: ${params.targetNodeId} not found`);
		}

		this.fieldName = params.fieldName.toLowerCase();
		this.fieldNameForEvent = params.fieldNameForEvent;
		this.emptyValueTitle = params.emptyValueTitle;
		this.fieldTitle = params.fieldTitle;
		this.context = params.context;
		this.messages = params.messages;
		this.isMultiple = (params.isMultiple === 'true');

		this.prepareItems(params);

		this.createWrappers();
		if (this.isMultiple)
		{
			Runtime.loadExtension('ui.entity-selector').then(exports => {
				this.tagSelector = this.getTagSelector(exports.TagSelector);
				this.tagSelector.renderTo(this.wrapper);
				this.adjustLayout(false);
			});
		}
		else
		{
			Runtime.loadExtension('ui.entity-selector').then(exports => {
				this.dialogSelector = this.getDialogSelector(exports.Dialog);
				this.prepareInput(this.targetNode);

				Event.bind(this.targetNode, 'click', () => {
					this.show();
				});

				if (this.selectedItems.size)
				{
					const selectedItems = [...this.selectedItems];
					this.input.value = selectedItems[0].title;
				}
				this.adjustLayout(false);
			});
		}
	}

	prepareItems(params: Params)
	{
		let values = params.items;
		if (!Array.isArray(values))
		{
			if (values === '')
			{
				return;
			}
			values = [values];
		}

		const entityId = this.fieldName;
		values.forEach((element: Element) => {
			const setItem = {
				id: element.VALUE,
				entityId: entityId,
				title: element.NAME,
				tabs: entityId
			};
			this.items.add(setItem);
			if (element.IS_SELECTED === true)
			{
				this.selectedItems.add(setItem);
			}
		});
	}

	prepareInput(node: HTMLElement): void
	{
		this.input = Tag.render`
			<input 
				name="${node.id}_input" 
				type="text" 
				class="ui-ctl-element main-ui-control main-enum-dialog-input" 
				autocomplete="off"
				placeholder="${this.emptyValueTitle}"
			/>
		`;
		Dom.append(this.input, node);

		const dialogSelector = this.dialogSelector;
		const input = this.input;

		Event.bind(this.input, 'keyup', (event: Event) => {
			if (!input.value.length)
			{
				dialogSelector.search('');
				dialogSelector.clearSearch();
				dialogSelector.deselectAll();
				dialogSelector.hide();
			}
			else
			{
				const selectedItems = dialogSelector.getSelectedItems();
				if (!selectedItems.some(item => {
					return (item.title.getText() === input.value);
				}))
				{
					dialogSelector.show();
					dialogSelector.clearSearch();
					dialogSelector.search(input.value);
				}
			}
		});
	}

	createWrappers(): void
	{
		this.createWrapper();
		this.createValuesWrapper();
	}

	createWrapper(): void
	{
		this.wrapper = Tag.render`<div class="ui-ctl-w100"></div>`;
		this.targetNode.appendChild(this.wrapper);
	}

	createValuesWrapper(): void
	{
		this.valuesWrapper = Tag.render`<div></div>`;
		this.wrapper.appendChild(this.valuesWrapper);
	}

	show(): void
	{
		this.dialogSelector.show();
	}

	getDialogSelector(entitySelector: EntitySelector): EntitySelector
	{
		const options = this.getDialogOptions();
		options.targetNode = this.targetNode;
		options.events = {
			'Item:onSelect': this.onElementSelect.bind(this),
			'Item:onDeselect': this.onElementDeselect.bind(this),
		};

		return new entitySelector(options);
	}

	getTagSelector(tagSelector: TagSelector): TagSelector
	{
		return new tagSelector({
			addButtonCaption: this.getMessage('addButtonCaption'),
			addButtonCaptionMore: this.getMessage('addButtonCaptionMore'),
			showCreateButton: false,
			dialogOptions: this.getDialogOptions(),
			items: this.getDialogSelectedItems(),
			height: 240,
			textBoxWidth: '100%',
			events: {
				onTagAdd: this.onElementSelect.bind(this),
				onTagRemove: this.onElementDeselect.bind(this),
			}
		});
	}

	getDialogOptions(): DialogOptions
	{
		return {
			context: this.context,
			items: this.getDialogItems(),
			selectedItems: this.getDialogSelectedItems(),
			height: 240,
			dropdownMode: true,
			showAvatars: false,
			compactView: true,
			multiple: this.isMultiple,
			enableSearch: false,
			tabs: [
				{
					id: this.fieldName,
					title: this.fieldTitle
				},
			],
		};
	}

	getDialogItems(): Array
	{
		return [...this.items];
	}

	getDialogSelectedItems(): Array
	{
		return [...this.selectedItems];
	}

	onElementSelect(event: BaseEvent): void
	{
		const item = this.getItemFromEventData(event);
		if (!this.isMultiple)
		{
			this.selectedItems.clear();
			this.input.value = item.getTitle();
		}

		this.selectedItems.add(this.createOption(item));
		this.adjustLayout();
	}

	onElementDeselect(event: BaseEvent): void
	{
		const item = this.getItemFromEventData(event);
		const unselectedItem = this.createOption(item);
		if (!this.isMultiple)
		{
			this.selectedItems.clear();
			this.input.value = '';
		}

		// remove object "unselectedItem" from selectedItems array
		this.selectedItems = new Set(
			[...this.selectedItems].filter(
				(element:DialogItem) => (JSON.stringify(element) !== JSON.stringify(unselectedItem))
			)
		);
		this.adjustLayout();
	}

	getItemFromEventData(event: BaseEvent): Item|TagItem
	{
		return (this.isMultiple ? event.getData().tag : event.getData().item);
	}

	createOption(item: Item): DialogItem
	{
		return {
			id: item.id,
			entityId: this.fieldName,
			title: item.title,
			tabs: this.fieldName,
		}
	}

	adjustLayout(isChanged: boolean = true): void
	{
		this.clearValueItems();
		if (this.selectedItems.size)
		{
			this.selectedItems.forEach(item => {
				this.adjustItem(item.id)
			});
		}
		else
		{
			this.adjustItem('');
		}

		if (isChanged)
		{
			BX.fireEvent(document.getElementById(this.fieldNameForEvent), 'change');
		}
	}

	clearValueItems(): void
	{
		this.valuesWrapper.innerHTML = '';
	}

	adjustItem(id: string): void
	{
		this.valuesWrapper.appendChild(this.createInputTag(id));
	}

	createInputTag(id: string): HTMLElement
	{
		return Tag.render`
			<input name="${this.fieldName.toUpperCase()}" type="hidden" value="${id}"/>
		`;
	}

	getMessage(key: string): string|null
	{
		return (this.messages[key] ?? null);
	}
}
