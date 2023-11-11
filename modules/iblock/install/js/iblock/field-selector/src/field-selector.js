import { Dom, Tag, Text, Type } from 'main.core';
import { type BaseEvent } from 'main.core.events';
import { type Item, type ItemId, TagSelector, type TagSelectorOptions } from 'ui.entity-selector';
import { FieldSelectorConfig } from './field-selector-config';
import { TabMessages } from './tab-messages';

export class FieldSelector
{
	state: boolean = true;
	containerId: string = '';
	fieldName: string = '';
	multiple: boolean = false;
	selectedItems: number[] = [];
	iblockId: number = 0;
	userType: string = '';
	entityId: string = '';
	searchMessages: TabMessages = {};

	constructor(selectorConfig: FieldSelectorConfig)
	{
		const config: FieldSelectorConfig = Type.isPlainObject(selectorConfig) ? selectorConfig : {};

		this.resetState();

		this.setContainerId(config.containerId);
		this.setFieldName(config.fieldName);
		this.setMultiple(config.multiple);
		this.setSelectedItems(config.selectedItems);
		this.setIblockId(config.iblockId);
		this.setUserType(config.userType);
		this.setEntityId(config.entityId);
		this.setSearchMessages(config.searchMessages);
	}

	resetState(): void
	{
		this.state = true;
	}

	isStateSuccess(): boolean
	{
		return this.state;
	}

	showError(error): void
	{
		this.state = false;
		if (Type.isStringFilled(error))
		{
			console.error(error);
		}
	}

	showWarning(warning): void
	{
		this.state = false;
		if (Type.isStringFilled(warning))
		{
			console.warn(warning);
		}
	}

	setContainerId(containerId): void
	{
		this.containerId = Type.isStringFilled(containerId) ? containerId : '';
		if (this.containerId === '')
		{
			this.showError('BX.Iblock.FieldSelector: containerId is empty. Selector is can\'t be used');
		}
	}

	getContainerId(): string
	{
		return this.containerId;
	}

	setFieldName(fieldName): void
	{
		this.fieldName = Type.isStringFilled(fieldName) ? fieldName : '';
		if (this.fieldName === '')
		{
			this.showError('BX.Iblock.FieldSelector: fieldName is empty. Selector is can\'t be used');
		}
	}

	getFieldName(): string
	{
		return this.fieldName;
	}

	setMultiple(multiple): void
	{
		this.multiple = Type.isBoolean(multiple) ? multiple : false;
	}

	getMultiple(): boolean
	{
		return this.multiple;
	}

	getTagSelectorContainerId(): string
	{
		return this.getContainerId() + '_selector';
	}

	getTagResultContainerId(): string
	{
		return this.getContainerId() + '_results';
	}

	getTagSelectorControlId(): string
	{
		return this.getContainerId() + 'Control';
	}

	setSelectedItems(selectedItems): void
	{
		this.selectedItems = [];
		if (Type.isArray(selectedItems))
		{
			selectedItems.forEach((value): void => {
				if (Type.isInteger(value))
				{
					this.selectedItems.push(value);
				}
			});
		}
		else if (Type.isInteger(selectedItems))
		{
			this.selectedItems.push(selectedItems);
		}
	}

	getSelectedItems(): number[]
	{
		return this.selectedItems;
	}

	getTagSelectorItems(): ItemId[]
	{
		const entityId: string = this.getEntityId();
		const result = [];
		this.getSelectedItems().forEach((value: number): void => {
			const item: ItemId = [
				entityId,
				value,
			];
			result.push(item);
		});

		return result;
	}

	setIblockId(iblockId): void
	{
		this.iblockId = 0;
		if (Type.isInteger(iblockId) && iblockId > 0)
		{
			this.iblockId = iblockId;
		}
	}

	getIblockId(): number
	{
		return this.iblockId;
	}

	setUserType(userType): void
	{
		this.userType = '';
		if (Type.isStringFilled(userType))
		{
			this.userType = userType;
		}
	}

	getUserType(): string
	{
		return this.userType;
	}

	setEntityId(entityId): void
	{
		this.entityId = Type.isStringFilled(entityId) ? entityId : '';
		if (this.entityId === '')
		{
			this.showError('BX.Iblock.FieldSelector: entityI id is empty. Selector is can\'t be used');
		}
	}

	getEntityId(): string
	{
		return this.entityId;
	}

	setSearchMessages(messages): void
	{
		if (Type.isPlainObject(messages))
		{
			this.searchMessages.title = Type.isStringFilled(messages.title) ? messages.title : '';
			this.searchMessages.subtitle = Type.isStringFilled(messages.subtitle) ? messages.subtitle : '';
		}
		else
		{
			this.searchMessages.title = '';
			this.searchMessages.subtitle = '';
		}
	}

	getSearchTabTitle(): string
	{
		return this.searchMessages.title;
	}

	getSearchSubtitle(): string
	{
		return this.searchMessages.subtitle;
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
			this.showError(
				'BX.Iblock.FieldSelector: dom-container ' + containerId + ' is absent. Selector is can\'t be used'
			);
		}

		const tagSelectorContainer = Tag.render`
			<div id="${this.getTagSelectorContainerId()}"></div>
		`;
		Dom.append(tagSelectorContainer, container);

		const tagResult = Tag.render`
			<div id="${this.getTagResultContainerId()}"></div>
		`;
		Dom.append(tagResult, container);

		this.renderSelectedItems(this.getSelectedItems());

		const tagSelectorConfig: TagSelectorOptions = {
			id: this.getTagSelectorControlId(),
			multiple: this.getMultiple(),
			dialogOptions: {
				id: this.getTagSelectorControlId(),
				multiple: this.getMultiple(),
				preselectedItems: this.getTagSelectorItems(),
				entities: [
					{
						id: this.getEntityId(),
						dynamicLoad: true,
						dynamicSearch: true,
						options: {
							iblockId: this.getIblockId(),
							propertyType: this.getUserType(),
						},
					},
				],
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

	renderSelectedItems(items: []): void
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
			items.forEach((value: number): void => {
				const hiddenValue = Tag.render`
					<input type="hidden" name="${fieldName}" value="${value.toString()}">
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
			selectedItems.forEach((item: Item) => {
				parsedValues.push(item.getId());
			});
			this.renderSelectedItems(parsedValues);
		}
	}
}
