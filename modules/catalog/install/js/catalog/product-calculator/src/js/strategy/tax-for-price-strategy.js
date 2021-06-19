import type {DiscountTypes} from '../discount-type';
import {DiscountType} from '../discount-type';
import type {FieldScheme} from "../field-scheme";
import {FieldStorage} from '../field-storage';
import {ProductCalculator} from '../product-calculator';

export class TaxForPriceStrategy
{
	calculator: ProductCalculator = null;

	constructor(productCalculator: ProductCalculator)
	{
		this.calculator = productCalculator;
	}

	getFieldStorage(): FieldStorage
	{
		return new FieldStorage(this.calculator.getFields());
	}

	getPricePrecision()
	{
		return this.calculator.getPricePrecision();
	}

	getCommonPrecision()
	{
		return this.calculator.getCommonPrecision();
	}

	getQuantityPrecision()
	{
		return this.calculator.getQuantityPrecision();
	}

	calculatePrice(value: number): FieldScheme
	{
		if (value < 0)
		{
			throw new Error('Price must be equal or greater than zero.')
		}

		value = this.roundPrice(value);

		const fieldStorage = this.getFieldStorage();

		if (fieldStorage.isTaxIncluded())
		{
			fieldStorage.setField('PRICE_BRUTTO', value);
		}
		else
		{
			fieldStorage.setField('PRICE_NETTO', value);
		}

		this.updatePrice(fieldStorage);

		this.activateCustomized(fieldStorage);

		return fieldStorage.getFields();
	}

	calculateQuantity(value: number): FieldScheme
	{
		if (value < 0)
		{
			throw new Error('Quantity must be equal or greater than zero.')
		}

		value = this.round(value, this.getQuantityPrecision());

		const fieldStorage = this.getFieldStorage();
		fieldStorage.setField('QUANTITY', value);

		this.updateRowDiscount(fieldStorage);
		this.updateTax(fieldStorage);
		this.updateSum(fieldStorage);

		return fieldStorage.getFields();
	}

	calculateDiscount(value: number): FieldScheme
	{
		const fieldStorage = this.getFieldStorage();

		if (value === 0.0)
		{
			this.clearResultPrices(fieldStorage);
		}
		else if (fieldStorage.isDiscountPercentage())
		{
			fieldStorage.setField('DISCOUNT_RATE', value);

			this.updateResultPrices(fieldStorage);

			fieldStorage.setField(
				'DISCOUNT_SUM',
				this.roundPrice(fieldStorage.getPriceNetto() - fieldStorage.getPriceExclusive())
			);
		}
		else if (fieldStorage.isDiscountMonetary())
		{
			fieldStorage.setField('DISCOUNT_SUM', value);

			this.updateResultPrices(fieldStorage);

			fieldStorage.setField(
				'DISCOUNT_RATE',
				this.round(
					this.calculateDiscountRate(
						fieldStorage.getPriceNetto(),
						fieldStorage.getPriceExclusive()
					),
					this.getCommonPrecision()
				)
			);
		}

		this.updateRowDiscount(fieldStorage);
		this.updateTax(fieldStorage);
		this.updateSum(fieldStorage);

		this.activateCustomized(fieldStorage);

		return fieldStorage.getFields();
	}

	calculateDiscountType(value: DiscountTypes): FieldScheme
	{
		const fieldStorage = this.getFieldStorage();

		fieldStorage.setField('DISCOUNT_TYPE_ID', value);

		this.updateResultPrices(fieldStorage);
		this.updateDiscount(fieldStorage);
		this.updateRowDiscount(fieldStorage);
		this.updateTax(fieldStorage);
		this.updateSum(fieldStorage);

		this.activateCustomized(fieldStorage);

		return fieldStorage.getFields();
	}

