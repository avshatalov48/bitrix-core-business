/* global assert */
import {Type, Text} from "main.core";
import Address from "../../../src/entity/address";
import AddressType from "../../../src/entity/address/addresstype";
import StringTemplateConverter from "../../../src/entity/address/converter/stringtemplateconverter";

describe("StringTemplateConverter", () =>
{
	it("Should be a function", () =>
	{
		assert(typeof StringTemplateConverter === "function");
	});

	const langId = "ru";

	// Russian phrases
	const addressMessages = {
		"ADDRESS_LINE_1": "ООО \"1С-Битрикс\"\\nул. Гостиная, д.3",
		"ADDRESS_LINE_2": "6 эт.",
		"LOCALITY": "г. Калининград",
		"SUB_LOCALITY_LEVEL_1": "стадион Балтика",
		"POSTAL_CODE": "236022",
		"ADM_LEVEL_2": "Центральный р-н",
		"ADM_LEVEL_1": "Калининградская обл.",
		"COUNTRY": "Россия"
	};

	const addressJson = `{ 
		"id": 149,
		"languageId": "${langId}",
		"fieldCollection": { 
			"410": "${addressMessages["ADDRESS_LINE_1"].replace(/"/g, "\\\"")}",
			"600": "${addressMessages["ADDRESS_LINE_2"]}",
			"300": "${addressMessages["LOCALITY"]}",
			"320": "${addressMessages["SUB_LOCALITY_LEVEL_1"]}",
			"50": "${addressMessages["POSTAL_CODE"]}",
			"210": "${addressMessages["ADM_LEVEL_2"]}",
			"200": "${addressMessages["ADM_LEVEL_1"]}",
			"100": "${addressMessages["COUNTRY"]}"
		},
		"location": { 
			"id": 320,
			"externalId": "EKJYYJKAE-2wrgTE76-gCA",
			"sourceCode": "GOOGLE",
			"type": 300,
			"name":"",
			"languageId": "${langId}",
			"latitude": "54.7189668",
			"longitude": "20.4885361"
		}
	}`;

	describe("parseFieldName", () =>
	{
		const address = new Address(JSON.parse(addressJson));
		const delimiter = ", ";
		const template = `["${delimiter}", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2]]`;
		const converter = new StringTemplateConverter(template, delimiter, false);

		it("Test normal", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 8;
			context = converter.parseFieldName(context);

			assert(context.hasOwnProperty("info"));
			assert(Type.isPlainObject(context["info"]));
			assert.deepEqual(
				context["info"],
				{
					"type": "field",
					"position": 8,
					"end": 24,
					"modifiers": "N",
					"name": "ADDRESS_LINE_1",
					"value": addressMessages["ADDRESS_LINE_1"].replace("\\n", "#S#"),
				}
			);
		});

		it("Test on error", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 40;
			context = converter.parseFieldName(context);

			assert(context.hasOwnProperty("hasError"));
			assert.strictEqual(context["hasError"], true);
			assert(context.hasOwnProperty("error"));
			assert(Type.isPlainObject(context["error"]));

			// Remove the error stack, no need to check
			assert(context["error"].hasOwnProperty("errors"));
			delete(context["error"]["errors"]);

			const errorCodes = converter.getErrorCodes();
			let errorIds = {};
			for (let code in errorCodes)
			{
				if (errorCodes.hasOwnProperty(code))
				{
					errorIds[errorCodes[code]] = code;
				}
			}

			assert.deepEqual(
				context["error"],
				{
					"code": parseInt(errorIds["ERR_PARSE_GROUP_FIELD_NAME"]),
					"position": 40,
					"info": {},
				}
			);
		});
	});

	describe("parseField", () =>
	{
		const address = new Address(JSON.parse(addressJson));
		const delimiter = ", ";
		const template = `["${delimiter}", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,"Text",LOCALITY,ADM_LEVEL_2,  `
			+ `["- ", [" ", COUNTRY]]]  ]`;
		const converter = new StringTemplateConverter(template, delimiter, false);

		it("Test address field", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 8;
			context = converter.parseField(context);

			assert(context.hasOwnProperty("info"));
			assert(Type.isPlainObject(context["info"]));
			assert.deepEqual(
				context["info"],
				{
					"type": "field",
					"position": 8,
					"end": 24,
					"modifiers": "N",
					"name": "ADDRESS_LINE_1",
					"value": addressMessages["ADDRESS_LINE_1"].replace("\\n", "#S#"),
					"isFieldListEnd": false,
				}
			);
		});

		it("Test text block", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 40;
			context = converter.parseField(context);

			assert(context.hasOwnProperty("info"));
			assert(Type.isPlainObject(context["info"]));
			assert.deepEqual(
				context["info"],
				{
					"type": "text",
					"position": 40,
					"end": 46,
					"value": "Text",
					"isFieldListEnd": false,
				}
			);
		});

		it("Test nested group", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 70;
			context = converter.parseField(context);

			assert(context.hasOwnProperty("info"));
			assert(Type.isPlainObject(context["info"]));
			assert.deepEqual(
				context["info"],
				{
					"type": "group",
					"position": 70,
					"end": 92,
					"value": ` - ${addressMessages["COUNTRY"]}`,
					"isFieldListEnd": true,
				}
			);
		});

		it("Test on error", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 90;
			context = converter.parseField(context);

			assert(context.hasOwnProperty("hasError"));
			assert.strictEqual(context["hasError"], true);
			assert(context.hasOwnProperty("error"));
			assert(Type.isPlainObject(context["error"]));

			// Remove the error stack, no need to check
			assert(context["error"].hasOwnProperty("errors"));
			delete(context["error"]["errors"]);

			const errorCodes = converter.getErrorCodes();
			let errorIds = {};
			for (let code in errorCodes)
			{
				if (errorCodes.hasOwnProperty(code))
				{
					errorIds[errorCodes[code]] = code;
				}
			}

			assert.deepEqual(
				context["error"],
				{
					"code": parseInt(errorIds["ERR_PARSE_GROUP_FIELD"]),
					"position": 90,
					"info": {},
				}
			);
		});
	});

	describe("parseBlocks", () =>
	{
		const address = new Address(JSON.parse(addressJson));
		const delimiter = ", ";
		const template = `Text before group ["${delimiter}", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,`
			+ `"Text in group",LOCALITY,ADM_LEVEL_2,  ["- ", [" ", COUNTRY]]]  ] Text after group`;
		const converter = new StringTemplateConverter(template, delimiter, false);

		it("Parsing the blocks", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 0;
			context = converter.parseBlocks(context);

			assert(context.hasOwnProperty("info"));
			assert(Type.isPlainObject(context["info"]));

			const blockValue = [
				"Text before group ",
				[
					addressMessages["ADDRESS_LINE_1"].replace("\\n", "#S#"),
					addressMessages["ADDRESS_LINE_2"],
					"Text in group",
					addressMessages["LOCALITY"],
					addressMessages["ADM_LEVEL_2"],
					" - " + addressMessages["COUNTRY"],
				].join(delimiter),
				" Text after group",
			];

			assert.deepEqual(
				context["info"],
				{
					"blocks": [
						{
							"type": "text",
							"position": 0,
							"length": 18,
							"value": blockValue[0],
						},
						{
							"type": "group",
							"position": 18,
							"length": 102,
							"value": blockValue[1],
						},
						{
							"type": "text",
							"position": 123,
							"length": 17,
							"value": blockValue[2],
						},
					]
				}
			);
		});
	});

	describe("parseGroup", () =>
	{
		const address = new Address(JSON.parse(addressJson));
		const delimiter = ", ";
		const template = `Text before group ["${delimiter}", [ADDRESS_LINE_1:N,ADDRESS_LINE_2,`
			+ `"Text in group",LOCALITY,ADM_LEVEL_2,  ["- ", [" ", COUNTRY]]]  ] Text after group`;
		const converter = new StringTemplateConverter(template, delimiter, false);

		it("Test normal", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 0;
			context = converter.parseGroup(context);

			assert(context.hasOwnProperty("info"));
			assert(Type.isPlainObject(context["info"]));

			const groupValue = [
				addressMessages["ADDRESS_LINE_1"].replace("\\n", "#S#"),
				addressMessages["ADDRESS_LINE_2"],
				"Text in group",
				addressMessages["LOCALITY"],
				addressMessages["ADM_LEVEL_2"],
				" - " + addressMessages["COUNTRY"],
			].join(delimiter);

			assert.deepEqual(
				context["info"],
				{
					"type": "group",
					"position": 18,
					"end": 123,
					"value": groupValue,
				}
			);
		});
		it("Test on error", () =>
		{
			let context = converter.createContext();
			context["template"] = template;
			context["address"] = address;
			context["position"] = 98;
			context = converter.parseGroup(context);

			assert(context.hasOwnProperty("hasError"));
			assert.strictEqual(context["hasError"], true);
			assert(context.hasOwnProperty("error"));
			assert(Type.isPlainObject(context["error"]));

			// Remove the error stack, no need to check
			assert(context["error"].hasOwnProperty("errors"));
			delete(context["error"]["errors"]);

			const errorCodes = converter.getErrorCodes();
			let errorIds = {};
			for (let code in errorCodes)
			{
				if (errorCodes.hasOwnProperty(code))
				{
					errorIds[errorCodes[code]] = code;
				}
			}

			assert.deepEqual(
				context["error"],
				{
					"code": parseInt(errorIds["ERR_PARSE_GROUP"]),
					"position": 98,
					"info": {"groupStartPosition": 0},
				}
			);
		});
	});

	describe("convert", () =>
	{
		const address = new Address(JSON.parse(addressJson));

		it ("Convert with complicated template", () =>
		{
			const delimiter = ", ";

			const template = "Text before group 1 "
				+ "[\"#S#\",["
				+ "  POSTAL_CODE,"
				+ "  COUNTRY,"
				+ "  \"Text in group 1\","
				+ "  ADM_LEVEL_1,"
				+ "  ADM_LEVEL_2,"
				+ "  LOCALITY,"
				+ "  ADDRESS_LINE_1:N,"
				+ "  ADDRESS_LINE_2"
				+ "]]"
				+ " Text after group 1"
				+ " <Escaped group template: \\[\\\", \\\", \\[F1,F2,F3\\]\\]>"
				+ " Text before group 2 "
				+ "[\"#S#\",["
				+ "  [\", \", [ADDRESS_LINE_1:N,\" Text within nested group \",ADDRESS_LINE_2]],"
				+ "  SUB_LOCALITY_LEVEL_1,"
				+ "  [\" \", ["
				+ "    LOCALITY,"
				+ "    [\" - \",[ADM_LEVEL_2,ADM_LEVEL_1]]"
				+ " ]],"
				+ "  POSTAL_CODE,COUNTRY]]"
				+ " Text after group 2"
			;

			const converter = new StringTemplateConverter(template, delimiter, false);

			const text = converter.convert(address);

			const expectedText = 
				"Text before group 1 "
				+ [
					addressMessages["POSTAL_CODE"],
					addressMessages["COUNTRY"],
					"Text in group 1",
					addressMessages["ADM_LEVEL_1"],
					addressMessages["ADM_LEVEL_2"],
					addressMessages["LOCALITY"],
					addressMessages["ADDRESS_LINE_1"].replace("\\n", delimiter),
					addressMessages["ADDRESS_LINE_2"],
				].join(delimiter)
				+ " Text after group 1"
				+ " <Escaped group template: [\", \", [F1,F2,F3]]>"
				+ " Text before group 2 "
				+ [
					[
						addressMessages["ADDRESS_LINE_1"].replace("\\n", delimiter),
						" Text within nested group ",
						addressMessages["ADDRESS_LINE_2"],
					].join(delimiter),
					addressMessages["SUB_LOCALITY_LEVEL_1"],
					[
						addressMessages["LOCALITY"],
						[addressMessages["ADM_LEVEL_2"], addressMessages["ADM_LEVEL_1"]].join(" - "),
					].join(" "),
					addressMessages["POSTAL_CODE"],
					addressMessages["COUNTRY"],
				].join(delimiter)
				+ " Text after group 2"
			;
			
			assert.strictEqual(text, expectedText);
		});

		it ("Convert with special chars", () =>
		{
			const delimiter = "<br>";
			const template = "'Text 1' [\"#S#\", [COUNTRY, LOCALITY, ADDRESS_LINE_1:NU]] \"Text 2\"";
			const converter = new StringTemplateConverter(template, delimiter, true);

			const text = converter.convert(address);

			const expectedText =
				Text.encode("'Text 1' ")
				+ [
					Text.encode(addressMessages["COUNTRY"]),
					Text.encode(addressMessages["LOCALITY"]),
					Text.encode(
						addressMessages["ADDRESS_LINE_1"].replace("\\n", "#S#").toUpperCase()
					).replace(/#S#/g, delimiter),
				].join(delimiter)
				+ Text.encode(" \"Text 2\"")
			;

			assert.strictEqual(text, expectedText);
		});

		it ("Convert using \"Province & Region by Locality\" scratch", () => {
			const delimiter = ", ";
			const template =
				"[\"#S#\",[ADDRESS_LINE_1:N,ADDRESS_LINE_2,LOCALITY,ADM_LEVEL_2,ADM_LEVEL_1,COUNTRY,POSTAL_CODE]]";
			const converter = new StringTemplateConverter(template, delimiter, false);

			const address1 = new Address(JSON.parse(addressJson));
			address1.setFieldValue(AddressType.LOCALITY, "Калининград");
			address1.setFieldValue(AddressType.ADM_LEVEL_1, "город Калининград");
			address1.setFieldValue(AddressType.ADM_LEVEL_2, "город Калининград");
			const text = converter.convert(address1);

			const expectedText =
				[
					addressMessages["ADDRESS_LINE_1"].replace(/\\n/g, delimiter),
					addressMessages["ADDRESS_LINE_2"],
					"Калининград",
					//"город Калининград",            Scratch "Region by Locality"
					//"город Калининград",            Scratch "Province by Locality"
					addressMessages["COUNTRY"],
					addressMessages["POSTAL_CODE"],
				].join(delimiter)
			;

			assert.strictEqual(text, expectedText);
		});
	});


});