import {Product} from "./product";

export class Sku extends Product
{
	getProductId()
	{
		return this.getConfig('productId');
	}
}