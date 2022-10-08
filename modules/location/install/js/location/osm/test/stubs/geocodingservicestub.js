import GeocodingService from '../../src/geocodingservice';

export default class GeoCodingserviceStub extends GeocodingService
{
	constructor()
	{
		super({
			requester: {request: function()
			{
				return null;
			}}
		});
	}
}