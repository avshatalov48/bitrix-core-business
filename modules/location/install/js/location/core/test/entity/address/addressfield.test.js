import AddressField from "../../../src/entity/address/addressfield";

describe('AddressField', () => {

	it('Should be a function', () => {
		assert(typeof AddressField === 'function');
	});

	it('Should be constructed successfully', () => {
		let field = new AddressField({
			type: 222,
			value: 'test value'
		});

		assert.ok(field instanceof AddressField);
		assert.equal(field.type, 222);
		assert.equal(field.value, 'test value');
	});

	describe('get / set', () => {
		it('Should return setted values', () => {
			let field = new AddressField({
				type: 222
			});

			field.value = 'test value';

			assert.equal(field.value, 'test value');
		});
	});
});