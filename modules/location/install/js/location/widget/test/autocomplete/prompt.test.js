/* global assert */
import Prompt from '../../src/autocomplete/prompt';

describe('Prompt', () => {

	it('Should be a function', () => {
		assert(typeof Prompt === 'function');
	});

	describe('createMenuItemText', () => {
		it('Should highlight searching text', () => {

			let res = Prompt.createMenuItemText('Russia', 'Rus');
			assert.equal(res, '<strong>Rus</strong>sia');

			res = Prompt.createMenuItemText('Russia', 'uss');
			assert.equal(res, 'R<strong>uss</strong>ia');

			res = Prompt.createMenuItemText('Russia', 'sia');
			assert.equal(res, 'Rus<strong>sia</strong>');

			res = Prompt.createMenuItemText('Russia', 'qwe');
			assert.equal(res, 'Russia');

			res = Prompt.createMenuItemText('901 Pitt Street, Mount Pleasant', '901 Pit str');
			assert.equal(res, '<strong>901</strong> <strong>Pit</strong>t <strong>str</strong>eet, Mount Pleasant');
		});
	});
});