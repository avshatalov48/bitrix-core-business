import {Loc, Type, Reflection} from "main.core";
import {EventEmitter} from "main.core.events";

import {CreationMenu} from "./creationmenu";
import {UserField} from 'ui.userfield';
import {MAX_FIELD_LENGTH, DefaultData, DefaultFieldData, FieldTypes} from "./fieldtypes";
import {Configurator} from "./configurator";

import 'sidepanel';
import 'uf';

/**
 * @memberof BX.UI.UserFieldFactory
 * @mixes EventEmitter
 */
export class Factory
{
	constructor(entityId: string, params: {
		menuId: ?string,
		types: ?Array,
		bindElement: ?Element,
		configuratorClass: ?Configurator,
		customTypesUrl: ?string,
		moduleId: ?string,
	} = {})
	{
		EventEmitter.makeObservable(this, 'BX.UI.UserFieldFactory.Factory');
		this.configuratorClass = Configurator;
		if(Type.isString(entityId) && entityId.length > 0)
		{
			this.entityId = entityId;
		}
		if(Type.isPlainObject(params))
		{
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
			this.moduleId = params.moduleId;
			this.setCustomTypesUrl(params.customTypesUrl)
				.setConfiguratorClass(params.configuratorClass);
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

		Object.keys(FieldTypes.getDescriptions()).forEach((name) =>
		{
			types.push({...FieldTypes.getDescriptions()[name], ...{name}});
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
		const types = this.types;
		if(this.customTypesUrl && !this.isCustomTypeAdded)
		{
			const customType = {...FieldTypes.getCustomTypeDescription()};
			customType.onClick = this.onCustomTypeClick.bind(this);
			types.push(customType);
			this.isCustomTypeAdded = true;
		}
		if(!this.menu)
		{
			this.menu = new CreationMenu(this.menuId, types, params);
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

	setCustomTypesUrl(customTypesUrl: string): this
	{
		this.customTypesUrl = customTypesUrl;

		return this;
	}

	getConfigurator(params: {
		userField: UserField,
		onSave: Function,
		onCancel: ?Function,
	}): Configurator
	{
		return new this.configuratorClass(params);
	}

	createUserField(fieldType: string, fieldName: ?string): UserField
	{
		let data = {...DefaultData, ...DefaultFieldData[fieldType], ...{userTypeId: fieldType}};

		if(!Type.isString(fieldName) || fieldName.length <= 0 || fieldName.length > MAX_FIELD_LENGTH)
		{
			fieldName = this.generateFieldName();
		}
		data.fieldName = fieldName;
		data.entityId = this.entityId;

		const userField = new UserField(data, {
			moduleId: this.moduleId,
		});
		userField.setTitle(this.getDefaultLabel(fieldType));

		this.emit('onCreateField', {
			userField,
		});

		return userField;
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

	onCustomTypeClick()
	{
		if(!this.customTypesUrl)
		{
			return;
		}
		BX.SidePanel.Instance.open(this.customTypesUrl.toString(), {
			cacheable: false,
			allowChangeHistory: false,
			width: 900,
			events: {
				onClose: (event) => {
					const slider = event.getSlider();
					if(slider)
					{
						const userFieldData = slider.getData().get('userFieldData');
						if(userFieldData)
						{
							const userField = UserField.unserialize(userFieldData);
							this.emit('onCreateCustomUserField', {userField});
						}
					}
				}
			}
		});
	}
}