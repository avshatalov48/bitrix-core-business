import {Dom, Event, Runtime, Loc, Type} from 'main.core';
import {Helper} from './landing.site_domain.helper';

export class Free
{
	/**
	 * Constructor.
	 */
	constructor(params)
	{
		this.idDomainSubmit = params.idDomainSubmit;
		this.idDomainCheck = params.idDomainCheck;
		this.idDomainName = params.idDomainName;
		this.idDomainAnother = params.idDomainAnother;
		this.idDomainAnotherMore = params.idDomainAnotherMore;
		this.idDomainErrorAlert = params.idDomainErrorAlert;
		this.saveBlocker = params.saveBlocker;
		this.saveBlockerCallback = params.saveBlockerCallback;
		this.promoCloseIcon = params.promoCloseIcon;
		this.promoCloseLink = params.promoCloseLink;
		this.promoBlock = params.promoBlock;
		this.maxVisibleSuggested = parseInt(params.maxVisibleSuggested || 10);
		this.tld = params.tld ? params.tld.toLowerCase() : 'tld';
		this.helper = new Helper(params);

		this.classes = {
			submit: 'ui-btn-clock'
		};

		if (this.promoCloseIcon && this.promoCloseLink) {
			Event.bind(this.promoCloseIcon, 'click', this.closePromoBlock.bind(this));
			Event.bind(this.promoCloseLink, 'click', this.closePromoBlock.bind(this));
		}

		if (this.idDomainAnotherMore) {
			Event.bind(this.idDomainAnotherMore, 'click', this.showMoreDomains.bind(this));
		}

		if (this.idDomainSubmit)
		{
			Event.bind(this.idDomainSubmit, 'click', function(event)
			{
				this.checkSubmit(event);
			}.bind(this));
		}

		if (this.idDomainCheck && this.idDomainName)
		{
			Event.bind(this.idDomainCheck, 'click', function(event)
			{
				this.checkDomain(event);
			}.bind(this));
		}

		if (this.idDomainName)
		{
			Event.bind(this.idDomainName, 'keyup', Runtime.debounce(function(event)
			{
				this.keyupCallback(event);
			}.bind(this), 500, this));
		}
	}