	calculateRowDiscount(value: number): FieldScheme
	{
		const fieldStorage = this.getFieldStorage();

		fieldStorage.setField('DISCOUNT_ROW', value);

		if (value !== 0 && fieldStorage.getQuantity() === 0)
		{
			fieldStorage.setField('QUANTITY', 1);
		}

		fieldStorage.setField('DISCOUNT_TYPE_ID', DiscountType.MONETARY);

		if (value === 0 || fieldStorage.getQuantity() === 0)
		{
			fieldStorage.setField('DISCOUNT_SUM', 0);
		}
		else
		{
			fieldStorage.setField(
				'DISCOUNT_SUM',
				this.roundPrice(fieldStorage.getDiscountRow() / fieldStorage.getQuantity())
			);
		}

		this.updateResultPrices(fieldStorage);

		this.updateDiscount(fieldStorage);
		this.updateRowDiscount(fieldStorage);
		this.updateTax(fieldStorage);
		this.updateSum(fieldStorage);

		this.activateCustomized(fieldStorage);

		return fieldStorage.getFields();
	}

	calculateTax(value: number): FieldScheme
	{
		const fieldStorage = this.getFieldStorage();
		fieldStorage.setField('TAX_RATE', value);

		this.updateBasePrices(fieldStorage);
		this.updateResultPrices(fieldStorage);

		if (fieldStorage.isTaxIncluded())
		{
			this.updateDiscount(fieldStorage);
			this.updateRowDiscount(fieldStorage);
		}

		this.updateTax(fieldStorage);
		this.updateSum(fieldStorage);

		this.activateCustomized(fieldStorage);

		return fieldStorage.getFields();
	}

	calculateTaxIncluded(value: 'Y' | 'N'): FieldScheme
	{
		const fieldStorage = this.getFieldStorage();

		if (fieldStorage.getTaxIncluded() !== value)
		{
			fieldStorage.setField('TAX_INCLUDED', value);

			if (fieldStorage.isTaxIncluded())
			{
				fieldStorage.setField('PRICE_BRUTTO', fieldStorage.getPriceNetto());
			}
			else
			{
				fieldStorage.setField('PRICE_NETTO', fieldStorage.getPriceBrutto());
			}
		}

		this.updatePrice(fieldStorage);

		this.activateCustomized(fieldStorage);

		return fieldStorage.getFields();
	}

	calculateRowSum(value: number): FieldScheme
	{
		const fieldStorage = this.getFieldStorage();

		fieldStorage.setField('SUM', value);

		if (fieldStorage.getQuantity() === 0)
		{
			fieldStorage.setField('QUANTITY', 1);
		}

		const discountSum = this.roundPrice(
			fieldStorage.getPriceNetto()
			- (
				fieldStorage.getSum()
				/ (fieldStorage.getQuantity() * (1 + fieldStorage.getTaxRate() / 100))
			)
		);

		fieldStorage.setField('DISCOUNT_SUM', discountSum);
		fieldStorage.setField('DISCOUNT_TYPE_ID', DiscountType.MONETARY);

		if (fieldStorage.isEmptyDiscount())
		{
			this.clearResultPrices(fieldStorage);
		}
		else if (fieldStorage.isDiscountHandmade())
		{
			this.updateResultPrices(fieldStorage);
		}

		this.updateDiscount(fieldStorage);
		this.updateRowDiscount(fieldStorage);
		this.updateTax(fieldStorage);

		this.activateCustomized(fieldStorage);

		return fieldStorage.getFields();
	}

	updatePrice(fieldStorage: FieldStorage): void
	{
		this.updateBasePrices(fieldStorage);

		if (fieldStorage.isEmptyDiscount())
		{
			this.clearResultPrices(fieldStorage);
		}
		else if (fieldStorage.isDiscountHandmade())
		{
			this.updateResultPrices(fieldStorage);
		}

		this.updateDiscount(fieldStorage);
		this.updateRowDiscount(fieldStorage);
		this.updateTax(fieldStorage);
		this.updateSum(fieldStorage);
	}

	clearResultPrices(fieldStorage: FieldStorage)
	{
		fieldStorage.setField('PRICE_EXCLUSIVE', fieldStorage.getPriceNetto());
		fieldStorage.setField('PRICE', fieldStorage.getPriceBrutto());

		fieldStorage.setField('DISCOUNT_RATE', 0.0);
		fieldStorage.setField('DISCOUNT_SUM', 0.0);
	}

	calculatePriceWithoutDiscount(price: number, discount: number, discountType: DiscountTypes)
	{
		let result = 0.0;

		switch (discountType)
		{
			case DiscountType.PERCENTAGE:
				result = price - (price * discount / 100);
				break;

			case DiscountType.MONETARY:
				result = price - discount;
				break;
		}

		return result;
	}

