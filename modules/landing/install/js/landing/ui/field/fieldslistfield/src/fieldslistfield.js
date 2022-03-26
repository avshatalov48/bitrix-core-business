import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {Dom, Runtime, Tag, Text, Type} from 'main.core';
import {Draggable} from 'ui.draganddrop.draggable';
import {FieldsPanel} from 'landing.ui.panel.fieldspanel';
import {ListItem} from 'landing.ui.component.listitem';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {TextField} from 'landing.ui.field.textfield';
import {BaseEvent} from 'main.core.events';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {FormClient} from 'crm.form.client';
import {ListSettingsField} from 'landing.ui.field.listsettingsfield';
import {SeparatorPanel} from 'landing.ui.panel.separatorpanel';
import {PageObject} from 'landing.pageobject';
import {Loader} from 'main.loader';
import type {ListItemOptions} from 'landing.ui.component.listitem';
import {ProductField} from 'landing.ui.field.productfield';
import 'calendar.resourcebookinguserfield';
import 'socnetlogdest';
import 'ui.hint';

import './css/style.css';
import {IconButton} from 'landing.ui.component.iconbutton';

export class FieldsListField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.FieldsListField');
		this.setLayoutClass('landing-ui-field-fields-list');

		this.onSelectFieldButtonClick = this.onSelectFieldButtonClick.bind(this);
		this.onSelectProductsButtonClick = this.onSelectProductsButtonClick.bind(this);
		this.onSelectSeparatorButtonClick = this.onSelectSeparatorButtonClick.bind(this);
		this.onItemRemove = this.onItemRemove.bind(this);
		this.onItemEdit = this.onItemEdit.bind(this);
		this.onDragEnd = this.onDragEnd.bind(this);
		this.onFormChange = this.onFormChange.bind(this);

		this.items = [];

		this.options.items.forEach((itemOptions) => {
			this.addItem(itemOptions);
		});

		this.actionPanel = new ActionPanel({
			renderTo: this.layout,
			left: [
				{
					id: 'selectField',
					text: Loc.getMessage('LANDING_FIELDS_ADD_FIELD_BUTTON_TITLE'),
					onClick: this.onSelectFieldButtonClick,
				},
			],
			right: [
				{
					id: 'addProducts',
					text: Loc.getMessage('LANDING_FIELDS_SELECT_PRODUCTS_BUTTON_TITLE'),
					onClick: this.onSelectProductsButtonClick,
				},
				{
					id: 'selectSeparator',
					text: Loc.getMessage('LANDING_FIELDS_SELECT_SEPARATOR_BUTTON_TITLE'),
					onClick: this.onSelectSeparatorButtonClick,
				},
			],
		});

		this.draggable = new Draggable({
			context: window.parent,
			container: this.getListContainer(),
			draggable: '.landing-ui-component-list-item',
			dragElement: '.landing-ui-button-icon-drag',
			type: Draggable.MOVE,
			offset: {
				y: -62,
			},
		});

		this.draggable.subscribe('end', this.onDragEnd);
	}

	createInput(): HTMLDivElement
	{
		return this.getListContainer();
	}

	getCrmFieldById(id: string)
	{
		return Object.values(this.options.crmFields)
			.reduce((acc, category) => {
				return [...acc, ...category.FIELDS];
			}, [])
			.find((currentField) => {
				return currentField.name === id;
			});
	}

	getCrmFieldCategoryById(id: string)
	{
		return this.options.crmFields[id];
	}

	addItem(itemOptions)
	{
		return this.createItem(itemOptions)
			.then((item) => {
				this.items.push(item);
				Dom.append(item.getLayout(), this.getListContainer());
			});
	}

	prependItem(itemOptions)
	{
		return this.createItem(itemOptions)
			.then((item) => {
				this.items.unshift(item);
				Dom.prepend(item.getLayout(), this.getListContainer());
			});
	}

	insertItemAfterIndex(itemOptions, index)
	{
		return this.createItem(itemOptions)
			.then((item) => {
				this.items.splice((index + 1), 0, item);
				Dom.insertAfter(item.getLayout(), this.getListContainer().childNodes[index]);
			});
	}

	static isSeparator(fieldId: ?string): boolean
	{
		if (Type.isStringFilled(fieldId))
		{
			return (
				fieldId.startsWith('hr')
				|| fieldId.startsWith('section')
				|| fieldId.startsWith('page')
			);
		}

		return false;
	}

	static getSeparatorTitle(fieldId: ?string): string
	{
		if (Type.isStringFilled(fieldId))
		{
			if (fieldId.startsWith('hr'))
			{
				return Loc.getMessage('LANDING_SEPARATOR_SOLID_LINE');
			}

			if (fieldId.startsWith('section'))
			{
				return Loc.getMessage('LANDING_SEPARATOR_HEADER');
			}

			if (fieldId.startsWith('page'))
			{
				return Loc.getMessage('LANDING_SEPARATOR_PAGE');
			}
		}

		return Loc.getMessage('LANDING_FIELDS_LIST_FIELD_SEPARATOR_TITLE');
	}

	isFieldAvailable(fieldId: ?string): boolean
	{
		if (Type.isStringFilled(fieldId))
		{
			if (fieldId.startsWith('product_'))
			{
				return true;
			}

			return Type.isPlainObject(
				this.getCrmFieldById(fieldId),
			);
		}

		return false;
	}

	getFieldItemTitle(fieldId: ?string): string
	{
		if (this.isFieldAvailable(fieldId))
		{
			if (fieldId.startsWith('product_'))
			{
				return Loc.getMessage('LANDING_FIELDS_LIST_FIELD_PRODUCTS_TITLE');
			}

			const crmField = this.getCrmFieldById(fieldId);
			const crmFieldCategory = this.getCrmFieldCategoryById(crmField.entity_name);

			return `${crmField.caption} · ${crmFieldCategory.CAPTION}`;
		}

		return '';
	}

	createResourceBookingFieldController(options: {[key: string]: any})
	{
		if (options.type === 'resourcebooking')
		{
			const root = PageObject.getRootWindow();
			const crmField = this.getCrmFieldById(options.id);
			return root.BX.Calendar.ResourcebookingUserfield.initCrmFormFieldController({
				field: {
					...options,
					dict: crmField,
					node: Tag.render`<div><div class="crm-webform-resourcebooking-wrap"></div></div>`,
				},
			});
		}

		return null;
	}

	createItem(
		options: {
			id: string,
			label: string,
			description: string,
			content?: any,
			type?: any,
			separatorTitle?: any,
		},
	): Promise<ListItem>
	{
		const listItemOptions: ListItemOptions = {
			id: options.id,
			type: options.type ? options.type : '',
			content: options.content,
			sourceOptions: {...options},
			draggable: true,
			removable: true,
			onRemove: this.onItemRemove,
			onEdit: this.onItemEdit,
			onFormChange: this.onFormChange,
			form: this.createFieldSettingsForm(options),
		};

		if (!FieldsListField.isSeparator(options.id))
		{
			if (this.isFieldAvailable(options.id))
			{
				listItemOptions.title = this.getFieldItemTitle(options.id);

				const crmField = this.getCrmFieldById(options.id);
				listItemOptions.description = options.label || (crmField ? crmField.caption : '');
				listItemOptions.editable = true;
				listItemOptions.isSeparator = false;
				listItemOptions.fieldController = this.createResourceBookingFieldController(options);


				if (options.editing.supportAutocomplete)
				{
					const autocompleteButton = new IconButton({
						id: 'autocomplete',
						type: (() => {
							if (options.autocomplete)
							{
								return IconButton.Types.user1Active;
							}

							return IconButton.Types.user1;
						})(),
						style: {
							opacity: 1,
							cursor: 'default',
						},
						title: (() => {
							if (options.autocomplete)
							{
								return Loc.getMessage('LANDING_FIELDS_ITEM_AUTOCOMPLETE_ENABLED');
							}

							return Loc.getMessage('LANDING_FIELDS_ITEM_AUTOCOMPLETE_DISABLED');
						})(),
					});

					listItemOptions.form.subscribe('onChange', (event: BaseEvent) => {
						if (event.getTarget().serialize().autocomplete)
						{
							autocompleteButton.setType(IconButton.Types.user1Active);
						}
						else
						{
							autocompleteButton.setType(IconButton.Types.user1);
						}
					});

					listItemOptions.actions = [
						autocompleteButton,
					];
				}

				const listItem = new ListItem(listItemOptions);

				if (listItemOptions.fieldController)
				{
					return new Promise((resolve) => {
						if (Type.isFunction(listItemOptions.fieldController.subscribe))
						{
							listItemOptions.fieldController.subscribe('afterInit', (event) => {
								options.booking.settings_data = event.getData().settings.data;
								resolve(listItem);
							});
						}
						else
						{
							resolve(listItem);
						}
					});
				}

				return Promise.resolve(listItem);
			}

			listItemOptions.editable = false;
			listItemOptions.isSeparator = false;
			listItemOptions.title = '';
			listItemOptions.description = Loc.getMessage('LANDING_FIELDS_ITEM_FIELD_UNAVAILABLE');
			listItemOptions.error = true;

			const listItem = new ListItem(listItemOptions);

			return Promise.resolve(listItem);
		}

		listItemOptions.isSeparator = true;
		listItemOptions.editable = !String(options.id).startsWith('hr_');
		listItemOptions.title = FieldsListField.getSeparatorTitle(options.id);

		if (Type.isString(options.label))
		{
			listItemOptions.description = options.label;
		}
		else if (String(options.id).startsWith('hr_'))
		{
			listItemOptions.description = FieldsListField.getSeparatorTitle(options.id);
		}
		else
		{
			const crmField = this.getCrmFieldById(options.id);
			if (Type.isPlainObject(crmField) && Type.isString(crmField.caption))
			{
				listItemOptions.description = crmField.caption;
			}
			else
			{
				listItemOptions.description = '';
			}
		}

		const listItem = new ListItem(listItemOptions);

		return Promise.resolve(listItem);
	}

	createCustomPriceDropdown(field)
	{
		return new BX.Landing.UI.Field.Dropdown({
			id: 'customPrice',
			selector: 'customPrice',
			items: [
				{name: Loc.getMessage('LANDING_FIELDS_LIST_FIELD_PRODUCTS_ALLOW_CUSTOM_PRICE_NOT_SELECTED'), value: null},
				...(field.items.map((item) => {
					return {name: item.label, value: item.value};
				})),
			],
			content: field.items.reduce((acc, item) => {
				if (item.changeablePrice && acc === null)
				{
					return item.value;
				}

				return acc;
			}, null),
		});
	}

	createProductDefaultValueDropdown(field)
	{
		const defaultValueField = new BX.Landing.UI.Field.Dropdown({
			id: 'productDefaultValue',
			selector: 'value',
			title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_LIST_DEFAULT_VALUE_TITLE'),
			content: field.value,
			items: [
				{
					label: Loc.getMessage('LANDING_FORM_DEFAULT_VALUE_NOT_SELECTED'),
					value: null,
				},
				...field.items,
			].map((item) => {
				return {
					name: item.label,
					value: item.value,
				};
			}),
		});

		if (field.items.length > 0)
		{
			defaultValueField.enable();
		}
		else
		{
			defaultValueField.disable();
		}

		return defaultValueField;
	}

	createDefaultValueField(field): BX.Landing.UI.Field.Dropdown
	{
		return new BX.Landing.UI.Field.Dropdown({
			selector: 'value',
			title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_LIST_DEFAULT_VALUE_TITLE'),
			content: field.value,
			items: [
				{
					label: Loc.getMessage('LANDING_FORM_DEFAULT_VALUE_NOT_SELECTED'),
					value: null,
				},
				...field.items,
			].map((item) => {
				return {
					name: item.label,
					value: item.value,
				};
			}),
		});
	}

	// eslint-disable-next-line class-methods-use-this
	createFieldSettingsForm(field)
	{
		const fields = [];
		const form = new FormSettingsForm({
			serializeModifier(value) {
				const modifiedValue = {...value};
				if (Reflect.has(value, 'label'))
				{
					modifiedValue.label = Text.decode(value.label);
				}

				if (Reflect.has(value, 'required'))
				{
					modifiedValue.required = value.required.includes('required');
				}

				if (Reflect.has(value, 'multiple'))
				{
					modifiedValue.multiple = value.multiple.includes('multiple');
				}

				if (Reflect.has(value, 'bigPic'))
				{
					modifiedValue.bigPic = value.bigPic.includes('bigPic');
				}

				if (Reflect.has(value, 'value') && Type.isArrayFilled(value.items))
				{
					modifiedValue.items = modifiedValue.items.map((item) => {
						item.selected = (value.value === item.value);
						return item;
					});
				}

				if (Reflect.has(value, 'products'))
				{
					modifiedValue.items = Runtime.clone(value.products);
					if (!Type.isPlainObject(modifiedValue.editing))
					{
						modifiedValue.editing = {};
					}

					if (Reflect.has(value, 'value') && Type.isArrayFilled(modifiedValue.items))
					{
						modifiedValue.items.forEach((item) => {
							item.selected = (String(value.value) === String(item.value));
						});
					}

					modifiedValue.editing.catalog = Runtime.clone(value.products);
				}

				if (Reflect.has(value, 'valueType'))
				{
					if (!Type.isPlainObject(modifiedValue.editing))
					{
						modifiedValue.editing = {};
					}

					if (!Type.isPlainObject(modifiedValue.editing.editable))
					{
						modifiedValue.editing.editable = {};
					}

					modifiedValue.editing.editable.valueType = value.valueType;
				}

				if (Type.isArray(value.useCustomPrice))
				{
					modifiedValue.items.forEach((item) => {
						item.changeablePrice = (
							value.useCustomPrice.includes('useCustomPrice')
							&& String(item.value) === String(value.customPrice)
						);
					});

					delete modifiedValue.customPrice;
					delete modifiedValue.useCustomPrice;
				}

				if (Type.isArray(value.autocomplete))
				{
					modifiedValue.autocomplete = value.autocomplete.length > 0;
				}

				if (Type.isArrayFilled(value.contentTypes))
				{
					if (value.contentTypes.includes('any'))
					{
						modifiedValue.contentTypes = [];
					}
				}

				return modifiedValue;
			},
		});

		if (field.type === 'product')
		{
			fields.push(
				new ProductField({
					title: Loc.getMessage('LANDING_FIELDS_LIST_FIELD_PRODUCTS_TITLE2'),
					selector: 'products',
					items: field.editing.catalog || [],
					iblockId: this.options.dictionary.catalog.id,
					onChange: () => {
						const oldCustomPrice = form.fields.get('customPrice');
						const newCustomPrice = this.createCustomPriceDropdown({
							...field,
							items: form.serialize().items,
						});

						const useCustomPrice = field.items.some((item) => {
							return item.changeablePrice;
						});

						const useCustomPriceField = form.fields.get('useCustomPrice');

						if (useCustomPrice || useCustomPriceField.getValue().includes('useCustomPrice'))
						{
							Dom.style(newCustomPrice.getLayout(), 'display', null);
						}
						else
						{
							Dom.style(newCustomPrice.getLayout(), 'display', 'none');
						}

						newCustomPrice.setValue(oldCustomPrice.getValue());

						form.replaceField(
							oldCustomPrice,
							newCustomPrice,
						);

						const oldDefaultValue = form.fields.get('productDefaultValue');
						const newDefaultValue = this.createProductDefaultValueDropdown({
							...field,
							items: form.serialize().items,
						});
						form.replaceField(
							oldDefaultValue,
							newDefaultValue,
						);
					},
				}),
			);
		}

		if (field.editing.hasLabel)
		{
			fields.push(
				new TextField({
					selector: 'label',
					title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_LABEL_FIELD_TITLE'),
					content: field.label,
					textOnly: true,
				}),
			);
		}

		if (field.editing.canBeRequired)
		{
			fields.push(
				new BX.Landing.UI.Field.Checkbox({
					selector: 'required',
					compact: true,
					items: [
						{
							name: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_REQUIRED_FIELD_TITLE'),
							value: 'required',
						},
					],
					value: field.required ? ['required'] : [],
				}),
			);
		}

		if (field.editing.canBeMultiple)
		{
			fields.push(
				new BX.Landing.UI.Field.Checkbox({
					selector: 'multiple',
					compact: true,
					items: [
						{
							name: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_MULTIPLE_FIELD_TITLE'),
							value: 'multiple',
						},
					],
					value: field.multiple ? ['multiple'] : [],
				}),
			);
		}

		if (field.editing.hasStringDefaultValue)
		{
			fields.push(
				new TextField({
					selector: 'value',
					title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_DEFAULT_VALUE_FIELD_TITLE'),
					content: field.value,
					textOnly: true,
				}),
			);
		}

		if (field.type === 'product')
		{
			fields.push(
				new BX.Landing.UI.Field.Checkbox({
					selector: 'bigPic',
					compact: true,
					items: [
						{
							name: Loc.getMessage('LANDING_FIELDS_LIST_FIELD_PRODUCTS_SHOW_BIG_PICTURE'),
							value: 'bigPic',
						},
					],
					value: field.bigPic ? ['bigPic'] : [],
				}),
			);

			const useCustomPrice = field.items.some((item) => {
				return item.changeablePrice;
			});

			const customPriceField = this.createCustomPriceDropdown(field);
			if (useCustomPrice)
			{
				Dom.style(customPriceField.getLayout(), 'display', null);
			}
			else
			{
				Dom.style(customPriceField.getLayout(), 'display', 'none');
			}

			fields.push(
				new BX.Landing.UI.Field.Checkbox({
					id: 'useCustomPrice',
					selector: 'useCustomPrice',
					compact: true,
					items: [
						{
							name: Loc.getMessage('LANDING_FIELDS_LIST_FIELD_PRODUCTS_ALLOW_CUSTOM_PRICE'),
							value: 'useCustomPrice',
						},
					],
					value: useCustomPrice ? ['useCustomPrice'] : [],
					onChange: (checkbox) => {
						if (checkbox instanceof BaseField)
						{
							const customPriceField = form.fields.get('customPrice');
							if (checkbox.getValue().includes('useCustomPrice'))
							{
								Dom.style(customPriceField.getLayout(), 'display', null);
							}
							else
							{
								Dom.style(customPriceField.getLayout(), 'display', 'none');
							}
						}
					},
				}),
			);

			fields.push(customPriceField);

			fields.push(this.createProductDefaultValueDropdown(field));
		}

		if (['list', 'radio'].includes(field.type) && field.editing.items.length > 0)
		{
			const defaultValueField = this.createDefaultValueField(field);
			const listSettingsField = new ListSettingsField({
				selector: 'items',
				title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_LIST_SETTINGS_TITLE'),
				items: (() => {
					return field.editing.items.map((item) => {
						const selectedItem = field.items.find((currentItem) => {
							return String(currentItem.value) === String(item.id);
						});
						const checked = !!selectedItem;

						return {
							name: checked ? selectedItem.label : item.value,
							value: item.id,
							checked,
						};
					});
				})(),
			});

			listSettingsField.subscribe('onChange', () => {
				const currentDefaultValueField = form.fields.find((item) => {
					return item.selector === 'value';
				});
				form.replaceField(
					currentDefaultValueField,
					this.createDefaultValueField({
						...field,
						items: form.serialize().items,
						value: currentDefaultValueField.getValue(),
					}),
				);
			});

			fields.push(listSettingsField);
			fields.push(defaultValueField);
		}

		if (
			Type.isPlainObject(field.editing)
			&& Type.isArrayFilled(field.editing.valueTypes)
		)
		{
			fields.push(
				new BX.Landing.UI.Field.Dropdown({
					selector: 'valueType',
					title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_VALUE_TYPE'),
					content: field.editing.editable.valueType,
					items: field.editing.valueTypes.map((item) => {
						return {name: item.name, value: item.id};
					}),
				}),
			);
		}

		if (
			field.type === 'file'
			&& Type.isArrayFilled(this.options.dictionary.contentTypes)
		)
		{
			const adjustContentTypesField = (value) => {
				if (value.includes('any'))
				{
					const inputs = [...contentTypesField.layout
						.querySelectorAll('.landing-ui-field-checkbox-item-checkbox')];
					inputs.forEach((input) => {
						if (Dom.attr(input, 'value') === 'any')
						{
							Dom.removeClass(input.closest('.landing-ui-field-checkbox-item'), 'landing-ui-disabled');
						}
						else
						{
							Dom.addClass(input.closest('.landing-ui-field-checkbox-item'), 'landing-ui-disabled');
						}
					});
				}
				else
				{
					const inputs = [...contentTypesField.layout
						.querySelectorAll('.landing-ui-field-checkbox-item-checkbox')];
					inputs.forEach((input) => {
						Dom.removeClass(input.closest('.landing-ui-field-checkbox-item'), 'landing-ui-disabled');
					});
				}
			};

			const selectedContentTypes = Type.isArrayFilled(field.contentTypes) ? field.contentTypes : ['any'];
			let lastValue = selectedContentTypes;
			const contentTypesField = new BX.Landing.UI.Field.Checkbox({
				selector: 'contentTypes',
				title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_ALLOWED_FILE_TYPE'),
				value: selectedContentTypes,
				items: [
					(() => {
						if (Loc.hasMessage('LANDING_FIELDS_ITEM_FORM_ALLOWED_ANY_FILE_TYPE'))
						{
							return {
								name: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_ALLOWED_ANY_FILE_TYPE'),
								value: 'any',
							};
						}

						return undefined;
					})(),
					...this.options.dictionary.contentTypes.map((item) => {
						const hint = item.hint
							? `<span class="ui-hint" data-hint="${Text.encode(item.hint)}"></span>`
							: ''
						;
						return {
							html: `<span style="display: flex; align-items: center;">${Text.encode(item.name)} ${hint}</span>`,
							name: '',
							value: item.id
						};
					}),
				],
				onValueChange: () => {
					const value = contentTypesField.getValue();

					if (value.includes('any'))
					{
						if (lastValue.includes('any'))
						{
							contentTypesField.setValue(value.filter((item) => item !== 'any'));
						}
						else
						{
							contentTypesField.setValue(['any']);
						}
					}

					lastValue = contentTypesField.getValue();
				},
			});

			BX.UI.Hint.init(contentTypesField.getLayout());
			fields.push(contentTypesField);
		}

		if (Text.toBoolean(field.editing.supportAutocomplete) === true)
		{
			fields.push(new BX.Landing.UI.Field.Checkbox({
				selector: 'autocomplete',
				compact: true,
				multiple: false,
				items: [
					{
						name: Loc.getMessage('LANDING_FIELDS_ITEM_ENABLE_AUTOCOMPLETE'),
						html: Text.encode(Loc.getMessage('LANDING_FIELDS_ITEM_ENABLE_AUTOCOMPLETE'))
							+ `<span 
									class="landing-ui-form-help" 
									style="margin: 0 0 0 5px;"
									onclick="top.BX.Helper.show('redirect=detail&code=14611764'); return false;"
								><a href="javascript: void();"></a></span>`
						,
						value: 'autocomplete',
					},
				],
				value: field.autocomplete ? ['autocomplete'] : false,
			}));
		}

		if (Text.toBoolean(field.editing.hasHint) === true)
		{
			fields.push(
				new TextField({
					selector: 'hint',
					title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_FIELD_HINT_TITLE'),
					content: field.hint,
					textOnly: true,
				}),
			);
		}

		if (Text.toBoolean(field.editing.supportHintOnFocus) === true)
		{
			fields.push(
				new BX.Landing.UI.Field.Checkbox({
					selector: 'hintOnFocus',
					compact: true,
					multiple: false,
					items: [
						{
							name: Loc.getMessage('LANDING_FIELDS_ITEM_ENABLE_HINT_ON_FOCUS'),
							value: 'hintOnFocus',
						},
					],
					value: field.hintOnFocus ? ['hintOnFocus'] : false,
				}),
			);
		}

		fields.forEach((currentField) => {
			form.addField(currentField);
		});

		return form;
	}

	getListContainer(): HTMLDivElement
	{
		return this.cache.remember('listContainer', () => {
			return Tag.render`<div class="landing-ui-field-fields-list-container"></div>`;
		});
	}

	onSelectFieldButtonClick(event: MouseEvent)
	{
		event.preventDefault();

		FieldsPanel
			.getInstance({
				isLeadEnabled: this.options.isLeadEnabled,
			})
			.show({
				disabledFields: this.items.map((item) => item.options.id),
			})
			.then((selectedFields) => {
				if (Type.isArrayFilled(selectedFields))
				{
					this.options.crmFields = FieldsPanel.getInstance().getOriginalCrmFields();
					this.onFieldsSelect(selectedFields);
				}
			});
	}

	onFieldsSelect(selectedFields: Array<string>)
	{
		const preparingOptions = {
			fields: selectedFields.map((fieldId) => {
				return {name: fieldId};
			}),
		};

		void this.showLoader();

		FormClient.getInstance()
			.prepareOptions(this.options.formOptions, preparingOptions)
			.then((result) => {
				void this.hideLoader();
				return Promise.all(
					result.data.fields.map((field) => {
						return this.addItem(field);
					}),
				);
			})
			.then(() => {
				this.emit('onChange', {skipPrepare: true});
			});
	}

	getValue()
	{
		return this.items.map((item) => {
			return item.getValue();
		});
	}

	// eslint-disable-next-line class-methods-use-this
	onSelectProductsButtonClick(event: MouseEvent)
	{
		event.preventDefault();

		const preparingOptions = {
			fields: [
				{type: 'product'},
			],
		};

		void this.showLoader();

		FormClient
			.getInstance()
			.prepareOptions(this.options.formOptions, preparingOptions)
			.then((result) => {
				void this.hideLoader();

				const promises = result.data.fields.map((field) => {
					return this.addItem(field);
				});

				Promise.all(promises)
					.then(() => {
						this.emit('onChange', {skipPrepare: true});
					});
			});
	}

	onSelectSeparatorButtonClick(event: MouseEvent)
	{
		event.preventDefault();
		SeparatorPanel.getInstance()
			.show()
			.then((separator) => {
				const fields = [separator];

				if (
					separator.type === 'page'
					&& !this.items.find((item) => item.options.type === 'page')
				)
				{
					fields.push({...fields[0]});
				}

				void this.showLoader();

				FormClient.getInstance()
					.prepareOptions(this.options.formOptions, {fields})
					.then((result) => {
						void this.hideLoader();

						let separatorPromise = Promise.resolve();
						if (
							separator.type === 'page'
							&& !this.items.find((item) => item.options.type === 'page')
						)
						{
							result.data.fields[0].label = Loc.getMessage('LANDING_FIELDS_ITEM_PAGE_TITLE')
								.replace('#number#', 1);
							result.data.fields[1].label = Loc.getMessage('LANDING_FIELDS_ITEM_PAGE_TITLE')
								.replace('#number#', 2);

							separatorPromise = Promise.all([
								this.prependItem(result.data.fields[0]),
								this.insertItemAfterIndex(result.data.fields[1], 1),
							]);
						}
						else
						{
							result.data.fields.forEach((field) => {
								const [type] = field.id.split('_');
								const count = this.items.filter((item) => {
									return item.options.id.startsWith(type);
								}).length;

								if (type === 'page')
								{
									field.label = Loc.getMessage('LANDING_FIELDS_ITEM_PAGE_TITLE')
										.replace('#number#', count + 1);
								}

								if (type === 'section')
								{
									field.label = Loc.getMessage('LANDING_FIELDS_ITEM_SECTION_TITLE')
										.replace('#number#', count + 1);
								}

								if (type === 'hr')
								{
									field.label = Loc.getMessage('LANDING_FIELDS_ITEM_LINE_TITLE')
										.replace('#number#', count + 1);
								}

								separatorPromise = this.addItem(field);
							});
						}

						separatorPromise.then(() => {
							this.emit('onChange', {skipPrepare: true});
						});
					});
			});
	}

	onItemRemove(event: BaseEvent)
	{
		this.items = this.items.filter((item) => {
			return item !== event.getTarget();
		});

		this.emit('onChange', {skipPrepare: true});
	}

	onItemEdit(event: BaseEvent)
	{
		const {options} = event.getTarget();
		if (options.fieldController)
		{
			event.preventDefault();
			options.fieldController.showSettingsPopup();
			setTimeout(() => {
				options.fieldController.settingsPopup.subscribeOnce('onClose', () => {
					options.sourceOptions.booking.settings_data = options.fieldController.getSettings().data;

					// eslint-disable-next-line camelcase
					const {settings_data} = options.sourceOptions.booking;
					Object.keys(settings_data).forEach((key) => {
						if (Type.isArray(settings_data[key].value))
						{
							settings_data[key].value = settings_data[key].value.join('|');
						}
					});
					this.emit('onChange', {skipPrepare: true});
				});
			}, 1000);
		}
	}

	onFormChange(event: BaseEvent)
	{
		this.emit('onChange', {skipPrepare: true});

		const target = event.getTarget();
		const value = target.getValue();

		target.setDescription(value.label);
	}

	onDragEnd()
	{
		setTimeout(() => {
			this.items = [...this.getListContainer().children].map((itemNode) => {
				const itemNodeId = Dom.attr(itemNode, 'data-id');
				return this.items.find((item) => {
					return item.options.id === itemNodeId;
				});
			});

			this.emit('onChange', {skipPrepare: true});
		});
	}

	getLoader(): Loader
	{
		return this.cache.remember('loader', () => {
			return new Loader({
				size: 50,
				mode: 'inline',
				offset: {
					top: '5px',
					left: '225px',
				},
			});
		});
	}

	showLoader(): Promise<any>
	{
		const loader = this.getLoader();
		const container = this.getListContainer();
		Dom.append(loader.layout, container);
		return loader.show(container);
	}

	hideLoader(): Promise<any>
	{
		const loader = this.getLoader();
		Dom.remove(loader.layout);
		return loader.hide();
	}
}