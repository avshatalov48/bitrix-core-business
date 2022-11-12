import {BitrixVue} from 'ui.vue3';

describe('BitrixVue', () => {
	describe('testNode', () => {
		it('Should return false if the element does not contain the className', () => {
			const element = document.createElement('div');
			element.className = 'test1 test2 test3';

			assert.equal(BitrixVue.testNode(element, {className: 'test4'}), false);
		});
		it('should return true if the element contains the className', () => {
			const element = document.createElement('div');
			element.className = 'test1 test2 test3';

			assert.equal(BitrixVue.testNode(element, {className: 'test1'}), true);
			assert.equal(BitrixVue.testNode(element, {className: 'test2'}), true);
			assert.equal(BitrixVue.testNode(element, {className: 'test3'}), true);
		});
	});
});