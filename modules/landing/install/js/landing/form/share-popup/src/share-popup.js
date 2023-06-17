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
							title: Loc.getMessage('LANDING_FORM_SHARE__HELP_TITLE'),
							icon: {
								className: 'landing-form-features-help-icon',
							},
							link: {
								label: Loc.getMessage('LANDING_FORM_SHARE__HELP_LINK_LABEL'),
								onClick: () => {
									const Feedback = Reflection.getClass('BX.UI.Feedback');
									if (!Type.isNil(Feedback))
									{
										Feedback.Form.open({
											id: 'form-editor-feedback-form',
											portalUri: 'https://bitrix24.team',
											forms: [
												{id: 1847, lang: 'ru', sec: 'bbih83', zones: ['ru']},
												{id: 1852, lang: 'kz', sec: 'dtw568', zones: ['kz']},
												{id: 1851, lang: 'by', sec: 'nnz05i', zones: ['by']},
												{id: 1855, lang: 'en', sec: '6lxt2y', zones: ['en', 'eu', 'in', 'uk']},
												{id: 1856, lang: 'de', sec: '574psk', zones: ['de']},
												{id: 1857, lang: 'la', sec: '9tlqqk', zones: ['es', 'mx', 'co']},
												{id: 1858, lang: 'br', sec: '9ptdnu', zones: ['com.br']},
												{id: 1859, lang: 'pl', sec: 'aynrqw', zones: ['pl']},
												{id: 1860, lang: 'fr', sec: 'ld3bh8', zones: ['fr']},
												{id: 1861, lang: 'it', sec: '1rlv2j', zones: ['it']},
												{id: 1862, lang: 'vn', sec: '5m169k', zones: ['vn']},
												{id: 1863, lang: 'tr', sec: '2mc2tg', zones: ['com.tr']},
											],
											defaultForm: {id: 1855, lang: 'en', sec: '6lxt2y'}
										});
									}
								},
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