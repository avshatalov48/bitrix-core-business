import {Type, Text} from "main.core";

type PriceCalculatorFields = {
	basePrice: number,
	finalPrice: number,
	extra: number | null,
	extraType: PriceCalculator.EXTRA_TYPE_MONETARY | PriceCalculator.EXTRA_TYPE_PERCENTAGE,
}

export class PriceCalculator
{
	static EXTRA_TYPE_PERCENTAGE = 1;
	static EXTRA_TYPE_MONETARY = 2;

	#fields: PriceCalculatorFields = {
		basePrice: 0,
		finalPrice: 0,
		extra: null,
		extraType: PriceCalculator.EXTRA_TYPE_PERCENTAGE,
	};


	constructor(fields: PriceCalculatorFields): void
	{
		this.#fields = {...this.#fields, ...fields};
	}

	getBasePrice(): number
	{
		return this.#fields.basePrice;
	}

	getFinalPrice(): number
	{
		return this.#fields.finalPrice;
	}

	getExtra(): number | null
	{
		return this.#fields.extra;
	}

	getExtraType(): PriceCalculator.EXTRA_TYPE_MONETARY | PriceCalculator.EXTRA_TYPE_PERCENTAGE
	{
		return this.#fields.extraType;
	}

	calculateBasePrice(basePrice: number): PriceCalculator
	{
		this.#fields.basePrice = basePrice;
		this.#fields.extra = Text.toNumber(this.#fields.extra);

		if (this.#fields.extraType === PriceCalculator.EXTRA_TYPE_MONETARY)
		{
			this.#fields.finalPrice = this.#fields.basePrice + this.#fields.extra;
		}
		else
		{
			this.#fields.finalPrice = this.#fields.basePrice * (1 + this.#fields.extra / 100);
		}

		return this;
	}

	calculateFinalPrice(finalPrice: number): PriceCalculator
	{
		this.#fields.finalPrice = finalPrice;
		const basePrice = Text.toNumber(this.#fields.basePrice);

		if (basePrice <= 0)
		{
			this.#fields.extraType = PriceCalculator.EXTRA_TYPE_MONETARY;
		}

		if (this.#fields.extraType === PriceCalculator.EXTRA_TYPE_MONETARY)
		{
			this.#fields.extra = this.#fields.finalPrice - basePrice;
		}
		else
		{
			this.#fields.extra = (this.#fields.finalPrice / basePrice - 1) * 100;
		}

		return this;
	}

	calculateExtra(extra: number): PriceCalculator
	{
		this.#fields.extra = extra;
		if (Type.isNil(extra))
		{
			return this;
		}

		return this.calculateBasePrice(this.#fields.basePrice);
	}

	calculateExtraType(extraType: PriceCalculator.EXTRA_TYPE_MONETARY | PriceCalculator.EXTRA_TYPE_PERCENTAGE): PriceCalculator
	{
		if (extraType !== PriceCalculator.EXTRA_TYPE_MONETARY)
		{
			extraType = PriceCalculator.EXTRA_TYPE_PERCENTAGE;
		}

		this.#fields.extraType = extraType;

		return this.calculateFinalPrice(this.#fields.finalPrice);
	}
}