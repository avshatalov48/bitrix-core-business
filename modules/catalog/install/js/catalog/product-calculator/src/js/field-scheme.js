import type {DiscountTypes} from './discount-type';

export type FieldScheme = {
	QUANTITY: number,
	BASE_PRICE: number,
	PRICE: number,
	PRICE_EXCLUSIVE: number,
	PRICE_NETTO: number,
	PRICE_BRUTTO: number,
	CUSTOMIZED: 'Y' | 'N',
	DISCOUNT_TYPE_ID: DiscountTypes,
	DISCOUNT_RATE: number,
	DISCOUNT_SUM: number,
	DISCOUNT_ROW: number,
	TAX_INCLUDED: 'Y' | 'N',
	TAX_RATE: number,
	TAX_SUM: number,
	SUM: number
}