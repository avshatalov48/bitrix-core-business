import {SeoAccount} from "./seoaccount";
import {ProductSelector} from 'catalog.product-selector';
import {Event, Loc, Tag, Text} from "main.core";
import {EventEmitter} from 'main.core.events';
import { type AdBuilderOptions } from './types/adbuilderoptions';
import { TagSelector } from 'ui.entity-selector';

export class SeoAdBuilder
{
	_instance: SeoAdBuilder;
	productSelector: ProductSelector;
	_DEFAULT_CURRENCY = 'RUB';

	_STAGES = {
		accountSelected: 1,
		postSelected: 2,
		pageSelected: 3,
		audienceSelected: 4,
		budgetSelected: 5,
		toModeration: 6
	};

	constructor(options: AdBuilderOptions)
	{
		if (this._instance)
		{
			return this._instance;
		}

		this.optionSelectedClass = 'crm-ads-new-campaign-item-option--selected';
		this.containerId = options.containerId;
		this.provider = options.provider;
		this.context = options.context;
		this.onRequest = options.onRequest;
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;
		this.postListUrl = options.postListUrl;
		this.audienceUrl = options.audienceUrl;
		this.crmAudienceUrl = options.crmAudienceUrl;
		this.pageConfigurationUrl = options.pageConfigurationUrl;
		this.mess = options.mess;
		this.type = options.type;
		this.iBlockId = options.iBlockId;
		this.basePriceId = options.basePriceId;
		this.storeExists = options.storeExists;
		this.isCloud = options.isCloud || false;

		this.clientId = options.clientId;
		this.accountId = options.accountId;
		this.baseCurrency = options.baseCurrency;
		this.arrows = document.querySelectorAll(".crm-ads-new-campaign-item-arrow");

		this.price = [];
		this.price[this._DEFAULT_CURRENCY] = [];
		this.price[this._DEFAULT_CURRENCY]['recommended'] = { duration: 3, value: 100 };
		this.price[this._DEFAULT_CURRENCY]['verified'] = { duration: 3, value: 200 };
		this.price[this._DEFAULT_CURRENCY]['boost'] = { duration: 3, value: 300 };
		this.price[this._DEFAULT_CURRENCY]['confident'] = { duration: 5, value: 500 };

		this.price['USD'] = [];
		this.price['USD']['recommended'] = { duration: 3, value: 50 };
		this.price['USD']['verified'] = { duration: 3, value: 100 };
		this.price['USD']['boost'] = { duration: 3, value: 150 };
		this.price['USD']['confident'] = { duration: 5, value: 200 };

		this.price['EUR'] = [];
		this.price['EUR']['recommended'] = { duration: 3, value: 50 };
		this.price['EUR']['verified'] = { duration: 3, value: 100 };
		this.price['EUR']['boost'] = { duration: 3, value: 150 };
		this.price['EUR']['confident'] = { duration: 5, value: 200 };

		this.completedStages = {};
		this.selectedRegions = {};

		this.loader = {
			init: caller => {
				this.caller = caller;
			},

			change: (loaderNode, inputNode, isShow) => {
				loaderNode.style.display = isShow ? '' : 'none';
				if (inputNode)
				{
					inputNode.disabled = (!inputNode.options.length === 0 || isShow) ? false : true;
				}
			}
		};

		this.init();
	}

	init()
	{
		this._instance = this;
		this.initiateUINodes();
		this.initiateAutoAudienceMode();


		for (let i = this._STAGES.accountSelected; i <= this._STAGES.toModeration; i++)
		{
			this.deActivateStage(i);
		}

		this.initiateAccounts();
		this.activateStage(this._STAGES.audienceSelected);

		this.initiateSwitcher('product');
		this.initiateSwitcher('audience');
		this.initiateSwitcher('budget');

		this.bindEvents()
		this.buildSelector();

		this.storeBlockShow(true);
	}

	reInitAdCreator()
	{
		this.adCreatorData = {};
		this.adCreatorData.audienceConfig = {};
		this.adCreatorData.crmAudienceConfig = {};
	}

