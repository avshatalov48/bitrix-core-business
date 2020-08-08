import {ajax} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events'

const PROPERTY_PREFIX = 'PROPERTY_';
const PROPERTY_BLOCK_NAME = 'properties';

export default class IblockSectionController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);

		this.isRequesting = false;
		this.clearServiceFields();

		EventEmitter.subscribe(this._editor, 'IblockSectionField:onChange', this.onChangeHandler.bind(this));
		EventEmitter.subscribe(this._editor, 'BX.UI.EntityEditor:onFieldCreate', this.onFieldAdd.bind(this));
		EventEmitter.subscribe(this._editor, 'BX.UI.EntityEditor:onFieldModify', this.onFieldUpdate.bind(this));
	}

	clearServiceFields()
	{
		this.lastDataHash = null;
		this.initialElements = null;
		this.deletedControls = {};
		this.deletedAvailableSchemes = {};
	}

	onChangeHandler(event: BaseEvent)
	{
		const [field] = event.getData();
		const newData = field.tileSelector.list.map(tile => tile.id);
		const newDataHash = JSON.stringify(newData);

		if (this.lastDataHash === null || this.lastDataHash !== newDataHash)
		{
			this.lastDataHash = newDataHash;

			clearTimeout(this.timeout);
			this.timeout = setTimeout(() => {
				this.refreshLinkedProperties(newData)
			}, 50);
		}
	}

	onFieldAdd(event: BaseEvent)
	{
		const [section, eventArgs] = event.getCompatData();
		const fields = this.getFieldsForm(eventArgs);
		ajax.runComponentAction(
			this._editor._settings.ajaxData.COMPONENT_NAME,
			'addProperty',
			{
				mode: 'class',
				signedParameters: this._editor._settings.ajaxData.SIGNED_PARAMETERS,
				data: fields
			}
		)
			.then(response => {
				const propertySection = this._editor.getSchemeElementByName(PROPERTY_BLOCK_NAME);
				const property = response.data.PROPERTY_FIELDS;

				if (!propertySection || !property)
				{
					return;
				}

				const additionalValues = response.data.ADDITIONAL_VALUES;
				if (additionalValues)
				{
					const model = this._editor._model;
					for (let [key, value] of Object.entries(additionalValues))
					{
						model.setField(key, value);
					}
				}
				let mode = BX.UI.EntityEditorMode.view;
				if (section instanceof BX.UI.EntityEditorSection)
				{
					mode = section.getMode()
				}

				const control = this.createProperty(property, {
					layout: {
						notifyIfNotDisplayed: true,
						forceDisplay: eventArgs.showAlways,
					},
					mode: mode
				});

				control.toggleOptionFlag(eventArgs.showAlways);

				this._editor.saveSchemeChanges();

				this.isRequesting = false;
			})
			.catch(response => {
				this.isRequesting = false;
			})
		;
	}

	onFieldUpdate(event: BaseEvent)
	{
		const [section, eventArgs] = event.getCompatData();
		if (!(eventArgs.field instanceof BX.UI.EntityEditorControl))
		{
			return;
		}

		const currentField = eventArgs.field;
		eventArgs.CODE = currentField.getId();

		const fields = this.getFieldsForm(eventArgs);
		const schemeElement = currentField.getSchemeElement();
		schemeElement._isRequired = eventArgs.mandatory;

		ajax.runComponentAction(
			this._editor._settings.ajaxData.COMPONENT_NAME,
			'updateProperty',
			{
				mode: 'class',
				signedParameters: this._editor._settings.ajaxData.SIGNED_PARAMETERS,
				data: fields
			}
		).then(response => {
			if (currentField instanceof BX.UI.EntityEditorDatetime || currentField instanceof BX.UI.EntityEditorMultiDatetime)
			{
				const data = currentField.getSchemeElement().getData();
				data.enableTime = eventArgs.enableTime;
			}
			let newType = null;
			let schemeElement = null;
			if (eventArgs.multiple === true)
			{
				if (currentField instanceof BX.UI.EntityEditorText)
				{
					newType = 'multitext';
				}
				else if (currentField instanceof BX.UI.EntityEditorList)
				{
					newType = 'multilist';
				}
				else if (currentField instanceof BX.UI.EntityEditorDatetime)
				{
					newType = 'multidatetime';
				}
				else if (currentField instanceof BX.UI.EntityEditorNumber)
				{
					newType = 'multinumber';
				}
			}
			else
			{
				if (currentField instanceof BX.UI.EntityEditorMultiList)
				{
					newType = 'list';
				}
				else if (currentField instanceof BX.UI.EntityEditorMultiDatetime)
				{
					newType = 'datetime';
				}
				else if (currentField instanceof BX.UI.EntityEditorMultiNumber)
				{
					newType = 'number';
				}
				else if (currentField instanceof BX.UI.EntityEditorMultiText)
				{
					newType = 'text';
				}

				schemeElement = currentField.getSchemeElement();
			}

			const property = response.data.PROPERTY_FIELDS;
			if (
				((currentField instanceof BX.UI.EntityEditorList) || (currentField instanceof BX.UI.EntityEditorMultiList))
				&& property
			)
			{
				schemeElement = BX.UI.EntitySchemeElement.create(property);
				newType = property.type;
			}
			if (newType)
			{
				const index = section.getChildIndex(currentField);
				const newControl = this._editor.createControl(
					newType,
					eventArgs.CODE,
					{
						schemeElement: schemeElement,
						model: section._model,
						parent: section,
						mode: section.getMode()
					}
				);

				section.addChild(newControl, {
					index,
					layout: {
						forceDisplay: true
					},
					enableSaving: false
				});

				section.removeChild(currentField, {
					enableSaving: false
				});
			}

			this.isRequesting = false;
		})
			.catch(response => {
				this.isRequesting = false;
			});
	}

	getFieldsForm(fields)
	{
		const form = new FormData();
		const formatted = {
			NAME: fields.label,
			MULTIPLE: fields.multiple ? 'Y' : 'N',
			IS_REQUIRED: fields.mandatory ? 'Y' : 'N',
			PROPERTY_TYPE: 'S',
			CODE: fields.CODE || ''
		};

		switch (fields.typeId)
		{
			case 'integer':
			case 'double':
				formatted.PROPERTY_TYPE = 'N';
				break;
			case 'list':
			case 'multilist':
				formatted.PROPERTY_TYPE = 'L';
				fields.enumeration.forEach((enumItem, key) => {
					form.append(this.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
					form.append(this.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE);
					form.append(this.getFormFieldName('VALUES][' + key + '][ID'), enumItem.ID);
				});
				break;
			case 'directory':
				formatted.USER_TYPE = 'directory';
				fields.enumeration.forEach((enumItem, key) => {
					form.append(this.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
					form.append(this.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE.value);
					form.append(this.getFormFieldName('VALUES][' + key + '][XML_ID'), enumItem.XML_ID);
					form.append(this.getFormFieldName('VALUES][' + key + '][FILE_ID'), enumItem.FILE_ID);
					form.append('FILES[' + enumItem.SORT + ']', enumItem.VALUE.file);
				});
				break;
			case 'boolean':
				formatted.PROPERTY_TYPE = 'L';
				form.append(this.getFormFieldName('VALUES][0][VALUE'), 'Y')
				formatted.LIST_TYPE = 'C';
				break;
			case 'money':
				formatted.USER_TYPE = 'Money';
				break;
			case 'address':
				formatted.USER_TYPE = 'map_google';
				break;
			case 'datetime':
			case 'multidatetime':
				formatted.USER_TYPE = (fields.enableTime === true) ? 'DateTime' : 'Date';
				break;
			case 'file':
				formatted.USER_TYPE = 'DiskFile';
				break;
		}

		for (let [key, item] of Object.entries(formatted))
		{
			form.append(this.getFormFieldName(key), item);
		}
		return form;
	}

	getFormFieldName(name)
	{
		return 'fields[' + name + ']';
	}

	refreshLinkedProperties(sectionIds)
	{
		if (this.isRequesting)
		{
			return;
		}

		this.isRequesting = true;

		ajax.runComponentAction(
			this._editor._settings.ajaxData.COMPONENT_NAME,
			'refreshLinkedProperties',
			{
				mode: 'class',
				signedParameters: this._editor._settings.ajaxData.SIGNED_PARAMETERS,
				data: {sectionIds}
			}
		)
			.then(response => {
				const allCurrentProperties = this.getAllCurrentProperties();

				if (this.initialElements === null)
				{
					this.initialElements = [...allCurrentProperties];
				}

				response.data.ENTITY_FIELDS.forEach(property => {
					if (!allCurrentProperties.includes(property.name))
					{
						this.addProperty(property, {
							layout: {
								forceDisplay: true
							},
							mode: BX.UI.EntityEditorMode.edit
						});
					}
				});

				const newProperties = response.data.ENTITY_FIELDS.map(el => el.name);
				allCurrentProperties.forEach(name => {
					if (!newProperties.includes(name))
					{
						this.removeProperty(name);
					}
				});

				this._editor.commitSchemeChanges();
				this.isRequesting = false;
			})
			.catch(response => {
				this.isRequesting = false;
			})
		;
	}

	getAllCurrentProperties()
	{
		const activeProperties = this._editor.getAllControls()
			.filter(el => el.getName().indexOf(PROPERTY_PREFIX) === 0)
			.map(el => el.getName());

		const hiddenProperties = this._editor.getAvailableSchemeElements()
			.filter(el => el.getName().indexOf(PROPERTY_PREFIX) === 0)
			.map(el => el.getName());

		return [...activeProperties, ...hiddenProperties];
	}

	addProperty(property, options = {})
	{
		if (property.name in this.deletedControls)
		{
			this.restoreDeletedProperty(this.deletedControls[property.name], options);
		}
		else if (property.name in this.deletedAvailableSchemes)
		{
			this.restoreDeletedAvailableProperty(this.deletedAvailableSchemes[property.name], options);
		}
		else
		{
			this.createProperty(property, options);
		}
	}

	restoreDeletedProperty(control, options = {})
	{
		const mode = options.mode || control._mode;
		control._mode = mode;

		control.getParent().addChild(control, {
			...options,
			enableSaving: false
		});

		if (mode === BX.UI.EntityEditorMode.edit)
		{
			this._editor.registerActiveControl(control);
		}
		else if (mode === BX.UI.EntityEditorMode.view)
		{
			this._editor.unregisterActiveControl(control);
		}
	}

	restoreDeletedAvailableProperty(schemeElement, options = {})
	{
		this._editor.addAvailableSchemeElement(schemeElement);
	}

	createProperty(property, options = {})
	{
		const propertyBlockScheme = this._editor.getSchemeElementByName(PROPERTY_BLOCK_NAME);
		const schemeElement = BX.UI.EntitySchemeElement.create(property);
		propertyBlockScheme._elements.push(schemeElement);

		const mode = options.mode || BX.UI.EntityEditorMode.edit;
		const control = this._editor.createControl(
			schemeElement.getType(),
			schemeElement.getName(),
			{
				schemeElement: schemeElement,
				model: this._model,
				parent: this,
				mode: mode
			}
		);

		if (!control)
		{
			return;
		}

		const propertyBlockControl = this._editor.getControlById(PROPERTY_BLOCK_NAME);
		propertyBlockControl.addChild(control, {
			...options,
			enableSaving: false
		});

		return control;
	}

	removeProperty(name)
	{
		const control = this._editor.getControlByIdRecursive(name);

		if (control)
		{
			this.deletedControls[control.getName()] = control;
			control.getParent().removeChild(control, {enableSaving: false});
			this._editor.removeAvailableSchemeElement(control.getSchemeElement());
			this._editor.unregisterActiveControl(control);
		}
		else
		{
			const schemeElement = this._editor.getAvailableSchemeElementByName(name);

			if (schemeElement)
			{
				this.deletedAvailableSchemes[schemeElement.getName()] = schemeElement;
				this._editor.removeAvailableSchemeElement(schemeElement);
			}
		}
	}

	rollback()
	{
		super.rollback();

		if (this.initialElements === null)
		{
			return;
		}

		const allCurrentProperties = this.getAllCurrentProperties();

		allCurrentProperties.forEach(element => {
			if (!this.initialElements.includes(element))
			{
				this.removeProperty(element);
			}
		});

		this.initialElements.forEach(element => {
			if (!allCurrentProperties.includes(element))
			{
				this.addProperty({name: element}, {
					layout: {
						forceDisplay: false
					},
					mode: BX.UI.EntityEditorMode.view
				});
			}
		});

		this._editor.commitSchemeChanges();

		this.clearServiceFields()
	}
}