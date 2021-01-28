import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc} from 'landing.loc';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {TextField} from 'landing.ui.field.textfield';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {BaseEvent} from 'main.core.events';
import {Reflection, Type} from 'main.core';
import UserSelectorField from './internal/userselectorfield/userselectorfield';

import './css/style.css';

export default class Other extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.SpamProtection');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_FORM_OTHER_TITLE'),
		});

		const otherForm = new FormSettingsForm({
			id: 'other',
			description: null,
			fields: [
				this.getNameField(),
				this.getUserSelectorField(),
				this.getLanguageField(),
				this.getUseSignField(),
			],
		});

		this.addItem(header);
		this.addItem(otherForm);
	}

	canRemoveCopyrights(): boolean
	{
		return this.options.dictionary.sign.canRemove;
	}

	getNameField(): TextField
	{
		return this.cache.remember('nameField', () => {
			return new TextField({
				title: Loc.getMessage('LANDING_FORM_OTHER_TITLE_NAME_TITLE'),
				selector: 'name',
				textOnly: true,
				content: this.options.values.name,
			});
		});
	}

	getUseSignField(): BX.Landing.UI.Field.Checkbox
	{
		return this.cache.remember('useSignField', () => {
			return new BX.Landing.UI.Field.Checkbox({
				selector: 'useSign',
				value: this.options.values.useSign ? ['useSign'] : [],
				items: [
					{
						value: 'useSign',
						html: `${Loc.getMessage('LANDING_HEADER_AND_BUTTONS_SHOW_SIGN')}${this.createCopyRight()}`,
					},
				],
				compact: true,
			});
		});
	}

	getUserSelectorField()
	{
		return this.cache.remember('userSelectorField', () => {
			return new UserSelectorField({
				selector: 'users',
				title: Loc.getMessage('LANDING_CRM_FORM_USER'),
				value: this.options.values.users.reduce((acc, item) => {
					if (Type.isStringFilled(item) || Type.isNumber(item))
					{
						acc.push(['user', item]);
					}

					return acc;
				}, []),
			});
		});
	}

	getLanguageField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('language', () => {
			return new BX.Landing.UI.Field.Dropdown({
				selector: 'language',
				title: Loc.getMessage('LANDING_CRM_FORM_LANGUAGE'),
				items: this.options.dictionary.languages.map((item) => {
					return {name: item.name, value: item.id};
				}),
				content: this.options.values.language,
			});
		});
	}

	// eslint-disable-next-line class-methods-use-this
	createCopyRight(): string
	{
		return `
			<span class="landing-ui-signin">
				<span class="landing-ui-sign">${Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_SIGN')}</span>
				<span class="landing-ui-sign-in">${Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_SIGN_BY')}</span>
				<span class="landing-ui-sign-24">24</span>
			</span>
		`;
	}

	valueReducer(value: {[key: string]: any}): {[key: string]: any}
	{
		return {
			name: value.name,
			data: {
				language: this.getLanguageField().getValue(),
				useSign: value.useSign.includes('useSign'),
			},
			responsible: {
				users: value.users,
			},
		};
	}

	onChange(event: BaseEvent)
	{
		if (!this.canRemoveCopyrights())
		{
			const checkbox = this.getUseSignField();

			if (!checkbox.getValue().includes('useSign'))
			{
				checkbox.setValue(['useSign']);
				if (Type.isStringFilled(this.options.dictionary.restriction.helper))
				{
					const evalGlobal = Reflection.getClass('BX.evalGlobal');
					if (Type.isFunction(evalGlobal))
					{
						evalGlobal(this.options.dictionary.restriction.helper);
					}
				}
			}
		}

		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}
}