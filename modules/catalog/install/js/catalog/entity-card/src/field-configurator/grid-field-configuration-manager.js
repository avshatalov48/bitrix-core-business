import GridFieldConfigurator from './grid-field-configurator'
import {Loc, Reflection, Type} from 'main.core';

export default class GridFieldConfigurationManager extends BX.UI.EntityConfigurationManager
{
	createFieldConfigurator(params, parent)
	{
		if (!Type.isPlainObject(params))
		{
			throw "GridFieldConfigurationManager: The 'params' argument must be object.";
		}

		return this.getSimpleFieldConfigurator(params, parent);
	}

	getSimpleFieldConfigurator(params, parent)
	{
		let typeId = "";
		const child = BX.prop.get(params, 'field', null);
		if (child)
		{
			typeId = child.getType();
			child.setVisible(false);

			if (!BX.prop.get(child.getSchemeElement().getData(), "isProductProperty", false))
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
						field: child,
						mandatoryConfigurator: null
					}
				);
			}
		}
		else
		{
			typeId = BX.prop.get(params, 'typeId', BX.UI.EntityUserFieldType.string);
		}

		this._fieldConfigurator = GridFieldConfigurator.create(
			'',
			{
				editor: this._editor,
				schemeElement: null,
				model: parent._model,
				mode: BX.UI.EntityEditorMode.edit,
				parent: parent,
				typeId: typeId,
				field: child,
				mandatoryConfigurator: null
			}
		);

		return this._fieldConfigurator;
	}

	isSelectionEnabled()
	{
		return false;
	}

	isCreationEnabled()
	{
		return false;
	}

	hasExternalForm(typeId)
	{
		return true;
	}

	getCreationPageUrl(typeId)
	{
		const filtered = this.getTypeInfos().filter((item) => {
			return item.name === typeId
		});
		if (filtered.length > 0)
		{
			return this.creationPageUrl.replace('#PROPERTY_TYPE#', typeId);
		}
	}

	openCreationPageUrl(typeId)
	{
		this.openCreationPageSlider(this.getCreationPageUrl(typeId));
	}

	openCreationPageSlider(url)
	{
		if (Type.isStringFilled(url))
		{
			BX.SidePanel.Instance.open(url, {
				width: 550,
				allowChangeHistory: false,
				cacheable: false
			});
		}
	}

	setCreationPageUrl(url)
	{
		return this.creationPageUrl = url;
	}

	getTypeInfos()
	{
		return [
			{
				name: "list",
				title: BX.message("CATALOG_ENTITY_CARD_LIST_TITLE"),
				legend: BX.message("CATALOG_ENTITY_CARD_LIST_LEGEND")
			},
			{
				name: "directory",
				title: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_TITLE"),
				legend: BX.message("CATALOG_ENTITY_CARD_DICTIONARY_LEGEND")
			}
		];
	}

	static create(id, settings)
	{
		const self = new this;
		self.initialize(id, settings);
		return self;
	}
}
