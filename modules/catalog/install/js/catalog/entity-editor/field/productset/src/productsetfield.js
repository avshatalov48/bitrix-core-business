import {Loc, Tag} from 'main.core';
import {TagSelector} from "ui.entity-selector";
import {BaseEvent} from "main.core.events";
import {Footer} from "./footer";
import {Const} from "./const";

export class ProductSetField extends BX.UI.EntityEditorField
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);

		this._input = null;
		this._inputWrapper = null;
		this.innerWrapper = null;
		this.entityList = null;
		this.tagSelector = null;
	}

	getContentWrapper()
	{
		return this.innerWrapper;
	}

	layout(options = {})
	{
		if (this._hasLayout)
		{
			return;
		}
		this.ensureWrapperCreated({});
		this.adjustWrapper();

		let title = this.getTitle();
		if (this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		this._wrapper.appendChild(this.createTitleNode(title));

		let name = this.getName();
		let value = this.getValue();

		if (!this.entityList)
		{
			this.entityList = this.getEntityListFromModel();
		}

		this._inputWrapper = Tag.render`<div></div>`;
		this._wrapper.appendChild(this._inputWrapper);

		let inputValue = '';
		if (value.length > 0)
		{
			inputValue = JSON.stringify(value);
		}

		this._input = Tag.render`<input name="${name}" type="hidden" value=""/>`;
		this._input.value = inputValue;

		this._wrapper.appendChild(this._input);

		this.innerWrapper = Tag.render`<div class="ui-entity-editor-content-block"></div>`;
		this._wrapper.appendChild(this.innerWrapper);

		if (this._mode === BX.UI.EntityEditorMode.edit)
		{
			this.getProductSelector(this.entityList);
			this.tagSelector.setReadonly(false);
			this.tagSelector.renderTo(this.innerWrapper);

			if (BX.UI.EntityEditorModeOptions.check(this._modeOptions, BX.UI.EntityEditorModeOptions.individual))
			{
				this.tagSelector.getDialog().show();
			}
		}
		else // if(this._mode === BX.UI.EntityEditorMode.view)
		{
			if (this.hasContentToDisplay())
			{
				this.getProductSelector(this.entityList, true);
				this.tagSelector.setReadonly(true);
				this.tagSelector.renderTo(this.innerWrapper);
			}
			else
			{
				const viewModeDisplay = Tag.render`<div class="ui-entity-editor-content-block-text">${Loc.getMessage('ENTITY_EDITOR_PRODUCT_SET_NOT_FILLED')}</div>`;
				this.innerWrapper.appendChild(viewModeDisplay);
			}
		}

		if (this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if (this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	}

	getProductSelector(value)
	{
		const iblockId = this.getIBlockIdFromModel();

		if (!this.tagSelector)
		{
			this.tagSelector = new TagSelector({
				textBoxWidth: '100%',
				multiple: true,
				dialogOptions: {
					context: 'catalog_document_productset',
					entities: [
						{
							id: Const.ENTITY_ID.PRODUCT_VARIATION,
							options: {
								iblockId: iblockId,
							},
						},
					],
					searchOptions: {
						allowCreateItem: false,
					},
					events: {
						'Item:onSelect': (event) => {
							this.handleUserSelectorChanges(event);
							this._changeHandler();
						},
						'Item:onDeselect': (event) => {
							this.handleUserSelectorChanges(event);
							this._changeHandler();
						},
					},
					footer: Footer,
				},
			});
		}

		if (this.tagSelector.getDialog() && value.length > 0)
		{
			const dialog = this.tagSelector.getDialog();

			value.forEach((item) => {
				dialog.addItem({
					id: item.PRODUCT_ID,
					title: item.PRODUCT_NAME,
					avatar: item.IMAGE,
					selected: true,
					entityId: Const.ENTITY_ID.PRODUCT_VARIATION,
				});
			});
		}

		return this.tagSelector;
	}

	handleUserSelectorChanges(event: BaseEvent)
	{
		this.entityList = [];
		const values = [];

		const selectedItems = event.getTarget().getSelectedItems();
		selectedItems.forEach((item) => {
			values.push({
				PRODUCT_ID: item.getId(),
				PRODUCT_TYPE: Const.TYPE.PRODUCT,
			});

			this.entityList.push({
				PRODUCT_ID: item.getId(),
				PRODUCT_TYPE: Const.TYPE.PRODUCT,
				PRODUCT_NAME: item.getTitle(),
			});
		});

		this._input.value = JSON.stringify(values);
	}

	validate(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.Catalog.Entity-editor.Field.ProductSet. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		let isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._input.value) !== "";
		if (!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	}

	hasValue()
	{
		if (this.getValue().length === 0)
		{
			return false;
		}

		return super.hasValue();
	}

	getModeSwitchType(mode)
	{
		let result = BX.UI.EntityEditorModeSwitchType.common;

		if (mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button | BX.UI.EntityEditorModeSwitchType.content;
		}

		return result;
	}

	getIBlockIdFromModel()
	{
		return this._model.getSchemeField(this._schemeElement, 'iblockId', 0);
	}

	getEntityListFromModel()
	{
		return this._model.getSchemeField(this._schemeElement, 'entityList', []);
	}

	rollback()
	{
		this.entityList = this.getEntityListFromModel();
	}

	static create(id, settings)
	{
		let self = new this(id, settings);
		self.initialize(id, settings);
		return self;
	}
}