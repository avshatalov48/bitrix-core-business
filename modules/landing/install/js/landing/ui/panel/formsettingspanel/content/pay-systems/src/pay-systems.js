import { Loc } from 'landing.loc';
import { HeaderCard } from 'landing.ui.card.headercard';
import { MessageCard } from 'landing.ui.card.messagecard';
import { ContentWrapper } from 'landing.ui.panel.basepresetpanel';
import { Dom, Tag, Text } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { PaySystemsSelectorField } from 'landing.ui.field.paysystemsselectorfield';
import { FormSettingsForm } from 'landing.ui.form.formsettingsform';
import { SchemeManager } from 'landing.ui.panel.formsettingspanel.content.crm.schememanager';

import './css/style.css';
import 'ui.sidepanel-content';

import messageIcon from './images/message-icon.svg';

export default class PaySystems extends ContentWrapper
{
	#moduleNotIncludedErrorCode = 'MODULE_NOT_INCLUDED';
	#schemeManager: SchemeManager;

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.PaySystemContent');

		this.#schemeManager = new SchemeManager([...options.dictionary.document.schemes]);

		this.addItem(this.#getHeaderCard());
		this.addItem(this.#getMessageCard());
		this.addItem(this.#getPaySystemsSelectorForm());
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => Tag.render`<div class="landing-ui-content-pay-system"></div>`);
	}

	#showErrorPlaceholder(title: string, subtitle: string = ''): void
	{
		Dom.clean(this.getLayout());
		Dom.append(
			this.getErrorPlaceholderLayout(title, subtitle),
			this.getLayout(),
		);
	}

	getErrorPlaceholderLayout(title: string, subtitle: string = ''): HTMLDivElement
	{
		return this.cache.remember(`errorTitle`, () => Tag.render`
			<div class="ui-slider-no-access">
				<div class="ui-slider-no-access-inner">
					<div class="ui-slider-no-access-title">${title}</div>
					<div class="ui-slider-no-access-subtitle">${subtitle}</div>
					<div class="ui-slider-no-access-img">
						<div class="ui-slider-no-access-img-inner"></div>
					</div>
				</div>
			</div>
		`,
		);
	}

	#getHeaderCard(): HeaderCard
	{
		return new HeaderCard({
			title: Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_TITLE'),
		});
	}

	#getMessageCard(): MessageCard
	{
		return new MessageCard({
			id: 'paymentMessage',
			header: Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_MESSAGE_HEADER'),
			description: Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_MESSAGE_DESCRIPTION'),
			icon: messageIcon,
			restoreState: true,
		});
	}

	#getPaySystemsSelectorForm(): FormSettingsForm
	{
		return this.cache.remember('paySystemsSelectorForm', () => {
			return new FormSettingsForm({
				id: 'paySystemsForm',
				title: Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_GET_PAYMENT'),
				toggleable: true,
				opened: this.options.formOptions.payment.use,
				fields: [
					this.#getPaySystemsSelectorField(),
				],
			});
		});
	}

	#getPaySystemsSelectorField(): PaySystemsSelectorField
	{
		return this.cache.remember('paySystemSelectorField',
			() => new PaySystemsSelectorField({
				id: 'paySystemsSelector',
				selector: 'paySystemsSelector',
				disabledPaySystems: [...this.options.formOptions.payment.disabledSystems],
				onFetchPaySystemsError: (errors) => this.onFetchPaySystemsError(errors),
				showMorePaySystemsBtn: true,
				morePaySystemsBtnSidePanelPath: this.options.dictionary.payment.moreSystemSliderPath,
			}),
		);
	}

	getValue(): { [p: string]: * }
	{
		const schemeId = Text.toNumber(this.options.formOptions.document.scheme);
		const usePayment = this.#getPaySystemsSelectorForm().isOpened();

		if (!this.#schemeManager.isInvoice(schemeId) && usePayment)
		{
			this.options.formOptions.document.scheme = this.#schemeManager.getSpecularSchemeId(schemeId);
		}

		return super.getValue();
	}

	valueReducer(value)
	{
		const paySystemsSelectorData = value.paySystemsSelector;
		const payment = this.options.formOptions.payment;
		const document = this.options.formOptions.document;

		payment.disabledSystems = paySystemsSelectorData.disabledPaySystems;
		payment.use = this.#getPaySystemsSelectorForm().isOpened();

		if (document.payment)
		{
			document.payment = { ...document.payment, ...payment };
		}
		else
		{
			document.payment = payment;
		}

		return { document };
	}

	onChange(event: BaseEvent): void
	{
		this.emit('onChange', { skipPrepare: true });
	}

	onFetchPaySystemsError(errors): void
	{
		const firstError = errors[0];
		if (firstError.code === this.#moduleNotIncludedErrorCode)
		{
			this.#showErrorPlaceholder(firstError.message);
		}
		else
		{
			this.#showErrorPlaceholder('Network error');
		}
	}
}