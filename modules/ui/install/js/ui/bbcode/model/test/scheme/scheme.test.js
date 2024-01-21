import { BBCodeScheme } from '../../src/scheme/scheme';

describe('ui.bbcode.model/scheme', () => {
	it('Create without options', () => {
		const scheme = new BBCodeScheme();

		assert.ok(scheme.getTagCase() === 'lowerCase');
		assert.ok(scheme.isPropagateUnresolvedNodes() === true);
		assert.ok(scheme.isAllowNewLineAfterBlockClosingTag() === true);
		assert.ok(scheme.isAllowNewLineBeforeBlockClosingTag() === true);
		assert.ok(scheme.isAllowNewLineAfterListItem() === true);
		assert.ok(scheme.isAllowNewLineAfterBlockOpeningTag() === true);
		assert.ok(scheme.isAllowNewLineBeforeBlockOpeningTag() === true);
	});

	it('Create with options', () => {
		const testFilter = () => {};
		const scheme = new BBCodeScheme({
			tagCase: 'upperCase',
			propagateUnresolvedNodes: false,
			newLineAfterListItem: false,
			newLineAfterBlockClosingTag: false,
			newLineBeforeBlockClosingTag: false,
			newLineAfterBlockOpeningTag: false,
			newLineBeforeBlockOpeningTag: false,
			childFilters: {
				'ttt': testFilter,
			},
		});

		assert.ok(scheme.getTagCase() === 'upperCase');
		assert.ok(scheme.isPropagateUnresolvedNodes() === false);
		assert.ok(scheme.isAllowNewLineAfterBlockClosingTag() === false);
		assert.ok(scheme.isAllowNewLineBeforeBlockClosingTag() === false);
		assert.ok(scheme.isAllowNewLineAfterListItem() === false);
		assert.ok(scheme.isAllowNewLineAfterBlockOpeningTag() === false);
		assert.ok(scheme.isAllowNewLineBeforeBlockOpeningTag() === false);
		assert.ok(scheme.getChildFilter('ttt') === testFilter);
	});
});
