import { RestClient } from 'rest.client';
import { Dexie } from 'ui.dexie';

export class SmileManager
{
	constructor(restClient)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
		if (typeof restClient === 'undefined')
		{
			this.restClient = new RestClient();
		}
		else
		{
			this.restClient = restClient;
		}

		this.db = new Dexie('bx-ui-smiles');
		this.db.version(1).stores({
			sets: 'id, parentId, name, type, image',
			smiles: 'id, setId, name, image, typing, width, height, originalWidth, originalHeight, definition',
		});
	}

	loadFromCache(): BX.Promise
	{
		const promise = new BX.Promise();

		const sets = [];
		const smiles = [];

		this.db.transaction('r', this.db.sets, this.db.smiles, () => {
			this.db.sets.each((set) => {
				return this.db.smiles.where('setId').equals(set.id).first().then((smile) => {
					sets.push({ ...set, image: smile.image });
				})
					.catch((error) => promise.reject(error));
			}).then(() => {
				return this.db.smiles.where('setId').equals(sets[0].id).each((smile) => {
					smiles.push(smile);
				});
			}).then(() => {
				const promiseResult = { sets, smiles };
				promise.resolve(promiseResult);
			}).catch((error) => promise.reject(error));
		});

		return promise;
	}

	loadFromServer(): BX.Promise
	{
		const promise = new BX.Promise();

		this.restClient.callMethod('smile.get').then((result) => {
			const sets = [];
			const smiles = [];

			const answer = result.data();

			const setImage = {};

			answer.smiles = answer.smiles.map((smile) => {
				if (!setImage[smile.setId])
				{
					setImage[smile.setId] = smile.image;
				}

				let originalWidth = smile.width;
				if (smile.definition === 'HD')
				{
					originalWidth *= 2;
				}
				else if (smile.definition === 'UHD')
				{
					originalWidth *= 4;
				}

				let originalHeight = smile.height;
				if (smile.definition === 'HD')
				{
					originalHeight *= 2;
				}
				else if (smile.definition === 'UHD')
				{
					originalHeight *= 4;
				}

				return { ...smile, originalWidth, originalHeight };
			});

			answer.sets.forEach((set) => {
				sets.push({ ...set, image: setImage[set.id] });
			});

			answer.smiles.forEach((smile) => {
				if (parseInt(smile.setId, 10) === parseInt(sets[0].id, 10))
				{
					smiles.push(smile);
				}
			});

			const promiseResult = { sets, smiles };

			promise.resolve(promiseResult);

			this.db.smiles.clear().then(() => {
				return this.db.sets.clear().then(() => {
					this.db.sets.bulkAdd(sets);
					this.db.smiles.bulkAdd(answer.smiles);
				}).catch((error) => promise.reject(error));
			}).catch((error) => promise.reject(error));
		}).catch((error) => promise.reject(error));

		return promise;
	}

	changeSet(setId: number): BX.Promise
	{
		const promise = new BX.Promise();

		this.db.smiles.where('setId').equals(setId).toArray((smiles) => {
			promise.resolve(smiles);
		}).catch((error) => promise.reject(error));

		return promise;
	}
}
