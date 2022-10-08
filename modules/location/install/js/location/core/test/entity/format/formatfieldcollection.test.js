import FormatFieldCollection from "../../../src/entity/format/formatfieldcollection";
import FormatField from "../../../src/entity/format/formatfield";


describe('FormatFieldCollection', () => {

	it('Should be a function', () => {
		assert(typeof FormatFieldCollection === 'function');
	});

	it('Should be constructed successfully', () => {
		let fields = new FormatFieldCollection();

		assert.ok(fields instanceof FormatFieldCollection);

		fields = new FormatFieldCollection({
			fields: [
				new FormatField({type: 222, name: 'test field'})
			]
		});

		assert.ok(fields instanceof FormatFieldCollection);
		assert.equal(fields.getField(222).name, 'test field')
	});
});