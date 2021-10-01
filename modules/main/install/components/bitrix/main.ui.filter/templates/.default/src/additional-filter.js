import {EventEmitter} from 'main.core.events';
import {Cache, Tag, Event, Dom, Loc, Type} from 'main.core';
import {Menu} from 'main.popup';

/**
 * @memberOf BX.Filter
 */
export class AdditionalFilter extends EventEmitter
{
	static Type = {
		IS_EMPTY: 'isEmpty',
		HAS_ANY_VALUE: 'hasAnyValue',
	}

	static getInstance(): AdditionalFilter
	{
		return AdditionalFilter.cache.remember('instance', () => {
			return new AdditionalFilter();
		});
	}

	static fetchAdditionalFilter(name: string, fields: {[key: string]: any}): ?string
	{
		if (Type.isStringFilled(name) && Type.isPlainObject(fields))
		{
			if (`${name}_${AdditionalFilter.Type.IS_EMPTY}` in fields)
			{
				return AdditionalFilter.Type.IS_EMPTY;
			}

			if (`${name}_${AdditionalFilter.Type.HAS_ANY_VALUE}` in fields)
			{
				return AdditionalFilter.Type.HAS_ANY_VALUE;
			}
		}

		return null;
	}

	static cache = new Cache.MemoryCache();
	cache = new Cache.MemoryCache();

	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Main.Filter.AdditionalFilter');
		this.options = {...options};

