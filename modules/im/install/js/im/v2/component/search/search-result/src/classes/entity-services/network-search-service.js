import {ajax as Ajax} from 'main.core';
import {Logger} from 'im.v2.lib.logger';
import {callBatch} from 'im.v2.lib.rest';
import {RestMethod, SearchEntityIdTypes} from 'im.v2.const';
import {SearchUtils} from '../search-utils';
import {Config} from '../search-config';

const RestMethodImopenlinesNetworkJoin = 'imopenlines.network.join';

export class NetworkSearchService
{
	#searchConfig: Config;

	constructor(searchConfig: Config)
	{
		this.#searchConfig = searchConfig;
	}

	search(query: string): Promise
	{
		const config = {
			json: this.#searchConfig.getNetwork()
		};

		config.json.searchQuery = {
			'queryWords': SearchUtils.getWordsFromString(query.trim()),
			'query': query.trim(),
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.doSearch', config).then(response => {
				Logger.warn(`Im.Search: Network Search request result`, response);

				resolve(response.data.dialog.items);
			}).catch(error => reject(error));
		});
	}

	loadItem(networkCode: string): Promise
	{
		const query = {
			[RestMethodImopenlinesNetworkJoin]: {code: networkCode},
			[RestMethod.imUserGet]: {id: `$result[${RestMethodImopenlinesNetworkJoin}]`}
		};

		return callBatch(query).then(result => {
			const user = result[RestMethod.imUserGet];

			return {
				id: user.id,
				entityId: SearchEntityIdTypes.bot,
				entityType: 'network',
				title: user.name,
				customData: {imUser: SearchUtils.convertKeysToUpperCase(user)},
				avatar: user.avatar,
			};
		});
	}
}