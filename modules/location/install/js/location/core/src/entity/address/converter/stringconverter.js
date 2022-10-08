import {Text} from 'main.core';
import Address from '../../address';
import Format from '../../format';
import StringTemplateConverter from './stringtemplateconverter';

export default class StringConverter
{
	static STRATEGY_TYPE_TEMPLATE = 'template';
	static STRATEGY_TYPE_TEMPLATE_COMMA = 'template_comma';
	static STRATEGY_TYPE_TEMPLATE_NL = 'template_nl';
	static STRATEGY_TYPE_TEMPLATE_BR = 'template_br';
	static STRATEGY_TYPE_FIELD_SORT = 'field_sort';
	static STRATEGY_TYPE_FIELD_TYPE = 'field_type';

	static CONTENT_TYPE_HTML = 'html';
	static CONTENT_TYPE_TEXT = 'text';
	/**
	 * Convert address to string
	 * @param {Address} address
	 * @param {Format} format
	 * @param {string} strategyType
	 * @param {string} contentType
	 * @returns {string}
	 */
	static convertAddressToString(address: Address, format: Format, strategyType: string, contentType: string): string
	{
		let result;

		if (strategyType === StringConverter.STRATEGY_TYPE_TEMPLATE
			|| strategyType === StringConverter.STRATEGY_TYPE_TEMPLATE_COMMA
			|| strategyType === StringConverter.STRATEGY_TYPE_TEMPLATE_NL
			|| strategyType === StringConverter.STRATEGY_TYPE_TEMPLATE_BR
		)
		{
			let delimiter = null;

			switch (strategyType)
			{
				case StringConverter.STRATEGY_TYPE_TEMPLATE_COMMA:
					delimiter = ', ';
					break;
				case StringConverter.STRATEGY_TYPE_TEMPLATE_NL:
					delimiter = '\n';
					break;
				case StringConverter.STRATEGY_TYPE_TEMPLATE_BR:
					delimiter = '<br />';
					break;
			}

			result = StringConverter.convertAddressToStringTemplate(
				address, format.getTemplate(), contentType, delimiter, format
			);
		}
		else if (strategyType === StringConverter.STRATEGY_TYPE_FIELD_SORT)
		{
			const fieldSorter = (a, b) => { return a.sort - b.sort; };
			result = StringConverter.convertAddressToStringByField(address, format, fieldSorter, contentType);
		}
		else if (strategyType === StringConverter.STRATEGY_TYPE_FIELD_TYPE)
		{
			const fieldSorter = (a, b) => {
				let sortResult;

				// We suggest that UNKNOWN must be the last
				if (a.type === 0)
				{
					sortResult = 1;
				}
				else if (b.type === 0)
				{
					sortResult = -1;
				}
				else
				{
					sortResult = a.type - b.type;
				}

				return sortResult;
			};

			result = StringConverter.convertAddressToStringByField(address, format, fieldSorter, contentType);
		}
		else
		{
			throw TypeError('Wrong strategyType');
		}

		return result;
	}

	/**
	 * Convert address to string
	 * @param {Address} address
	 * @param {string} template
	 * @param {string} contentType
	 * @param {string|null} delimiter
	 * @param {Format|null} format
	 * @returns {string}
	 */
	static convertAddressToStringTemplate(
		address: Address,
		template: Template,
		contentType: string,
		delimiter: string = null,
		format: Format = null
	): string
	{
		const needHtmlEncode = (contentType === StringConverter.CONTENT_TYPE_HTML);

		if (delimiter === null)
		{
			delimiter = needHtmlEncode ? '<br />' : '\n';
		}

		const templateConverter = new StringTemplateConverter(template.template, delimiter, needHtmlEncode, format);
		return templateConverter.convert(address);
	}

	/**
	 * Convert address to string
	 * @param {Address} address
	 * @param {Format} format
	 * @param {Function} fieldSorter
	 * @param {string} contentType
	 * @returns {string}
	 */
	static convertAddressToStringByField(
		address: Address,
		format: Format,
		fieldSorter: Function,
		contentType: string
	): string
	{
		if (!(format instanceof Format))
		{
			BX.debug('format must be instance of Format');
		}

		if (!(address instanceof Address))
		{
			BX.debug('address must be instance of Address');
		}

		const fieldCollection = format.fieldCollection;

		if (!fieldCollection)
		{
			return '';
		}

		const fields = Object.values(fieldCollection.fields);

		// todo: make only once or cache?
		fields.sort(fieldSorter);

		let result = '';

		for(const field of fields)
		{
			let value = address.getFieldValue(field.type);

			if (value === null)
			{
				continue;
			}

			if (contentType === StringConverter.CONTENT_TYPE_HTML)
			{
				value = Text.encode(value);
			}

			if (result !== '')
			{
				result += format.delimiter;
			}

			result += value;
		}

		return result;
	}
}