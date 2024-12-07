import Uri from '../../src/lib/uri';


describe('core/uri', () => {
	it('Should be exported as function', () => {
		assert(typeof Uri === 'function');
	});

	it('Should be a constructor', () => {
		assert(new Uri());
	});

	describe('#getSchema()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getSchema === 'function');
		});

		it('Should return schema of passed url', () => {
			const url = 'https://test.com/?test1=1';
			const uri = new Uri(url);
			assert(uri.getSchema() === 'https');

			const url2 = 'sms://+79670000000';
			const uri2 = new Uri(url2);
			assert(uri2.getSchema() === 'sms');
		});

		it('Should return empty string if passed uri without schema', () => {
			const url = 'test.com/?test1=1';
			const uri = new Uri(url);
			assert(uri.getSchema() === '');
		});
	});

	describe('#setSchema()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.setSchema === 'function');
		});

		it('Should set passed schema', () => {
			const url = 'https://test.com/?test1=1';
			const uri = new Uri(url);
			assert(uri.getSchema() === 'https');

			uri.setSchema('http');
			assert(uri.getSchema() === 'http');
			assert(uri.toString() === 'http://test.com/?test1=1');

			uri.setSchema('mailto');
			assert(uri.getSchema() === 'mailto');
			assert(uri.toString() === 'mailto://test.com/?test1=1');

			uri.setSchema('');
			assert(uri.getSchema() === '');
			assert(uri.toString() === 'test.com/?test1=1');
		});
	});

	describe('#getHost()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getHost === 'function');
		});

		it('Should return host of passed url', () => {
			const url = 'https://test.com:9999/?test1=1';
			const uri = new Uri(url);
			assert(uri.getHost() === 'test.com');
		});

		it('Should return empty string if passed uri without host', () => {
			const url = '?test1=1';
			const uri = new Uri(url);
			assert(uri.getHost() === '');
		});
	});

	describe('#setHost()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.setHost === 'function');
		});

		it('Should set passed host', () => {
			const url = 'https://test.com/?test1=1';
			const uri = new Uri(url);
			assert(uri.getHost() === 'test.com');

			uri.setHost('bitrix24.io');
			assert(uri.getHost() === 'bitrix24.io');
			assert(uri.toString() === 'https://bitrix24.io/?test1=1');

			uri.setHost('bitrix24.ru');
			assert(uri.getHost() === 'bitrix24.ru');
			assert(uri.toString() === 'https://bitrix24.ru/?test1=1');

			uri.setHost('');
			assert(uri.getHost() === '');
			assert(uri.toString() === '/?test1=1');
		});
	});

	describe('#getPort()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getPort === 'function');
		});

		it('Should return port of passed uri', () => {
			const url = 'https://test.com:9999/?test1=1';
			const uri = new Uri(url);
			assert(uri.getPort() === '9999');
		});

		it('Should return empty string for uri without port', () => {
			const url = 'https://test.com/?test1=1';
			const uri = new Uri(url);
			assert(uri.getPort() === '');
		});
	});

	describe('#setPort()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.setPort === 'function');
		});

		it('Should set passed port', () => {
			const url = 'https://test.com:3333/?test1=1';
			const uri = new Uri(url);
			assert(uri.getPort() === '3333');

			uri.setPort('1111');
			assert(uri.getPort() === '1111');
			assert(uri.toString() === 'https://test.com:1111/?test1=1');

			uri.setPort('2222');
			assert(uri.getPort() === '2222');
			assert(uri.toString() === 'https://test.com:2222/?test1=1');

			uri.setPort('');
			assert(uri.getPort() === '');
			assert(uri.toString() === 'https://test.com/?test1=1');
		});
	});

	describe('#getPath()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getPath === 'function');
		});

		it('Should return path of passed uri', () => {
			const url = 'https://test.com/test/path?test1=1';
			const uri = new Uri(url);
			assert(uri.getPath() === '/test/path');
		});

		it('Should return one slash if passed uri without path', () => {
			const url = 'https://test.com/?test1=1';
			const uri = new Uri(url);
			assert(uri.getPath() === '/');
		});
	});

	describe('#setPath', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.setPath === 'function');
		});

		it('Should set passed path', () => {
			const url = 'https://test.com/test/path?test1=1';
			const uri = new Uri(url);
			assert(uri.getPath() === '/test/path');

			uri.setPath('/new/path');
			assert(uri.getPath() === '/new/path');
			assert(uri.toString() === 'https://test.com/new/path?test1=1');

			uri.setPath('new/path');
			assert(uri.getPath() === '/new/path');
			assert(uri.toString() === 'https://test.com/new/path?test1=1');

			uri.setPath('');
			assert(uri.getPath() === '/');
			assert(uri.toString() === 'https://test.com/?test1=1');
		});
	});

	describe('#getQuery()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getQuery === 'function');
		});

		it('Should return valid query', () => {
			const url = 'https://test.com:9999/?test1=1&test2=2&testarr[]=1&testarr[]=2&testobj[1]=1&testobj[2]=2';
			const uri = new Uri(url);
			assert(uri.getQuery() === '?test1=1&test2=2&testarr[]=1&testarr[]=2&testobj[1]=1&testobj[2]=2');
		});

		it('Should return empty string if passed uri without query', () => {
			const url = 'https://test.com:9999';
			const uri = new Uri(url);
			assert(uri.getQuery() === '');
		});
	});

	describe('#getQueryParam()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getQueryParam === 'function');
		});

		it('Should return param value from passed url string', () => {
			const url = 'https://test.com?param=1&paramarr[]=2&paramobj[1]=3';
			const uri = new Uri(url);
			assert(uri.getQueryParam('param') === '1');

			const arr = uri.getQueryParam('paramarr');
			assert(Array.isArray(arr));
			assert(arr.length === 1);
			assert(arr[0] === '2');

			const obj = uri.getQueryParam('paramobj');
			assert(typeof obj === 'object' && !!obj);
			assert('1' in obj);
			assert(obj['1'] === '3');
		});

		it('Should return null if passed not exists param', () => {
			const url = 'https://test.com?param=1&paramarr[]=2&paramobj[1]=3';
			const uri = new Uri(url);
			assert(uri.getQueryParam('werwerwerwer') === null);
		});
	});

	describe('#setQueryParam()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.setQueryParam === 'function');
		});

		it('Should set passed param with value', () => {
			const uri = new Uri('https://test.com');

			uri.setQueryParam('test1', '1');
			assert(uri.getQueryParam('test1') === '1', '01');
			assert(uri.toString() === 'https://test.com?test1=1', '02');

			uri.setQueryParam('test2', [1, 2]);
			const test2 = uri.getQueryParam('test2');
			assert(Array.isArray(test2), '1');
			assert(test2.length === 2, '2');
			assert(test2[0] === '1', '3');
			assert(test2[1] === '2', '4');
			assert(uri.toString() === 'https://test.com?test1=1&test2[]=1&test2[]=2');
		});
	});

	describe('#getQueryParams()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getQueryParams === 'function');
		});

		it('Should return query params from passed url', () => {
			const uri = new Uri('https://test.com/?test1=1&test2[]=1&test2[]=2&test3[1]=1&test3[2]=2');
			const params = uri.getQueryParams();

			assert(typeof params === 'object' && !!params);
			assert(params['test1'] === '1');
			assert(Array.isArray(params['test2']));
			assert(params['test2'].length === 2);
			assert(typeof params['test3'] === 'object' && !!params['test3']);
			assert(params['test3']['1'] === '1');
			assert(params['test3']['2'] === '2');
		});

		it('Should return empty object if passed url without params', () => {
			const uri = new Uri('https://test.com');
			const params = uri.getQueryParams();
			assert(Object.keys(params).length === 0);
		});
	});

	describe('#setQueryParams()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.setQueryParams === 'function');
		});

		it('Should set passed params', () => {
			const uri = new Uri('https://test.com');

			uri.setQueryParams({
				param1: 1,
				param2: [1, 2],
				param3: {'1': 1, '2': 2},
			});

			assert(uri.getQueryParam('param1') === '1');
			assert(Array.isArray(uri.getQueryParam('param2')));
			assert(typeof uri.getQueryParam('param2') === 'object');
			assert(uri.toString() === 'https://test.com?param1=1&param2[]=1&param2[]=2&param3[1]=1&param3[2]=2');
		});
	});

	describe('#removeQueryParam()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.removeQueryParam === 'function');
		});

		it('Should remove passed query param', () => {
			const uri = new Uri('https://test.com/?param1=1&param2[]=1&param3[1]=1');

			uri.removeQueryParam('param1', 'param2');
			assert(uri.getQueryParam('param1') === null);
			assert(uri.getQueryParam('param2') === null);
			const param3 = uri.getQueryParam('param3');
			assert(typeof param3 === 'object' && !!param3);
			assert(uri.toString() === 'https://test.com/?param3[1]=1');
			assert(uri.getQueryParams()['param1'] === undefined);
			assert(uri.getQueryParams()['param2'] === undefined);
		});
	});

	describe('#getFragment()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.getFragment === 'function');
		});

		it('Should return fragment from passed url', () => {
			const uri = new Uri('http://test.com/?test1=1#hash');
			assert(uri.getFragment() === 'hash');
		});

		it('Should return empty string if passed url without fragment', () => {
			const uri = new Uri('http://test.com/?test1=1');
			assert(uri.getFragment() === '');
		});

		it('Should parse complicated fragment', () => {
			const uri = new Uri('http://test.com/?test1=1#user%3A%20%D0%98comma,2+2=4&2*2=4!');
			assert(uri.getFragment() === 'user%3A%20%D0%98comma,2+2=4&2*2=4!');
		});
	});

	describe('#setFragment()', () => {
		it('Should be a function', () => {
			const uri = new Uri();
			assert(typeof uri.setFragment === 'function');
		});

		it('Should set passed fragment', () => {
			const uri = new Uri('http://test.com/?test1=1#hash');
			uri.setFragment('hash2');
			assert(uri.getFragment() === 'hash2');
		});
	});

	it('Should support url with //', () => {
		const uri = new Uri('//test.com/my/path');

		assert.ok(uri.toString() === '//test.com/my/path');
	});

	it('Should works with malformed URI sequence', () => {
		const uri = new Uri('http://test.com/path?test=%E0%A4%A&test2=test2value');

		assert.ok(uri.getQueryParam('test') === '%E0%A4%A');
		assert.ok(uri.getQueryParam('test2') === 'test2value');
	});

	it('Should works if passed url as query param value', () => {
		const paramValue = encodeURIComponent('http://my.com/?param=1&param=2');
		const uri = new Uri(`http://test.com/path?test=${paramValue}`);

		assert.ok(uri.getQueryParam('test') === paramValue);
		assert.ok(uri.toString() === `http://test.com/path?test=${paramValue}`);
	});

	it('Should works with encoded values', () => {
		const params = {
			test1: encodeURIComponent('тестовое значение'),
			test2: encodeURIComponent('тестовое значение 2'),
			test3: encodeURIComponent('тестовое значение 3'),
		};
		const uri = new Uri('//test.com');

		uri.setQueryParams(params);

		assert.ok(uri.getQueryParam('test1') === params.test1);
		assert.ok(uri.getQueryParam('test2') === params.test2);
		assert.ok(uri.getQueryParam('test3') === params.test3);
	});

	it('Should works query with not unique params', () => {
		const uri = new Uri(
			'/?test=1&test=2&test=3',
		);

		assert.deepEqual(uri.getQueryParams(), {test: 3});
	});

	it('Should not sort params (bug 118046)', () => {
		const source = '/bitrix/components/bitrix/crm.requisite.edit/slider.ajax.php?requisite_id=0&sessid=b39479020231cbaf84b7299cacaeab47&etype=3&eid=0&external_context_id=CONTACT_0&pid=1&pseudo_id=n1';
		const params = {"IFRAME": "Y", "IFRAME_TYPE": "SIDE_SLIDER"};
		const uri = new Uri(source);
		uri.setQueryParams(params);

		assert.ok(uri.toString() === `${source}&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER`);
	});

	describe('Security tests', () => {
		it('Prototype pollution #1', () => {
			const url = 'https://my-site.com/?__proto__[customProp1]=badValue1';
			const uri = new Uri(url);

			assert.ok(typeof Object.customProp === 'undefined', 'Affected object prototype');
			assert.ok(uri.getQueryParam('__proto__') === null, '__proto__ access');
		});
	});
});