import {Product as ProductConst} from "sale.checkout.const";

class Product
{
	static isService(item)
	{
		return item.product.type === ProductConst.type.service;
	}

	static isLimitedQuantity(item)
	{
		return item.product.checkMaxQuantity === 'Y'
	}
}

export {
	Product
}