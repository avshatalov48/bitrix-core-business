import type {FormTotal} from "./form-total";
import type {BasketItem} from "./basket-item";

export type FormScheme = {
	currency: string,
	taxIncluded: 'Y' | 'N',
	basket: Array<BasketItem>,
	total: FormTotal,
}