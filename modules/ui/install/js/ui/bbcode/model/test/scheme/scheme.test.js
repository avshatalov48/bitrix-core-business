import { BBCodeScheme } from '../../src/scheme/bbcode-scheme';
import { BBCodeTagScheme } from '../../src/scheme/node-schemes/tag-scheme';
import { DefaultBBCodeScheme } from '../../src/scheme/default-bbcode-scheme';

describe('BBCodeScheme', () => {
	let bbCodeScheme: BBCodeScheme;

	beforeEach(() => {
		const options = {
			tagSchemes: [],
		};

		bbCodeScheme = new BBCodeScheme(options);
	});

	describe('BBCodeScheme.constructor', () => {
		it('should throw a TypeError if options is not an object', () => {
			assert.throws(() => {
				new BBCodeScheme(null);
			}, TypeError);
		});

		it('should set tag schemes with the provided options', () => {
			const tagSchemes = [
				new BBCodeTagScheme({ name: 'p' }),
				new BBCodeTagScheme({ name: ['b', 'u'] }),
			];
			const bbCodeScheme = new BBCodeScheme({
				tagSchemes,
			});

			assert.deepEqual(bbCodeScheme.getTagSchemes(), tagSchemes);
		});
	});

	describe('BBCodeScheme.setTagSchemes', () => {
		it('should set tag schemes with the provided tag schemes', () => {
			const tagSchemes = [new BBCodeTagScheme({ name: 'p' })];
			bbCodeScheme.setTagSchemes(tagSchemes);

			assert.deepEqual(bbCodeScheme.getTagSchemes(), tagSchemes);
		});

		it('should throw a TypeError if any of the provided tag schemes is not an instance of TagScheme', () => {
			const tagSchemes = [new BBCodeTagScheme({ name: 'p' }), {}];

			assert.throws(() => {
				bbCodeScheme.setTagSchemes(tagSchemes);
			}, TypeError);
		});
	});

	describe('BBCodeScheme.setTagScheme', () => {
		it('should add the provided tag schemes to the existing tag schemes', () => {
			const existingTagSchemes = [new BBCodeTagScheme({ name: 'div' })];
			const newTagSchemes = [new BBCodeTagScheme({ name: 'p' })];
			bbCodeScheme.setTagSchemes(existingTagSchemes);
			bbCodeScheme.setTagScheme(...newTagSchemes);

			assert.deepEqual(bbCodeScheme.getTagSchemes(), [...existingTagSchemes, ...newTagSchemes]);
		});

		it('should remove the names of the new tag schemes from the existing tag schemes', () => {
			const existingTagScheme1 = new BBCodeTagScheme({ name: ['p', 'b', 'i'] });
			const existingTagScheme2 = new BBCodeTagScheme({ name: ['s', 'u'] });
			const newTagScheme1 = new BBCodeTagScheme({ name: 'p' });
			const newTagScheme2 = new BBCodeTagScheme({ name: 'u' });

			bbCodeScheme.setTagSchemes([existingTagScheme1, existingTagScheme2]);
			bbCodeScheme.setTagScheme(newTagScheme1, newTagScheme2);

			assert.ok(bbCodeScheme.getTagSchemes().length === 4);
			assert.deepEqual(existingTagScheme1.getName(), ['b', 'i']);
			assert.deepEqual(existingTagScheme2.getName(), ['s']);
			assert.deepEqual(newTagScheme1.getName(), ['p']);
			assert.deepEqual(newTagScheme2.getName(), ['u']);
		});

		it('should throw a TypeError if any of the provided tag schemes is not an instance of TagScheme', () => {
			const tagSchemes = [new BBCodeTagScheme({ name: 'p' }), {}];

			assert.throws(() => {
				bbCodeScheme.setTagScheme(...tagSchemes);
			}, TypeError);
		});
	});

	describe('BBCodeScheme.getTagSchemes', () => {
		it('should return a copy of the tagSchemes property', () => {
			const tagSchemes = [new BBCodeTagScheme({ name: 'p' })];
			bbCodeScheme.setTagSchemes(tagSchemes);

			assert.deepEqual(bbCodeScheme.getTagSchemes(), tagSchemes);
		});
	});

	describe('BBCodeScheme.getTagScheme', () => {
		it('should return the tag scheme with the provided tag name if it exists', () => {
			const tagScheme1 = new BBCodeTagScheme({ name: 'tag1' });
			const tagScheme2 = new BBCodeTagScheme({ name: 'tag2' });
			bbCodeScheme.setTagSchemes([tagScheme1, tagScheme2]);

			assert.deepEqual(bbCodeScheme.getTagScheme('tag1'), tagScheme1);
		});
	});

	describe('BBCodeScheme.setOutputTagCase', () => {
		it('should throw a TypeError if passed not allowed string value', () => {
		    assert.throws(() => {
				bbCodeScheme.setOutputTagCase('test');
			});
		});

		it('should throw a TypeError if passed object', () => {
			assert.throws(() => {
				bbCodeScheme.setOutputTagCase({});
			});
		});

		it('should throw a TypeError if passed number', () => {
			assert.throws(() => {
				bbCodeScheme.setOutputTagCase(2);
			});
		});

		it('should does not throws if passed null', () => {
		    assert.doesNotThrow(() => {
				bbCodeScheme.setOutputTagCase(null);
			});
		});

		it('should does not throws if passed undefined', () => {
			assert.doesNotThrow(() => {
				bbCodeScheme.setOutputTagCase(undefined);
			});
		});

		it('should sets allowed case', () => {
			bbCodeScheme.setOutputTagCase(BBCodeScheme.Case.LOWER);
			assert.equal(bbCodeScheme.getOutputTagCase(), BBCodeScheme.Case.LOWER);

			bbCodeScheme.setOutputTagCase(BBCodeScheme.Case.UPPER);
			assert.equal(bbCodeScheme.getOutputTagCase(), BBCodeScheme.Case.UPPER);
		});
	});

	describe('BBCodeScheme.setUnresolvedNodesHoisting', () => {
	    it('should throw a TypeError if passed string', () => {
			assert.throws(() => {
				bbCodeScheme.setUnresolvedNodesHoisting('111');
			});
	    });

		it('should throw a TypeError if passed object', () => {
			assert.throws(() => {
				bbCodeScheme.setUnresolvedNodesHoisting({});
			});

			assert.throws(() => {
				bbCodeScheme.setUnresolvedNodesHoisting([]);
			});
		});

		it('should does not throws if passed null', () => {
			assert.doesNotThrow(() => {
				bbCodeScheme.setUnresolvedNodesHoisting(null);
			});
		});

		it('should does not throws if passed undefined', () => {
			assert.doesNotThrow(() => {
				bbCodeScheme.setUnresolvedNodesHoisting(undefined);
			});
		});

		it('should sets allowed value', () => {
		    bbCodeScheme.setUnresolvedNodesHoisting(true);
			assert.equal(bbCodeScheme.isAllowedUnresolvedNodesHoisting(), true);

			bbCodeScheme.setUnresolvedNodesHoisting(false);
			assert.equal(bbCodeScheme.isAllowedUnresolvedNodesHoisting(), false);
		});
	});

	describe('BBCodeScheme.isChildAllowed', () => {
		it('Should return true if child allowed or false if not allowed', () => {
		    const scheme = new BBCodeScheme({
				tagSchemes: [
					new BBCodeTagScheme({
						name: 'p',
						group: ['#block'],
						allowedChildren: ['b', 'u'],
					}),
					new BBCodeTagScheme({
						name: 'table',
						group: ['#block'],
						allowedChildren: ['tr'],
					}),
					new BBCodeTagScheme({
						name: 'tr',
						group: ['#block'],
						allowedChildren: ['td'],
					}),
					new BBCodeTagScheme({
						name: ['b', 'u', 'i', 's'],
						group: ['#inline'],
						allowedChildren: ['#inline'],
					}),
				],
			});

			const p = scheme.createElement({ name: 'p' });
			const b = scheme.createElement({ name: 'b' });
			const u = scheme.createElement({ name: 'u' });
			const table = scheme.createElement({ name: 'table' });
			const tr = scheme.createElement({ name: 'tr' });

			assert.ok(scheme.isChildAllowed(p, b));
			assert.ok(scheme.isChildAllowed(p, u));
			assert.ok(scheme.isChildAllowed(p.getName(), b.getName()));

			assert.ok(scheme.isChildAllowed(table, tr));
			assert.ok(scheme.isChildAllowed(table.getName(), tr.getName()));

			assert.ok(scheme.isChildAllowed(p, table) === false);
			assert.ok(scheme.isChildAllowed(p.getName(), table.getName()) === false);
		});
	});

	describe('BBCodeScheme.isVoid', () => {
		it('should return true if passed void node', () => {
			const scheme = new BBCodeScheme({
				tagSchemes: [
					new BBCodeTagScheme({
						name: 'disk',
						void: true,
						allowedChildren: ['b', 'u'],
					}),
					new BBCodeTagScheme({
						name: 'table',
						group: ['#block'],
						allowedChildren: ['tr'],
					}),
				],
			});

			assert.ok(scheme.isVoid('disk'));
			assert.ok(scheme.isVoid('table') === false);

			const disk = scheme.createElement({ name: 'disk' });
			assert.ok(scheme.isVoid(disk));

			const table = scheme.createElement({ name: 'table' });
			assert.ok(scheme.isVoid(table) === false);
		});
	});

	describe('BBCodeScheme.isAllowedIn', () => {
		it('should return true if allowed child of parent or return false', () => {
			const scheme = new BBCodeScheme({
				tagSchemes: [
					new BBCodeTagScheme({
						name: '#root',
					}),
					new BBCodeTagScheme({
						name: 'table',
						group: ['#block'],
						allowedChildren: ['tr'],
						allowedIn: ['#root'],
					}),
					new BBCodeTagScheme({
						name: 'tr',
						group: ['#block'],
						allowedChildren: ['td', 'th'],
						allowedIn: ['table'],
					}),
					new BBCodeTagScheme({
						name: ['td', 'th'],
						group: ['#block'],
						allowedChildren: ['#inline', '#block'],
						allowedIn: ['tr'],
					}),
				],
			});

			const root = scheme.createRoot();
			const table = scheme.createElement({ name: 'table' });
			const tr = scheme.createElement({ name: 'tr' });
			const td = scheme.createElement({ name: 'td' });
			const th = scheme.createElement({ name: 'th' });

			assert.ok(scheme.isChildAllowed(root, table));
			assert.ok(scheme.isChildAllowed(table, tr));
			assert.ok(scheme.isChildAllowed(tr, td));
			assert.ok(scheme.isChildAllowed(tr, th));

			assert.ok(scheme.isChildAllowed(root, tr) === false);
			assert.ok(scheme.isChildAllowed(root, td) === false);
			assert.ok(scheme.isChildAllowed(root, th) === false);
			assert.ok(scheme.isChildAllowed(th, table) === false);

			root.appendChild(table);
			assert.ok(root.getChildren().includes(table));

			root.appendChild(tr);
			assert.ok(root.getChildren().includes(tr) === false);

			td.appendChild(tr);
			assert.ok(td.getChildren().includes(tr) === false);
		});
	});
});
