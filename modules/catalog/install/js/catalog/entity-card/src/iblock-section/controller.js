import {ajax} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events'

const PROPERTY_PREFIX = 'PROPERTY_';
const PROPERTY_BLOCK_NAME = 'properties';

export default class IblockSectionController extends BX.UI.EntityEditorController
{
	onChangeHandler = this.handleChange.bind(this);

	constructor(id, settings = {})
	{
		super();
		this.initialize(id, settings);

		this.isRequesting = false;
		this.clearServiceFields();

		EventEmitter.subscribe(this._editor, 'IblockSectionField:onChange', this.onChangeHandler);
	}

	clearServiceFields()
	{
		this.lastDataHash = null;
		this.initialElements = null;
		this.deletedControls = {};
		this.deletedAvailableSchemes = {};
	}

	handleChange(event: BaseEvent)
	{
		const newData = event.getData();
		newData.shift();
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