	/**
	 * Handler on keyup input.
	 */
	keyupCallback()
	{
		if (this.idDomainName.value === '')
		{
			this.helper.setError(Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
			return;
		}

		this.helper.setSuccess('');
	}

	/**
	 * Closes promo banner.
	 */
	closePromoBlock()
	{
		this.promoBlock.remove();
	}

	/**
	 * Shows full block of suggesiotn domains.
	 */
	showMoreDomains()
	{
		this.idDomainAnother.style.height = this.idDomainAnother.children[0].offsetHeight + 'px';
		this.idDomainAnotherMore.classList.add('landing-domain-block-available-btn-hide');
	}

	/**
	 * Makes some check before submit.
	 */
	checkSubmit(event)
	{
		if (Dom.hasClass(this.idDomainSubmit, this.classes.submit))
		{
			event.preventDefault();
			return;
		}

		this.checkDomainName();

		if (this.helper.isErrorShowed())
		{
			event.preventDefault();
		}
		else if (this.saveBlocker && this.saveBlockerCallback)
		{
			this.saveBlockerCallback();
			event.preventDefault();
		}
		else
		{
			Dom.addClass(this.idDomainSubmit, this.classes.submit);
		}
	}

	/**
	 * Sets suggested domain to the main input.
	 * @param {string} domainName Domain name.
	 */
	selectSuggested(domainName)
	{
		this.idDomainName.value = domainName;
		this.helper.setSuccess(
			Loc.getMessage('LANDING_TPL_DOMAIN_AVAILABLE')
		);
	}

	/**
	 * Fill suggested domain area.
	 * @param {array} suggest Suggested domains.
	 */
	fillSuggest(suggest)
	{
		if (!this.idDomainAnother)
		{
			return;
		}

		if (this.idDomainAnotherMore)
		{
			if (suggest.length > this.maxVisibleSuggested)
			{
				Dom.show(this.idDomainAnotherMore);
				this.idDomainAnotherMore.classList.remove('landing-domain-block-available-btn-hide');
			}
			else
			{
				Dom.hide(this.idDomainAnotherMore);
			}
		}

		if (suggest.length)
		{
			Dom.show(this.idDomainAnother.parentNode);
		}
		else
		{
			Dom.hide(this.idDomainAnother.parentNode);
		}

		var children = [];

		for (let i = 0, c = suggest.length; i < c; i++)
		{
			children.push(
				Dom.create(
					'div',
					{
						props: {
							className: 'landing-domain-block-available-item'
						},
						children: [
							Dom.create(
								'input',
								{
									props: {
										className: ''
									},
									attrs: {
										name: 'domain-edit-suggest',
										id: 'domain-edit-suggest-' + i,
										type: 'radio'
									},
									events: {
										click: i => {
											this.selectSuggested(suggest[i]);
										}
									}
								}
							),
							Dom.create(
								'label',
								{
									props: {
										className: 'landing-domain-block-available-label'
									},
									attrs: {
										for: 'domain-edit-suggest-' + i
									},
									text: suggest[i]
								}
							)
						]
					}
				)
			);
		}

		this.idDomainAnother.innerHTML = '';
		this.idDomainAnother.appendChild(Dom.create(
			'div',
			{
				props: {
					className: 'landing-domain-block-available-list'
				},
				children: children
			}
		));

		if (this.idDomainAnotherMore.style.display === 'none')
		{
			this.idDomainAnother.style.height = this.idDomainAnother.children[0].offsetHeight + 'px';
		}
		else
		{
			this.idDomainAnother.style.height = 80 + 'px';
		}
	}

	/**
	 * Checks that domain name is correct.
	 */
	checkDomainName()
	{
		this.idDomainName.value =
			Type.isString(this.idDomainName.value)
				? this.idDomainName.value.trim()
				: this.idDomainName.value
		;
		const domainRe = RegExp('^[a-z0-9-]+\.' + (this.tld) + '$');

		if (this.idDomainName.value === '')
		{
			this.helper.setError(Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EMPTY'));
		}
		else if (!domainRe.test(this.idDomainName.value.toLowerCase()))
		{
			this.helper.setError(Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK'));
		}
		else if (
			this.idDomainName.value.indexOf('--') !== -1 ||
			this.idDomainName.value.indexOf('-.') !== -1 ||
			this.idDomainName.value.indexOf('-') === 0
		)
		{
			this.helper.setError(Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_CHECK_DASH'));
		}
	}

	/**
	 * Makes whois query for user pointed domain.
	 */
	checkDomain(event)
	{
		event.preventDefault();

		if (this.helper.isLoaderShowed())
		{
			return;
		}

		this.checkDomainName();
		if (this.helper.isErrorShowed())
		{
			return;
		}

		this.helper.showLoader();
		this.fillSuggest([]);

		BX.ajax({
			url: '/bitrix/tools/landing/ajax.php?action=Domain::whois',
			method: 'POST',
			data: {
				data: {
					domainName: this.idDomainName.value,
					tld: this.tld
				},
				sessid: Loc.getMessage('bitrix_sessid')
			},
			dataType: 'json',
			onsuccess: function (data)
			{
				this.helper.hideLoader();
				if (data.type === 'success')
				{
					const result = data.result;
					if (!result.enable)
					{
						if (result.suggest)
						{
							this.fillSuggest(result.suggest);
						}
						this.helper.setError(
							Loc.getMessage('LANDING_TPL_ERROR_DOMAIN_EXIST')
						);
					}
					else
					{
						this.helper.setSuccess(
							Loc.getMessage('LANDING_TPL_DOMAIN_AVAILABLE')
						);
					}
				}
			}.bind(this)
		});
	}
}