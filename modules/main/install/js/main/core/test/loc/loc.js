import Loc from '../../src/lib/loc';

describe('Loc', () => {
	it('Should replace macros', () => {

		const id = 'TEST_MESS_ID';
		const message = 'Max File Size is #fileSize#';

		Loc.setMessage('TEST_MESS_ID', 'Max File Size is #fileSize#');

		assert.equal(Loc.getMessage(id), message);
		assert.equal(Loc.getMessage(id, { '#fileSize#': 12345 }), 'Max File Size is 12345');

		Loc.setMessage('TEST_MESS_ID2', 'One two one two one');
		assert.equal(Loc.getMessage('TEST_MESS_ID2', { one: '---' }), '--- two --- two ---');
		assert.equal(Loc.getMessage('TEST_MESS_ID2', { two: '---' }), 'One --- one --- one');

		Loc.setMessage('TEST_MESS_ID3', 'One two three two one');
		assert.equal(Loc.getMessage('TEST_MESS_ID3', { one: '---', two: '###' }), '--- ### three ### ---');
	});
});