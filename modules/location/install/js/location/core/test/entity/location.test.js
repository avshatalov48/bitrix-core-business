import Location from "../../src/entity/location";

describe('Location', () => {

	it('Should be a function', () => {
		assert(typeof Location === 'function');
	});

	it('Should successfully constructed without params', () => {
		let location = new Location();
		assert.ok(location instanceof Location);
	});

	it('Should successfully constructed with params', () => {
		let testData = `
			{
				"id":0,
				"externalId":"ChIJM5M9n_k940YR4lo5Ozf8Qxk",
				"sourceCode":"GOOGLE",
				"type":0,
				"name":"Bts Gostinaya 5",
				"languageId":"en",
				"latitude":"54.7181357",
				"longitude":"20.4882992"
			}`;

		let location = new Location(
			JSON.parse(testData)
		);
		assert.ok(location instanceof Location);
		assert.equal(location.id, 0);
		assert.equal(location.externalId, 'ChIJM5M9n_k940YR4lo5Ozf8Qxk');
	});
});