import { Node } from '../../src/nodes/node';
import { ModelFactory } from '../../src/factory/factory';
import { BBCodeScheme } from '../../src/scheme/scheme';

const factory = new ModelFactory();

describe('ui.bbcode.model/nodes', () => {
	describe('TextNode', () => {
		it('Create TextNode with options object', () => {
			const parent = factory.createNode();
			const node = factory.createTextNode({
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
			const node = factory.createTextNode('test text');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === 'test text');
			assert.ok(node.toString() === 'test text');
		});

		it('Should encode return decoded content', () => {
			const node = factory.createTextNode('&#91;text&#93;');

			assert.ok(node.getContent() === '[text]');
			assert.ok(node.toString() === '[text]');
		});

		it('TextNode.setParent()', () => {
			const node = factory.createTextNode();
			const parent1 = factory.createNode();
			const parent2 = factory.createNode();

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('TextNode.toJSON()', () => {
			const node = factory.createTextNode('test text');

			assert.deepEqual(node.toJSON(), {content: 'test text', name: '#text'});
		});

		it('TextNode.setName()', () => {
			const node = factory.createTextNode();

			assert.ok(node.getName() === '#text');

			node.setName('11111');

			assert.ok(node.getName() === '#text');
		});

		it('TextNode.clone()', () => {
			const sourceNode = factory.createTextNode('test text');
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});

		describe('TextNode.splitText()', () => {
			it('should throws if passed offset less than 0', () => {
				const textNode = factory.createTextNode('test text');

				assert.throws(() => {
					textNode.splitText(-1);
				});

				assert.throws(() => {
					textNode.splitText(-20);
				});
			});

			it('should throws if passed offset more than text length', () => {
				const textNode = factory.createTextNode('test text');

				assert.throws(() => {
					textNode.splitText(10);
				});

				assert.throws(() => {
					textNode.splitText(20);
				});
			});

			it('should returns null for left node if passed 0 offset', () => {
				const textNode = factory.createTextNode('test text');
				const [leftNode, rightNode] = textNode.splitText(0);

				assert.ok(leftNode === null);
				assert.ok(rightNode.getName() === '#text');
			});

			it('should returns null for right node if offset equal text length', () => {
				const textNode = factory.createTextNode('test text');
				const [leftNode, rightNode] = textNode.splitText(9);

				assert.ok(leftNode.getName() === '#text');
				assert.ok(rightNode === null);
			});

			it('should split node if passed offset in text range', () => {
				const textNode = factory.createTextNode('test text');

				const [leftNode1, rightNode1] = textNode.splitText(1);
				assert.ok(leftNode1.getContent() === 't');
				assert.ok(rightNode1.getContent() === 'est text');

				const [leftNode2, rightNode2] = textNode.splitText(2);
				assert.ok(leftNode2.getContent() === 'te');
				assert.ok(rightNode2.getContent() === 'st text');

				const [leftNode3, rightNode3] = textNode.splitText(3);
				assert.ok(leftNode3.getContent() === 'tes');
				assert.ok(rightNode3.getContent() === 't text');

				const [leftNode4, rightNode4] = textNode.splitText(4);
				assert.ok(leftNode4.getContent() === 'test');
				assert.ok(rightNode4.getContent() === ' text');

				const [leftNode5, rightNode5] = textNode.splitText(5);
				assert.ok(leftNode5.getContent() === 'test ');
				assert.ok(rightNode5.getContent() === 'text');

				const [leftNode6, rightNode6] = textNode.splitText(6);
				assert.ok(leftNode6.getContent() === 'test t');
				assert.ok(rightNode6.getContent() === 'ext');

				const [leftNode7, rightNode7] = textNode.splitText(7);
				assert.ok(leftNode7.getContent() === 'test te');
				assert.ok(rightNode7.getContent() === 'xt');

				const [leftNode8, rightNode8] = textNode.splitText(8);
				assert.ok(leftNode8.getContent() === 'test tex');
				assert.ok(rightNode8.getContent() === 't');
			});

			it('should returns this node as left node if offset is equal content length', () => {
				const textNode = factory.createTextNode('test text');
				const [leftNode, rightNode] = textNode.splitText(9);

				assert.ok(leftNode === textNode);
				assert.ok(rightNode === null);
			});

			it('should returns this node as right node if offset is equal 0', () => {
				const textNode = factory.createTextNode('test text');
				const [leftNode, rightNode] = textNode.splitText(0);

				assert.ok(leftNode === null);
				assert.ok(rightNode === textNode);
			});

			it('should replace the current node with new nodes', () => {
				const textNode = factory.createTextNode('test text');
				const pNode = factory.createElementNode({
					name: 'p',
					children: [
						textNode,
					],
				});

				textNode.splitText(4);

				assert.ok(pNode.getChildrenCount() === 2);
				assert.ok(pNode.getFirstChild().getContent() === 'test');
				assert.ok(pNode.getLastChild().getContent() === ' text');
			});
		});
	});

	describe('NewLineNode', () => {
		it('Create NewLineNode without options', () => {
			const node = factory.createNewLineNode();

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('Create NewLineNode with options object', () => {
			const parent = factory.createNode();
			const node = factory.createNewLineNode({
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
			const node = factory.createNewLineNode('1111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('NewLineNode.setContent() do not affected content property', () => {
			const node = factory.createNewLineNode();

			node.setContent('1111111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('NewLineNode.setParent()', () => {
			const node = factory.createNewLineNode();
			const parent1 = factory.createNode();
			const parent2 = factory.createNode();

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('NewLineNode.toJSON()', () => {
			const node = factory.createNewLineNode();

			assert.deepEqual(node.toJSON(), {content: '\n', name: '#linebreak'});
		});

		it('NewLineNode.setName()', () => {
			const node = factory.createNewLineNode();

			assert.ok(node.getName() === '#linebreak');

			node.setName('111111');

			assert.ok(node.getName() === '#linebreak');
		});

		it('NewLineNode.setContent()', () => {
			const node = factory.createNewLineNode();

			assert.ok(node.getContent() === '\n');

			node.setContent('111111');

			assert.ok(node.getContent() === '\n');
		});

		it('NewLineNode.clone()', () => {
		    const sourceNode = factory.createNewLineNode();
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});
	});

	describe('TabNode', () => {
		it('Create TabNode without options', () => {
			const node = factory.createTabNode();

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('Create TabNode with options object', () => {
			const parent = factory.createNode();
			const node = factory.createTabNode({ content: '1111', parent });

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
			assert.ok(node.getParent() === parent);
		});

		it('Create TabNode with options string', () => {
			const node = factory.createTabNode('1111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('TabNode.setContent() do not affected content property', () => {
			const node = factory.createTabNode();

			node.setContent('1111111');

			assert.ok(node.getType() === Node.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('TabNode.setParent()', () => {
			const node = factory.createTabNode();
			const parent1 = factory.createNode();
			const parent2 = factory.createNode();

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('TabNode.toJSON()', () => {
			const node = factory.createTabNode();

			assert.deepEqual(node.toJSON(), {content: '\t', name: '#tab'});
		});

		it('TabNode.setName()', () => {
			const node = factory.createTabNode();

			assert.ok(node.getName() === '#tab');

			node.setName('111111');

			assert.ok(node.getName() === '#tab');
		});

		it('TabNode.setContent()', () => {
			const node = factory.createTabNode();

			assert.ok(node.getContent() === '\t');

			node.setContent('111111');

			assert.ok(node.getContent() === '\t');
		});

		it('TabNode.clone()', () => {
			const sourceNode = factory.createTabNode();
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});
	});

	describe('FragmentNode', () => {
		it('Create FragmentNode with options', () => {
			const node = factory.createFragmentNode({
				children: [
					factory.createTextNode('text'),
					factory.createFragmentNode({
						children: [
							factory.createTextNode('text2'),
						],
					})
				],
			});

			assert.ok(node.getType() === Node.FRAGMENT_NODE);
			assert.ok(node.getChildrenCount() === 2);
			assert.ok(node.getChildren().at(0).getContent() === 'text');
			assert.ok(node.getChildren().at(1).getContent() === 'text2');
		});

		it('FragmentNode.clone()', () => {
			const sourceNode = factory.createFragmentNode();
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
		});

		it('FragmentNode.clone({ deep: true })', () => {
			const sourceNode = factory.createFragmentNode({
				children: [
					factory.createElementNode({
						name: 'b',
						children: [
							factory.createTextNode('test text'),
						],
					}),
				],
			});
			const clonedNode = sourceNode.clone({ deep: true });

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});
	});

	describe('ElementNode', () => {
		it('Create ElementNode with options object', () => {
			const node = factory.createElementNode({
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

			const node2 = factory.createElementNode({
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

			const node3 = factory.createElementNode({
				name: 'p',
				children: [
					factory.createTextNode('test'),
					factory.createElementNode({
						name: 'b',
						children: [
							factory.createTextNode('bold'),
						],
					}),
					factory.createNewLineNode(),
					factory.createNewLineNode(),
					factory.createElementNode({
						name: 'i',
					}),
					factory.createFragmentNode({
						children: [
							factory.createElementNode({
								name: 'b',
								value: 'fragment1',
							}),
							factory.createTextNode('fragment2'),
							factory.createNewLineNode(),
							factory.createTabNode(),
						],
					}),
				],
			});

			assert.ok(node3.getType() === Node.ELEMENT_NODE);
			assert.ok(node3.getName() === 'p');
			assert.ok(node3.getChildrenCount() === 8);
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
			assert.ok(node3.getChildren().at(5).getName() === 'b');
			assert.ok(node3.getChildren().at(5).getValue() === 'fragment1');
			assert.ok(node3.getChildren().at(6).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(6).getContent() === 'fragment2');
			assert.ok(node3.getChildren().at(7).getType() === Node.TEXT_NODE);
			assert.ok(node3.getChildren().at(7).getContent() === '\n');
		});

		it('ElementNode.appendChild()', () => {
			const node = factory.createElementNode({
				name: 'p',
			});

			const bold = factory.createElementNode({
				name: 'b',
			});

			const italic = factory.createElementNode({
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
			const node = factory.createElementNode({
				name: 'p',
			});

			const bold = factory.createElementNode({
				name: 'b',
			});

			const italic = factory.createElementNode({
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

			const strike = factory.createElementNode({ name: 's' });
			const text = factory.createTextNode('test');
			const fragment = factory.createFragmentNode({
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
			const node = factory.createElementNode({
				name: 'p',
			});

			const bold = factory.createElementNode({
				name: 'b',
			});

			const text = factory.createTextNode('test');
			const newLine = factory.createNewLineNode();

			node.appendChild(...[bold, text, newLine]);
			assert.ok(node.getChildrenCount() === 3);
			assert.ok(node.getChildren().at(0) === bold);
			assert.ok(node.getChildren().at(1) === text);
			assert.ok(node.getChildren().at(2) === newLine);

			node.removeChild(text);
			assert.ok(text.getParent() === null);
			assert.ok(node.getChildrenCount() === 2);
			assert.ok(node.getChildren().at(0) === bold);
			assert.ok(node.getChildren().at(1) === newLine);

			node.removeChild(bold);
			assert.ok(node.getChildrenCount() === 1);
			assert.ok(bold.getParent() === null);
			assert.ok(node.getChildren().at(0) === newLine);
		});

		it('Should sets inline true automatically for inline elements', () => {
			const bold = factory.createElementNode({ name: 'b' });
			assert.ok(bold.isInline() === true);

			const italic = factory.createElementNode({ name: 'i' });
			assert.ok(italic.isInline() === true);

			const underline = factory.createElementNode({ name: 'u' });
			assert.ok(underline.isInline() === true);

			const strike = factory.createElementNode({ name: 's' });
			assert.ok(strike.isInline() === true);

			const size = factory.createElementNode({ name: 'size' });
			assert.ok(size.isInline() === true);

			const color = factory.createElementNode({ name: 'color' });
			assert.ok(color.isInline() === true);

			const left = factory.createElementNode({ name: 'left' });
			assert.ok(left.isInline() === true);

			const center = factory.createElementNode({ name: 'center' });
			assert.ok(center.isInline() === true);

			const right = factory.createElementNode({ name: 'right' });
			assert.ok(right.isInline() === true);

			const url = factory.createElementNode({ name: 'url' });
			assert.ok(url.isInline() === true);

			const img = factory.createElementNode({ name: 'img' });
			assert.ok(img.isInline() === true);
		});

		it('Should NOT sets inline true automatically for inline elements', () => {
			const p = factory.createElementNode({ name: 'p' });
			assert.ok(p.isInline() === false);

			const code = factory.createElementNode({ name: 'code' });
			assert.ok(code.isInline() === false);

			const table = factory.createElementNode({ name: 'table' });
			assert.ok(table.isInline() === false);

			const tr = factory.createElementNode({ name: 'tr' });
			assert.ok(tr.isInline() === false);

			const td = factory.createElementNode({ name: 'td' });
			assert.ok(td.isInline() === false);

			const th = factory.createElementNode({ name: 'th' });
			assert.ok(th.isInline() === false);

			const list = factory.createElementNode({ name: 'list' });
			assert.ok(list.isInline() === false);
		});

		it('Table child filter', () => {
			const table = factory.createElementNode({
				name: 'table',
				children: [
					factory.createElementNode({ name: 'p' }),
					factory.createElementNode({ name: 'td' }),
					factory.createElementNode({ name: 'th' }),
					factory.createElementNode({ name: 'tr' }),
					factory.createTextNode('test'),
					factory.createNewLineNode(),
					factory.createTabNode(),
				],
			});

			assert.ok(table.getChildrenCount() === 1);
			assert.ok(table.getChildren().at(0).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');

			table.appendChild(factory.createElementNode({ name: 'p' }));
			table.appendChild(factory.createTextNode('test'));
			table.appendChild(factory.createNewLineNode());

			assert.ok(table.getChildrenCount() === 1);
			assert.ok(table.getChildren().at(0).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');

			table.appendChild(factory.createElementNode({ name: 'tr' }));

			assert.ok(table.getChildrenCount() === 2);
			assert.ok(table.getChildren().at(0).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');
			assert.ok(table.getChildren().at(1).getType() === Node.ELEMENT_NODE);
			assert.ok(table.getChildren().at(1).getName() === 'tr');
		});

		it('Table row child filter', () => {
			const row = factory.createElementNode({
				name: 'tr',
			});
			const tr = factory.createElementNode({ name: 'tr' });
			const td = factory.createElementNode({ name: 'td' });
			const th = factory.createElementNode({ name: 'th' });
			const p = factory.createElementNode({ name: 'p' });
			const text = factory.createTextNode('test');

			row.appendChild(...[tr, td, th, p, text]);

			assert.ok(row.getChildrenCount() === 2);
			assert.ok(row.getChildren().at(0) === td);
			assert.ok(row.getChildren().at(1) === th);
		});

		it('Table cell child filter', () => {
			const td = factory.createElementNode({
				name: 'td'
			});

			const table = factory.createElementNode({ name: 'table' });
			const tr = factory.createElementNode({ name: 'tr' });
			const p = factory.createElementNode({ name: 'p' });
			const bold = factory.createElementNode({ name: 'b' });
			const strike = factory.createElementNode({ name: 's' });
			const text = factory.createTextNode('test');
			const newLine = factory.createNewLineNode();
			const tab = factory.createTabNode();

			td.appendChild(...[table, tr, p, bold, strike, text, newLine, tab]);

			assert.ok(td.getChildrenCount() === 4);
			assert.ok(td.getChildren().at(0) === bold);
			assert.ok(td.getChildren().at(1) === strike);
			assert.ok(td.getChildren().at(2) === text);
			assert.ok(td.getChildren().at(3) === newLine);
		});

		it('Code child converter', () => {
			const code = factory.createElementNode({
				name: 'code',
				children: [
					factory.createElementNode({
						name: 'b',
						children: [
							factory.createTextNode({ content: 'Test' }),
						],
					}),
				],
			});

			assert.ok(code.getChildrenCount() === 1);
			assert.ok(code.getChildren().at(0).getContent() === '[b]Test[/b]');
			assert.deepEqual(code.toString(), '[code]\n[b]Test[/b]\n[/code]');
		});

		it('Propagate unresolved nodes from constructor options', () => {
		    const rootNode = factory.createRootNode({
				children: [
					factory.createElementNode({
						name: 'p',
						value: 'p1',
						children: [
							factory.createElementNode({
								name: 'p',
								value: 'p2',
							}),
						],
					}),
				],
			});

			assert.ok(rootNode.getChildrenCount() === 2);
			assert.ok(rootNode.getFirstChild().getValue() === 'p2');
			assert.ok(rootNode.getLastChild().getValue() === 'p1');
		});

		it('Node name and attribute names must always be in lowercase', () => {
		    const p = factory.createElementNode({
				name: 'P',
				attributes: {
					ATTR1: 'UPPER',
					aTTR2: 'LOWER',
				},
				children: [
					factory.createElementNode({
						name: 'B',
					}),
				],
			});

			assert.ok(p.getName() === 'p');
			assert.ok(p.getAttribute('attr1') === 'UPPER');
			assert.ok(p.getAttribute('attr2') === 'LOWER');
			assert.deepEqual(p.getAttributes(), {attr1: 'UPPER', attr2: 'LOWER'});
			assert.ok(p.getFirstChild().getName() === 'b');
		});

		it('Must return the tag name and attribute names with lowerCase', () => {
			const localFactory = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.LOWER_CASE,
				}),
			});

			const p = localFactory.createElementNode({
				name: 'P',
				attributes: {
					attr1: 'value1',
					attr2: 'value2',
				},
			});

			assert.ok(p.toString() === '[p attr1=value1 attr2=value2][/p]');
		});

		it('Must return the tag name and attribute names with upperCase', () => {
			const localFactory = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.UPPER_CASE,
				}),
			});

			const p = localFactory.createElementNode({
				name: 'P',
				attributes: {
					attr1: 'value1',
					attr2: 'value2',
				},
			});

			assert.ok(p.toString() === '[P ATTR1=value1 ATTR2=value2][/P]');
		});

		it('getName should return tag name in lowerCase', () => {
			const localFactory1 = new ModelFactory();

			const localFactory2 = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.LOWER_CASE,
				}),
			});

			const localFactory3 = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.UPPER_CASE,
				}),
			});

			const p1 = localFactory1.createElementNode({ name: 'P' });
			const p2 = localFactory2.createElementNode({ name: 'P' });
			const p3 = localFactory3.createElementNode({ name: 'P' });

			assert.ok(p1.getName() === 'p');
			assert.ok(p2.getName() === 'p');
			assert.ok(p3.getName() === 'p');
		});

		it('getAttribute should be case-insensitive', () => {
			const localFactory1 = new ModelFactory();

			const localFactory2 = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.LOWER_CASE,
				}),
			});

			const localFactory3 = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.UPPER_CASE,
				}),
			});

			const p1 = localFactory1.createElementNode({
				name: 'p',
				attributes: {
					Attr1: 'value1',
					ATTR2: 'value2',
					attr3: 'value3',
				},
			});

			const p2 = localFactory2.createElementNode({
				name: 'p',
				attributes: {
					Attr1: 'value1',
					ATTR2: 'value2',
					attr3: 'value3',
				},
			});

			const p3 = localFactory3.createElementNode({
				name: 'p',
				attributes: {
					Attr1: 'value1',
					ATTR2: 'value2',
					attr3: 'value3',
				},
			});

			assert.ok(p1.getAttribute('attr1') === 'value1');
			assert.ok(p1.getAttribute('Attr1') === 'value1');
			assert.ok(p1.getAttribute('ATTR1') === 'value1');
			assert.ok(p1.getAttribute('attr2') === 'value2');
			assert.ok(p1.getAttribute('Attr2') === 'value2');
			assert.ok(p1.getAttribute('ATTR2') === 'value2');
			assert.ok(p1.getAttribute('attr3') === 'value3');
			assert.ok(p1.getAttribute('Attr3') === 'value3');
			assert.ok(p1.getAttribute('ATTR3') === 'value3');

			assert.ok(p2.getAttribute('attr1') === 'value1');
			assert.ok(p2.getAttribute('Attr1') === 'value1');
			assert.ok(p2.getAttribute('ATTR1') === 'value1');
			assert.ok(p2.getAttribute('attr2') === 'value2');
			assert.ok(p2.getAttribute('Attr2') === 'value2');
			assert.ok(p2.getAttribute('ATTR2') === 'value2');
			assert.ok(p2.getAttribute('attr3') === 'value3');
			assert.ok(p2.getAttribute('Attr3') === 'value3');
			assert.ok(p2.getAttribute('ATTR3') === 'value3');

			assert.ok(p3.getAttribute('attr1') === 'value1');
			assert.ok(p3.getAttribute('Attr1') === 'value1');
			assert.ok(p3.getAttribute('ATTR1') === 'value1');
			assert.ok(p3.getAttribute('attr2') === 'value2');
			assert.ok(p3.getAttribute('Attr2') === 'value2');
			assert.ok(p3.getAttribute('ATTR2') === 'value2');
			assert.ok(p3.getAttribute('attr3') === 'value3');
			assert.ok(p3.getAttribute('Attr3') === 'value3');
			assert.ok(p3.getAttribute('ATTR3') === 'value3');
		});

		it('inline element detection should be case-insensitive', () => {
			const localFactory1 = new ModelFactory();

			const localFactory2 = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.LOWER_CASE,
				}),
			});

			const localFactory3 = new ModelFactory({
				scheme: new BBCodeScheme({
					tagCase: BBCodeScheme.UPPER_CASE,
				}),
			});

			const p1Upper = localFactory1.createElementNode({ name: 'P' });
			const p1Lower = localFactory1.createElementNode({ name: 'p' });
			const b1Upper = localFactory1.createElementNode({ name: 'B' });
			const b1Lower = localFactory1.createElementNode({ name: 'b' });

			const p2Upper = localFactory2.createElementNode({ name: 'P' });
			const p2Lower = localFactory2.createElementNode({ name: 'p' });
			const b2Upper = localFactory2.createElementNode({ name: 'B' });
			const b2Lower = localFactory2.createElementNode({ name: 'b' });

			const p3Upper = localFactory3.createElementNode({ name: 'P' });
			const p3Lower = localFactory3.createElementNode({ name: 'p' });
			const b3Upper = localFactory3.createElementNode({ name: 'B' });
			const b3Lower = localFactory3.createElementNode({ name: 'b' });

			assert.ok(p1Lower.isInline() === false);
			assert.ok(p1Upper.isInline() === false);
			assert.ok(b1Lower.isInline() === true);
			assert.ok(b1Upper.isInline() === true);

			assert.ok(p2Lower.isInline() === false);
			assert.ok(p2Upper.isInline() === false);
			assert.ok(b2Lower.isInline() === true);
			assert.ok(b2Upper.isInline() === true);

			assert.ok(p3Lower.isInline() === false);
			assert.ok(p3Upper.isInline() === false);
			assert.ok(b3Lower.isInline() === true);
			assert.ok(b3Upper.isInline() === true);
		});

		describe('ElementNode.clone()', () => {
			it('Clone without options', () => {
				const sourceNode = factory.createElementNode({
					name: 'p',
					value: 'test',
					attributes: {
						x: 1,
						y: '2',
					},
					children: [
						factory.createTextNode('test text'),
						factory.createElementNode({
							name: 'b',
							children: [
								factory.createTextNode('bold'),
							],
						}),
					],
				});

				const clonedNode = sourceNode.clone();

				assert.deepEqual(clonedNode.toString(), '[p=test x=1 y=2][/p]');
			});

			it('Clone with { deep: true }', () => {
				const sourceNode = factory.createElementNode({
					name: 'p',
					value: 'test',
					attributes: {
						x: 1,
						y: '2',
					},
					children: [
						factory.createTextNode('test text'),
						factory.createElementNode({
							name: 'b',
							children: [
								factory.createTextNode('bold'),
							],
						}),
					],
				});

				const clonedNode = sourceNode.clone();

				assert.deepEqual(clonedNode.toString(), clonedNode.toString());
			});
		});

		describe('Formatting', () => {
		    it('Should add linebreak before opening tag if previews sibling is plain text', () => {
				const root = factory.createRootNode({
					children: [
						factory.createTextNode({ content: 'text' }),
						factory.createElementNode({ name: 'p' })
					],
				});

				assert.deepEqual(root.toString(), 'text\n[p][/p]');
		    });

			it('Should not add linebreak before opening tag if previews sibling is linebreak', () => {
				const root = factory.createRootNode({
					children: [
						factory.createTextNode({ content: 'text' }),
						factory.createNewLineNode(),
						factory.createElementNode({ name: 'p' })
					],
				});

				assert.deepEqual(root.toString(), 'text\n[p][/p]');
			});

			it('Should not add linebreak before opening tag if node is first child', () => {
				const root = factory.createRootNode({
					children: [
						factory.createElementNode({ name: 'p' }),
						factory.createTextNode({ content: 'text' }),
						factory.createNewLineNode(),
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\ntext\n');
			});

			it('Should add linebreak before opening tag if previews sibling is inline', () => {
				const root = factory.createRootNode({
					children: [
						factory.createElementNode({ name: 'b' }),
						factory.createElementNode({ name: 'p' }),
					],
				});

				assert.deepEqual(root.toString(), '[b][/b]\n[p][/p]');
			});

			it('Should add linebreak after closing tag if new sibling is plain text', () => {
				const root = factory.createRootNode({
					children: [
						factory.createElementNode({ name: 'p' }),
						factory.createTextNode({ content: 'text' }),
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\ntext');
			});

			it('Should add linebreak after closing tag if new sibling is inline', () => {
				const root = factory.createRootNode({
					children: [
						factory.createElementNode({ name: 'p' }),
						factory.createElementNode({ name: 'b' }),
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\n[b][/b]');
			});

			it('Should not add linebreak after closing tag if new sibling is linebreak', () => {
				const root = factory.createRootNode({
					children: [
						factory.createElementNode({ name: 'p' }),
						factory.createNewLineNode()
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\n');
			});

			it('Should not add linebreak after closing tag if node is last child', () => {
				const root = factory.createRootNode({
					children: [
						factory.createTextNode({ content: 'text' }),
						factory.createElementNode({ name: 'p' }),
					],
				});

				assert.deepEqual(root.toString(), 'text\n[p][/p]');
			});
		});
	});
});