	initiateAccounts()
	{
		this.seoAccount = new SeoAccount({
			clientNode: this.uiNodes.clientInput,
			provider: this.provider,
			avatarNode: this.uiNodes.avatar,
			linkNode: this.uiNodes.link,
			accountNode: this.uiNodes.account,
			instagramAccountNode: this.uiNodes.instagramAccount,
			clientBlock: this.uiNodes.clientBlock,
			signedParameters: this.signedParameters,
			componentName: this.componentName,
			uiNodes: this.uiNodes
		});

		this.profileConfigured = false;
		if (!this.clientId && !this.provider.PROFILE)
		{ // use first client by default
			for (let i = 0; i < this.provider.CLIENTS.length; i++)
			{
				this.seoAccount.setProfile(this.provider.CLIENTS[i]);
				this.profileConfigured = true;
				break;
			}
		}

		this.loader.init(this);

		if (this.provider.PROFILE)
		{
			this.activateStage(this._STAGES.accountSelected);
		}

		if(!this.profileConfigured)
		{
			this.seoAccount.setProfile(this.provider.PROFILE);
		}

		this.seoAccount._helper.showBlockByAuth();
	}

	bindEvents()
	{
		Event.bind(this.uiNodes.addPost, 'click', this.openPostSlider.bind(this));

		this.uiNodes.createLinks.forEach(function(createLink) {
			Event.bind(createLink, 'click', BX.proxy(function() {
				if (!this.hasPostLis)
				{
					this.showBlockRefresh();
				}
			}, this));
		}, this);

		Event.bind(this.uiNodes.refreshButton, 'click', BX.proxy(function() {
			this.seoAccount.getProvider();
		}, this));

		if (this.uiNodes.autoRemover.checker)
		{
			Event.bind(this.uiNodes.autoRemover.checker, 'click', () => {
				var autoRemover = this.uiNodes.autoRemover;
				autoRemover.select.disabled = !autoRemover.checker.checked;
			});
		}

		Event.bind(this.uiNodes.logout, 'click', BX.proxy(function() {
			this.seoAccount.logout(this.clientId);
		}, this));

		Event.bind(this.uiNodes.addClientBtn, 'click', BX.proxy(function() {
			BX.util.popup(_this.provider.AUTH_URL, 800, 600);
		}, this));

		this.arrows.forEach(arrow => {
				arrow.addEventListener('click', this.switchCollapsed);
			}
		);

		Event.bind(this.uiNodes.account, 'change', this.checkCurrency.bind(this));
		document.querySelectorAll('.seo-ads-budget-item-block').forEach(div => {
			Event.bind(div, 'click', this.calculateTotal.bind(this));
		});

		document.querySelectorAll('.seo-ads-audience-item-block').forEach(div => {
			Event.bind(div, 'click', this.changeAudienceMode.bind(this));
		});

		document.querySelectorAll('.seo-ads-product-item-block').forEach(div => {
			Event.bind(div, 'click', this.changeProductSelectionMode.bind(this));
		});

		Event.bind(this.uiNodes.audienceExpert, 'click', this.showAudienceExpertModeForm.bind(this));
		Event.bind(this.uiNodes.productExpert, 'click', this.openTargetPageSlider.bind(this));

		Event.bind(this.uiNodes.addProductBtn, 'click', this.toCreateStoreSlider.bind(this));
		Event.bind(this.uiNodes.addCurrencyBtn, 'click', this.addCurrency.bind(this));
		Event.bind(this.uiNodes.toModerationBtn, 'click', this.sendToModeration.bind(this));
	}

