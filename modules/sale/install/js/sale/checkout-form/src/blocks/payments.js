import {ajax, Type} from 'main.core';
import {BaseBlock} from './base-block';
import {Dom, Tag, Text} from 'main.core';
import {rest as Rest} from 'rest.client';

export class Payments extends BaseBlock
{
	refreshLayout(forceLayout: boolean = false): void
	{
		let mode;

		const formStage = this.getForm().getStage();
		const blockStage = this.getStage();

		if (Type.isPlainObject(blockStage))
		{
			const {view: viewStage, edit: editStage, hide: hideStage} = blockStage;
			let currentStage = 0;

			while (currentStage <= formStage)
			{
				if (currentStage === hideStage)
				{
					mode = undefined;
				}
				else if (currentStage === editStage)
				{
					mode = BaseBlock.EDIT_MODE;
				}
				else if (currentStage === viewStage)
				{
					mode = BaseBlock.VIEW_MODE;
				}

				currentStage++;
			}
		}
		else if (Type.isNumber(blockStage))
		{
			if (blockStage <= formStage)
			{
				mode = BaseBlock.EDIT_MODE;
			}
		}

		this.clearLayout();

		if (mode || forceLayout)
		{
			if (mode)
			{
				this.setMode(mode);
			}

			this.layout();
		}
	}

	layout()
	{
		let access = this.getForm().getSchemeField('hash');
		let paySystemReturnUrl = this.getForm().getParameter('paySystemReturnUrl');

		let payments = this.getForm().getField('payments');

		let paymentId = 0;
		Object.keys(payments).forEach((id)=>{paymentId = id; return false;});

		ajax.runAction(
			'sale.entity.paymentpay',
			{
				data: {
					fields: {
						paymentId: paymentId,
						accessCode: access,
						returnUrl: paySystemReturnUrl,
					}
				}
			}
		)
			.then(this.getPaySystemsList.bind(this));

		//${this.getPaySystemNodes()}
	}

	getPaySystemsList(response)
	{
		const wrapper = this.getWrapper();

		if(
			BX.type.isPlainObject(response.data)
			&& BX.type.isNotEmptyString(response.data.html)
		)
		{
			 BX.html(wrapper, response.data.html);

			BX.addCustomEvent('onChangePaySystems', ()=>{
				this.getForm().refreshLayout();
			});
		}
	}

	getPaySystems(): []
	{
		return this.getForm().getSchemeField('paySystems', []);
	}

	getPaySystemNodes(): HTMLElement[]
	{
		const paySystemNodes = [];

		this.getPaySystems().forEach((item) => {
			paySystemNodes.push(Tag.render`
				<div class="checkout-checkout-method">
					<div class="checkout-checkout-method-image-block">
						<img src="${item.logotipSrc}" alt="" class="checkout-checkout-method-img">
					</div>
					<div class="checkout-checkout-method-name-block">
						<div class="checkout-checkout-method-name">${Text.encode(item.name)}</div>
						<div class="checkout-checkout-method-description">${Text.encode(item.description)}</div>
					</div>
					<div class="checkout-checkout-method-btn-block">
						<button 
							class="btn btn-primary checkout-checkout-btn btn-sm rounded-pill"
							data-paysystem-id="${item.id}"
							onclick="${this.handleCheckoutClick.bind(this)}"
						>Checkout</button>
<!--						<button class="checkout-checkout-btn checkout-checkout-btn-selected btn btn-sm rounded-pill">Selected</button>-->
					</div>
				</div>
			`);
		});

		return Tag.render`
			<div class="checkout-checkout-method-list">
				${paySystemNodes}
			</div>
		`;
	}

	handleCheckoutClick(event: MouseEvent): void
	{
		const paySystemId = Text.toNumber(event.target.getAttribute('data-paysystem-id'));

		this.getForm().setFieldNoDemand('paySystemId', paySystemId);
		event.target.setAttribute('disabled', 'disabled');
		Dom.addClass(event.target, 'checkout-checkout-btn-selected');
		event.target.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>' + event.target.innerHTML;

		let payments = this.getForm().getField('payments');
		let paymentId = 0;
		Object.keys(payments).forEach((id)=>{paymentId = id; return false;});

		ajax.runAction(
			'sale.entity.paymentpay',
			{
				data: {
					fields: {
						ID: paymentId
					}
				}
			}
		)
			.then(function (response) {

				if(
					BX.type.isPlainObject(response.data)
					&& BX.type.isNotEmptyString(response.data.html)
				)
				{
					BX.html(event.target, response.data.html);
				}
			})
		;
	}
}