import './field.css'
import {ajax, Dom, Event, Loc, Runtime, Tag, Text, Type} from 'main.core'
import {type BaseEvent, EventEmitter} from 'main.core.events'

export default class IblockSectionField extends BX.UI.EntityEditorField
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);

		this.innerWrapper = null;
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
			callback: this.initEntitySelector.bind(this)
		});
	}

	initEntitySelector()
	{
		EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, 'Item:onSelect', this.markAsChanged.bind(this));
		EventEmitter.subscribe(EventEmitter.GLOBAL_TARGET, 'Item:onDeselect', this.markAsChanged.bind(this));
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
			this.getSections().forEach((section) => {
				// ui-tile-selector-item-%type%
				let picture = '';
				if (Type.isStringFilled(section.PICTURE))
				{
					picture = Tag.render`<span class="ui-tile-selector-item-picture" style="background-image: url('${Text.encode(section.PICTURE)}');"></span>`;
				}
				content.push(Tag.render`
					<span class="ui-tile-selector-item ui-tile-selector-item-readonly-yes">
						${picture}
						<span data-role="tile-item-name">${Text.encode(section.NAME)}</span>
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