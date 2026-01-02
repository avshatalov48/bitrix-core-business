import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

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
			.catch(([error]) => {
				console.error('GiphyLoadService error', error);
				throw error;
			});
	}

	async getQuery(searchQuery: string, nextPage): Promise<GifItem[]>
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

		const payload = {
			data: {
				filter: {
					search: searchQuery,
				},
				limit: PAGE_SIZE,
				offset: this.pageNumber * PAGE_SIZE,
			},
		};

		const gifs: GifItem[] = await runAction(RestMethod.imBotGiphyList, payload)
			.catch(([error]) => {
				console.error('GiphyLoadService error', error);
				throw error;
			});

		if (gifs.length < PAGE_SIZE)
		{
			this.hasMoreItemsToLoad = false;
		}

		return gifs;
	}
}
