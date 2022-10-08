import {Type, Text} from "main.core";
import Address from "../../address";
import AddressType from '../addresstype';
import Format from "../../format";

const STR_DELIMITER_PLACEHOLDER = "#S#";
const REGEX_COMMA_AMONG_EMPTY_SPACE = "\\s*,\\s*";
const REGEX_GROUP_DELIMITER = "(\\\"([^\"\\\\]*|\\\\\"|\\\\\\\\|\\\\)*\")";
const REGEX_GROUP_FIELD_TEXT = REGEX_GROUP_DELIMITER;
const REGEX_GROUP_FIELD_NAME = "([a-zA-Z][a-zA-Z_0-9]*(:(NU|UN|N|U))?)";
const REGEX_GROUP_FIELD_LIST_END = "\\s*\\]";
const REGEX_GROUP_END = REGEX_GROUP_FIELD_LIST_END;
const REGEX_PART_FROM_DELIMITER_TO_FIELD_LIST = "\\s*,\\s*\\[\\s*";
const REGEX_GROUP_PART_BEFORE_FIELDS =
	"(([^\\[\\\\]|\\\\\\[|\\\\\\\\)*)(\\[\\s*)(\"([^\"\\\\]*|\\\\\"|\\\\\\\\|\\\\)*\")\\s*,\\s*\\[\\s*";

const ERR_PARSE_GROUP_START_POSITION = 1100;
const ERR_PARSE_GROUP_START = 1110;
const ERR_PARSE_GROUP_DELIMITER = 1120;
const ERR_PARSE_PART_FROM_DELIMITER_TO_FIELD_LIST = 1130;
const ERR_PARSE_GROUP_FIELD_TEXT = 1140;
const ERR_PARSE_GROUP_FIELD_NAME = 1150;
const ERR_PARSE_GROUP_FIELD = 1160;
const ERR_PARSE_GROUP_FIELD_LIST = 1170;
const ERR_PARSE_GROUP_FIELD_LIST_DELIMITER = 1180;
const ERR_PARSE_GROUP_FIELD_LIST_END = 1190;
const ERR_PARSE_GROUP_END = 1200;
const ERR_PARSE_GROUP = 1210;


export default class StringTemplateConverter
{
	#template = "";
	#delimiter = "";
	#htmlEncode = false;
	#format = null;

	constructor(template: string, delimiter: string, htmlEncode: boolean, format: Format = null)
	{
		this.#template = template;
		this.#delimiter = delimiter;
		this.#htmlEncode = htmlEncode;
		this.#format = format;
	}

	getErrorCodes()
	{
		let result = {};

		result[ERR_PARSE_GROUP_START_POSITION] = "ERR_PARSE_GROUP_START_POSITION";
		result[ERR_PARSE_GROUP_START] = "ERR_PARSE_GROUP_START";
		result[ERR_PARSE_GROUP_DELIMITER] = "ERR_PARSE_GROUP_DELIMITER";
		result[ERR_PARSE_PART_FROM_DELIMITER_TO_FIELD_LIST] = "ERR_PARSE_PART_FROM_DELIMITER_TO_FIELD_LIST";
		result[ERR_PARSE_GROUP_FIELD_TEXT] = "ERR_PARSE_GROUP_FIELD_TEXT";
		result[ERR_PARSE_GROUP_FIELD_NAME] = "ERR_PARSE_GROUP_FIELD_NAME";
		result[ERR_PARSE_GROUP_FIELD] = "ERR_PARSE_GROUP_FIELD";
		result[ERR_PARSE_GROUP_FIELD_LIST] = "ERR_PARSE_GROUP_FIELD_LIST";
		result[ERR_PARSE_GROUP_FIELD_LIST_DELIMITER] = "ERR_PARSE_GROUP_FIELD_LIST_DELIMITER";
		result[ERR_PARSE_GROUP_FIELD_LIST_END] = "ERR_PARSE_GROUP_FIELD_LIST_END";
		result[ERR_PARSE_GROUP_END] = "ERR_PARSE_GROUP_END";
		result[ERR_PARSE_GROUP] = "ERR_PARSE_GROUP";

		return result;
	}
	
