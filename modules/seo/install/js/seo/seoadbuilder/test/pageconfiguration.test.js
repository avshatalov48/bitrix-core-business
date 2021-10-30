import {
	PageConfiguration
} from '../src';

describe('BX.Seo.PageConfiguration', () => {

	describe('Basic usage', () => {
		it('Should validate a uri', () => {

			const pageConfiguration = new PageConfiguration();
			assert.equal(pageConfiguration.validateUrl('test'), false);
			assert.equal(pageConfiguration.validateUrl('https://test.su'), true);
		});
	});
});