	initiateUINodes()
	{
		this.containerNode = BX('crm-ads-new-campaign');
		BX.UI.Hint.init(this.containerNode);

		this.uiNodes = {
			'avatar': this.containerNode.querySelector('[data-bx-ads-auth-avatar]'),
			'name': this.containerNode.querySelector('[data-bx-ads-auth-name]'),
			'link': this.containerNode.querySelector('[data-bx-ads-auth-link]'),
			'logout': this.containerNode.querySelector('[data-bx-ads-auth-logout]'),
			'clientBlock': this.containerNode.querySelector('[data-bx-ads-client]'),
			'clientInput': this.containerNode.querySelector('[data-bx-ads-client-input]'),
			'account': this.containerNode.querySelector('[data-bx-ads-account]'),
			'accountLoader': this.containerNode.querySelector('[data-bx-ads-account-loader]'),
			'instagramAccount': this.containerNode.querySelector('[data-bx-ads-instagram-account]'),
			'instagramAccountLoader': this.containerNode.querySelector('[data-bx-ads-instagram-account-loader]'),
			'errorNotFound': this.containerNode.querySelector('[data-bx-ads-post-not-found]'),
			'addPost': this.containerNode.querySelector('.crm-ads-new-campaign-item-post-new'),
			'addProductBtn': this.containerNode.querySelector('.seo-ads-add-product-btn'),
			'addCurrencyBtn': this.containerNode.querySelector('.seo-ads-currency-apply-btn'),
			'toModerationBtn': this.containerNode.querySelector('.seo-ads-to-moderation-btn'),
			'refreshButton': this.containerNode.querySelector('[data-bx-ads-refresh-btn]'),
			'currencyBlock': document.querySelector('.seo-ads-currency-block'),
			'audienceSummary': document.querySelector('.seo-ads-audience-summary'),
			'createLinks': BX.convert.nodeListToArray(
				this.containerNode.querySelectorAll('[data-bx-ads-post-create-link]')
			),
			'accountNotice': {
				'instagram': this.containerNode.querySelector('.seo-ads-no-ad-account-instagram'),
				'ad': this.containerNode.querySelector('.seo-ads-no-ad-account'),
			},
			'audienceExpert': BX('crm-ads-new-campaign-item-expert-audience'),
			'productExpert': BX('crm-ads-new-campaign-item-expert-product'),
			'budgetExpert': BX('crm-ads-new-campaign-item-expert-budget'),
			'autoRemover': {
				'node': this.containerNode.querySelector('[data-bx-ads-post-auto-remove]'),
				'checker': this.containerNode.querySelector('[data-bx-ads-post-auto-remove-checker]'),
				'select': this.containerNode.querySelector('[data-bx-ads-post-auto-remove-select]')
			},
			'form': {
				'permalink': this.containerNode.querySelector('[data-bx-ads-permalink]'),
				'mediaId': this.containerNode.querySelector('[data-bx-ads-media-id]'),
				'targetUrl': this.containerNode.querySelector('[data-bx-ads-target-url]'),
				'duration': this.containerNode.querySelector('[data-bx-ads-duration]'),
				'page': this.containerNode.querySelector('[data-bx-ads-page-id]'),
				'body': this.containerNode.querySelector('[data-bx-ads-body]'),
				'adsId': this.containerNode.querySelector('[data-bx-ads-id]'),
				'pageId': this.containerNode.querySelector('[data-bx-ads-page-id]'),
				'budget': this.containerNode.querySelector('[data-bx-ads-budget]'),
				'ageFrom': this.containerNode.querySelector('[data-bx-ads-age-from]'),
				'ageTo': this.containerNode.querySelector('[data-bx-ads-age-to]'),
				'genders': this.containerNode.querySelector('[data-bx-ads-genders]'),
				'interests': this.containerNode.querySelector('[data-bx-ads-interests]'),
				'imageUrl': this.containerNode.querySelector('[data-bx-ads-image-url]'),
				'instagramAccountId': this.containerNode.querySelector('[data-bx-ads-actor-id]'),
				'segmentInclude': this.containerNode.querySelector('[data-bx-ads-segment-include]'),
				'segmentExclude': this.containerNode.querySelector('[data-bx-ads-segment-exclude]'),
				'regions': this.containerNode.querySelector('[data-bx-ads-regions]')
			},
			'adsStoreBlock': this.containerNode.querySelectorAll('.seo-ads-store'),
			'addClientBtn': this.containerNode.querySelector('[data-bx-ads-client-add-btn]'),
			'addPostBtn': this.containerNode.querySelector('[data-bx-ads-post-add]')
		};
	}

	initiateSwitcher(id)
	{
		new BX.UI.Switcher({
			node: BX(`crm-ads-new-campaign-item-expert-${id}`),
			size: "small"
		});
	}

	checkCurrency()
	{
		const account = this.uiNodes.account;
		this.usedCurrency = account.options[account.selectedIndex].dataset.currency;
		this.currencyExists(this.usedCurrency);
	}

