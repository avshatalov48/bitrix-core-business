import {Dom, Event, Tag} from "main.core";

export default class GridFieldConfigurator extends BX.UI.EntityEditorFieldConfigurator
{
	static create(id, settings)
	{
		const self = new this;
		self.initialize(id, settings);
		return self;
	}

	// ToDo remove unused methods
	appendEnumerationSettings()
	{
		if (this._typeId === "list" || this._typeId === "multilist")
		{
			Dom.append(Tag.render`<hr class="ui-entity-editor-line">`, this._wrapper);

			const enumWrapper = Tag.render`
				<div class="ui-entity-editor-content-block">
					<div class="ui-entity-editor-block-title">
						<span class="ui-entity-editor-block-title-text">${BX.message("UI_ENTITY_EDITOR_UF_ENUM_ITEMS")}</span>
					</div>
				</div>
			`;

			Dom.append(enumWrapper, this._wrapper);

			this._enumItemContainer = Tag.render`
					<div class="ui-entity-editor-content-block"></div>
				`;
			Dom.append(this._enumItemContainer, enumWrapper);

			const addButton = Tag.render`
					<div class="ui-entity-card-content-add-field">
						${BX.message("UI_ENTITY_EDITOR_ADD")}
					</div>
				`;
			Event.bind(addButton, "click", this.onEnumerationItemAddButtonClick.bind(this));

			Dom.append(
				Tag.render`
					<div class="ui-entity-editor-content-block-add-field">
						${addButton}
					</div>
				`,
				enumWrapper
			);

			if (this._field)
			{
				this._field.getItems().forEach(enumFields => {
					if (enumFields.VALUE !== '')
					{
						this.createEnumerationItem({
							VALUE: enumFields.NAME,
							ID: enumFields.VALUE
						});
					}
				});
			}

			this.createEnumerationItem();
			this.initItemFocusHandlers();
		}
	}

	onEnumerationItemAddButtonClick()
	{
		this.unbindItemFocusHandlers();
		this.createEnumerationItem().focus();
		this.bindLastItemFocusHandler();
	}

	onEnumerationItemFocus()
	{
		this.unbindItemFocusHandlers();
		this.createEnumerationItem();
		this.bindLastItemFocusHandler();
	}

	initItemFocusHandlers()
	{
		this.unbindItemFocusHandlers();
		this.bindLastItemFocusHandler();
	}

	unbindItemFocusHandlers()
	{
		this._enumItems.forEach(item => Event.unbindAll(item._labelInput, 'focus'));
	}

	bindLastItemFocusHandler()
	{
		const lastItem = this._enumItems[this._enumItems.length - 1];
		if (lastItem)
		{
			Event.bindOnce(lastItem._labelInput, 'focus', this.onEnumerationItemFocus.bind(this));
		}
	}

	createEnumerationItem(data)
	{
		var item = BX.UI.EntityEditorUserFieldListItem.create(
			"",
			{
				configurator: this,
				container: this._enumItemContainer,
				data: data
			}
		);

		this._enumItems.push(item);
		item.layout();
		return item;
	}

	removeEnumerationItem(item)
	{
		for (var i = 0, length = this._enumItems.length; i < length; i++)
		{
			if (this._enumItems[i] === item)
			{
				this._enumItems[i].clearLayout();
				this._enumItems.splice(i, 1);
				this.initItemFocusHandlers();
				break;
			}
		}
	}

	prepareSaveParams(e)
	{
		const params = super.prepareSaveParams(this, arguments);
		if (this._typeId === 'list' || this._typeId === 'multilist')
		{
			params['enumeration'] = [];
			const hashes = [];
			this._enumItems.forEach(enumItem => {
				if (!(enumItem instanceof BX.UI.EntityEditorUserFieldListItem))
				{
					return;
				}

				const enumData = enumItem.prepareData();
				if (!enumData)
				{
					return;
				}

				const hash = BX.util.hashCode(enumData['VALUE']);
				if (BX.util.in_array(hash, hashes))
				{
					return;
				}

				hashes.push(hash);
				enumData['SORT'] = (params['enumeration'].length + 1) * 100;
				params['enumeration'].push(enumData);
			});
		}
		else if (this._typeId === 'datetime' || this._typeId === 'multidatetime')
		{
			params['enableTime'] = this._isTimeEnabledCheckBox.checked;
		}

		return params;
	}

	getMultipleCheckBox()
	{
		var checkBox = this.createOption({caption: BX.message("UI_ENTITY_EDITOR_UF_MULTIPLE_FIELD")});
		if (
			this._field instanceof BX.UI.EntityEditorMultiText
			|| this._field instanceof BX.UI.EntityEditorMultiNumber
			|| this._field instanceof BX.UI.EntityEditorMultiList
			|| this._field instanceof BX.UI.EntityEditorMultiDatetime
		)
		{
			checkBox.checked = true;
		}

		return checkBox;
	}

	getIsRequiredCheckBox()
	{
		let checkBox = null;
		if (this._typeId !== "boolean")
		{
			if (this._enableMandatoryControl)
			{
				if (this._mandatoryConfigurator)
				{
					checkBox = this.createOption(
						{
							caption: this._mandatoryConfigurator.getTitle() + ":",
							labelSettings: {props: {className: "ui-entity-new-field-addiction-label"}},
							containerSettings: {style: {alignItems: "center"}},
							elements: this._mandatoryConfigurator.getButton().prepareLayout()
						}
					);

					checkBox.checked = (this._field && this._field.isRequired())
						|| this._mandatoryConfigurator.isCustomized();

					this._mandatoryConfigurator.setSwitchCheckBox(checkBox);
					this._mandatoryConfigurator.setLabel(checkBox.nextSibling);

					this._mandatoryConfigurator.setEnabled(checkBox.checked);
					this._mandatoryConfigurator.adjust();
				}
				else
				{
					checkBox = this.createOption({caption: BX.message("UI_ENTITY_EDITOR_UF_REQUIRED_FIELD")});
					checkBox.checked = this._field && this._field.isRequired();
				}
			}
		}

		return checkBox;
	}

	getIsTimeEnabledCheckBox()
	{
		var checkBox = null;
		if (this._typeId === "datetime" || this._typeId === "multidatetime")
		{
			checkBox = this.createOption({caption: BX.message("UI_ENTITY_EDITOR_UF_ENABLE_TIME")});
			checkBox.checked = this._field && this._field.isTimeEnabled();
		}
		return checkBox;
	}
}