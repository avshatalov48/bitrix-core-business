import {Dom, Event, Reflection, Tag, Text, Type, Loc} from "main.core";
import IblockDirectoryFieldItem from "./iblock-directory-field-item";

export default class IblockFieldConfigurator extends BX.UI.EntityEditorFieldConfigurator
{
	static create(id, settings)
	{
		const self = new this;
		self.initialize(id, settings);
		return self;
	}
	constructor()
	{
		super();
		this._enumItems = [];
	}
	layoutInternal()
	{
		Dom.append(this.getInputContainer(), this._wrapper);
		if(this._typeId === "list" || this._typeId === "multilist" || this._typeId === "directory")
		{
			Dom.append(Tag.render`<hr class="ui-entity-editor-line">`, this._wrapper);
			if (BX.prop.get(this._field?.getSchemeElement().getData(), 'isConfigurable', null) !== false)
			{
				Dom.append(this.getEnumerationContainer(), this._wrapper);
			}
		}

		Dom.append(this.getOptionContainer(), this._wrapper);
		Dom.append(this.getErrorContainer(), this._wrapper);
		Dom.append(Tag.render`<hr class="ui-entity-editor-line">`, this._wrapper);
		Dom.append(this.getButtonContainer(), this._wrapper);
	}
	getOptionContainer()
	{
		const isNew = (this._field === null);
		this._optionWrapper = Tag.render`
			<div class="ui-entity-editor-content-block"></div>
		`;

		if (this._typeId === "datetime" || this._typeId === "multidatetime")
		{
			this._isTimeEnabledCheckBox = this.getIsTimeEnabledCheckBox();
		}

		if (this._typeId !== "boolean" && this._enableMandatoryControl)
		{
			this._isRequiredCheckBox = this.getIsRequiredCheckBox();
		}

		if (this.isAllowedMultipleCheckBox())
		{
			this._isMultipleCheckBox = this.getMultipleCheckBox();
		}

		this._isPublic = this.getIsPublicCheckBox();

		//region Show Always
		this._showAlwaysCheckBox = this.createOption({
			caption: Loc.getMessage('UI_ENTITY_EDITOR_SHOW_ALWAYS'),
			helpUrl: 'https://helpdesk.bitrix24.ru/open/7046149/',
			helpCode: '9627471'
		});
		this._showAlwaysCheckBox.checked = (
			isNew
				? BX.prop.getBoolean(this._settings, 'showAlways', true)
				: this._field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways)
		);

		if (!this.isAllowedShowAlwaysCheckBox())
		{
			Dom.style(this._showAlwaysCheckBox.closest('div.ui-ctl-checkbox'), 'display', 'none');
		}
		//endregion

