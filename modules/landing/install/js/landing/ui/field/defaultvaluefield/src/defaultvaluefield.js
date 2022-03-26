import {Dom, Tag, Text, Type, Runtime} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {DateTimeField} from 'landing.ui.field.datetimefield';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';
import {Draggable} from 'ui.draganddrop.draggable';
import {Loc} from 'landing.loc';
import {ListItem} from 'landing.ui.component.listitem';
import {BaseEvent} from 'main.core.events';
import {FieldsPanel} from 'landing.ui.panel.fieldspanel';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {VariablesField} from 'landing.ui.field.variablesfield';

type CrmField = {
	type: 'list' | 'string' | 'checkbox' | 'date' | 'text' | 'typed_string' | 'file',
	entity_field_name: string,
	entity_name: string,
	name: string,
	caption: string,
	multiple: boolean,
	required: boolean,
	hidden: boolean,
	items: Array<{ID: any, VALUE: any}>,
};

type CrmFieldCategory = {
	CAPTION: string,
	FIELDS: Array<CrmField>
};

type ItemOptions = {
	field: CrmField,
	value: any,
	displayedValue: string,
	displayedLabel: string,
};

export class DefaultValueField extends BaseField
{
	static isListField(field: CrmField): boolean
	{
		return Type.isArray(field.items);
	}

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.DefaultValueField');
		this.subscribeFromOptions(fetchEventsFromOptions(options));

		this.onSelectFieldButtonClick = this.onSelectFieldButtonClick.bind(this);
		this.onItemRemove = this.onItemRemove.bind(this);
		this.onDragEnd = this.onDragEnd.bind(this);
		this.onFormChange = this.onFormChange.bind(this);

		this.items = [];

