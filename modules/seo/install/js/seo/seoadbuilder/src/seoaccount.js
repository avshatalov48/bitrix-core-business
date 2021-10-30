import { Helper } from './helper';
import { type AccountFields } from './types/account';
import {LoginFactory} from "seo.ads.login";

export class SeoAccount
{
	_helper: Helper;
	constructor(options: AccountFields): SeoAccount
	{
		this.clientNode = options.clientNode;
		this.avatarNode = options.avatarNode;
		this.accountNode = options.accountNode;
		this.instagramAccountNode = options.instagramAccountNode;
		this.linkNode = options.linkNode;
		this.provider = options.provider;
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;
		this.uiNodes = options.uiNodes;
		this._helper = Helper.getInstance(this, []);
		this.loaded = [];

		this.clientSelector = new BX.Seo.Ads.ClientSelector(options.clientBlock, {
			selected: this.provider.PROFILE,
			items: this.provider.CLIENTS,
			canAddItems: true,
			events: {
				onNewItem: () => {
					LoginFactory.getLoginObject(this.provider).login();
				},
				onSelectItem: item => {
					this.setProfile(item);
				},
				onRemoveItem: item => {
					this.logout(item.CLIENT_ID);
				}
			}
		});

		return this;
	}

	listenSeoAuth()
	{
		BX.addCustomEvent(
			window,
			'seo-client-auth-result',
			BX.proxy(this.onSeoAuth, this)
		);
	}

	onSeoAuth(eventData)
	{
		eventData.reload = false;
		this.getProvider(eventData.clientId);
	}

	logout(clientId)
	{
		const analyticsLabel =
			!(this.provider.TYPE === "facebook" || this.provider.TYPE === "instagram")
				? {}
				: {
					connect: "FBE",
					action: "disconnect",
					type: "disconnect"
				}
		;

		this._helper.showBlock('loading');
		this._helper.request(
			'logout',
			{logoutClientId: clientId},
			provider =>
				{
					this.provider = provider;
					if (this.clientSelector)
					{
						this.clientSelector.setSelected(this.provider.PROFILE);
						this.clientSelector.setItems(this.provider.CLIENTS);
					}
					this._helper.setProvider(provider);
					this._helper.showBlockByAuth();
				},
			analyticsLabel
		);
	}

	getProvider(clientId)
	{
		this.showBlock('loading');
		this.request('getProvider', {}, provider => {
			this.provider = provider;

			if (this.clientSelector)
			{
				if (!this.provider.PROFILE ||
					(clientId && clientId !== this.provider.PROFILE.CLIENT_ID)
				)
				{
					// set PROFILE equal to clientId or first record from CLIENTS:
					for (let i = 0; i < this.provider.CLIENTS.length; i++)
					{
						let client = this.provider.CLIENTS[i];

						if (!clientId || clientId.toString() === client.CLIENT_ID.toString())
						{
							this.setProfile(client);
							break;
						}
					}
				}
				this.clientSelector.setSelected(this.provider.PROFILE);
				this.clientSelector.setItems(this.provider.CLIENTS);
			}
			this.showBlockByAuth();
		});
	}

	loadAccounts(type)
	{
		// this.loader.forAccount(true);
		if (this.clientSelector)
		{
			this.clientSelector.disable();
		}

		this._helper.request('getAccounts', {}, data => {
				if (this.clientSelector)
				{
					this.clientSelector.enable();
				}

				this.uiNodes.accountNotice.ad.style.display = 'none';
				if (!data.length)
				{
					this.uiNodes.accountNotice.ad.style.display = 'block';
					return;
				}
				const dropDownData = data.map(accountData => {
					return {
						caption: accountData.name,
						value: accountData.id,
						selected: accountData.id === this.accountId,
						currency: accountData.currency
					};
				}, this);

				this._helper.fillDropDownControl(this.accountNode, dropDownData);
				if (dropDownData.length > 0)
				{
					setTimeout(() => {
						BX.fireEvent(this.accountNode, 'change');
					}, 150);
				}
				this.accountNode.disabled = false

			}
		);
	}

	loadInstagramAccounts(type)
	{
		if (this.clientSelector)
		{
			this.clientSelector.disable();
		}

		this._helper.request('getInstagramAccounts', {}, data => {
				if (this.clientSelector)
				{
					this.clientSelector.enable();
				}

				this.uiNodes.accountNotice.instagram.style.display = 'none';
				if (!data.length)
				{
					this.uiNodes.accountNotice.instagram.style.display = 'block';
					return;
				}
				const dropDownData = data.map(accountData => {
					return {
						caption: accountData.name,
						value: accountData.id,
						pageId: accountData.page_id,
						actorId: accountData.actor_id
					};
				}, this);

				this._helper.fillDropDownControl(this.instagramAccountNode, dropDownData);
				if (dropDownData.length > 0)
				{
					setTimeout(() => {
						BX.fireEvent(this.instagramAccountNode, 'change');
					}, 150);
				}
				else
				{

				}

				this.instagramAccountNode.disabled = false;
			}
		);
	}

	loadSettings()
	{
		this.instagramAccountNode.disabled  = true;
		this.accountNode.disabled  = true;

		const type = this.provider.TYPE;
		const isSupportAccount = this.provider.IS_SUPPORT_ACCOUNT;

		if (!this.provider.PROFILE)
		{
			return;
		}

		if (!this.loaded.includes(type))
		{
			this.loaded.push(type);
		}

		if (this.accountNode && isSupportAccount)
		{
			this.loadAccounts();
			this.loadInstagramAccounts();
		}
	}

	setProfile(item)
	{
		this.clientId = item && item.CLIENT_ID ? item.CLIENT_ID : null;
		this.provider.PROFILE = item;
		this.accountId = null;
		this.pageId = null;

		if(this.clientSelector.selected)
		{
			this._helper.showBlockMain();
		}

		this.clientSelector.setSelected(item);
	}
}