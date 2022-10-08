import {Tag, Dom} from "main.core";
import Photo from "./photo";

export default class Gallery
{
	#photos = [];
	#container = null;
	#photosContainer = null;
	#thumbnailHeight;
	#thumbnailWidth;
	#photoService;
	#maxPhotoCount;
	#location;

	constructor(props)
	{
		this.#thumbnailHeight = props.thumbnailHeight;
		this.#thumbnailWidth = props.thumbnailWidth;
		this.#maxPhotoCount = props.maxPhotoCount;
		this.#photoService = props.photoService;
	}

	refresh()
	{
		if(this.#location)
		{
			this.#photoService.requestPhotos({				
				location: this.#location,
				thumbnailHeight: this.#thumbnailHeight,
				thumbnailWidth: this.#thumbnailWidth,
				maxPhotoCount: this.#maxPhotoCount
				
			})
			.then((photosData) => {
				if(Array.isArray(photosData) && photosData.length > 0)
				{
					this.#setPhotos(photosData);
					this.show();
				}
				else
				{
					this.hide();
				}
			});
		}
		else
		{
			this.hide();
		}
	}

	set location(location: Location)
	{
		this.#location = location;
		this.refresh();
	}

	#setPhotos(photosData)
	{
		if(!this.#location)
		{
			return;
		}

		let photos = [];

		for(let photo of photosData)
		{
			photos.push(
				new Photo({
					url: photo.thumbnail.url,
					link: photo.url,
					location: this.#location,
					title: this.#location.name + " ( " + BX.util.strip_tags(photo.description) + ' )'
				})
			);
		}

		if(!Array.isArray(photos))
		{
			BX.debug('Wrong type of photos. Must be array');
			return;
		}

		this.#photos = [];

		for(let photo of photos)
		{
			this.#photos.push(photo);
		}

		if(this.#photos.length > 0 && this.#photosContainer)
		{
			let renderedPhotos = this.#photos ? this.#renderPhotos(this.#photos) : '';

			this.#photosContainer.innerHTML = '';

			if(renderedPhotos.length > 0)
			{
				for (let photo of renderedPhotos)
				{
					this.#photosContainer.appendChild(photo);
				}
			}
		}
	}

	hide()
	{
		if(this.#container)
		{
			this.#container.style.display = 'none';
		}
	}

	isHidden()
	{
		return !this.#container || this.#container.clientWidth <= 0;
	}

	show()
	{
		if(this.#container)
		{
			this.#container.style.display = 'block';
		}
	}

	render()
	{
		this.#photosContainer = Tag.render`					
				<div class="location-map-photo-inner">					
				</div>`;

		this.#container = Tag.render`
			<div class="location-map-photo-container">
				${this.#photosContainer}
			</div>`;

		return this.#container;
	}

	#renderPhotos(photos)
	{
		let result = [];

		for (let photo of photos)
		{
			result.push(photo.render());
		}

		return result;
	}
}