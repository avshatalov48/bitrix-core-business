import {Product} from "./product";

export class Sku extends Product
{
	TYPE = 'sku';

	getFileType(): string
	{
		return (this.config.fileType === Product.TYPE) ? Product.TYPE : Sku.TYPE;
	}

	getProductId()
	{
		return this.getConfig('productId');
	}
}