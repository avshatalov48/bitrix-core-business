/* global assert */
import {Type, Text} from "main.core";
import Address from "../../../src/entity/address";
import AddressType from "../../../src/entity/address/addresstype.js";
import StringConverter from "../../../src/entity/address/converter/stringconverter"
import {Format, FormatTemplateType} from "location.core";

describe("StringConverterCrmCompatibility", () =>
{
	it("Converter should be compatible with module CRM", () =>
	{
		const langId = "ru";

		const addressVariant = [];
		addressVariant[0] = new Address({"languageId": langId});
		addressVariant[0].setFieldValue(
			AddressType.ADDRESS_LINE_1,
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"\n<ADDRESS_1_ITEM_4>]"
		);

		addressVariant[1] = new Address({"languageId": langId});
		addressVariant[1].setFieldValue(
			AddressType.ADDRESS_LINE_1,
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]"
		);
		addressVariant[1].setFieldValue(AddressType.ADDRESS_LINE_2, "[<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]");
		addressVariant[1].setFieldValue(AddressType.LOCALITY, "[CITY]");
		addressVariant[1].setFieldValue(AddressType.POSTAL_CODE, "[POSTAL_CODE]");

		addressVariant[2] = new Address({"languageId": langId});
		addressVariant[2].setFieldValue(
			AddressType.ADDRESS_LINE_1,
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]"
		);
		addressVariant[2].setFieldValue(AddressType.ADDRESS_LINE_2, "[<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]");
		addressVariant[2].setFieldValue(AddressType.LOCALITY, "[CITY]");
		addressVariant[2].setFieldValue(AddressType.POSTAL_CODE, "[POSTAL_CODE]");
		addressVariant[2].setFieldValue(AddressType.ADM_LEVEL_1, "[PROVINCE]");
		addressVariant[2].setFieldValue(AddressType.ADM_LEVEL_2, "[REGION]");
		addressVariant[2].setFieldValue(AddressType.COUNTRY, "[COUNTRY]");

		const addressFieldTypes = [
			[AddressType.ADDRESS_LINE_1, "ADDRESS_LINE_1"],
			[AddressType.ADDRESS_LINE_2, "ADDRESS_LINE_2"],
			[AddressType.LOCALITY, "LOCALITY"],
			[AddressType.POSTAL_CODE, "POSTAL_CODE"],
			[AddressType.ADM_LEVEL_1, "ADM_LEVEL_1"],
			[AddressType.ADM_LEVEL_2, "ADM_LEVEL_2"],
			[AddressType.COUNTRY, "COUNTRY"],
		];
		const addressFieldTypeMap = {};
		for (let i = 0; i < addressFieldTypes.length; i++)
		{
			addressFieldTypeMap[addressFieldTypes[i][0]] = addressFieldTypes[i][1];
		}

		const testVariant = [];
		testVariant[0] = "[ADDRESS_1] only with components";
		testVariant[1] = "[ADDRESS_1] and [ADDRESS_2] with components";
		testVariant[2] = "All filled";

		const formatVariant = [];
		formatVariant[0] = "EU";
		formatVariant[1] = "UK";
		formatVariant[2] = "US";
		formatVariant[3] = "RU";
		formatVariant[4] = "RU_2";

		const formatDataMap = {
			"RU": {
				"languageId": langId,
				"name": "Россия",
				"description": "ул. Лесная, д. 5, кв. 176<br/>Москва<br/>Россия<br/>125075",
				"delimiter": ", ",
				"sort": 100,
				"templateCollection": {
					"DEFAULT": "[\"#S#\",[ADDRESS_LINE_1:N,ADDRESS_LINE_2,LOCALITY,ADM_LEVEL_2,ADM_LEVEL_1,COUNTRY,POSTAL_CODE]]",
				},
				"code": "RU",
				"fieldForUnRecognized": 600,
				"fieldCollection": [
					{
						"sort": 600,
						"type": 600,
						"name": "Квартира, офис, комната, этаж",
						"description": ""
					},
					{
						"sort": 500,
						"type": 410,
						"name": "Улица, номер дома",
						"description": ""
					},
					{
						"sort": 400,
						"type": 300,
						"name": "Населенный пункт",
						"description": ""
					},
					{
						"sort": 350,
						"type": 210,
						"name": "Район",
						"description": ""
					},
					{
						"sort": 300,
						"type": 200,
						"name": "Регион",
						"description": ""
					},
					{
						"sort": 200,
						"type": 100,
						"name": "Страна",
						"description": ""
					},
					{
						"sort": 100,
						"type": 50,
						"name": "Почтовый индекс",
						"description": ""
					}
				]
			},
			"EU": {
				"languageId": langId,
				"name": "Европа",
				"description": "Musterstr. 321<br/>54321 Musterstadt<br/>Deutschland",
				"delimiter": " ",
				"sort": 200,
				"templateCollection": {
					"DEFAULT": "[\"#S#\",[ADDRESS_LINE_1:N,ADDRESS_LINE_2,[\" \",[POSTAL_CODE,LOCALITY,ADM_LEVEL_2,ADM_LEVEL_1]],COUNTRY]]"
				},
				"code": "EU",
				"fieldForUnRecognized": 600,
				"fieldCollection": [
					{
						"sort": 600,
						"type": 600,
						"name": "Квартира, офис, комната, этаж",
						"description": ""
					},
					{
						"sort": 500,
						"type": 410,
						"name": "Улица, номер дома",
						"description": ""
					},
					{
						"sort": 400,
						"type": 300,
						"name": "Населенный пункт",
						"description": ""
					},
					{
						"sort": 200,
						"type": 100,
						"name": "Страна",
						"description": ""
					},
					{
						"sort": 100,
						"type": 50,
						"name": "Почтовый индекс",
						"description": ""
					}
				]
			},
			"US": {
				"languageId": langId,
				"name": "США",
				"description": "455 Larkspur Dr.<br/>California Springs CA 92926<br/>USA",
				"delimiter": " ",
				"sort": 300,
				"templateCollection": {
					"DEFAULT": "[\"#S#\",[ADDRESS_LINE_1:N,ADDRESS_LINE_2,[\" \",[LOCALITY,ADM_LEVEL_2,ADM_LEVEL_1,POSTAL_CODE:U]],COUNTRY:U]]"
				},
				"code": "US",
				"fieldForUnRecognized": 600,
				"fieldCollection": [
					{
						"sort": 400,
						"type": 600,
						"name": "Квартира, офис, комната, этаж",
						"description": ""
					},
					{
						"sort": 400,
						"type": 410,
						"name": "Улица, номер дома",
						"description": ""
					},
					{
						"sort": 300,
						"type": 300,
						"name": "Населенный пункт",
						"description": ""
					},
					{
						"sort": 250,
						"type": 200,
						"name": "Регион",
						"description": ""
					},
					{
						"sort": 200,
						"type": 100,
						"name": "Страна",
						"description": ""
					},
					{
						"sort": 100,
						"type": 50,
						"name": "Почтовый индекс",
						"description": ""
					}
				]
			},
			"UK": {
				"languageId": langId,
				"name": "Великобритания",
				"description": "49 Featherstone Street<br/>LONDON<br/>EC1Y 8SY<br/>UNITED KINGDOM",
				"delimiter": " ",
				"sort": 400,
				"templateCollection": {
					"DEFAULT": "[\"#S#\",[ADDRESS_LINE_1:N,ADDRESS_LINE_2,LOCALITY:U,ADM_LEVEL_2:U,ADM_LEVEL_1:U,POSTAL_CODE:U,COUNTRY]]"
				},
				"code": "UK",
				"fieldForUnRecognized": 600,
				"fieldCollection": [
					{
						"sort": 400,
						"type": 600,
						"name": "Квартира, офис, комната, этаж",
						"description": ""
					},
					{
						"sort": 400,
						"type": 410,
						"name": "Улица, номер дома",
						"description": ""
					},
					{
						"sort": 300,
						"type": 300,
						"name": "Населенный пункт",
						"description": ""
					},
					{
						"sort": 200,
						"type": 100,
						"name": "Страна",
						"description": ""
					},
					{
						"sort": 100,
						"type": 50,
						"name": "Почтовый индекс",
						"description": ""
					}
				]
			},
			"RU_2": {
				"languageId": langId,
				"name": "Россия (вариант 2)",
				"description": "125075<br/>Россия<br/>Москва<br/>ул. Лесная, д. 5, кв. 176",
				"delimiter": ", ",
				"sort": 500,
				"templateCollection": {
					"DEFAULT": "[\"#S#\",[POSTAL_CODE,COUNTRY,ADM_LEVEL_1,ADM_LEVEL_2,LOCALITY,ADDRESS_LINE_1:N,ADDRESS_LINE_2]]"
				},
				"code": "RU_2",
				"fieldForUnRecognized": 600,
				"fieldCollection": [
					{
						"sort": 600,
						"type": 600,
						"name": "Квартира, офис, комната, этаж",
						"description": ""
					},
					{
						"sort": 500,
						"type": 410,
						"name": "Улица, номер дома",
						"description": ""
					},
					{
						"sort": 400,
						"type": 300,
						"name": "Населенный пункт",
						"description": ""
					},
					{
						"sort": 350,
						"type": 210,
						"name": "Район",
						"description": ""
					},
					{
						"sort": 300,
						"type": 200,
						"name": "Регион",
						"description": ""
					},
					{
						"sort": 200,
						"type": 100,
						"name": "Страна",
						"description": ""
					},
					{
						"sort": 100,
						"type": 50,
						"name": "Почтовый индекс",
						"description": ""
					}
				]
			}
		};

		const formatMethod = [];
		formatMethod[0] = "formatTextComma";
		formatMethod[1] = "formatTextMultiline";
		formatMethod[2] = "formatTextMultilineSpecialchar";
		formatMethod[3] = "formatHtmlMultiline";
		formatMethod[4] = "formatHtmlMultilineSpecialchar";

		const crmExampleVariant = [
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\", <ADDRESS_1_ITEM_4>]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"\n<ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;\n"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"<br /><ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;<br />"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\", <ADDRESS_1_ITEM_4>]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"\n<ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;\n"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"<br /><ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;<br />"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\", <ADDRESS_1_ITEM_4>]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"\n<ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;\n"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"<br /><ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;<br />"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\", <ADDRESS_1_ITEM_4>]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"\n<ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;\n"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"<br /><ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;<br />"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\", <ADDRESS_1_ITEM_4>]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"\n<ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;\n"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"<br /><ADDRESS_1_ITEM_4>]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;<br />"
			+ "&lt;ADDRESS_1_ITEM_4&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [POSTAL_CODE] [CITY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[POSTAL_CODE] [CITY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[POSTAL_CODE] [CITY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[POSTAL_CODE] [CITY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[POSTAL_CODE] [CITY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [CITY], [POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[CITY]\n[POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[CITY]\n[POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[CITY]<br />[POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[CITY]<br />[POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [CITY] [POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[CITY] [POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[CITY] [POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[CITY] [POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[CITY] [POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [CITY], [POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[CITY]\n[POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[CITY]\n[POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[CITY]<br />[POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[CITY]<br />[POSTAL_CODE]",
			"[POSTAL_CODE], [CITY], [<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], "
			+ "[<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]",
			"[POSTAL_CODE]\n[CITY]\n[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n"
			+ "[<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]",
			"[POSTAL_CODE]\n[CITY]\n[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, "
			+ "&quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]",
			"[POSTAL_CODE]<br />[CITY]<br />[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />"
			+ "[<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]",
			"[POSTAL_CODE]<br />[CITY]<br />[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, "
			+ "&quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [POSTAL_CODE] [CITY] [REGION] [PROVINCE], [COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[POSTAL_CODE] [CITY] [REGION] [PROVINCE]\n[COUNTRY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[POSTAL_CODE] [CITY] [REGION] [PROVINCE]\n"
			+ "[COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[POSTAL_CODE] [CITY] [REGION] [PROVINCE]<br />[COUNTRY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[POSTAL_CODE] [CITY] [REGION] [PROVINCE]"
			+ "<br />[COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [CITY], [REGION], [PROVINCE], [POSTAL_CODE], [COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[CITY]\n[REGION]\n[PROVINCE]\n[POSTAL_CODE]\n[COUNTRY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[CITY]\n[REGION]\n[PROVINCE]\n[POSTAL_CODE]\n"
			+ "[COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[CITY]<br />[REGION]<br />[PROVINCE]<br />[POSTAL_CODE]<br />[COUNTRY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[CITY]<br />[REGION]<br />[PROVINCE]<br />"
			+ "[POSTAL_CODE]<br />[COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [CITY] [REGION] [PROVINCE] [POSTAL_CODE], [COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[CITY] [REGION] [PROVINCE] [POSTAL_CODE]\n[COUNTRY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[CITY] [REGION] [PROVINCE] [POSTAL_CODE]\n"
			+ "[COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[CITY] [REGION] [PROVINCE] [POSTAL_CODE]<br />[COUNTRY]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[CITY] [REGION] [PROVINCE] [POSTAL_CODE]"
			+ "<br />[COUNTRY]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>], [CITY], [REGION], [PROVINCE], [COUNTRY], [POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]\n[CITY]\n[REGION]\n[PROVINCE]\n[COUNTRY]\n[POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]\n[CITY]\n[REGION]\n[PROVINCE]\n[COUNTRY]\n"
			+ "[POSTAL_CODE]",
			"[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, "
			+ "<ADDRESS_2_ITEM_2>]<br />[CITY]<br />[REGION]<br />[PROVINCE]<br />[COUNTRY]<br />[POSTAL_CODE]",
			"[&lt;ADDRESS_1_ITEM_1&gt;, &#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />"
			+ "[&lt;ADDRESS_2_ITEM_1&gt;, &lt;ADDRESS_2_ITEM_2&gt;]<br />[CITY]<br />[REGION]<br />[PROVINCE]<br />"
			+ "[COUNTRY]<br />[POSTAL_CODE]",
			"[POSTAL_CODE], [COUNTRY], [PROVINCE], [REGION], [CITY], [<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', "
			+ "\"<ADDRESS_1_ITEM_3>\"], [<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]",
			"[POSTAL_CODE]\n[COUNTRY]\n[PROVINCE]\n[REGION]\n[CITY]\n[<ADDRESS_1_ITEM_1>, '<ADDRESS_1_ITEM_2>', "
			+ "\"<ADDRESS_1_ITEM_3>\"]\n[<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]",
			"[POSTAL_CODE]\n[COUNTRY]\n[PROVINCE]\n[REGION]\n[CITY]\n[&lt;ADDRESS_1_ITEM_1&gt;, "
			+ "&#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]\n[&lt;ADDRESS_2_ITEM_1&gt;, "
			+ "&lt;ADDRESS_2_ITEM_2&gt;]",
			"[POSTAL_CODE]<br />[COUNTRY]<br />[PROVINCE]<br />[REGION]<br />[CITY]<br />[<ADDRESS_1_ITEM_1>, "
			+ "'<ADDRESS_1_ITEM_2>', \"<ADDRESS_1_ITEM_3>\"]<br />[<ADDRESS_2_ITEM_1>, <ADDRESS_2_ITEM_2>]",
			"[POSTAL_CODE]<br />[COUNTRY]<br />[PROVINCE]<br />[REGION]<br />[CITY]<br />[&lt;ADDRESS_1_ITEM_1&gt;, "
			+ "&#39;&lt;ADDRESS_1_ITEM_2&gt;&#39;, &quot;&lt;ADDRESS_1_ITEM_3&gt;&quot;]<br />[&lt;ADDRESS_2_ITEM_1&gt;, "
			+ "&lt;ADDRESS_2_ITEM_2&gt;]",
		];

		// testModel[testId][formatId][methodId] = [addressVariantId, crmExampleVariantId]
		const testModel = [
			[
				[[0, 0], [0, 1], [0, 2], [0, 3], [0, 4]],
				[[0, 5], [0, 6], [0, 7], [0, 8], [0, 9]],
				[[0, 10], [0, 11], [0, 12], [0, 13], [0, 14]],
				[[0, 15], [0, 16], [0, 17], [0, 18], [0, 19]],
				[[0, 20], [0, 21], [0, 22], [0, 23], [0, 24]],
			],
			[
				[[1, 25], [1, 26], [1, 27], [1, 28], [1, 29]],
				[[1, 30], [1, 31], [1, 32], [1, 33], [1, 34]],
				[[1, 35], [1, 36], [1, 37], [1, 38], [1, 39]],
				[[1, 40], [1, 41], [1, 42], [1, 43], [1, 44]],
				[[1, 45], [1, 46], [1, 47], [1, 48], [1, 49]],
			],
			[
				[[2, 50], [2, 51], [2, 52], [2, 53], [2, 54]],
				[[2, 55], [2, 56], [2, 57], [2, 58], [2, 59]],
				[[2, 60], [2, 61], [2, 62], [2, 63], [2, 64]],
				[[2, 65], [2, 66], [2, 67], [2, 68], [2, 69]],
				[[2, 70], [2, 71], [2, 72], [2, 73], [2, 74]],
			],
		];

		for (let testId = 0; testId < testVariant.length; testId++)
		{
			let addressVariantId = testId;
			for (let formatId = 0; formatId < formatVariant.length; formatId++)
			{
				let formatName = formatVariant[formatId];
				let format = new Format(formatDataMap[formatName]);
				for (let methodId = 0; methodId < formatMethod.length; methodId++)
				{
					const methodName = formatMethod[methodId];
					let strategyType = null;
					let contentType = null;
					let locationAddressFormatted = "";
					switch (methodName)
					{
						case "formatTextComma":
							strategyType = StringConverter.STRATEGY_TYPE_TEMPLATE_COMMA;
							contentType = StringConverter.CONTENT_TYPE_TEXT;
							break;
						case "formatTextMultiline":
							strategyType = StringConverter.STRATEGY_TYPE_TEMPLATE;
							contentType = StringConverter.CONTENT_TYPE_TEXT;
							break;
						case "formatTextMultilineSpecialchar":
							strategyType = StringConverter.STRATEGY_TYPE_TEMPLATE_NL;
							contentType = StringConverter.CONTENT_TYPE_HTML;
							break;
						case "formatHtmlMultiline":
							strategyType = StringConverter.STRATEGY_TYPE_TEMPLATE_BR;
							contentType = StringConverter.CONTENT_TYPE_TEXT;
							break;
						case "formatHtmlMultilineSpecialchar":
							strategyType = StringConverter.STRATEGY_TYPE_TEMPLATE_BR;
							contentType = StringConverter.CONTENT_TYPE_HTML;
							break;
					}
					if (contentType !== null)
					{
						locationAddressFormatted = StringConverter.convertAddressToString(
							addressVariant[addressVariantId],
							format,
							strategyType,
							contentType
						);

						// Additional info for debug
						let assertMessage = "";
						const actualValue = locationAddressFormatted;
						const exampleVariantId = testModel[testId][formatId][methodId][1];
						const expectedValue = crmExampleVariant[exampleVariantId];
						if (expectedValue !== actualValue)
						{
							assertMessage =
								`Test variant: "${testVariant[testId]}"\n`
								+ `Format variant: "${formatVariant[formatId]}"\n`
								+ `Method variant: "${formatMethod[methodId]}"\n`
								+ `Address variant index: ${addressVariantId}\n`
								+ `Examlple variant index: ${exampleVariantId}\n`
								+ `Address fields:\n`;
							const addressFields = addressVariant[addressVariantId].fieldCollection.fields;
							for (let type in addressFields)
							{
								if (addressFields.hasOwnProperty(type))
								{
									if (addressFieldTypeMap.hasOwnProperty(type))
									{
										const addressFieldTypeTitle = (addressFieldTypeMap[type] + ":").padEnd(24, " ");
										let value = addressFields[type].value;
										if (Type.isStringFilled(value))
										{
											value = "\"" + value.replace(/"/g, "\\\"").replace(/\n/g, "\\n") + "\"";
										}
										else if (value !== "")
										{
											value = "<not string value>";
										}
										assertMessage += `  ${addressFieldTypeTitle} ${value}\n`;
									}
								}
							}
						}
						assert.strictEqual(
							expectedValue,
							actualValue,
							assertMessage
						);
					}
				}
			}
		}
	});
});