	updateBasePrices(fieldStorage: FieldStorage): void
	{
		if (fieldStorage.isTaxIncluded())
		{
			fieldStorage.setField(
				'PRICE_NETTO',
				this.roundPrice(
					this.calculatePriceWithoutTax(fieldStorage.getPriceBrutto(), fieldStorage.getTaxRate())
				)
			);
		}
		else
		{
			fieldStorage.setField(
				'PRICE_BRUTTO',
				this.roundPrice(
					this.calculatePriceWithTax(fieldStorage.getPriceNetto(), fieldStorage.getTaxRate())
				)
			);
		}
	}

	updateResultPrices(fieldStorage: FieldStorage): void
	{
		// price without tax
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

		fieldStorage.setField('PRICE_EXCLUSIVE', this.roundPrice(exclusivePrice));
		fieldStorage.setField(
			'PRICE',
			this.roundPrice(this.calculatePriceWithTax(exclusivePrice, fieldStorage.getTaxRate()))
		);
	}

	activateCustomized(fieldStorage: FieldStorage): void
	{
		fieldStorage.setField('CUSTOMIZED', 'Y');
	}

	updateDiscount(fieldStorage: FieldStorage): void
	{
		if (fieldStorage.isEmptyDiscount())
		{
			this.clearResultPrices(fieldStorage);
		}
		else if (fieldStorage.isDiscountPercentage())
		{
			fieldStorage.setField(
				'DISCOUNT_SUM',
				this.round(fieldStorage.getPriceNetto() - fieldStorage.getPriceExclusive())
			);
		}
		else if (fieldStorage.isDiscountMonetary())
		{
			fieldStorage.setField(
				'DISCOUNT_RATE',
				this.round(
					this.calculateDiscountRate(
						fieldStorage.getPriceNetto(),
						fieldStorage.getPriceNetto() - fieldStorage.getDiscountSum()
					),
					this.getCommonPrecision()
				)
			);
		}
	}

	updateRowDiscount(fieldStorage: FieldStorage): void
	{
		fieldStorage.setField(
			'DISCOUNT_ROW',
			this.roundPrice(fieldStorage.getDiscountSum() * fieldStorage.getQuantity())
		);
	}

	updateTax(fieldStorage: FieldStorage): void
	{
		let sum;

		if (fieldStorage.isTaxIncluded())
		{
			sum =
				fieldStorage.getPrice()
				* fieldStorage.getQuantity()
				* (1 - 1 / (1 + fieldStorage.getTaxRate() / 100))
			;
		}
		else
		{
			sum =
				fieldStorage.getPriceExclusive()
				* fieldStorage.getQuantity()
				* (fieldStorage.getTaxRate() / 100)
			;
		}

		fieldStorage.setField('TAX_SUM', this.roundPrice(sum));
	}

	updateSum(fieldStorage: FieldStorage): void
	{
		let sum;

		if (fieldStorage.isTaxIncluded())
		{
			sum = fieldStorage.getPrice() * fieldStorage.getQuantity();
		}
		else
		{
			sum = this.calculatePriceWithTax(
				fieldStorage.getPriceExclusive() * fieldStorage.getQuantity(),
				fieldStorage.getTaxRate()
			);
		}

		fieldStorage.setField('SUM', this.roundPrice(sum));
	}

	calculateDiscountRate(originalPrice: number, price: number): number
	{
		if (originalPrice === 0.0)
		{
			return 0.0;
		}

		if (price === 0.0)
		{
			return originalPrice > 0 ? 100.0 : -100.0;
		}

		return (originalPrice - price) / originalPrice * 100;
	}

	calculatePriceWithoutTax(price: number, taxRate: number)
	{
		// Tax is not included in price
		return price / (1 + (taxRate / 100));
	}

	calculatePriceWithTax(price: number, taxRate: number): number
	{
		// Tax is included in price
		return price + price * taxRate / 100;
	}

	round(value: number, precision = ProductCalculator.DEFAULT_PRECISION): number
	{
		const factor = Math.pow(10, precision);

		return Math.round(value * factor) / factor;
	}

	roundPrice(value: number): number
	{
		return this.round(value, this.getPricePrecision());
	}
}