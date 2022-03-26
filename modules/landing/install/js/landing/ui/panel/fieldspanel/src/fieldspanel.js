import {Cache, Dom, Tag, Type} from 'main.core';
import {Content} from 'landing.ui.panel.content';
import {Loader} from 'main.loader';
import {Backend} from 'landing.backend';
import {PageObject} from 'landing.pageobject';
import {SidebarButton} from 'landing.ui.button.sidebarbutton';
import {Loc} from 'landing.loc';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {BaseButton} from 'landing.ui.button.basebutton';
import {Text} from 'landing.ui.field.textfield';
import {FormSettingsPanel} from 'landing.ui.panel.formsettingspanel';
import {FormClient} from 'crm.form.client';
import 'ui.userfieldfactory';

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
		Dom.insertAfter(this.getSearchContainer(), this.header);
		Dom.append(this.getCreateFieldLayout(), this.body);

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
			});

		this.appendFooterButton(
			new BaseButton('save_settings', {
				text: Loc.getMessage('LANDING_FIELDS_PANEL_ADD_SELECTED_BUTTON'),
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
	}

	isMultiple(): boolean
	{
		return this.cache.get('multiple', true);
	}

	setMultiple(mode: boolean)
	{
		this.cache.set('multiple', mode);
	}

	setAllowedTypes(types: Array<string>)
	{
		this.cache.set('allowedTypes', types);
	}

	getAllowedTypes(): Array<string>
	{
		return this.cache.get('allowedTypes', []);
	}

	setDisabledFields(fields: Array<string>)
	{
		this.cache.set('disabledFields', fields);
	}

	getDisabledFields(): Array<string>
	{
		return this.cache.get('disabledFields', []);
	}

	setAllowedCategories(categories: Array<string>)
	{
		this.cache.set('allowedCategories', categories);
	}

	getAllowedCategories(): Array<string>
	{
		return this.cache.get('allowedCategories', []);
	}

	resetFactoriesCache()
	{
		this.cache.keys().forEach((key) => {
			if (key.startsWith('userFieldFactory_'))
			{
				this.cache.delete(key);
			}
		});
	}

	show(options = {}): Promise<any>
	{
		this.getSearchField().input.textContent = '';

		this.setMultiple(true);
		this.setAllowedTypes([]);
		this.setDisabledFields([]);
		this.setAllowedCategories([]);
		this.resetFactoriesCache();

		if (Type.isArrayFilled(options.disabledFields))
		{
			this.setDisabledFields(options.disabledFields);
		}

		if (Type.isArrayFilled(options.allowedCategories))
		{
			this.setAllowedCategories(options.allowedCategories);
		}

		if (Type.isArrayFilled(options.allowedTypes))
		{
			this.setAllowedTypes(options.allowedTypes);
		}

		if (Type.isBoolean(options.multiple))
		{
			this.setMultiple(options.multiple);
		}

		this.loadPromise
			.then(() => {
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
						else
						{
							Dom.show(button.layout);
						}
					});
				}
				else
				{
					this.sidebarButtons.forEach((button) => {
						Dom.show(button.layout);
					});
				}

				const filteredFieldsTree = this.getFilteredFieldsTree();
				const categories = Object.keys(filteredFieldsTree);

				this.sidebarButtons.forEach((button) => {
					button.deactivate();

					if (categories.includes(button.id))
					{
						Dom.show(button.getLayout());
					}
					else
					{
						Dom.hide(button.getLayout());
					}
				});

				if (this.sidebarButtons.length > 0)
				{
					this.resetState();

					const firstShowedButton = this.sidebarButtons.find((button) => {
						return button.getLayout().hidden !== true;
					});
					if (firstShowedButton)
					{
						firstShowedButton.getLayout().click();
					}
				}
			});

		super.show(options).then(() => {
			this.getSearchField().enableEdit();
			this.getSearchField().input.focus();
		});

		return new Promise((resolve) => {
			this.promiseResolver = resolve;
		});
	}

	hide()
	{
		this.setCrmFields(this.getOriginalCrmFields());
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
		this.hideCreateFieldButton();
		void this.getLoader().show();
	}

	hideLoader()
	{
		this.showCreateFieldButton();
		void this.getLoader().hide();
	}

	load(): Promise<any>
	{
		return Backend.getInstance()
			.action('Form::getCrmFields')
			.then((result) => {
				this.setOriginalCrmFields(result);
				this.setCrmFields(result);
				Object.assign(FormSettingsPanel.getInstance().getCrmFields(), result);

				return FormClient
					.getInstance()
					.getDictionary()
					.then((dictionary) => {
						this.setFormDictionary(dictionary);
					});
			});
	}

	setFormDictionary(dictionary)
	{
		this.cache.set('formDictionary', dictionary);
	}

	getFormDictionary(): {[key: string]: any}
	{
		return this.cache.get('formDictionary', {});
	}

	setOriginalCrmFields(fields)
	{
		this.cache.set('originalFields', fields);
	}

	getOriginalCrmFields(): CrmFieldsList
	{
		return this.cache.get('originalFields') || {};
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

		const hideCreateButton = this.getAllowedTypes().every((type) => {
			return Type.isPlainObject(type);
		});
		if (Type.isArrayFilled(this.getAllowedTypes()) && hideCreateButton)
		{
			this.hideCreateFieldButton();
		}
		else
		{
			this.showCreateFieldButton();
		}

		const crmFields = this.getCrmFields();
		if (Reflect.has(crmFields, button.id))
		{
			this.clearContent();

			const form = this.createFieldsListForm(button.id);
			this.appendForm(form);
		}
	}

	getFilteredFieldsTree()
	{
		const searchString = String(this.getSearchField().getValue()).toLowerCase().trim();
		const allowedCategories = this.getAllowedCategories();
		const allowedTypes = this.getAllowedTypes();

		return Object
			.entries(this.getCrmFields())
			.reduce((acc, [categoryId, category]) => {
				if (
					(
						categoryId !== 'CATALOG'
						&& categoryId !== 'ACTIVITY'
						&& categoryId !== 'INVOICE'
					)
					&& (
						!Type.isArrayFilled(allowedCategories)
						|| allowedCategories.includes(categoryId)
					)
				)
				{
					const filteredFields = category.FIELDS.filter((field) => {
						const fieldCaption = String(field.caption).toLowerCase().trim();
						if (Type.isArrayFilled(allowedTypes))
						{
							const isTypeAllowed = allowedTypes.some(allowedType => {
								if (!Type.isPlainObject(allowedType))
								{
									allowedType = {type: allowedType};
								}
								if (
									allowedType.entityFieldName
									&& allowedType.entityFieldName !== field.entity_field_name
								)
								{
									return false;
								}
								if (
									Type.isBoolean(allowedType.multiple)
									&& allowedType.multiple !== field.multiple
								)
								{
									return false;
								}

								return field.type === allowedType.type;
							});
							if (!isTypeAllowed)
							{
								return false;
							}
						}

						return (
							!Type.isStringFilled(searchString)
							|| fieldCaption.includes(searchString)
						);
					});

					if (Type.isArrayFilled(filteredFields))
					{
						acc[categoryId] = {
							...category,
							FIELDS: filteredFields,
						};
					}
				}

				return acc;
			}, {});
	}

	createFieldsListForm(category: string): FormSettingsForm
	{
		const fieldsListTree = this.getFilteredFieldsTree();
		const disabledFields = this.getDisabledFields();
		const fieldOptions = {
			items: fieldsListTree[category].FIELDS.map((field) => {
				return {
					name: field.caption,
					value: field.name,
					disabled: (
						Type.isArrayFilled(disabledFields)
						&& disabledFields.includes(field.name)
					),
				};
			}),
			value: this.getState()[category] || [],
			onValueChange: (checkbox) => {
				const state = {...this.getState()};
				state[category] = checkbox.getValue();
				this.setState(state);
			},
		};

		return new FormSettingsForm({
			fields: [
				this.isMultiple()
					? new BX.Landing.UI.Field.Checkbox(fieldOptions)
					: new BX.Landing.UI.Field.Radio(fieldOptions)
				,
			],
		});
	}

	onSearchChange()
	{
		const filteredFieldsTree = this.getFilteredFieldsTree();
		const categories = Object.keys(filteredFieldsTree);

		this.sidebarButtons.forEach((button) => {
			button.deactivate();

			if (categories.includes(button.id))
			{
				Dom.show(button.getLayout());
			}
			else
			{
				Dom.hide(button.getLayout());
			}
		});

		this.clearContent();

		const [firstCategory] = categories;
		if (firstCategory)
		{
			const firstCategoryButton = this.sidebarButtons.get(firstCategory);
			if (firstCategoryButton)
			{
				firstCategoryButton.activate();
			}

			const form = this.createFieldsListForm(firstCategory);

			this.showCreateFieldButton();
			this.appendForm(form);
		}
		else
		{
			this.hideCreateFieldButton();
		}
	}

	getSearchField(): Text
	{
		return this.cache.remember('searchField', () => {
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.Landing.UI.Field.Text({
				selector: 'search',
				textOnly: true,
				placeholder: Loc.getMessage('LANDING_FIELDS_PANEL_SEARCH'),
				onChange: this.onSearchChange.bind(this),
			});
		});
	}

	getSearchContainer(): HTMLDivElement
	{
		return this.cache.remember('searchLayout', () => {
			return Tag.render`
				<div class="landing-ui-panel-content-element landing-ui-panel-content-search">
					${this.getSearchField().getLayout()}
					<div class="landing-ui-panel-content-search-icon"></div>
				</div>
			`;
		});
	}

	getUserFieldFactory(entityId: string)
	{
		const factory = this.cache.remember(`userFieldFactory_${entityId}`, () => {
			const rootWindow = PageObject.getRootWindow();
			const preparedEntityId = (() => {
				if (entityId.startsWith('DYNAMIC_'))
				{
					return this.getCrmFields()[entityId].DYNAMIC_ID;
				}

				return `CRM_${entityId}`;
			})();

			return new rootWindow.BX.UI.UserFieldFactory.Factory(
				preparedEntityId,
				{
					moduleId: 'crm',
					bindElement: this.getCreateFieldButton(),
				},
			);
		});

		if (Type.isArrayFilled(this.getAllowedTypes()))
		{
			factory.types = factory.types.filter((type) => {
				return this.getAllowedTypes().includes(type.name);
			});
		}
		else
		{
			factory.types = factory.types.filter((type) => {
				return type.name !== 'employee';
			});
		}

		return factory;
	}

	onCreateFieldClick(event: MouseEvent)
	{
		event.preventDefault();

		const dictionary = this.getFormDictionary();

		if (
			Type.isPlainObject(dictionary.permissions)
			&& Type.isPlainObject(dictionary.permissions.userField)
			&& dictionary.permissions.userField.add === false
		)
		{
			const rootWindow = PageObject.getRootWindow();
			rootWindow.BX.UI.Dialogs.MessageBox.alert(Loc.getMessage('LANDING_FORM_ADD_USER_FIELD_PERMISSION_DENIED'));
			return;
		}

		const activeButton = this.sidebarButtons.getActive();
		const currentCategoryId = activeButton.id;

		const factory = this.getUserFieldFactory(currentCategoryId);
		const menu = factory.getMenu();

		menu.open((type) => {
			const configurator = factory.getConfigurator({
				userField: factory.createUserField(type),
				onSave: (userField) => {
					userField
						.save()
						.then(() => {
							return this.load();
						})
						.then(() => {
							this.getSearchField()
								.setValue(userField.getData().editFormLabel[Loc.getMessage('LANGUAGE_ID')]);
							this.showCreateFieldButton();
						});
				},
				onCancel: () => {
					this.showCreateFieldButton();
					this.sidebarButtons.getActive().getLayout().click();
				},
			});

			this.clearContent();
			Dom.append(configurator.render(), this.content);
			this.hideCreateFieldButton();
		});
	}

	getCreateFieldButton(): HTMLSpanElement
	{
		return this.cache.remember('getCreateFieldButton', () => {
			return Tag.render`
				<div
					class="landing-ui-panel-content-create-field-button"
					onclick="${this.onCreateFieldClick.bind(this)}"
				>
					${Loc.getMessage('LANDING_FIELDS_PANEL_CREATE_FIELD')}
				</div>
			`;
		});
	}

	getCreateFieldLayout(): HTMLDivElement
	{
		return this.cache.remember('createFieldLayout', () => {
			return Tag.render`
				<div class="landing-ui-panel-content-create-field">
					${this.getCreateFieldButton()}
				</div>
			`;
		});
	}

	isUserFieldEditorShowed(): boolean
	{
		return Type.isDomNode(this.content.querySelector('.ui-userfieldfactory-configurator'));
	}

	showCreateFieldButton()
	{
		Dom.append(this.getCreateFieldLayout(), this.body);
	}

	hideCreateFieldButton()
	{
		Dom.remove(this.getCreateFieldLayout(), this.body);
	}
}