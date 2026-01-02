import 'im.v2.test';

import { updateBrowserTitleCounter } from '../../src/helpers/update-browser-title-counter';

describe('updateBrowserTitleCounter', () => {
	it('adds counter to empty title', () => {
		document.title = 'Original Title';
		updateBrowserTitleCounter(5);
		assert.equal(document.title, '(5) Original Title');
	});

	it('updates existing counter', () => {
		document.title = '(3) Original Title';
		updateBrowserTitleCounter(7);
		assert.equal(document.title, '(7) Original Title');
	});

	it('removes counter when new counter is 0', () => {
		document.title = '(5) Original Title';
		updateBrowserTitleCounter(0);
		assert.equal(document.title, 'Original Title');
	});

	it('converts counter > 99 to 99+', () => {
		document.title = 'Original Title';
		updateBrowserTitleCounter(150);
		assert.equal(document.title, '(99+) Original Title');
	});

	it('does not update if counter is the same', () => {
		document.title = '(7) Original Title';
		updateBrowserTitleCounter(7);
		assert.equal(document.title, '(7) Original Title');
	});

	it('does not update if counter new counter is more than 99', () => {
		document.title = '(99+) Original Title';
		updateBrowserTitleCounter(100);
		assert.equal(document.title, '(99+) Original Title');
	});
});
