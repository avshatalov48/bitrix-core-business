import {BaseBlock} from './base-block';
import {ajax, Tag, Loc, Type} from 'main.core';
import Form from '../form/form';

export class PlaceOrder extends BaseBlock
{
	constructor(form: Form, options: Object = {})
	{
		super(form, options);

		this.saveOrderHandler = this.saveOrder.bind(this);

		const properties = this.getForm().getParameter('userConsentPropertyData');

		this.userConsent = {
			id: 1,
			title: Loc.getMessage('SALE_BLOCKS_PLACE_ORDER_NOW'),
			isLoaded: 'Y',
			autoSave: 'Y',
			isChecked: 'Y',
			submitEventName: 'onUserConsent',
			fields: Type.isArrayFilled(properties)? JSON.stringify(properties):[]
		};

		this.isAllowedSubmitting = this.userConsent.isChecked === 'Y';
	}

	layout_(): void
	{
		this.getWrapper().appendChild(Tag.render`
						<div>
							${this.getConsent()}
							${this.getSaveButton()}
						</div>
					`);
	}

	layout(): void
	{
		const wrapper = this.getWrapper();

		ajax.runAction(
			'sale.entity.userconsentrequest',
			{
				data: {
					fields: this.userConsent
				}
			}
		)
			.then((response)=>{
				if(
					BX.type.isPlainObject(response.data)
					&& BX.type.isNotEmptyString(response.data.html)
				)
				{
					let consent = response.data.html;

					wrapper.appendChild(Tag.render`
						<div>
							${consent}
							${this.getSaveButton()}
						</div>
					`);

					if (BX.UserConsent !== undefined)
					{
						let control = BX.UserConsent.load(wrapper);

						BX.addCustomEvent(
							control,
							BX.UserConsent.events.accepted,
							() => this.isAllowedSubmitting = true
						);
						BX.addCustomEvent(
							control,
							BX.UserConsent.events.refused,
							() => this.isAllowedSubmitting = false
						);
					}
				}
			});
	}

	getConsent(): HTMLElement
	{

		// todo replace with existing consent api
		return Tag.render`
		 	<label class="checkout-agreement-container">
		 		<div class="checkout-agreement-block">
		 			<input type="checkbox" class="checkout-agreement-input" checked="checked">
		 		</div>
		 		<div class="checkout-agreement-block">
		 			<div class="checkout-agreement-text">${Loc.getMessage('SALE_BLOCKS_PLACE_ORDER_TEXT')}</div>
		 		</div>
		 	</label>
		 `;
	}

	getSaveButton(): HTMLElement
	{
		return this.getCache().remember('save-button', () => {
			return Tag.render`
				<div class="checkout-btn-container">
					<button
						class="btn btn-primary product-item-detail-buy-button btn-lg rounded-pill"
						onclick="${this.saveOrderHandler}"
						>${Loc.getMessage('SALE_BLOCKS_PLACE_ORDER_NOW')}</button>
				</div>
			`;
		});
	}

	saveOrder()
	{
		BX.onCustomEvent(this.userConsent.submitEventName, []);

		if(this.isAllowedSubmitting)
		{
			this.getSaveButton().disabled = true;
			this.getForm().requestSave();
		}
	}
}