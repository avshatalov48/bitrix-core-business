import IblockFieldConfigurator from './iblock-field-configurator'

export default class IblockFieldConfigurationManager extends BX.UI.EntityConfigurationManager
{
	createFieldConfigurator(params, parent)
	{
		if (!BX.type.isPlainObject(params))
		{
			throw "IblockFieldConfigurationManager: The 'params' argument must be object.";
		}

		return this.getSimpleFieldConfigurator(params, parent);
	}

	getSimpleFieldConfigurator(params, parent)
	{
		let typeId = "";
		const field = BX.prop.get(params, 'field', null);
		if (field)
		{
			typeId = field.getType();
			field.setVisible(false);

			if (!BX.prop.get(field.getSchemeElement().getData(), "isProductProperty", false))
			{
				return this._fieldConfigurator = BX.UI.EntityEditorFieldConfigurator.create(
					"",
					{
						editor: this._editor,
						schemeElement: null,
						model: parent._model,
						mode: BX.UI.EntityEditorMode.edit,
						parent: parent,
						typeId: typeId,
						field: field,
						mandatoryConfigurator: null
					}
				);
			}
			else if (BX.prop.get(field.getSchemeElement().getData(), "userType", false))
			{
				typeId = BX.prop.getString(field.getSchemeElement().getData(), "userType")
			}
		}
		else
		{
			typeId = BX.prop.get(params, 'typeId', BX.UI.EntityUserFieldType.string);
		}

		this._fieldConfigurator = IblockFieldConfigurator.create(
			'',
			{
				editor: this._editor,
				schemeElement: null,
				model: parent._model,
				mode: BX.UI.EntityEditorMode.edit,
				parent: parent,
				typeId: typeId,
				field: field,
				mandatoryConfigurator: null
			}
		);

		return this._fieldConfigurator;
	}

	isCreationEnabled()
	{
		return this._editor?.isSectionEditEnabled() && !this._editor?.isReadOnly();
	}

	getCreationPageUrl(typeId)
	{
		return this.creationPageUrl;
	}

	openCreationPageUrl(typeId)
	{
		BX.SidePanel.Instance.open(this.getCreationPageUrl(typeId), {
			width: 900, // corresponds to the slider settings on `iblock/install/components/bitrix/iblock.property.grid/templates/.default/script.es6.js`
			allowChangeHistory: false,
			cacheable: false
		});
	}

	setCreationPageUrl(url)
	{
		return this.creationPageUrl = url;
	}

	getTypeInfos()
	{
		var items = [];
		items.push({
			name: "string",
			title: BX.message("UI_ENTITY_EDITOR_UF_STRING_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_STRING_LEGEND")
		});
		items.push({
			name: "list",
			title: BX.message("UI_ENTITY_EDITOR_UF_ENUM_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_ENUM_LEGEND")
		});
		items.push({
			name: "datetime",
			title: BX.message("UI_ENTITY_EDITOR_UF_DATETIME_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_DATETIME_LEGEND")
		});
		items.push({
			name: "address",
			title: BX.message("UI_ENTITY_EDITOR_UF_ADDRESS_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_ADDRESS_LEGEND")
		});

		items.push({
			name: "money",
			title: BX.message("UI_ENTITY_EDITOR_UF_MONEY_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_MONEY_LEGEND")
		});
		items.push({
			name: "boolean",
			title: BX.message("UI_ENTITY_EDITOR_BOOLEAN_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_BOOLEAN_LEGEND")
		});
		items.push({
			name: "double",
			title: BX.message("UI_ENTITY_EDITOR_UF_DOUBLE_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_DOUBLE_LEGEND")
		});
		items.push({
			name: "directory",
			title: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_TITLE"),
			legend: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_LEGEND")
		});

		items.push({
			name: "custom",
			title: BX.message("UI_ENTITY_EDITOR_UF_CUSTOM_TITLE"),
			legend: BX.message("UI_ENTITY_EDITOR_UF_CUSTOM_LEGEND")
		});

		return items;
	}

	static create(id, settings)
	{
		const self = new this;
		self.initialize(id, settings);
		return self;
	}
}
