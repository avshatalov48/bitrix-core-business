import { BBCodeEncoder } from '../../src/encoder';

describe('BBCodeEncoder', () => {
	it('should encode special chars', () => {
	    const source = `
			[test]
			&#91;
			&#93;
			&amp;#91;
			&amp;#93;
			'
			"
			<
			>
		`;

		const encodedSource = `
			&#91;test&#93;
			&amp;#91;
			&amp;#93;
			&amp;amp;#91;
			&amp;amp;#93;
			&#39;
			&quot;
			&lt;
			&gt;
		`;

		const encoder = new BBCodeEncoder();
		const result = encoder.encodeText(source);

		assert.equal(result, encodedSource);
	});

	it('should decode special chars', () => {
		const encodedSource = `
			&#91;test&#93;
			&amp;#91;
			&amp;#93;
			&amp;amp;#91;
			&amp;amp;#93;
			&#39;
			&quot;
			&lt;
			&gt;
		`;

		const source = `
			[test]
			&#91;
			&#93;
			&amp;#91;
			&amp;#93;
			'
			"
			<
			>
		`;

		const encoder = new BBCodeEncoder();
		const result = encoder.decodeText(encodedSource);

		assert.equal(result, source);
	});
});
