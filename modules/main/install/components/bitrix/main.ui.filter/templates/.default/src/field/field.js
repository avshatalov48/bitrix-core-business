import {Event, Dom, Cache, Tag, Type} from 'main.core';

const onValueChange = Symbol('onValueChange');

export class Field extends Event.EventEmitter
{
	static instances = new WeakMap();

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Filter.Field');

		this.id = options.options.NAME;
		this.parent = options.parent;
		this.node = options.node;
		this.options = {...options.options};
		this.cache = new Cache.MemoryCache();
		this[onValueChange] = this[onValueChange].bind(this);

		Event.bind(this.node, 'input', this[onValueChange]);
		Event.bind(this.node, 'change', this[onValueChange]);

		const clearButtons = [
			...this.node.querySelectorAll('.main-ui-control-value-delete'),
		];

		clearButtons.forEach((button) => {
			button.addEventListener('click', () => {
				setTimeout(() => {
					this[onValueChange]();
				});
			});
		});

		const MO = new MutationObserver(() => {
			this[onValueChange]();
		});

		const selects = [
			...this.node.querySelectorAll('.main-ui-select'),
		];

		selects.forEach((select) => {
			MO.observe(select, {
				attributes: true,
				attributeFilter: ['data-value'],
			});
		});

		Field.instances.set(this.node, this);
	}

	subscribe(eventName, listener)
	{
		Event.EventEmitter.subscribe(
			this,
			eventName.replace('BX.Filter.Field:', ''),
			listener,
		);
	}

	[onValueChange]()
	{
		this.emit('change', {
			field: this,
			value: this.getValue(),
		});
	}

	/**
	 * @private
	 * @return {HTMLDivElement}
	 */
	getAdditionalFieldContainer(): HTMLDivElement
	{
		return this.cache.remember('additionalFieldsContainer', () => {
			return Tag.render`
				<div class="main-ui-filter-additional-fields-container"></div>
			`;
		});
	}

	/**
	 * @private
	 * @return {boolean}
	 */
	hasAdditional(): boolean
	{
		return Dom.hasClass(this.node, 'main-ui-filter-field-with-additional-fields');
	}

	addAdditionalField(field): Field
	{
		if (!this.hasAdditional())
		{
			Dom.addClass(this.node, 'main-ui-filter-field-with-additional-fields');
			Dom.append(this.getAdditionalFieldContainer(), this.node);
		}

		const preset = this.parent.getPreset();
		const options = this.prepareFieldOptions(field);
		const renderedField = preset.createControl(options);
		this.appendRenderedField(renderedField);

		return Field.instances.get(renderedField);
	}

	// eslint-disable-next-line class-methods-use-this
	prepareListItems(items = {})
	{
		if (Type.isPlainObject(items))
		{
			return Object.entries(items).map(([VALUE, NAME]) => {
				return {NAME, VALUE};
			});
		}

		return {};
	}

	/**
	 * @private
	 * @return {object}
	 */
	prepareFieldOptions(options): {[key: string]: any}
	{
		if (Type.isPlainObject(options))
		{
			const stubs = this.parent.params.FIELDS_STUBS;
			const {type = 'string'} = options;
			const stub = stubs.find((item) => item.NAME === type);

			if (Type.isPlainObject(stub))
			{
				const baseField = {
					...stub,
					NAME: options.id,
					LABEL: options.name,
					TYPE: type === 'checkbox' ? 'SELECT' : stub.TYPE,
					VALUE_REQUIRED: options.valueRequired === true,
				};

				if (type === 'list')
				{
					return {
						...baseField,
						ITEMS: [
							...baseField.ITEMS,
							this.prepareListItems(options.items),
						],
						params: {
							isMulti: (() => {
								if (Type.isPlainObject(options.params))
								{
									return options.params === true;
								}

								return false;
							})(),
						},
					};
				}

				if (type === 'date')
				{
					const subType = (() => {
						if (
							Type.isPlainObject(options.value)
							&& Reflect.has(options.value, '_datesel')
						)
						{
							// eslint-disable-next-line no-underscore-dangle
							return options.value._datesel;
						}

						return this.parent.dateTypes.NONE;
					})();
					return {
						...baseField,
						SUB_TYPES: (() => {
							if (Type.isArray(options.exclude))
							{
								return baseField.SUB_TYPES.filter((item) => {
									return !options.exclude.includes(item.VALUE);
								});
							}

							return baseField.SUB_TYPES;
						})(),
						SUB_TYPE: (() => {
							return baseField.SUB_TYPES.find((item) => {
								return item.VALUE === subType;
							});
						})(),
						VALUES: (() => {
							if (Type.isPlainObject(options.value))
							{
								return {...options.value};
							}

							return baseField.VALUES;
						})(),
					};
				}

				if (
					type === 'string'
					|| type === 'custom_date'
					|| type === 'number'
					|| type === 'checkbox'
					|| type === 'custom_entity'
				)
				{
					return baseField;
				}
			}
		}

		return options;
	}

	/**
	 * @private
	 */
	appendRenderedField(field: HTMLElement)
	{
		if (Type.isDomNode(field))
		{
			const additionalFieldsContainer = this.getAdditionalFieldContainer();
			Dom.append(field, additionalFieldsContainer);
		}
	}

	getValue(): {[key: string]: any} | string | number
	{
		const allValues = this.parent.getFilterFieldsValues();
		const {TYPE, NAME} = this.options;

		if (TYPE === 'DATE' || TYPE === 'NUMBER')
		{
			return Object.entries(allValues).reduce((acc, [key, value]) => {
				if (key.startsWith(NAME))
				{
					acc[key.replace(NAME, '')] = value;
				}

				return acc;
			}, {});
		}

		if (NAME in allValues)
		{
			return allValues[NAME];
		}

		return '';
	}

	setValue(value)
	{
		const {TYPE: type} = this.options;

		if (type === 'DATE' || type === 'NUMBER')
		{
			if (Type.isPlainObject(value))
			{
				const container = this.parent.getFieldListContainer();

				Object.entries(value).forEach(([key, fieldValue]) => {
					const fieldNode = container.querySelector(`[data-name="${this.id}"] [data-name="${this.id}${key}"], [data-name="${this.id}"] [name="${this.id}${key}"]`);

					if (fieldNode)
					{
						if (Dom.hasClass(fieldNode, 'main-ui-select'))
						{
							const items = Dom.attr(fieldNode, 'data-items');

							if (Type.isArray(items))
							{
								const item = items.find((currentItem) => currentItem.VALUE === fieldValue);

								if (Type.isPlainObject(item))
								{
									Dom.attr(fieldNode, 'data-value', item);

									const nameNode = fieldNode.querySelector('.main-ui-select-name');
									if (nameNode)
									{
										nameNode.innerText = item.NAME;
									}

									let result = BX.Main.ui.Factory.get(fieldNode);

									if (!result)
									{
										result = {node: fieldNode, instance: new BX.Main.ui.select(fieldNode)};
										BX.Main.ui.Factory.data.push(result);
									}

									if (Type.isPlainObject(result))
									{
										BX.onCustomEvent(window, 'UI::Select::Change', [result.instance, item]);
									}
								}
							}
						}
						else if (fieldNode.tagName === 'INPUT')
						{
							fieldNode.value = fieldValue;
						}
					}
				});
			}
		}
	}
}