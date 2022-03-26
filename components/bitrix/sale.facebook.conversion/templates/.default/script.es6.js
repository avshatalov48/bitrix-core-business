import {Event, Loc, Reflection, Tag} from 'main.core';
import {LoginFactory} from 'seo.ads.login';

class SaleFacebookConversion
{
	constructor(containerId, options)
	{
		this.wrapper = document.getElementById(containerId);
		this.eventName = options.eventName;
		this.facebookBusinessParams = options.facebookBusinessParams;
		this.shops = options.shops;
		this.conversionDataLabelsText = options.conversionDataLabelsText;
		this.title = options.title;

		if (this.facebookBusinessParams.available)
		{
			this.layout();
		}
		else
		{
			this.layoutError();
		}
	}

	layout()
	{
		this.wrapper.innerHTML = '';
		this.wrapper.appendChild(this.getTitleLayout());
		this.wrapper.appendChild(this.getInformationLayout());

		if (this.facebookBusinessParams.auth && this.facebookBusinessParams.profile)
		{
			this.wrapper.appendChild(this.getFacebookAuthConnectedLayout());

			if (this.shops)
			{
				const shopsContainer = Tag.render`<div class="facebook-conversion-shops-container"></div>`;
				for (let shopId in this.shops)
				{
					const paramsContainer = this.getParamsContainerLayout(shopId);
					const switcherContainer = this.getSwitcherContainerLayout(shopId, paramsContainer);
					const shopContainer = this.getShopContainerLayout(shopId, switcherContainer, paramsContainer);

					shopsContainer.appendChild(shopContainer);
				}
				this.wrapper.appendChild(shopsContainer);
			}
		}
		else
		{
			this.wrapper.appendChild(this.getFacebookAuthDisconnectedLayout());
		}
	}

	getShopContainerLayout(shopId, switcherContainer, paramsContainer)
	{
		const shopName = this.shops[shopId].name;
		return Tag.render`
			<div>
				<div class="facebook-conversion-shop-container">
					<div class="facebook-conversion-shop-name">
						${Tag.safe`${shopName}`}
					</div>
					<div>
						${switcherContainer}
					</div>
				</div>
				${paramsContainer}
			</div>
		`;
	}

	notify(message: string)
	{
		BX.UI.Notification.Center.notify({
			content: message,
			autoHideDelay: 5000,
		});
	}

	getSwitcherContainerLayout(shopId, paramsContainer)
	{
		const switcherContainer = Tag.render`<span></span>`;
		const switcher = new BX.UI.Switcher({
			node: switcherContainer,
			checked: this.shops[shopId].enabled === 'Y',
		});

		switcher.handlers = {
			checked: this.changeShopEnabledState.bind(this, shopId, 'N', paramsContainer),
			unchecked: this.changeShopEnabledState.bind(this, shopId, 'Y', paramsContainer),
		};

		return switcherContainer;
	}

	changeShopEnabledState(shopId, state, paramsContainer)
	{
		this.shops[shopId].enabled = state;
		BX.ajax.runComponentAction('bitrix:sale.facebook.conversion',
			'changeShopEnabledState',
			{
				mode: 'class',
				data: {
					eventName: this.eventName,
					shopId: shopId,
					enabled: state,
				},
			}
		).then(() => {
			this.notify(Loc.getMessage('FACEBOOK_CONVERSION_SAVE_SUCCESS'));
			paramsContainer.style.display = state === 'Y' ? 'block' : 'none';
		}).catch(() => {
			this.notify(Loc.getMessage('FACEBOOK_CONVERSION_SAVE_ERROR'));
		});
	}

	getParamsContainerLayout(shopId)
	{
		const params = this.shops[shopId].params;
		const enabled = this.shops[shopId].enabled;
		const paramsContainer = Tag.render`<div class="facebook-conversion-params-container"></div>`;
		paramsContainer.style.display = enabled === 'Y' ? 'block' : 'none';

		for (let paramName in params)
		{
			const param = params[paramName];
			const isNeedToDisableParam = paramName === 'id' || paramName === 'ids';

			const checkbox = Tag.render`
				<input
					id="${shopId + '_' + paramName}"
					type="checkbox"
					class="ui-ctl-element"
					${param === 'Y' ? 'checked' : ''}
				>
			`;
			checkbox.disabled = isNeedToDisableParam;
			Event.bind(checkbox, 'change', this.onParamCheckboxChange.bind(this, shopId, paramName));
			paramsContainer.appendChild(Tag.render`
				<div>
					<label class="ui-ctl ui-ctl-checkbox">
						${checkbox}
						<span class="ui-ctl-label-text ${isNeedToDisableParam ? 'facebook-conversion-text-disabled' : ''}">
							${this.conversionDataLabelsText[paramName]}
						</span>
					</label>
				</div>
			`);
		}

		return paramsContainer;
	}

	onParamCheckboxChange(shopId, paramName, event)
	{
		const checked = event.currentTarget.checked;

		const dependedParamId = event.currentTarget.dataset.dependedParamId;
		if (dependedParamId)
		{
			this.changeDependedParamCheckboxState(dependedParamId, checked);
		}

		this.shops[shopId].params[paramName] = checked ? 'Y' : 'N';

		BX.ajax.runComponentAction('bitrix:sale.facebook.conversion',
			'changeParamState',
			{
				mode: 'class',
				data: {
					eventName: this.eventName,
					shopId: shopId,
					paramName: paramName,
					state: checked ? 'Y' : 'N',
				},
			}
		).then(() => {
			this.notify(Loc.getMessage('FACEBOOK_CONVERSION_SAVE_SUCCESS'));
		}).catch(() => {
			this.notify(Loc.getMessage('FACEBOOK_CONVERSION_SAVE_ERROR'));
		});
	}

