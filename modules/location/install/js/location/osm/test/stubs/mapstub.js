export default class MapStub
{
	hasLayerValue = true;
	zoom = 0;
	lat = 0;
	lon = 0;

	hasLayer()
	{
		return this.hasLayerValue;
	}

	panTo(latLon)
	{
		this.lat = latLon[0];
		this.lon = latLon[1];
	}

	setZoom(zoom)
	{
		this.zoom = zoom;
	}
}