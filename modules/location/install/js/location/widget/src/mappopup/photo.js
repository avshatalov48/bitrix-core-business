import {Tag} from "main.core";

export default class Photo
{
	#description;
	#url;
	#link;
	#location;
	#title;

	constructor(props)
	{
		this.#url = props.url;
		this.#link = props.link || '';
		this.#description = props.description || '';
		this.#location = props.location;
		this.#title = props.title || '';
	}

	render()
	{
		let description = '';

		if(this.#description)
		{
			//todo: sanitize
			description = Tag.render`<span class="location-map-item-description">${this.#description}</span>`;
		}

		return Tag.render`
			<div class="location-map-photo-item-block">
				<span class="location-map-photo-item-block-image-block-inner">
					${description}
					<span 
						data-viewer data-viewer-type="image" 
						data-src="${this.#link}" 
						data-title="${this.#title}"
						class="location-map-item-photo-image" 
						data-viewer-group-by="${this.#location.externalId}"
						style="background-image: url(${this.#url});">							
					</span>
				</span>
			</div>`;
	}
}