import { Cache, Dom, Event, Runtime, Tag, Text, Type } from 'main.core';
import { Loc } from 'landing.loc';
import { Env } from 'landing.env';
import { BaseField } from 'landing.ui.field.basefield';
import type { SourceItem } from './internal/prepare-sources';
import prepareSources from './internal/prepare-sources';
import prepareValue from './internal/prepare-value';
import './css/style.css';
import getFilterStub from './internal/filter-stub';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class SourceField extends BaseField
{
	constructor(options)
	{
		super(options);
		Dom.addClass(this.layout, 'landing-ui-field-source');

		this.items = prepareSources(options.items, options.stubText);
		this.value = prepareValue(options.value, this.items);
		this.cache = new Cache.MemoryCache();
		this.linkType = options.linkType ?? '';
		this.onButtonClick = this.onButtonClick.bind(this);
		this.onMenuItemClick = this.onMenuItemClick.bind(this);
		this.onSliderMessage = this.onSliderMessage.bind(this);
		this.onPlaceholderRemoveClick = this.onPlaceholderRemoveClick.bind(this);
		this.onPlaceholderClick = this.onPlaceholderClick.bind(this);

		Dom.append(this.getGrid(), this.layout);
		if (!options.hideSort)
		{
			Dom.append(this.getSortByField().layout, this.layout);
			Dom.append(this.getSortOrderField().layout, this.layout);
		}

		if (options.useLink)
		{
			Dom.append(this.getLink(), this.layout);
		}

		if (options.showValueInHeader && options.showValueInHeader !== false)
		{
			Dom.append(this.getValueLayoutWrapper(), this.header);
		}

		this.setValue(this.value);

		window.top.BX.addCustomEvent('SidePanel.Slider:onMessage', this.onSliderMessage);
	}

	getItem(value: string): ?SourceItem
	{
		return this.items.find((item) => {
			return item.value === value;
		});
	}

	getButtonField(): ?BX.Landing.UI.Button.BaseButton
	{
		return this.getMenuItems().length > 1
			? this.cache.remember('buttonField', () => {
				new BX.Landing.UI.Button.BaseButton('dropdown_button', {
					text: Loc.getMessage('LINK_URL_SUGGESTS_SELECT'),
					className: 'landing-ui-button-select-link',
					onClick: this.onButtonClick,
				});
			})
			: null;
	}

	getSortByField(): BX.Landing.UI.Field.DropdownInline
	{
		return this.cache.remember('sortByField', () => {
			const item = this.getItem(this.value.source);
			return new BX.Landing.UI.Field.DropdownInline({
				title: Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_SORT_TITLE').toLowerCase(),
				items: item.sort.items,
				content: this.value.sort.by,
			});
		});
	}

	getSortOrderField(): BX.Landing.UI.Field.DropdownInline
	{
		return this.cache.remember('sortOrderField', () => {
			return new BX.Landing.UI.Field.DropdownInline({
				title: ', ',
				items: [
					{name: Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_SORT_DESC'), value: 'DESC'},
					{name: Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_SORT_ASC'), value: 'ASC'},
				],
				content: this.value.sort.order,
			});
		});
	}

	getValueLayout(): HTMLSpanElement
	{
		return this.cache.remember('valueLayout', () => {
			return Tag.render`<span></span>`;
		});
	}

	getValueLayoutWrapper(): HTMLSpanElement
	{
		return this.cache.remember('valueLayoutWrapper', () => {
			return Tag.render`<span>&nbsp;(${this.getValueLayout()})</span>`;
		});
	}

	getInput(): HTMLDivElement
	{
		return this.input;
	}

	getGrid(): HTMLDivElement
	{
		return this.cache.remember('grid', () => {
			return Tag.render`
				<div class="landing-ui-field-source-grid">
					<div class="landing-ui-field-source-grid-left">${this.getInput()}</div>
					<div class="landing-ui-field-source-grid-right">${this.getButtonField()?.layout}</div>
				</div>
			`;
		});
	}

	onButtonClick()
	{
		this.getMenu().show();
	}

	onMenuItemClick(item)
	{
		const value = prepareValue({source: item.value}, this.items);
		this.setValue(value);
		this.getMenu().close();
		this.openSourceFilterSlider(item.url);
	}

	onPlaceholderClick(item, event: MouseEvent)
	{
		event.preventDefault();
		this.openSourceFilterSlider(item.url);
	}

	// eslint-disable-next-line class-methods-use-this
	onPlaceholderRemoveClick(item, event: MouseEvent)
	{
		event.preventDefault();
		event.stopPropagation();

		const {currentTarget} = event;

		if (Type.isDomNode(currentTarget))
		{
			const placeholder = currentTarget
				.closest('.landing-ui-field-source-placeholder');

			if (placeholder)
			{
				Dom.remove(placeholder);
			}

			if (this.getPlaceholders().length <= 0)
			{
				const value = prepareValue({source: this.getValue().source}, this.items);
				this.prepareLink(value);
				this.value = value;
				this.setFilter(value.filter);
			}

			this.value.filter = this.getPlaceholders().map((placeholderNode) => {
				return Dom.attr(placeholderNode, 'data-item');
			});
		}
	}

	onSliderMessage(event)
	{
		if (event.getEventId() === 'save')
		{
			const sourceValue = {...this.getValue(), filter: event.getData().filter};
			const value = prepareValue(sourceValue, this.items);
			this.prepareLink(value);

			this.value = value;
			this.setFilter(value.filter);
		}
	}

	openSourceFilterSlider(url)
	{
		if (Type.isString(url))
		{
			const siteId = Env.getInstance().getOptions().site_id;

			BX.SidePanel.Instance.open(url, {
				cacheable: false,
				requestMethod: 'post',
				requestParams: {
					filter: this.getValue().filter,
					landingParams: {
						siteId,
					},
				},
			});
		}
	}

	getMenuItems(): Array<{id: string, text: string, onclick: () => {}}>
	{
		return this.cache.remember('menuItems', () => {
			return this.items.map((item) => {
				return {
					id: item.value,
					text: Text.encode(item.name),
					onclick: () => this.onMenuItemClick(item),
				};
			});
		});
	}

	getMenu(): BX.PopupMenuWindow
	{
		return this.cache.remember('menu', () => {
			const form = this.input.closest(
				'.landing-ui-field-source',
			);

			const menu = new BX.PopupMenuWindow({
				id: `${this.selector}_${Text.getRandom()}`,
				bindElement: this.getButtonField()?.layout,
				autoHide: true,
				items: this.getMenuItems(),
				className: 'landing-ui-field-source-popup',
				events: {
					onPopupShow: () => {
						const buttonPosition = Dom.getRelativePosition(
							this.getButtonField()?.layout,
							form,
						);

						const offsetX = 0;
						const popupWindowTop = buttonPosition.bottom;

						requestAnimationFrame(() => {
							Dom.style(menu.popupWindow.popupContainer, {
								top: `${popupWindowTop}px`,
								left: 'auto',
								right: `${offsetX}px`,
							});
						});
					},
				},
			});

			Dom.append(menu.popupWindow.popupContainer, form);

			return menu;
		});
	}

	addPlaceholder(options)
	{
		const placeholder = Tag.render`
			<div class="landing-ui-field-source-placeholder">
				<span class="landing-ui-field-source-placeholder-text">${Text.encode(options.name)}</span>
			</div>
		`;

		Dom.attr(placeholder, {
			'data-item': options,
			title: options.name,
		});

		if (!options.url)
		{
			Dom.addClass(placeholder.firstElementChild, 'landing-ui-field-source-placeholder-text-plain');
		}

		if (options.url)
		{
			if (options.useLink)
			{
				const removeButton = Tag.render`<span class="landing-ui-field-source-placeholder-remove"></span>`;
				Dom.append(removeButton, placeholder);
				Event.bind(removeButton, 'click', this.onPlaceholderRemoveClick.bind(this, options));
			}
			Event.bind(placeholder, 'click', this.onPlaceholderClick.bind(this, options));
		}

		Dom.append(placeholder, this.input);
	}

	getPlaceholders(): Array<HTMLElement>
	{
		return [...this.input.querySelectorAll('.landing-ui-field-source-placeholder')];
	}

	setFilter(filter: Array<{key: string, name: string, value: any, url: ?string}>)
	{
		Dom.clean(this.getInput());

		filter.forEach((field) => {
			this.addPlaceholder(field);
		});
	}

	setSource({value, name}: SourceItem)
	{
		const valueLayout = this.getValueLayout();
		Dom.attr(valueLayout, 'data-value', value);
		valueLayout.innerText = name;
	}

	setSortByItems(items: {name: string, value: string})
	{
		if (Type.isArray(items))
		{
			this.getSortByField().setItems(items);
		}
	}

	setValue(value, preventEvent)
	{
		const preparedValue = prepareValue(value, this.items);
		const sourceItem = this.getItem(value.source);

		if (Type.isPlainObject(sourceItem))
		{
			if (
				preparedValue.source !== this.value.source
				|| this.getPlaceholders().length <= 0
			)
			{
				this.value = Runtime.clone(preparedValue);

				this.setFilter(preparedValue.filter);
				this.setSource(sourceItem);

				const sortByField = this.getSortByField();
				sortByField.setItems(sourceItem.sort.items);
				sortByField.setValue(preparedValue.sort.by);

				const orderByField = this.getSortOrderField();
				orderByField.setValue(preparedValue.sort.order);

				if (!preventEvent)
				{
					this.onValueChangeHandler(this);
				}
			}
		}
	}

	getValue()
	{
		const value = Runtime.clone(this.value);

		value.filter = value.filter
			.filter((field) => {
				return field.key !== getFilterStub().key;
			})
			.map((field) => {
				Reflect.deleteProperty(field, 'url');
				return field;
			});

		value.sort.by = this.getSortByField().getValue();
		value.sort.order = this.getSortOrderField().getValue();

		return value;
	}

	getCurrentSource(): SourceItem
	{
		const value = this.getValue();
		return this.getItem(value.source);
	}

	isDetailPageAllowed()
	{
		const source = this.getCurrentSource();

		return (
			!Type.isPlainObject(source)
			|| !Type.isPlainObject(source.settings)
			|| source.settings.detailPage !== false
		);
	}

	getLink(): HTMLDivElement
	{
		let href = '#';
		let hrefAttr = '';
		const value = this.value?.filter?.[0]?.value ?? null;
		if (this.linkType === 'group' && value && value.startsWith('SG'))
		{
			href = `/workgroups/group/${value.slice(2)}/`;
			hrefAttr = `href="${href}"`;
		}

		return this.cache.remember('linkLayout', () => {
			const text = BX.Landing.Loc.getMessage('LANDING_SOURCEFIELD_LINK_TEXT');

			return Tag.render`
				<div class="landing-ui-field-link-container">
					<a ${hrefAttr} target="_blank" class="landing-ui-field-link">
						${text}
					</a>
				</div>
			`;
		});
	}

	prepareLink(value)
	{
		const linkElement = this.layout.querySelector('.landing-ui-field-link');

		if (linkElement && this.linkType === 'group')
		{
			const newValue = value?.filter?.[0]?.value ?? null;
			if (newValue !== null && newValue.startsWith('SG'))
			{
				linkElement.href = `/workgroups/group/${newValue.slice(2)}/`;
			}
			else
			{
				linkElement.removeAttribute('href');
			}
		}
	}
}