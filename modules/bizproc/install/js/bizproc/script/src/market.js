let instance = null;

export default class Market
{
	static get Instance(): Market
	{
		if(instance === null)
		{
			instance = new Market();
		}

		return instance;
	}

	showForPlacement(placement: string)
	{
		if (BX.rest && BX.rest.Marketplace)
		{
			BX.rest.Marketplace.open({PLACEMENT: placement});
		}
	}
}