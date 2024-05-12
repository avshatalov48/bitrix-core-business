import { DefaultBBCodeScheme } from '../../src/scheme/default-bbcode-scheme';

describe('Parent <-> child rules (auto-generated)', () => {
	let scheme;
	beforeEach(() => {
		scheme = new DefaultBBCodeScheme();
	});
	
	
	it('b allowed #text', () => {
		assert.ok(scheme.isChildAllowed('b', '#text'));
	});
					
	it('b allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('b', '#linebreak'));
	});
					
	it('b allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('b', '#inline'));
	});
					
	it('b allowed b', () => {
		assert.ok(scheme.isChildAllowed('b', 'b'));
	});
					
	it('b allowed u', () => {
		assert.ok(scheme.isChildAllowed('b', 'u'));
	});
					
	it('b allowed i', () => {
		assert.ok(scheme.isChildAllowed('b', 'i'));
	});
					
	it('b allowed s', () => {
		assert.ok(scheme.isChildAllowed('b', 's'));
	});
					
	it('b allowed #format', () => {
		assert.ok(scheme.isChildAllowed('b', '#format'));
	});
					
	it('b allowed span', () => {
		assert.ok(scheme.isChildAllowed('b', 'span'));
	});
					
	it('b allowed url', () => {
		assert.ok(scheme.isChildAllowed('b', 'url'));
	});
					
	it('b allowed user', () => {
		assert.ok(scheme.isChildAllowed('b', 'user'));
	});
					
	it('b allowed project', () => {
		assert.ok(scheme.isChildAllowed('b', 'project'));
	});
					
	it('b allowed department', () => {
		assert.ok(scheme.isChildAllowed('b', 'department'));
	});
					
	it('b allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('b', '#mention'));
	});
					
	it('b allowed disk', () => {
		assert.ok(scheme.isChildAllowed('b', 'disk'));
	});
					
	it('b allowed #void', () => {
		assert.ok(scheme.isChildAllowed('b', '#void'));
	});
					
	it('u allowed #text', () => {
		assert.ok(scheme.isChildAllowed('u', '#text'));
	});
					
	it('u allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('u', '#linebreak'));
	});
					
	it('u allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('u', '#inline'));
	});
					
	it('u allowed b', () => {
		assert.ok(scheme.isChildAllowed('u', 'b'));
	});
					
	it('u allowed u', () => {
		assert.ok(scheme.isChildAllowed('u', 'u'));
	});
					
	it('u allowed i', () => {
		assert.ok(scheme.isChildAllowed('u', 'i'));
	});
					
	it('u allowed s', () => {
		assert.ok(scheme.isChildAllowed('u', 's'));
	});
					
	it('u allowed #format', () => {
		assert.ok(scheme.isChildAllowed('u', '#format'));
	});
					
	it('u allowed span', () => {
		assert.ok(scheme.isChildAllowed('u', 'span'));
	});
					
	it('u allowed url', () => {
		assert.ok(scheme.isChildAllowed('u', 'url'));
	});
					
	it('u allowed user', () => {
		assert.ok(scheme.isChildAllowed('u', 'user'));
	});
					
	it('u allowed project', () => {
		assert.ok(scheme.isChildAllowed('u', 'project'));
	});
					
	it('u allowed department', () => {
		assert.ok(scheme.isChildAllowed('u', 'department'));
	});
					
	it('u allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('u', '#mention'));
	});
					
	it('u allowed disk', () => {
		assert.ok(scheme.isChildAllowed('u', 'disk'));
	});
					
	it('u allowed #void', () => {
		assert.ok(scheme.isChildAllowed('u', '#void'));
	});
					
	it('i allowed #text', () => {
		assert.ok(scheme.isChildAllowed('i', '#text'));
	});
					
	it('i allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('i', '#linebreak'));
	});
					
	it('i allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('i', '#inline'));
	});
					
	it('i allowed b', () => {
		assert.ok(scheme.isChildAllowed('i', 'b'));
	});
					
	it('i allowed u', () => {
		assert.ok(scheme.isChildAllowed('i', 'u'));
	});
					
	it('i allowed i', () => {
		assert.ok(scheme.isChildAllowed('i', 'i'));
	});
					
	it('i allowed s', () => {
		assert.ok(scheme.isChildAllowed('i', 's'));
	});
					
	it('i allowed #format', () => {
		assert.ok(scheme.isChildAllowed('i', '#format'));
	});
					
	it('i allowed span', () => {
		assert.ok(scheme.isChildAllowed('i', 'span'));
	});
					
	it('i allowed url', () => {
		assert.ok(scheme.isChildAllowed('i', 'url'));
	});
					
	it('i allowed user', () => {
		assert.ok(scheme.isChildAllowed('i', 'user'));
	});
					
	it('i allowed project', () => {
		assert.ok(scheme.isChildAllowed('i', 'project'));
	});
					
	it('i allowed department', () => {
		assert.ok(scheme.isChildAllowed('i', 'department'));
	});
					
	it('i allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('i', '#mention'));
	});
					
	it('i allowed disk', () => {
		assert.ok(scheme.isChildAllowed('i', 'disk'));
	});
					
	it('i allowed #void', () => {
		assert.ok(scheme.isChildAllowed('i', '#void'));
	});
					
	it('s allowed #text', () => {
		assert.ok(scheme.isChildAllowed('s', '#text'));
	});
					
	it('s allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('s', '#linebreak'));
	});
					
	it('s allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('s', '#inline'));
	});
					
	it('s allowed b', () => {
		assert.ok(scheme.isChildAllowed('s', 'b'));
	});
					
	it('s allowed u', () => {
		assert.ok(scheme.isChildAllowed('s', 'u'));
	});
					
	it('s allowed i', () => {
		assert.ok(scheme.isChildAllowed('s', 'i'));
	});
					
	it('s allowed s', () => {
		assert.ok(scheme.isChildAllowed('s', 's'));
	});
					
	it('s allowed #format', () => {
		assert.ok(scheme.isChildAllowed('s', '#format'));
	});
					
	it('s allowed span', () => {
		assert.ok(scheme.isChildAllowed('s', 'span'));
	});
					
	it('s allowed url', () => {
		assert.ok(scheme.isChildAllowed('s', 'url'));
	});
					
	it('s allowed user', () => {
		assert.ok(scheme.isChildAllowed('s', 'user'));
	});
					
	it('s allowed project', () => {
		assert.ok(scheme.isChildAllowed('s', 'project'));
	});
					
	it('s allowed department', () => {
		assert.ok(scheme.isChildAllowed('s', 'department'));
	});
					
	it('s allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('s', '#mention'));
	});
					
	it('s allowed disk', () => {
		assert.ok(scheme.isChildAllowed('s', 'disk'));
	});
					
	it('s allowed #void', () => {
		assert.ok(scheme.isChildAllowed('s', '#void'));
	});
					
	it('#inline allowed #text', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#text'));
	});
					
	it('#inline allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#linebreak'));
	});
					
	it('#inline allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#inline'));
	});
					
	it('#inline allowed b', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'b'));
	});
					
	it('#inline allowed u', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'u'));
	});
					
	it('#inline allowed i', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'i'));
	});
					
	it('#inline allowed s', () => {
		assert.ok(scheme.isChildAllowed('#inline', 's'));
	});
					
	it('#inline allowed #format', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#format'));
	});
					
	it('#inline allowed span', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'span'));
	});
					
	it('#inline allowed url', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'url'));
	});
					
	it('#inline allowed user', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'user'));
	});
					
	it('#inline allowed project', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'project'));
	});
					
	it('#inline allowed department', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'department'));
	});
					
	it('#inline allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#mention'));
	});
					
	it('#inline allowed disk', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'disk'));
	});
					
	it('#inline allowed #void', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#void'));
	});
					
	it('#inline allowed img', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'img'));
	});
					
	it('#format allowed #text', () => {
		assert.ok(scheme.isChildAllowed('#format', '#text'));
	});
					
	it('#format allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('#format', '#linebreak'));
	});
					
	it('#format allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('#format', '#inline'));
	});
					
	it('#format allowed b', () => {
		assert.ok(scheme.isChildAllowed('#format', 'b'));
	});
					
	it('#format allowed u', () => {
		assert.ok(scheme.isChildAllowed('#format', 'u'));
	});
					
	it('#format allowed i', () => {
		assert.ok(scheme.isChildAllowed('#format', 'i'));
	});
					
	it('#format allowed s', () => {
		assert.ok(scheme.isChildAllowed('#format', 's'));
	});
					
	it('#format allowed #format', () => {
		assert.ok(scheme.isChildAllowed('#format', '#format'));
	});
					
	it('#format allowed span', () => {
		assert.ok(scheme.isChildAllowed('#format', 'span'));
	});
					
	it('#format allowed url', () => {
		assert.ok(scheme.isChildAllowed('#format', 'url'));
	});
					
	it('#format allowed user', () => {
		assert.ok(scheme.isChildAllowed('#format', 'user'));
	});
					
	it('#format allowed project', () => {
		assert.ok(scheme.isChildAllowed('#format', 'project'));
	});
					
	it('#format allowed department', () => {
		assert.ok(scheme.isChildAllowed('#format', 'department'));
	});
					
	it('#format allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('#format', '#mention'));
	});
					
	it('#format allowed disk', () => {
		assert.ok(scheme.isChildAllowed('#format', 'disk'));
	});
					
	it('#format allowed #void', () => {
		assert.ok(scheme.isChildAllowed('#format', '#void'));
	});
					
	it('span allowed #text', () => {
		assert.ok(scheme.isChildAllowed('span', '#text'));
	});
					
	it('span allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('span', '#linebreak'));
	});
					
	it('span allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('span', '#inline'));
	});
					
	it('span allowed b', () => {
		assert.ok(scheme.isChildAllowed('span', 'b'));
	});
					
	it('span allowed u', () => {
		assert.ok(scheme.isChildAllowed('span', 'u'));
	});
					
	it('span allowed i', () => {
		assert.ok(scheme.isChildAllowed('span', 'i'));
	});
					
	it('span allowed s', () => {
		assert.ok(scheme.isChildAllowed('span', 's'));
	});
					
	it('span allowed #format', () => {
		assert.ok(scheme.isChildAllowed('span', '#format'));
	});
					
	it('span allowed span', () => {
		assert.ok(scheme.isChildAllowed('span', 'span'));
	});
					
	it('span allowed url', () => {
		assert.ok(scheme.isChildAllowed('span', 'url'));
	});
					
	it('span allowed user', () => {
		assert.ok(scheme.isChildAllowed('span', 'user'));
	});
					
	it('span allowed project', () => {
		assert.ok(scheme.isChildAllowed('span', 'project'));
	});
					
	it('span allowed department', () => {
		assert.ok(scheme.isChildAllowed('span', 'department'));
	});
					
	it('span allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('span', '#mention'));
	});
					
	it('span allowed disk', () => {
		assert.ok(scheme.isChildAllowed('span', 'disk'));
	});
					
	it('span allowed #void', () => {
		assert.ok(scheme.isChildAllowed('span', '#void'));
	});
					
	it('img allowed #text', () => {
		assert.ok(scheme.isChildAllowed('img', '#text'));
	});
					
	it('#inlineBlock allowed #text', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#text'));
	});
					
	it('url allowed #text', () => {
		assert.ok(scheme.isChildAllowed('url', '#text'));
	});
					
	it('url allowed #format', () => {
		assert.ok(scheme.isChildAllowed('url', '#format'));
	});
					
	it('url allowed b', () => {
		assert.ok(scheme.isChildAllowed('url', 'b'));
	});
					
	it('url allowed u', () => {
		assert.ok(scheme.isChildAllowed('url', 'u'));
	});
					
	it('url allowed i', () => {
		assert.ok(scheme.isChildAllowed('url', 'i'));
	});
					
	it('url allowed s', () => {
		assert.ok(scheme.isChildAllowed('url', 's'));
	});
					
	it('url allowed img', () => {
		assert.ok(scheme.isChildAllowed('url', 'img'));
	});
					
	it('p allowed #text', () => {
		assert.ok(scheme.isChildAllowed('p', '#text'));
	});
					
	it('p allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('p', '#linebreak'));
	});
					
	it('p allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('p', '#inline'));
	});
					
	it('p allowed b', () => {
		assert.ok(scheme.isChildAllowed('p', 'b'));
	});
					
	it('p allowed u', () => {
		assert.ok(scheme.isChildAllowed('p', 'u'));
	});
					
	it('p allowed i', () => {
		assert.ok(scheme.isChildAllowed('p', 'i'));
	});
					
	it('p allowed s', () => {
		assert.ok(scheme.isChildAllowed('p', 's'));
	});
					
	it('p allowed #format', () => {
		assert.ok(scheme.isChildAllowed('p', '#format'));
	});
					
	it('p allowed span', () => {
		assert.ok(scheme.isChildAllowed('p', 'span'));
	});
					
	it('p allowed url', () => {
		assert.ok(scheme.isChildAllowed('p', 'url'));
	});
					
	it('p allowed user', () => {
		assert.ok(scheme.isChildAllowed('p', 'user'));
	});
					
	it('p allowed project', () => {
		assert.ok(scheme.isChildAllowed('p', 'project'));
	});
					
	it('p allowed department', () => {
		assert.ok(scheme.isChildAllowed('p', 'department'));
	});
					
	it('p allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('p', '#mention'));
	});
					
	it('p allowed disk', () => {
		assert.ok(scheme.isChildAllowed('p', 'disk'));
	});
					
	it('p allowed #void', () => {
		assert.ok(scheme.isChildAllowed('p', '#void'));
	});
					
	it('p allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('p', '#inlineBlock'));
	});
					
	it('p allowed img', () => {
		assert.ok(scheme.isChildAllowed('p', 'img'));
	});
					
	it('p allowed video', () => {
		assert.ok(scheme.isChildAllowed('p', 'video'));
	});
					
	it('#block allowed #text', () => {
		assert.ok(scheme.isChildAllowed('#block', '#text'));
	});
					
	it('#block allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('#block', '#linebreak'));
	});
					
	it('#block allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('#block', '#inline'));
	});
					
	it('#block allowed b', () => {
		assert.ok(scheme.isChildAllowed('#block', 'b'));
	});
					
	it('#block allowed u', () => {
		assert.ok(scheme.isChildAllowed('#block', 'u'));
	});
					
	it('#block allowed i', () => {
		assert.ok(scheme.isChildAllowed('#block', 'i'));
	});
					
	it('#block allowed s', () => {
		assert.ok(scheme.isChildAllowed('#block', 's'));
	});
					
	it('#block allowed #format', () => {
		assert.ok(scheme.isChildAllowed('#block', '#format'));
	});
					
	it('#block allowed span', () => {
		assert.ok(scheme.isChildAllowed('#block', 'span'));
	});
					
	it('#block allowed url', () => {
		assert.ok(scheme.isChildAllowed('#block', 'url'));
	});
					
	it('#block allowed user', () => {
		assert.ok(scheme.isChildAllowed('#block', 'user'));
	});
					
	it('#block allowed project', () => {
		assert.ok(scheme.isChildAllowed('#block', 'project'));
	});
					
	it('#block allowed department', () => {
		assert.ok(scheme.isChildAllowed('#block', 'department'));
	});
					
	it('#block allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('#block', '#mention'));
	});
					
	it('#block allowed disk', () => {
		assert.ok(scheme.isChildAllowed('#block', 'disk'));
	});
					
	it('#block allowed #void', () => {
		assert.ok(scheme.isChildAllowed('#block', '#void'));
	});
					
	it('#block allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('#block', '#inlineBlock'));
	});
					
	it('#block allowed img', () => {
		assert.ok(scheme.isChildAllowed('#block', 'img'));
	});
					
	it('#block allowed video', () => {
		assert.ok(scheme.isChildAllowed('#block', 'video'));
	});
					
	it('#block allowed #block', () => {
		assert.ok(scheme.isChildAllowed('#block', '#block'));
	});
					
	it('#block allowed p', () => {
		assert.ok(scheme.isChildAllowed('#block', 'p'));
	});
					
	it('#block allowed list', () => {
		assert.ok(scheme.isChildAllowed('#block', 'list'));
	});
					
	it('#block allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('#block', '#shadowRoot'));
	});
					
	it('#block allowed quote', () => {
		assert.ok(scheme.isChildAllowed('#block', 'quote'));
	});
					
	it('#block allowed code', () => {
		assert.ok(scheme.isChildAllowed('#block', 'code'));
	});
					
	it('#block allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('#block', 'spoiler'));
	});
					
	it('#block allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('#block', '#tab'));
	});
					
	it('list allowed *', () => {
		assert.ok(scheme.isChildAllowed('list', '*'));
	});
					
	it('* allowed #text', () => {
		assert.ok(scheme.isChildAllowed('*', '#text'));
	});
					
	it('* allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('*', '#linebreak'));
	});
					
	it('* allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('*', '#inline'));
	});
					
	it('* allowed b', () => {
		assert.ok(scheme.isChildAllowed('*', 'b'));
	});
					
	it('* allowed u', () => {
		assert.ok(scheme.isChildAllowed('*', 'u'));
	});
					
	it('* allowed i', () => {
		assert.ok(scheme.isChildAllowed('*', 'i'));
	});
					
	it('* allowed s', () => {
		assert.ok(scheme.isChildAllowed('*', 's'));
	});
					
	it('* allowed #format', () => {
		assert.ok(scheme.isChildAllowed('*', '#format'));
	});
					
	it('* allowed span', () => {
		assert.ok(scheme.isChildAllowed('*', 'span'));
	});
					
	it('* allowed url', () => {
		assert.ok(scheme.isChildAllowed('*', 'url'));
	});
					
	it('* allowed user', () => {
		assert.ok(scheme.isChildAllowed('*', 'user'));
	});
					
	it('* allowed project', () => {
		assert.ok(scheme.isChildAllowed('*', 'project'));
	});
					
	it('* allowed department', () => {
		assert.ok(scheme.isChildAllowed('*', 'department'));
	});
					
	it('* allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('*', '#mention'));
	});
					
	it('* allowed disk', () => {
		assert.ok(scheme.isChildAllowed('*', 'disk'));
	});
					
	it('* allowed #void', () => {
		assert.ok(scheme.isChildAllowed('*', '#void'));
	});
					
	it('* allowed img', () => {
		assert.ok(scheme.isChildAllowed('*', 'img'));
	});
					
	it('table allowed tr', () => {
		assert.ok(scheme.isChildAllowed('table', 'tr'));
	});
					
	it('tr allowed th', () => {
		assert.ok(scheme.isChildAllowed('tr', 'th'));
	});
					
	it('tr allowed td', () => {
		assert.ok(scheme.isChildAllowed('tr', 'td'));
	});
					
	it('th allowed #text', () => {
		assert.ok(scheme.isChildAllowed('th', '#text'));
	});
					
	it('th allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('th', '#linebreak'));
	});
					
	it('th allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('th', '#inline'));
	});
					
	it('th allowed b', () => {
		assert.ok(scheme.isChildAllowed('th', 'b'));
	});
					
	it('th allowed u', () => {
		assert.ok(scheme.isChildAllowed('th', 'u'));
	});
					
	it('th allowed i', () => {
		assert.ok(scheme.isChildAllowed('th', 'i'));
	});
					
	it('th allowed s', () => {
		assert.ok(scheme.isChildAllowed('th', 's'));
	});
					
	it('th allowed #format', () => {
		assert.ok(scheme.isChildAllowed('th', '#format'));
	});
					
	it('th allowed span', () => {
		assert.ok(scheme.isChildAllowed('th', 'span'));
	});
					
	it('th allowed url', () => {
		assert.ok(scheme.isChildAllowed('th', 'url'));
	});
					
	it('th allowed user', () => {
		assert.ok(scheme.isChildAllowed('th', 'user'));
	});
					
	it('th allowed project', () => {
		assert.ok(scheme.isChildAllowed('th', 'project'));
	});
					
	it('th allowed department', () => {
		assert.ok(scheme.isChildAllowed('th', 'department'));
	});
					
	it('th allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('th', '#mention'));
	});
					
	it('th allowed disk', () => {
		assert.ok(scheme.isChildAllowed('th', 'disk'));
	});
					
	it('th allowed #void', () => {
		assert.ok(scheme.isChildAllowed('th', '#void'));
	});
					
	it('th allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('th', '#inlineBlock'));
	});
					
	it('th allowed img', () => {
		assert.ok(scheme.isChildAllowed('th', 'img'));
	});
					
	it('th allowed video', () => {
		assert.ok(scheme.isChildAllowed('th', 'video'));
	});
					
	it('th allowed #block', () => {
		assert.ok(scheme.isChildAllowed('th', '#block'));
	});
					
	it('th allowed p', () => {
		assert.ok(scheme.isChildAllowed('th', 'p'));
	});
					
	it('th allowed list', () => {
		assert.ok(scheme.isChildAllowed('th', 'list'));
	});
					
	it('th allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('th', '#shadowRoot'));
	});
					
	it('th allowed quote', () => {
		assert.ok(scheme.isChildAllowed('th', 'quote'));
	});
					
	it('th allowed code', () => {
		assert.ok(scheme.isChildAllowed('th', 'code'));
	});
					
	it('th allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('th', 'spoiler'));
	});
					
	it('td allowed #text', () => {
		assert.ok(scheme.isChildAllowed('td', '#text'));
	});
					
	it('td allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('td', '#linebreak'));
	});
					
	it('td allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('td', '#inline'));
	});
					
	it('td allowed b', () => {
		assert.ok(scheme.isChildAllowed('td', 'b'));
	});
					
	it('td allowed u', () => {
		assert.ok(scheme.isChildAllowed('td', 'u'));
	});
					
	it('td allowed i', () => {
		assert.ok(scheme.isChildAllowed('td', 'i'));
	});
					
	it('td allowed s', () => {
		assert.ok(scheme.isChildAllowed('td', 's'));
	});
					
	it('td allowed #format', () => {
		assert.ok(scheme.isChildAllowed('td', '#format'));
	});
					
	it('td allowed span', () => {
		assert.ok(scheme.isChildAllowed('td', 'span'));
	});
					
	it('td allowed url', () => {
		assert.ok(scheme.isChildAllowed('td', 'url'));
	});
					
	it('td allowed user', () => {
		assert.ok(scheme.isChildAllowed('td', 'user'));
	});
					
	it('td allowed project', () => {
		assert.ok(scheme.isChildAllowed('td', 'project'));
	});
					
	it('td allowed department', () => {
		assert.ok(scheme.isChildAllowed('td', 'department'));
	});
					
	it('td allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('td', '#mention'));
	});
					
	it('td allowed disk', () => {
		assert.ok(scheme.isChildAllowed('td', 'disk'));
	});
					
	it('td allowed #void', () => {
		assert.ok(scheme.isChildAllowed('td', '#void'));
	});
					
	it('td allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('td', '#inlineBlock'));
	});
					
	it('td allowed img', () => {
		assert.ok(scheme.isChildAllowed('td', 'img'));
	});
					
	it('td allowed video', () => {
		assert.ok(scheme.isChildAllowed('td', 'video'));
	});
					
	it('td allowed #block', () => {
		assert.ok(scheme.isChildAllowed('td', '#block'));
	});
					
	it('td allowed p', () => {
		assert.ok(scheme.isChildAllowed('td', 'p'));
	});
					
	it('td allowed list', () => {
		assert.ok(scheme.isChildAllowed('td', 'list'));
	});
					
	it('td allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('td', '#shadowRoot'));
	});
					
	it('td allowed quote', () => {
		assert.ok(scheme.isChildAllowed('td', 'quote'));
	});
					
	it('td allowed code', () => {
		assert.ok(scheme.isChildAllowed('td', 'code'));
	});
					
	it('td allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('td', 'spoiler'));
	});
					
	it('#shadowRoot allowed #text', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#text'));
	});
					
	it('#shadowRoot allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#linebreak'));
	});
					
	it('#shadowRoot allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#inline'));
	});
					
	it('#shadowRoot allowed b', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'b'));
	});
					
	it('#shadowRoot allowed u', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'u'));
	});
					
	it('#shadowRoot allowed i', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'i'));
	});
					
	it('#shadowRoot allowed s', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 's'));
	});
					
	it('#shadowRoot allowed #format', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#format'));
	});
					
	it('#shadowRoot allowed span', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'span'));
	});
					
	it('#shadowRoot allowed url', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'url'));
	});
					
	it('#shadowRoot allowed user', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'user'));
	});
					
	it('#shadowRoot allowed project', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'project'));
	});
					
	it('#shadowRoot allowed department', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'department'));
	});
					
	it('#shadowRoot allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#mention'));
	});
					
	it('#shadowRoot allowed disk', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'disk'));
	});
					
	it('#shadowRoot allowed #void', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#void'));
	});
					
	it('#shadowRoot allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#inlineBlock'));
	});
					
	it('#shadowRoot allowed img', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'img'));
	});
					
	it('#shadowRoot allowed video', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'video'));
	});
					
	it('#shadowRoot allowed #block', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#block'));
	});
					
	it('#shadowRoot allowed p', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'p'));
	});
					
	it('#shadowRoot allowed list', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'list'));
	});
					
	it('#shadowRoot allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#shadowRoot'));
	});
					
	it('#shadowRoot allowed quote', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'quote'));
	});
					
	it('#shadowRoot allowed code', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'code'));
	});
					
	it('#shadowRoot allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'spoiler'));
	});
					
	it('quote allowed #text', () => {
		assert.ok(scheme.isChildAllowed('quote', '#text'));
	});
					
	it('quote allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('quote', '#linebreak'));
	});
					
	it('quote allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('quote', '#inline'));
	});
					
	it('quote allowed b', () => {
		assert.ok(scheme.isChildAllowed('quote', 'b'));
	});
					
	it('quote allowed u', () => {
		assert.ok(scheme.isChildAllowed('quote', 'u'));
	});
					
	it('quote allowed i', () => {
		assert.ok(scheme.isChildAllowed('quote', 'i'));
	});
					
	it('quote allowed s', () => {
		assert.ok(scheme.isChildAllowed('quote', 's'));
	});
					
	it('quote allowed #format', () => {
		assert.ok(scheme.isChildAllowed('quote', '#format'));
	});
					
	it('quote allowed span', () => {
		assert.ok(scheme.isChildAllowed('quote', 'span'));
	});
					
	it('quote allowed url', () => {
		assert.ok(scheme.isChildAllowed('quote', 'url'));
	});
					
	it('quote allowed user', () => {
		assert.ok(scheme.isChildAllowed('quote', 'user'));
	});
					
	it('quote allowed project', () => {
		assert.ok(scheme.isChildAllowed('quote', 'project'));
	});
					
	it('quote allowed department', () => {
		assert.ok(scheme.isChildAllowed('quote', 'department'));
	});
					
	it('quote allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('quote', '#mention'));
	});
					
	it('quote allowed disk', () => {
		assert.ok(scheme.isChildAllowed('quote', 'disk'));
	});
					
	it('quote allowed #void', () => {
		assert.ok(scheme.isChildAllowed('quote', '#void'));
	});
					
	it('quote allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('quote', '#inlineBlock'));
	});
					
	it('quote allowed img', () => {
		assert.ok(scheme.isChildAllowed('quote', 'img'));
	});
					
	it('quote allowed video', () => {
		assert.ok(scheme.isChildAllowed('quote', 'video'));
	});
					
	it('quote allowed #block', () => {
		assert.ok(scheme.isChildAllowed('quote', '#block'));
	});
					
	it('quote allowed p', () => {
		assert.ok(scheme.isChildAllowed('quote', 'p'));
	});
					
	it('quote allowed list', () => {
		assert.ok(scheme.isChildAllowed('quote', 'list'));
	});
					
	it('quote allowed table', () => {
		assert.ok(scheme.isChildAllowed('quote', 'table'));
	});
					
	it('quote allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('quote', '#shadowRoot'));
	});
					
	it('quote allowed quote', () => {
		assert.ok(scheme.isChildAllowed('quote', 'quote'));
	});
					
	it('quote allowed code', () => {
		assert.ok(scheme.isChildAllowed('quote', 'code'));
	});
					
	it('quote allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('quote', 'spoiler'));
	});
					
	it('video allowed #text', () => {
		assert.ok(scheme.isChildAllowed('video', '#text'));
	});
					
	it('spoiler allowed #text', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#text'));
	});
					
	it('spoiler allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#linebreak'));
	});
					
	it('spoiler allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#inline'));
	});
					
	it('spoiler allowed b', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'b'));
	});
					
	it('spoiler allowed u', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'u'));
	});
					
	it('spoiler allowed i', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'i'));
	});
					
	it('spoiler allowed s', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 's'));
	});
					
	it('spoiler allowed #format', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#format'));
	});
					
	it('spoiler allowed span', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'span'));
	});
					
	it('spoiler allowed url', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'url'));
	});
					
	it('spoiler allowed user', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'user'));
	});
					
	it('spoiler allowed project', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'project'));
	});
					
	it('spoiler allowed department', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'department'));
	});
					
	it('spoiler allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#mention'));
	});
					
	it('spoiler allowed disk', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'disk'));
	});
					
	it('spoiler allowed #void', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#void'));
	});
					
	it('spoiler allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#inlineBlock'));
	});
					
	it('spoiler allowed img', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'img'));
	});
					
	it('spoiler allowed video', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'video'));
	});
					
	it('spoiler allowed #block', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#block'));
	});
					
	it('spoiler allowed p', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'p'));
	});
					
	it('spoiler allowed list', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'list'));
	});
					
	it('spoiler allowed table', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'table'));
	});
					
	it('spoiler allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#shadowRoot'));
	});
					
	it('spoiler allowed quote', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'quote'));
	});
					
	it('spoiler allowed code', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'code'));
	});
					
	it('spoiler allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'spoiler'));
	});
					
	it('user allowed #text', () => {
		assert.ok(scheme.isChildAllowed('user', '#text'));
	});
					
	it('user allowed #format', () => {
		assert.ok(scheme.isChildAllowed('user', '#format'));
	});
					
	it('user allowed b', () => {
		assert.ok(scheme.isChildAllowed('user', 'b'));
	});
					
	it('user allowed u', () => {
		assert.ok(scheme.isChildAllowed('user', 'u'));
	});
					
	it('user allowed i', () => {
		assert.ok(scheme.isChildAllowed('user', 'i'));
	});
					
	it('user allowed s', () => {
		assert.ok(scheme.isChildAllowed('user', 's'));
	});
					
	it('project allowed #text', () => {
		assert.ok(scheme.isChildAllowed('project', '#text'));
	});
					
	it('project allowed #format', () => {
		assert.ok(scheme.isChildAllowed('project', '#format'));
	});
					
	it('project allowed b', () => {
		assert.ok(scheme.isChildAllowed('project', 'b'));
	});
					
	it('project allowed u', () => {
		assert.ok(scheme.isChildAllowed('project', 'u'));
	});
					
	it('project allowed i', () => {
		assert.ok(scheme.isChildAllowed('project', 'i'));
	});
					
	it('project allowed s', () => {
		assert.ok(scheme.isChildAllowed('project', 's'));
	});
					
	it('department allowed #text', () => {
		assert.ok(scheme.isChildAllowed('department', '#text'));
	});
					
	it('department allowed #format', () => {
		assert.ok(scheme.isChildAllowed('department', '#format'));
	});
					
	it('department allowed b', () => {
		assert.ok(scheme.isChildAllowed('department', 'b'));
	});
					
	it('department allowed u', () => {
		assert.ok(scheme.isChildAllowed('department', 'u'));
	});
					
	it('department allowed i', () => {
		assert.ok(scheme.isChildAllowed('department', 'i'));
	});
					
	it('department allowed s', () => {
		assert.ok(scheme.isChildAllowed('department', 's'));
	});
					
	it('#mention allowed #text', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#text'));
	});
					
	it('#mention allowed #format', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#format'));
	});
					
	it('#mention allowed b', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'b'));
	});
					
	it('#mention allowed u', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'u'));
	});
					
	it('#mention allowed i', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'i'));
	});
					
	it('#mention allowed s', () => {
		assert.ok(scheme.isChildAllowed('#mention', 's'));
	});
					
	it('#root allowed b', () => {
		assert.ok(scheme.isChildAllowed('#root', 'b'));
	});
					
	it('#root allowed u', () => {
		assert.ok(scheme.isChildAllowed('#root', 'u'));
	});
					
	it('#root allowed i', () => {
		assert.ok(scheme.isChildAllowed('#root', 'i'));
	});
					
	it('#root allowed s', () => {
		assert.ok(scheme.isChildAllowed('#root', 's'));
	});
					
	it('#root allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('#root', '#inline'));
	});
					
	it('#root allowed #format', () => {
		assert.ok(scheme.isChildAllowed('#root', '#format'));
	});
					
	it('#root allowed span', () => {
		assert.ok(scheme.isChildAllowed('#root', 'span'));
	});
					
	it('#root allowed img', () => {
		assert.ok(scheme.isChildAllowed('#root', 'img'));
	});
					
	it('#root allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('#root', '#inlineBlock'));
	});
					
	it('#root allowed url', () => {
		assert.ok(scheme.isChildAllowed('#root', 'url'));
	});
					
	it('#root allowed p', () => {
		assert.ok(scheme.isChildAllowed('#root', 'p'));
	});
					
	it('#root allowed #block', () => {
		assert.ok(scheme.isChildAllowed('#root', '#block'));
	});
					
	it('#root allowed list', () => {
		assert.ok(scheme.isChildAllowed('#root', 'list'));
	});
					
	it('#root allowed table', () => {
		assert.ok(scheme.isChildAllowed('#root', 'table'));
	});
					
	it('#root allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('#root', '#shadowRoot'));
	});
					
	it('#root allowed quote', () => {
		assert.ok(scheme.isChildAllowed('#root', 'quote'));
	});
					
	it('#root allowed code', () => {
		assert.ok(scheme.isChildAllowed('#root', 'code'));
	});
					
	it('#root allowed video', () => {
		assert.ok(scheme.isChildAllowed('#root', 'video'));
	});
					
	it('#root allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('#root', 'spoiler'));
	});
					
	it('#root allowed user', () => {
		assert.ok(scheme.isChildAllowed('#root', 'user'));
	});
					
	it('#root allowed project', () => {
		assert.ok(scheme.isChildAllowed('#root', 'project'));
	});
					
	it('#root allowed department', () => {
		assert.ok(scheme.isChildAllowed('#root', 'department'));
	});
					
	it('#root allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('#root', '#mention'));
	});
					
	it('#root allowed disk', () => {
		assert.ok(scheme.isChildAllowed('#root', 'disk'));
	});
					
	it('#root allowed #void', () => {
		assert.ok(scheme.isChildAllowed('#root', '#void'));
	});
					
	it('#root allowed #root', () => {
		assert.ok(scheme.isChildAllowed('#root', '#root'));
	});
					
	it('#root allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('#root', '#fragment'));
	});
					
	it('#root allowed #text', () => {
		assert.ok(scheme.isChildAllowed('#root', '#text'));
	});
					
	it('#root allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('#root', '#linebreak'));
	});
					
	it('#root allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('#root', '#tab'));
	});
					
	
	
	it('b not allowed img', () => {
		assert.ok(scheme.isChildAllowed('b', 'img') === false);
	});
					
	it('b not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('b', '#inlineBlock') === false);
	});
					
	it('b not allowed p', () => {
		assert.ok(scheme.isChildAllowed('b', 'p') === false);
	});
					
	it('b not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('b', '#block') === false);
	});
					
	it('b not allowed list', () => {
		assert.ok(scheme.isChildAllowed('b', 'list') === false);
	});
					
	it('b not allowed *', () => {
		assert.ok(scheme.isChildAllowed('b', '*') === false);
	});
					
	it('b not allowed table', () => {
		assert.ok(scheme.isChildAllowed('b', 'table') === false);
	});
					
	it('b not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('b', 'tr') === false);
	});
					
	it('b not allowed th', () => {
		assert.ok(scheme.isChildAllowed('b', 'th') === false);
	});
					
	it('b not allowed td', () => {
		assert.ok(scheme.isChildAllowed('b', 'td') === false);
	});
					
	it('b not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('b', '#shadowRoot') === false);
	});
					
	it('b not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('b', 'quote') === false);
	});
					
	it('b not allowed code', () => {
		assert.ok(scheme.isChildAllowed('b', 'code') === false);
	});
					
	it('b not allowed video', () => {
		assert.ok(scheme.isChildAllowed('b', 'video') === false);
	});
					
	it('b not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('b', 'spoiler') === false);
	});
					
	it('b not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('b', '#root') === false);
	});
					
	it('b not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('b', '#fragment') === false);
	});
					
	it('b not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('b', '#tab') === false);
	});
					
	it('u not allowed img', () => {
		assert.ok(scheme.isChildAllowed('u', 'img') === false);
	});
					
	it('u not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('u', '#inlineBlock') === false);
	});
					
	it('u not allowed p', () => {
		assert.ok(scheme.isChildAllowed('u', 'p') === false);
	});
					
	it('u not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('u', '#block') === false);
	});
					
	it('u not allowed list', () => {
		assert.ok(scheme.isChildAllowed('u', 'list') === false);
	});
					
	it('u not allowed *', () => {
		assert.ok(scheme.isChildAllowed('u', '*') === false);
	});
					
	it('u not allowed table', () => {
		assert.ok(scheme.isChildAllowed('u', 'table') === false);
	});
					
	it('u not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('u', 'tr') === false);
	});
					
	it('u not allowed th', () => {
		assert.ok(scheme.isChildAllowed('u', 'th') === false);
	});
					
	it('u not allowed td', () => {
		assert.ok(scheme.isChildAllowed('u', 'td') === false);
	});
					
	it('u not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('u', '#shadowRoot') === false);
	});
					
	it('u not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('u', 'quote') === false);
	});
					
	it('u not allowed code', () => {
		assert.ok(scheme.isChildAllowed('u', 'code') === false);
	});
					
	it('u not allowed video', () => {
		assert.ok(scheme.isChildAllowed('u', 'video') === false);
	});
					
	it('u not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('u', 'spoiler') === false);
	});
					
	it('u not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('u', '#root') === false);
	});
					
	it('u not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('u', '#fragment') === false);
	});
					
	it('u not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('u', '#tab') === false);
	});
					
	it('i not allowed img', () => {
		assert.ok(scheme.isChildAllowed('i', 'img') === false);
	});
					
	it('i not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('i', '#inlineBlock') === false);
	});
					
	it('i not allowed p', () => {
		assert.ok(scheme.isChildAllowed('i', 'p') === false);
	});
					
	it('i not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('i', '#block') === false);
	});
					
	it('i not allowed list', () => {
		assert.ok(scheme.isChildAllowed('i', 'list') === false);
	});
					
	it('i not allowed *', () => {
		assert.ok(scheme.isChildAllowed('i', '*') === false);
	});
					
	it('i not allowed table', () => {
		assert.ok(scheme.isChildAllowed('i', 'table') === false);
	});
					
	it('i not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('i', 'tr') === false);
	});
					
	it('i not allowed th', () => {
		assert.ok(scheme.isChildAllowed('i', 'th') === false);
	});
					
	it('i not allowed td', () => {
		assert.ok(scheme.isChildAllowed('i', 'td') === false);
	});
					
	it('i not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('i', '#shadowRoot') === false);
	});
					
	it('i not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('i', 'quote') === false);
	});
					
	it('i not allowed code', () => {
		assert.ok(scheme.isChildAllowed('i', 'code') === false);
	});
					
	it('i not allowed video', () => {
		assert.ok(scheme.isChildAllowed('i', 'video') === false);
	});
					
	it('i not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('i', 'spoiler') === false);
	});
					
	it('i not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('i', '#root') === false);
	});
					
	it('i not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('i', '#fragment') === false);
	});
					
	it('i not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('i', '#tab') === false);
	});
					
	it('s not allowed img', () => {
		assert.ok(scheme.isChildAllowed('s', 'img') === false);
	});
					
	it('s not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('s', '#inlineBlock') === false);
	});
					
	it('s not allowed p', () => {
		assert.ok(scheme.isChildAllowed('s', 'p') === false);
	});
					
	it('s not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('s', '#block') === false);
	});
					
	it('s not allowed list', () => {
		assert.ok(scheme.isChildAllowed('s', 'list') === false);
	});
					
	it('s not allowed *', () => {
		assert.ok(scheme.isChildAllowed('s', '*') === false);
	});
					
	it('s not allowed table', () => {
		assert.ok(scheme.isChildAllowed('s', 'table') === false);
	});
					
	it('s not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('s', 'tr') === false);
	});
					
	it('s not allowed th', () => {
		assert.ok(scheme.isChildAllowed('s', 'th') === false);
	});
					
	it('s not allowed td', () => {
		assert.ok(scheme.isChildAllowed('s', 'td') === false);
	});
					
	it('s not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('s', '#shadowRoot') === false);
	});
					
	it('s not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('s', 'quote') === false);
	});
					
	it('s not allowed code', () => {
		assert.ok(scheme.isChildAllowed('s', 'code') === false);
	});
					
	it('s not allowed video', () => {
		assert.ok(scheme.isChildAllowed('s', 'video') === false);
	});
					
	it('s not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('s', 'spoiler') === false);
	});
					
	it('s not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('s', '#root') === false);
	});
					
	it('s not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('s', '#fragment') === false);
	});
					
	it('s not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('s', '#tab') === false);
	});
					
	it('#inline not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#inlineBlock') === false);
	});
					
	it('#inline not allowed p', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'p') === false);
	});
					
	it('#inline not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#block') === false);
	});
					
	it('#inline not allowed list', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'list') === false);
	});
					
	it('#inline not allowed *', () => {
		assert.ok(scheme.isChildAllowed('#inline', '*') === false);
	});
					
	it('#inline not allowed table', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'table') === false);
	});
					
	it('#inline not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'tr') === false);
	});
					
	it('#inline not allowed th', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'th') === false);
	});
					
	it('#inline not allowed td', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'td') === false);
	});
					
	it('#inline not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#shadowRoot') === false);
	});
					
	it('#inline not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'quote') === false);
	});
					
	it('#inline not allowed code', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'code') === false);
	});
					
	it('#inline not allowed video', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'video') === false);
	});
					
	it('#inline not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('#inline', 'spoiler') === false);
	});
					
	it('#inline not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#root') === false);
	});
					
	it('#inline not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#fragment') === false);
	});
					
	it('#inline not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('#inline', '#tab') === false);
	});
					
	it('#format not allowed img', () => {
		assert.ok(scheme.isChildAllowed('#format', 'img') === false);
	});
					
	it('#format not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('#format', '#inlineBlock') === false);
	});
					
	it('#format not allowed p', () => {
		assert.ok(scheme.isChildAllowed('#format', 'p') === false);
	});
					
	it('#format not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('#format', '#block') === false);
	});
					
	it('#format not allowed list', () => {
		assert.ok(scheme.isChildAllowed('#format', 'list') === false);
	});
					
	it('#format not allowed *', () => {
		assert.ok(scheme.isChildAllowed('#format', '*') === false);
	});
					
	it('#format not allowed table', () => {
		assert.ok(scheme.isChildAllowed('#format', 'table') === false);
	});
					
	it('#format not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('#format', 'tr') === false);
	});
					
	it('#format not allowed th', () => {
		assert.ok(scheme.isChildAllowed('#format', 'th') === false);
	});
					
	it('#format not allowed td', () => {
		assert.ok(scheme.isChildAllowed('#format', 'td') === false);
	});
					
	it('#format not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('#format', '#shadowRoot') === false);
	});
					
	it('#format not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('#format', 'quote') === false);
	});
					
	it('#format not allowed code', () => {
		assert.ok(scheme.isChildAllowed('#format', 'code') === false);
	});
					
	it('#format not allowed video', () => {
		assert.ok(scheme.isChildAllowed('#format', 'video') === false);
	});
					
	it('#format not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('#format', 'spoiler') === false);
	});
					
	it('#format not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('#format', '#root') === false);
	});
					
	it('#format not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('#format', '#fragment') === false);
	});
					
	it('#format not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('#format', '#tab') === false);
	});
					
	it('span not allowed img', () => {
		assert.ok(scheme.isChildAllowed('span', 'img') === false);
	});
					
	it('span not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('span', '#inlineBlock') === false);
	});
					
	it('span not allowed p', () => {
		assert.ok(scheme.isChildAllowed('span', 'p') === false);
	});
					
	it('span not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('span', '#block') === false);
	});
					
	it('span not allowed list', () => {
		assert.ok(scheme.isChildAllowed('span', 'list') === false);
	});
					
	it('span not allowed *', () => {
		assert.ok(scheme.isChildAllowed('span', '*') === false);
	});
					
	it('span not allowed table', () => {
		assert.ok(scheme.isChildAllowed('span', 'table') === false);
	});
					
	it('span not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('span', 'tr') === false);
	});
					
	it('span not allowed th', () => {
		assert.ok(scheme.isChildAllowed('span', 'th') === false);
	});
					
	it('span not allowed td', () => {
		assert.ok(scheme.isChildAllowed('span', 'td') === false);
	});
					
	it('span not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('span', '#shadowRoot') === false);
	});
					
	it('span not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('span', 'quote') === false);
	});
					
	it('span not allowed code', () => {
		assert.ok(scheme.isChildAllowed('span', 'code') === false);
	});
					
	it('span not allowed video', () => {
		assert.ok(scheme.isChildAllowed('span', 'video') === false);
	});
					
	it('span not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('span', 'spoiler') === false);
	});
					
	it('span not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('span', '#root') === false);
	});
					
	it('span not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('span', '#fragment') === false);
	});
					
	it('span not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('span', '#tab') === false);
	});
					
	it('img not allowed b', () => {
		assert.ok(scheme.isChildAllowed('img', 'b') === false);
	});
					
	it('img not allowed u', () => {
		assert.ok(scheme.isChildAllowed('img', 'u') === false);
	});
					
	it('img not allowed i', () => {
		assert.ok(scheme.isChildAllowed('img', 'i') === false);
	});
					
	it('img not allowed s', () => {
		assert.ok(scheme.isChildAllowed('img', 's') === false);
	});
					
	it('img not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('img', '#inline') === false);
	});
					
	it('img not allowed #format', () => {
		assert.ok(scheme.isChildAllowed('img', '#format') === false);
	});
					
	it('img not allowed span', () => {
		assert.ok(scheme.isChildAllowed('img', 'span') === false);
	});
					
	it('img not allowed img', () => {
		assert.ok(scheme.isChildAllowed('img', 'img') === false);
	});
					
	it('img not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('img', '#inlineBlock') === false);
	});
					
	it('img not allowed url', () => {
		assert.ok(scheme.isChildAllowed('img', 'url') === false);
	});
					
	it('img not allowed p', () => {
		assert.ok(scheme.isChildAllowed('img', 'p') === false);
	});
					
	it('img not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('img', '#block') === false);
	});
					
	it('img not allowed list', () => {
		assert.ok(scheme.isChildAllowed('img', 'list') === false);
	});
					
	it('img not allowed *', () => {
		assert.ok(scheme.isChildAllowed('img', '*') === false);
	});
					
	it('img not allowed table', () => {
		assert.ok(scheme.isChildAllowed('img', 'table') === false);
	});
					
	it('img not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('img', 'tr') === false);
	});
					
	it('img not allowed th', () => {
		assert.ok(scheme.isChildAllowed('img', 'th') === false);
	});
					
	it('img not allowed td', () => {
		assert.ok(scheme.isChildAllowed('img', 'td') === false);
	});
					
	it('img not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('img', '#shadowRoot') === false);
	});
					
	it('img not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('img', 'quote') === false);
	});
					
	it('img not allowed code', () => {
		assert.ok(scheme.isChildAllowed('img', 'code') === false);
	});
					
	it('img not allowed video', () => {
		assert.ok(scheme.isChildAllowed('img', 'video') === false);
	});
					
	it('img not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('img', 'spoiler') === false);
	});
					
	it('img not allowed user', () => {
		assert.ok(scheme.isChildAllowed('img', 'user') === false);
	});
					
	it('img not allowed project', () => {
		assert.ok(scheme.isChildAllowed('img', 'project') === false);
	});
					
	it('img not allowed department', () => {
		assert.ok(scheme.isChildAllowed('img', 'department') === false);
	});
					
	it('img not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('img', '#mention') === false);
	});
					
	it('img not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('img', 'disk') === false);
	});
					
	it('img not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('img', '#void') === false);
	});
					
	it('img not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('img', '#root') === false);
	});
					
	it('img not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('img', '#fragment') === false);
	});
					
	it('img not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('img', '#linebreak') === false);
	});
					
	it('img not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('img', '#tab') === false);
	});
					
	it('#inlineBlock not allowed b', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'b') === false);
	});
					
	it('#inlineBlock not allowed u', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'u') === false);
	});
					
	it('#inlineBlock not allowed i', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'i') === false);
	});
					
	it('#inlineBlock not allowed s', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 's') === false);
	});
					
	it('#inlineBlock not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#inline') === false);
	});
					
	it('#inlineBlock not allowed #format', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#format') === false);
	});
					
	it('#inlineBlock not allowed span', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'span') === false);
	});
					
	it('#inlineBlock not allowed img', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'img') === false);
	});
					
	it('#inlineBlock not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#inlineBlock') === false);
	});
					
	it('#inlineBlock not allowed url', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'url') === false);
	});
					
	it('#inlineBlock not allowed p', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'p') === false);
	});
					
	it('#inlineBlock not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#block') === false);
	});
					
	it('#inlineBlock not allowed list', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'list') === false);
	});
					
	it('#inlineBlock not allowed *', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '*') === false);
	});
					
	it('#inlineBlock not allowed table', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'table') === false);
	});
					
	it('#inlineBlock not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'tr') === false);
	});
					
	it('#inlineBlock not allowed th', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'th') === false);
	});
					
	it('#inlineBlock not allowed td', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'td') === false);
	});
					
	it('#inlineBlock not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#shadowRoot') === false);
	});
					
	it('#inlineBlock not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'quote') === false);
	});
					
	it('#inlineBlock not allowed code', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'code') === false);
	});
					
	it('#inlineBlock not allowed video', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'video') === false);
	});
					
	it('#inlineBlock not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'spoiler') === false);
	});
					
	it('#inlineBlock not allowed user', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'user') === false);
	});
					
	it('#inlineBlock not allowed project', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'project') === false);
	});
					
	it('#inlineBlock not allowed department', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'department') === false);
	});
					
	it('#inlineBlock not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#mention') === false);
	});
					
	it('#inlineBlock not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', 'disk') === false);
	});
					
	it('#inlineBlock not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#void') === false);
	});
					
	it('#inlineBlock not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#root') === false);
	});
					
	it('#inlineBlock not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#fragment') === false);
	});
					
	it('#inlineBlock not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#linebreak') === false);
	});
					
	it('#inlineBlock not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('#inlineBlock', '#tab') === false);
	});
					
	it('url not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('url', '#inline') === false);
	});
					
	it('url not allowed span', () => {
		assert.ok(scheme.isChildAllowed('url', 'span') === false);
	});
					
	it('url not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('url', '#inlineBlock') === false);
	});
					
	it('url not allowed url', () => {
		assert.ok(scheme.isChildAllowed('url', 'url') === false);
	});
					
	it('url not allowed p', () => {
		assert.ok(scheme.isChildAllowed('url', 'p') === false);
	});
					
	it('url not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('url', '#block') === false);
	});
					
	it('url not allowed list', () => {
		assert.ok(scheme.isChildAllowed('url', 'list') === false);
	});
					
	it('url not allowed *', () => {
		assert.ok(scheme.isChildAllowed('url', '*') === false);
	});
					
	it('url not allowed table', () => {
		assert.ok(scheme.isChildAllowed('url', 'table') === false);
	});
					
	it('url not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('url', 'tr') === false);
	});
					
	it('url not allowed th', () => {
		assert.ok(scheme.isChildAllowed('url', 'th') === false);
	});
					
	it('url not allowed td', () => {
		assert.ok(scheme.isChildAllowed('url', 'td') === false);
	});
					
	it('url not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('url', '#shadowRoot') === false);
	});
					
	it('url not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('url', 'quote') === false);
	});
					
	it('url not allowed code', () => {
		assert.ok(scheme.isChildAllowed('url', 'code') === false);
	});
					
	it('url not allowed video', () => {
		assert.ok(scheme.isChildAllowed('url', 'video') === false);
	});
					
	it('url not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('url', 'spoiler') === false);
	});
					
	it('url not allowed user', () => {
		assert.ok(scheme.isChildAllowed('url', 'user') === false);
	});
					
	it('url not allowed project', () => {
		assert.ok(scheme.isChildAllowed('url', 'project') === false);
	});
					
	it('url not allowed department', () => {
		assert.ok(scheme.isChildAllowed('url', 'department') === false);
	});
					
	it('url not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('url', '#mention') === false);
	});
					
	it('url not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('url', 'disk') === false);
	});
					
	it('url not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('url', '#void') === false);
	});
					
	it('url not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('url', '#root') === false);
	});
					
	it('url not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('url', '#fragment') === false);
	});
					
	it('url not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('url', '#linebreak') === false);
	});
					
	it('url not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('url', '#tab') === false);
	});
					
	it('p not allowed p', () => {
		assert.ok(scheme.isChildAllowed('p', 'p') === false);
	});
					
	it('p not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('p', '#block') === false);
	});
					
	it('p not allowed list', () => {
		assert.ok(scheme.isChildAllowed('p', 'list') === false);
	});
					
	it('p not allowed *', () => {
		assert.ok(scheme.isChildAllowed('p', '*') === false);
	});
					
	it('p not allowed table', () => {
		assert.ok(scheme.isChildAllowed('p', 'table') === false);
	});
					
	it('p not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('p', 'tr') === false);
	});
					
	it('p not allowed th', () => {
		assert.ok(scheme.isChildAllowed('p', 'th') === false);
	});
					
	it('p not allowed td', () => {
		assert.ok(scheme.isChildAllowed('p', 'td') === false);
	});
					
	it('p not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('p', '#shadowRoot') === false);
	});
					
	it('p not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('p', 'quote') === false);
	});
					
	it('p not allowed code', () => {
		assert.ok(scheme.isChildAllowed('p', 'code') === false);
	});
					
	it('p not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('p', 'spoiler') === false);
	});
					
	it('p not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('p', '#root') === false);
	});
					
	it('p not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('p', '#fragment') === false);
	});
					
	it('p not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('p', '#tab') === false);
	});
					
	it('#block not allowed *', () => {
		assert.ok(scheme.isChildAllowed('#block', '*') === false);
	});
					
	it('#block not allowed table', () => {
		assert.ok(scheme.isChildAllowed('#block', 'table') === false);
	});
					
	it('#block not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('#block', 'tr') === false);
	});
					
	it('#block not allowed th', () => {
		assert.ok(scheme.isChildAllowed('#block', 'th') === false);
	});
					
	it('#block not allowed td', () => {
		assert.ok(scheme.isChildAllowed('#block', 'td') === false);
	});
					
	it('#block not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('#block', '#root') === false);
	});
					
	it('#block not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('#block', '#fragment') === false);
	});
					
	it('list not allowed b', () => {
		assert.ok(scheme.isChildAllowed('list', 'b') === false);
	});
					
	it('list not allowed u', () => {
		assert.ok(scheme.isChildAllowed('list', 'u') === false);
	});
					
	it('list not allowed i', () => {
		assert.ok(scheme.isChildAllowed('list', 'i') === false);
	});
					
	it('list not allowed s', () => {
		assert.ok(scheme.isChildAllowed('list', 's') === false);
	});
					
	it('list not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('list', '#inline') === false);
	});
					
	it('list not allowed #format', () => {
		assert.ok(scheme.isChildAllowed('list', '#format') === false);
	});
					
	it('list not allowed span', () => {
		assert.ok(scheme.isChildAllowed('list', 'span') === false);
	});
					
	it('list not allowed img', () => {
		assert.ok(scheme.isChildAllowed('list', 'img') === false);
	});
					
	it('list not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('list', '#inlineBlock') === false);
	});
					
	it('list not allowed url', () => {
		assert.ok(scheme.isChildAllowed('list', 'url') === false);
	});
					
	it('list not allowed p', () => {
		assert.ok(scheme.isChildAllowed('list', 'p') === false);
	});
					
	it('list not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('list', '#block') === false);
	});
					
	it('list not allowed list', () => {
		assert.ok(scheme.isChildAllowed('list', 'list') === false);
	});
					
	it('list not allowed table', () => {
		assert.ok(scheme.isChildAllowed('list', 'table') === false);
	});
					
	it('list not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('list', 'tr') === false);
	});
					
	it('list not allowed th', () => {
		assert.ok(scheme.isChildAllowed('list', 'th') === false);
	});
					
	it('list not allowed td', () => {
		assert.ok(scheme.isChildAllowed('list', 'td') === false);
	});
					
	it('list not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('list', '#shadowRoot') === false);
	});
					
	it('list not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('list', 'quote') === false);
	});
					
	it('list not allowed code', () => {
		assert.ok(scheme.isChildAllowed('list', 'code') === false);
	});
					
	it('list not allowed video', () => {
		assert.ok(scheme.isChildAllowed('list', 'video') === false);
	});
					
	it('list not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('list', 'spoiler') === false);
	});
					
	it('list not allowed user', () => {
		assert.ok(scheme.isChildAllowed('list', 'user') === false);
	});
					
	it('list not allowed project', () => {
		assert.ok(scheme.isChildAllowed('list', 'project') === false);
	});
					
	it('list not allowed department', () => {
		assert.ok(scheme.isChildAllowed('list', 'department') === false);
	});
					
	it('list not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('list', '#mention') === false);
	});
					
	it('list not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('list', 'disk') === false);
	});
					
	it('list not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('list', '#void') === false);
	});
					
	it('list not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('list', '#root') === false);
	});
					
	it('list not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('list', '#fragment') === false);
	});
					
	it('list not allowed #text', () => {
		assert.ok(scheme.isChildAllowed('list', '#text') === false);
	});
					
	it('list not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('list', '#linebreak') === false);
	});
					
	it('list not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('list', '#tab') === false);
	});
					
	it('* not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('*', '#inlineBlock') === false);
	});
					
	it('* not allowed p', () => {
		assert.ok(scheme.isChildAllowed('*', 'p') === false);
	});
					
	it('* not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('*', '#block') === false);
	});
					
	it('* not allowed list', () => {
		assert.ok(scheme.isChildAllowed('*', 'list') === false);
	});
					
	it('* not allowed *', () => {
		assert.ok(scheme.isChildAllowed('*', '*') === false);
	});
					
	it('* not allowed table', () => {
		assert.ok(scheme.isChildAllowed('*', 'table') === false);
	});
					
	it('* not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('*', 'tr') === false);
	});
					
	it('* not allowed th', () => {
		assert.ok(scheme.isChildAllowed('*', 'th') === false);
	});
					
	it('* not allowed td', () => {
		assert.ok(scheme.isChildAllowed('*', 'td') === false);
	});
					
	it('* not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('*', '#shadowRoot') === false);
	});
					
	it('* not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('*', 'quote') === false);
	});
					
	it('* not allowed code', () => {
		assert.ok(scheme.isChildAllowed('*', 'code') === false);
	});
					
	it('* not allowed video', () => {
		assert.ok(scheme.isChildAllowed('*', 'video') === false);
	});
					
	it('* not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('*', 'spoiler') === false);
	});
					
	it('* not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('*', '#root') === false);
	});
					
	it('* not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('*', '#fragment') === false);
	});
					
	it('* not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('*', '#tab') === false);
	});
					
	it('table not allowed b', () => {
		assert.ok(scheme.isChildAllowed('table', 'b') === false);
	});
					
	it('table not allowed u', () => {
		assert.ok(scheme.isChildAllowed('table', 'u') === false);
	});
					
	it('table not allowed i', () => {
		assert.ok(scheme.isChildAllowed('table', 'i') === false);
	});
					
	it('table not allowed s', () => {
		assert.ok(scheme.isChildAllowed('table', 's') === false);
	});
					
	it('table not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('table', '#inline') === false);
	});
					
	it('table not allowed #format', () => {
		assert.ok(scheme.isChildAllowed('table', '#format') === false);
	});
					
	it('table not allowed span', () => {
		assert.ok(scheme.isChildAllowed('table', 'span') === false);
	});
					
	it('table not allowed img', () => {
		assert.ok(scheme.isChildAllowed('table', 'img') === false);
	});
					
	it('table not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('table', '#inlineBlock') === false);
	});
					
	it('table not allowed url', () => {
		assert.ok(scheme.isChildAllowed('table', 'url') === false);
	});
					
	it('table not allowed p', () => {
		assert.ok(scheme.isChildAllowed('table', 'p') === false);
	});
					
	it('table not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('table', '#block') === false);
	});
					
	it('table not allowed list', () => {
		assert.ok(scheme.isChildAllowed('table', 'list') === false);
	});
					
	it('table not allowed *', () => {
		assert.ok(scheme.isChildAllowed('table', '*') === false);
	});
					
	it('table not allowed table', () => {
		assert.ok(scheme.isChildAllowed('table', 'table') === false);
	});
					
	it('table not allowed th', () => {
		assert.ok(scheme.isChildAllowed('table', 'th') === false);
	});
					
	it('table not allowed td', () => {
		assert.ok(scheme.isChildAllowed('table', 'td') === false);
	});
					
	it('table not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('table', '#shadowRoot') === false);
	});
					
	it('table not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('table', 'quote') === false);
	});
					
	it('table not allowed code', () => {
		assert.ok(scheme.isChildAllowed('table', 'code') === false);
	});
					
	it('table not allowed video', () => {
		assert.ok(scheme.isChildAllowed('table', 'video') === false);
	});
					
	it('table not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('table', 'spoiler') === false);
	});
					
	it('table not allowed user', () => {
		assert.ok(scheme.isChildAllowed('table', 'user') === false);
	});
					
	it('table not allowed project', () => {
		assert.ok(scheme.isChildAllowed('table', 'project') === false);
	});
					
	it('table not allowed department', () => {
		assert.ok(scheme.isChildAllowed('table', 'department') === false);
	});
					
	it('table not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('table', '#mention') === false);
	});
					
	it('table not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('table', 'disk') === false);
	});
					
	it('table not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('table', '#void') === false);
	});
					
	it('table not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('table', '#root') === false);
	});
					
	it('table not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('table', '#fragment') === false);
	});
					
	it('table not allowed #text', () => {
		assert.ok(scheme.isChildAllowed('table', '#text') === false);
	});
					
	it('table not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('table', '#linebreak') === false);
	});
					
	it('table not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('table', '#tab') === false);
	});
					
	it('tr not allowed b', () => {
		assert.ok(scheme.isChildAllowed('tr', 'b') === false);
	});
					
	it('tr not allowed u', () => {
		assert.ok(scheme.isChildAllowed('tr', 'u') === false);
	});
					
	it('tr not allowed i', () => {
		assert.ok(scheme.isChildAllowed('tr', 'i') === false);
	});
					
	it('tr not allowed s', () => {
		assert.ok(scheme.isChildAllowed('tr', 's') === false);
	});
					
	it('tr not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('tr', '#inline') === false);
	});
					
	it('tr not allowed #format', () => {
		assert.ok(scheme.isChildAllowed('tr', '#format') === false);
	});
					
	it('tr not allowed span', () => {
		assert.ok(scheme.isChildAllowed('tr', 'span') === false);
	});
					
	it('tr not allowed img', () => {
		assert.ok(scheme.isChildAllowed('tr', 'img') === false);
	});
					
	it('tr not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('tr', '#inlineBlock') === false);
	});
					
	it('tr not allowed url', () => {
		assert.ok(scheme.isChildAllowed('tr', 'url') === false);
	});
					
	it('tr not allowed p', () => {
		assert.ok(scheme.isChildAllowed('tr', 'p') === false);
	});
					
	it('tr not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('tr', '#block') === false);
	});
					
	it('tr not allowed list', () => {
		assert.ok(scheme.isChildAllowed('tr', 'list') === false);
	});
					
	it('tr not allowed *', () => {
		assert.ok(scheme.isChildAllowed('tr', '*') === false);
	});
					
	it('tr not allowed table', () => {
		assert.ok(scheme.isChildAllowed('tr', 'table') === false);
	});
					
	it('tr not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('tr', 'tr') === false);
	});
					
	it('tr not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('tr', '#shadowRoot') === false);
	});
					
	it('tr not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('tr', 'quote') === false);
	});
					
	it('tr not allowed code', () => {
		assert.ok(scheme.isChildAllowed('tr', 'code') === false);
	});
					
	it('tr not allowed video', () => {
		assert.ok(scheme.isChildAllowed('tr', 'video') === false);
	});
					
	it('tr not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('tr', 'spoiler') === false);
	});
					
	it('tr not allowed user', () => {
		assert.ok(scheme.isChildAllowed('tr', 'user') === false);
	});
					
	it('tr not allowed project', () => {
		assert.ok(scheme.isChildAllowed('tr', 'project') === false);
	});
					
	it('tr not allowed department', () => {
		assert.ok(scheme.isChildAllowed('tr', 'department') === false);
	});
					
	it('tr not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('tr', '#mention') === false);
	});
					
	it('tr not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('tr', 'disk') === false);
	});
					
	it('tr not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('tr', '#void') === false);
	});
					
	it('tr not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('tr', '#root') === false);
	});
					
	it('tr not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('tr', '#fragment') === false);
	});
					
	it('tr not allowed #text', () => {
		assert.ok(scheme.isChildAllowed('tr', '#text') === false);
	});
					
	it('tr not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('tr', '#linebreak') === false);
	});
					
	it('tr not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('tr', '#tab') === false);
	});
					
	it('th not allowed *', () => {
		assert.ok(scheme.isChildAllowed('th', '*') === false);
	});
					
	it('th not allowed table', () => {
		assert.ok(scheme.isChildAllowed('th', 'table') === false);
	});
					
	it('th not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('th', 'tr') === false);
	});
					
	it('th not allowed th', () => {
		assert.ok(scheme.isChildAllowed('th', 'th') === false);
	});
					
	it('th not allowed td', () => {
		assert.ok(scheme.isChildAllowed('th', 'td') === false);
	});
					
	it('th not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('th', '#root') === false);
	});
					
	it('th not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('th', '#fragment') === false);
	});
					
	it('th not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('th', '#tab') === false);
	});
					
	it('td not allowed *', () => {
		assert.ok(scheme.isChildAllowed('td', '*') === false);
	});
					
	it('td not allowed table', () => {
		assert.ok(scheme.isChildAllowed('td', 'table') === false);
	});
					
	it('td not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('td', 'tr') === false);
	});
					
	it('td not allowed th', () => {
		assert.ok(scheme.isChildAllowed('td', 'th') === false);
	});
					
	it('td not allowed td', () => {
		assert.ok(scheme.isChildAllowed('td', 'td') === false);
	});
					
	it('td not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('td', '#root') === false);
	});
					
	it('td not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('td', '#fragment') === false);
	});
					
	it('td not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('td', '#tab') === false);
	});
					
	it('#shadowRoot not allowed *', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '*') === false);
	});
					
	it('#shadowRoot not allowed table', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'table') === false);
	});
					
	it('#shadowRoot not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'tr') === false);
	});
					
	it('#shadowRoot not allowed th', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'th') === false);
	});
					
	it('#shadowRoot not allowed td', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', 'td') === false);
	});
					
	it('#shadowRoot not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#root') === false);
	});
					
	it('#shadowRoot not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#fragment') === false);
	});
					
	it('#shadowRoot not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('#shadowRoot', '#tab') === false);
	});
					
	it('quote not allowed *', () => {
		assert.ok(scheme.isChildAllowed('quote', '*') === false);
	});
					
	it('quote not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('quote', 'tr') === false);
	});
					
	it('quote not allowed th', () => {
		assert.ok(scheme.isChildAllowed('quote', 'th') === false);
	});
					
	it('quote not allowed td', () => {
		assert.ok(scheme.isChildAllowed('quote', 'td') === false);
	});
					
	it('quote not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('quote', '#root') === false);
	});
					
	it('quote not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('quote', '#fragment') === false);
	});
					
	it('quote not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('quote', '#tab') === false);
	});
					
	it('video not allowed b', () => {
		assert.ok(scheme.isChildAllowed('video', 'b') === false);
	});
					
	it('video not allowed u', () => {
		assert.ok(scheme.isChildAllowed('video', 'u') === false);
	});
					
	it('video not allowed i', () => {
		assert.ok(scheme.isChildAllowed('video', 'i') === false);
	});
					
	it('video not allowed s', () => {
		assert.ok(scheme.isChildAllowed('video', 's') === false);
	});
					
	it('video not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('video', '#inline') === false);
	});
					
	it('video not allowed #format', () => {
		assert.ok(scheme.isChildAllowed('video', '#format') === false);
	});
					
	it('video not allowed span', () => {
		assert.ok(scheme.isChildAllowed('video', 'span') === false);
	});
					
	it('video not allowed img', () => {
		assert.ok(scheme.isChildAllowed('video', 'img') === false);
	});
					
	it('video not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('video', '#inlineBlock') === false);
	});
					
	it('video not allowed url', () => {
		assert.ok(scheme.isChildAllowed('video', 'url') === false);
	});
					
	it('video not allowed p', () => {
		assert.ok(scheme.isChildAllowed('video', 'p') === false);
	});
					
	it('video not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('video', '#block') === false);
	});
					
	it('video not allowed list', () => {
		assert.ok(scheme.isChildAllowed('video', 'list') === false);
	});
					
	it('video not allowed *', () => {
		assert.ok(scheme.isChildAllowed('video', '*') === false);
	});
					
	it('video not allowed table', () => {
		assert.ok(scheme.isChildAllowed('video', 'table') === false);
	});
					
	it('video not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('video', 'tr') === false);
	});
					
	it('video not allowed th', () => {
		assert.ok(scheme.isChildAllowed('video', 'th') === false);
	});
					
	it('video not allowed td', () => {
		assert.ok(scheme.isChildAllowed('video', 'td') === false);
	});
					
	it('video not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('video', '#shadowRoot') === false);
	});
					
	it('video not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('video', 'quote') === false);
	});
					
	it('video not allowed code', () => {
		assert.ok(scheme.isChildAllowed('video', 'code') === false);
	});
					
	it('video not allowed video', () => {
		assert.ok(scheme.isChildAllowed('video', 'video') === false);
	});
					
	it('video not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('video', 'spoiler') === false);
	});
					
	it('video not allowed user', () => {
		assert.ok(scheme.isChildAllowed('video', 'user') === false);
	});
					
	it('video not allowed project', () => {
		assert.ok(scheme.isChildAllowed('video', 'project') === false);
	});
					
	it('video not allowed department', () => {
		assert.ok(scheme.isChildAllowed('video', 'department') === false);
	});
					
	it('video not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('video', '#mention') === false);
	});
					
	it('video not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('video', 'disk') === false);
	});
					
	it('video not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('video', '#void') === false);
	});
					
	it('video not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('video', '#root') === false);
	});
					
	it('video not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('video', '#fragment') === false);
	});
					
	it('video not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('video', '#linebreak') === false);
	});
					
	it('video not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('video', '#tab') === false);
	});
					
	it('spoiler not allowed *', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '*') === false);
	});
					
	it('spoiler not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'tr') === false);
	});
					
	it('spoiler not allowed th', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'th') === false);
	});
					
	it('spoiler not allowed td', () => {
		assert.ok(scheme.isChildAllowed('spoiler', 'td') === false);
	});
					
	it('spoiler not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#root') === false);
	});
					
	it('spoiler not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#fragment') === false);
	});
					
	it('spoiler not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('spoiler', '#tab') === false);
	});
					
	it('user not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('user', '#inline') === false);
	});
					
	it('user not allowed span', () => {
		assert.ok(scheme.isChildAllowed('user', 'span') === false);
	});
					
	it('user not allowed img', () => {
		assert.ok(scheme.isChildAllowed('user', 'img') === false);
	});
					
	it('user not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('user', '#inlineBlock') === false);
	});
					
	it('user not allowed url', () => {
		assert.ok(scheme.isChildAllowed('user', 'url') === false);
	});
					
	it('user not allowed p', () => {
		assert.ok(scheme.isChildAllowed('user', 'p') === false);
	});
					
	it('user not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('user', '#block') === false);
	});
					
	it('user not allowed list', () => {
		assert.ok(scheme.isChildAllowed('user', 'list') === false);
	});
					
	it('user not allowed *', () => {
		assert.ok(scheme.isChildAllowed('user', '*') === false);
	});
					
	it('user not allowed table', () => {
		assert.ok(scheme.isChildAllowed('user', 'table') === false);
	});
					
	it('user not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('user', 'tr') === false);
	});
					
	it('user not allowed th', () => {
		assert.ok(scheme.isChildAllowed('user', 'th') === false);
	});
					
	it('user not allowed td', () => {
		assert.ok(scheme.isChildAllowed('user', 'td') === false);
	});
					
	it('user not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('user', '#shadowRoot') === false);
	});
					
	it('user not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('user', 'quote') === false);
	});
					
	it('user not allowed code', () => {
		assert.ok(scheme.isChildAllowed('user', 'code') === false);
	});
					
	it('user not allowed video', () => {
		assert.ok(scheme.isChildAllowed('user', 'video') === false);
	});
					
	it('user not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('user', 'spoiler') === false);
	});
					
	it('user not allowed user', () => {
		assert.ok(scheme.isChildAllowed('user', 'user') === false);
	});
					
	it('user not allowed project', () => {
		assert.ok(scheme.isChildAllowed('user', 'project') === false);
	});
					
	it('user not allowed department', () => {
		assert.ok(scheme.isChildAllowed('user', 'department') === false);
	});
					
	it('user not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('user', '#mention') === false);
	});
					
	it('user not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('user', 'disk') === false);
	});
					
	it('user not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('user', '#void') === false);
	});
					
	it('user not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('user', '#root') === false);
	});
					
	it('user not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('user', '#fragment') === false);
	});
					
	it('user not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('user', '#linebreak') === false);
	});
					
	it('user not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('user', '#tab') === false);
	});
					
	it('project not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('project', '#inline') === false);
	});
					
	it('project not allowed span', () => {
		assert.ok(scheme.isChildAllowed('project', 'span') === false);
	});
					
	it('project not allowed img', () => {
		assert.ok(scheme.isChildAllowed('project', 'img') === false);
	});
					
	it('project not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('project', '#inlineBlock') === false);
	});
					
	it('project not allowed url', () => {
		assert.ok(scheme.isChildAllowed('project', 'url') === false);
	});
					
	it('project not allowed p', () => {
		assert.ok(scheme.isChildAllowed('project', 'p') === false);
	});
					
	it('project not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('project', '#block') === false);
	});
					
	it('project not allowed list', () => {
		assert.ok(scheme.isChildAllowed('project', 'list') === false);
	});
					
	it('project not allowed *', () => {
		assert.ok(scheme.isChildAllowed('project', '*') === false);
	});
					
	it('project not allowed table', () => {
		assert.ok(scheme.isChildAllowed('project', 'table') === false);
	});
					
	it('project not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('project', 'tr') === false);
	});
					
	it('project not allowed th', () => {
		assert.ok(scheme.isChildAllowed('project', 'th') === false);
	});
					
	it('project not allowed td', () => {
		assert.ok(scheme.isChildAllowed('project', 'td') === false);
	});
					
	it('project not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('project', '#shadowRoot') === false);
	});
					
	it('project not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('project', 'quote') === false);
	});
					
	it('project not allowed code', () => {
		assert.ok(scheme.isChildAllowed('project', 'code') === false);
	});
					
	it('project not allowed video', () => {
		assert.ok(scheme.isChildAllowed('project', 'video') === false);
	});
					
	it('project not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('project', 'spoiler') === false);
	});
					
	it('project not allowed user', () => {
		assert.ok(scheme.isChildAllowed('project', 'user') === false);
	});
					
	it('project not allowed project', () => {
		assert.ok(scheme.isChildAllowed('project', 'project') === false);
	});
					
	it('project not allowed department', () => {
		assert.ok(scheme.isChildAllowed('project', 'department') === false);
	});
					
	it('project not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('project', '#mention') === false);
	});
					
	it('project not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('project', 'disk') === false);
	});
					
	it('project not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('project', '#void') === false);
	});
					
	it('project not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('project', '#root') === false);
	});
					
	it('project not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('project', '#fragment') === false);
	});
					
	it('project not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('project', '#linebreak') === false);
	});
					
	it('project not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('project', '#tab') === false);
	});
					
	it('department not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('department', '#inline') === false);
	});
					
	it('department not allowed span', () => {
		assert.ok(scheme.isChildAllowed('department', 'span') === false);
	});
					
	it('department not allowed img', () => {
		assert.ok(scheme.isChildAllowed('department', 'img') === false);
	});
					
	it('department not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('department', '#inlineBlock') === false);
	});
					
	it('department not allowed url', () => {
		assert.ok(scheme.isChildAllowed('department', 'url') === false);
	});
					
	it('department not allowed p', () => {
		assert.ok(scheme.isChildAllowed('department', 'p') === false);
	});
					
	it('department not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('department', '#block') === false);
	});
					
	it('department not allowed list', () => {
		assert.ok(scheme.isChildAllowed('department', 'list') === false);
	});
					
	it('department not allowed *', () => {
		assert.ok(scheme.isChildAllowed('department', '*') === false);
	});
					
	it('department not allowed table', () => {
		assert.ok(scheme.isChildAllowed('department', 'table') === false);
	});
					
	it('department not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('department', 'tr') === false);
	});
					
	it('department not allowed th', () => {
		assert.ok(scheme.isChildAllowed('department', 'th') === false);
	});
					
	it('department not allowed td', () => {
		assert.ok(scheme.isChildAllowed('department', 'td') === false);
	});
					
	it('department not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('department', '#shadowRoot') === false);
	});
					
	it('department not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('department', 'quote') === false);
	});
					
	it('department not allowed code', () => {
		assert.ok(scheme.isChildAllowed('department', 'code') === false);
	});
					
	it('department not allowed video', () => {
		assert.ok(scheme.isChildAllowed('department', 'video') === false);
	});
					
	it('department not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('department', 'spoiler') === false);
	});
					
	it('department not allowed user', () => {
		assert.ok(scheme.isChildAllowed('department', 'user') === false);
	});
					
	it('department not allowed project', () => {
		assert.ok(scheme.isChildAllowed('department', 'project') === false);
	});
					
	it('department not allowed department', () => {
		assert.ok(scheme.isChildAllowed('department', 'department') === false);
	});
					
	it('department not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('department', '#mention') === false);
	});
					
	it('department not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('department', 'disk') === false);
	});
					
	it('department not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('department', '#void') === false);
	});
					
	it('department not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('department', '#root') === false);
	});
					
	it('department not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('department', '#fragment') === false);
	});
					
	it('department not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('department', '#linebreak') === false);
	});
					
	it('department not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('department', '#tab') === false);
	});
					
	it('#mention not allowed #inline', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#inline') === false);
	});
					
	it('#mention not allowed span', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'span') === false);
	});
					
	it('#mention not allowed img', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'img') === false);
	});
					
	it('#mention not allowed #inlineBlock', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#inlineBlock') === false);
	});
					
	it('#mention not allowed url', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'url') === false);
	});
					
	it('#mention not allowed p', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'p') === false);
	});
					
	it('#mention not allowed #block', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#block') === false);
	});
					
	it('#mention not allowed list', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'list') === false);
	});
					
	it('#mention not allowed *', () => {
		assert.ok(scheme.isChildAllowed('#mention', '*') === false);
	});
					
	it('#mention not allowed table', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'table') === false);
	});
					
	it('#mention not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'tr') === false);
	});
					
	it('#mention not allowed th', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'th') === false);
	});
					
	it('#mention not allowed td', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'td') === false);
	});
					
	it('#mention not allowed #shadowRoot', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#shadowRoot') === false);
	});
					
	it('#mention not allowed quote', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'quote') === false);
	});
					
	it('#mention not allowed code', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'code') === false);
	});
					
	it('#mention not allowed video', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'video') === false);
	});
					
	it('#mention not allowed spoiler', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'spoiler') === false);
	});
					
	it('#mention not allowed user', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'user') === false);
	});
					
	it('#mention not allowed project', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'project') === false);
	});
					
	it('#mention not allowed department', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'department') === false);
	});
					
	it('#mention not allowed #mention', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#mention') === false);
	});
					
	it('#mention not allowed disk', () => {
		assert.ok(scheme.isChildAllowed('#mention', 'disk') === false);
	});
					
	it('#mention not allowed #void', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#void') === false);
	});
					
	it('#mention not allowed #root', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#root') === false);
	});
					
	it('#mention not allowed #fragment', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#fragment') === false);
	});
					
	it('#mention not allowed #linebreak', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#linebreak') === false);
	});
					
	it('#mention not allowed #tab', () => {
		assert.ok(scheme.isChildAllowed('#mention', '#tab') === false);
	});
					
	it('#root not allowed *', () => {
		assert.ok(scheme.isChildAllowed('#root', '*') === false);
	});
					
	it('#root not allowed tr', () => {
		assert.ok(scheme.isChildAllowed('#root', 'tr') === false);
	});
					
	it('#root not allowed th', () => {
		assert.ok(scheme.isChildAllowed('#root', 'th') === false);
	});
					
	it('#root not allowed td', () => {
		assert.ok(scheme.isChildAllowed('#root', 'td') === false);
	});
					
});
		