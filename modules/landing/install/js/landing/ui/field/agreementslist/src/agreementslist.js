import {Dom, Loc, Reflection, Runtime, Tag, Type} from 'main.core';
import {Menu} from 'main.popup';
import {BaseField} from 'landing.ui.field.basefield';
import {Draggable} from 'ui.draganddrop.draggable';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {FormClient} from 'crm.form.client';
import {ListItem} from 'landing.ui.component.listitem';
import {ActionPanel} from 'landing.ui.component.actionpanel';
import {BaseEvent} from 'main.core.events';
import {Loader} from 'main.loader';
import {Backend} from 'landing.backend';
import {FormSettingsPanel} from 'landing.ui.panel.formsettingspanel';

import './css/style.css';

type Agreement = {
	id: string,
	checked: boolean,
	label: string,
	name: string,
	required: boolean,
	value: 'Y' | 'N',
	content: {
		text: string,
		title: string,
		url: ?string,
	},
};

type AgreementsListItem = {
	id: string | number,
	name: string,
	labelText: string,
};

/**
 * @memberOf BX.Landing.UI.Field
 */
export class AgreementsList extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.AgreementsList');

		this.onSelectAgreementClick = this.onSelectAgreementClick.bind(this);
		this.onCreateAgreementClick = this.onCreateAgreementClick.bind(this);
		this.onUserConsentEditSave = this.onUserConsentEditSave.bind(this);
		this.onUserConsentEditCancel = this.onUserConsentEditCancel.bind(this);
		this.onItemRemoveClick = this.onItemRemoveClick.bind(this);
		this.onDragEnd = this.onDragEnd.bind(this);

		this.items = [];

		Dom.replace(this.input, this.getListContainer());
		Dom.append(this.getActionsContainer(), this.layout);

		void this.showAgreementLoader();

		FormClient
			.getInstance()
			.prepareOptions(this.options.formOptions, this.options.value)
			.then((result) => {
				return result.data.agreements.map((item, index) => {
					return Runtime.merge(item, this.options.value[index]);
				});
			})
			.then((agreements) => {
				void this.hideAgreementLoader();
				agreements.forEach((agreement) => {
					this.addItem(agreement);
				});
			});

		this.draggable = new Draggable({
			context: window.parent,
			container: this.getListContainer(),
			draggable: '.landing-ui-component-list-item',
			dragElement: '.landing-ui-button-icon-drag',
			type: Draggable.MOVE,
			offset: {
				y: -62,
			},
		});

		this.draggable
			.subscribe('end', this.onDragEnd);

		const addCustomEvent = Reflection.getClass('top.BX.addCustomEvent');
		addCustomEvent(window.top, 'main-user-consent-to-list', this.onUserConsentEditCancel);
		addCustomEvent(window.top, 'main-user-consent-saved', this.onUserConsentEditSave);
	}

	getAgreementsList(): Array<AgreementsListItem>
	{
		return this.cache.remember('agreementsList', () => {
			return this.options.agreementsList;
		});
	}

	setAgreementsList(agreements: Array<AgreementsListItem>)
	{
		this.cache.set('agreementsList', agreements);
	}

	loadAgreementsList(): Promise<Array<AgreementsListItem>>
	{
		return Backend.getInstance()
			.action('Form::getAgreements')
			.then((agreements) => {
				return Runtime.orderBy(agreements, ['id'], ['asc']);
			});
	}

	getAgreementById(id: string | number): ?AgreementsListItem
	{
		return this.getAgreementsList().find((agreement) => {
			return String(id) === String(agreement.id);
		});
	}

	addItem(itemOptions)
	{
		const item = this.createItem(itemOptions);
		item.appendTo(this.getListContainer());

		this.items = this.items.filter((currentItem) => {
			return String(currentItem.options.id) !== String(item.options.id);
		});

		this.items.push(item);
	}

	getListContainer(): HTMLDivElement
	{
		return this.cache.remember('listContainer', () => {
			return Tag.render`<div class="landing-ui-field-agreements-list-container"></div>`;
		});
	}

	getActionsContainer(): HTMLDivElement
	{
		return this.cache.remember('actionsContainer', () => {
			return Tag.render`
				<div class="landing-ui-field-agreements-list-actions-container">
					${this.getSelectAgreementButton()}
					${this.getCreateAgreementButton()}
				</div>
			`;
		});
	}

	getSelectAgreementButton(): HTMLSpanElement
	{
		return this.cache.remember('selectAgreementButton', () => {
			return Tag.render`
				<span class="landing-ui-field-agreements-list-actions-button" onclick="${this.onSelectAgreementClick}">
					${Loc.getMessage('LANDING_AGREEMENT_LIST_SELECT_BUTTON_LABEL')}
				</span>
			`;
		});
	}

	getCreateAgreementButton(): HTMLSpanElement
	{
		return this.cache.remember('createAgreementButton', () => {
			return Tag.render`
				<span class="landing-ui-field-agreements-list-actions-button" onclick="${this.onCreateAgreementClick}">
					${Loc.getMessage('LANDING_AGREEMENT_LIST_CREATE_BUTTON_LABEL')}
				</span>
			`;
		});
	}

	getSelectedAgreements(): Array<number>
	{
		return [...this.getListContainer().children].map((item) => {
			return Dom.attr(item, 'data-value');
		});
	}

	getAgreementsMenu(): Menu
	{
		return this.cache.remember('agreementsMenu', () => {
			const menu = new Menu({
				bindElement: this.getSelectAgreementButton(),
				autoHide: true,
				maxWidth: 400,
				maxHeight: 205,
				events: {
					onPopupShow: () => {
						setTimeout(() => {
							Dom.style(menu.getMenuContainer(), {
								left: '0px',
								right: 'auto',
								top: '30px',
							});
						});
					},
				},
			});

			this.getAgreementsList()
				.filter((agreement) => {
					return !this.items.some((item) => {
						return String(item.options.id) === String(agreement.id);
					});
				})
				.forEach((agreement) => {
					menu.addMenuItem({
						id: agreement.id,
						text: agreement.name,
						onclick: this.onAgreementsMenuItemClick.bind(this, agreement),
					});
				});

			Dom.append(menu.getMenuContainer(), this.getActionsContainer());

			return menu;
		});
	}

	refreshAgreementsMenu()
	{
		const agreementsMenu = this.getAgreementsMenu();
		agreementsMenu.close();
		agreementsMenu.destroy();
		this.cache.delete('agreementsMenu');
	}

	// eslint-disable-next-line class-methods-use-this
	createItemForm(agreement: Agreement)
	{
		return new FormSettingsForm({
			id: agreement.id,
			title: Loc.getMessage('LANDING_AGREEMENT_FORM_TITLE'),
			onChange: () => {
				this.emit('onChange', {skipPrepare: true});
			},
			serializeModifier(value) {
				if (value.type === 'type1')
				{
					return {
						checked: true,
						required: true,
					};
				}

				if (value.type === 'type2')
				{
					return {
						checked: false,
						required: true,
					};
				}

				if (value.type === 'type3')
				{
					return {
						checked: true,
						required: false,
					};
				}

				if (value.type === 'type4')
				{
					return {
						checked: false,
						required: false,
					};
				}
			},
			fields: [
				new RadioButtonField({
					selector: 'type',
					value: (() => {
						if (
							agreement.checked === true
							&& agreement.required === true
						)
						{
							return 'type1';
						}

						if (
							agreement.checked === false
							&& agreement.required === true
						)
						{
							return 'type2';
						}

						if (
							agreement.checked === true
							&& agreement.required === false
						)
						{
							return 'type3';
						}

						if (
							agreement.checked === false
							&& agreement.required === false
						)
						{
							return 'type4';
						}
					})(),
					items: [
						{
							id: 'type1',
							title: Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_1'),
							icon: 'landing-ui-agreement-type-1-icon',
						},
						{
							id: 'type2',
							title: Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_2'),
							icon: 'landing-ui-agreement-type-2-icon',
						},
						{
							id: 'type3',
							title: Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_3'),
							icon: 'landing-ui-agreement-type-3-icon',
						},
						{
							id: 'type4',
							title: Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_4'),
							icon: 'landing-ui-agreement-type-4-icon',
						},
					],
				}),
				new ActionPanel({
					left: [
						{
							id: 'edit',
							text: Loc.getMessage('LANDING_AGREEMENT_EDIT_BUTTON_LABEL'),
							onClick: () => this.editAgreement(agreement),
						},
						{
							id: 'list',
							text: Loc.getMessage('LANDING_AGREEMENT_CONSENTS_BUTTON_LABEL'),
							onClick: () => this.openConsentsList(agreement),
						}
					],
				}),
			],
		});
	}

	getAgreementLoader(): Loader
	{
		return this.cache.remember('agreementLoader', () => {
			return new Loader({
				size: 50,
				mode: 'inline',
				offset: {
					top: '5px',
					left: '225px',
				},
			});
		});
	}

	showAgreementLoader(): Promise
	{
		const loader = this.getAgreementLoader();
		const container = this.getListContainer();
		Dom.append(loader.layout, container);
		return loader.show(container);
	}

	hideAgreementLoader(): Promise
	{
		const loader = this.getAgreementLoader();
		Dom.remove(loader.layout);
		return loader.hide();
	}

	onAgreementsMenuItemClick(itemOptions)
	{
		void this.showAgreementLoader();

		FormClient
			.getInstance()
			.prepareOptions(this.options.formOptions, {agreements: [{id: itemOptions.id}]})
			.then((result) => {
				void this.hideAgreementLoader();
				this.addItem(result.data.agreements[0]);
				this.emit('onChange', {skipPrepare: true});
			});

		this.refreshAgreementsMenu();
	}

	onSelectAgreementClick(event: MouseEvent)
	{
		event.preventDefault();

		const menu = this.getAgreementsMenu();
		if (!menu.getPopupWindow().isShown())
		{
			menu.show();
		}
		else
		{
			menu.close();
		}
	}

	onCreateAgreementClick(event: MouseEvent)
	{
		event.preventDefault();
		this.editAgreement({id: 0});
	}

	// eslint-disable-next-line class-methods-use-this
	onItemHeaderClick(agreement: Agreement, event: MouseEvent)
	{
		event.preventDefault();

		const {parentElement} = event.currentTarget;

		Dom.toggleClass(parentElement, 'landing-ui-field-agreements-list-item-active');
	}

	createItem(options: Agreement): ListItem
	{
		const agreementListItem = this.getAgreementById(options.id);

		return new ListItem({
			id: options.id,
			title: agreementListItem.name,
			description: agreementListItem.labelText,
			sourceOptions: options,
			draggable: true,
			editable: true,
			removable: true,
			form: this.createItemForm(options),
			onRemove: this.onItemRemoveClick,
		});
	}

	setCurrentlyEdited(agreement: Agreement)
	{
		this.cache.set('setCurrentlyEdited', agreement);
	}

	getCurrentlyEdited(): ?Agreement
	{
		return this.cache.get('setCurrentlyEdited') || null;
	}

	// eslint-disable-next-line
	buildEditPath(agreementId): string
	{
		return `/settings/configs/userconsent/edit/${agreementId}/`;
	}

	// eslint-disable-next-line
	buildConsentsListPath(agreementId)
	{
		return `/settings/configs/userconsent/consents/${agreementId}/`;
	}

	editAgreement(agreement: Agreement)
	{
		this.setCurrentlyEdited(agreement);

		const editPath = this.buildEditPath(agreement.id);
		BX.SidePanel.Instance.open(
			editPath,
			{
				cacheable: false,
				allowChangeHistory: false,
			},
		);
	}

	closeEditAgreementSlider()
	{
		const currentlyEdited = this.getCurrentlyEdited();
		if (Type.isPlainObject(currentlyEdited))
		{
			const path = this.buildEditPath(currentlyEdited.id);
			const slider = BX.SidePanel.Instance.getSlider(path);
			if (slider)
			{
				slider.close();
			}
		}
	}

	openConsentsList(agreement: Agreement)
	{
		const editPath = this.buildConsentsListPath(agreement.id);
		BX.SidePanel.Instance.open(
			editPath,
			{
				cacheable: false,
				allowChangeHistory: false,
			},
		);
	}

	onUserConsentEditCancel()
	{
		this.closeEditAgreementSlider();
	}

	onUserConsentEditSave()
	{
		this.closeEditAgreementSlider();
		void this.showAgreementLoader();

		const value = this.getValue();

		this.loadAgreementsList()
			.then((agreements) => {
				this.setAgreementsList(agreements);
				FormSettingsPanel.getInstance().setAgreements(agreements);

				const currentlyEdited = this.getCurrentlyEdited();
				if (currentlyEdited && currentlyEdited.id === 0)
				{
					const lastAgreement = [...agreements].pop();
					FormClient
						.getInstance()
						.prepareOptions(this.options.formOptions, {agreements: [lastAgreement]})
						.then((result) => {
							void this.hideAgreementLoader();
							this.addItem(result.data.agreements[0]);
							this.refreshAgreementsMenu();
							this.emit('onChange', {skipPrepare: true});
						});
				}
				else
				{
					Dom.clean(this.getListContainer());
					void this.showAgreementLoader();

					FormClient
						.getInstance()
						.prepareOptions(this.options.formOptions, {agreements: value})
						.then((result) => {
							void this.hideAgreementLoader();
							this.items = [];
							value.forEach((agreement) => {
								const resultAgreement = result.data.agreements.find((currentAgreement) => {
									return String(currentAgreement.id) === String(agreement.id);
								});

								if (resultAgreement)
								{
									this.addItem({
										...resultAgreement,
										checked: agreement.checked,
										required: agreement.required,
									});
								}
								else
								{
									this.addItem(agreement);
								}
							});
							this.refreshAgreementsMenu();
							this.emit('onChange', {skipPrepare: true});
						});
				}
			});
	}

	onItemRemoveClick(event: BaseEvent)
	{
		const value = event.getTarget().getValue();

		this.items = this.items.filter((item) => {
			return String(item.options.id) !== String(value.id);
		});

		this.refreshAgreementsMenu();
		this.emit('onItemRemove', {item: value});
		this.emit('onChange', {skipPrepare: true});
	}

	onDragEnd()
	{
		const items = this.items;
		this.items = [];

		[...this.getListContainer().children].forEach((element) => {
			const id = Dom.attr(element, 'data-id');
			const item = items.find((currentItem) => {
				return String(currentItem.options.id) === String(id);
			});

			if (item)
			{
				this.items.push(item);
			}
		});

		this.emit('onChange', {skipPrepare: true});
	}

	getValue(): Array<Agreement>
	{
		return this.items.map((item) => {
			return item.getValue();
		});
	}
}