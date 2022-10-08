import LocationRepository from "../../src/repository/locationrepository";
import ActionTestRunner from "./actiontestrunner";
import Location from "../../src/entity/location";
import Address from "../../src/entity/address";

describe('LocationRepository', () => {

	let actionRunner = new ActionTestRunner();
	let repository = new LocationRepository({actionRunner: actionRunner});

	it('Should be a function', () => {
		assert(typeof LocationRepository === 'function');
	});

	describe('findParents', () => {
		it('Should return locations collection', () => {

			actionRunner.response = `[
				{
					"id":222,
					"externalId":"ChIJM5M9n_k940YR4lo5Ozf8Qxk",
					"sourceCode":"GOOGLE",
					"type":100,
					"name":"Bts Gostinaya 5",
					"languageId":"en",
					"latitude":"54.7181357",
					"longitude":"20.4882992"
				},
				{
					"id":223,
					"externalId":"ChIJM5M9n_k940YR4lo5Ozf8Qxk1",
					"sourceCode":"GOOGLE",
					"type":100,
					"name":"Bts Gostinaya 6",
					"languageId":"en",
					"latitude":"54.7181357",
					"longitude":"20.4882992"
				}
			]`;

			repository.findParents(
				new Location(),
				'en'
			)
				.then((locationCollection) => {
					assert.ok(locationCollection);
					assert.ok(Array.isArray(locationCollection));
					assert.ok(locationCollection.length = 2);

					let location = locationCollection[0];

					assert.ok(location instanceof Location);
					assert.equal(location.id, 222);
					assert.equal(location.name, 'Bts Gostinaya 6');
				});
		});

		it('Should throw exception', () => {
			assert.throws(
				() => {
					repository.findParents(new Address(), 'en');
				},
				Error,
				'Location must be defined'
			);
		});
	});

	describe('findByExternalId', () => {
		it('Should return Location', () => {

			actionRunner.response = `
				{
					"id":222,
					"externalId":"ChIJM5M9n_k940YR4lo5Ozf8Qxk",
					"sourceCode":"GOOGLE",
					"type":100,
					"name":"Bts Gostinaya 5",
					"languageId":"en",
					"latitude":"54.7181357",
					"longitude":"20.4882992"
				}`;

			repository.findByExternalId(
				'ChIJM5M9n_k940YR4lo5Ozf8Qxk',
				'GOOGLE.',
				'en'
			)
			.then((location) => {
				assert.ok(location instanceof Location);
				assert.equal(location.id, 222);
				assert.equal(location.name, 'Bts Gostinaya 5');
			});
		});

		it('Should throw exception', () => {
			assert.throws(
				() => {
					repository.findByExternalId(
						'',
						'GOOGLE',
						'en'
					);
				},
				Error,
				'externalId and sourceCode and languageId must be defined'
			);

			assert.throws(
				() => {
					repository.findByExternalId(
						'ChIJM5M9n_k940YR4lo5Ozf8Qxk',
						'',
						'en'
					);
				},
				Error,
				'externalId and sourceCode and languageId must be defined'
			);

			assert.throws(
				() => {
					repository.findByExternalId(
						'ChIJM5M9n_k940YR4lo5Ozf8Qxk',
						'GOOGLE'
					);
				},
				Error,
				'externalId and sourceCode and languageId must be defined'
			);
		});
	});

	describe('findById', () => {
		it('Should return Location', () => {

			actionRunner.response = `
				{
					"id":222,
					"externalId":"ChIJM5M9n_k940YR4lo5Ozf8Qxk",
					"sourceCode":"GOOGLE",
					"type":100,
					"name":"Bts Gostinaya 5",
					"languageId":"en",
					"latitude":"54.7181357",
					"longitude":"20.4882992"
				}`;

			repository.findById(
				222,
				'en'
			)
				.then((location) => {
					assert.ok(location instanceof Location);
					assert.equal(location.id, 222);
					assert.equal(location.name, 'Bts Gostinaya 5');
				});
		});

		it('Should throw exception', () => {
			assert.throws(
				() => {
					repository.findById(
						0,
						'en'
					);
				},
				Error,
				'id and languageId must be defined'
			);

			assert.throws(
				() => {
					repository.findById(
						222
					);
				},
				Error,
				'id and languageId must be defined'
			);
		});
	});
});