import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';

const PAGE_SIZE = 15;

export type GifItem = {
	preview: string,
	original: string,
}

export class GifService
{
	constructor()
	{
		this.pageNumber = 1;
		this.hasMoreItemsToLoad = true;
	}

	getPopular(): Promise<GifItem[]>
	{
		return runAction(RestMethod.imBotGiphyListPopular, {})
			.catch((error) => {
				Logger.error('GiphyLoadService error', error);
			});
	}

	getQuery(searchQuery: string, nextPage): Promise<GifItem[]>
	{
		if (nextPage)
		{
			this.pageNumber++;
		}
		else
		{
			this.pageNumber = 1;
			this.hasMoreItemsToLoad = true;
		}

		return runAction(RestMethod.imBotGiphyList, {
			data: {
				filter: {
					search: searchQuery,
				},
				limit: PAGE_SIZE,
				offset: this.pageNumber * PAGE_SIZE,
			},
		})
			.then((gifs: GifItem[]) => {
				if (gifs.length < PAGE_SIZE)
				{
					this.hasMoreItemsToLoad = false;
				}

				return gifs;
			})

			.catch((error) => {
				Logger.error('GiphyLoadService error', error);
			});
	}
}
