import {FormElementPosition} from "./form-element-position";
import type {BasketItem} from "./basket-item";
import type {BasketMeasure} from "./basket-measure";
import type {BasketTax} from "./basket-tax";
import type {DiscountTypes} from "catalog.product-calculator";

export type FormOption = {
	basket: Array<BasketItem>,
	measures: Array<BasketMeasure>,
	iblockId?: number,
	basePriceId?: number,
	taxList: Array<BasketTax>,
	currencySymbol?: string,
	singleProductMode: boolean,
	showResults: boolean,
	enableEmptyProductError: boolean,
	currency?: string,
	pricePrecision: number,
	allowedDiscountTypes: Array<DiscountTypes>,
	taxIncluded: 'Y' | 'N',
	showDiscountBlock: 'Y' | 'N',
	showTaxBlock: 'Y' | 'N',
	newItemPosition: FormElementPosition,
	buttonsPosition: FormElementPosition,
	urlBuilderContext: string,
	hideUnselectedProperties: boolean,
}