		Event.bind(document, 'click', this.onDocumentClick.bind(this));
	}

	getAdditionalFilterMenu(): Menu
	{
		return this.cache.remember('menu', () => {
			return new Menu({
				id: 'additional_filter_menu',
				autoHide: false,
				items: [
					{
						id: 'isEmpty',
						text: Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_MENU_IS_EMPTY'),
						onclick: this.onAdditionalFilterMenuItemClick.bind(
							this,
							AdditionalFilter.Type.IS_EMPTY,
						),
					},
					{
						id: 'hasAnyValue',
						text: Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_MENU_HAS_ANY_VALUE'),
						onclick: this.onAdditionalFilterMenuItemClick.bind(
							this,
							AdditionalFilter.Type.HAS_ANY_VALUE,
						),
					},
					{
						id: 'delimiter',
						delimiter: true,
					},
					{
						id: 'helper',
						html:  Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_HOW') + '<span class="ui-hint"><span class="ui-hint-icon"></span></span>',
						onclick: function() {
							if(top.BX.Helper)
							{
								top.BX.Helper.show("redirect=detail&code=14006190");
								event.preventDefault();
							}
						},
					},
				],
			});
		});
	}

	onAdditionalFilterMenuItemClick(typeId: $Values<AdditionalFilter.Type>)
	{
		const node = this.getCurrentFieldNode();
		this.initAdditionalFilter(node, typeId);
	}

	onDocumentClick()
	{
		this.getAdditionalFilterMenu().close();
	}

	setCurrentFieldId(fieldId: string)
	{
		this.cache.set('currentFieldId', fieldId);
	}

	getCurrentFieldId(): string
	{
		return this.cache.get('currentFieldId', '');
	}

	setCurrentFieldNode(node: HTMLDivElement)
	{
		this.cache.set('currentFieldNode', node);
	}

	getCurrentFieldNode(): HTMLDivElement
	{
		return this.cache.get('currentFieldNode');
	}

	onAdditionalFilterButtonClick(fieldId: string, event: MouseEvent)
	{
		event.stopPropagation();
		const {currentTarget} = event;

		this.setCurrentFieldId(fieldId);
		this.setCurrentFieldNode(currentTarget.parentElement);

		const menu = this.getAdditionalFilterMenu();
		const allowedItems = String(Dom.attr(currentTarget, 'data-allowed-types')).split(',');
		menu.getMenuItems().forEach((menuItem) => {
			let menuItemId = menuItem.getId();
			if (allowedItems.includes(menuItemId) || (menuItemId === 'helper') || (menuItemId === 'delimiter'))
			{
				Dom.removeClass(menuItem.layout.item, 'main-ui-disable');
			}
			else
			{
				Dom.addClass(menuItem.layout.item, 'main-ui-disable');
			}
		});

		if (menu.getPopupWindow().isShown())
		{
			if (menu.getPopupWindow().bindElement !== currentTarget)
			{
				menu.getPopupWindow().setBindElement(currentTarget);
				menu.getPopupWindow().adjustPosition();
			}
			else
			{
				menu.close();
			}
		}
		else
		{
			menu.getPopupWindow().setBindElement(currentTarget);
			menu.show();
		}
	}

	getAdditionalFilterButton({fieldId, enabled}): HTMLDivElement
	{
		return this.cache.remember(`field_${fieldId}`, () => {
			const disabled = !Type.isArrayFilled(enabled) && enabled !== true;
			const allowedTypes = (() => {
				if (Type.isArrayFilled(enabled))
				{
					return enabled.join(',');
				}

				if (!disabled)
				{
					return [
						AdditionalFilter.Type.IS_EMPTY,
						AdditionalFilter.Type.HAS_ANY_VALUE,
					].join(',');
				}

				return '';
			})();

			return Tag.render`
				<span 
					class="ui-icon ui-icon-service-light-other main-ui-filter-additional-filters-button${disabled ? ' main-ui-disable' : ''}"
					onclick="${this.onAdditionalFilterButtonClick.bind(this, fieldId)}"
					data-allowed-types="${allowedTypes}"
				>
					<i></i>
				</span>
			`;
		});
	}

	initAdditionalFilter(fieldNode: HTMLDivElement, typeId: $Values<AdditionalFilter.Type>)
	{
		let currentFieldId = this.getCurrentFieldId();
		if (currentFieldId === '')
		{
			currentFieldId = fieldNode.attributes[1].value;
		}
		const placeholder = this.getAdditionalFilterPlaceholderField(currentFieldId, typeId);

		Dom.addClass(fieldNode, 'main-ui-filter-field-with-additional-filter');

		const currentPlaceholder = fieldNode.querySelector('.main-ui-filter-additional-filter-placeholder');
		if (currentPlaceholder)
		{
			Dom.replace(currentPlaceholder, placeholder);
		}
		else
		{
			Dom.append(placeholder, fieldNode);
		}
	}

	restoreField(fieldNode: HTMLDivElement)
	{
		if (Type.isDomNode(fieldNode))
		{
			const placeholder = fieldNode.querySelector('.main-ui-filter-additional-filter-placeholder');
			if (placeholder)
			{
				Dom.remove(placeholder);
			}

			Dom.removeClass(fieldNode, 'main-ui-filter-field-with-additional-filter');
		}
	}

	getAdditionalFilterPlaceholderField(fieldId: string, typeId: $Values<AdditionalFilter.Type>): HTMLDivElement
	{
		return this.cache.remember(`placeholder_${fieldId}_${typeId}`, () => {
			const message = (() => {
				if (typeId === AdditionalFilter.Type.HAS_ANY_VALUE)
				{
					return Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_HAS_ANY_VALUE');
				}

				return Loc.getMessage('MAIN_UI_FILTER__ADDITIONAL_FILTER_PLACEHOLDER_IS_EMPTY');
			})();

			const onRemoveClick = (event: MouseEvent) => {
				this.restoreField(
					event.currentTarget.closest('.main-ui-filter-field-with-additional-filter'),
				);
			};

			return Tag.render`
				<div class="main-ui-control main-ui-filter-additional-filter-placeholder" data-type="${typeId}">
					<div class="main-ui-square">
						<div class="main-ui-square-item">${message}</div>
						<div class="main-ui-item-icon main-ui-square-delete" onclick="${onRemoveClick}"></div>
					</div>
				</div>
			`;
		});
	}

	getFilter(fieldNode: HTMLDivElement): ?{[key: string]: any}
	{
		if (Type.isDomNode(fieldNode))
		{
			const placeholder = fieldNode.querySelector('.main-ui-filter-additional-filter-placeholder');
			if (Type.isDomNode(placeholder))
			{
				const type = Dom.attr(placeholder, 'data-type');
				const fieldId = Dom.attr(fieldNode, 'data-name');

				return {[`${fieldId}_${type}`]: 'y'};
			}
		}

		return null;
	}
}