	getErrorsText(context: {}): string
	{
		let result = "";

		const errorCodes = this.getErrorCodes();
		const errors = context["error"]["errors"];
		for (let i = 0; i < errors.length; i++)
		{
			result += `Error: ${errors[i]["position"]}, ${errorCodes[errors[i]["code"]]}\n`;
			if (errors[i].hasOwnProperty("info") && Type.isPlainObject(errors[i]["info"]))
			{
				const errorInfo = errors[i]["info"];
				let needHeader = true;
				for (let paramName in errorInfo)
				{
					if (errorInfo.hasOwnProperty(paramName))
					{
						let paramValue = errorInfo[paramName];
						let needPrint = false;
						if (Type.isString(paramValue))
						{
							paramValue = `"${paramValue}"`;
							needPrint = true;
						}
						else if (Type.isNumber(paramValue) || Type.isFloat(paramValue))
						{
							needPrint = true;
						}
						else if (Type.isBoolean(paramValue))
						{
							paramValue = ((paramValue) ? "true" : "false");
							needPrint = true;
						}
						else if (Type.isArray(paramValue))
						{
							paramValue = "[...]";
							needPrint = true;
						}
						else if (Type.isObject(paramValue))
						{
							paramValue = '{...}';
							needPrint = true;
						}
						if (needPrint)
						{
							if (needHeader)
							{
								result += "  Error info:\n";
								needHeader = false;
							}
							result += `    ${paramName}: ${paramValue}\n`;
						}
					}
				}
			}
		}

		let templateValue = context["template"].replace("\n", "\\n");
		templateValue = templateValue.replace("\"", "\\\"");
		result += `Template: "${templateValue}"\n\n`;

		return result;
	}

	createContext()
	{
		return {
			"level": 0,
			"position": 0,
			"template": "",
			"address": null,
			"info": {},
			"hasError": false,
			"error": {
				"code": 0,
				"position": 0,
				"errors": [],
				"info": {},
			},
		};
	}

	clearContextInfo(context)
	{
		context["info"] = {};

		return context;
	}

	clearContextError(context)
	{
		context["hasError"] = false;
		context["error"] = {
			"code": 0,
			"position": 0,
			"errors": [],
			"info": {},
		};

		return context;
	}

	clearContextInfoAndError(context)
	{
		return this.clearContextError(this.clearContextInfo(context));
	}

	unescapeText(text: string): string
	{
		let result = "";
		let i;

		for (i = 0; i < text.length; i++)
		{
			if (text[i] === "\\")
			{
				if ((text.length - i) > 1)
				{
					result += text[++i];
				}
			}
			else
			{
				result += text[i];
			}
		}

		return result;
	}

	parseGroupDelimiter(context: {}): {}
	{
		// Capturing the group's separator
		const delimiterStartPosition = context["position"];
		//                [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for ^^^^
		const regEx = new RegExp(REGEX_GROUP_DELIMITER, "mg");
		regEx.lastIndex = delimiterStartPosition;
		const matches = regEx.exec(context["template"]);
		if (matches && matches.index === delimiterStartPosition)
		{
			context["info"] = {
				"position": delimiterStartPosition,
				"end": delimiterStartPosition + matches[0].length,
				"value": this.unescapeText(
					context["template"].substr(
						delimiterStartPosition + 1,
						matches[0].length - 2
					)
				),
			};
			context["position"] = context["info"]["end"];
		}
		else
		{
			this.addContextError(context, ERR_PARSE_GROUP_DELIMITER, delimiterStartPosition);
		}

		return context;
	}

	parseFieldText(context: {}): {}
	{
		const textBlockStartPosition = context["position"];
		// [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for                         ^^^^^^
		const regEx = new RegExp(REGEX_GROUP_FIELD_TEXT, "mg");
		regEx.lastIndex = textBlockStartPosition;
		const matches = regEx.exec(context["template"]);
		if (matches && matches.index === textBlockStartPosition)
		{
			context["info"] = {
				"type": "text",
				"position": textBlockStartPosition,
				"end": textBlockStartPosition + matches[0].length,
				"value": this.unescapeText(
					context["template"].substr(
						textBlockStartPosition + 1,
						matches[0].length - 2
					)
				),
			};
			context["position"] = context["info"]["end"];
		}
		else
		{
			this.addContextError(context, ERR_PARSE_GROUP_FIELD_TEXT, textBlockStartPosition);
		}

		return context;
	}

