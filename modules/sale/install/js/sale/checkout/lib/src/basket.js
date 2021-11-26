class Basket
{
	static toFixed(quantity, measureRatio, availableQuantity = 0)
	{
		let precisionFactor =  Math.pow(10, 6);
		let reminder = (quantity / measureRatio - ((quantity / measureRatio).toFixed(0))).toFixed(5),
			remain;



		if (parseFloat(reminder) === 0)
		{
			return quantity;
		}
		
		if (measureRatio !== 0 && measureRatio !== 1)
		{
			remain = (quantity * precisionFactor) % (measureRatio * precisionFactor) / precisionFactor;

			if (measureRatio > 0 && remain > 0)
			{
				if (
					remain >= measureRatio / 2
					&& (
						availableQuantity === 0
						|| (quantity + measureRatio - remain) <= availableQuantity
					)
				)
				{
					quantity += (measureRatio * precisionFactor - remain * precisionFactor) / precisionFactor;
				}
				else
				{
					quantity = (quantity * precisionFactor -  remain * precisionFactor) / precisionFactor;
				}
			}
		}
		
		return quantity;
	}

	// isRatioFloat(value)
	// {
	// 	return parseInt(value) !== parseFloat(value)
	// }

	static isValueFloat(value)
	{
		return parseInt(value) !== parseFloat(value)
	}

	static roundValue(value)
	{
		if(Basket.isValueFloat(value))
		{
			return Basket.roundFloatValue(value)
		}
		else
		{
			return parseInt(value, 10)
		}
	}

	static roundFloatValue(value)
	{
		let precision = 6;
		let precisionFactor = Math.pow(10, precision);

		return Math.round(parseFloat(value) * precisionFactor) / precisionFactor;
	}
}

export {
	Basket
}