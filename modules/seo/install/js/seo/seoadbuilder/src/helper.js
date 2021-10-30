import { PopupManager } from 'main.popup';
import { Button } from 'ui.buttons';
import { SeoAccount } from './seoaccount';
import { Loc, Tag, Type } from "main.core";

export class Helper
{
	_instance: Helper;

	constructor(seoAccount: SeoAccount): Helper
	{
		this.provider = seoAccount.provider;
		this.clientId = seoAccount.clientId;
		this.clientSelector = seoAccount.clientSelector;
		this.clientNode = seoAccount.clientNode;
		this.avatarNode = seoAccount.avatarNode;
		this.linkNode = seoAccount.linkNode;
		this.seoAccount = seoAccount;
		this.signedParameters = seoAccount.signedParameters;
		this.containerNode = BX('crm-ads-new-campaign');

		this.mess = {
			errorAction: Loc.getMessage('UI_HELPER_ERROR_MSG'),
			dlgBtnClose: Loc.getMessage('UI_HELPER_BUTTON_CLOSE')

		}

		return this;
	}


	setProvider(value)
	{
		this.provider = value;
	}

	static getCreated(): Helper
	{
		if(this._instance === undefined)
		{
			return null;
		}
		return this._instance
	}

	static getInstance(seoAccount: SeoAccount, signedParameters): Helper
	{
		if(this._instance === undefined)
		{
			this._instance = new Helper(seoAccount, signedParameters)
		}

		return this._instance;
	}

	request(action, requestData, callback, analytics)
	{
		requestData.action = action;
		requestData.type = this.seoAccount.provider.TYPE;
		requestData.clientId = this.seoAccount.clientId;

		this.sendActionRequest(
			action,
			requestData,
			(response) => this.onResponse(response, callback),
			null,
			analytics || {}
		);
	}

	onResponse(response, callback)
	{
		if (!response.error)
		{
			callback.apply(this, [response.data]);
		}
	}

	sendActionRequest(action, data, callbackSuccess, callbackFailure, analytics)
	{
		callbackSuccess = callbackSuccess || null;
		callbackFailure = callbackFailure || BX.proxy(this.showErrorPopup, this);
		data = data || {};
		analytics = analytics || {};

		BX.ajax.runComponentAction(
			this.seoAccount.componentName,
			action,
			{
				mode: 'class',
				signedParameters: this.signedParameters,
				data: data,
				analyticsLabel: analytics
			})
		.then(
			response => {
				const data = response.data || {};
				if (data.error)
				{
					callbackFailure.apply(this, [data]);
				}
				else if (callbackSuccess)
				{
					callbackSuccess.apply(this, [data]);
				}
			},
			() => {
				const data = { 'error': true, 'text': '' };
				callbackFailure.apply(this, [data]);
			}
		);
	}

	showErrorPopup(data)
	{
		console.log(data);
		const text = data.text || this.mess.errorAction;

		const popup = PopupManager.create({
				id: 'crm_ads_rtg_error',
				autoHide: true,
				lightShadow: true,
				closeByEsc: true,
				overlay: { backgroundColor: 'black', opacity: 500 },
				events: {
					'onPopupClose': this.onErrorPopupClose.bind(this)
				},
				buttons: [
					new Button({
						text: 'close' || this.mess.dlgBtnClose,
						events: {
							click: function() {
								popup.close();
							}
						}
					})
				],
			}
		);

		popup.setContent( `<span class="crm-ads-rtg-warning-popup-alert">${text}</span>`)

		popup.show();
	}

	onErrorPopupClose()
	{
		if (this.clientSelector)
		{
			this.clientSelector.enable();
		}
	}


	showBlock(blockCodes)
	{
		blockCodes = Type.isArray(blockCodes) ? blockCodes : [blockCodes];
		const attributeBlock = 'data-bx-ads-block';
		const blockNodes = [...this.containerNode.querySelectorAll('[' + attributeBlock + ']')];
		blockNodes.forEach(blockNode => {
			const code = blockNode.getAttribute(attributeBlock);
			const isShow = blockCodes.includes(code);
			blockNode.style.display = isShow ? (blockNode.dataset.flex?'flex':'block') : 'none';
		}, this);
	}

	showBlockRefresh()
	{
		this.showBlock(['auth', 'refresh']);
	}

	showBlockLogin()
	{
		this.showBlock('login');

		const btn = BX('seo-ads-login-btn');
		if (btn && this.provider && this.provider.AUTH_URL)
		{
			btn.setAttribute(
				'onclick',
				'BX.util.popup(\'' + this.provider.AUTH_URL + '\', 800, 600);'
			);
		}
		if (this.clientNode)
		{
			this.clientNode.value = "";
		}
	}

	showBlockMain()
	{
		if (this.avatarNode)
		{
			this.avatarNode.style['background-image'] = 'url(' + this.provider.PROFILE.PICTURE + ')';
		}
		if (this.nameNode)
		{
			this.nameNode.innerText = this.provider.PROFILE.NAME;
		}
		if (this.linkNode)
		{
			if (this.provider.PROFILE.LINK)
			{
				this.linkNode.setAttribute('href', this.provider.PROFILE.LINK);
			}
			else
			{
				this.linkNode.removeAttribute('href');
			}
		}
		if (this.clientNode)
		{
			this.clientNode.value =
				this.provider.PROFILE && this.provider.PROFILE.CLIENT_ID ?
					this.provider.PROFILE.CLIENT_ID :
					"";
		}

		this.showBlock(['auth', 'main']);

		this.seoAccount.loadSettings();
	}


	showBlockByAuth()
	{
		if (this.provider.HAS_AUTH)
		{
			this.showBlockMain();
		}
		else
		{
			this.showBlockLogin();
		}
	}

	fillDropDownControl(node, items)
	{
		items = items || [];
		node.innerHTML = '';
		items.forEach(item => {
			if (!item || !item.caption)
			{
				return;
			}

			const option =
				Tag.render`<option value='${item.value}' selected='${!!item.selected}'>${item.caption}</option>`;

			if(item.currency)
			{
				option.dataset.currency = item.currency;
			}

			if(item.pageId)
			{
				option.dataset.pageId = item.pageId;
			}

			if(item.actorId)
			{
				option.dataset.actorId = item.actorId;
			}

			node.appendChild(option);
		});
	}
}