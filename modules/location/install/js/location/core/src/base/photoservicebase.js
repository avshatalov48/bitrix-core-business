import {Location} from 'location.core';

export type BasePhotoServiceRequestPhotosPropsType = {
	location: Location,
	thumbnailHeight: number,
	thumbnailWidth: number,
	maxPhotoCount: number
}

export default class PhotoServiceBase
{
	requestPhotos(props: BasePhotoServiceRequestPhotosPropsType): Promise
	{
		throw new Error('Must be implemented');
	}
}