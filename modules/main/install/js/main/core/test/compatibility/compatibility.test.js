import {addClass, hasClass, removeClass, toggleClass} from '../../src/core-compatibility';

describe('compatibility', () => {
	describe('addClass', () => {
		it('Should works with passed element id', () => {
			const element = document.createElement('div');
			element.id = 'testid';
			document.body.appendChild(element);

			addClass('testid', ['test1', 'test2', 'test3']);
			assert(element.className === 'test1 test2 test3');
		});
	});

	describe('removeClass', () => {
		it('Should works with passed element id', () => {
			const element = document.createElement('div');
			element.id = 'testid2';
			element.className = 'test1 test2 test3';
			document.body.appendChild(element);

			removeClass('testid2', 'test1');
			assert(element.className === 'test2 test3');
		});
	});

	describe('hasClass', () => {
		it('Should works with passed element id', () => {
			const element = document.createElement('div');
			element.id = 'testid3';
			element.className = 'test1 test2 test3';
			document.body.appendChild(element);

			assert(hasClass('testid3', 'test2') === true);
		});
	});

	describe('toggleClass', () => {
		it('Should works with passed element id', () => {
			const element = document.createElement('div');
			element.id = 'testid4';
			element.className = 'test1 test2 test3';
			document.body.appendChild(element);

			toggleClass('testid4', 'test2');
			assert(element.className = 'test1 test3');

			toggleClass('testid4', 'test2');
			assert(element.className = 'test1 test2 test3');
		});
	});
});