import {BaseBlock} from './base-block';
import {Dom, Tag, Text, Type, Loc} from 'main.core';

export class Success extends BaseBlock
{
	layout()
	{
		this.getWrapper().appendChild(Tag.render`
			<div class="checkout-order-status-successful">
				<svg class="checkout-order-status-icon" width="105" height="106" viewBox="0 0 105 106" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path opacity="0.6" stroke="#fff" stroke-width="3" fill-rule="evenodd" clip-rule="evenodd" d="M52.5 104C80.6665 104 103.5 81.1665 103.5 53C103.5 24.8335 80.6665 2 52.5 2C24.3335 2 1.5 24.8335 1.5 53C1.5 81.1665 24.3335 104 52.5 104Z"/>
					<path fill="#fff" fill-rule="evenodd" clip-rule="evenodd" d="M45.517 72L28.5 55.4156L34.4559 49.611L45.517 60.3909L70.5441 36L76.5 41.8046L45.517 72Z"/>
				</svg>				
				${this.showBeforePayment()}
				${this.showAfterPayment()}
			</div>
		`);

		Dom.addClass(document.body, 'container-overflow-hidden');
	}

	clearLayout()
	{
		super.clearLayout();
		Dom.removeClass(document.body, 'container-overflow-hidden');
	}

	showOrderStatus()
	{
		const orderNumber = this.getForm().getSchemeField('ACCOUNT_NUMBER');

		return Tag.render`			
			<div class="checkout-order-status-text">
				<strong>${Loc.getMessage('SALE_BLOCKS_SUCCESS_ORDER')} #${Text.encode(orderNumber)}</strong> ${Loc.getMessage('SALE_BLOCKS_SUCCESS_ORDER_CREATED')}
			</div>
		`;
	}

	showPaymentStatus()
	{
		const orderNumber = this.getForm().getSchemeField('ACCOUNT_NUMBER');

		return Tag.render`			
			<div class="checkout-order-status-text">
				<strong>${Loc.getMessage('SALE_BLOCKS_SUCCESS_ORDER')} #${Text.encode(orderNumber)}</strong>
			</div>
		`;
	}

	showManagerWillCall()
	{
		return Tag.render`
			<div class="checkout-order-common-container">
				<div class="checkout-order-common-row">
					<svg class="checkout-order-common-row-icon" width="26" height="27" viewBox="0 0 26 27" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M12.9124 26.6569C20.0093 26.6569 25.7624 20.9038 25.7624 13.807C25.7624 6.71014 20.0093 0.957031 12.9124 0.957031C5.81561 0.957031 0.0625 6.71014 0.0625 13.807C0.0625 20.9038 5.81561 26.6569 12.9124 26.6569Z" fill="white"/>
						<path fill-rule="evenodd" clip-rule="evenodd" d="M10.8218 19.498L5.72461 14.5304L7.50861 12.7918L10.8218 16.0207L18.3182 8.71484L20.1022 10.4535L10.8218 19.498Z" fill="#65A90F"/>
					</svg>
					<div>${Loc.getMessage('SALE_BLOCKS_SUCCESS_CALL')}</div>
				</div>
			</div>
		`;
	}

	showPaymentSum()
	{
		const total = this.getForm().getSchemeField('ORDER_PRICE_TOTAL');

		return Tag.render`
			<div class="checkout-order-common-container">
				<div class="checkout-order-common-row">
					<svg class="checkout-order-common-row-icon" width="26" height="27" viewBox="0 0 26 27" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M12.9124 26.6569C20.0093 26.6569 25.7624 20.9038 25.7624 13.807C25.7624 6.71014 20.0093 0.957031 12.9124 0.957031C5.81561 0.957031 0.0625 6.71014 0.0625 13.807C0.0625 20.9038 5.81561 26.6569 12.9124 26.6569Z" fill="white"/>
						<path fill-rule="evenodd" clip-rule="evenodd" d="M10.8218 19.498L5.72461 14.5304L7.50861 12.7918L10.8218 16.0207L18.3182 8.71484L20.1022 10.4535L10.8218 19.498Z" fill="#65A90F"/>
					</svg>
					<div>${Loc.getMessage('SALE_BLOCKS_SUCCESS_TO_PAID').replace('#PAID#', total.orderTotalPriceFormated)}</div>
				</div>
				<div class="checkout-order-common-row">
					<div>${Loc.getMessage('SALE_BLOCKS_SUCCESS_DELIVERY')}</div>
				</div>
			</div>
		`;
	}

