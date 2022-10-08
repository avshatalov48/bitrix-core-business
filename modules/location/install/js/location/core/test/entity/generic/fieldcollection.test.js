import FieldCollection from "../../../src/entity/generic/fieldcollection";
import Field from "../../../src/entity/generic/field";

describe('FieldCollection', () => {

	it('Should be a function', () => {
		assert(typeof FieldCollection === 'function');
	});

	it('Should be constructed successfully', () => {
		let fields = new FieldCollection();

		assert.ok(fields instanceof FieldCollection);

		fields = new FieldCollection({
			fields: [
				new Field({type: 222})
			]
		});

		assert.ok(fields instanceof FieldCollection);
	});

	describe('getField, setField', () => {

		let fields = new FieldCollection();

		fields.setField(new Field({
			type: 222
		}));

		it('Should be instance of Field', () => {
			assert.ok(fields.getField(222) instanceof Field);
		});

		it('Should return initial type', () => {
			assert.equal(fields.getField(222).type, 222);
		});

	});

	describe('isFieldExists', () => {

		let fields = new FieldCollection({
			fields: [
				new Field({type: 222})
			]
		});

		it('Should return true', () => {
			assert.equal(fields.isFieldExists(222), true);
		});

		it('Should return false', () => {
			assert.equal(fields.isFieldExists(333), false);
		});
	});
});