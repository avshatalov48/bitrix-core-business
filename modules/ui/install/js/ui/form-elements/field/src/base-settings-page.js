import { Dom, Tag, Type, Loc, Runtime } from 'main.core';
import { HelpMessage, Row } from 'ui.section';
import { BaseField } from 'ui.form-elements.view';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { BaseSettingsElement } from './base-settings-element';
import { ErrorCollection } from './error-collection';
import { SettingsField } from './settings-field';
import { SettingsRow } from './settings-row';
import { SettingsSection } from './settings-section';

export class BaseSettingsPage extends BaseSettingsElement
{
	fields: Object = {};
	#content: ?HTMLElement;
	#page: ?HTMLElement;
	titlePage: string = '';
	descriptionPage: string = '';
	#data: ?Object = null;
	/**
	 * @type {?Analytic}
	 */
	#analytic: ?Object;
	#subPage: Map = new Map;
	#subPageExtensions: Array = [];
	#permission: ?Object;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Intranet.Settings');
	}

	getAnalytic(): ?Object
	{
		return this.#analytic;
	}

	/**
	 * @param analytic
	 */
	setAnalytic(analytic: ?Object): void
	{
		this.#analytic = analytic;
	}

	setPermission(permission: ?Object): void
	{
		this.#permission = permission;
	}

	getPermission(): ?Object
	{
		return this.#permission;
	}

	hasValue(key: string): boolean
	{
		if (Type.isNil(this.#data) || !Type.isObject(this.#data))
		{
			return false;
		}

		return !Type.isNil(this.#data[key]);
	}

	getValue(key: string): ?any
	{
		if (!this.hasValue(key))
		{
			return null;
		}

		return this.#data[key];
	}

	hasData(): boolean
	{
		return this.#data !== null;
	}

	getType(): string
	{
		return '';
	}

	getPage(): HTMLElement
	{
		if (!this.getPermission()?.canRead())
		{
			return Tag.render`<div id="${this.getType()}-page-wrapper"></div>`;
		}

		if (this.#page)
		{
			return this.#page;
		}

		if (Type.isNil(this.#data))
		{
			this.#fetchData();
		}

		this.#page = Tag.render`
			<div id="${this.getType()}-page-wrapper">
				${Type.isNil(this.#data) ? LoaderPage.getWrapper() : this.render()}
			</div>
		`;

		return this.#page;
	}

	reload()
	{
		Dom.remove(this.render());
		this.#content = null;
		this.#data = null;
		Dom.append(LoaderPage.getWrapper(), this.getPage());
		this.#fetchData();
	}

	render(): HTMLElement
	{
		if (this.#content)
		{
			return this.#content;
		}

		this.#content = Tag.render`
			<div>
				<div class="intranet-settings__page-header_wrap">
					<div class="intranet-settings__page-header_inner">
						<h1 class="intranet-settings__page-header">${this.titlePage}</h1>
						<p class="intranet-settings__page-header_desc">${this.descriptionPage}</p>
					</div>
					<div class="intranet-settings__header-widget"></div>
				</div>
				<form id="form-${this.getType()}-page" onsubmit="return false;">
					<div class="intranet-settings__content-box"></div>
				</form>
			</div>
		`;
		const headerWidget = this.headerWidgetRender();
		const headerWidgetWrapper = this.#content.querySelector('.intranet-settings__header-widget');
		if (headerWidget)
		{
			Dom.append(headerWidget, headerWidgetWrapper);
			Dom.addClass(this.#content.querySelector('.intranet-settings__page-header_wrap'), '--with-header-widget')
		}
		else
		{
			Dom.remove(headerWidgetWrapper);
		}

		const formNode = this.#content.querySelector('form');
		const contentNode = formNode.querySelector('.intranet-settings__content-box');

		formNode.addEventListener('change', () => {
			if (this.getPermission()?.canEdit())
			{
				this.emit('change', { source: this });
			}
		});

		this.appendSections(contentNode);

		EventEmitter.emit(
			EventEmitter.GLOBAL_TARGET,
			'BX.Intranet.Settings:onContentFetched', {
				page: this,
			},
		);

		return this.#content;
	}

	hasContent(): boolean
	{
		return !Type.isNil(this.#content);
	}

	headerWidgetRender(): HTMLElement
	{
		return '';
	}

	#fetchData()
	{
		(new Promise((resolve, reject) => {
			Runtime
				.loadExtension(this.#subPageExtensions)
				.then((exports) => {
					// 1. collect data by Event for old extensions
					EventEmitter.emit(
							EventEmitter.GLOBAL_TARGET,
							'BX.Intranet.Settings:onPageFetched:' + this.getType(),
							event
						)
						.forEach((subPage: BaseSettingsPage) => this.#subPage.set(subPage.getType(), subPage))
					;
					// 2. collect data by export for new extensions
					Object.values(exports).forEach((desirableClass) => {
						if (Type.isObject(desirableClass))
						{
							if (desirableClass.prototype instanceof BaseSettingsPage)
							{
								const subPage = new desirableClass();
								this.#subPage.set(subPage.getType(), subPage);
							}
							else if (desirableClass instanceof BaseSettingsPage)
							{
								const subPage = desirableClass;
								this.#subPage.set(subPage.getType(), subPage);
							}
						}
					});

					const event = new BaseEvent();
					const eventResult = EventEmitter
						.emit(this, 'fetch', event)
						.some((ajaxPromise: Promise) => {
							if (ajaxPromise instanceof Promise)
							{
								ajaxPromise.then(resolve, reject);
								return true;
							}
							return false;
						})
					;
					if (eventResult !== true)
					{
						reject({error: 'The handler for fetching page data was not found. '});
					}
				})
			;
		})).then(this.onSuccessDataFetched.bind(this), this.onFailDataFetched.bind(this));
	}

	onSuccessDataFetched(response)
	{
		this.setData(response.data);
	}

	setData(data): void
	{
		this.#data = data;

		this.#subPage.forEach((subPage: BaseSettingsPage) => {
			subPage.setData(data);
		});

		if (this.#page)
		{
			Dom.clean(this.#page);
			this.#content = null;
			Dom.append(this.render(), this.#page);
		}

		EventEmitter.emit(
			EventEmitter.GLOBAL_TARGET,
			'BX.Intranet.Settings:onPageComplete', {
				page: this,
			},
		);
	}

	onFailDataFetched(response): void
	{
		ErrorCollection.showSystemError(Loc.getMessage('INTRANET_SETTINGS_ERROR_FETCH_DATA'));
	}

	getFormNode(): ?HTMLElement
	{
		return this.render().querySelector('form');
	}

	appendSections(contentNode: HTMLElement): void
	{
		const sections = this.getSections();

		this.#subPage.forEach((subPage: BaseSettingsPage) => {
			sections.push(...subPage.getSections());
		});

		sections
			.sort((sectionA: SettingsSection, sectionB: SettingsSection) => sectionA.getSectionSort() - sectionB.getSectionSort())
			.forEach((section: SettingsSection) => {
				contentNode.appendChild(
					section.render()
				)
			})
		;
	}

	expandPage(subPageExtensions: array): this
	{
		if (Type.isArray(subPageExtensions))
		{
			this.#subPageExtensions.push(...subPageExtensions);
		}

		return this;
	}

	getSections(): SettingsSection[]
	{
		return [];
	}

	helpMessageProviderFactory(message: ?HTMLElement): function
	{
		message = Type.isNil(message) ? Loc.getMessage('INTRANET_SETTINGS_FIELD_HELP_MESSAGE') : message;

		return (id: string, node: HTMLElement) => {
			return new HelpMessage(id, node, message);
		};
	}

	static addToSectionHelper(fieldView: BaseField, sectionSettings: SettingsSection, row: Row = null): void
	{
		let settingsField = new SettingsField({
			fieldView: fieldView,
		});
		new SettingsRow({
			row: row,
			child: settingsField,
			parent: sectionSettings,
		});
	}
}

class LoaderPage
{
	static #wrapper: HTMLElement;

	static getWrapper()
	{
		if (LoaderPage.#wrapper)
		{
			return LoaderPage.#wrapper;
		}

		LoaderPage.#wrapper = Tag.render`
			<div class="intranet-settings__loader"></div>
		`;
		// const loader = new Loader({target: LoaderPage.#wrapper, size: 200});
		// loader.show();

		return LoaderPage.#wrapper;
	}
}