	calculateTotal(event)
	{
		if(this.checkInstagramAccount())
		{
			return;
		}

		const target = event.target.dataset.type ? event.target : event.target.parentNode;

		const type = target.dataset.type;
		const price = this.price[this.usedCurrency][type];
		const total = price.duration * price.value;

		document.querySelectorAll('.seo-ads-budget-total-value').forEach(element => {
			element.textContent = total;
		});

		document.querySelector('.seo-ads-budget-total-currency').textContent = this.usedCurrency;
		document.querySelector('.seo-ads-budget-total-duration').textContent = price.duration;

		document.querySelector('.seo-ads-total-budget').textContent = total;
		document.querySelector('.seo-ads-total-currency').textContent = this.usedCurrency;
		document.querySelector('.seo-ads-total-duration').textContent = price.duration;

		document.querySelector('.crm-ads-new-campaign-item-cost').style.display = 'block';

		document.querySelectorAll('.seo-ads-budget-item-block').forEach(div => {
			div.classList.remove(this.optionSelectedClass);
		});

		target.classList.add(this.optionSelectedClass);

		this.uiNodes.form.budget.value = total;
		this.uiNodes.form.duration.value = price.duration;
		this.prepareCurrencyBlocks();
		this.activateStage(this._STAGES.budgetSelected);
	}

	checkInstagramAccount()
	{
		if (!this.uiNodes.instagramAccount.value)
		{
			this.scrollToStage(this._STAGES.accountSelected);
			return true;
		}
		return false;
	}

	changeAudienceMode(event)
	{
		if(this.checkInstagramAccount())
		{
			return;
		}

		const target = event.target.dataset.type ? event.target : event.target.parentNode;

		const type = target.dataset.type;

		document.querySelectorAll('.seo-ads-audience-item-block').forEach(div => {
			div.classList.remove(this.optionSelectedClass);
		});

		target.classList.add(this.optionSelectedClass);

		switch (type)
		{
			case 'auto':
				this.initiateAutoAudienceMode();
				break;
			case 'crm':
				this.showCrmAudienceExpertModeForm();
				break;
			case 'expert':
				this.showAudienceExpertModeForm();
				break;
		}
	}

	changeProductSelectionMode(event)
	{
		if(this.checkInstagramAccount())
		{
			return;
		}

		const target = event.target.dataset.type ? event.target : event.target.parentNode;

		const type = target.dataset.type;
		document.querySelectorAll('.seo-ads-product-item-block').forEach(div => {
			div.classList.remove(this.optionSelectedClass);
		});

		target.classList.add(this.optionSelectedClass);

		switch (type)
		{
			case 'auto':
				this.storeBlockShow(true);
				break;
			case 'expert':
				this.openTargetPageSlider();
				break;
		}
	}

	storeBlockShow(isShown)
	{
		this.uiNodes.adsStoreBlock.forEach((element) => {
			if(this.storeExists && element.dataset.type === 'store-not-created')
			{
				return;
			}

			if(!this.storeExists && element.dataset.type !== 'store-not-created')
			{
				return;
			}


			element.style.display = isShown?'block':'none';
		});
	}

	prepareCurrencyBlocks()
	{
		document.querySelectorAll('.seo-ads-current-currency').forEach(element => {
			element.textContent = this.usedCurrency;
		});
	}

	prepareCurrencyBlock(currency = this._DEFAULT_CURRENCY)
	{
		if (!this.price[currency])
		{
			for (const key in this.price[this._DEFAULT_CURRENCY])
			{
				this.convertToCurrency(
					key,
					this._DEFAULT_CURRENCY !== this.baseCurrency ? this.baseCurrency : currency,
					this.price[this._DEFAULT_CURRENCY][key]
				);
			}

			return;
		}

		for (const key in this.price[currency])
		{
			document.querySelector(`.seo-ads-budget-${key}-duration`).textContent = this.price[currency][key].duration;
			document.querySelector(`.seo-ads-budget-${key}-value`).textContent = this.price[currency][key].value;
			document.querySelector(`.seo-ads-budget-${key}-currency`).textContent = currency;
		}
	}

