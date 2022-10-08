import {PhotoServiceBase, BasePhotoServiceRequestPhotosPropsType} from "location.core";

export default class PhotoService extends PhotoServiceBase
{
	#map;
	#service;
	#googleSource;
	#loadingPromise;

	constructor(props)
	{
		super(props);
		this.#googleSource = props.googleSource;
		this.#map = props.map;
	}

	#getLoaderPromise()
	{
		if(!this.#loadingPromise)
		{
			//map haven't rendered yet	`
			if(this.#map.loaderPromise === null)
			{
				return;
			}

			this.#loadingPromise = this.#map.loaderPromise.then(() => {
				this.#service = new google.maps.places.PlacesService(this.#map.googleMap);
			});
		}

		return this.#loadingPromise;
	}

	requestPhotos(props: BasePhotoServiceRequestPhotosPropsType): Promise
	{
		return new Promise((resolve) => {

			let promise = this.#getLoaderPromise();

			if(!promise)
			{
				resolve([]);
			}

			let loaderPromise = this.#getLoaderPromise();

			if(!loaderPromise)
			{
				resolve([]);
			}

			loaderPromise
			 .then(() => {
				if(props.location.sourceCode !== this.#googleSource.sourceCode)
				{
					resolve([]);
					return;
				}

				if(props.location.externalId.length <= 0)
				{
					resolve([]);
					return;
				}

				this.#service.getDetails(
					{
						placeId: props.location.externalId,
						fields: ['photos']
					},
					function(place, status)
					{
						let resultPhotos = [];

						if (status === google.maps.places.PlacesServiceStatus.OK)
						{
							if(Array.isArray(place.photos))
							{
								let count = 0;

								for(let gPhoto of place.photos)
								{
									resultPhotos.push({
										url: gPhoto.getUrl(),
										width: gPhoto.width,
										height: gPhoto.height,
										description: Array.isArray(gPhoto.html_attributions) ? gPhoto.html_attributions.join('<br>') : '',
										thumbnail: {
											url: gPhoto.getUrl({
												maxHeight: props.thumbnailHeight,
												maxWidth: props.thumbnailWidth
											}),
											width: props.thumbnailWidth,
											height: props.thumbnailHeight
										}
									});

									count++;

									if(props.maxPhotoCount && count >= props.maxPhotoCount)
									{
										break;
									}
								}
							}
						}
						resolve(resultPhotos);
					}
				);
			});
		});
	}
}