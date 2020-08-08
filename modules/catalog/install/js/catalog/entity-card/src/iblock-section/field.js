import './field.css'
import {ajax, Dom, Event, Loc, Runtime, Tag, Text} from 'main.core'
import {type BaseEvent, EventEmitter} from 'main.core.events'

export default class IblockSectionField extends BX.UI.EntityEditorField
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);

		this.innerWrapper = null;
		this.tileSelector = null;
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

		this.ensureWrapperCreated({classNames: ['catalog-entity-editor-content-block-field-iblock-section']});
		this.adjustWrapper();

		if (this.isNeedToDisplay())
		{
			this._wrapper.appendChild(this.createTitleNode(this.getTitle()));

			if (this._mode === BX.UI.EntityEditorMode.edit)
			{
				this.drawEditMode();
			}
			else
			{
				this.drawViewMode();
			}

			if (this.isContextMenuEnabled())
			{
				this._wrapper.appendChild(this.createContextMenuButton());
			}
		}

		this.registerLayout(options);
		this._hasLayout = true;
	}

	drawEditMode()
	{
		this.defaultInput = Tag.render`<input type="hidden" name="${this.getName()}[]" value="0">`;
		this._wrapper.appendChild(this.defaultInput);

		this.innerWrapper = Tag.render`<div class="ui-entity-editor-content-block"></div>`;
		this._wrapper.appendChild(this.innerWrapper);

		ajax.runComponentAction(
			'bitrix:catalog.productcard.iblocksectionfield',
			'lazyLoad',
			{
				mode: 'ajax',
				data: {
					iblockId: this.getIblockId(),
					productId: this.getProductId(),
					selectedSectionIds: this.getValue()
				}
			}
		)
			.then(this.renderFromResponse.bind(this))
			.catch(response => {
				throw new Error(response.errors.join("\n"));
			})
		;
	}

	renderFromResponse(response)
	{
		if (!this._wrapper)
		{
			return;
		}

		Runtime.html(this.innerWrapper, response.data.html, {
			callback: this.initTileSelector.bind(this)
		});
	}

	initTileSelector()
	{
		if (BX.UI && BX.UI.TileSelector)
		{
			this.tileSelector = BX.UI.TileSelector.getById(this.getTileSelectorId());

			if (!this.tileSelector)
			{
				throw new Error('Tile selector `' + this.getTileSelectorId() + '` not found.');
			}
			if (this.tileSelector)
			{
				this.changeDisplay(this.tileSelector.buttonAdd, false);
			}

			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.buttonSelectFirst, this.tileSelectorSelectFirstHandler.bind(this));
			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.input, this.onInputSearch.bind(this));
			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.buttonAdd, this.onButtonAdd.bind(this));
			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.buttonSelect, this.onButtonSelect.bind(this));
			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.tileAdd, this.markAsChanged.bind(this));
			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.tileRemove, this.markAsChanged.bind(this));
			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.search, this.onInputEnd.bind(this));
			EventEmitter.subscribe(this.tileSelector, this.tileSelector.events.searcherInit, this.onSearcherInit.bind(this));
			Event.bind(this.tileSelector.input, 'blur', this.onBlur.bind(this));
			if (this.tileSelector.buttonAdd)
			{
				this.tileSelector.buttonAdd.style.top = 'auto';
				this.tileSelector.buttonAdd.style.bottom = 0;
			}
		}
	}

	changeDisplay(node, isShow)
	{
		if (!node)
		{
			return;
		}

		node.style.display = isShow ? '' : 'none';
	}

	markAsChanged(event: BaseEvent)
	{
		super.markAsChanged();
		EventEmitter.emit(this.getEditor(), 'IblockSectionField:onChange', [this, ...event.getData()]);
	}

	onBlur()
	{
		window.setTimeout(() => {
			this.tileSelector.onInputEnd()
		}, 500);
	}

	onSearcherInit(event)
	{
		const [seacher] = event.getData();
		const popup = BX.PopupWindowManager.getPopupById(seacher.id);
		if (popup)
		{
			popup.destroy();
		}
		if (seacher.categoryContainer)
		{
			seacher.categoryContainer.parentNode.removeChild(seacher.categoryContainer);
		}
	}

	onInputEnd()
	{
		this.changeDisplay(this.tileSelector.buttonAdd, false);
	}

	getTileSelectorId()
	{
		const iblockId = this.getIblockId() || 0;
		const productId = this.getProductId() || 0;

		return `catalog-iblocksectionfield-${iblockId}-${productId}`;
	}

	onButtonSelect()
	{
		if (this.tileSelector)
		{
			this.tileSelector.showSearcher();
		}
	}

	onButtonAdd()
	{
		if (!BX.type.isNotEmptyString(this.tileSelector.input.value))
		{
			return;
		}
		ajax.runComponentAction(
			'bitrix:catalog.productcard.iblocksectionfield',
			'addSection',
			{
				mode: 'ajax',
				data: {
					iblockId: this.getIblockId(),
					name: this.tileSelector.input.value
				}
			}
		)
			.then(this.onAddSection.bind(this))
			.catch(response => {
				throw new Error(response.errors.join("\n"));
			})
		;
	}

	onAddSection(response)
	{
		const item = this.tileSelector.searcher.addItem('all', response.data.id, response.data.name);
		this.tileSelector.searcher.onItemClick(item);
		if (this.tileSelector.searcher.popup)
		{
			this.tileSelector.searcher.popup.destroy();
			this.tileSelector.searcher.popup = null;
		}
		this.changeDisplay(item.node, false);
		this.tileSelector.onInputEnd();
		this.onInputEnd();
	}

	onInputSearch()
	{
		const name = this.tileSelector.input.value;
		var regexp = new RegExp(BX.util.escapeRegExp(name), 'i');
		if (!this.tileSelector.searcher)
		{
			return;
		}
		const filtered = this.tileSelector.searcher.items.filter((item) => regexp.test(item.name));
		if (filtered.length === 0)
		{
			this.changeDisplay(this.tileSelector.buttonAdd, true);
			this.tileSelector.searcher.hide();
		}
		else
		{
			this.changeDisplay(this.tileSelector.buttonAdd, false);
			this.tileSelector.searcher.show();
		}
	}

	tileSelectorSelectFirstHandler()
	{
		ajax.runComponentAction(
			'bitrix:catalog.productcard.iblocksectionfield',
			'getSections',
			{
				mode: 'ajax',
				data: {
					iblockId: this.getIblockId()
				}
			}
		)
			.then(response => {
				this.tileSelector.setSearcherData(response.data || []);
			})
			.catch(this.tileSelector.hideSearcher.bind(this.tileSelector))
		;
	}

	drawViewMode()
	{
		if (this.hasNoSections())
		{
			this.innerWrapper = Tag.render`
				<div class="ui-entity-editor-content-block">
					${Loc.getMessage("CATALOG_ENTITY_CARD_EMPTY_SECTION")}
				</div>
			`;
			Dom.addClass(this._wrapper, 'ui-entity-editor-content-block-click-empty');
		}
		else
		{
			const content = [];
			Object.entries(this.getSections()).forEach(([id, name]) => {
				// ui-tile-selector-item-%type%
				content.push(Tag.render`
					<span class="ui-tile-selector-item ui-tile-selector-item-readonly-yes">
						<span data-role="tile-item-name">${Text.encode(name)}</span>
					</span>
				`)
			});
			this.innerWrapper = Tag.render`
				<div class="ui-entity-editor-content-block">
					<span class="ui-tile-selector-selector-wrap readonly">
						${content}
					</span>
				</div>`
			;
		}

		this._wrapper.appendChild(this.innerWrapper);
	}

	getSections()
	{
		return this._model.getField('IBLOCK_SECTION_DATA', {});
	}

	getIblockId()
	{
		return this._model.getField('IBLOCK_ID', 0);
	}

	getProductId()
	{
		return this._model.getField('ID', 0);
	}

	hasNoSections()
	{
		const sectionIds = this.getValue();

		return sectionIds.length === 0
			|| (
				sectionIds.length === 1
				&& (sectionIds.includes('0') || sectionIds.includes(0))
			);
	}

	doClearLayout(options)
	{
		if (this.tileSelector)
		{
			EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.buttonSelect);
			EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.buttonSelectFirst);
			EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.tileAdd);
			EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.tileRemove);
			EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.searcherInit);
			EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.search);
			EventEmitter.unsubscribeAll(this.tileSelector, this.tileSelector.events.buttonAdd);
		}

		if (this.defaultInput)
		{
			Dom.clean(this.defaultInput);
			this.defaultInput = null;
		}

		if (this.innerWrapper)
		{
			Dom.clean(this.innerWrapper);
			this.innerWrapper = null;
		}

		this._hasLayout = false;
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
}