		return this._optionWrapper;
	}

	isAllowedMultipleCheckBox()
	{
		if (BX.prop.get(this?._field?.getSchemeElement()?._settings, 'allowedMultiple', true) === false)
		{
			return false;
		}
		if (this._typeId === 'boolean')
		{
			return false;
		}
		const isEnabledOfferTree = this?._field?.getSchemeElement()?._settings?.isEnabledOfferTree;
		const isMultiple = this?._field?.getSchemeElement()?._settings?.multiple;

		return !isEnabledOfferTree || isMultiple;
	}

	isAllowedShowAlwaysCheckBox()
	{
		return true;
	}

	getInputTitle()
	{
		const manager = this._editor.getUserFieldManager();
		return this._field ? this._field.getTitle() : manager.getDefaultFieldLabel(this._typeId);
	}
	getErrorContainer()
	{
		this._errorContainer = Tag.render`
			<div class="ui-entity-editor-content-block"></div>
		`;
		return this._errorContainer;
	}

	getEnumerationContainer()
	{
		const enumWrapper = Tag.render`
			<div class="ui-entity-editor-content-block">
				<div class="ui-entity-editor-block-title">
					<span class="ui-entity-editor-block-title-text">${Loc.getMessage("UI_ENTITY_EDITOR_UF_ENUM_ITEMS")}</span>
				</div>
			</div>
		`;

		this._enumItemContainer = Tag.render`
				<div class="ui-entity-editor-content-block"></div>
			`;
		Dom.append(this._enumItemContainer, enumWrapper);

		const addButton = Tag.render`
				<div class="ui-entity-card-content-add-field">
					${Loc.getMessage("UI_ENTITY_EDITOR_ADD")}
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
						FILE_ID: enumFields.IMAGE || null,
						IMAGE_SRC: enumFields.IMAGE_SRC || '',
						TEXT: enumFields.TEXT || '',
						ID: enumFields.VALUE
					});
				}
			});
		}

		const lastItem = this.createEnumerationItem();

		this._draggable = new BX.UI.DragAndDrop.Draggable({
			container: this._enumItemContainer,
			draggable: '.ui-ctl-row',
			dragElement: '.ui-ctl-row-draggable',
			type: BX.UI.DragAndDrop.Draggable.CLONE
		});

		lastItem.focus();
		this.initItemClickHandlers();
		return enumWrapper;
	}

	onEnumerationItemAddButtonClick()
	{
		this.unbindItemClickHandlers();
		this.createEnumerationItem().focus();
		this.bindLastItemClickHandler();
	}

	onEnumerationItemClick()
	{
		this.unbindItemClickHandlers();
		this.createEnumerationItem();
		this.bindLastItemClickHandler();
	}

	initItemClickHandlers()
	{
		this.unbindItemClickHandlers();
		this.bindLastItemClickHandler();
	}

	unbindItemClickHandlers()
	{
		this._enumItems.forEach(item => Event.unbindAll(item._labelInput, 'click'));
	}

	bindLastItemClickHandler()
	{
		const lastItem = this._enumItems[this._enumItems.length - 1];
		if (lastItem)
		{
			Event.bindOnce(lastItem._labelInput, 'click', this.onEnumerationItemClick.bind(this));
		}
	}

	createEnumerationItem(data)
	{
		let item = null;
		if (this._typeId === 'directory')
		{
			item = IblockDirectoryFieldItem.create(
				"",
				{
					configurator: this,
					container: this._enumItemContainer,
					data: data
				}
			);
		}
		else
		{
			item = BX.UI.EntityEditorUserFieldListItem.create(
				"",
				{
					configurator: this,
					container: this._enumItemContainer,
					data: data
				}
			);
		}

		this._enumItems.push(item);
		item.layout();
		return item;
	}

	removeEnumerationItem(item)
	{
		for (let i = 0, length = this._enumItems.length; i < length; i++)
		{
			if (this._enumItems[i] === item)
			{
				this._enumItems[i].clearLayout();
				this._enumItems.splice(i, 1);
				this.initItemClickHandlers();
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

				if (Type.isNil(enumData['ID']))
				{
					enumData['ID'] = Text.getRandom();
				}

				let sortIndex = -1;
				if (this._draggable)
				{
					sortIndex = this._draggable.getElementIndex(enumItem.getDraggableContainer());
				}

				sortIndex = (sortIndex >= 0) ? sortIndex : params['enumeration'].length;
				enumData["SORT"] = (Text.toNumber(sortIndex) + 1) * 100;
				params['enumeration'].push(enumData);
			});
		}
		if (this._typeId === 'directory')
		{
			params['enumeration'] = [];
			this._enumItems.forEach(enumItem => {
				if (!(enumItem instanceof IblockDirectoryFieldItem))
				{
					return;
				}

				const enumData = enumItem.prepareData();
				if (!enumData)
				{
					return;
				}

				enumData['SORT'] = (params['enumeration'].length + 1) * 100;
				params['enumeration'].push(enumData);
			});
		}
		else if (this._typeId === 'datetime' || this._typeId === 'multidatetime')
		{
			params['enableTime'] = this._isTimeEnabledCheckBox.checked;
		}

		if (this._field)
		{
			if (this._isMultipleCheckBox)
			{
				params["multiple"] = this._isMultipleCheckBox.checked;
			}
		}
		else
		{
			if(this._typeId === "boolean")
			{
				params["multiple"] = false;
			}
			else if(this._isMultipleCheckBox)
			{
				params["multiple"] = this._isMultipleCheckBox.checked;
			}
		}

		if (this._isPublic)
		{
			params["isPublic"] = this._isPublic.checked;
		}

		if (this._typeId === 'custom')
		{
			params['userType'] = this._field?.getSchemeElement()?._settings?.settings['USER_TYPE'];
		}

		return params;
	}

	getMultipleCheckBox()
	{
		const checkBox = this.createOption({caption: BX.message("UI_ENTITY_EDITOR_UF_MULTIPLE_FIELD")});
		if (
			this._field instanceof BX.UI.EntityEditorMultiText
			|| this._field instanceof BX.UI.EntityEditorMultiNumber
			|| this._field instanceof BX.UI.EntityEditorMultiList
			|| this._field instanceof BX.UI.EntityEditorMultiDatetime
			|| this._field instanceof BX.UI.EntityEditorMultiMoney
			|| (this._field instanceof BX.UI.EntityEditorCustom && this._field.getSchemeElement()._settings.multiple)
		)
		{
			checkBox.checked = true;
		}

		return checkBox;
	}

	onSaveButtonClick()
	{
		if(this._isLocked)
		{
			return;
		}

		if(this._mandatoryConfigurator)
		{
			if(this._mandatoryConfigurator.isChanged())
			{
				this._mandatoryConfigurator.acceptChanges();
			}
			this._mandatoryConfigurator.close();
		}

		const params = this.prepareSaveParams();

		if (this._field instanceof BX.UI.EntityEditorCustom)
		{
			this._field.getSchemeElement().mergeSettings({multiple: params.multiple});

			const modes = ['edit', 'view'];
			for (let i = 0; i < modes.length; i++)
			{
				const htmlListName = BX.prop.getString(this._field.getSchemeElement().getData(), modes[i] + 'List', null);
				const htmlList = BX.prop.getObject(this._field.getModel().getData(), htmlListName, null);

				if (htmlList !== null)
				{
					const newHtml = params.multiple ? htmlList.MULTIPLE : htmlList.SINGLE;
					const htmlName = BX.prop.getString(this._field.getSchemeElement().getData(), modes[i], null);

					if (BX.prop.getString(this._field.getModel().getData(), htmlName, null) !== null)
					{
						this._field.getModel().setField(htmlName, newHtml);
						this._field.getModel().setInitFieldValue(htmlName, newHtml);
						if (modes[i] === 'view')
						{
							if (newHtml === '')
							{
								Dom.clean(this._field.getContentWrapper());
								Dom.append(
									Tag.render`
										<div class="ui-entity-editor-content-block-text">
											${Loc.getMessage("UI_ENTITY_EDITOR_FIELD_EMPTY")}							
										</div>
									`,
									this._field.getContentWrapper()
								);
							}
							else
							{
								this._field.getContentWrapper().innerHTML = newHtml;
							}
						}
					}
				}
			}
		}

		this._field?.getSchemeElement()?.setDataParam('isPublic', params['isPublic']);

		BX.onCustomEvent(this, "onSave", [ this, params]);
	}

	getIsRequiredCheckBox()
	{
		let checkBox;
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
			checkBox = this.createOption({caption: Loc.getMessage("UI_ENTITY_EDITOR_UF_REQUIRED_FIELD")});
			checkBox.checked = this._field && this._field.isRequired();
		}
		return checkBox;
	}

	getIsTimeEnabledCheckBox()
	{
		const checkBox = this.createOption({caption:  Loc.getMessage("UI_ENTITY_EDITOR_UF_ENABLE_TIME")});
		checkBox.checked = this._field && this._field.isTimeEnabled();
		return checkBox;
	}

	getIsPublicCheckBox()
	{
		const checkBox = this.createOption({caption: Loc.getMessage("CATALOG_ENTITY_EDITOR_IS_PUBLIC_PROPERTY")});
		if (!this._field)
		{
			checkBox.checked = true;
		}
		else
		{
			checkBox.checked = this._field.getSchemeElement() && BX.prop.get(this._field.getSchemeElement().getData(), "isPublic", true);
		}
		return checkBox;
	}
}

Reflection.namespace('BX.Catalog').IblockFieldConfigurator = IblockFieldConfigurator;