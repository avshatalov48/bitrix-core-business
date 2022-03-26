import {ajax, Loc, Tag} from "main.core";
import {Dialog, TagSelector} from 'ui.entity-selector';

export default class Contractor extends BX.UI.EntityEditorField {
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);

		this._input = null;
		this.innerWrapper = null;
		this.currentContractorName = '';
		this.viewModeDisplay = null;
	}

	getContentWrapper()
	{
		return this.innerWrapper;
	}

	layout(options = {})
	{
		if(this._hasLayout)
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
		let data = this._schemeElement.getData();
		if (!this.currentContractorName)
		{
			this.currentContractorName = this.getContractorNameFromModel();
		}
		this._input = Tag.render`<input name="${name}" type="hidden" value="${value}"/>`;
		this._wrapper.appendChild(this._input);

		this.innerWrapper = Tag.render`<div class="ui-entity-editor-content-block"></div>`;
		this._wrapper.appendChild(this.innerWrapper);

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			let currentSelectedItems = [];
			if (value)
			{
				currentSelectedItems.push({
					id: value,
					entityId: 'contractor',
					title: this.currentContractorName,
				})
			}

			let contractorSelector = new TagSelector({
				items: currentSelectedItems,
				placeholder: Loc.getMessage('DOCUMENT_CONTRACTOR_FIELD_PLACEHOLDER'),
				textBoxWidth: '100%',
				multiple: false,
				dialogOptions: {
					context: 'catalog_document_contractors',
					entities: [
						{
							id: 'contractor',
							dynamicLoad: true,
							dynamicSearch: true,
						},
					],
					searchOptions: {
						allowCreateItem: true,
						footerOptions: {
							label: Loc.getMessage('DOCUMENT_ADD_CONTRACTOR'),
						}
					},
					events: {
						'Item:onSelect': (event) => {
							this._input.value = event.data.item.getId();
							if (this.viewModeDisplay)
							{
								this.currentContractorName = event.data.item.getTitle();
								this.viewModeDisplay.innerHTML = BX.util.htmlspecialchars(this.currentContractorName);
							}
							this._changeHandler();
						},
						'Search:onItemCreateAsync': this.createContractor.bind(this),
					},
				},
			});

			contractorSelector.renderTo(this.innerWrapper);

			if (BX.UI.EntityEditorModeOptions.check(this._modeOptions, BX.UI.EntityEditorModeOptions.individual))
			{
				contractorSelector.getDialog().show();
			}
		}
		else // if(this._mode === BX.UI.EntityEditorMode.view)
		{
			if (this.hasContentToDisplay())
			{
				this.viewModeDisplay = Tag.render`<div class="ui-entity-editor-content-block-text">${BX.util.htmlspecialchars(this.currentContractorName)}</div>`;
			}
			else
			{
				this.viewModeDisplay = Tag.render`<div class="ui-entity-editor-content-block-text">${Loc.getMessage('DOCUMENT_CONTRACTOR_NOT_FILLED')}</div>`;
			}

			this.innerWrapper.appendChild(this.viewModeDisplay);
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

	validate(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.Catalog.DocumentCard.Contractor. Invalid validation context";
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
		if (this.getValue() === '0')
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

	createContractor(event)
	{
		let {searchQuery} = event.getData();
		let companyName = searchQuery.getQuery();

		return new Promise(
			(resolve, reject) => {
				const dialog: Dialog = event.getTarget();
				const fields = {
					companyName,
				};

				dialog.showLoader();
				ajax.runAction(
					'catalog.contractor.createContractor',
					{
						data: {
							fields
						}
					}
				)
				.then(response => {
					dialog.hideLoader();
					const item = dialog.addItem({
						id: response.data.id,
						entityId: 'contractor',
						title: searchQuery.getQuery(),
						tabs: dialog.getRecentTab().getId(),
					});

					if (item)
					{
						item.select();
					}

					dialog.hide();
					resolve();
				})
				.catch(() => {
					dialog.hideLoader();
					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('DOCUMENT_ADD_CONTRACTOR_ERROR'),
					});
					dialog.hide();
					reject();
				});
			}
		);
	}

	getContractorNameFromModel()
	{
		return this._model.getSchemeField(this._schemeElement, 'contractorName', '');
	}

	rollback()
	{
		this.currentContractorName = this.getContractorNameFromModel();
	}
}
