import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {Dom, Runtime, Tag, Type} from 'main.core';
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
import {ResourcebookingUserfield} from 'calendar.resourcebookinguserfield';
import {PageObject} from 'landing.pageobject';
import type {ListItemOptions} from 'landing.ui.component.listitem';
import 'socnetlogdest';

import './css/style.css';

export class FieldsListField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.FieldsListField');
		this.setLayoutClass('landing-ui-field-fields-list');

		this.onSelectFieldButtonClick = this.onSelectFieldButtonClick.bind(this);
		this.onSelectProductButtonClick = this.onSelectProductButtonClick.bind(this);
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
					text: Loc.getMessage('LANDING_FIELDS_SELECT_FIELD_BUTTON_TITLE'),
					onClick: this.onSelectFieldButtonClick,
				},
			],
			right: [
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
				listItemOptions.description = options.label || crmField.caption;
				listItemOptions.editable = true;
				listItemOptions.isSeparator = false;
				listItemOptions.fieldController = this.createResourceBookingFieldController(options);

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

	createFieldSettingsForm(field)
	{
		const fields = [];

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

		if (field.type === 'list' && field.editing.items.length > 0)
		{
			const defaultValueField = new BX.Landing.UI.Field.Dropdown({
				selector: 'value',
				title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_LIST_DEFAULT_VALUE_TITLE'),
				items: [
					{
						value: Loc.getMessage('LANDING_FORM_DEFAULT_VALUE_NOT_SELECTED'),
						id: null,
					},
					...field.editing.items,
				].map((item) => {
					return {
						name: item.value,
						value: item.id,
					};
				}),
			});

			fields.push(
				new ListSettingsField({
					selector: 'items',
					title: Loc.getMessage('LANDING_FIELDS_ITEM_FORM_LIST_SETTINGS_TITLE'),
					items: field.editing.items.map((item) => {
						return {
							name: item.value,
							value: item.id,
							checked: true,
						};
					}),
				}),
			);

			fields.push(defaultValueField);
		}

		return new FormSettingsForm({
			fields,
			serializeModifier(value) {
				const modifiedValue = {...value};
				if (Reflect.has(value, 'required'))
				{
					modifiedValue.required = value.required.includes('required');
				}

				if (Reflect.has(value, 'multiple'))
				{
					modifiedValue.multiple = value.multiple.includes('multiple');
				}

				if (Reflect.has(value, 'value') && Type.isArrayFilled(value.items))
				{
					modifiedValue.items = modifiedValue.items.map((item) => {
						item.selected = (value.value === item.value);
						return item;
					});
				}

				return modifiedValue;
			},
		});
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
				this.onFieldsSelect(selectedFields);
			});
	}

	onFieldsSelect(selectedFields: Array<string>)
	{
		const preparingOptions = {
			fields: selectedFields.map((fieldId) => {
				return {name: fieldId};
			}),
		};

		FormClient.getInstance()
			.prepareOptions(this.options.formOptions, preparingOptions)
			.then((result) => {
				const promises = result.data.fields.map((field) => {
					return this.addItem(field);
				});

				Promise.all(promises)
					.then(() => {
						this.emit('onChange', {skipPrepare: true});
					});
			});
	}

	getValue()
	{
		return this.items.map((item) => {
			return item.getValue();
		});
	}

	onSelectProductButtonClick(event: MouseEvent)
	{
		event.preventDefault();
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

				FormClient.getInstance()
					.prepareOptions(this.options.formOptions, {fields})
					.then((result) => {
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
}