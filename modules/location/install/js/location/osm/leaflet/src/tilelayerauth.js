import * as Leaflet from './vendor/leaflet-src.esm';
import TokenContainer from '../../src/tokencontainer';

export default class TileLayerAuth extends Leaflet.TileLayer
{
	#tokenContainer;
	#hostName;
	#waitingRequests = [];
	#processingUnauthorized = false;

	setTokenContainer(tokenContainer: TokenContainer)
	{
		this.#tokenContainer = tokenContainer;
	}

	setHostName(hostName: string)
	{
		this.#hostName = hostName;
	}

	requestTile(url: string, img: Element, done: Function, isUnAuth: boolean)
	{
		fetch(url, {
			method: 'GET',
			cache: 'force-cache',
			headers: new Headers({
				'Authorization': `Bearer ${this.#tokenContainer.token}`,
				'Bx-Location-Osm-Host': this.#hostName,
			}),
		})
			.then((response) => {

				if(response.status === 200)
				{
					return response.blob();
				}

				if(response.status === 401 && !isUnAuth)
				{
					this.#processUnauthorizedResponse(url, img, done);
					return null;
				}

				console.error(`Response status: ${response.status}`);
			})
			.then((blobResponse) =>
			{
				if(blobResponse)
				{
					const reader = new FileReader();
					reader.onload = () =>
					{
						img.src = reader.result;
					};
					reader.readAsDataURL(blobResponse);
					done(null, img);
				}
			})
			.catch((response) => {
				console.error(response);
			});
	}

	createTile(coords, done) {
		const url = this.getTileUrl(coords);
		const img = document.createElement('img');

		if(this.#processingUnauthorized)
		{
			this.#waitingRequests.push([url, img, done]);
		}
		else
		{
			this.requestTile(url, img, done, false);
		}

		return img;
	}

	#processUnauthorizedResponse(url, img, done)
	{
		this.#processingUnauthorized = true;
		this.#waitingRequests.push([url, img, done]);

		this.#tokenContainer.refreshToken()
			.then((sourceToken) => {

				while(this.#waitingRequests.length > 0)
				{
					const item = 	this.#waitingRequests.pop();
					setTimeout(() => {
						this.requestTile(item[0], item[1], item[2], true);
					}, 1);
				}

				this.#processingUnauthorized = false;
			});
	}
}