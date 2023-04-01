import 'ui.design-tokens';
import {Type, Dom} from 'main.core';
import {Popup, PopupOptions} from 'main.popup';
import {BitrixVue} from 'ui.vue3';
import {EventEmitter} from 'main.core.events';

import {Content} from './content';
import 'checkbox-list.css';

export type CheckboxListOptions = {
	lang?: CheckboxListLang;
	compactField?: CheckboxListCompactField;
	sections?: CheckboxListSection[];
	categories: CheckboxListCategory[];
	options: CheckboxListOption[];
	columnCount?: number;
	popupOptions?: PopupOptions;
	events: {[key: string]: () => {}};
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
}

export type CheckboxListCompactField = {
	value: boolean,
	defaultValue: boolean,
}

export default class CheckboxList extends EventEmitter
{
	constructor(options: CheckboxListOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Dialogs.CheckboxList');
		this.subscribeFromOptions(options.events);

		if (!Type.isArrayFilled(options.categories))
		{
			throw new Error('CheckboxList: "categories" parameter is required.');
		}
		this.categories = options.categories;

		if (!Type.isArrayFilled(options.options))
		{
			throw new Error('CheckboxList: "options" parameter is required.');
		}
		this.options = options.options;

		this.compactField = Type.isPlainObject(options.compactField) ? options.compactField : null;
		this.sections = Type.isArray(options.sections) ? options.sections : null;
		this.lang = Type.isPlainObject(options.lang) ? options.lang : {};
		this.popup = null;
		this.columnCount = Type.isNumber(options.columnCount) ? options.columnCount : 4;
		this.popupOptions = Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	}

	getPopup(): Popup
	{
		const container = Dom.create('div');
		Dom.addClass(container, 'ui-checkbox-list__app-container');

		if(!this.popup)
		{
			this.popup = new Popup({
				className: 'ui-checkbox-list-popup',
				width: 997,
				overlay: true,
				autoHide: true,
				minHeight: 422,
				borderRadius: 20,
				contentPadding: 0,
				contentBackground: 'transparent',
				animation: 'fading-slide',
				titleBar: this.lang.title,
				content: container,
				closeIcon: true,
				closeByEsc: true,
				...this.popupOptions,
			});

			BitrixVue.createApp(Content,{
				compactField: this.compactField,
				lang: this.lang,
				sections: this.sections,
				categories: this.categories,
				options: this.options,
				popup: this.popup,
				columnCount: this.columnCount,
				dialog: this,
			}).mount(container);
		}

		return this.popup;
	}

	show()
	{
		this.getPopup().show();
	}

	hide()
	{
		this.getPopup().hide();
	}
}