import {Address} from 'location.core';
import BaseRepository from './baserepository';

export default class AddressRepository extends BaseRepository
{
	constructor(props = {})
	{
		props.path = 'location.api.address';
		super(props);
	}

	findById(addressId: number)
	{
		if(addressId <= 0)
		{
			throw new Error('addressId must be more than zero');
		}

		return this.actionRunner.run(
			'findById',
			{
				addressId: addressId,
			})
			.then(this.processResponse)
			.then((address) => { // address json data or null
				let result = null;

				if(address)
				{
					result = this.convertJsonToAddress(address);
				}

				return result;
		});
	}

	save(address)
	{
		if(!address)
		{
			throw new Error('address must be defined');
		}

		return this.actionRunner.run(
			'save',
			{
				address: address,
			})
			.then(this.processResponse)
			.then((response) => {	//Address json data
				let result = null;

				if(typeof response === 'object')
				{
					result = this.convertJsonToAddress(response);
				}

				return result;
			});
	}

	convertJsonToAddress(jsonData)
	{
		return new Address(jsonData);
	}
}

