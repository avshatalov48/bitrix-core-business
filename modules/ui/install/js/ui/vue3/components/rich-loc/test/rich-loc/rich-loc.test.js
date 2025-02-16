import { getTemplateItems, unfoldTemplate } from '../../src/lib';

describe('unfoldTemplate', () => {
	it('should return text without placeholders', () => {
		const text = unfoldTemplate('[spotlight]Моя компания[/spotlight]', '[spotlight]');
		assert.equal(text, 'Моя компания');
	});

	it('should return foreign text without placeholders', () => {
		const text = unfoldTemplate('<span>Lorem ipsum dolor sit amet</span>', '<span>');
		assert.equal(text, 'Lorem ipsum dolor sit amet');
	});
});

describe('getTemplateItems', () => {
	it('should return template item from text', () => {
		const text = 'Будет виден только в списке для пересечения. Подходит для дополнительных ресурсов, которые не бронируются отдельно. [helpdesk]Подробнее[/helpdesk]';
		const templateItems = getTemplateItems(text, '[helpdesk]');

		assert.equal(templateItems.length, 1);
		assert.equal(templateItems[0].placeholder, '[helpdesk]');
		assert.equal(templateItems[0].template, '[helpdesk]Подробнее[/helpdesk]');
		assert.equal(templateItems[0].index, 116);
	});

	it('should return two templates from text', () => {
		const text = 'Использовать <span>рабочее время</span> компании. [helpdesk]Подробнее[/helpdesk]';
		const templateItems = getTemplateItems(text, ['<span>', '[helpdesk]']);

		assert.equal(templateItems.length, 2);
		assert.deepEqual(templateItems[0], {
			index: 13,
			placeholder: '<span>',
			template: '<span>рабочее время</span>',
		});
		assert.deepEqual(templateItems[1], {
			index: 50,
			placeholder: '[helpdesk]',
			template: '[helpdesk]Подробнее[/helpdesk]',
		});
	});

	it('should return empty array if placeholder is not set', () => {
		const text = '[helpdesk]Доступность[/helpdesk] ресурса';
		const templateItems = getTemplateItems(text, []);

		assert.equal(templateItems.length, 0);
	});

	it('should return empty array if the text is empty', () => {
		const templateItems = getTemplateItems('', ['[helpdesk]', '[bold]']);
		assert.equal(templateItems.length, 0);
	});

	it('should return two templateItems with a nested placeholder', () => {
		const text = 'Будет виден только в [helpdesk][bold]списке[/bold] для пересечения[/helpdesk].';
		const templateItems = getTemplateItems(text, ['[bold]', '[helpdesk]']);

		assert.equal(templateItems.length, 2);
		assert.deepEqual(templateItems[0], {
			index: 21,
			placeholder: '[helpdesk]',
			template: '[helpdesk][bold]списке[/bold] для пересечения[/helpdesk]',
		});
		assert.deepEqual(templateItems[1], {
			index: 31,
			placeholder: '[bold]',
			template: '[bold]списке[/bold]',
		});
	});
});