	convertToCurrency(key, targetCurrency, price)
	{
		this.seoAccount._helper.request('convertCurrency', {
				sourceCurrency: this.baseCurrency,
				targetCurrency: targetCurrency,
				amount: price.value
			}, response => {
				const amount = response.amount;

				if (!this.price[targetCurrency])
				{
					this.price[targetCurrency] = [];
				}

				if (!this.price[targetCurrency][key])
				{
					this.price[targetCurrency][key] = { duration: price.duration, value: amount };
				}

				if (Object.keys(this.price[targetCurrency]).length === 4)
				{
					this.prepareCurrencyBlock(targetCurrency);
				}
			}
		);
	}

	currencyExists(currency)
	{
		this.seoAccount._helper.request('checkCurrencyExists', {
				currency: currency
			}, response => {
				const exists = response.exists;

				if (exists === false)
				{
					this.prepareCurrencyBlocks();
					this.uiNodes.currencyBlock.style.display = 'block';
				}

				this.prepareCurrencyBlock(this.usedCurrency);
			}
		);
	}

	addCurrency()
	{
		const count = document.querySelector('.seo-ads-currency-count');
		const course = document.querySelector('.seo-ads-currency-course');
		if (!count.value || !course)
		{
			return;
		}

		this.seoAccount._helper.request('addCurrency', {
				newCurrency: this.usedCurrency,
				course: course.value,
				amountCnt: count.value
			}, response => {
				const success = response.success;

				if (success === false)
				{
					return;
				}

				this.uiNodes.currencyBlock.style.display = 'none';
				delete (this.price[this.usedCurrency]);
				this.prepareCurrencyBlock(this.usedCurrency);
			}
		);
	}

	switchCollapsed(event)
	{
		const block = event.target.closest('.crm-ads-new-campaign-item');
		const content = block.querySelector('.crm-ads-new-campaign-item-content');

		if (block.classList.contains('crm-ads-new-campaign-item--hide'))
		{
			block.classList.remove('crm-ads-new-campaign-item--hide');
			content.style.height = content.scrollHeight + 'px';
		}
		else
		{
			block.classList.add('crm-ads-new-campaign-item--hide');
			content.style.height = content.scrollHeight + 'px';
			setTimeout(() => content.style.height = '0');
		}
	}

	clipTitle(title)
	{
		if (!title)
		{
			return;
		}
		const text = title.textContent;
		const nodeHeight = 20;
		BX.cleanNode(title);

		const titleInner = BX.create("span", {
			text: text
		});
		title.appendChild(titleInner);

		let a = 0;
		while (titleInner.offsetHeight > nodeHeight && text.length > a)
		{
			a = a + 1;
			titleInner.innerText = text.slice(0, -a) + '...';
		}
	}

	onPostSelected(event)
	{
		if (event.eventId === "seo-ads-post-selected" && event.data)
		{
			if (!event.data.media_url)
			{
				this.deActivateStage(this._STAGES.postSelected);
				return;
			}

			const postItem = Tag.render` 
			<div class="crm-ads-new-campaign-item-post">
			   <div class="crm-ads-new-campaign-item-post-img" 
					style="background-image: url(${event.data.media_url})">
			   </div>
			   <span class="crm-ads-new-campaign-item-post-text">${Text.encode(event.data.caption||'')}</span>
			   <span class="crm-ads-new-campaign-item-post-delete"></span>
			</div>
			`;

			const postListNode = document.querySelector('.crm-ads-new-campaign-item-posts');
			const addNewNode = document.querySelector('.crm-ads-new-campaign-item-post-new');
			const previewNode = document.querySelector('.crm-ads-new-campaign-item-total-preview-img-value');

			if (addNewNode !== postListNode.firstChild)
			{
				postListNode.removeChild(postListNode.firstChild);
			}

			postListNode.insertBefore(postItem, postListNode.firstChild);
			Event.bind(postItem.querySelector('.crm-ads-new-campaign-item-post-delete'), 'click', () => {
				postItem.parentNode.removeChild(postItem);
			});

			previewNode.style.backgroundImage = 'url(' + event.data.media_url + ')';

			this.postData = event.data;

			const title = document.querySelector('.crm-ads-new-campaign-item-post-text');
			this.clipTitle(title);
			this.activateStage(this._STAGES.postSelected);
		}
	}

