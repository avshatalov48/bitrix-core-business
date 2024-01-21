import {Loc, Runtime} from 'main.core';
import {Login} from "./login";
import FacebookLoginComponent from './view/facebooklogincomponent.js';
import 'ui.dialogs.messagebox';
import './style.css'
import 'sidepanel';
import 'ui.sidepanel.layout';

export class FacebookLogin extends Login
{
	login()
	{
		BX.SidePanel.Instance.open('seo-fbe-install',{
			contentCallback: slider => {
					return BX.UI.SidePanel.Layout.createContent({
					title: Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TITLE_MSGVER_1'),
					extensions:['seo.ads.login', 'ui.forms'],
					design:{ section: false },
					content()
					{
						return BX.ajax.runAction(
							'seo.api.business.setup.default',
							{
									data: {},
									analyticsLabel: {
										connect: "FBE",
										action: "connection_configuration",
										type: "connection"
									}
							})
							.then( response => slider.getData().set('setup',response.data) ?? true )
							.then(() => BX.ajax.runAction('seo.api.business.config.default', {data:{}}))
							.then( response => slider.getData().set('config',response.data) ?? true )
							.then(() => {
								slider.getData().set('COMPONENT_KEY', new FacebookLoginComponent({
									propsData: {
										defaultSetup: slider.getData().get('setup'),
										defaultConfig: slider.getData().get('config'),
									}
								}).$mount());
								return slider.getData().get('COMPONENT_KEY').$el;
							});
					},
					buttons:({cancelButton, SaveButton}) =>
					{
						return [
							new SaveButton({
								onclick: () => this.submit(),
								text: Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_SUBMIT_BUTTON'),
							}),
							cancelButton,
						];
					},
				});
			},
			title: Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TITLE_MSGVER_1'),
			width: (BX.SidePanel.Instance.getTopSlider()?.getWidth() ?? 850),
			cacheable: false
		});
	}
	reject()
	{
		BX.SidePanel.Instance.getSlider('seo-fbe-install')?.close();
	}
	submit()
	{
		let slider = BX.SidePanel.Instance.getSlider('seo-fbe-install');
		if (slider && slider.getData().has('COMPONENT_KEY'))
		{
			slider.close();
			if(slider.getData().get('COMPONENT_KEY').validate())
			{
				this.servicePopup = BX.util.popup('',800,600);
				BX.ajax.runAction('seo.api.business.extension.install', {
					data: {
						engineCode: this.provider.ENGINE_CODE,
						setup: slider.getData().get('COMPONENT_KEY').getSetup(),
						config: slider.getData().get('COMPONENT_KEY').getConfig()
					},
					analyticsLabel: {
						connect: "FBE",
						action: "connection_start",
						type: "connection"
					}
				}).then((response) => {
						if(response && response.data && response.data.authUrl)
						{
							this.servicePopup.location = response.data.authUrl;
						}
					},
					(response) => {
						this.servicePopup.close();
						BX.UI.Dialogs.MessageBox.alert(
							Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ERROR_CONTENT'),
							Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ERROR_TITLE')
						);
					});
			}
			else
			{

				slider.getData().get('COMPONENT_KEY').alert(
					Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ERROR_TITLE'),
					Loc.getMessage('SEO_ADS_FACEBOOK_BUSINESS_LOGIN_FIELDS_ERROR_CONTENT'),
					(messageBox) => {
						messageBox.close();
						this.login();
					}
				);
			}
		}
	}
}