	splitFieldName(fieldName: string): []
	{
		const parts = fieldName.split(":");
		const namePart = parts[0];
		const modifiersPart = (parts.length > 1) ? parts[1] : "";

		return [namePart, modifiersPart];
	}

	#isTemplateForFieldExists(fieldName: string): boolean
	{
		return this.#format && this.#format.getTemplate(fieldName) !== null;
	}

	#getFieldValueByTemplate(fieldName: string, address: Address): ?string
	{
		if (!this.#isTemplateForFieldExists(fieldName))
		{
			return null;
		}

		const template = this.#format.getTemplate(fieldName).template;
		const templateConverter = new StringTemplateConverter(template, this.#delimiter, this.#htmlEncode, this.#format);
		return templateConverter.convert(address);
	}
	
	#getAlterFieldValue(address: Address, fieldType: number): string
	{
		let localityValue = address.getFieldValue(AddressType.LOCALITY);
		localityValue = Type.isString(localityValue) ? localityValue : "";
		let result = address.getFieldValue(fieldType);
		if (!Type.isString(result))
		{
			result = "";
		}
		if (result !== "" && localityValue !== "")
		{
			const localityValueUpper = localityValue.toUpperCase();
			const targetValueUpper = result.toUpperCase();
			if (targetValueUpper.length >= localityValueUpper.length)
			{
				const targetValueSubstr = targetValueUpper.substr(
					targetValueUpper.length - localityValueUpper.length
				);
				if (localityValueUpper === targetValueSubstr)
				{
					result = "";
				}
			}
		}

		return result;
	}
	
	getAddressFieldValue(address: Address, fieldName: string, fieldModifiers: string): string
	{
		let  result = "";

		if (!Type.isUndefined(AddressType[fieldName]))
		{
			if (fieldName === "ADM_LEVEL_1" || fieldName === "ADM_LEVEL_2")
			{
				// Scratch "Province & Region by Locality"
				result = this.#getAlterFieldValue(address, AddressType[fieldName]);
			}
			else
			{
				result = address.getFieldValue(AddressType[fieldName]);
			}

			if (result === null)
			{
				result = this.#getFieldValueByTemplate(fieldName, address);
			}
		}
		if (!Type.isString(result))
		{
			result = "";
		}
		if (result !== "")
		{
			if (fieldModifiers.indexOf("N") >= 0)
			{
				result = result.replace(/(\r\n|\n|\r)/g, "#S#");
			}
			if (fieldModifiers.indexOf("U") >= 0)
			{
				result = result.toUpperCase();
			}
		}

		return result;
	}

	parseFieldName(context: {}): {}
	{
		const fieldNameStartPosition = context["position"];
		//          [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for  ^^^^^^^^^^^^^^^^
		const regEx = new RegExp(REGEX_GROUP_FIELD_NAME, "mg");
		regEx.lastIndex = fieldNameStartPosition;
		const matches = regEx.exec(context["template"]);
		if (matches && matches.index === fieldNameStartPosition)
		{
			context["position"] = fieldNameStartPosition + matches[0].length;
			const fieldParts = this.splitFieldName(matches[0]);
			const fieldName = fieldParts[0];
			const fieldModifiers = fieldParts[1];
			const fieldValue = this.getAddressFieldValue(context["address"], fieldName, fieldModifiers);
			context["info"] = {
				"type": "field",
				"position": fieldNameStartPosition,
				"end": context["position"],
				"modifiers": fieldModifiers,
				"name": fieldName,
				"value": fieldValue,
			};
		}
		else
		{
			this.addContextError(context, ERR_PARSE_GROUP_FIELD_NAME, fieldNameStartPosition);
		}

		return context;
	}

	parseFieldListDelimiter(context: {}): {}
	{
		const markerStartPosition = context["position"];
		// [", ", [ADDRESS_LINE_1:N , ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for         ^^^
		const regEx = new RegExp(REGEX_COMMA_AMONG_EMPTY_SPACE, "mg");
		regEx.lastIndex = markerStartPosition;
		const matches = regEx.exec(context["template"]);
		if (matches && matches.index === markerStartPosition)
		{
			context["position"] = markerStartPosition + matches[0].length;
		}
		else
		{
			this.addContextError(context, ERR_PARSE_GROUP_FIELD_LIST_DELIMITER, markerStartPosition);
		}

		return context;
	}

	parseFieldListEnd(context: {}): {}
	{
		const markerStartPosition = context["position"];
		// [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for                                                    ^
		const regEx = new RegExp(REGEX_GROUP_FIELD_LIST_END, "mg");
		regEx.lastIndex = markerStartPosition;
		const matches = regEx.exec(context["template"]);
		if (matches && matches.index === markerStartPosition)
		{
			context["position"] = markerStartPosition + matches[0].length;
		}
		else
		{
			this.addContextError(context, ERR_PARSE_GROUP_FIELD_LIST_END, markerStartPosition);
		}

		return context;
	}

	parseField(context: {}): {}
	{
		let fieldInfo = [];
		const fieldStartPosition = context["position"];
		const errors = [];

		// Checking for the presence of a text block
		context = this.parseFieldText(context);

		if (context["hasError"])
		{
			this.unshiftError(errors, context["error"]["code"], context["error"]["position"]);
			context = this.clearContextInfoAndError(context);
			// Checking for the presence of a field name
			context = this.parseFieldName(context);
		}

		if (context["hasError"])
		{
			this.unshiftError(errors, context["error"]["code"], context["error"]["position"]);
			context = this.clearContextInfoAndError(context);
			// Checking for the presence of a nested group
			context = this.parseGroup(context);
			if (context["hasError"])
			{
				this.unshiftError(errors, context["error"]["code"], context["error"]["position"]);
			}
			else if (context["info"]["position"] > fieldStartPosition)
			{
				// Group found beyond the expected position
				this.addContextError(context, ERR_PARSE_GROUP_START_POSITION, fieldStartPosition);
				this.unshiftError(errors, context["error"]["code"], context["error"]["position"]);
			}
		}

		if (!context["hasError"])
		{
			fieldInfo = context["info"];
			fieldInfo["isFieldListEnd"] = false;
			context = this.clearContextInfo(context);

			// Checking for the presence of a field separator
			context = this.parseFieldListDelimiter(context);

			if (context["hasError"])
			{
				this.unshiftError(errors, context["error"]["code"], context["error"]["position"]);
				context = this.clearContextInfoAndError(context);
				// Checking for the presence of the end sign of the field list
				context = this.parseFieldListEnd(context);
				if (context["hasError"])
				{
					this.unshiftError(errors, context["error"]["code"], context["error"]["position"]);
				}
				else
				{
					fieldInfo["isFieldListEnd"] = true;
				}
			}
		}

		if (context["hasError"])
		{
			this.unshiftError(errors,  ERR_PARSE_GROUP_FIELD, fieldStartPosition);
			this.addContextErrors(context, errors);
		}
		else
		{
			context["info"] = fieldInfo;
		}

		return context;
	}

	parseGroupFieldList(context: {}): {}
	{
		const fieldListStartPosition = context["position"];
		const fieldValues = [];
		//            [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for ^^^
		const regEx = new RegExp(REGEX_PART_FROM_DELIMITER_TO_FIELD_LIST, "mg");
		regEx.lastIndex = fieldListStartPosition;
		const matches = regEx.exec(context["template"]);
		if (matches && matches.index === fieldListStartPosition)
		{
			context["position"] = fieldListStartPosition + matches[0].length;
			let isFieldListEnd = false;
			while (!(context["hasError"] || isFieldListEnd))
			{
				context = this.parseField(context);
				if (!context["hasError"])
				{
					isFieldListEnd = (
						context["info"].hasOwnProperty("isFieldListEnd")
						&& context["info"]["isFieldListEnd"]
					);
					if (context["info"]["value"] !== "")
					{
						fieldValues.push(context["info"]["value"]);
					}
					context = this.clearContextInfo(context);
				}
			}

			if (!context["hasError"])
			{
				context["info"] = {"fieldValues": fieldValues};
			}
		}
		else
		{
			this.addContextError(context, ERR_PARSE_PART_FROM_DELIMITER_TO_FIELD_LIST, fieldListStartPosition);
		}

		if (context["hasError"])
		{
			this.addContextError(context, ERR_PARSE_GROUP_FIELD_LIST, fieldListStartPosition);
		}

		return context;
	}

	parseGroupStart(context: {}): {}
	{
		//                 [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for ^^^^^^^^
		const regEx = new RegExp(REGEX_GROUP_PART_BEFORE_FIELDS, "mg");
		regEx.lastIndex = context["position"];
		const matches = regEx.exec(context["template"])
		if (matches)
		{
			context["info"]["groupStartPosition"] = matches.index + matches[1].length;
			context["info"]["groupDelimiterStartPosition"] = matches.index + matches[1].length + matches[3].length;
		}
		else
		{
			this.addContextError(context, ERR_PARSE_GROUP_START, context["position"]);
		}

		return context;
	}

	parseGroupEnd(context: {}): {}
	{
		const markerStartPosition = context["position"];
		// [", ", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]
		// Are looking for                                                     ^
		const regEx = new RegExp(REGEX_GROUP_END, "mg");
		regEx.lastIndex = markerStartPosition;
		const matches = regEx.exec(context["template"]);
		if (matches && matches.index === markerStartPosition)
		{
			context["position"] = markerStartPosition + matches[0].length;
		}
		else
		{
			this.addContextError(context, ERR_PARSE_GROUP_END, markerStartPosition);
		}

		return context;
	}

	parseGroup(context: {}): {}
	{
		const startSearchPosition = context["position"];
		let groupStartPosition = 0;
		let delimiterValue = "";
		let fieldValues = [];

		context["level"]++;

		// Checking for the presence of a start of a group
		context = this.parseGroupStart(context);

		if (!context["hasError"])
		{
			// Found a sign of the beginning of a group
			groupStartPosition = context["info"]["groupStartPosition"];
			context["position"] = context["info"]["groupDelimiterStartPosition"];
			context = this.clearContextInfo(context);
			context = this.parseGroupDelimiter(context);
		}

		if (!context["hasError"])
		{
			// The value of the group separator was got
			delimiterValue = context["info"]["value"];
			context = this.clearContextInfo(context);
			context = this.parseGroupFieldList(context);
		}

		if (!context["hasError"])
		{
			// The values of the field list was got
			fieldValues = context["info"]["fieldValues"];
			context = this.clearContextInfo(context);
			context = this.parseGroupEnd(context);
		}

		if (!context["hasError"])
		{
			// Kremlin,Moscow,Moscow,Russia,103132 -> Kremlin,Moscow,Russia,103132
			fieldValues = [...new Set(fieldValues)];

			let value = fieldValues.join(delimiterValue);

			// Kaliningrad, Narvskaya, 72, , kv 8 -> Kaliningrad, Narvskaya, 72, kv 8
			const reg = new RegExp(`(${delimiterValue}){2,}`, 'gim');
			value = value.replace(new RegExp(reg), delimiterValue);

			// The sign of the end of the group is received, the assembly of the group value.
			context["info"] = {
				"type": "group",
				"position": groupStartPosition,
				"end": context["position"],
				"value": value,
			};
		}

		context["level"]--;

		if (context["hasError"])
		{
			this.addContextError(
				context,
				ERR_PARSE_GROUP,
				startSearchPosition,
				{"groupStartPosition": groupStartPosition}
			);
		}

		return context;
	}

	appendTextBlock(blocks: [], position: number, value: string)
	{
		let lastBlockIndex = blocks.length - 1;
		let lastBlock = (lastBlockIndex >= 0) ? blocks[lastBlockIndex] : null;
		if (lastBlock && lastBlock.hasOwnProperty("type") && lastBlock["type"] === "text")
		{
			blocks[lastBlockIndex]["value"] += value;
			blocks[lastBlockIndex]["length"] += value.length;
		}
		else
		{
			blocks[++lastBlockIndex] = {
				"type": "text",
				"position": position,
				"length": value.length,
				"value": value,
			};
		}
	}

	appendGroupBlock(blocks: [], position: number, value: string)
	{
		blocks.push({
			"type": "group",
			"position": position,
			"length": value.length,
			"value": value,
		});
	}

	unshiftError(errors: [{}], code: number, position: number, info: {} = null)
	{
		errors.unshift({
			"code": code,
			"position": position,
			"info": (Type.isPlainObject(info)) ? info : {},
		});
	}

	addContextError(context: {}, code: number, position: number, info: {} = null)
	{
		context["hasError"] = true;
		context["error"]["code"] = code;
		context["error"]["position"] = position;
		context["error"]["info"] = (Type.isPlainObject(info)) ? info : {};
		this.unshiftError(context["error"]["errors"], code, position, info);
	}

	addContextErrors(context: {}, errors: [{}], info: {} = null)
	{
		context["hasError"] = true;
		context["error"]["code"] = errors[0]["code"];
		context["error"]["position"] = errors[0]["position"];
		context["error"]["info"] = (Type.isPlainObject(info)) ? info : {};
		context["error"]["errors"].splice(0, 0, errors);
	}

	parseBlocks(context: {}): {}
	{
		/* Variable for debug only
		let errorDisplayed = false;
		*/

		const blocks = [];

		const templateLength = context["template"].length;
		while (context["position"] < templateLength)
		{
			const blockStartPosition = context["position"];
			context = this.parseGroup(context);
			if (context["hasError"])
			{
				// Debug info
				/*if (!errorDisplayed)
				{
					console.info(this.getErrorsText(context));
					errorDisplayed = true;
				}*/

				const errorInfo = context["error"]["info"];
				let blockLength;
				if (!Type.isPlainObject(errorInfo)
					&& errorInfo.hasOwnProperty("groupStartPosition")
					&& errorInfo["groupStartPosition"] > blockStartPosition
				)
				{
					blockLength = errorInfo["groupStartPosition"] - blockStartPosition + 1;
				}
				else
				{
					blockLength = 1;
				}

				this.appendTextBlock(
					blocks,
					context["error"]["position"],
					context["template"].substr(blockStartPosition, blockLength)
				);
				context = this.clearContextInfoAndError(context);
				context["position"] = blockStartPosition + blockLength;
			}
			else
			{
				const groupStartPosition = context["info"]["position"];
				if (groupStartPosition > blockStartPosition)
				{
					this.appendTextBlock(
						blocks,
						blockStartPosition,
						context["template"].substr(
							blockStartPosition,
							groupStartPosition - blockStartPosition
						)
					);
				}

				if (context["info"]["value"] !== "")
				{
					this.appendGroupBlock(
						blocks,
						groupStartPosition,
						context["info"]["value"]
					);
				}

				context = this.clearContextInfo(context);
			}
		}

		if (!context["hasError"])
		{
			context["info"] = {"blocks": blocks};
		}

		return context;
	}

	convert(address: Address): string
	{
		let result = "";

		let context = this.createContext();
		context["template"] = this.#template;
		context["address"] = address;

		context = this.parseBlocks(context);

		if (!context["hasError"])
		{
			const blocks = context["info"]["blocks"];
			for (let i = 0; i < blocks.length; i++)
			{
				if (blocks[i]["type"] === "text")
				{
					result += this.unescapeText(blocks[i]["value"]);
				}
				else
				{
					result += blocks[i]["value"];
				}
			}
		}

		if (result !== "")
		{
			const temp = result.split(STR_DELIMITER_PLACEHOLDER);
			let parts = [];
			for (let i = 0; i < temp.length; i++)
			{
				if (temp[i] !== "")
				{
					parts.push(temp[i]);
				}
			}
			if (this.#htmlEncode && parts.length > 0)
			{
				for (let i = 0; i < parts.length; i++)
				{
					parts[i] = Text.encode(parts[i]);
				}
			}

			result = parts.join(this.#delimiter);
		}
		return result;
	}
}