	openPostSlider()
	{
		if (this.uiNodes.instagramAccount.value)
		{
			this.openSlider(this.postListUrl, {
				sessid: BX.bitrix_sessid(),
				componentParams: {
					ACCOUNT_ID: this.uiNodes.instagramAccount.value,
					CLIENT_ID: this.uiNodes.clientInput.value,
					TYPE: this.provider.TYPE
				}
			}, this.onPostSelected);
		}
	}

	onTargetPageSelected(event)
	{
		if (event.eventId === "seo-ads-target-post-selected" && event.data)
		{
			if (!event.data.targetUrl)
			{
				this.deActivateStage(this._STAGES.pageSelected);
				return;
			}

			document.querySelector('.seo-ads-target-url').textContent = event.data.targetUrl;
			this.uiNodes.form.targetUrl.value = event.data.targetUrl;
			this.activateStage(this._STAGES.pageSelected);
		}
	}

	onFBAudienceConfigured(event)
	{
		if (event.eventId === "seo-fb-audience-configured" && event.data)
		{
			this.reInitAdCreator();
			if (!event.data)
			{
				this.deActivateStage(this._STAGES.audienceSelected);
				return;
			}

			this.adCreatorData.audienceConfig = event.data;
			this.activateStage(this._STAGES.audienceSelected);

			this.uiNodes.audienceSummary.innerHTML = this.buildAudienceSummary();
		}
	}

	onCrmAudienceConfigured(event)
	{
		if (event.eventId === "seo-crm-audience-configured" && event.data)
		{
			this.reInitAdCreator();
			if (!event.data)
			{
				this.deActivateStage(this._STAGES.audienceSelected);
				return;
			}

			this.adCreatorData.crmAudienceConfig = event.data;
			this.activateStage(this._STAGES.audienceSelected);
			this.uiNodes.audienceSummary.innerHTML = this.buildAudienceSummary();
		}
	}

	openTargetPageSlider()
	{
		if (this.uiNodes.instagramAccount.value)
		{
			this.storeBlockShow(false);
			this.openSlider(
				this.pageConfigurationUrl, {
					sessid: BX.bitrix_sessid(),
					targetUrl: this.uiNodes.form.targetUrl.value || '',
					cacheable: false
				},
				this.onTargetPageSelected
			);
		}
	}

	openSlider(url, params, callback)
	{
		const sliderOptions = {
			width: 1150,
			cacheable: params.cacheable || true,
			allowChangeHistory: false,
			requestMethod: 'post',
			requestParams: params
		};

		const eventName = BX.SidePanel.Slider.getEventFullName("onMessage");

		BX.removeAllCustomEvents(
			window,
			eventName,
			callback.bind(this)
		);

		BX.addCustomEvent(
			window,
			eventName,
			callback.bind(this)
		);

		BX.SidePanel.Instance.open(
			url,
			sliderOptions
		);
	}

	showAudienceExpertModeForm()
	{
		if (this.uiNodes.instagramAccount.value)
		{
			this.openSlider(this.audienceUrl, {
				sessid: BX.bitrix_sessid(),
				componentParams: {
					ACCOUNT_ID: this.uiNodes.instagramAccount.value,
					CLIENT_ID: this.uiNodes.clientInput.value,
					TYPE: this.provider.TYPE
				}
			}, this.onFBAudienceConfigured);
		}
	}

	showCrmAudienceExpertModeForm()
	{
		if (this.uiNodes.instagramAccount.value)
		{
			this.openSlider(this.crmAudienceUrl, {
				sessid: BX.bitrix_sessid(),
				componentParams: {
					TYPE: this.provider.TYPE
				}
			}, this.onCrmAudienceConfigured);
		}
	}

	initiateAutoAudienceMode()
	{

			this.reInitAdCreator();
			this.adCreatorData.crmAudienceConfig.genders = [1,2];
			this.adCreatorData.crmAudienceConfig.ageFrom = 25;
			this.adCreatorData.crmAudienceConfig.ageTo = 45;

			this.activateStage(this._STAGES.audienceSelected);
			this.uiNodes.audienceSummary.innerHTML = Loc.getMessage('SEO_AD_BUILDER_AUDIENCE_MEN_WOMAN_25_45');
	}

