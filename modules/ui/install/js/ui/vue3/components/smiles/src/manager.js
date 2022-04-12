import {Dexie} from "ui.dexie";
import {RestClient} from "rest.client";

export class SmileManager
{
	constructor(restClient)
	{
		if (typeof restClient !== 'undefined')
		{
			this.restClient = restClient;
		}
		else
		{
			this.restClient = new RestClient;
		}

		this.db = new Dexie('bx-ui-smiles');
		this.db.version(1).stores({
			sets: "id, parentId, name, type, image",
			smiles: "id, setId, name, image, typing, width, height, originalWidth, originalHeight, definition",
		});
	}

	loadFromCache()
	{
		let promise = new BX.Promise();

		let sets = [];
		let smiles = [];

		let timer = new Date();

		this.db.transaction('r', this.db.sets, this.db.smiles, () =>
		{
			this.db.sets.each(set => {
				return this.db.smiles.where('setId').equals(set.id).first().then(smile => {
					sets.push({...set, image: smile.image});
				}).catch(error => promise.reject(error));
			}).then(() => {
				return this.db.smiles.where('setId').equals(sets[0].id).each(smile => {
					smiles.push(smile);
				});
			}).then(() => {
				let promiseResult = {sets, smiles};
				promise.resolve(promiseResult);
			}).catch(error => promise.reject(error));
		});

		return promise;
	}

	loadFromServer()
	{
		let promise = new BX.Promise();
		let timer = new Date();

		this.restClient.callMethod('smile.get').then(result =>
		{
			let sets = [];
			let smiles = [];

			let answer = result.data();

			let setImage = {};

			answer.smiles = answer.smiles.map(function(smile){
				if (!setImage[smile.setId])
				{
					setImage[smile.setId] = smile.image;
				}

				let originalWidth = smile.width;
				if (smile.definition == 'HD')
				{
					originalWidth = originalWidth*2;
				}
				else if (smile.definition == 'UHD')
				{
					originalWidth = originalWidth*4;
				}

				let originalHeight = smile.height;
				if (smile.definition == 'HD')
				{
					originalHeight = originalHeight*2;
				}
				else if (smile.definition == 'UHD')
				{
					originalHeight = originalHeight*4;
				}

				return {...smile, originalWidth, originalHeight}
			});

			answer.sets.forEach(set => {
				sets.push({...set, image: setImage[set.id]});
			});

			answer.smiles.forEach(smile => {
				if (smile.setId == sets[0].id)
				{
					smiles.push(smile);
				}
			});

			let promiseResult = {sets, smiles};

			promise.resolve(promiseResult);

			this.db.smiles.clear().then(() => {
				return this.db.sets.clear().then(() => {
					this.db.sets.bulkAdd(sets);
					this.db.smiles.bulkAdd(answer.smiles);
				}).catch(error => promise.reject(error));
			}).catch(error => promise.reject(error));

		}).catch(error => promise.reject(error));

		return promise;
	}

	changeSet(setId)
	{
		let promise = new BX.Promise();

		this.db.smiles.where('setId').equals(setId).toArray(smiles => {
			promise.resolve(smiles);
		}).catch(error => promise.reject(error));

		return promise;
	}
}

