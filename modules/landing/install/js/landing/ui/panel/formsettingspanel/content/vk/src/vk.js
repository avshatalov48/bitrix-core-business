import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc} from 'landing.loc';
import {BaseCard} from 'landing.ui.card.basecard';
import {Dom} from 'main.core';
import {BaseEvent} from 'main.core.events';
import {type FormOptions} from 'crm.form.type';
import {MessageCard} from 'landing.ui.card.messagecard';
import {Integration} from 'crm.form.integration';

export default class VkContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.VkContent');

		this.addItem(
			new HeaderCard({
				title: Loc.getMessage('LANDING_SIDEBAR_BUTTON_VK'),
			}),
		);

		if (!this.options.dictionary.integration.canUse)
		{
			this.addItem(
				new MessageCard({
					header: Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_HEADER'),
					description: Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_VK_TEXT'),
					angle: false,
					closeable: false,
				}),
			);

			return;
		}

		const buttonCard = new BaseCard();
		Dom.style(buttonCard.getLayout(), {
			padding: 0,
			margin: 0,
		});

		const integration = new Integration({
			type: 'vkontakte',
			form: this.options.formOptions,
			fields: this.options.crmFields,
			dictionary: this.options.dictionary,
		});
		integration.subscribe('change', this.onChange.bind(this));
		Dom.append(
			integration.render(),
			buttonCard.getBody()
		);
		this.addItem(buttonCard);
	}

	prepareButtonText(formOptions: FormOptions)
	{
		const enabled = formOptions.integration.cases.some((item) => {
			return item.providerCode === 'vkontakte';
		});

		if (enabled)
		{
			return Loc.getMessage('LANDING_FORM_SETTINGS_VK_BUTTON_ENABLED');
		}

		return Loc.getMessage('LANDING_FORM_SETTINGS_VK_BUTTON');
	}

	valueReducer(value: {[key: string]: any}): {[key: string]: any}
	{
		return {
			integration: this.options.formOptions.integration,
		};
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {skipPrepare: false});
	}

	getData()
	{
		return this.options.formOptions.integration.cases.filter(data => data.providerCode === 'vkontakte')[0] || null;
	}
}