	buildAudienceSummary()
	{
		let summary = ''

		if(this.adCreatorData.audienceConfig.genderTitles)
		{
			summary += `${Loc.getMessage('SEO_AD_BUILDER_GENDER')}: ${this.adCreatorData.audienceConfig.genderTitles.join(', ')} `;
		}

		if(this.adCreatorData.audienceConfig.ageFrom)
		{
			summary += `${this.adCreatorData.audienceConfig.ageFrom} - ${this.adCreatorData.audienceConfig.ageTo}
			 ${Loc.getMessage('SEO_AD_BUILDER_YEARS_OLD')} <br/>`;
		}

		if(this.adCreatorData.audienceConfig.interests)
		{
			let interests = [];
			this.adCreatorData.audienceConfig.interests.forEach((interest) => {
				interests.push(interest.name);
			});

			summary += `${Loc.getMessage('SEO_AD_BUILDER_INTERESTS')}: ${interests.join(', ')}<br/>`;
		}

		if(this.adCreatorData.crmAudienceConfig.segmentInclude)
		{
			summary += `${Loc.getMessage('SEO_AD_BUILDER_CRM_AUDIENCE')}<br/>`;
		}

		if(Object.keys(this.selectedRegions).length)
		{
			let regions = [];
			for(let code in this.selectedRegions)
			{
				regions.push(this.selectedRegions[code].title);
			}

			summary += `${Loc.getMessage('SEO_AD_BUILDER_REGION')}: ${regions.join(', ')}<br/>`;
		}

		return summary;
	}

	sendToModeration(event)
	{
		this.uiNodes.toModerationBtn.classList.add('ui-btn-wait');

		const formNode = this.uiNodes.form;

		if (Object.keys(this.completedStages).length < 6)
		{
			for (let i = this._STAGES.accountSelected; i <= this._STAGES.toModeration; i++)
			{
				if (!this.completedStages[i])
				{
					this.scrollToStage(i);
					this.uiNodes.toModerationBtn.classList.remove('ui-btn-wait');
					return;
				}
			}
			this.uiNodes.toModerationBtn.classList.remove('ui-btn-wait');
			return;
		}

		const instagramAccount = this.uiNodes.instagramAccount.options[
			this.uiNodes.instagramAccount.selectedIndex
			].dataset;

		const params = {
			client_id: this.uiNodes.clientInput.value,
			budget: formNode.budget.value,
			duration: formNode.duration.value,
			targetUrl: formNode.targetUrl.value,
			accountId: this.uiNodes.account.value,
			instagramAccountId: instagramAccount.actorId,
			pageId: instagramAccount.pageId,
			body: this.postData.caption,
			mediaId: this.postData.id,
			permalink: this.postData.permalink,
			imageUrl: this.postData.media_url,
			countries: this.selectedRegions,
			interests: this.adCreatorData.audienceConfig.interests || [],
			ageFrom: this.adCreatorData.audienceConfig.ageFrom || '',
			ageTo: this.adCreatorData.audienceConfig.ageTo || '',
			genders: this.adCreatorData.audienceConfig.genders || ''
		};

		const form = document.getElementById('bx-sender-letter-edit')
			.querySelector('form');

		formNode.permalink.value = this.postData.permalink;
		formNode.pageId.value = params.pageId;
		formNode.body.value = this.postData.caption;
		formNode.mediaId.value = params.mediaId;
		formNode.imageUrl.value = params.imageUrl;
		formNode.instagramAccountId.value = params.instagramAccountId;
		formNode.interests.value = JSON.stringify(params.interests);
		formNode.ageFrom.value = params.ageFrom;
		formNode.ageTo.value = params.ageTo;
		formNode.genders.value = JSON.stringify(params.genders);
		formNode.regions.value = JSON.stringify(params.countries);

		const include = this.adCreatorData.crmAudienceConfig.segmentInclude || [];
		const exclude = this.adCreatorData.crmAudienceConfig.segmentExclude || [];
		for (let i = 0; i < include.length; i++)
		{
			const input = Tag.render`<input type="hidden" name='SEGMENT[INCLUDE][]'>`;
			input.value = include[i];
			form.appendChild(input);
		}
		for (let i = 0; i < exclude.length; i++)
		{
			const input = Tag.render`<input type="hidden" name='SEGMENT[EXCLUDE][]'>`;
			input.value = exclude[i];
			form.appendChild(input);
		}

		form.submit();
	}

