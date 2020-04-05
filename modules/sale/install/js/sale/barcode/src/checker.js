/**
 * Check if barcode exist
 */
export default class Checker
{
	/**
	 * @param {string} barcode
	 * @param {integer} basketId
	 * @param {integer} orderId
	 * @param {integer} storeId
	 * @returns {Promise<T>}
	 */
	static isBarcodeExist(barcode, basketId, orderId, storeId)
	{
		return BX.ajax.runAction('sale.barcode.isBarcodeExist', {
			data: {
				barcode: barcode,
				basketId: basketId,
				orderId: orderId,
				storeId: storeId
			}
		})
		.then(
			// Success
			(response) => {
				if(response.data
					&& typeof response.data.RESULT !== 'undefined'
				)
				{
					return response.data.RESULT;
				}

				throw new Error('Result is unknown');
			}
		);
	}
}