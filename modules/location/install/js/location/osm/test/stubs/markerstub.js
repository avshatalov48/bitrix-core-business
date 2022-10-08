export default class MarkerStub
{
	map = null;
	lat = 0;
	lon = 0;
	draggable = false;

	addTo(map)
	{
		this.map = map;
	}

	remove()
	{
		this.map = null;
	}

	setLatLng(latLng)
	{
		this.lat = latLng[0];
		this.lon = latLng[1];
	}
}