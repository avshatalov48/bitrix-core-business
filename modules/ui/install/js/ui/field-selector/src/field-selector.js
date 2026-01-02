import { Dom, Tag, Text, Type } from 'main.core';
import { EventEmitter, type BaseEvent } from 'main.core.events';
import { type Item, type ItemId, TagSelector, type TagSelectorOptions, type EntityOptions } from 'ui.entity-selector';
import { FieldSelectorConfig } from './config/field-selector-config';
import { TabMessages } from './config/tab-messages';
import ValueType from './value/value-type';
import { BaseCollection } from './value/base-collection';
import { IntegerCollection } from './value/integer-collection';
import { StringCollection } from './value/string-collection';

export class FieldSelector
{
	#state: boolean = true;
	#containerId: string = '';
	#fieldName: string = '';
	#multiple: boolean = false;
	#valueCollection: BaseCollection;
	#context: ?string = null;
	#entities: EntityOptions[] = [];
	#searchMessages: TabMessages = {};
	#changeEvents: string[] = [];

	constructor(selectorConfig: FieldSelectorConfig)
	{
		const config: FieldSelectorConfig = Type.isPlainObject(selectorConfig) ? selectorConfig : {};

		this.initState();

		this.setContainerId(config.containerId);
		this.setFieldName(config.fieldName);
		this.setMultiple(config.multiple);

		this.setContext(config.context);
		this.setEntities(config.entities);

		this.initValueCollection(config.collectionType);
		if (!this.isStateSuccess())
		{
			return;
		}
		this.setValues(Type.isArray(config.selectedItems) ? config.selectedItems : [config.selectedItems]);

		this.setSearchMessages(config.searchMessages);
		this.setChangeEvents(config.changeEvents);
	}

	initState(): void
	{
		this.#state = true;
	}

	isStateSuccess(): boolean
	{
		return this.#state;
	}

	showError(error): void
	{
		this.#state = false;
		if (Type.isStringFilled(error))
		{
			console.error(`BX.UI.FieldSelector: ${error}`);
		}
	}

