import { Dom, Runtime, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Item, TagSelector } from 'ui.entity-selector';

type ItemId = [
	string,
		string | number
];

const SECTION_TYPE = 'iblock_section';
const ELEMENT_TYPE = 'iblock_element';

export class UserFieldSelector
{
	#params: string;
	#fieldName: string;
	#value: Array;
	#isMultiple: string;
	#iblockId: number;
	#type: string;

	#tagSelector: ?TagSelector;

	#hiddenInputsContainer: ?HTMLElement;
	#tagSelectorContainer: ?HTMLElement;

	constructor(params: string)
	{
		this.#params = JSON.parse(params);

		this.#init();
	}

	#init(): void
	{
		const { fieldName, value, isMultiple, iblockId, type } = this.#params;

		if (!Type.isStringFilled(fieldName))
		{
			throw new RangeError('fieldName must be a string');
		}

		if (![SECTION_TYPE, ELEMENT_TYPE].includes(type))
		{
			throw new RangeError(`type must be a ${SECTION_TYPE} or ${ELEMENT_TYPE}`);
		}

		if (!Type.isNumber(iblockId) || iblockId <= 0)
		{
			throw new RangeError('iblockId must be a number and greater than 0');
		}

		this.#fieldName = fieldName;
		this.#value = (Type.isArray(value) ? value : []).filter((item) => !Number.isNaN(item) && item > 0);
		this.#isMultiple = isMultiple === true;
		this.#iblockId = iblockId;
		this.#type = type;
	}

	renderTo(node: HTMLElement): void
	{
		Dom.clean(node);

		this.#appendHiddenInputsContainer(node);
		this.#renderHiddenInputs();

		this.#appendTagSelectorContainer(node);
		this.#renderTagSelector();
	}

	#appendHiddenInputsContainer(node: HTMLElement): void
	{
		const hiddenInputsContainer = this.#getHiddenInputsContainer();
		Dom.append(hiddenInputsContainer, node);
	}

	#getHiddenInputsContainer(): ?HTMLElement
	{
		if (!this.#hiddenInputsContainer)
		{
			this.#hiddenInputsContainer = Tag.render`<span></span>`;
		}

		return this.#hiddenInputsContainer;
	}

	#renderHiddenInputs(): void
	{
		const hiddenInputsContainer = this.#getHiddenInputsContainer();

		Dom.clean(hiddenInputsContainer);

		const fieldName = this.#isMultiple ? `${this.#fieldName}[]` : this.#fieldName;

		const values = Type.isArrayFilled(this.#value) ? this.#value : [null];
		values.forEach((value) => {
			const input = Tag.render`
				<input type="hidden" name="${fieldName}" value="${value === null ? '' : Number(value)}">
			`;
			Dom.append(input, hiddenInputsContainer);
		});
	}

	#appendTagSelectorContainer(node: HTMLElement): void
	{
		const hiddenInputsContainer = this.#getTagSelectorContainer();
		Dom.append(hiddenInputsContainer, node);
	}

	#getTagSelectorContainer(): ?HTMLElement
	{
		if (!this.#tagSelectorContainer)
		{
			this.#tagSelectorContainer = Tag.render`<span></span>`;
		}

		return this.#tagSelectorContainer;
	}

	#renderTagSelector(): void
	{
		const tagSelectorContainer = this.#getTagSelectorContainer();

		this.#getTagSelector().renderTo(tagSelectorContainer);
	}

	#getTagSelector(): TagSelector
	{
		if (!this.#tagSelector)
		{
			this.#tagSelector = new TagSelector({
				multiple: this.#isMultiple,
				dialogOptions: {
					context: 'USER_FIELD',
					preselectedItems: this.#getPreselectedItems(),
					entities: [
						{
							id: this.#getDataProviderEntityId(),
							dynamicLoad: true,
							dynamicSearch: true,
							options: {
								iblockId: this.#iblockId,
							},
						},
					],
					events: {
						'Item:onSelect': () => {
							this.#onChange();
						},
						'Item:onDeselect': () => {
							this.#onChange();
						},
					},
				},
			});
		}

		return this.#tagSelector;
	}

	#onChange(): void
	{
		const selectedItems = this.#getTagSelector().getDialog().getSelectedItems();

		const values = [];
		selectedItems.forEach((item: Item) => {
			values.push(item.getId());
		});
		this.#value = values;

		this.#renderHiddenInputs();

		EventEmitter.emit(this, 'change', { values });
	}

	#getPreselectedItems(): ItemId[]
	{
		const values = Runtime.clone(Type.isArray(this.#value) ? this.#value : [this.#value]);

		return values.map((value) => [this.#getDataProviderEntityId(), Number(value)]);
	}

	#getDataProviderEntityId(): string
	{
		return (this.#type === SECTION_TYPE ? 'iblock-property-section' : 'iblock-property-element');
	}
}
