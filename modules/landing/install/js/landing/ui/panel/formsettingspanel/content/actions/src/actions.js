import {Dom, Text, Type} from 'main.core';
import {Loc} from 'landing.loc';
import {HeaderCard} from 'landing.ui.card.headercard';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {BaseEvent} from 'main.core.events';
import {PresetField} from 'landing.ui.field.presetfield';
import {TextField} from 'landing.ui.field.textfield';
import {ActionPagesField} from './internal/action-pages/action-pages';
import {MessageCard} from 'landing.ui.card.messagecard';

import type1Icon from './images/type1.svg';
import type2Icon from './images/type2.svg';
import type3Icon from './images/type3.svg';

import './css/style.css';
import {RefillActionPagesField} from "./internal/action-pages/refill-action-pages";

export default class ActionsContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ActionsContent');

		Dom.addClass(this.getLayout(), 'landing-ui-actions-content-wrapper');

		this.addItem(this.getHeader());

		this.addItem(this.getTypeButtons());

		if (this.options.form)
		{
			this.options.form.sent = false;
			this.options.form.error = false;
		}

		const onBlur = () => {
			this.options.form.sent = false;
			this.options.form.error = false;
		};

		const showFailure =  (event: BaseEvent) => {
			const show = event.data.show || null
			this.options.formOptions.result = this.getValue().result;
			this.options.form.stateText = this.options.formOptions.result.failure.text;
			this.options.form.sent = show === null ? !this.options.form.sent : show;
			this.options.form.error = this.options.form.sent;
		};

		this.getActionPages()
			.subscribe('onShowSuccess', (event: BaseEvent) => {
				const show = event.data.show || null
				this.options.formOptions.result = this.getValue().result;
				this.options.form.stateText = this.options.formOptions.result.success.text;
				this.options.form.sent = show === null ? !this.options.form.sent : show;
				this.options.form.error = false;
			})
			.subscribe('onShowFailure', showFailure)
			.subscribe('onBlur', onBlur)
		;

		this.getRefillActionPages()
			.subscribe('onShowSuccess', (event: BaseEvent) => {
				const show = event.data.show || null
				this.options.formOptions.result = this.getValue().result;
				this.options.form.stateText = this.options.formOptions.result.success.text;

				this.options.form.stateButton.text = this.options.formOptions.result.refill
					&& this.options.formOptions.result.refill.active
					? this.options.formOptions.result.refill.caption
					: '';

				this.options.form.sent = show === null ? !this.options.form.sent : show;
				this.options.form.error = false;

				if (!Type.isFunction(this.options.form.stateButton.handler))
				{
					this.options.form.stateButton.handler = ()=>{};
				}
			})
			.subscribe('onShowFailure', showFailure)
			.subscribe('onBlur', onBlur)
		;
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_ACTIONS_TITLE'),
			});
		});
	}

	getMessage(type: string): MessageCard
	{
		return new MessageCard({
			id: 'actionsMessage' + type,
			header: Loc.getMessage('LANDING_ACTIONS_MESSAGE_HEADER_' + type),
			description: Loc.getMessage('LANDING_ACTIONS_MESSAGE_DESCRIPTION_' + type),
			restoreState: true,
		});

	}

	getTypeButtons(): RadioButtonField
	{
		return this.cache.remember('typeButtons', () => {
			return new RadioButtonField({
				selectable: true,
				value: (() => {
					if (
						this.options.formOptions.result.refill.active
					)
					{
						return 'type3';
					}

					if (
						Type.isStringFilled(this.options.formOptions.result.success.url)
						|| Type.isStringFilled(this.options.formOptions.result.failure.url)
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
					{
						id: 'type3',
						title: Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_3'),
						icon: 'landing-ui-form-actions-type3',
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

	getSuccessLinkField(): BX.Landing.UI.Field.LinkUrl
	{
		return this.cache.remember('successLinkField', () => {
			return new TextField({
				title: Loc.getMessage('LANDING_FORM_ACTIONS_SUCCESS_FIELD_TITLE'),
				placeholder: 'http://',
				textOnly: true,
				content: this.options.formOptions.result.success.url,
				onInput: this.onChange.bind(this),
			});
		});
	}

	getFailureLinkField(): BX.Landing.UI.Field.LinkUrl
	{
		return this.cache.remember('failureLinkField', () => {
			return new TextField({
				title: Loc.getMessage('LANDING_FORM_ACTIONS_FAILURE_FIELD_TITLE'),
				placeholder: 'http://',
				textOnly: true,
				content: this.options.formOptions.result.failure.url,
				onInput: this.onChange.bind(this),
			});
		});
	}

	getRefillCaptionField(): BX.Landing.UI.Field.LinkUrl
	{
		return this.cache.remember('refillCaptionFill', () => {
			return new TextField({
				title: Loc.getMessage('LANDING_FORM_ACTIONS_REFILL_CAPTION_FIELD_TITLE'),
				textOnly: true,
				content: this.options.formOptions.result.refill.caption || Loc.getMessage('LANDING_FORM_ACTIONS_REFILL_CAPTION'),
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
				content: this.options.formOptions.result.redirectDelay,
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
				successText: this.options.formOptions.result.success.text,
				failureText: this.options.formOptions.result.failure.text,
				onChange: this.onChange.bind(this),
			});
		});
	}

	getRefillActionPages()
	{
		return this.cache.remember('refillActionPages', () => {
			return new RefillActionPagesField({
				successText: this.options.formOptions.result.success.text,
				buttonCaption: this.options.formOptions.result.refill.caption,
				failureText: this.options.formOptions.result.failure.text,
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
		this.addItem(this.getMessage(data.item.id))

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

		if (data.item.id === 'type3')
		{
			typeDropdown.setIcon(type3Icon);
			this.addItem(this.getRefillActionPages());
		}

	}

	getValue(): {[p: string]: any}
	{
		const useRefill =  this.getTypeButtons().getValue() === 'type3';
		const actionPagesValue = !useRefill
			? this.getActionPages().getValue()
			: this.getRefillActionPages().getValue();
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
				refill: {
					active: useRefill,
					caption: useRefill ? actionPagesValue.buttonCaption : ''
				}
			},
		};
	}
}