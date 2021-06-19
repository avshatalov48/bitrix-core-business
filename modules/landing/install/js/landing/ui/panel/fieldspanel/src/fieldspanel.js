import {Cache, Dom, Runtime, Type} from 'main.core';
import {Content} from 'landing.ui.panel.content';
import {Loader} from 'main.loader';
import {Backend} from 'landing.backend';
import {PageObject} from 'landing.pageobject';
import {SidebarButton} from 'landing.ui.button.sidebarbutton';
import {Loc} from 'landing.loc';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {BaseButton} from 'landing.ui.button.basebutton';

import './css/style.css';

type CrmField = {
	type: 'list' | 'string' | 'checkbox' | 'date' | 'text' | 'typed_string' | 'file',
	entity_field_name: string,
	entity_name: string,
	name: string,
	caption: string,
	multiple: boolean,
	required: boolean,
	hidden: boolean,
	items: Array<{ID: any, VALUE: any}>,
};

type CrmFieldsList = {
	[categoryId: string]: {
		CAPTION: string,
		FIELDS: Array<CrmField>
	},
};

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class FieldsPanel extends Content
{
	static getInstance(options): FieldsPanel
	{
		const rootWindow = PageObject.getRootWindow();
		const rootWindowPanel = rootWindow.BX.Landing.UI.Panel.FieldsPanel;
		if (!rootWindowPanel.instance && !FieldsPanel.instance)
		{
			rootWindowPanel.instance = new FieldsPanel(options);
		}

		const instance = (rootWindowPanel.instance || FieldsPanel.instance);
		instance.options = options;

		return instance;
	}

	adjustActionsPanels = false;

	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Panel.FieldsPanel');
		this.setLayoutClass('landing-ui-panel-fields');
		this.setOverlayClass('landing-ui-panel-fields-overlay');
		this.setTitle(Loc.getMessage('LANDING_FIELDS_PANEL_TITLE'));

		this.onSaveClick = this.onSaveClick.bind(this);
		this.onCancelClick = this.onCancelClick.bind(this);

		this.options = options;
		this.cache = new Cache.MemoryCache();

		Dom.append(this.layout, this.getViewContainer());
		Dom.append(this.overlay, this.getViewContainer());

		this.showLoader();
		this.loadPromise = this.load()
			.then(() => {
				this.hideLoader();

				Object.entries(this.getCrmFields())
					.forEach(([categoryId, category]) => {
						if (
							categoryId !== 'CATALOG'
							&& categoryId !== 'ACTIVITY'
							&& categoryId !== 'INVOICE'
						)
						{
							if (
								Type.isPlainObject(options)
								&& Type.isBoolean(options.isLeadEnabled)
								&& !options.isLeadEnabled
								&& categoryId === 'LEAD'
							)
							{
								return;
							}

							const button = new SidebarButton({
								id: categoryId,
								text: category.CAPTION,
								child: true,
								onClick: () => {
									this.onSidebarButtonClick(button);
								},
							});

							this.appendSidebarButton(button);
						}
					});

				this.sidebarButtons[0].getLayout().click();

				this.appendFooterButton(
					new BaseButton('save_settings', {
						text: Loc.getMessage('BLOCK_SAVE'),
						onClick: this.onSaveClick,
						className: 'landing-ui-button-content-save',
						attrs: {title: Loc.getMessage('LANDING_TITLE_OF_SLIDER_SAVE')},
					}),
				);

				this.appendFooterButton(
					new BaseButton('cancel_settings', {
						text: Loc.getMessage('BLOCK_CANCEL'),
						onClick: this.onCancelClick,
						className: 'landing-ui-button-content-cancel',
						attrs: {title: Loc.getMessage('LANDING_TITLE_OF_SLIDER_CANCEL')},
					}),
				);
			});
	}

	show(options = {}): Promise<any>
	{
		this.disabledFields = [];

		if (Type.isArrayFilled(options.disabledFields))
		{
			this.disabledFields = options.disabledFields;
		}

		this.loadPromise
			.then(() => {
				if (Type.isArray(options.allowedTypes))
				{
					this.originalCrmFields = this.getCrmFields();
					const crmFields = Runtime.clone(this.originalCrmFields);
					const preparedCrmFields = Object.entries(crmFields)
						.reduce((acc, [categoryId, category]) => {
							const filteredFields = category.FIELDS.filter((field) => {
								return options.allowedTypes.includes(field.type);
							});

							if (Type.isArrayFilled(filteredFields))
							{
								acc[categoryId] = {
									...category,
									FIELDS: filteredFields,
								};
							}

							return acc;
						}, {});

					this.setCrmFields(preparedCrmFields);
				}

				this.sidebarButtons.forEach((button) => {
					Dom.show(button.layout);
				});

				if (options.isLeadEnabled === false)
				{
					const leadButton = this.sidebarButtons.get('LEAD');
					if (leadButton)
					{
						Dom.hide(leadButton.layout);
					}
				}

				if (Type.isArrayFilled(options.allowedCategories))
				{
					this.sidebarButtons.forEach((button) => {
						if (!options.allowedCategories.includes(button.id))
						{
							Dom.hide(button.layout);
						}
					});
				}

				if (this.sidebarButtons.length > 0)
				{
					this.resetState();
					this.sidebarButtons[0].getLayout().click();
				}
			});

		void super.show(options);

		return new Promise((resolve) => {
			this.promiseResolver = resolve;
		});
	}

	hide()
	{
		if (Type.isArrayFilled(this.originalCrmFields))
		{
			this.setCrmFields(this.originalCrmFields);
			delete this.originalCrmFields;
		}

		return super.hide();
	}

	onSaveClick()
	{
		const selectedFields = Object.values(this.getState())
			.reduce((acc, fields) => {
				return [...acc, ...fields];
			}, []);

		this.promiseResolver(selectedFields);
		void this.hide();
		this.resetState();
	}

	onCancelClick()
	{
		void this.hide();
		this.resetState();
	}

	getViewContainer(): HTMLDivElement
	{
		return this.cache.remember('viewContainer', () => {
			const rootWindow = PageObject.getRootWindow();
			return rootWindow.document.querySelector('.landing-ui-view-container');
		});
	}

	getLoader(): Loader
	{
		return this.cache.remember('loader', () => {
			return new Loader({
				target: this.body,
			});
		});
	}

	showLoader()
	{
		void this.getLoader().show();
	}

	hideLoader()
	{
		void this.getLoader().hide();
	}

	load(): Promise<any>
	{
		return Backend.getInstance()
			.action('Form::getCrmFields')
			.then((result) => {
				this.setCrmFields(result);
			});
	}

	setCrmFields(fields)
	{
		this.cache.set('fields', fields);
	}

	getCrmFields(): CrmFieldsList
	{
		return this.cache.get('fields') || {};
	}

	setState(state: {[categoryId: string]: Array<string>})
	{
		this.cache.set('state', state);
	}

	getState(): {[categoryId: string]: Array<string>}
	{
		return this.cache.get('state') || {};
	}

	resetState()
	{
		this.cache.delete('state');
	}

	onSidebarButtonClick(button: SidebarButton)
	{
		const activeButton = this.sidebarButtons.getActive();
		if (activeButton)
		{
			activeButton.deactivate();
		}

		button.activate();

		const crmFields = this.getCrmFields();
		if (Reflect.has(crmFields, button.id))
		{
			this.clearContent();

			const fields = crmFields[button.id].FIELDS;
			const form = new FormSettingsForm({
				fields: [
					new BX.Landing.UI.Field.Checkbox({
						items: fields.map((field) => {
							return {
								name: field.caption,
								value: field.name,
								disabled: this.disabledFields.includes(field.name),
							};
						}),
						value: this.getState()[button.id] || [],
						onValueChange: (checkbox) => {
							const state = {...this.getState()};
							state[button.id] = checkbox.getValue();
							this.setState(state);
						},
					}),
				],
			});

			this.appendForm(form);
		}
	}
}