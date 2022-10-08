import type {FieldScheme} from './field-scheme';
import type {DiscountTypes} from './discount-type';
import {TaxForPriceStrategy} from './strategy/tax-for-price-strategy';

export class ProductCalculator
{
	#fields: FieldScheme = {};
	#strategy: TaxForPriceStrategy = {};
	#settings = {};

	static DEFAULT_PRECISION: number = 2;

	constructor(fields: FieldScheme = {}, settings = {})
	{
		this.setFields(fields);
		this.setSettings(settings);
		this.setCalculationStrategy(new TaxForPriceStrategy(this));
	}

	setField(name, value): ProductCalculator
	{
		this.#fields[name] = value;

		return this;
	}

	setCalculationStrategy(strategy: TaxForPriceStrategy = {}): ProductCalculator
	{
		this.#strategy = strategy;

		return this;
	}

	setFields(fields: FieldScheme): ProductCalculator
	{
		for (const name in fields)
		{
			if (fields.hasOwnProperty(name))
			{
				this.setField(name, fields[name]);
			}
		}

		return this;
	}

	getFields(): FieldScheme
	{
		return {...this.#fields};
	}

	setSettings(settings = {}): ProductCalculator
	{
		this.#settings = {...settings};

		return this;
	}

	getSettings(): {}
	{
		return {...this.#settings};
	}

	#getSetting(name, defaultValue)
	{
		return this.#settings.hasOwnProperty(name) ? this.#settings[name] : defaultValue;
	}

	getPricePrecision()
	{
		return this.#getSetting('pricePrecision', ProductCalculator.DEFAULT_PRECISION);
	}

	getCommonPrecision()
	{
		return this.#getSetting('commonPrecision', ProductCalculator.DEFAULT_PRECISION);
	}

	getQuantityPrecision()
	{
		return this.#getSetting('quantityPrecision', ProductCalculator.DEFAULT_PRECISION);
	}

	calculateBasePrice(value: number): FieldScheme
	{
		return this.#strategy.calculateBasePrice(value);
	}

	calculatePrice(value: number): FieldScheme
	{
		return this.#strategy.calculatePrice(value);
	}

	calculateQuantity(value: number): FieldScheme
	{
		return this.#strategy.calculateQuantity(value);
	}

	calculateDiscount(value: number): FieldScheme
	{
		return this.#strategy.calculateDiscount(value);
	}

	calculateDiscountType(value: DiscountTypes): FieldScheme
	{
		return this.#strategy.calculateDiscountType(value);
	}

	calculateRowDiscount(value: number): FieldScheme
	{
		return this.#strategy.calculateRowDiscount(value);
	}

	calculateTax(value: number | null): FieldScheme
	{
		return this.#strategy.calculateTax(value);
	}

	calculateTaxIncluded(value: 'Y' | 'N'): FieldScheme
	{
		return this.#strategy.calculateTaxIncluded(value);
	}

	calculateRowSum(value: number): FieldScheme
	{
		return this.#strategy.calculateRowSum(value);
	}
}