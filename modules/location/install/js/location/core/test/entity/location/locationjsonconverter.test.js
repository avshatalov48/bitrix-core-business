import LocationJsonConverter from "../../../src/entity/location/locationjsonconverter";
import Location from "../../../src/entity/location";

describe('LocationJsonConverter', () => {

	it('Should be a function', () => {
		assert(typeof LocationJsonConverter === 'function');
	});

	let testData = `
			{
				"id":5,
				"externalId":"ChIJM5M9n_k940YR4lo5Ozf8Qxk",
				"sourceCode":"GOOGLE",
				"type":0,
				"name":"Bts Gostinaya 5",
				"languageId":"en",
				"latitude":"54.7181357",
				"longitude":"20.4882992"				
			}`;

	describe('convertJsonToLocation', () => {

		it('Should return Location', () => {

			let location = new Location(
				JSON.parse(testData)
			);
			assert.ok(location instanceof Location);
			assert.equal(location.id, 5);
			assert.equal(location.externalId, 'ChIJM5M9n_k940YR4lo5Ozf8Qxk');
		});
	});

	describe('convertLocationToJson', () => {

		it('Should return JSON', () => {

			let location = new Location(
				JSON.parse(testData)
			);

			let json = LocationJsonConverter.convertLocationToJson(location);
			assert.equal(json, '{"id":5,"code":"","externalId":"ChIJM5M9n_k940YR4lo5Ozf8Qxk","sourceCode":"GOOGLE","type":0,"name":"Bts Gostinaya 5","languageId":"en","latitude":"54.7181357","longitude":"20.4882992","fieldCollection":{},"address":null}');
		});
	});
});