	showSeparator()
	{
		return Tag.render`
			<div class="checkout-order-section-separator">${Loc.getMessage('SALE_BLOCKS_SUCCESS_OR')}</div>
		`;
	}

	showContinueProcessing()
	{
		return Tag.render`
			<div class="checkout-order-status-btn-container">
				<button
					class="btn btn-checkout-order-status btn-md rounded-pill"
					onclick="${this.onContinueProcessingHandler.bind(this)}"
				>${Loc.getMessage('SALE_BLOCKS_SUCCESS_CHECKOUT')}</button>
			</div>
		`;
	}

	onContinueProcessingHandler(event: Event)
	{
		// todo setField()
		let url = this.getForm().parameters['paySystemReturnUrl'];
		//todo
		url = this.addLinkParam(url, 'orderId', this.getForm().getSchemeField('orderId'));
		url = this.addLinkParam(url, 'access', this.getForm().getSchemeField('hash'));
		this.getForm().parameters['paySystemReturnUrl'] = url;

		// todo refresh layout with paysystems
		this.getForm().refreshLayout();

		// todo
		delete BX.UserConsent;

		this.pushState({
			orderId: this.getForm().getSchemeField('orderId'),
			access:  this.getForm().getSchemeField('hash')
		});
	}

	getCurrentUrl()
	{
		return window.location.protocol + "//" + window.location.hostname + (window.location.port != '' ? ':' + window.location.port : '') +
			window.location.pathname + window.location.search;
	}

	addLinkParam(link, name, value)
	{
		if(!link.length)
		{
			return '?' + name + '=' + value;
		}
		link = BX.Uri.removeParam(link, name);
		if(link.indexOf('?') != -1)
		{
			return link + '&' + name + '=' + value;
		}
		return link + '?' + name + '=' + value;
	}

	pushState(params)
	{
		let url = '';
		url = this.getCurrentUrl();
		url = this.addLinkParam(url, 'orderId', params.orderId);
		url = this.addLinkParam(url, 'access', params.access);

		window.history.pushState(null, null, url);
	}

	showContinueShopping()
	{
		return Tag.render`
			<div class="checkout-order-status-btn-container">
				<button
					class="btn btn-checkout-order-status btn-md rounded-pill"
					onclick="${this.onContinueShoppingHandler.bind(this)}"
				>${Loc.getMessage('SALE_BLOCKS_SUCCESS_CONTINUE')}</button>
			</div>
		`;
	}

	onContinueShoppingHandler(event: Event)
	{
		event.target.disable = true;

		const redirectPath = this.getForm().getParameter('emptyBasketHintPath');
		if (Type.isStringFilled(redirectPath))
		{
			document.location.href = redirectPath;
		}
	}

	isContinueProcessingEnabled()
	{
		return this.getForm().getParameter('showContinueProcessing', false);
	}

	isPaymentSelected()
	{
		return this.getForm().getField('paySystemId', 0) > 0;
	}

	hasPaySystems()
	{
		return this.getForm().getSchemeField('paySystems', []).length > 0;
	}

	showBeforePayment()
	{
		if (!this.isContinueProcessingEnabled || this.isPaymentSelected())
		{
			return '';
		}

		if(this.hasPaySystems())
		{
			return [
				this.showOrderStatus(),
				this.showManagerWillCall(),
				this.showSeparator(),
				this.showContinueProcessing()
			];
		}
		else
		{
			return [
				this.showOrderStatus(),
				this.showManagerWillCall(),
			];
		}


	}

	showAfterPayment()
	{
		if (!this.isPaymentSelected())
		{
			return '';
		}

		return [
			this.showPaymentStatus(),
			this.showPaymentSum(),
			this.showContinueShopping()
		];
	}
}