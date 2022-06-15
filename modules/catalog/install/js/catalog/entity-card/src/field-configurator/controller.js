import {ajax} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events'

export default class FieldConfiguratorController extends BX.UI.EntityEditorController
{
	fieldAddHandler = this.handleFieldAdd.bind(this);
	fieldUpdateHandler = this.handleFieldUpdate.bind(this);

	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);

		EventEmitter.subscribe(this._editor, 'BX.UI.EntityEditor:onFieldCreate', this.fieldAddHandler);
		EventEmitter.subscribe(this._editor, 'BX.UI.EntityEditor:onFieldModify', this.fieldUpdateHandler);
	}

	handleFieldAdd(event: BaseEvent)
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
				const property = response.data.PROPERTY_FIELDS;
				if (!property)
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

				const control = this.createProperty(property, section.getName(), {
					layout: {
						notifyIfNotDisplayed: true,
						forceDisplay: eventArgs.showAlways
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

	handleFieldUpdate(event: BaseEvent)
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
			const property = response?.data?.PROPERTY_FIELDS;
			if (currentField instanceof BX.UI.EntityEditorDatetime || currentField instanceof BX.UI.EntityEditorMultiDatetime)
			{
				const schemeElementData = currentField.getSchemeElement().getData();
				const propertyData = property?.data;
				if (propertyData)
				{
					schemeElementData.enableTime = propertyData.enableTime;
					schemeElementData.dateViewFormat = propertyData.dateViewFormat;
					currentField.refreshLayout();
				}
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
			}
			schemeElement = currentField.getSchemeElement();
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

				currentField._schemeElement = null;
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
			IS_PUBLIC: fields.isPublic ? 'Y' : 'N',
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
				(fields.enumeration || []).forEach((enumItem, key) => {
					form.append(this.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
					form.append(this.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE);
					form.append(this.getFormFieldName('VALUES][' + key + '][ID'), enumItem.ID);
				});
				break;
			case 'directory':
				formatted.USER_TYPE = 'directory';
				(fields.enumeration || []).forEach((enumItem, key) => {
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

	createProperty(property, sectionName, options = {})
	{
		const sectionSchemeElement = this._editor.getSchemeElementByName(sectionName);
		if (!sectionSchemeElement)
		{
			return;
		}

		const schemeElement = BX.UI.EntitySchemeElement.create(property);
		sectionSchemeElement._elements.push(schemeElement);

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

		const sectionControl = this._editor.getControlById(sectionName);
		sectionControl.addChild(control, {
			...options,
			enableSaving: false
		});

		return control;
	}
}