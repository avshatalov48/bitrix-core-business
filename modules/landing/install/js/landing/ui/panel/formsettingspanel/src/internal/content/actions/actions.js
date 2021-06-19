import {Dom, Text, Type} from 'main.core';
import {Loc} from 'landing.loc';
import {HeaderCard} from 'landing.ui.card.headercard';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {BaseEvent} from 'main.core.events';
import {PresetField} from 'landing.ui.field.presetfield';
import {TextField} from 'landing.ui.field.textfield';
import {ActionPagesField} from './internal/action-pages/action-pages';

import type1Icon from './images/type1.svg';
import type2Icon from './images/type2.svg';

import './css/style.css';

export default class ActionsContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ActionsContent');

		Dom.addClass(this.getLayout(), 'landing-ui-actions-content-wrapper');

		this.addItem(this.getHeader());
		this.addItem(this.getTypeButtons());

		this.getActionPages()
			.subscribe('onShowSuccess', () => {
				this.emit('onShowSuccess');
			})
			.subscribe('onShowFailure', () => {
				this.emit('onShowFailure');
			});
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_ACTIONS_TITLE'),
			});
		});
	}

	getTypeButtons(): RadioButtonField
	{
		return this.cache.remember('typeButtons', () => {
			return new RadioButtonField({
				selectable: true,
				value: (() => {
					if (
						Type.isStringFilled(this.options.values.success.url)
						&& Type.isStringFilled(this.options.values.failure.url)
					)
					{
						return 'type2';
					}

					return 'type1';
				})(),
				items: [
					{
						id: 'type1',
						title: Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_1'),
						icon: 'landing-ui-form-actions-type1',
					},
					{
						id: 'type2',
						title: Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_2'),
						icon: 'landing-ui-form-actions-type2',
					},
				],
				onChange: this.onTypeChange.bind(this),
			});
		});
	}

	getCheckbox()
	{
		return this.cache.remember('checkbox', () => {
			return new BX.Landing.UI.Field.Checkbox({
				items: [
					{name: Loc.getMessage('LANDING_FORM_ACTIONS_CHECKBOX_TITLE'), value: true},
				],
			});
		});
	}

	getTypeDropdown(): PresetField
	{
		return this.cache.remember('typeDropdown', () => {
			const field = new PresetField({
				events: {
					onClick: () => {
						this.clear();
						this.addItem(this.getHeader());
						this.addItem(this.getTypeButtons());
					},
				},
			});
			field.setTitle(Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_DROPDOWN_TITLE'));
			field.setIcon(type1Icon);

			return field;
		});
	}

	getSuccessLinkField(): BX.Landing.UI.Field.LinkURL
	{
		return this.cache.remember('successLinkField', () => {
			return new TextField({
				title: Loc.getMessage('LANDING_FORM_ACTIONS_SUCCESS_FIELD_TITLE'),
				placeholder: 'http://',
				textOnly: true,
				content: this.options.values.success.url,
				onInput: this.onChange.bind(this),
			});
		});
	}

	getFailureLinkField(): BX.Landing.UI.Field.LinkURL
	{
		return this.cache.remember('failureLinkField', () => {
			return new TextField({
				title: Loc.getMessage('LANDING_FORM_ACTIONS_FAILURE_FIELD_TITLE'),
				placeholder: 'http://',
				textOnly: true,
				content: this.options.values.failure.url,
				onInput: this.onChange.bind(this),
			});
		});
	}

	getDelayField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('delayField', () => {
			return new BX.Landing.UI.Field.Dropdown({
				selector: 'redirectDelay',
				title: Loc.getMessage('LANDING_FORM_ACTIONS_DELAY_TITLE'),
				content: this.options.values.redirectDelay,
				items: Array.from({length: 11}, (item, index) => {
					return {
						name: `${index} ${Loc.getMessage('LANDING_FORM_ACTIONS_DELAY_ITEM')}`,
						value: (index),
					};
				}),
			});
		});
	}

	onChange()
	{
		this.emit('onChange', {skipPrepare: true});
	}

	getActionPages()
	{
		return this.cache.remember('actionPages', () => {
			return new ActionPagesField({
				successText: this.options.values.success.text,
				failureText: this.options.values.failure.text,
				onChange: this.onChange.bind(this),
			});
		});
	}

	onTypeChange(event: BaseEvent)
	{
		const data = event.getData();
		const typeDropdown = this.getTypeDropdown();

		this.clear();

		this.addItem(this.getHeader());
		this.addItem(typeDropdown);

		typeDropdown.setLinkText(data.item.title.replace(/&nbsp;/, ' '));

		if (data.item.id === 'type1')
		{
			typeDropdown.setIcon(type1Icon);
			this.addItem(this.getActionPages());
		}

		if (data.item.id === 'type2')
		{
			typeDropdown.setIcon(type2Icon);
			this.addItem(this.getSuccessLinkField());
			this.addItem(this.getFailureLinkField());
			this.addItem(this.getDelayField());
		}
	}

	getValue(): {[p: string]: any}
	{
		const actionPagesValue = this.getActionPages().getValue();
		const useRedirect = this.getTypeButtons().getValue() === 'type2';

		return {
			result: {
				success: {
					text: actionPagesValue.success,
					url: useRedirect ? Text.decode(this.getSuccessLinkField().getValue()) : '',
				},
				failure: {
					text: actionPagesValue.failure,
					url: useRedirect ? Text.decode(this.getFailureLinkField().getValue()) : '',
				},
				redirectDelay: this.getDelayField().getValue(),
			},
		};
	}
}