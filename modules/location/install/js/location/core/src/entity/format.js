import {Type} from 'main.core';
import FormatFieldCollection from './format/formatfieldcollection';
import AddressType from './address/addresstype';
import FormatTemplateCollection from './format/formattemplatecollection';
import FormatTemplate from './format/formattemplate';
import FormatTemplateType from './format/formattemplatetype';

/**
 * Class defines how the Address will look like
 */
export default class Format
{
	code;
	name;
	description;
	languageId;
	templateCollection;
	fieldCollection;
	delimiter;
	fieldForUnRecognized;

	constructor(props)
	{
		if (Type.isUndefined(props.languageId))
		{
			throw new TypeError('LanguageId must be defined');
		}

		this.languageId = props.languageId;
		this.code = props.code || '';
		this.name = props.name || '';
		this.templateAutocomplete = props.templateAutocomplete || '';
		this.templateAddressLine1 = props.templateAddressLine1 || '';
		this.description = props.description || '';
		this.delimiter = props.delimiter || ', ';
		this.fieldForUnRecognized = props.fieldForUnRecognized || AddressType.UNKNOWN;

		this.fieldCollection = new FormatFieldCollection();

		if (Type.isObject(props.fieldCollection))
		{
			this.fieldCollection.initFields(props.fieldCollection);
		}

		let collection = {};

		if (Type.isObject(props.templateCollection))
		{
			collection = props.templateCollection;
		}

		this.templateCollection = new FormatTemplateCollection(collection);


	}

	getField(type)
	{
		return this.fieldCollection.getField(type);
	}

	isFieldExists(type)
	{
		return this.fieldCollection.isFieldExists(type);
	}

	getTemplate(type: string = FormatTemplateType.DEFAULT): FormatTemplate
	{
		return this.templateCollection.getTemplate(type);
	}

	isTemplateExists(type: string): boolean
	{
		return this.templateCollection.isTemplateExists(type);
	}

	get template(): FormatTemplate
	{
		return this.templateCollection.getTemplate();
	}
}