	activateStage(stageNum)
	{
		const stage = document.querySelector(`[data-stage="${stageNum}"]`);
		const line = stage.querySelector('.crm-ads-new-campaign-item-line');
		const number = stage.querySelector('.crm-ads-new-campaign-item-number');
		const checker = stage.querySelector('.crm-ads-new-campaign-item-number-checker');

		if (line && number)
		{
			line.classList.remove('crm-ads-new-campaign-item--inactive');
			number.classList.remove('crm-ads-new-campaign-item--inactive');
		}

		if (checker)
		{
			checker.style.display = 'block';
		}

		this.completedStages[stageNum] = stageNum;

		if (Object.keys(this.completedStages).length === 5)
		{
			this.activateStage(this._STAGES.toModeration);
		}

		if (Object.keys(this.completedStages).length < 5)
		{
			this.deActivateStage(this._STAGES.toModeration);
		}
	}

	deActivateStage(stageNum)
	{
		const stage = document.querySelector(`[data-stage="${stageNum}"]`);
		const line = stage.querySelector('.crm-ads-new-campaign-item-line');
		const number = stage.querySelector('.crm-ads-new-campaign-item-number');
		const checker = stage.querySelector('.crm-ads-new-campaign-item-number-checker');

		if (line && number)
		{
			line.classList.add('crm-ads-new-campaign-item--inactive');
			number.classList.add('crm-ads-new-campaign-item--inactive');
		}

		if (checker)
		{
			checker.style.display = 'none';
		}

		delete (this.completedStages[stageNum]);

		if (Object.keys(this.completedStages).length < 6 && this.completedStages[this._STAGES.toModeration])
		{
			this.deActivateStage(this._STAGES.toModeration);
		}
	}

	scrollToStage(stageNum)
	{
		const stage = document.querySelector(`[data-stage="${stageNum}"]`);

		stage.scrollIntoView({
			behavior: 'smooth'
		});
	}

	buildSelector()
	{
		const selector = new TagSelector({
			id: 'seo-ads-regions',
			dialogOptions: {
				id: 'seo-ads-regions',
				context: 'SEO_ADS_REGIONS',
				dropdownMode: true,
				compactView: true,
				showAvatars: false,
				width: 350,
				height: 250,
				recentTabOptions: {
					stub: true,
					stubOptions: {
						title: Loc.getMessage('UI_TAG_SELECTOR_START_INPUT')
					}
				},
				searchOptions: {
					allowCreateItem: false
				},
				events: {
					'Item:onSelect': event => {
						const data = event.data.item;
						this.selectedRegions[data.id] = data;
						this.uiNodes.audienceSummary.innerHTML = this.buildAudienceSummary();
					}
				},
				entities: [
					{
						id: 'facebook_regions',
						searchable: true,
						dynamicSearch: true,
						options: {
							clientId: this.uiNodes.clientInput.value
						}
					}
				]
			}
		});

		selector.renderTo(document.getElementById('seo-ads-regions'));
		selector.getDialog().getRecentTab().setVisible(false)
		const selectorOptions = {
			iblockId: this.iBlockId,
			basePriceId: this.basePriceId,
			fields: {NAME:''},
			fileInputId: '',
			config: {
				ENABLE_SEARCH: true,
				ENABLE_IMAGE_CHANGE_SAVING: true
			}
		};

		this.productSelector = new ProductSelector('facebook-product-selector', selectorOptions);

		EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.productSelectedEvent.bind(this));
	}

	productSelectedEvent(event)
	{
		const fieldData = event.data.fields;
		this.seoAccount._helper.request('getProductUrl', {
				id: fieldData.ID
			}, response => {
				document.querySelector('.seo-ads-target-url').textContent = response;
				this.uiNodes.form.targetUrl.value = response;
				this.activateStage(this._STAGES.pageSelected)
			}
		)
	}

	toCreateStoreSlider()
	{
		if (!this.isCloud)
		{
			this.openTargetPageSlider();
			return;
		}

		const sliderOptions = {
			width: 990,
			cacheable: true,
			allowChangeHistory: false,
			requestMethod: 'get'
		};

		BX.SidePanel.Instance.open(
			'/shop/stores/site/edit/0/?super=Y',
			sliderOptions
		);
	}
}