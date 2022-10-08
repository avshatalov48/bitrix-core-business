import AddressRepository from '../../src/repository/addressrepository';
import ActionTestRunner from "./actiontestrunner";
import Address from "../../src/entity/address";

describe('AddressRepository', () => {

	let actionRunner = new ActionTestRunner(),
		repository = new AddressRepository({actionRunner: actionRunner});

	it('Should be a function', () => {
		assert(typeof AddressRepository === 'function');
	});

	describe('save', () => {

		it('Should return result of savings', () => {
			actionRunner.response = '{"isSuccess":true,"errors":[],"address":{"id":209,"languageId":"en","fieldCollection":{"100":"Russia","200":"Kaliningrad Oblast","300":"Donskoye","500":"\u0443\u043b. \u042f\u043d\u0442\u0430\u0440\u043d\u0430\u044f 22 - 5","620":"\u0418\u0432\u0430\u043d\u043e\u0432\u0441\u043a\u0438\u0439. \u0422.\u0422."},"location":{"id":307,"externalId":"ChIJvT9BL7m1_EYRIHR0RbO_23c","sourceCode":"GOOGLE","type":300,"name":"Donskoye","languageId":"en","latitude":"54.935372","longitude":"19.965019"}}}';
			let address = '{"id":175,"languageId":"en","fieldCollection":{"100":"Russia","200":"Kaliningrad Oblast","210":"Zelenogradsky District","300":"Zelenogradsk"},"location":{"id":294,"externalId":"ChIJezDGYPhE40YRjOf-wZR10os","sourceCode":"GOOGLE","type":300,"name":"","languageId":"en","latitude":"54.956234","longitude":"20.474702"}}';

			repository.save(address)
				.then((response) => {
					assert.ok(response);
					assert.ok(response.isSuccess = true);
					assert.equal(response.address.getFieldValue(300), 'Zelenogradsk');
			});
		});

		it('Should throw exception', () => {
			assert.throws(
				() => {
					repository.save();
				},
				Error,
				'address must be defined'
			);
		});
	});
});