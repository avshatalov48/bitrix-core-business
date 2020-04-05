import Validation from '../../src/lib/validation';
import validEmails from './data/valid-emails';

describe('Validation', () => {
	it('Should be exported as function', () => {
		assert(typeof Validation === 'function');
	});

	describe('#isEmail()', () => {
		it('Should be exported as function', () => {
			assert(typeof Validation.isEmail === 'function');
		});

		it('Should return true for each valid email', () => {
			validEmails.forEach((email) => {
				assert(Validation.isEmail(email));
			});
		});
	});
});