	setContainerId(containerId): void
	{
		this.#containerId = Type.isStringFilled(containerId) ? containerId : '';
		if (this.#containerId === '')
		{
			this.showError('containerId is empty. Selector is can\'t be used');
		}
	}

	getContainerId(): string
	{
		return this.#containerId;
	}

	setFieldName(fieldName): void
	{
		this.#fieldName = Type.isStringFilled(fieldName) ? fieldName : '';
		if (this.#fieldName === '')
		{
			this.showError('fieldName is empty. Selector is can\'t be used');
		}
	}

	getFieldName(): string
	{
		return this.#fieldName;
	}

	setMultiple(multiple): void
	{
		this.#multiple = Type.isBoolean(multiple) ? multiple : false;
	}

	getMultiple(): boolean
	{
		return this.#multiple;
	}

	getTagSelectorContainerId(): string
	{
		return `${this.getContainerId()}_selector`;
	}

	getTagResultContainerId(): string
	{
		return `${this.getContainerId()}_results`;
	}

	getTagSelectorControlId(): string
	{
		return `${this.getContainerId()}Control`;
	}

	initValueCollection(valueType: string): void
	{
		if (!ValueType.isValid(valueType))
		{
			this.showError('Unknown value collection type. Selector is can\'t be used');

			return;
		}

		switch (valueType)
		{
			case ValueType.STRING:
				this.#valueCollection = new StringCollection();
				break;
			case ValueType.INTEGER:
				this.#valueCollection = new IntegerCollection();
				break;
			default:
				break;
		}
	}

	setValues(rawValues: []): void
	{
		this.#valueCollection.set(rawValues);
	}

	getValues(): []
	{
		return this.#valueCollection.get();
	}

	setContext(context: ?string): void
	{
		this.#context = context;
	}

	getContext(): ?string
	{
		return this.#context ?? null;
	}

	setEntities(entities: EntityOptions[]): void
	{
		this.#entities = [];
		if (!Type.isArrayFilled(entities))
		{
			this.showError('Entity list is empty. Selector is can\'t be used');

			return;
		}

		this.#entities = entities;
	}

	getEntities(): EntityOptions[]
	{
		return this.#entities;
	}

	setSearchMessages(messages): void
	{
		if (Type.isPlainObject(messages))
		{
			this.#searchMessages.title = Type.isStringFilled(messages.title) ? messages.title : '';
			this.#searchMessages.subtitle = Type.isStringFilled(messages.subtitle) ? messages.subtitle : '';
		}
		else
		{
			this.#searchMessages.title = '';
			this.#searchMessages.subtitle = '';
		}
	}

	getSearchTabTitle(): string
	{
		return this.#searchMessages.title;
	}

	getSearchSubtitle(): string
	{
		return this.#searchMessages.subtitle;
	}

	setChangeEvents(events): void
	{
		this.#changeEvents = [];
		if (Type.isArrayFilled(events))
		{
			events.forEach((value): void => {
				if (Type.isStringFilled(value))
				{
					this.#changeEvents.push(value);
				}
			});
		}
	}

	getChangeEvents(): string[]
	{
		return this.#changeEvents;
	}

	render(): void
	{
		if (!this.isStateSuccess())
		{
			return;
		}

		const containerId: string = this.getContainerId();
		const container = document.getElementById(containerId);
		if (!Type.isElementNode(container))
		{
			this.showError(`dom-container ${containerId} is absent. Selector is can't be used`);

			return;
		}

		const tagSelectorContainer = Tag.render`
			<div id="${this.getTagSelectorContainerId()}"></div>
		`;
		Dom.append(tagSelectorContainer, container);

		const tagResult = Tag.render`
			<div id="${this.getTagResultContainerId()}"></div>
		`;
		Dom.append(tagResult, container);

		this.renderSelectedItems(this.getValues());

		const tagSelectorConfig: TagSelectorOptions = {
			id: this.getTagSelectorControlId(),
			multiple: this.getMultiple(),
			dialogOptions: {
				id: this.getTagSelectorControlId(),
				context: this.getContext(),
				multiple: this.getMultiple(),
				preselectedItems: this.getValues(),
				entities: this.getEntities(),
				searchOptions: {
					allowCreateItem: false,
				},
				searchTabOptions: {
					stub: true,
					stubOptions: {
						title: Text.encode(this.getSearchTabTitle()),
						subtitle: Text.encode(this.getSearchSubtitle()),
						arrow: false,
					},
				},
				events: {
					'Item:onSelect': this.updateSelectedItems.bind(this),
					'Item:onDeselect': this.updateSelectedItems.bind(this),
				},
			},
		};

		const tagSelector = new TagSelector(tagSelectorConfig);
		tagSelector.renderTo(tagSelectorContainer);
	}

	renderSelectedItems(items: ItemId[]): void
	{
		const tagResult = document.getElementById(this.getTagResultContainerId());
		if (!Type.isDomNode(tagResult))
		{
			return;
		}

		const fieldName = this.getFieldName();
		tagResult.innerHTML = '';
		if (items.length > 0)
		{
			items.forEach((value: ItemId): void => {
				const hiddenValue = Tag.render`
					<input type="hidden" name="${fieldName}" value="${Tag.safe`${value[1].toString()}`}">
				`;
				Dom.append(hiddenValue, tagResult);
			});
		}
		else
		{
			const emptyValue = Tag.render`
				<input type="hidden" name="${fieldName}" value="">
			`;
			Dom.append(emptyValue, tagResult);
		}
	}

	updateSelectedItems(event: BaseEvent): void
	{
		const dialog = event.getTarget();
		if (!dialog.isMultiple())
		{
			dialog.hide();
		}

		const selectedItems = dialog.getSelectedItems();
		if (Type.isArray(selectedItems))
		{
			const parsedValues = [];
			selectedItems.forEach((item: Item): void => {
				parsedValues.push([item.getEntityId(), item.getId()]);
			});
			this.setValues(parsedValues);
			this.renderSelectedItems(parsedValues);
			const eventList: string[] = this.getChangeEvents();
			eventList.forEach((changeEvent: string): void => {
				EventEmitter.emit(changeEvent);
			});
		}
	}
}
