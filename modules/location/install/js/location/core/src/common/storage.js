import JsonConverter from '../entity/address/converter/jsonconverter';
import Address from '../entity/address';

export default class Storage
{
	#lastAddressLocalStorageKey = `bitrixLocationLastAddress`;

	static #instance = null;

	static getInstance()
	{
		if(Storage.#instance === null)
		{
			Storage.#instance = new Storage();
		}

		return Storage.#instance;
	}

	set lastAddress(address: ?Address)
	{
		if (address)
		{
			BX.localStorage.set(this.#lastAddressLocalStorageKey, {'json': address.toJson()}, 86400 * 30);
		}
	}
	get lastAddress()
	{
		const lastAddress = BX.localStorage.get(this.#lastAddressLocalStorageKey);
		if (lastAddress && lastAddress['json'])
		{
			try
			{
				return JsonConverter.convertJsonToAddress(JSON.parse(lastAddress['json']));
			}
			catch(e) {}
		}

		return null;
	}
}
