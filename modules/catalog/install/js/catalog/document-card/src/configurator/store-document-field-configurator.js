import { Reflection, Loc } from 'main.core';

export default class StoreDocumentFieldConfigurator extends BX.UI.EntityEditorFieldConfigurator
{
	static create(id, settings): StoreDocumentFieldConfigurator
	{
		const self: StoreDocumentFieldConfigurator = new this();
		self.initialize(id, settings);

		return self;
	}

	getOptionContainer()
	{
		const optionContainer = super.getOptionContainer();
		this._isRequiredCheckBox = this.getField().getData().requiredIsEditable ? this.getIsRequiredCheckBox() : null;

		return optionContainer;
	}

	onSaveButtonClick(): void
	{
		this.getField().getSchemeElement()._isRequired = this._isRequiredCheckBox.checked;
		super.onSaveButtonClick();

		BX.ajax.runComponentAction(
			'bitrix:catalog.store.document.detail',
			'changeRequired',
			{
				mode: 'class',
				data: {
					documentType: this.getEditor().getModel().getData().DOC_TYPE,
					fieldName: this.getField()._id,
					required: this.getField().isRequired() ? 'Y' : 'N',
				},
			},
		);
	}

	getIsRequiredCheckBox()
	{
		const checkBox = this.createOption({ caption: Loc.getMessage('UI_ENTITY_EDITOR_UF_REQUIRED_FIELD') });
		checkBox.checked = this._field && this._field.isRequired();

		return checkBox;
	}
}

Reflection.namespace('BX.Catalog').StoreDocumentFieldConfigurator = StoreDocumentFieldConfigurator;
