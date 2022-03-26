import {FieldScheme} from "catalog.product-calculator";
import type {BasketItemScheme} from "./basket-item-scheme";
import type {BasketItemError} from "./basket-item-error";

export type BasketItem = {
	offerId?: number,
	selectorId: string,
	fields: BasketItemScheme,
	calculatedFields: FieldScheme,
	showDiscount: 'Y' | 'N',
	showTax: 'Y' | 'N',
	skuTree: Array<any>,
	image: null,
	sum: number,
	discountSum: number,
	detailUrl: string,
	encodedFields?: string,
	hasEditRights: boolean,
	errors: Array<BasketItemError>,
}