import BaseRepository from "./baserepository";
import Format from "../entity/format";
import {Type} from "main.core";

/**
 * Class responsible for the addresses format obtaining.
 */
export default class FormatRepository extends BaseRepository
{
	constructor(props = {}) {
		props.path = 'location.api.format';
		super(props);
	}

	/**
	 * Find all available formats
	 * @param {string} languageId
	 * @returns {Promise}
	 */
	findAll(languageId: string): Promise
	{
		if(!Type.isString(languageId))
		{
			throw new TypeError('languageId must be type of string');
		}

		return this.actionRunner.run(
			'findAll',
			{
				languageId: languageId
			})
			.then(this.processResponse)
			.then(
				(data) => this.convertFormatCollection(data)
			);
	}

	/**
	 * Find address format by its code
	 * @param {string} formatCode
	 * @param {string} languageId
	 * @returns {Promise}
	 */
	findByCode(formatCode: string, languageId: string): Promise
	{
		if(!Type.isString(formatCode))
		{
			throw new TypeError('formatCode must be type of string');
		}

		if(!Type.isString(languageId))
		{
			throw new TypeError('languageId must be type of string');
		}

		return this.actionRunner.run(
			'findByCode',
			{
				formatCode: formatCode,
				languageId: languageId
			})
			.then(this.processResponse)
			.then(this.convertFormatData);
	}

	/**
	 * Find default address format
	 * @param {string} languageId
	 * @returns {Promise}
	 */
	findDefault(languageId: string): Promise
	{
		if(!Type.isString(languageId))
		{
			throw new TypeError('languageId must be type of string');
		}

		return this.actionRunner.run(
			'findDefault',
			{
				languageId: languageId
			})
			.then(this.processResponse)
			.then(this.convertFormatData);
	}

	convertFormatCollection(formatDataCollection: Array): Array<Format>
	{
		if(!Type.isArray(formatDataCollection))
		{
			throw new TypeError('Can\'t convert format collection data');
		}

		let result = [];

		formatDataCollection.forEach((format) => {
			result.push(
				this.convertFormatData(format)
			);
		});
		
		return result;
	}

	convertFormatData(formatData: {}): Format
	{
		if(!Type.isObject(formatData))
		{
			throw new TypeError('Can\'t convert format data');
		}

		return new Format(formatData);
	}
}
