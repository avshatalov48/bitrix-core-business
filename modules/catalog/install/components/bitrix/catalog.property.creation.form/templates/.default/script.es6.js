import {ajax, Dom, Reflection, Tag, Type} from 'main.core';

class PropertyCreationForm extends BX.Catalog.IblockFieldConfigurator
{
	initialize(id, settings = {})
	{
		super.initialize(id, settings);
		BX.addCustomEvent(this, "onSave", BX.delegate(this.onFormSave, this));
		BX.addCustomEvent(this, "onCancel", BX.delegate(this.onFormCancel, this));
		this.componentName = settings.componentName || '';
		this.signedParameters = settings.signedParameters || '';
	}

	getInputTitle()
	{
		return (!this.isCreationMode()) ? this._field.getTitle() : '';
	}

	isCreationMode()
	{
		return this._field === null;
	}

	onFormSave(sender, params)
	{
		if (this._isLocked)
		{
			return;
		}

		this._isLocked = true;
		BX.addClass(this._saveButton, "ui-btn-wait");
		const fields = this.formatConfiguratorFields(params);

		if (this.isCreationMode())
		{
			this.addProperty(fields);
		}
		else
		{
			this.updateProperty(fields);
		}
	}

	addProperty(fields)
	{
		ajax.runComponentAction(
			this.componentName,
			'addProperty',
			{
				mode: 'class',
				signedParameters: this.signedParameters,
				data: fields
			}
		)
			.then(response => {
				fields.CODE = response.data.PROPERTY_GRID_CODE;
				BX.SidePanel.Instance.postMessage(window, 'PropertyCreationForm:onAdd', {fields});
				this.onFormCancel();
			})
			.catch(this.onError.bind(this))
		;
	}

	updateProperty(fields)
	{
		ajax.runComponentAction(
			this.componentName,
			'updateProperty',
			{
				mode: 'class',
				signedParameters: this.signedParameters,
				data: fields
			}
		)
			.then(response => {
				BX.SidePanel.Instance.postMessage(window, 'PropertyCreationForm:onModify', {fields});
				this.onFormCancel();
			})
			.catch(this.onError.bind(this))
		;
	}

	onError(response)
	{
		Dom.removeClass(this._saveButton, "ui-btn-wait");
		this._isLocked = false;

		if (this._errorContainer)
		{
			Dom.clean(this._errorContainer);

			if (Type.isArray(response.errors))
			{
				response.errors.forEach((error) => {
					this.showError(error);
				});
			}
		}
	}

	getFormFieldName(name)
	{
		return 'fields['+name+']';
	}

	showError(error)
	{
		this._errorContainer.append(
			Tag.render`
				<p class="ui-entity-editor-field-error-text">${error.message}</p>
			`
		);
	}

	formatConfiguratorFields(fields)
	{
		const form = new FormData();
		const formatted = {
			NAME: fields.label,
			MULTIPLE: fields.multiple ? 'Y' : 'N',
			IS_REQUIRED: fields.mandatory ? 'Y' : 'N',
			PROPERTY_TYPE: 'S',
		};

		if (fields.field)
		{
			formatted.ID = fields.field.getId();
		}

		switch (fields.typeId)
		{
			case 'directory':
				formatted.DIRECTORY_NAME = fields.directoryName;
				formatted.USER_TYPE = 'directory';
				fields.enumeration = fields.enumeration || [];
				fields.enumeration.forEach((enumItem, key) => {
					form.append(this.getFormFieldName('VALUES]['+key+'][SORT'), enumItem.SORT);
					form.append(this.getFormFieldName('VALUES]['+key+'][VALUE'), enumItem.VALUE.value);
					if (!this.isCreationMode())
					{
						form.append(this.getFormFieldName('VALUES]['+key+'][XML_ID'), enumItem.XML_ID);
						form.append(this.getFormFieldName('VALUES]['+key+'][FILE_ID'), enumItem.FILE_ID);
					}
					form.append('FILES['+enumItem.SORT+']' , enumItem.VALUE.file);
				});
				break;
			case 'list':
				formatted.PROPERTY_TYPE = 'L';
				fields.enumeration = fields.enumeration || [];
				fields.enumeration.forEach((enumItem, key) => {
					form.append(this.getFormFieldName('VALUES]['+key+'][SORT'), enumItem.SORT);
					form.append(this.getFormFieldName('VALUES]['+key+'][VALUE'), enumItem.VALUE);
					form.append(this.getFormFieldName('VALUES]['+key+'][ID'), enumItem.ID);
				});
				break;
			case 'boolean':
				formatted.PROPERTY_TYPE = 'L';
				formatted.VALUES = ['Y'];
				formatted.LIST_TYPE = 'C';
				break;
		}

		for (let [key, item] of Object.entries(formatted))
		{
			form.append(this.getFormFieldName(key), item);
		}
		return form;
	}

	onFormCancel()
	{
		BX.SidePanel.Instance.close();
	}
}

Reflection.namespace('BX.Catalog').PropertyCreationForm = PropertyCreationForm;