	changeDependedParamCheckboxState(dependedParamId, isCheckedRequiredCheckbox)
	{
		const dependedCheckbox = document.getElementById(dependedParamId);
		if (dependedCheckbox)
		{
			dependedCheckbox.disabled = !isCheckedRequiredCheckbox;
			const parentCheckboxNode = dependedCheckbox.parentNode;
			const checkboxText = parentCheckboxNode.querySelector('.ui-ctl-label-text');
			checkboxText.className =
				dependedCheckbox.disabled
					? 'ui-ctl-label-text facebook-conversion-text-disabled'
					: 'ui-ctl-label-text'
			;
		}
	}

	getTitleLayout()
	{
		return Tag.render`
			<p class="facebook-conversion-event-title">
				${this.title}
			</p>
		`;
	}

	getInformationLayout()
	{
		return Tag.render`
			<div class="facebook-conversion-information-container">
				<div class="facebook-conversion-logo-container">
					<div class="facebook-conversion-logo">
					</div>
				</div>
				<div>
					<div class="facebook-conversion-description">
						${Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION')}
					</div>
					<ol class="facebook-conversion-description-list">
						<li>${Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_GIVE_EVENTS')}</li>
						<li>${Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_GIVE_CLIENT_ACTIONS')}</li>
						<li>${Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_MAKE_AD_AUDIENCES')}</li>
						<li>${Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_SHOW_AD')}</li>
					</ol>
					<div>
						<a 
							href="https://www.facebook.com/business/help/1292598407460746?id=1205376682832142" 
							class="facebook-conversion-info" 
							target="_blank"
						>
							${Loc.getMessage('FACEBOOK_CONVERSION_DESCRIPTION_INFO')}
						</a>
					</div>
				</div>
			</div>
		`;
	}

	getFacebookAuthDisconnectedLayout()
	{
		return Tag.render`
			<div class="facebook-conversion-auth-container-disconnected">
				<div class="facebook-conversion-auth-container-connect-title">
					${Loc.getMessage('FACEBOOK_CONVERSION_CONNECT_TITLE')}
				</div>
				<div class="facebook-conversion-auth-connect-container">
					<a
						class="ui-btn ui-btn-light-border"
						onclick="${this.login.bind(this)}"
					>
						${Loc.getMessage('FACEBOOK_CONVERSION_CONNECT')}
					</a>
					<span class="facebook-conversion-auth-connect-info">
						${Loc.getMessage('FACEBOOK_CONVERSION_CONNECT_INFO')}
					</span>
				</div>
			</div>
		`;
	}

	login()
	{
		LoginFactory.getLoginObject({
			'TYPE' : 'facebook',
			'ENGINE_CODE' : 'business.facebook'
		}).login();
	}

	getFacebookAuthConnectedLayout()
	{
		return Tag.render`
			<div class="facebook-conversion-auth-container-connected">
				<div class="facebook-conversion-auth-social-avatar">
					<div
						class="facebook-conversion-auth-social-avatar-icon"
						style="background-image: url(${Tag.safe`
							${this.facebookBusinessParams.profile.picture}
						`})"
					>
					</div>
				</div>
				<div class="facebook-conversion-auth-social-user">
					<a
						${
							this.facebookBusinessParams.profile.url
								? 'href="' + Tag.safe`${this.facebookBusinessParams.profile.url}` + '"'
								: ''
						}
						target="_top"
						class="facebook-conversion-auth-social-user-link"
					>
						${Tag.safe`${this.facebookBusinessParams.profile.name}`}
					</a>
				</div>
				<div class="facebook-conversion-auth-social-disconnect">
					<span
						class="facebook-conversion-auth-social-disconnect-link"
						onclick="${this.logout.bind(this)}"
					>
						${Loc.getMessage('FACEBOOK_CONVERSION_DISCONNECT')}
					</span>
				</div>
			</div>
		`;
	}

	logout()
	{
		BX.ajax.runComponentAction(
			'bitrix:sale.facebook.conversion',
			'logout',
			{
				mode: 'class',
				analyticsLabel: {
					connect: 'FBE',
					action: 'disconnect',
					type: 'disconnect'
				}
			}
		).then(() => {
			document.location.reload();
		}).catch(() => {
			document.location.reload();
		});
	}

	layoutError()
	{
		const errorNode = Tag.render`
			<div class="ui-slider-no-access">
				<div class="ui-slider-no-access-inner">
					<div class="ui-slider-no-access-title">
						${Loc.getMessage('FACEBOOK_CONVERSION_NOT_AVAILABLE')}
					</div>
					<div class="ui-slider-no-access-img">
						<div class="ui-slider-no-access-img-inner"></div>
					</div>
				</div>
			</div>
		`;
		this.wrapper.appendChild(errorNode);
	}
}

Reflection.namespace('BX').SaleFacebookConversion = SaleFacebookConversion;
