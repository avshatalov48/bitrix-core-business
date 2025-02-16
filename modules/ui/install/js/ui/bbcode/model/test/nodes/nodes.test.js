import { BBCodeNode } from '../../src/nodes/node';
import { BBCodeScheme } from '../../src/scheme/bbcode-scheme';
import { BBCodeTagScheme } from '../../src/scheme/node-schemes/tag-scheme';
import { DefaultBBCodeScheme } from '../../src/scheme/default-bbcode-scheme';

describe('ui.bbcode.model/nodes', () => {
	let scheme;

	beforeEach(() => {
		scheme = new DefaultBBCodeScheme();
	});

	describe('TextNode', () => {
		it('Create TextNode with options object', () => {
			const parent = scheme.createNode({ name: 'p' });
			const node = scheme.createText({
				content: 'test text',
				name: '1111',
				parent,
			});

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === 'test text');
			assert.ok(node.toString() === 'test text');
			assert.ok(node.getParent() === parent);
			assert.ok(node.getName() === '#text');
		});

		it('Create TextNode with options string', () => {
			const node = scheme.createText('test text');

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === 'test text');
			assert.ok(node.toString() === 'test text');
		});

		it('Should return decoded content', () => {
			const node = scheme.createText('&#91;text&#93;');

			assert.ok(node.getContent() === '&#91;text&#93;');
			assert.ok(node.toString() === '&#91;text&#93;');
		});

		it('TextNode.setParent()', () => {
			const node = scheme.createText();
			const parent1 = scheme.createNode({ name: 'p' });
			const parent2 = scheme.createNode({ name: 'p' });

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('TextNode.toJSON()', () => {
			const node = scheme.createText('test text');

			assert.deepEqual(node.toJSON(), {content: 'test text', name: '#text'});
		});

		it('TextNode.setName()', () => {
			const node = scheme.createText();

			assert.ok(node.getName() === '#text');

			node.setName('11111');

			assert.ok(node.getName() === '#text');
		});

		it('TextNode.clone()', () => {
			const sourceNode = scheme.createText('test text');
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});

		describe('TextNode.split()', () => {
			it('should throws if passed offset less than 0', () => {
				const textNode = scheme.createText('test text');

				assert.throws(() => {
					textNode.split({ offset: -1});
				});

				assert.throws(() => {
					textNode.split({ offset: -20 });
				});
			});

			it('should throws if passed offset more than text length', () => {
				const textNode = scheme.createText('test text');

				assert.throws(() => {
					textNode.split({ offset: 10 });
				});

				assert.throws(() => {
					textNode.split({ offset: 20 });
				});
			});

			it('should returns null for left node if passed 0 offset', () => {
				const textNode = scheme.createText('test text');
				const [leftNode, rightNode] = textNode.split({ offset: 0 });

				assert.ok(leftNode === null);
				assert.ok(rightNode.getName() === '#text');
			});

			it('should returns null for right node if offset equal text length', () => {
				const textNode = scheme.createText('test text');
				const [leftNode, rightNode] = textNode.split({ offset: 9 });

				assert.ok(leftNode.getName() === '#text');
				assert.ok(rightNode === null);
			});

			it('should split node if passed offset in text range', () => {
				const textNode = scheme.createText('test text');

				const [leftNode1, rightNode1] = textNode.split({ offset: 1 });
				assert.ok(leftNode1.getContent() === 't');
				assert.ok(rightNode1.getContent() === 'est text');

				const [leftNode2, rightNode2] = textNode.split({ offset: 2 });
				assert.ok(leftNode2.getContent() === 'te');
				assert.ok(rightNode2.getContent() === 'st text');

				const [leftNode3, rightNode3] = textNode.split({ offset: 3 });
				assert.ok(leftNode3.getContent() === 'tes');
				assert.ok(rightNode3.getContent() === 't text');

				const [leftNode4, rightNode4] = textNode.split({ offset: 4 });
				assert.ok(leftNode4.getContent() === 'test');
				assert.ok(rightNode4.getContent() === ' text');

				const [leftNode5, rightNode5] = textNode.split({ offset: 5 });
				assert.ok(leftNode5.getContent() === 'test ');
				assert.ok(rightNode5.getContent() === 'text');

				const [leftNode6, rightNode6] = textNode.split({ offset: 6 });
				assert.ok(leftNode6.getContent() === 'test t');
				assert.ok(rightNode6.getContent() === 'ext');

				const [leftNode7, rightNode7] = textNode.split({ offset: 7 });
				assert.ok(leftNode7.getContent() === 'test te');
				assert.ok(rightNode7.getContent() === 'xt');

				const [leftNode8, rightNode8] = textNode.split({ offset: 8 });
				assert.ok(leftNode8.getContent() === 'test tex');
				assert.ok(rightNode8.getContent() === 't');
			});

			it('should returns this node as left node if offset is equal content length', () => {
				const textNode = scheme.createText('test text');
				const [leftNode, rightNode] = textNode.split({ offset: 9 });

				assert.ok(leftNode === textNode);
				assert.ok(rightNode === null);
			});

			it('should returns this node as right node if offset is equal 0', () => {
				const textNode = scheme.createText('test text');
				const [leftNode, rightNode] = textNode.split({ offset: 0 });

				assert.ok(leftNode === null);
				assert.ok(rightNode === textNode);
			});
		});
	});

	describe('NewLineNode', () => {
		it('Create NewLineNode without options', () => {
			const node = scheme.createNewLine();

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('Create NewLineNode with options object', () => {
			const parent = scheme.createNode({ name: 'p' });
			const node = scheme.createNewLine({
				content: '1111',
				name: '11111',
				parent,
			});

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
			assert.ok(node.getParent() === parent);
			assert.ok(node.getName() === '#linebreak');
		});

		it('Create NewLineNode with options string', () => {
			const node = scheme.createNewLine('1111');

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('NewLineNode.setContent() do not affected content property', () => {
			const node = scheme.createNewLine();

			node.setContent('1111111');

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\n');
			assert.ok(node.toString() === '\n');
		});

		it('NewLineNode.setParent()', () => {
			const node = scheme.createNewLine();
			const parent1 = scheme.createNode({ name: 'p' });
			const parent2 = scheme.createNode({ name: 'p' });

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('NewLineNode.toJSON()', () => {
			const node = scheme.createNewLine();

			assert.deepEqual(node.toJSON(), {content: '\n', name: '#linebreak'});
		});

		it('NewLineNode.setName()', () => {
			const node = scheme.createNewLine();

			assert.ok(node.getName() === '#linebreak');

			node.setName('111111');

			assert.ok(node.getName() === '#linebreak');
		});

		it('NewLineNode.setContent()', () => {
			const node = scheme.createNewLine();

			assert.ok(node.getContent() === '\n');

			node.setContent('111111');

			assert.ok(node.getContent() === '\n');
		});

		it('NewLineNode.clone()', () => {
		    const sourceNode = scheme.createNewLine();
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});
	});

	describe('TabNode', () => {
		it('Create TabNode without options', () => {
			const node = scheme.createTab();

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('Create TabNode with options object', () => {
			const parent = scheme.createNode({ name: 'p' });
			const node = scheme.createTab({ content: '1111', parent });

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
			assert.ok(node.getParent() === parent);
		});

		it('Create TabNode with options string', () => {
			const node = scheme.createTab('1111');

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('TabNode.setContent() do not affected content property', () => {
			const node = scheme.createTab();

			node.setContent('1111111');

			assert.ok(node.getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node.getContent() === '\t');
			assert.ok(node.toString() === '\t');
		});

		it('TabNode.setParent()', () => {
			const node = scheme.createTab();
			const parent1 = scheme.createNode({ name: 'p' });
			const parent2 = scheme.createNode({ name: 'p' });

			assert.ok(node.getParent() === null);

			node.setParent(parent1);
			assert.ok(node.getParent() === parent1);

			node.setParent(parent2);
			assert.ok(node.getParent() === parent2);

			node.setParent(null);
			assert.ok(node.getParent() === null);
		});

		it('TabNode.toJSON()', () => {
			const node = scheme.createTab();

			assert.deepEqual(node.toJSON(), {content: '\t', name: '#tab'});
		});

		it('TabNode.setName()', () => {
			const node = scheme.createTab();

			assert.ok(node.getName() === '#tab');

			node.setName('111111');

			assert.ok(node.getName() === '#tab');
		});

		it('TabNode.setContent()', () => {
			const node = scheme.createTab();

			assert.ok(node.getContent() === '\t');

			node.setContent('111111');

			assert.ok(node.getContent() === '\t');
		});

		it('TabNode.clone()', () => {
			const sourceNode = scheme.createTab();
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});
	});

	describe('FragmentNode', () => {
		it('Create FragmentNode with options', () => {
			const node = scheme.createFragment({
				children: [
					scheme.createText('text'),
					scheme.createFragment({
						children: [
							scheme.createText('text2'),
						],
					})
				],
			});

			assert.ok(node.getType() === BBCodeNode.FRAGMENT_NODE);
			assert.ok(node.getChildrenCount() === 2);
			assert.ok(node.getChildren().at(0).getContent() === 'text');
			assert.ok(node.getChildren().at(1).getContent() === 'text2');
		});

		it('FragmentNode.clone()', () => {
			const sourceNode = scheme.createFragment();
			const clonedNode = sourceNode.clone();

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
		});

		it('FragmentNode.clone({ deep: true })', () => {
			const sourceNode = scheme.createFragment({
				children: [
					scheme.createElement({
						name: 'b',
						children: [
							scheme.createText('test text'),
						],
					}),
				],
			});
			const clonedNode = sourceNode.clone({ deep: true });

			assert.deepEqual(sourceNode.toString(), clonedNode.toString());
			assert.ok(sourceNode.getScheme() === clonedNode.getScheme());
		});

		it('Must be able to include any elements', () => {
		    const fragment = scheme.createFragment();

			const p = scheme.createElement({ name: 'p' });
			const b = scheme.createElement({ name: 'b' });
			const table = scheme.createElement({ name: 'table' });
			const td = scheme.createElement({ name: 'td' });
			const spoiler = scheme.createElement({ name: 'spoiler' });

			const newline = scheme.createNewLine();
			const tab = scheme.createTab();

			fragment.appendChild(p, b, table, td, spoiler, newline, tab);

			assert.ok(fragment.getChildren().includes(p));
			assert.ok(fragment.getChildren().includes(b));
			assert.ok(fragment.getChildren().includes(table));
			assert.ok(fragment.getChildren().includes(td));
			assert.ok(fragment.getChildren().includes(spoiler));
			assert.ok(fragment.getChildren().includes(newline));
			assert.ok(fragment.getChildren().includes(tab));
		});
	});

	describe('ElementNode', () => {
		it('Create ElementNode with options object', () => {
			const node = scheme.createElement({
				name: 'p',
			});

			assert.ok(node.getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(node.getName() === 'p');
			assert.ok(node.getValue() === '');
			assert.ok(node.isVoid() === false);
			assert.deepEqual(node.getAttributes(), {});
			assert.ok(node.getChildrenCount() === 0);
			assert.deepEqual(node.getChildren(), []);
			assert.ok(node.hasChildren() === false);

			const node2 = scheme.createElement({
				name: 'disk',
				value: 'test',
				attributes: {
					key1: 'value1',
					key2: true,
					key3: 33,
				},
			});

			assert.ok(node2.getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(node2.getName() === 'disk');
			assert.ok(node2.getValue() === 'test');
			assert.ok(node2.isVoid() === true);
			assert.deepEqual(node2.getAttributes(), { key1: 'value1', key2: true, key3: 33 });

			const node3 = scheme.createElement({
				name: 'p',
				children: [
					scheme.createText('test'),
					scheme.createElement({
						name: 'b',
						children: [
							scheme.createText('bold'),
						],
					}),
					scheme.createNewLine(),
					scheme.createNewLine(),
					scheme.createElement({
						name: 'i',
					}),
					scheme.createFragment({
						children: [
							scheme.createElement({
								name: 'b',
								value: 'fragment1',
							}),
							scheme.createText('fragment2'),
							scheme.createNewLine(),
							scheme.createTab(),
						],
					}),
				],
			});

			assert.ok(node3.getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(node3.getName() === 'p');
			assert.ok(node3.getChildrenCount() === 8);
			assert.ok(node3.hasChildren() === true);
			assert.ok(node3.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node3.getChildren().at(0).getContent() === 'test');
			assert.ok(node3.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(node3.getChildren().at(1).getName() === 'b');
			assert.ok(node3.getChildren().at(1).getChildrenCount() === 1);
			assert.ok(node3.getChildren().at(1).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node3.getChildren().at(1).getChildren().at(0).getContent() === 'bold');
			assert.ok(node3.getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node3.getChildren().at(2).getContent() === '\n');
			assert.ok(node3.getChildren().at(3).getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node3.getChildren().at(3).getContent() === '\n');
			assert.ok(node3.getChildren().at(4).getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(node3.getChildren().at(4).getName() === 'i');
			assert.ok(node3.getChildren().at(5).getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(node3.getChildren().at(5).getName() === 'b');
			assert.ok(node3.getChildren().at(5).getValue() === 'fragment1');
			assert.ok(node3.getChildren().at(6).getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node3.getChildren().at(6).getContent() === 'fragment2');
			assert.ok(node3.getChildren().at(7).getType() === BBCodeNode.TEXT_NODE);
			assert.ok(node3.getChildren().at(7).getContent() === '\n');
		});

		it('ElementNode.appendChild()', () => {
			const node = scheme.createElement({
				name: 'p',
			});

			const bold = scheme.createElement({
				name: 'b',
			});

			const italic = scheme.createElement({
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
			const node = scheme.createElement({
				name: 'p',
			});

			const bold = scheme.createElement({
				name: 'b',
			});

			const italic = scheme.createElement({
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

			const strike = scheme.createElement({ name: 's' });
			const text = scheme.createText('test');
			const fragment = scheme.createFragment({
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
			const node = scheme.createElement({
				name: 'p',
			});

			const bold = scheme.createElement({
				name: 'b',
			});

			const text = scheme.createText('test');
			const newLine = scheme.createNewLine();

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

		it('Table child filter', () => {
			const table = scheme.createElement({
				name: 'table',
				children: [
					scheme.createElement({ name: 'p' }),
					scheme.createElement({ name: 'td' }),
					scheme.createElement({ name: 'th' }),
					scheme.createElement({ name: 'tr' }),
					scheme.createText('test'),
					scheme.createNewLine(),
					scheme.createTab(),
				],
			});

			assert.ok(table.getChildrenCount() === 1);
			assert.ok(table.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');

			table.appendChild(scheme.createElement({ name: 'p' }));
			table.appendChild(scheme.createText('test'));
			table.appendChild(scheme.createNewLine());

			assert.ok(table.getChildrenCount() === 1);
			assert.ok(table.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');

			table.appendChild(scheme.createElement({ name: 'tr' }));

			assert.ok(table.getChildrenCount() === 2);
			assert.ok(table.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(table.getChildren().at(0).getName() === 'tr');
			assert.ok(table.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
			assert.ok(table.getChildren().at(1).getName() === 'tr');
		});

		it('Table row child filter', () => {
			const row = scheme.createElement({
				name: 'tr',
			});
			const tr = scheme.createElement({ name: 'tr' });
			const td = scheme.createElement({ name: 'td' });
			const th = scheme.createElement({ name: 'th' });
			const p = scheme.createElement({ name: 'p' });
			const text = scheme.createText('test');

			row.appendChild(...[tr, td, th, p, text]);

			assert.ok(row.getChildrenCount() === 2);
			assert.ok(row.getChildren().at(0) === td);
			assert.ok(row.getChildren().at(1) === th);
		});

		xit('Table cell child filter', () => {
			const td = scheme.createElement({
				name: 'td',
			});

			const table = scheme.createElement({ name: 'table' });
			const tr = scheme.createElement({ name: 'tr' });
			const p = scheme.createElement({ name: 'p' });
			const bold = scheme.createElement({ name: 'b' });
			const strike = scheme.createElement({ name: 's' });
			const text = scheme.createText('test');
			const newLine = scheme.createNewLine();
			const tab = scheme.createTab();

			td.appendChild(...[table, tr, p, bold, strike, text, newLine, tab]);

			assert.ok(td.getChildrenCount() === 4);
			assert.ok(td.getChildren().at(0) === bold);
			assert.ok(td.getChildren().at(1) === strike);
			assert.ok(td.getChildren().at(2) === text);
			assert.ok(td.getChildren().at(3) === newLine);
		});

		it('Propagate unresolved nodes from constructor options', () => {
		    const rootNode = scheme.createRoot({
				children: [
					scheme.createElement({
						name: 'p',
						value: 'p1',
						children: [
							scheme.createElement({
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
		    const p = scheme.createElement({
				name: 'P',
				attributes: {
					ATTR1: 'UPPER',
					aTTR2: 'LOWER',
				},
				children: [
					scheme.createElement({
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
			const localFactory =new BBCodeScheme({
				tagSchemes: [
					new BBCodeTagScheme({
						name: 'p',
					}),
				],
				tagCase: BBCodeScheme.Case.LOWER,
			});

			const p = localFactory.createElement({
				name: 'P',
				attributes: {
					attr1: 'value1',
					attr2: 'value2',
				},
			});

			assert.ok(p.toString() === '[p attr1=value1 attr2=value2][/p]');
		});

		it('Must return the tag name and attribute names with upperCase', () => {
			const localScheme = new BBCodeScheme({
				tagSchemes: [
					new BBCodeTagScheme({
						name: 'p',
					}),
				],
				outputTagCase: BBCodeScheme.Case.UPPER,
			});

			const p = localScheme.createElement({
				name: 'P',
				attributes: {
					attr1: 'value1',
					attr2: 'value2',
				},
			});

			assert.ok(p.toString() === '[P ATTR1=value1 ATTR2=value2][/P]');
		});

		it('getName should return tag name in lowerCase', () => {
			const tagSchemes = [
				new BBCodeTagScheme({
					name: 'p',
				}),
			];

			const localScheme1 = new BBCodeScheme({
				tagSchemes,
			});

			const localScheme2 = new BBCodeScheme({
				tagSchemes,
				tagCase: BBCodeScheme.Case.LOWER,
			});

			const localScheme3 = new BBCodeScheme({
				tagSchemes,
				tagCase: BBCodeScheme.Case.UPPER,
			});

			const p1 = localScheme1.createElement({ name: 'P' });
			const p2 = localScheme2.createElement({ name: 'P' });
			const p3 = localScheme3.createElement({ name: 'P' });

			assert.ok(p1.getName() === 'p');
			assert.ok(p2.getName() === 'p');
			assert.ok(p3.getName() === 'p');
		});

		it('getAttribute should be case-insensitive', () => {
			const tagSchemes = [
				new BBCodeTagScheme({
					name: 'p',
				}),
			];

			const localScheme = new BBCodeScheme({
				tagSchemes,
			});

			const localScheme2 = new BBCodeScheme({
				tagSchemes,
				tagCase: BBCodeScheme.Case.LOWER,
			});

			const localScheme3 = new BBCodeScheme({
				tagSchemes,
				tagCase: BBCodeScheme.Case.UPPER,
			});

			const p1 = localScheme.createElement({
				name: 'p',
				attributes: {
					Attr1: 'value1',
					ATTR2: 'value2',
					attr3: 'value3',
				},
			});

			const p2 = localScheme2.createElement({
				name: 'p',
				attributes: {
					Attr1: 'value1',
					ATTR2: 'value2',
					attr3: 'value3',
				},
			});

			const p3 = localScheme3.createElement({
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

		describe('ElementNode.clone()', () => {
			it('Clone without options', () => {
				const sourceNode = scheme.createElement({
					name: 'p',
					value: 'test',
					attributes: {
						x: 1,
						y: '2',
					},
					children: [
						scheme.createText('test text'),
						scheme.createElement({
							name: 'b',
							children: [
								scheme.createText('bold'),
							],
						}),
					],
				});

				const clonedNode = sourceNode.clone();

				assert.deepEqual(clonedNode.toString(), '[p=test x=1 y=2][/p]');
			});

			it('Clone with { deep: true }', () => {
				const sourceNode = scheme.createElement({
					name: 'p',
					value: 'test',
					attributes: {
						x: 1,
						y: '2',
					},
					children: [
						scheme.createText('test text'),
						scheme.createElement({
							name: 'b',
							children: [
								scheme.createText('bold'),
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
				const root = scheme.createRoot({
					children: [
						scheme.createText({ content: 'text' }),
						scheme.createElement({ name: 'p' }),
					],
				});

				assert.deepEqual(root.toString(), 'text\n[p][/p]');
		    });

			it('Should not add linebreak before opening tag if previews sibling is linebreak', () => {
				const root = scheme.createRoot({
					children: [
						scheme.createText({ content: 'text' }),
						scheme.createNewLine(),
						scheme.createElement({ name: 'p' }),
					],
				});

				assert.deepEqual(root.toString(), 'text\n[p][/p]');
			});

			it('Should not add linebreak before opening tag if node is first child', () => {
				const root = scheme.createRoot({
					children: [
						scheme.createElement({ name: 'p' }),
						scheme.createText({ content: 'text' }),
						scheme.createNewLine(),
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\ntext\n');
			});

			it('Should add linebreak before opening tag if previews sibling is inline', () => {
				const root = scheme.createRoot({
					children: [
						scheme.createElement({ name: 'b' }),
						scheme.createElement({ name: 'p' }),
					],
				});

				assert.deepEqual(root.toString(), '[b][/b]\n[p][/p]');
			});

			it('Should add linebreak after closing tag if new sibling is plain text', () => {
				const root = scheme.createRoot({
					children: [
						scheme.createElement({ name: 'p' }),
						scheme.createText({ content: 'text' }),
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\ntext');
			});

			it('Should add linebreak after closing tag if new sibling is inline', () => {
				const root = scheme.createRoot({
					children: [
						scheme.createElement({ name: 'p' }),
						scheme.createElement({ name: 'b' }),
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\n[b][/b]');
			});

			it('Should not add linebreak after closing tag if new sibling is linebreak', () => {
				const root = scheme.createRoot({
					children: [
						scheme.createElement({ name: 'p' }),
						scheme.createNewLine(),
					],
				});

				assert.deepEqual(root.toString(), '[p][/p]\n');
			});

			it('Should not add linebreak after closing tag if node is last child', () => {
				const root = scheme.createRoot({
					children: [
						scheme.createText({ content: 'text' }),
						scheme.createElement({ name: 'p' }),
					],
				});

				assert.deepEqual(root.toString(), 'text\n[p][/p]');
			});
		});

		describe('ElementNode.splitByChildIndex()', () => {
		    it('should return left and right node if passed index', () => {
		        const node = scheme.createElement({
					name: 'p',
					children: [
						scheme.createElement({
							name: 'b',
							children: [
								scheme.createText('bold'),
							],
						}),
						scheme.createElement({
							name: 'i',
							children: [
								scheme.createText('italic'),
							],
						}),
						scheme.createElement({
							name: 's',
							children: [
								scheme.createText('strike'),
							],
						}),
					],
				});

				const [leftNode, rightNode] = node.splitByChildIndex(1);

				assert.deepEqual(leftNode.toString(), '[p]\n[b]bold[/b]\n[/p]');
				assert.deepEqual(rightNode.toString(), '[p]\n[i]italic[/i][s]strike[/s]\n[/p]');
		    });

			it('should replaces this node with left and right nodes', () => {
				const node = scheme.createElement({
					name: 'p',
					children: [
						scheme.createElement({
							name: 'b',
							children: [
								scheme.createText('bold'),
							],
						}),
						scheme.createElement({
							name: 'i',
							children: [
								scheme.createText('italic'),
							],
						}),
						scheme.createElement({
							name: 's',
							children: [
								scheme.createText('strike'),
							],
						}),
					],
				});

				const rootNode = scheme.createRoot({
					children: [
						node,
					],
				});

				const [leftNode, rightNode] = node.splitByChildIndex(1);

				assert.ok(rootNode.getChildrenCount() === 2);
				assert.ok(rootNode.getFirstChild() === leftNode);
				assert.ok(rootNode.getLastChild() === rightNode);
			});

			it('should throws if passed index more than children count', () => {
			    const node = scheme.createElement({
					name: 'p',
					children: [
						scheme.createText('test text'),
					],
				});

				assert.throws(() => {
					node.splitByChildIndex(2);
				});

				assert.throws(() => {
					node.splitByChildIndex(99);
				});
			});

			it('should throws if passed index less than 0', () => {
				const node = scheme.createElement({
					name: 'p',
					children: [
						scheme.createText('test text'),
					],
				});

				assert.throws(() => {
					node.splitByChildIndex(-1);
				});

				assert.throws(() => {
					node.splitByChildIndex(-22);
				});
			});
		});

		describe('ElementNode.toPlainText()', () => {
			it('should returns plain text', () => {
				scheme.setTagScheme(
					new BBCodeTagScheme({
						name: 'div',
					}),
				);

				const node = scheme.createElement({
					name: 'div',
					children: [
						scheme.createElement({
							name: 'b',
							children: [
								scheme.createText('bold'),
							],
						}),
						scheme.createElement({
							name: 'i',
							children: [
								scheme.createText(' italic'),
							],
						}),
						scheme.createElement({
							name: 's',
							children: [
								scheme.createText(' strike'),
							],
						}),
						scheme.createElement({
							name: 'div',
							children: [
								scheme.createText(' p1'),
								scheme.createText(' p2'),
								scheme.createElement({
									name: 'div',
									children: [
										scheme.createText(' pp1'),
										scheme.createText(' pp2'),
									],
								}),
							],
						}),
					],
				});

				assert.equal(node.toPlainText(), 'bold italic strike p1 p2 pp1 pp2');
			});
		});

		describe('Node.split()', () => {
		    it('Should split tree into left and right parts by index', () => {
				const localScheme = new BBCodeScheme({
					tagSchemes: [
						new BBCodeTagScheme({
							name: ['b', 'i', 's'],
						}),
						new BBCodeTagScheme({
							name: ['div1', 'div2', 'div3'],
							stringify: BBCodeTagScheme.defaultBlockStringifier,
						}),
						new BBCodeTagScheme({
							name: '#text',
						}),
					],
				});

				const node = localScheme.createElement({
					name: 'div1',
					children: [
						localScheme.createElement({
							name: 'b',
							children: [
								localScheme.createText('bold'),
							],
						}),
						localScheme.createElement({
							name: 'i',
							children: [
								localScheme.createText(' italic'),
							],
						}),
						localScheme.createElement({
							name: 's',
							children: [
								localScheme.createText(' strike'),
							],
						}),
						localScheme.createElement({
							name: 'div2',
							children: [
								localScheme.createText(' p1'),
								localScheme.createText(' p2'),
								localScheme.createElement({
									name: 'div3',
									children: [
										localScheme.createText(' pp1'),
										localScheme.createText(' pp2'),
									],
								}),
							],
						}),
					],
				});

				const [left0, right0] = node.split({ offset: 0 });
				assert.ok(left0 === null);
				assert.deepEqual(right0, node);

				const [left1, right1] = node.split({ offset: 1 });
				assert.deepEqual(left1.toString(), '[div1]\n[b]b[/b]\n[/div1]');
				assert.deepEqual(right1.toString(), '[div1]\n[b]old[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left1.toPlainText(), 'b');
				assert.deepEqual(right1.toPlainText(), 'old italic strike p1 p2 pp1 pp2');

				const [left2, right2] = node.split({ offset: 2 });
				assert.deepEqual(left2.toString(), '[div1]\n[b]bo[/b]\n[/div1]');
				assert.deepEqual(right2.toString(), '[div1]\n[b]ld[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left2.toPlainText(), 'bo');
				assert.deepEqual(right2.toPlainText(), 'ld italic strike p1 p2 pp1 pp2');

				const [left3, right3] = node.split({ offset: 3 });
				assert.deepEqual(left3.toString(), '[div1]\n[b]bol[/b]\n[/div1]');
				assert.deepEqual(right3.toString(), '[div1]\n[b]d[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left3.toPlainText(), 'bol');
				assert.deepEqual(right3.toPlainText(), 'd italic strike p1 p2 pp1 pp2');

				const [left4, right4] = node.split({ offset: 4 });
				assert.deepEqual(left4.toString(), '[div1]\n[b]bold[/b]\n[/div1]');
				assert.deepEqual(right4.toString(), '[div1]\n[i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left4.toPlainText(), 'bold');
				assert.deepEqual(right4.toPlainText(), ' italic strike p1 p2 pp1 pp2');

				const [left5, right5] = node.split({ offset: 5 });
				assert.deepEqual(left5.toString(), '[div1]\n[b]bold[/b][i] [/i]\n[/div1]');
				assert.deepEqual(right5.toString(), '[div1]\n[i]italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left5.toPlainText(), 'bold ');
				assert.deepEqual(right5.toPlainText(), 'italic strike p1 p2 pp1 pp2');

				const [left6, right6] = node.split({ offset: 6 });
				assert.deepEqual(left6.toString(), '[div1]\n[b]bold[/b][i] i[/i]\n[/div1]');
				assert.deepEqual(right6.toString(), '[div1]\n[i]talic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left6.toPlainText(), 'bold i');
				assert.deepEqual(right6.toPlainText(), 'talic strike p1 p2 pp1 pp2');

				const [left7, right7] = node.split({ offset: 7 });
				assert.deepEqual(left7.toString(), '[div1]\n[b]bold[/b][i] it[/i]\n[/div1]');
				assert.deepEqual(right7.toString(), '[div1]\n[i]alic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left7.toPlainText(), 'bold it');
				assert.deepEqual(right7.toPlainText(), 'alic strike p1 p2 pp1 pp2');

				const [left8, right8] = node.split({ offset: 8 });
				assert.deepEqual(left8.toString(), '[div1]\n[b]bold[/b][i] ita[/i]\n[/div1]');
				assert.deepEqual(right8.toString(), '[div1]\n[i]lic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left8.toPlainText(), 'bold ita');
				assert.deepEqual(right8.toPlainText(), 'lic strike p1 p2 pp1 pp2');

				const [left9, right9] = node.split({ offset: 9 });
				assert.deepEqual(left9.toString(), '[div1]\n[b]bold[/b][i] ital[/i]\n[/div1]');
				assert.deepEqual(right9.toString(), '[div1]\n[i]ic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left9.toPlainText(), 'bold ital');
				assert.deepEqual(right9.toPlainText(), 'ic strike p1 p2 pp1 pp2');

				const [left10, right10] = node.split({ offset: 10 });
				assert.deepEqual(left10.toString(), '[div1]\n[b]bold[/b][i] itali[/i]\n[/div1]');
				assert.deepEqual(right10.toString(), '[div1]\n[i]c[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left10.toPlainText(), 'bold itali');
				assert.deepEqual(right10.toPlainText(), 'c strike p1 p2 pp1 pp2');

				const [left11, right11] = node.split({ offset: 11 });
				assert.deepEqual(left11.toString(), '[div1]\n[b]bold[/b][i] italic[/i]\n[/div1]');
				assert.deepEqual(right11.toString(), '[div1]\n[s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left11.toPlainText(), 'bold italic');
				assert.deepEqual(right11.toPlainText(), ' strike p1 p2 pp1 pp2');

				const [left12, right12] = node.split({ offset: 12 });
				assert.deepEqual(left12.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] [/s]\n[/div1]');
				assert.deepEqual(right12.toString(), '[div1]\n[s]strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left12.toPlainText(), 'bold italic ');
				assert.deepEqual(right12.toPlainText(), 'strike p1 p2 pp1 pp2');

				const [left13, right13] = node.split({ offset: 13 });
				assert.deepEqual(left13.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] s[/s]\n[/div1]');
				assert.deepEqual(right13.toString(), '[div1]\n[s]trike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left13.toPlainText(), 'bold italic s');
				assert.deepEqual(right13.toPlainText(), 'trike p1 p2 pp1 pp2');

				const [left14, right14] = node.split({ offset: 14 });
				assert.deepEqual(left14.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] st[/s]\n[/div1]');
				assert.deepEqual(right14.toString(), '[div1]\n[s]rike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left14.toPlainText(), 'bold italic st');
				assert.deepEqual(right14.toPlainText(), 'rike p1 p2 pp1 pp2');

				const [left15, right15] = node.split({ offset: 15 });
				assert.deepEqual(left15.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] str[/s]\n[/div1]');
				assert.deepEqual(right15.toString(), '[div1]\n[s]ike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left15.toPlainText(), 'bold italic str');
				assert.deepEqual(right15.toPlainText(), 'ike p1 p2 pp1 pp2');

				const [left16, right16] = node.split({ offset: 16 });
				assert.deepEqual(left16.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] stri[/s]\n[/div1]');
				assert.deepEqual(right16.toString(), '[div1]\n[s]ke[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left16.toPlainText(), 'bold italic stri');
				assert.deepEqual(right16.toPlainText(), 'ke p1 p2 pp1 pp2');

				const [left17, right17] = node.split({ offset: 17 });
				assert.deepEqual(left17.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strik[/s]\n[/div1]');
				assert.deepEqual(right17.toString(), '[div1]\n[s]e[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left17.toPlainText(), 'bold italic strik');
				assert.deepEqual(right17.toPlainText(), 'e p1 p2 pp1 pp2');

				const [left18, right18] = node.split({ offset: 18 });
				assert.deepEqual(left18.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[/div1]');
				assert.deepEqual(right18.toString(), '[div1]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left18.toPlainText(), 'bold italic strike');
				assert.deepEqual(right18.toPlainText(), ' p1 p2 pp1 pp2');

				const [left19, right19] = node.split({ offset: 19 });
				assert.deepEqual(left19.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n \n[/div2]\n[/div1]');
				assert.deepEqual(right19.toString(), '[div1]\n[div2]\np1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left19.toPlainText(), 'bold italic strike ');
				assert.deepEqual(right19.toPlainText(), 'p1 p2 pp1 pp2');

				const [left20, right20] = node.split({ offset: 20 });
				assert.deepEqual(left20.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p\n[/div2]\n[/div1]');
				assert.deepEqual(right20.toString(), '[div1]\n[div2]\n1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left20.toPlainText(), 'bold italic strike p');
				assert.deepEqual(right20.toPlainText(), '1 p2 pp1 pp2');

				const [left21, right21] = node.split({ offset: 21 });
				assert.deepEqual(left21.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1\n[/div2]\n[/div1]');
				assert.deepEqual(right21.toString(), '[div1]\n[div2]\n p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left21.toPlainText(), 'bold italic strike p1');
				assert.deepEqual(right21.toPlainText(), ' p2 pp1 pp2');

				const [left22, right22] = node.split({ offset: 22 });
				assert.deepEqual(left22.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 \n[/div2]\n[/div1]');
				assert.deepEqual(right22.toString(), '[div1]\n[div2]\np2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left22.toPlainText(), 'bold italic strike p1 ');
				assert.deepEqual(right22.toPlainText(), 'p2 pp1 pp2');

				const [left23, right23] = node.split({ offset: 23 });
				assert.deepEqual(left23.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p\n[/div2]\n[/div1]');
				assert.deepEqual(right23.toString(), '[div1]\n[div2]\n2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left23.toPlainText(), 'bold italic strike p1 p');
				assert.deepEqual(right23.toPlainText(), '2 pp1 pp2');

				const [left24, right24] = node.split({ offset: 24 });
				assert.deepEqual(left24.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[/div2]\n[/div1]');
				assert.deepEqual(right24.toString(), '[div1]\n[div2]\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left24.toPlainText(), 'bold italic strike p1 p2');
				assert.deepEqual(right24.toPlainText(), ' pp1 pp2');

				const [left25, right25] = node.split({ offset: 25 });
				assert.deepEqual(left25.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n \n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(right25.toString(), '[div1]\n[div2]\n[div3]\npp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left25.toPlainText(), 'bold italic strike p1 p2 ');
				assert.deepEqual(right25.toPlainText(), 'pp1 pp2');

				const [left26, right26] = node.split({ offset: 26 });
				assert.deepEqual(left26.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n p\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(right26.toString(), '[div1]\n[div2]\n[div3]\np1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left26.toPlainText(), 'bold italic strike p1 p2 p');
				assert.deepEqual(right26.toPlainText(), 'p1 pp2');

				const [left27, right27] = node.split({ offset: 27 });
				assert.deepEqual(left27.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(right27.toString(), '[div1]\n[div2]\n[div3]\n1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left27.toPlainText(), 'bold italic strike p1 p2 pp');
				assert.deepEqual(right27.toPlainText(), '1 pp2');

				const [left28, right28] = node.split({ offset: 28 });
				assert.deepEqual(left28.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(right28.toString(), '[div1]\n[div2]\n[div3]\n pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left28.toPlainText(), 'bold italic strike p1 p2 pp1');
				assert.deepEqual(right28.toPlainText(), ' pp2');

				const [left29, right29] = node.split({ offset: 29 });
				assert.deepEqual(left29.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 \n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(right29.toString(), '[div1]\n[div2]\n[div3]\npp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left29.toPlainText(), 'bold italic strike p1 p2 pp1 ');
				assert.deepEqual(right29.toPlainText(), 'pp2');

				const [left30, right30] = node.split({ offset: 30 });
				assert.deepEqual(left30.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 p\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(right30.toString(), '[div1]\n[div2]\n[div3]\np2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left30.toPlainText(), 'bold italic strike p1 p2 pp1 p');
				assert.deepEqual(right30.toPlainText(), 'p2');

				const [left31, right31] = node.split({ offset: 31 });
				assert.deepEqual(left31.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(right31.toString(), '[div1]\n[div2]\n[div3]\n2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left31.toPlainText(), 'bold italic strike p1 p2 pp1 pp');
				assert.deepEqual(right31.toPlainText(), '2');

				const [left32, right32] = node.split({ offset: 32 });
				assert.deepEqual(left32.toString(), '[div1]\n[b]bold[/b][i] italic[/i][s] strike[/s]\n[div2]\n p1 p2\n[div3]\n pp1 pp2\n[/div3]\n[/div2]\n[/div1]');
				assert.deepEqual(left32.toPlainText(), 'bold italic strike p1 p2 pp1 pp2');
				assert.deepEqual(right32, null);
			});

			it('Should split node with text nodes only', () => {
			    const node = scheme.createElement({
					name: 'p',
					children: [
						scheme.createText('text1'),
						scheme.createText('text2'),
						scheme.createText('text3'),
					],
				});

				const [left1, right1] = node.split({ offset: 0 });
				assert.deepEqual(left1, null);
				assert.deepEqual(right1.toString(), '[p]\ntext1text2text3\n[/p]');
				assert.deepEqual(right1.toPlainText(), 'text1text2text3');

				const [left5, right5] = node.split({ offset: 5 });
				assert.deepEqual(left5.toString(), '[p]\ntext1\n[/p]');
				assert.deepEqual(right5.toString(), '[p]\ntext2text3\n[/p]');
				assert.deepEqual(left5.toPlainText(), 'text1');
				assert.deepEqual(right5.toPlainText(), 'text2text3');

				const [left15, right15] = node.split({ offset: 15 });
				assert.deepEqual(left15.toString(), '[p]\ntext1text2text3\n[/p]');
				assert.deepEqual(right15, null);
				assert.deepEqual(left15.toPlainText(), 'text1text2text3');
			});

			it('Should split tree by words', () => {
				const node = scheme.createElement({
					name: 'p',
					children: [
						scheme.createText('text1 text2 text3'),
						scheme.createText('text4'),
						scheme.createText('text5'),
					],
				});

				const [left0, right0] = node.split({ offset: 0, byWord: true });
				assert.deepEqual(left0, null);
				assert.deepEqual(right0.toPlainText(), 'text1 text2 text3text4text5');

				const [left1, right1] = node.split({ offset: 1, byWord: true });
				assert.deepEqual(left1.toPlainText(), '');
				assert.deepEqual(right1.toPlainText(), 'text1 text2 text3text4text5');

				const [left2, right2] = node.split({ offset: 2, byWord: true });
				assert.deepEqual(left2.toPlainText(), '');
				assert.deepEqual(right2.toPlainText(), 'text1 text2 text3text4text5');

				const [left3, right3] = node.split({ offset: 3, byWord: true });
				assert.deepEqual(left3.toPlainText(), '');
				assert.deepEqual(right3.toPlainText(), 'text1 text2 text3text4text5');

				const [left4, right4] = node.split({ offset: 4, byWord: true });
				assert.deepEqual(left4.toPlainText(), '');
				assert.deepEqual(right4.toPlainText(), 'text1 text2 text3text4text5');

				const [left5, right5] = node.split({ offset: 5, byWord: true });
				assert.deepEqual(left5.toPlainText(), 'text1');
				assert.deepEqual(right5.toPlainText(), ' text2 text3text4text5');

				const [left6, right6] = node.split({ offset: 6, byWord: true });
				assert.deepEqual(left6.toPlainText(), 'text1 ');
				assert.deepEqual(right6.toPlainText(), 'text2 text3text4text5');

				const [left7, right7] = node.split({ offset: 7, byWord: true });
				assert.deepEqual(left7.toPlainText(), 'text1 ');
				assert.deepEqual(right7.toPlainText(), 'text2 text3text4text5');

				const [left8, right8] = node.split({ offset: 8, byWord: true });
				assert.deepEqual(left8.toPlainText(), 'text1 ');
				assert.deepEqual(right8.toPlainText(), 'text2 text3text4text5');

				const [left9, right9] = node.split({ offset: 9, byWord: true });
				assert.deepEqual(left9.toPlainText(), 'text1 ');
				assert.deepEqual(right9.toPlainText(), 'text2 text3text4text5');

				const [left10, right10] = node.split({ offset: 10, byWord: true });
				assert.deepEqual(left10.toPlainText(), 'text1 ');
				assert.deepEqual(right10.toPlainText(), 'text2 text3text4text5');

				const [left11, right11] = node.split({ offset: 11, byWord: true });
				assert.deepEqual(left11.toPlainText(), 'text1 text2');
				assert.deepEqual(right11.toPlainText(), ' text3text4text5');

				const [left12, right12] = node.split({ offset: 12, byWord: true });
				assert.deepEqual(left12.toPlainText(), 'text1 text2 ');
				assert.deepEqual(right12.toPlainText(), 'text3text4text5');

				const [left13, right13] = node.split({ offset: 13, byWord: true });
				assert.deepEqual(left13.toPlainText(), 'text1 text2 ');
				assert.deepEqual(right13.toPlainText(), 'text3text4text5');

				const [left14, right14] = node.split({ offset: 14, byWord: true });
				assert.deepEqual(left14.toPlainText(), 'text1 text2 ');
				assert.deepEqual(right14.toPlainText(), 'text3text4text5');

				const [left15, right15] = node.split({ offset: 15, byWord: true });
				assert.deepEqual(left15.toPlainText(), 'text1 text2 ');
				assert.deepEqual(right15.toPlainText(), 'text3text4text5');

				const [left16, right16] = node.split({ offset: 16, byWord: true });
				assert.deepEqual(left16.toPlainText(), 'text1 text2 ');
				assert.deepEqual(right16.toPlainText(), 'text3text4text5');

				const [left17, right17] = node.split({ offset: 17, byWord: true });
				assert.deepEqual(left17.toPlainText(), 'text1 text2 text3');
				assert.deepEqual(right17.toPlainText(), 'text4text5');

				const [left18, right18] = node.split({ offset: 18, byWord: true });
				assert.deepEqual(left18.toPlainText(), 'text1 text2 text3');
				assert.deepEqual(right18.toPlainText(), 'text4text5');

				const [left19, right19] = node.split({ offset: 19, byWord: true });
				assert.deepEqual(left19.toPlainText(), 'text1 text2 text3');
				assert.deepEqual(right19.toPlainText(), 'text4text5');

				const [left20, right20] = node.split({ offset: 20, byWord: true });
				assert.deepEqual(left20.toPlainText(), 'text1 text2 text3');
				assert.deepEqual(right20.toPlainText(), 'text4text5');

				const [left21, right21] = node.split({ offset: 21, byWord: true });
				assert.deepEqual(left21.toPlainText(), 'text1 text2 text3');
				assert.deepEqual(right21.toPlainText(), 'text4text5');

				const [left22, right22] = node.split({ offset: 22, byWord: true });
				assert.deepEqual(left22.toPlainText(), 'text1 text2 text3text4');
				assert.deepEqual(right22.toPlainText(), 'text5');

				const [left23, right23] = node.split({ offset: 23, byWord: true });
				assert.deepEqual(left23.toPlainText(), 'text1 text2 text3text4');
				assert.deepEqual(right23.toPlainText(), 'text5');

				const [left24, right24] = node.split({ offset: 24, byWord: true });
				assert.deepEqual(left24.toPlainText(), 'text1 text2 text3text4');
				assert.deepEqual(right24.toPlainText(), 'text5');

				const [left25, right25] = node.split({ offset: 25, byWord: true });
				assert.deepEqual(left25.toPlainText(), 'text1 text2 text3text4');
				assert.deepEqual(right25.toPlainText(), 'text5');

				const [left26, right26] = node.split({ offset: 26, byWord: true });
				assert.deepEqual(left26.toPlainText(), 'text1 text2 text3text4');
				assert.deepEqual(right26.toPlainText(), 'text5');

				const [left27, right27] = node.split({ offset: 27, byWord: true });
				assert.deepEqual(left27.toPlainText(), 'text1 text2 text3text4text5');
				assert.deepEqual(right27, null);
			});

			it('should split node with linebreaks', () => {
			    const node = scheme.createElement({
					name: 'b',
					children: [
						scheme.createText('text'),
						scheme.createNewLine(),
						scheme.createText('text2'),
						scheme.createNewLine(),
						scheme.createNewLine(),
						scheme.createText('text3'),
					],
				});

				const [leftTree, rightTree] = node.split({ offset: 5 });

				assert.deepEqual(leftTree.toString(), '[b]text\n[/b]');
				assert.deepEqual(rightTree.toString(), '[b]text2\n\ntext3[/b]');
			});

			it('should split node with tabs', () => {
				const node = scheme.createRoot({
					children: [
						scheme.createText('text'),
						scheme.createTab(),
						scheme.createText('text2'),
						scheme.createTab(),
						scheme.createTab(),
						scheme.createText('text3'),
					],
				});

				const [leftTree, rightTree] = node.split({ offset: 5 });

				assert.deepEqual(leftTree.toString(), 'text\t');
				assert.deepEqual(rightTree.toString(), 'text2\t\ttext3');
			});
		});

		describe('ElementNode.insertBefore()', () => {
		    it('should insert nodes before current node', () => {
				const p1 = scheme.createElement({ name: 'p', value: 1 });
				const p2 = scheme.createElement({ name: 'p', value: 2 });
				const p3 = scheme.createElement({ name: 'p', value: 3 });
				const p4 = scheme.createElement({ name: 'p', value: 4 });

				const root = scheme.createRoot({
					children: [
						p1,
						p2,
						p3,
						p4,
					],
				});

				const p11 = scheme.createElement({ name: 'p', value: 11 });
				const p22 = scheme.createElement({ name: 'p', value: 22 });

				p2.insertBefore(p11, p22);

				assert.ok(root.getChildrenCount() === 6);

				const children = root.getChildren();
				assert.ok(children.at(0) === p1);
				assert.ok(children.at(1) === p11);
				assert.ok(children.at(2) === p22);
				assert.ok(children.at(3) === p2);
				assert.ok(children.at(4) === p3);
				assert.ok(children.at(5) === p4);
		    });

			it('should insert nodes before current nodes if current node is first child of parent', () => {
				const p1 = scheme.createElement({ name: 'p', value: 1 });
				const p2 = scheme.createElement({ name: 'p', value: 2 });

				const root = scheme.createRoot({
					children: [
						p1,
						p2,
					],
				});

				const p3 = scheme.createElement({ name: 'p', value: 3 });
				const p4 = scheme.createElement({ name: 'p', value: 4 });

				p1.insertBefore(p3, p4);

				assert.ok(root.getChildrenCount() === 4);

				const children = root.getChildren();
				assert.ok(children.at(0) === p3);
				assert.ok(children.at(1) === p4);
				assert.ok(children.at(2) === p1);
				assert.ok(children.at(3) === p2);
			});
		});

		describe('ElementNode.insertAfter()', () => {
			it('should insert nodes before current node', () => {
				const p1 = scheme.createElement({ name: 'p', value: 1 });
				const p2 = scheme.createElement({ name: 'p', value: 2 });
				const p3 = scheme.createElement({ name: 'p', value: 3 });
				const p4 = scheme.createElement({ name: 'p', value: 4 });

				const root = scheme.createRoot({
					children: [
						p1,
						p2,
						p3,
						p4,
					],
				});

				const p11 = scheme.createElement({ name: 'p', value: 11 });
				const p22 = scheme.createElement({ name: 'p', value: 22 });

				p2.insertAfter(p11, p22);

				assert.ok(root.getChildrenCount() === 6);

				const children = root.getChildren();
				assert.ok(children.at(0) === p1);
				assert.ok(children.at(1) === p2);
				assert.ok(children.at(2) === p11);
				assert.ok(children.at(3) === p22);
				assert.ok(children.at(4) === p3);
				assert.ok(children.at(5) === p4);
			});

			it('should insert nodes before current nodes if current node is last child of parent', () => {
				const p1 = scheme.createElement({ name: 'p', value: 1 });
				const p2 = scheme.createElement({ name: 'p', value: 2 });

				const root = scheme.createRoot({
					children: [
						p1,
						p2,
					],
				});

				const p3 = scheme.createElement({ name: 'p', value: 3 });
				const p4 = scheme.createElement({ name: 'p', value: 4 });

				p2.insertAfter(p3, p4);

				assert.ok(root.getChildrenCount() === 4);

				const children = root.getChildren();
				assert.ok(children.at(0) === p1);
				assert.ok(children.at(1) === p2);
				assert.ok(children.at(2) === p3);
				assert.ok(children.at(3) === p4);
			});
		});

		describe('ElementNode.trimLinebreaks()', () => {
		    it('should removes all start and end linebreaks', () => {
		        const element = scheme.createElement({
					name: 'p',
					children: [
						scheme.createNewLine(),
						scheme.createNewLine(),
						scheme.createText('test'),
						scheme.createNewLine(),
						scheme.createNewLine(),
					],
				});

				element.trimLinebreaks();

				assert.ok(element.getChildrenCount() === 1);
				assert.ok(element.getFirstChild().getContent() === 'test');
		    });
		});
	});

	describe('Default scheme rules', () => {
		describe('allowedChildren', () => {
			it('b, i, u, s, span should include allowed tags only', () => {
				const b = scheme.createElement({
					name: 'b',
					children: [
						scheme.createElement({ name: 'i' }),
						scheme.createElement({ name: 'u' }),
						scheme.createElement({ name: 's' }),
						scheme.createText('text'),
						scheme.createNewLine(),
						scheme.createElement({ name: 'p' }),
					],
				});

				assert.ok(b.getChildrenCount() === 5, '#1');
				assert.ok(b.getChildren().at(0).getName() === 'i', '#2');
				assert.ok(b.getChildren().at(1).getName() === 'u', '#3');
				assert.ok(b.getChildren().at(2).getName() === 's', '#4');
				assert.ok(b.getChildren().at(3).getName() === '#text', '#6');
				assert.ok(b.getChildren().at(4).getName() === '#linebreak', '#7');
			});

			it('img, url should include only allowed child', () => {
				const img = scheme.createElement({
					name: 'img',
					children: [
						scheme.createElement({ name: 'b' }),
						scheme.createNewLine(),
						scheme.createTab(),
						scheme.createText('test'),
					],
				});

				assert.ok(img.getChildrenCount() === 1);
				assert.ok(img.getLastChild().getName() === '#text');

				const url = scheme.createElement({
					name: 'url',
					children: [
						scheme.createElement({ name: 'b' }),
						scheme.createNewLine(),
						scheme.createTab(),
						scheme.createText('test'),
					],
				});

				assert.ok(url.getChildrenCount() === 2);
				assert.ok(url.getFirstChild().getName() === 'b');
				assert.ok(url.getLastChild().getName() === '#text');
			});

			it('p should include only allowed child', () => {
				const p = scheme.createElement({
					name: 'p',
					children: [
						scheme.createElement({ name: 'b' }),
						scheme.createElement({ name: 'i' }),
						scheme.createText('test'),
						scheme.createNewLine(),
						scheme.createTab(),
						scheme.createElement({ name: 'p' }),
						scheme.createElement({ name: 'disk' }),
					],
				});

				assert.ok(p.getChildrenCount() === 5);
				assert.ok(p.getChildren().at(0).getName() === 'b');
				assert.ok(p.getChildren().at(1).getName() === 'i');
				assert.ok(p.getChildren().at(2).getName() === '#text');
				assert.ok(p.getChildren().at(3).getName() === '#linebreak');
				assert.ok(p.getChildren().at(4).getName() === 'disk');
			});
		});

		describe('void', () => {
		    it('disk should be void', () => {
				const disk = scheme.createElement({ name: 'disk' });
				assert.ok(disk.isVoid());
		    });

			it('table should not be void', () => {
			    const table = scheme.createElement({ name: 'table' });
				assert.ok(table.isVoid() === false);
			});
		});

		describe('stringify', () => {
			it('* content should be without final line break', () => {
				const item = scheme.createElement({
					name: '*',
					children: [
						scheme.createText('test'),
						scheme.createNewLine(),
					],
				});

				assert.deepEqual(item.toString(), '[*]test');

				const item2 = scheme.createElement({
					name: '*',
					children: [
						scheme.createText('test\n'),
					],
				});

				assert.deepEqual(item2.toString(), '[*]test');
			});

			it('p should includes line breaks after opening tag and before closing tag', () => {
				const p = scheme.createElement({
					name: 'p',
					children: [
						scheme.createText('test'),
					],
				});

				assert.deepEqual(p.toString(), '[p]\ntest\n[/p]');
			});

			it('p should includes line breaks before opening tag and after closing tag', () => {
				const p = scheme.createElement({
					name: 'p',
					children: [
						scheme.createText('test'),
					],
				});

				void scheme.createRoot({
					children: [
						scheme.createElement({ name: 'b' }),
						p,
						scheme.createElement({ name: 'i' }),
					],
				});

				assert.deepEqual(p.toString(), '\n[p]\ntest\n[/p]\n');
			});
		});

		describe('convertChild', () => {
		    it('code should include strings, line breaks and tabs', () => {
		        const code = scheme.createElement({
					name: 'code',
					children: [
						scheme.createElement({
							name: 'p',
							children: [
								scheme.createText('test'),
							],
						}),
						scheme.createNewLine(),
						scheme.createTab(),
						scheme.createText('test1'),
					],
				});
		    });

			it('should return correct canBeEmpty value', () => {
			    const scheme = new BBCodeScheme({
					tagSchemes: [
						new BBCodeTagScheme({
							name: 'div',
							canBeEmpty: true,
						}),
						new BBCodeTagScheme({
							name: 'div2',
							canBeEmpty: false,
						}),
						new BBCodeTagScheme({
							name: 'div3',
						}),
					],
				});

				assert.ok(scheme.createElement({ name: 'div'}).canBeEmpty());
				assert.ok(scheme.createElement({ name: 'div2'}).canBeEmpty() === false);
				assert.ok(scheme.createElement({ name: 'div3'}).canBeEmpty());
			});
		});
	});
});