		this.actionPanel = new ActionPanel({
			renderTo: this.layout,
			left: [
				{
					id: 'selectField',
					text: Loc.getMessage('LANDING_DEFAULT_VALUE_ADD_FIELD'),
					onClick: this.onSelectFieldButtonClick,
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

		this.options.items.forEach((item) => {
			const itemOptions = this.prepareItemOptions({
				id: `${item.entityName}_${item.fieldName}`,
				value: item.value,
			});

			if (itemOptions)
			{
				this.addItem(itemOptions);
			}
		});
	}

	prepareItemOptions(options: {id: string, value: any}): ?ItemOptions
	{
		const crmField = this.getCrmFieldById(options.id);
		if (crmField)
		{
			const displayedValue = (() => {
				if (DefaultValueField.isListField(crmField))
				{
					const fieldItems = this.getFieldItems(crmField);
					const item = fieldItems.find((currentItem) => {
						return currentItem.ID === options.value;
					});

					if (item)
					{
						return item.VALUE;
					}
					
					if (Type.isArrayFilled(fieldItems))
					{
						return fieldItems[0].VALUE;
					}

					return Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_DEFAULT_VALUE');
				}

				if (crmField.type === 'checkbox')
				{
					if (Text.toBoolean(options.value))
					{
						return Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_YES');
					}

					return Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_NO');
				}

				if (Type.isStringFilled(options.value))
				{
					return options.value;
				}

				return Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_DEFAULT_VALUE');
			})();

			const displayedLabel = (() => {
				const fieldCategory = this.getCrmFieldCategoryById(crmField.entity_name);
				return `${crmField.caption} · ${fieldCategory.CAPTION}`;
			})();

			return {
				field: crmField,
				value: options.value,
				displayedValue,
				displayedLabel,
			};
		}

		return null;
	}

	getListContainer(): HTMLDivElement
	{
		return this.cache.remember('listContainer', () => {
			return Tag.render`<div class="landing-ui-field-defaultvalue-list-container"></div>`;
		});
	}

	createInput(): HTMLDivElement
	{
		return this.getListContainer();
	}

	getCrmFieldById(id: string): ?CrmField
	{
		return Object.values(this.options.crmFields)
			.reduce((acc, category) => {
				return [...acc, ...category.FIELDS];
			}, [])
			.find((currentField) => {
				return currentField.name === id;
			});
	}

	getCrmFieldCategoryById(id: string): ?CrmFieldCategory
	{
		return this.options.crmFields[id];
	}

	addItem(options: ItemOptions)
	{
		this.items.push(
			new ListItem({
				id: options.field.name,
				title: options.displayedLabel,
				description: options.displayedValue,
				draggable: true,
				editable: true,
				removable: true,
				appendTo: this.getListContainer(),
				onRemove: this.onItemRemove,
				onFormChange: this.onFormChange,
				form: this.createItemForm(options),
			}),
		);
	}

	getItemById(id: string): ?ListItem
	{
		return this.items.find((currentItem) => {
			return currentItem.options.id === id;
		});
	}

	onItemRemove(event: BaseEvent)
	{
		this.items = this.items.filter((item) => {
			return item !== event.getTarget();
		});

		this.emit('onChange', {skipPrepare: true});
	}

	onFormChange(event: BaseEvent)
	{
		const value = event.getTarget().getValue();
		const item = this.getItemById(value.name);
		const options = this.prepareItemOptions({
			id: value.name,
			value: value.label,
		});

		if (item)
		{
			item.setDescription(options.displayedValue);
		}

		this.emit('onChange', {skipPrepare: true});
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

	getValue()
	{
		return this.items.map((item) => {
			const sourceValue = item.getValue();
			const crmField = this.getCrmFieldById(sourceValue.name);

			return {
				entityName: crmField.entity_name,
				fieldName: crmField.entity_field_name,
				value: sourceValue.value,
			};
		});
	}

	onFieldsSelect(selectedFields: Array<string>)
	{
		selectedFields.forEach((fieldId) => {
			this.addItem(
				this.prepareItemOptions({
					id: fieldId,
				}),
			);
		});

		this.emit('onChange', {skipPrepare: true});
	}

	getAllowedCategories(): Array<string>
	{
		const schemeId = this.options.formOptions.document.scheme;
		const scheme = this.options.dictionary.document.schemes.find((item) => {
			return String(schemeId) === String(item.id);
		});

		if (Type.isPlainObject(scheme))
		{
			return Runtime.clone(scheme.entities);
		}

		return [];
	}

	onSelectFieldButtonClick(event: MouseEvent)
	{
		event.preventDefault();

		FieldsPanel
			.getInstance({isLeadEnabled: this.options.isLeadEnabled})
			.show({
				isLeadEnabled: this.options.isLeadEnabled,
				allowedCategories: this.getAllowedCategories(),
				allowedTypes: [
					'string',
					'list',
					'enumeration',
					'checkbox',
					'boolean',
					'radio',
					'text',
					'integer',
					'double',
					'date',
					'datetime',
					'typed_string',
				],
			})
			.then((selectedFields) => {
				this.options.crmFields = FieldsPanel.getInstance().getOriginalCrmFields();
				this.onFieldsSelect(selectedFields);
			});
	}

	/**
	 * @private
	 */
	getFieldItems(field): ?Array<any>
	{
		if (field.entity_field_name === 'STAGE_ID')
		{
			if (
				Type.isPlainObject(this.options.formOptions.document)
				&& Type.isPlainObject(this.options.formOptions.document.deal)
			)
			{
				const categoryId = Text.toNumber(
					this.options.formOptions.document.deal.category,
				);

				if (categoryId > 0)
				{
					return field.itemsByCategory[categoryId];
				}
			}
		}

		return field.items;
	}

	createItemForm(options = {}): FormSettingsForm
	{
		const form = new FormSettingsForm({
			serializeModifier: (value) => {
				if (
					options.field.type === 'list'
					|| options.field.type === 'checkbox'
					|| options.field.type === 'bool'
				)
				{
					const valueItem = this.getFieldItems(form.fields[0]).find((item) => {
						return item.value === value.value;
					});

					if (valueItem)
					{
						value.label = valueItem.name;
					}
				}
				else
				{
					value.label = value.value;
				}

				return value;
			},
		});

		if (DefaultValueField.isListField(options.field))
		{
			form.addField(
				new BX.Landing.UI.Field.Dropdown({
					selector: 'value',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
					content: options.value,
					items: this.getFieldItems(options.field).map((item) => {
						return {name: item.VALUE, value: item.ID};
					}),
				}),
			);

			return form;
		}

		if (
			options.field.type === 'bool'
			|| options.field.type === 'checkbox'
		)
		{
			form.addField(
				new BX.Landing.UI.Field.Dropdown({
					selector: 'value',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
					content: options.value,
					items: [
						{name: Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_NO'), value: 'N'},
						{name: Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_YES'), value: 'Y'},
					],
				}),
			);

			return form;
		}

		if (
			options.field.type === 'date'
			|| options.field.type === 'datetime'
		)
		{
			form.addField(
				new DateTimeField({
					selector: 'value',
					title: Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
					time: options.field.type === 'datetime',
					content: options.value || '',
				}),
			);

			return form;
		}

		form.addField(
			new VariablesField({
				selector: 'value',
				title: Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
				variables: this.options.personalizationVariables,
				content: options.value || '',
			}),
		);

		return form;
	}
}