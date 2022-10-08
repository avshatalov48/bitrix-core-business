/* global assert */
import AddressJsonConverter from '../../../src/entity/address/converter/jsonconverter';
import Address from '../../../src/entity/address';

describe('AddressJsonConverter', () =>
{
	it('Should be a function', () =>
	{
		assert(typeof AddressJsonConverter === 'function');
	});

	const testData = `{ 
				"id":175,
				"languageId":"en",
				"fieldCollection":{ 
					"100":"Russia",
					"200":"Kaliningrad Oblast",
					"210":"Zelenogradsky District",
					"300":"Zelenogradsk"
				},
				"location":{ 
					"id":294,
					"externalId":"ChIJezDGYPhE40YRjOf-wZR10os",
					"sourceCode":"GOOGLE",
					"type":300,
					"name":"",
					"languageId":"en",
					"latitude":"54.956234",
					"longitude":"20.474702"
				}
		}`;

	describe('convertJsonToAddress', () =>
	{
		it('Should return Address', () =>
		{
			const address = AddressJsonConverter.convertJsonToAddress(
				JSON.parse(testData)
			);
			assert.ok(address instanceof Address);
			assert.equal(address.id, 175);
			assert.equal(address.languageId, 'en');
			assert.equal(address.getFieldValue(200), 'Kaliningrad Oblast');
			// todo: location
		});
	});

	describe('convertAddressToJson', () =>
	{
		it('Should return JSON String', () =>
		{
			const address = new Address(JSON.parse(testData));
			const json = AddressJsonConverter.convertAddressToJson(address);

			assert.equal(typeof json, 'string');
			assert.equal(json, '{"id":175,"languageId":"en","latitude":0,"longitude":0,"fieldCollection":{"100":"Russia","200":"Kaliningrad Oblast","210":"Zelenogradsky District","300":"Zelenogradsk"},"links":[],"location":{"id":294,"code":"","externalId":"ChIJezDGYPhE40YRjOf-wZR10os","sourceCode":"GOOGLE","type":300,"name":"","languageId":"en","latitude":"54.956234","longitude":"20.474702","fieldCollection":{},"address":null}}');
			// todo: location
		});
	});
});