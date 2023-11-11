import { Node } from '../../src/bbom/node';
import { TextNode } from '../../src/bbom/text-node';
import { NewLineNode } from '../../src/bbom/new-line-node';
import { TabNode } from '../../src/bbom/tab-node';
import { ElementNode } from '../../src/bbom/element-node';
import { FragmentNode } from '../../src/bbom/fragment-node';

describe('ui.bbcode.parser/BBOM', () => {
	describe('TextNode', () => {
		it('Create TextNode with options object', () => {
			const parent = new Node();
			const node = new TextNode({
				content: 'test text',
				name: '1111',
				parent,
			});

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === 'test text');
			assert.ok(node.toString() === 'test text');
			assert.ok(node.getParent() === parent);
			assert.ok(node.getName() === '#text');
		});

		it('Create TextNode with options string', () => {
			const node = new TextNode('test text');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === 'test text');
			assert.ok(node.toString() === 'test text');
		});

		it('Should encode return decoded content', () => {
			const node = new TextNode('&#91;text&#93;');

			assert.ok(node.getContent() === '[text]');
			assert.ok(node.toString() === '[text]');
		});

		it('TextNode.setParent()', () => {
			const node = new TextNode();
			const parent1 = new Node();
			const parent2 = new Node();

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('TextNode.toJSON()', () => {
			const node = new TextNode('test text');

			assert.deepEqual(node.toJSON(), {content: 'test text', name: '#text'});
		});

		it('TextNode.setName()', () => {
		    const node = new TextNode();

			assert.ok(node.getName() === '#text');

			node.setName('11111');

			assert.ok(node.getName() === '#text');
		});
	});

	describe('NewLineNode', () => {
		it('Create NewLineNode without options', () => {
			const node = new NewLineNode();

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('Create NewLineNode with options object', () => {
			const parent = new Node();
			const node = new NewLineNode({
				content: '1111',
				name: '11111',
				parent,
			});

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
			assert.ok(node.getParent() === parent);
			assert.ok(node.getName() === '#linebreak');
		});

		it('Create NewLineNode with options string', () => {
			const node = new NewLineNode('1111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('NewLineNode.setContent() do not affected content property', () => {
			const node = new NewLineNode();

			node.setContent('1111111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('NewLineNode.setParent()', () => {
			const node = new NewLineNode();
			const parent1 = new Node();
			const parent2 = new Node();

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('NewLineNode.toJSON()', () => {
			const node = new NewLineNode();

			assert.deepEqual(node.toJSON(), {content: '\n', name: '#linebreak'});
		});

		it('NewLineNode.setName()', () => {
		    const node = new NewLineNode();

			assert.ok(node.getName() === '#linebreak');

			node.setName('111111');

			assert.ok(node.getName() === '#linebreak');
		});

		it('NewLineNode.setContent()', () => {
			const node = new NewLineNode();

			assert.ok(node.getContent() === '\n');

			node.setContent('111111');

			assert.ok(node.getContent() === '\n');
		});
	});

	describe('TabNode', () => {
		it('Create TabNode without options', () => {
			const node = new TabNode();

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('Create TabNode with options object', () => {
			const parent = new Node();
			const node = new TabNode({ content: '1111', parent });

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
			assert.ok(node.getParent() === parent);
		});

		it('Create TabNode with options string', () => {
			const node = new TabNode('1111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('TabNode.setContent() do not affected content property', () => {
			const node = new TabNode();

			node.setContent('1111111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('TabNode.setParent()', () => {
			const node = new TabNode();
			const parent1 = new Node();
			const parent2 = new Node();

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('TabNode.toJSON()', () => {
			const node = new TabNode();

			assert.deepEqual(node.toJSON(), {content: '\t', name: '#tab'});
		});

		it('TabNode.setName()', () => {
			const node = new TabNode();

			assert.ok(node.getName() === '#tab');

			node.setName('111111');

			assert.ok(node.getName() === '#tab');
		});

		it('TabNode.setContent()', () => {
			const node = new TabNode();

			assert.ok(node.getContent() === '\t');

			node.setContent('111111');

			assert.ok(node.getContent() === '\t');
		});
	});

	describe('FragmentNode', () => {
		it('Create FragmentNode with options', () => {
			const node = new FragmentNode({
				children: [
					new TextNode('text'),
					new FragmentNode({
						children: [
							new TextNode('text2'),
						],
					})
				],
			});

			assert.ok(node.getType() === Node.FRAGMENT_NODE);
			assert.ok(node.getChildrenCount() === 2);
			assert.ok(node.getChildren().at(0).getContent() === 'text');
			assert.ok(node.getChildren().at(1).getContent() === 'text2');
		});
	});

	describe('ElementNode', () => {
	    it('Create ElementNode with options object', () => {
	        const node = new ElementNode({
				name: 'p',
			});

			assert.ok(node.getType() === Node.ELEMENT_NODE);
			assert.ok(node.getName() === 'p');
			assert.ok(node.getValue() === '');
			assert.ok(node.isVoid() === false);
			assert.ok(node.isInline() === false);
			assert.deepEqual(node.getAttributes(), {});
			assert.ok(node.getChildrenCount() === 0);
			assert.deepEqual(node.getChildren(), []);
			assert.ok(node.hasChildren() === false);

			const node2 = new ElementNode({
				name: 'disk',
				value: 'test',
				void: true,
				inline: true,
				attributes: {
					key1: 'value1',
					key2: true,
					key3: 33,
				},
			});

			assert.ok(node2.getType() === Node.ELEMENT_NODE);
			assert.ok(node2.getName() === 'disk');
			assert.ok(node2.getValue() === 'test');
			assert.ok(node2.isVoid() === true);
			assert.ok(node2.isInline() === true);
			assert.deepEqual(node2.getAttributes(), { key1: 'value1', key2: true, key3: 33 });

			const node3 = new ElementNode({
				name: 'p',
				children: [
					new TextNode('test'),
					new ElementNode({
						name: 'b',
						children: [
							new TextNode('bold'),
						],
					}),
					new NewLineNode(),
					new NewLineNode(),
					new ElementNode({
						name: 'i',
					}),
					new FragmentNode({
						children: [
							new ElementNode({
								name: 'p',
								value: 'fragment1',
							}),
							new TextNode('fragment2'),
							new NewLineNode(),
							new TabNode(),
						],
					}),
				],
			});

			assert.ok(node3.getType() === Node.ELEMENT_NODE);
			assert.ok(node3.getName() === 'p');
			assert.ok(node3.getChildrenCount() === 9);
			assert.ok(node3.hasChildren() === true);
			assert.ok(node3.getChildren().at(0).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(0).getContent() === 'test');
			assert.ok(node3.getChildren().at(1).getType() === Node.ELEMENT_NODE);
			assert.ok(node3.getChildren().at(1).getName() === 'b');
			assert.ok(node3.getChildren().at(1).getChildrenCount() === 1);
			assert.ok(node3.getChildren().at(1).getChildren().at(0).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(1).getChildren().at(0).getContent() === 'bold');
			assert.ok(node3.getChildren().at(2).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(2).getContent() === '\n');
			assert.ok(node3.getChildren().at(3).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(3).getContent() === '\n');
			assert.ok(node3.getChildren().at(4).getType() === Node.ELEMENT_NODE);
			assert.ok(node3.getChildren().at(4).getName() === 'i');
			assert.ok(node3.getChildren().at(5).getType() === Node.ELEMENT_NODE);
			assert.ok(node3.getChildren().at(5).getName() === 'p');
			assert.ok(node3.getChildren().at(5).getValue() === 'fragment1');
			assert.ok(node3.getChildren().at(6).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(6).getContent() === 'fragment2');
			assert.ok(node3.getChildren().at(7).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(7).getContent() === '\n');
			assert.ok(node3.getChildren().at(8).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(8).getContent() === '\t');
	    });

		it('ElementNode.appendChild()', () => {
		    const node = new ElementNode({
				name: 'p',
			});

			const bold = new ElementNode({
				name: 'b',
			});

			const italic = new ElementNode({
				name: 'i',
			});

			assert.ok(node.hasChildren() === false);

			node.appendChild(bold);
			assert.ok(node.hasChildren() === true);
			assert.ok(node.getChildrenCount() === 1);
			assert.ok(node.getChildren().at(0) === bold);
			assert.ok(bold.getParent() === node);

			node.appendChild(italic);
			assert.ok(node.hasChildren() === true);
			assert.ok(node.getChildrenCount() === 2);
			assert.ok(node.getChildren().at(0) === bold);
			assert.ok(node.getChildren().at(1) === italic);
			assert.ok(italic.getParent() === node);

			node.appendChild(bold);
			assert.ok(node.getChildrenCount() === 2);
			assert.ok(node.getChildren().at(0) === italic);
			assert.ok(node.getChildren().at(1) === bold);
			assert.ok(bold.getParent() === node);
		});

		it('ElementNode.replaceChild()', () => {
			const node = new ElementNode({
				name: 'p',
			});

			const bold = new ElementNode({
				name: 'b',
			});

			const italic = new ElementNode({
				name: 'i',
			});

			node.appendChild(bold);
			assert.ok(node.getChildrenCount() === 1);
			assert.ok(node.getChildren().at(0) === bold);
			assert.ok(node.getChildren().at(0).getParent() === node);

			node.replaceChild(bold, italic);
			assert.ok(node.getChildrenCount() === 1);
			assert.ok(node.getChildren().at(0) === italic);
			assert.ok(node.getChildren().at(0).getParent() === node);
			assert.ok(bold.getParent() === null);

			const strike = new ElementNode({ name: 's' });
			const text = new TextNode('test');
			const fragment = new FragmentNode({
				children: [
					strike,
					text,
				],
			});

			node.replaceChild(italic, fragment);
			assert.ok(node.getChildrenCount() === 2);
			assert.ok(node.getChildren().at(0) === strike);
			assert.ok(node.getChildren().at(1) === text);
		});

		it('ElementNode.removeChild()', () => {
		    const node = new ElementNode({
				name: 'p',
			});

			const bold = new ElementNode({
				name: 'b',
			});

			const text = new TextNode('test');

			const newLine = new NewLineNode();
			const tab = new TabNode();

			node.appendChild(...[bold, text, newLine, tab]);
			assert.ok(node.getChildrenCount() === 4);
			assert.ok(node.getChildren().at(0) === bold);
			assert.ok(node.getChildren().at(1) === text);
			assert.ok(node.getChildren().at(2) === newLine);
			assert.ok(node.getChildren().at(3) === tab);

			node.removeChild(text);
			assert.ok(text.getParent() === null);
			assert.ok(node.getChildrenCount() === 3);
			assert.ok(node.getChildren().at(0) === bold);
			assert.ok(node.getChildren().at(1) === newLine);
			assert.ok(node.getChildren().at(2) === tab);

			node.removeChild(bold, tab);
			assert.ok(node.getChildrenCount() === 1);
			assert.ok(bold.getParent() === null);
			assert.ok(tab.getParent() === null);
			assert.ok(node.getChildren().at(0) === newLine);
		});

		it('Should sets inline true automatically for inline elements', () => {
		    const bold = new ElementNode({ name: 'b' });
			assert.ok(bold.isInline() === true);

		    const italic = new ElementNode({ name: 'i' });
			assert.ok(italic.isInline() === true);

		    const underline = new ElementNode({ name: 'u' });
			assert.ok(underline.isInline() === true);

		    const strike = new ElementNode({ name: 's' });
			assert.ok(strike.isInline() === true);

		    const size = new ElementNode({ name: 'size' });
			assert.ok(size.isInline() === true);

		    const color = new ElementNode({ name: 'color' });
			assert.ok(color.isInline() === true);

		    const left = new ElementNode({ name: 'left' });
			assert.ok(left.isInline() === true);

		    const center = new ElementNode({ name: 'center' });
			assert.ok(center.isInline() === true);

		    const right = new ElementNode({ name: 'right' });
			assert.ok(right.isInline() === true);

		    const url = new ElementNode({ name: 'url' });
			assert.ok(url.isInline() === true);

		    const img = new ElementNode({ name: 'img' });
			assert.ok(img.isInline() === true);
		});

		it('Should NOT sets inline true automatically for inline elements', () => {
			const p = new ElementNode({ name: 'p' });
			assert.ok(p.isInline() === false);

			const code = new ElementNode({ name: 'code' });
			assert.ok(code.isInline() === false);

			const table = new ElementNode({ name: 'table' });
			assert.ok(table.isInline() === false);

			const tr = new ElementNode({ name: 'tr' });
			assert.ok(tr.isInline() === false);

			const td = new ElementNode({ name: 'td' });
			assert.ok(td.isInline() === false);

			const th = new ElementNode({ name: 'th' });
			assert.ok(th.isInline() === false);

			const list = new ElementNode({ name: 'list' });
			assert.ok(list.isInline() === false);
		});

		it('Table child filter', () => {
		    const table = new ElementNode({
				name: 'table',
				children: [
					new ElementNode({ name: 'p' }),
					new ElementNode({ name: 'td' }),
					new ElementNode({ name: 'th' }),
					new ElementNode({ name: 'tr' }),
					new TextNode('test'),
					new NewLineNode(),
					new TabNode(),
				],
			});

			assert.ok(table.getChildrenCount() === 1);
			assert.ok(table.getChildren().at(0).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');

			table.appendChild(new ElementNode({ name: 'p' }));
			table.appendChild(new TextNode('test'));
			table.appendChild(new NewLineNode());

			assert.ok(table.getChildrenCount() === 1);
			assert.ok(table.getChildren().at(0).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');

			table.appendChild(new ElementNode({ name: 'tr' }));

			assert.ok(table.getChildrenCount() === 2);
			assert.ok(table.getChildren().at(0).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');
			assert.ok(table.getChildren().at(1).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(1).getName() === 'tr');
		});

		it('Table row child filter', () => {
		    const row = new ElementNode({
				name: 'tr',
			});
			const tr = new ElementNode({ name: 'tr' });
			const td = new ElementNode({ name: 'td' });
			const th = new ElementNode({ name: 'th' });
			const p = new ElementNode({ name: 'p' });
			const text = new TextNode('test');

			row.appendChild(...[tr, td, th, p, text]);

			assert.ok(row.getChildrenCount() === 2);
			assert.ok(row.getChildren().at(0) === td);
			assert.ok(row.getChildren().at(1) === th);
		});

		it('Table cell child filter', () => {
			const td = new ElementNode({
				name: 'td'
			});

			const table = new ElementNode({ name: 'table' });
			const tr = new ElementNode({ name: 'tr' });
			const p = new ElementNode({ name: 'p' });
			const bold = new ElementNode({ name: 'b' });
			const strike = new ElementNode({ name: 's' });
			const text = new TextNode('test');
			const newLine = new NewLineNode();
			const tab = new TabNode();

			td.appendChild(...[table, tr, p, bold, strike, text, newLine, tab]);

			assert.ok(td.getChildrenCount() === 4);
			assert.ok(td.getChildren().at(0) === bold);
			assert.ok(td.getChildren().at(1) === strike);
			assert.ok(td.getChildren().at(2) === text);
			assert.ok(td.getChildren().at(3) === newLine);
		});
	});
});
