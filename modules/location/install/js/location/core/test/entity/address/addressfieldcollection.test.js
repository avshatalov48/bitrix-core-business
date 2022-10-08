import AddressFieldCollection from "../../../src/entity/address/addressfieldcollection";
import AddressField from "../../../src/entity/address/addressfield";

describe('AddressFieldCollection', () => {

	it('Should be a function', () => {
		assert(typeof AddressFieldCollection === 'function');
	});

	it('Should be constructed successfully', () => {
		let fields = new AddressFieldCollection();

		assert.ok(fields instanceof AddressFieldCollection);

		fields = new AddressFieldCollection({
			fields: [
				new AddressField({type: 222, value: 'testValue'})
			]
		});

		assert.ok(fields instanceof AddressFieldCollection);
	});

	describe('getFieldValue, setFieldValue', () => {

		let fields = new AddressFieldCollection();

		fields.setFieldValue(222,'testValue');

		it('Should return correct value', () => {
			assert.equal(fields.getFieldValue(222), 'testValue');
		});
	});
});