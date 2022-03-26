import {Runtime, Type} from 'main.core';
import type {FieldScheme} from './field-scheme';
import type {DiscountTypes} from './discount-type';
import {DiscountType} from './discount-type';
import {ProductCalculator} from "catalog.product-calculator";

const initialFields = {
	QUANTITY: 1,
	PRICE: 0,
	PRICE_EXCLUSIVE: 0,
	PRICE_NETTO: 0,
	PRICE_BRUTTO: 0,
	CUSTOMIZED: 'N',
	DISCOUNT_TYPE_ID: DiscountType.UNDEFINED,
	DISCOUNT_RATE: 0,
	DISCOUNT_SUM: 0,
	DISCOUNT_ROW: 0,
	TAX_INCLUDED: 'N',
	TAX_RATE: 0,
	TAX_SUM: 0,
	SUM: 0
};

export class FieldStorage
{
	fields: FieldScheme;

	constructor(fields: FieldScheme, calculator: ProductCalculator)
	{
		this.fields = {...initialFields};

		if (Type.isPlainObject(fields))
		{
			this.fields = {...this.fields, ...fields};
		}

		this.calculator = calculator;
	}

	#getPricePrecision()
	{
		return this.calculator.getPricePrecision();
	}

	#getCommonPrecision()
	{
		return this.calculator.getCommonPrecision();
	}

	#getQuantityPrecision()
	{
		return this.calculator.getQuantityPrecision();
	}

	getFields()
	{
		return Runtime.clone(this.fields);
	}

	getField(name: string, defaultValue)
	{
		return this.fields.hasOwnProperty(name) ? this.fields[name] : defaultValue;
	}

	setField(name: string, value): void
	{
		value = this.#validateValue(name, value);
		this.fields[name] = value;
	}

	#validateValue(name: string, value): any
	{
		const priceFields = [
			'PRICE',
			'PRICE_EXCLUSIVE',
			'PRICE_NETTO',
			'PRICE_BRUTTO',
			'DISCOUNT_SUM',
			'DISCOUNT_ROW',
			'TAX_SUM',
			'SUM'
		];
		if (name === 'DISCOUNT_TYPE_ID')
		{
			value =
				(value === DiscountType.PERCENTAGE || value === DiscountType.MONETARY)
					? value
					: DiscountType.UNDEFINED
			;

		}
		else if (name === 'QUANTITY')
		{
			value = FieldStorage.#round(value, this.#getQuantityPrecision());
		}
		else if (name === 'CUSTOMIZED' || name === 'TAX_INCLUDED' )
		{
			value = (value === 'Y') ? 'Y' : 'N';
		}
		else if (name === 'TAX_RATE' || name === 'DISCOUNT_RATE')
		{
			value = FieldStorage.#round(value, this.#getCommonPrecision());
		}
		else if (priceFields.includes(name))
		{
			value = FieldStorage.#round(value, this.#getPricePrecision());
		}

		return value;
	}

	static #round(value: number, precision = ProductCalculator.DEFAULT_PRECISION): number
	{
		const factor = Math.pow(10, precision);

		return Math.round(value * factor) / factor;
	}

	getBasePrice(): number
	{
		return this.getField('BASE_PRICE', 0);
	}

	getPrice(): number
	{
		return this.getField('PRICE', 0);
	}

	getPriceExclusive(): number
	{
		return this.getField('PRICE_EXCLUSIVE', 0);
	}

	getPriceNetto(): number
	{
		return this.getField('PRICE_NETTO', 0);
	}

	getPriceBrutto(): number
	{
		return this.getField('PRICE_BRUTTO', 0);
	}

	getQuantity(): number
	{
		return this.getField('QUANTITY', 1);
	}

	getDiscountType(): DiscountTypes
	{
		return this.getField('DISCOUNT_TYPE_ID', DiscountType.UNDEFINED);
	}

	isDiscountUndefined(): boolean
	{
		return this.getDiscountType() === DiscountType.UNDEFINED;
	}

	isDiscountPercentage(): boolean
	{
		return this.getDiscountType() === DiscountType.PERCENTAGE;
	}

	isDiscountMonetary(): boolean
	{
		return this.getDiscountType() === DiscountType.MONETARY;
	}

	isDiscountHandmade(): boolean
	{
		return this.isDiscountPercentage() || this.isDiscountMonetary();
	}

	getDiscountRate(): number
	{
		return this.getField('DISCOUNT_RATE', 0);
	}

	getDiscountSum(): number
	{
		return this.getField('DISCOUNT_SUM', 0);
	}

	getDiscountRow(): number
	{
		return this.getField('DISCOUNT_ROW', 0);
	}

	isEmptyDiscount(): boolean
	{
		if (this.isDiscountPercentage())
		{
			return this.getDiscountRate() === 0;
		}

		if (this.isDiscountMonetary())
		{
			return this.getDiscountSum() === 0;
		}

		return this.isDiscountUndefined();
	}

	getTaxIncluded(): 'Y' | 'N'
	{
		return this.getField('TAX_INCLUDED', 'N');
	}

	isTaxIncluded(): boolean
	{
		return this.getTaxIncluded() === 'Y';
	}

	getTaxRate(): number
	{
		return this.getField('TAX_RATE', 0);
	}

	getTaxSum(): number
	{
		return this.getField('TAX_SUM', 0);
	}

	getSum(): number
	{
		return this.getField('SUM', 0);
	}
}