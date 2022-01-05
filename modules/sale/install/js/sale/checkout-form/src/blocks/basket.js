import {BaseBlock} from './base-block';
import {Dom, Tag, Text, Loc, Type, ajax} from 'main.core';
import Form from "../form/form";

export class Basket extends BaseBlock
{
	constructor(form: Form, options: Object = {})
	{
		super(form, options);

		this.deleteItemHandler = this.delete.bind(this);
	}

	delete(event: Event)
	{
		const div = event.target;

		if (!Type.isDomNode(div))
		{
			return;
		}

		const itemId = div.getAttribute('data-item-id');

		ajax.runAction(
			'sale.entity.deletebasketitem',
			{
				data: {
					id: itemId
				}
			}
		)
			.then((response)=>{
				const redirectPath = this.getForm().getParameter('currentPage');
				if (Type.isStringFilled(redirectPath))
				{
					document.location.href = redirectPath;
				}
			});
	}

	layout(): void
	{
		this.getWrapper().appendChild(
			// workaround: Tag.render`` can't render table with dynamic rows content
			Dom.create('table', {
				attrs: {className: 'checkout-item-list'},
				children: [
					...this.getProducts(),
					this.getTotalNode()
				]
			})
		);
	}

	getBasketItems(): Array<mixed>
	{
		return this.getForm().getSchemeField('basketItems', []);
	}

	getBasketPositionsCount(): number
	{
		return this.getBasketItems().length;
	}

	getProducts(): HTMLElement[]
	{
		const itemNodes = [];

		this.getBasketItems().forEach((item) => {
			const discountNode = this.getItemDiscountNode(item);
			const propsNode = this.getItemPropsNode(item);
			const imageSrc = item.catalogProduct.frontImage ? item.catalogProduct.frontImage.src : '';
			const itemNode = Tag.render`
				<table>
					<tr class="checkout-item">
						<td>
							<div class="checkout-item-info">
								<div class="checkout-item-image-block">
									<img src="${imageSrc}" alt="" class="checkout-item-image">
								</div>
								
								<div class="checkout-item-name-block">
									<h2 class="checkout-item-name">${Text.encode(item.name)}</h2>
									<div>${propsNode}</div>
								</div>
								
								<div class="checkout-item-quantity-block">
									<div class="checkout-item-quantity-field-container">
<!--										<div class="checkout-item-quantity-btn-minus no-select"></div>-->
										<div class="checkout-item-quantity-field-block">
										<input class="checkout-item-quantity-field" type="text" inputmode="numeric" value="${item.quantity}">
										</div>
<!--										<div class="checkout-item-quantity-btn-plus no-select"></div>-->
									</div>
									<span class="checkout-item-quantity-description">
										<span class="checkout-item-quantity-description-text">${Text.encode(item.measureText)}</span>
										<span class="checkout-item-quantity-description-price"></span>
									</span>
								</div>
							</div>
						</td>
						<td>
							<div class="checkout-item-price-block">
								${discountNode}
								<span class="checkout-item-price">${item.sum}</span>
							</div>
						</td>
					</tr>
				</table>
			`;
			itemNodes.push(this.getFirstRowFromTable(itemNode))
		});

		return itemNodes;
	}

	getItemDiscountNode(item)
	{
		if (item.sumDiscountDiff === 0)
		{
			return '';
		}

		return Tag.render`
			<div class="checkout-item-price-discount-container">
				<span class="checkout-item-price-discount">${item.sumBaseFormated}</span>
				<span class="checkout-item-price-discount-diff">-${item.sumDiscountDiffFormated}</span>
			</div>
		`;
	}

	getItemPropsNode(item)
	{
		if (item.props === 0)
		{
			return '';
		}
		const propsItems = {};
		const propsItemsDom = [];
		item.props.forEach((i) => {
			propsItems[i] = {
				name: i.name,
				value: i.value,
			};

			var domRender = Tag.render`<div class="checkout-item-props">${propsItems[i].name}: ${propsItems[i].value}</div>`;

			propsItemsDom.push(domRender);
		});

		return propsItemsDom;
	}

	getTotalData(): Array<mixed>
	{
		return this.getForm().getSchemeField('orderPriceTotal', {});
	}

	getTotalNode(): HTMLElement
	{
		const total = this.getTotalData();
		const discountNode = this.getTotalDiscountNode(total);
		const subTotalNode = Tag.render`
			<table>
				<tr class="checkout-item-summary">
					<td>
						<div class="checkout-summary">
							<span>${Loc.getMessage('SALE_BLOCKS_BASKET_ITEMS')}</span>
<!--							<span class="checkout-icon-helper"></span>-->
						</div>
					</td>
					<td>
						<div class="checkout-item-price-block">
							${discountNode}
							<span class="checkout-item-price">${total.orderPriceFormated}</span>
						</div>
					</td>
				</tr>
			</table>
		`;

		return this.getFirstRowFromTable(subTotalNode);
	}

	getTotalDiscountNode(total)
	{
		if (total.basketPriceDiscountDiffValue === 0)
		{
			return '';
		}

		return Tag.render`
			<div class="checkout-item-price-discount-container">
				<span class="checkout-item-price-discount">${total.priceWithoutDiscount}</span>
				<span class="checkout-item-price-discount-diff">-${total.basketPriceDiscountDiff}</span>
			</div>
		`;
	}

	// workaround: Tag.render`` can't render tr/td nodes without table node
	getFirstRowFromTable(table: HTMLTableElement): HTMLTableRowElement
	{
		return table.rows[0];
	}
}