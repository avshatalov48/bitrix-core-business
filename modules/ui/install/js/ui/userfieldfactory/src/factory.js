import {Loc, Type, Reflection} from "main.core";
import {EventEmitter} from "main.core.events";

import {CreationMenu} from "./creationmenu";
import {Field} from "./field";
import {MAX_FIELD_LENGTH, DefaultData, DefaultFieldData, FieldDescriptions} from "./fieldtypes";
import {Configurator} from "./configurator";

import 'uf';

/**
 * @memberof BX.UI.UserFieldFactory
 * @mixes EventEmitter
 */
export class Factory
{
	constructor(entityId: string, params: {
		creationSignature: string,
		menuId: ?string,
		types: ?Array,
		bindElement: ?Element,
		configuratorClass: ?Configurator,
	} = {})
	{
		EventEmitter.makeObservable(this, 'UI.UserFieldFactory.Factory');
		this.configuratorClass = Configurator;
		if(Type.isString(entityId) && entityId.length > 0)
		{
			this.entityId = entityId;
		}
		if(Type.isPlainObject(params))
		{
			if(Type.isString(params.creationSignature))
			{
				this.creationSignature = params.creationSignature;
			}
			if(Type.isString(params.menuId))
			{
				this.menuId = params.menuId;
			}
			if(!Type.isArray(params.types))
			{
				params.types = [];
			}
			if(Type.isDomNode(params.bindElement))
			{
				this.bindElement = params.bindElement;
			}
			this.setConfiguratorClass(params.configuratorClass);
		}
		else
		{
			params.types = [];
		}
		this.types =  this.getFieldTypes().concat(params.types);
	}

	getFieldTypes(): Array
	{
		const types = [];

		Object.keys(FieldDescriptions).forEach((name) =>
		{
			types.push({...FieldDescriptions[name], ...{name}});
		});

		this.emit('OnGetUserTypes', {
			types
		});

		return types;
	}

	getMenu(params: Object): CreationMenu
	{
		if(!Type.isPlainObject(params))
		{
			params = {};
		}
		if(!Type.isDomNode(params.bindElement))
		{
			params.bindElement = this.bindElement;
		}
		if(!this.menu)
		{
			this.menu = new CreationMenu(this.menuId, this.types, params);
		}

		return this.menu;
	}

	setConfiguratorClass(configuratorClassName: string|Function)
	{
		let configuratorClass = null;
		if(Type.isString(configuratorClassName))
		{
			configuratorClass = Reflection.getClass(configuratorClassName);
		}
		else if(Type.isFunction(configuratorClassName))
		{
			configuratorClass = configuratorClassName;
		}

		if(Type.isFunction(configuratorClass) && configuratorClass.prototype instanceof Configurator)
		{
			this.configuratorClass = configuratorClass;
		}
	}

	getConfigurator(params: {
		field: Field,
		onSave: Function,
		onCancel: ?Function,
	}): Configurator
	{
		return new this.configuratorClass(params);
	}

	createField(fieldType: string, fieldName: ?string): Field
	{
		let data = {...DefaultData, ...DefaultFieldData[fieldType], ...{USER_TYPE_ID: fieldType}};

		if(!Type.isString(fieldName) || fieldName.length <= 0 || fieldName.length > MAX_FIELD_LENGTH)
		{
			fieldName = this.generateFieldName();
		}
		data.FIELD = fieldName;
		data.ENTITY_ID = this.entityId;
		data.SIGNATURE = this.creationSignature;

		const field = new Field(data);
		field.setTitle(this.getDefaultLabel(fieldType));

		this.emit('onCreateField', {
			field,
		});

		return field;
	}

	getDefaultLabel(fieldType: string): string
	{
		let label = Loc.getMessage('UI_USERFIELD_FACTORY_UF_LABEL');
		this.types.forEach((type) =>
		{
			if(type.name === fieldType && Type.isString(type.defaultTitle))
			{
				label = type.defaultTitle;
			}
		});

		return label;
	}

