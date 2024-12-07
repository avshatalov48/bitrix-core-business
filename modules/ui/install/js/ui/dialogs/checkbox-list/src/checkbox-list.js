import 'checkbox-list.css';
import { Dom, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup, PopupOptions } from 'main.popup';
import 'ui.design-tokens';
import { BitrixVue, VueCreateAppResult } from 'ui.vue3';

import { Content } from './content';

export type CheckboxListEvents = {
	onApply?: Function,
	onCancel?: Function,
	onDefault?: Function,
}

export type CheckboxListOptions = {
	context?: CheckboxListContext;
	lang?: CheckboxListLang;
	compactField?: CheckboxListCompactField;
	sections?: CheckboxListSection[];
	categories: CheckboxListCategory[];
	options: CheckboxListOption[];
	columnCount?: number;
	popupOptions?: PopupOptions;
	events: CheckboxListEvents;
	params?: CheckboxListParams;
	customFooterElements?: Object[];
	closeAfterApply?: boolean;
}

export type CheckboxListContext = {
	parentType: string;
}

export type CheckboxListLang = {
	title: string,
	switcher: string,
	placeholder: string,
	defaultBtn: string,
	acceptBtn: string,
	cancelBtn: string,
	selectAllBtn: string,
	deselectAllBtn: string,
}

export type CheckboxListSection = {
	key: string,
	title: string,
	value: boolean,
}

export type CheckboxListCategory = {
	title: string,
	sectionKey: string,
	key: string,
}

export type CheckboxListOption = {
	title: string,
	value: boolean,
	categoryKey: string,
	defaultValue: boolean,
	id: string,
	locked?: boolean,
	data?: Object,
}

export type CheckboxListCompactField = {
	value: boolean,
	defaultValue: boolean,
}

export type CheckboxListParams = {
	useSearch?: boolean;
	useSectioning?: boolean;
	closeAfterApply?: boolean;
	showBackToDefaultSettings?: boolean;
	isEditableOptionsTitle?: boolean;
	destroyPopupAfterClose?: boolean;
}

export class CheckboxList extends EventEmitter
{
	layoutApp: ?VueCreateAppResult = null;
	layoutComponent: ?Object = null;

	constructor(options: CheckboxListOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Dialogs.CheckboxList');
		this.subscribeFromOptions(options.events);

		this.context = Type.isPlainObject(options.context) ? options.context : null;
		this.compactField = Type.isPlainObject(options.compactField) ? options.compactField : null;
		this.sections = Type.isArray(options.sections) ? options.sections : null;
		this.lang = Type.isPlainObject(options.lang) ? options.lang : {};
		this.popup = null;
		this.columnCount = Type.isNumber(options.columnCount) ? options.columnCount : 4;
		this.popupOptions = Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
		this.params = Type.isPlainObject(options.params) ? options.params : {};

		const useSectioning = (this.params.useSectioning ?? true);
		if (useSectioning && !Type.isArray(options.categories))
		{
			throw new Error('CheckboxList: "categories" parameter is required.');
		}
		this.categories = options.categories;

		if (useSectioning && !Type.isArray(options.options))
		{
			throw new Error('CheckboxList: "options" parameter is required.');
		}
		this.options = options.options;

		this.customFooterElements = Type.isArrayFilled(options.customFooterElements) ? options.customFooterElements : [];
		this.closeAfterApply = Type.isBoolean(options.closeAfterApply) ? options.closeAfterApply : true;
	}

	getPopup(): Popup
	{
		const container = Dom.create('div');
		Dom.addClass(container, 'ui-checkbox-list__app-container');

		if (!this.popup)
		{
			const { lang, layoutComponent, popupOptions } = this;

			const { innerWidth, innerHeight } = window;

			this.popup = new Popup({
				className: 'ui-checkbox-list-popup',
				width: 997,
				maxWidth: Math.round(innerWidth * 0.9),
				overlay: true,
				autoHide: true,
				minHeight: 200,
				maxHeight: Math.round(innerHeight * 0.9),
				borderRadius: 20,
				contentPadding: 0,
				contentBackground: 'transparent',
				animation: 'fading-slide',
				titleBar: lang.title,
				content: container,
				closeIcon: true,
				closeByEsc: true,
				...popupOptions,
				events: {
					onPopupClose: () => layoutComponent?.restoreOptionValues(),
				},
			});

			const {
				compactField,
				customFooterElements,
				sections,
				categories,
				options,
				popup,
				params,
				context,
			} = this;

			this.layoutApp = BitrixVue.createApp(
				Content,
				{
					compactField,
					customFooterElements,
					lang,
					sections,
					categories,
					options,
					popup,
					columnCount: this.#getColumnCount(),
					params,
					context,
					dialog: this,
				},
			);

			// eslint-disable-next-line unicorn/consistent-destructuring
			this.layoutComponent = this.layoutApp.mount(container);
		}

		return this.popup;
	}

	#getColumnCount(): number
	{
		let { columnCount } = this;
		const { innerWidth } = window;

		if (innerWidth <= 480)
		{
			columnCount = 1;
		}
		else if (innerWidth <= 768 && columnCount > 2)
		{
			columnCount = 2;
		}

		return columnCount;
	}

	show(): void
	{
		this.getPopup().show();
		this.#getLayoutComponent().setFocusToSearchInput();
	}

	hide(): void
	{
		this.layoutComponent?.destroyOrClosePopup();
	}

	destroy(): void
	{
		if (!this.layoutApp)
		{
			return;
		}

		this.hide();

		this.layoutApp.unmount();
		this.layoutComponent = null;
		this.popup = null;
	}

	isShown(): boolean
	{
		return this.popup && this.popup.isShown();
	}

	getOptions(): []
	{
		return this.#getLayoutComponent().getOptions();
	}

	getSelectedOptions(): ?string[]
	{
		return this.#getLayoutComponent().getCheckedOptionsId();
	}

	handleSwitcherToggled(id: string): void
	{
		return this.#getLayoutComponent().handleSwitcherToggled(id);
	}

	handleOptionToggled(id: string): void
	{
		return this.#getLayoutComponent().toggleOption(id);
	}

	saveColumns(columnIds: [], callback: Function): void
	{
		if (!Type.isArrayFilled(columnIds))
		{
			return;
		}

		columnIds.forEach((id) => this.selectOption(id));

		this.apply();
	}

	selectOption(id: string, value: boolean): void
	{
		// to maintain backward compatibility without creating dependencies on main within the ticket #187991
		// @todo remove later and set default value = true in the function signature
		if (value !== false)
		{
			// eslint-disable-next-line no-param-reassign
			value = true;
		}

		this.#getLayoutComponent().select(id, value);
	}

	apply(): void
	{
		this.#getLayoutComponent().apply();
	}

	#getLayoutComponent(): Object
	{
		if (!this.layoutComponent)
		{
			void this.getPopup();
		}

		return this.layoutComponent;
	}
}
