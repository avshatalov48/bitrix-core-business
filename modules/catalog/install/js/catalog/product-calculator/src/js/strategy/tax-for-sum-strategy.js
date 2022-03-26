import {TaxForPriceStrategy} from "./tax-for-price-strategy";
import {FieldStorage} from "../field-storage";
import {DiscountType} from "../discount-type";

export class TaxForSumStrategy extends TaxForPriceStrategy
{
	calculatePriceWithoutTax(price, taxRate)
	{
		return price;
	}

	updateResultPrices(fieldStorage: FieldStorage): void
	{
		let exclusivePrice;

		if (fieldStorage.isDiscountPercentage())
		{
			exclusivePrice = this.calculatePriceWithoutDiscount(
				fieldStorage.getPriceNetto(),
				fieldStorage.getDiscountRate(),
				DiscountType.PERCENTAGE
			);
		}
		else if (fieldStorage.isDiscountMonetary())
		{
			exclusivePrice = this.calculatePriceWithoutDiscount(
				fieldStorage.getPriceNetto(),
				fieldStorage.getDiscountSum(),
				DiscountType.MONETARY
			);
		}
		else
		{
			exclusivePrice = fieldStorage.getPriceExclusive();
		}

		fieldStorage.setField('PRICE_EXCLUSIVE', exclusivePrice);
		
		if (fieldStorage.isTaxIncluded())
		{
			fieldStorage.setField('PRICE', exclusivePrice);
		}
		else
		{
			fieldStorage.setField(
				'PRICE',
				this.calculatePriceWithTax(exclusivePrice, fieldStorage.getTaxRate())
			);
		}
	}
}