	generateFieldName(): string
	{
		let name = 'UF_' + (this.entityId ? (this.entityId + "_") : "");
		let dateSuffix = (new Date()).getTime().toString();
		if(name.length + dateSuffix.length > MAX_FIELD_LENGTH)
		{
			dateSuffix = dateSuffix.substr(((name.length + dateSuffix.length) - MAX_FIELD_LENGTH));
		}

		name += dateSuffix;

		return name;
	}

	saveField(field: Field): Promise<?Field, Array>
	{
		return new Promise((resolve, reject) =>
		{
			if(field instanceof Field)
			{
				if(field.isSaved())
				{
					this.getEditManager().update({ "FIELDS": [field.getData()]}, (response) =>
					{
						this.onFieldSave(field, response, resolve, reject);
					});
				}
				else
				{
					this.getEditManager().add({ "FIELDS": [field.getData()]}, (response) =>
					{
						this.onFieldSave(field, response, resolve, reject);
					});
				}
			}
			else
			{
				reject(['Wrong parameter: field must be instance of Field']);
			}
		});
	}

	deleteField(field: Field): Promise
	{
		return new Promise((resolve, reject) =>
		{
			if(field instanceof Field)
			{
				if(field.isSaved())
				{
					this.getEditManager().delete({ "FIELDS": [field.getData()]}, (response) =>
					{
						this.onFieldDelete(field, response, resolve, reject);
					});
				}
			}
			else
			{
				reject(['Wrong parameter: field must be instance of Field']);
			}
		});
	}

	onFieldSave(field: Field, response, onSuccess: Function, onError: Function): void
	{
		if(Type.isPlainObject(response))
		{
			if(response.ERROR && Type.isArray(response.ERROR) && response.ERROR.length > 0)
			{
				onError(response.ERROR);
			}
			else
			{
				const fieldData = this.getFieldDataFromResponse(response);
				if(fieldData)
				{
					field.markAsSaved()
						.setData(fieldData);
					if(Type.isFunction(onSuccess))
					{
						onSuccess(field);
					}
					this.emit('onFieldSave', {
						field,
					});
				}
			}
		}
		else
		{
			if(Type.isFunction(onError))
			{
				if(Type.isArray(this.managerErrors) && this.managerErrors.length > 0)
				{
					onError(this.managerErrors);
					this.managerErrors = [];
				}
				else
				{
					onError([Loc.getMessage('UI_USERFIELD_SAVE_ERROR')]);
				}
			}
		}
	}

	onFieldDelete(field: Field, response, onSuccess: Function, onError: Function): void
	{
		if(Type.isPlainObject(response) || Type.isArray(response))
		{
			if(Type.isPlainObject(response) && response.ERROR && Type.isArray(response.ERROR) && response.ERROR.length > 0)
			{
				onError(response.ERROR);
			}
			else
			{
				if(Type.isFunction(onSuccess))
				{
					onSuccess(field);
				}
				this.emit('onFieldDelete', {
					field,
				});
			}
		}
		else
		{
			if(Type.isFunction(onError))
			{
				if(Type.isArray(this.managerErrors) && this.managerErrors.length > 0)
				{
					onError(this.managerErrors);
					this.managerErrors = [];
				}
				else
				{
					onError([Loc.getMessage('UI_USERFIELD_DELETE_ERROR')]);
				}
			}
		}
	}

	getFieldDataFromResponse(response: Object): ?Object
	{
		if(Type.isPlainObject(response))
		{
			let fieldData = null;
			Object.keys(response).forEach((fieldName) =>
			{
				if(Type.isPlainObject(response[fieldName]['FIELD']))
				{
					fieldData = response[fieldName]['FIELD'];
				}
			});

			return fieldData;
		}

		return null;
	}

	getEditManager(): BX.Main.UF.Manager
	{
		if(!this.editManager)
		{
			this.editManager = BX.Main.UF.EditManager;

			this.editManager.displayError = (errors: Array) =>
			{
				this.managerErrors = errors;
			};
		}

		return this.editManager;
	}
}