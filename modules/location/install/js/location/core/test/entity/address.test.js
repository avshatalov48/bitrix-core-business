import Address from "../../src/entity/address";

describe('Address', () => {

	it('Should be a function', () => {
		assert(typeof Address === 'function');
	});

	it('Should sucessfully constructed with minimal  set of params', () => {
		let address = new Address({languageId: 'en'});
		assert.ok(address instanceof Address);
	});

	it('Should sucessfully constructed from JSON', () => {
		let testData = `{ 
				"id":175,
				"languageId":"en",
				"fieldCollection":{ 
					"100":"Russia",
					"200":"Kaliningrad Oblast",
					"210":"Zelenogradsky District",
					"300":"Zelenogradsk"
				},
				"links":[
					{"entityId": 123, "entityType": "ADDRESS_TEST_TYPE"},
					{"entityId": 124, "entityType": "ADDRESS_TEST_TYPE"}
				],
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

		let address = new Address(JSON.parse(testData));
		assert.ok(address instanceof Address);
		assert.equal(address.id, 175);
		assert.equal(address.languageId, 'en');
		assert.equal(address.getFieldValue(200), 'Kaliningrad Oblast');
		assert.equal(address.links.length, 2);
		assert.equal(address.links[0].entityId, 123);
		assert.equal(address.links[1].entityType, "ADDRESS_TEST_TYPE");
	});

	it('Should rise exception when constructed without the languageId', () => {
		assert.throws(
			() => {
				let address = new Address();
			},
			Error,
			'LanguageId must be defined'
		);
	});

	describe('setFieldValue,  getFieldValue', () => {

		let address = new Address({
			languageId: 'en',
			fieldCollection: {
				200: 'testValue'
			}
		});

		it('getFieldValue should reurn equal value', () => {
			assert.equal(address.getFieldValue(200), 'testValue');
		});

		it('getFieldValue should return null', () => {
			assert.equal(address.getFieldValue(300), null);
		});

		address.setFieldValue(400, 'testValue2');
		address.setFieldValue(500, 'testValue3');

		it('getFieldValue should return testValue3', () => {
			assert.equal(address.getFieldValue(500), 'testValue3');
		});
	});

	describe('isFieldExists', () => {

		let address = new Address({
			languageId: 'en',
			fieldCollection: {
				200:'testValue'
			}
		});

		it('Should return true', () => {
			assert.ok(address.isFieldExists(200));
		});

		it('Should return false', () => {
			assert.equal(address.isFieldExists(300), false);
		});
	});

	describe('setters / getters', () => {

		let address = new Address({
			languageId: 'en',
			id: 123,
		});

		it('Should return id', () => {
			assert.equal(address.id, 123);
		});

		it('Should return languageId', () => {
			assert.equal(address.languageId, 'en');
		});

		it('Should return null', () => {
			assert.equal(address.location, null);
		});
	});

	describe('addLink', () => {
		let address = new Address({
			languageId: 'en'
		});

		assert.equal(address.links.length, 0);

		address.addLink(123, 'ADDRESS_TEST_ENTITY');

		assert.equal(address.links.length, 1);
		assert.equal(address.links[0].entityId, 123);
		assert.equal(address.links[0].entityType, "ADDRESS_TEST_ENTITY");
	});

	describe('clearLinks', () => {
		let address = new Address({
			languageId: 'en',
			links: [
				{"entityId": 123, "entityType": "ADDRESS_TEST_ENTITY"},
				{"entityId": 124, "entityType": "ADDRESS_TEST_ENTITY"}
			]
		});

		assert.equal(address.links.length, 2);
		assert.equal(address.links[0].entityId, 123);
		assert.equal(address.links[1].entityType, "ADDRESS_TEST_ENTITY");

		address.clearLinks();

		assert.equal(address.links.length, 0);
	});
});