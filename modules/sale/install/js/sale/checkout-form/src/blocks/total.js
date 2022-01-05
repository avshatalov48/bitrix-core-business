import {BaseBlock} from './base-block';
import {Dom, Tag, Loc} from 'main.core';

export class Total extends BaseBlock
{
	layout(): void
	{
		const total = this.getForm().getSchemeField('orderPriceTotal');

		this.getWrapper().appendChild(
			// workaround: Tag.render`` can't render table with dynamic rows content
			Dom.create('table', {
				attrs: {className: 'checkout-summary-list'},
				children: [
					this.getBasketTotalNode(total),
					this.getDiscountNode(total),
					this.getShippingNode(total),
					this.getTaxesNode(total),
					this.getSummaryNode(total)
				]
			})
		);
	}

	getBasketPositionsCount(): number
	{
		return this.getForm().getSchemeField('basketItems', []).length;
	}

	getBasketTotalNode(total): HTMLTableRowElement
	{

		return this.getFirstRowFromTable(Tag.render`
			<table>
				<tr class="checkout-summary-item checkout-summary-item-subtotal">
					<td>
						<div class="checkout-summary">
							<span>${Loc.getMessage('SALE_BLOCKS_TOTAL_ITEMS')}</span>
<!--							<span class="checkout-icon-helper"></span>-->
						</div>
					</td>
					<td>
						<div class="checkout-item-price-block">
							<span class="checkout-item-price">${total.priceWithoutDiscount}</span>
						</div>
					</td>
				</tr>
			</table>
		`);
	}

	getDiscountNode(total): HTMLTableRowElement
	{
		if (total.discountPrice === 0)
		{
			return '';
		}

		return this.getFirstRowFromTable(Tag.render`
			<table>
				<tr class="checkout-summary-item checkout-summary-item-discount">
					<td>
						<div class="checkout-summary">
							<span>${Loc.getMessage('SALE_BLOCKS_TOTAL_DISCOUNT')}</span>
<!--							<span class="checkout-icon-helper"></span>-->
						</div>
					</td>
					<td>
						<div class="checkout-item-price-block">
							<span class="checkout-summary-item-price-discount">-${total.discountPriceFormated}</span>
						</div>
					</td>
				</tr>
			</table>
		`);
	}

	getShippingNode(total): HTMLTableRowElement
	{
		if (true)
		{
			return '';
		}

		return this.getFirstRowFromTable(Tag.render`
			<table>
				<tr class="checkout-summary-item">
					<td>
						<div class="checkout-summary">
							<span>${Loc.getMessage('SALE_BLOCKS_TOTAL_NAME')}</span>
<!--							<span class="checkout-icon-helper"></span>-->
							<div class="checkout-summary-item-description">${Loc.getMessage('SALE_BLOCKS_TOTAL_DESCRIPTION')}</div>
						</div>
					</td>
					<td>
						<div class="checkout-item-price-block">
							<span class="checkout-item-price">$10.55</span>
						</div>
					</td>
				</tr>
			</table>
		`);
	}

	getTaxesNode(total): HTMLTableRowElement
	{
		if (true)
		{
			return '';
		}

		return this.getFirstRowFromTable(Tag.render`
			<table>
				<tr class="checkout-summary-item">
					<td>
						<div class="checkout-summary">
							<span>${Loc.getMessage('SALE_BLOCKS_TOTAL_TAXES')}</span>
							<span class="checkout-icon-helper"></span>
						</div>
					</td>
					<td>
						<div class="checkout-item-price-block">
							<span class="checkout-item-price">$13.99</span>
						</div>
					</td>
				</tr>
			</table>
		`);
	}

	getSummaryNode(total): HTMLTableRowElement
	{
		return this.getFirstRowFromTable(Tag.render`
			<table>
				<tr class="checkout-summary-item checkout-summary-item-total">
					<td>
						<div class="checkout-summary">
							<span>${Loc.getMessage('SALE_BLOCKS_TOTAL_TOTAL')}</span>
						</div>
					</td>
					<td>
						<div class="checkout-item-price-block">
<!--							<span class="checkout-item-price">${total.orderTotalPriceFormated}</span>-->
							<span class="checkout-item-price">${total.orderPriceFormated}</span>
						</div>
					</td>
				</tr>
			</table>
		`);
	}

	// workaround: Tag.render`` can't render tr/td nodes without table node
	getFirstRowFromTable(table: HTMLTableElement): HTMLTableRowElement
	{
		return table.rows[0];
	}
}