import {Cache, Reflection, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {FeaturesPopup} from 'landing.features-popup';
import {Loc} from 'landing.loc';
import {PageObject} from 'landing.pageobject';
import {Env} from 'landing.env';
import {Embed} from 'crm.form.embed';
import 'ui.feedback.form';
import {PhoneVerify} from 'bitrix24.phoneverify';

import './css/style.css';

const PHONE_VERIFY_FORM_ENTITY = 'crm_webform';

/**
 * @memberOf BX.Landing.Form
 */
export class SharePopup extends EventEmitter
{
	#cache = new Cache.MemoryCache();

	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Landing.Form.SharePopup');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);
	}

	setOptions(options)
	{
		this.#cache.set('options', {...options});
	}

	getOptions(): {[key: string]: any}
	{
		return this.#cache.get('options', {});
	}

	#getFeaturesPopup(): FeaturesPopup
	{
		return this.#cache.remember('featuresPopup', () => {
			return new FeaturesPopup({
				bindElement: this.getOptions().bindElement,
				items: [
					{
						id: 'share',
						title: Loc.getMessage('LANDING_FORM_SHARE__SHARE_TITLE'),
						theme: FeaturesPopup.Themes.Highlight,
						icon: {
							className: 'landing-form-features-share-icon',
						},
						link: {
							label: Loc.getMessage('LANDING_FORM_SHARE__SHARE_LINK_LABEL'),
							onClick: () => {
								if (!Type.isNil(BX.Helper))
								{
									BX.Helper.show('redirect=detail&code=13003062');
								}
							},
						},
						actionButton: {
							label: Loc.getMessage('LANDING_FORM_SHARE__SHARE_ACTION_LABEL'),
							onClick: () => {
								const editorWindow = PageObject.getEditorWindow();
								const {formEditorData} = editorWindow.BX.Landing.Env.getInstance().getOptions();
								if (
									Type.isPlainObject(formEditorData)
									&& Type.isPlainObject(formEditorData.formOptions)
								)
								{
									if (this.getOptions()?.phoneVerified)
									{
										Embed.openSlider(formEditorData.formOptions.id);
									}
									else
									{
										this.#showPhoneVerifySlider(formEditorData.formOptions.id).then((verified) => {
											if (verified)
											{
												Embed.openSlider(formEditorData.formOptions.id);
											}
										});
									}
								}
							},
						},
					},
					{
						id: 'communication',
						title: Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_TITLE'),
						icon: {
							className: 'landing-form-features-communication-icon',
						},
						link: {
							label: Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_LINK_LABEL'),
							onClick: () => {
								if (!Type.isNil(BX.Helper))
								{
									BX.Helper.show('redirect=detail&code=6986667');
								}
							},
						},
						actionButton: {
							label: Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_ACTION_LABEL'),
							onClick: () => {
								const {landingParams} = PageObject.getRootWindow();
								if (
									!Type.isNil(landingParams)
									&& Type.isStringFilled(landingParams.PAGE_URL_LANDING_SETTINGS)
								)
								{
									const SidePanel: BX.SidePanel = Reflection.getClass('BX.SidePanel');
									if (!Type.isNil(SidePanel))
									{
										SidePanel.Instance.open(
											`${landingParams['PAGE_URL_LANDING_SETTINGS']}#b24widget`,
										);
									}
								}
							},
						},
					},
					[
						{
							id: 'help',
							title: Loc.getMessage('LANDING_FORM_SHARE__HELP_TITLE_MSGVER_1'),
							icon: {
								className: 'landing-form-features-help-icon',
							},
							link: {
								label: Loc.getMessage('LANDING_FORM_SHARE__HELP_LINK_LABEL_MSGVER_1'),
								onClick: () => {},
							},
							onClick: () => {
								BX.UI.Feedback.Form.open({
									id: 'form-editor-feedback-form',
									portalUri: this.getOptions().portalUri,
									forms: this.getOptions().forms,
									presets: {
										source: 'landing',
									},
								});
							},
						},
						{
							id: 'settings',
							icon: {
								className: 'landing-form-features-settings-icon',
							},
							onClick: () => {
								const {landingParams} = PageObject.getRootWindow();
								if (
									!Type.isNil(landingParams)
									&& Type.isStringFilled(landingParams.PAGE_URL_LANDING_SETTINGS)
								)
								{
									const SidePanel: BX.SidePanel = Reflection.getClass('BX.SidePanel');
									if (!Type.isNil(SidePanel))
									{
										SidePanel.Instance.open(landingParams['PAGE_URL_LANDING_SETTINGS']);
									}
								}
							},
						},
					],
				],
			});
		});
	}

	show()
	{
		this.#getFeaturesPopup().show();
	}

	hide()
	{
		this.#getFeaturesPopup().hide();
	}

	#showPhoneVerifySlider(formId: number): Promise
	{
		if (typeof PhoneVerify !== 'undefined')
		{
			return PhoneVerify.getInstance()
				.setEntityType(PHONE_VERIFY_FORM_ENTITY)
				.setEntityId(formId)
				.startVerify({
					sliderTitle: Loc.getMessage('LANDING_FORM_PHONE_VERIFY_CUSTOM_SLIDER_TITLE'),
					title: Loc.getMessage('LANDING_FORM_PHONE_VERIFY_CUSTOM_TITLE'),
					description: Loc.getMessage('LANDING_FORM_PHONE_VERIFY_CUSTOM_DESCRIPTION'),
				});
		}
		return Promise.resolve(true);
	}
}
