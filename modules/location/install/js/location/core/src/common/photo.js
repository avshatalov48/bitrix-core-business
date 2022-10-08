import {type Thumbnail} from 'thumbnail';

export type Photo =
{
	url: string;
	width: number; /* pixels */
	height: number; /* pixels */
	description: string;
	thumbnail: Thumbnail;
}