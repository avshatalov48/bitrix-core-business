const { test } = require('@playwright/test');
const { focusEditor, initializeTest, pasteFromClipboard, assertHTML } = require('./utils');
const { moveToLineBeginning, moveToLineEnd, moveRight } = require('./keyboard');
const { paragraph, text, link, autolink, br } = require('./html');

test.describe('AutoLink', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Can convert url-like text into links', async ({ page }) => {
		await focusEditor(page);

		await page.keyboard.type(
			'Three links: http://example.co.uk and https://example.com/path?with=query#and-hash and www.example.com',
		);

		await assertHTML(
			page,
			paragraph(
				text('Three links: ')
				+ autolink('http://example.co.uk')
				+ text(' and ')
				+ autolink('https://example.com/path?with=query#and-hash')
				+ text(' and ')
				+ link('www.example.com', 'https://www.example.com'),
			),
		);
	});

	test('Can create link when pasting text with urls', async ({ page }) => {
		await focusEditor(page);

		await pasteFromClipboard(page, {
			'text/plain': 'Hello http://example.com and https://example.com/path?with=query#and-hash and www.example.com',
		});

		await assertHTML(
			page,
			paragraph(
				text('Hello ')
				+ autolink('http://example.com')
				+ text(' and ')
				+ autolink('https://example.com/path?with=query#and-hash')
				+ text(' and ')
				+ link('www.example.com', 'https://www.example.com'),
			),
		);
	});

	test('Can destruct links if add non-spacing text in front or right after it', async ({ page }) => {
		const htmlWithLink = paragraph(autolink('http://example.com'));

		await focusEditor(page);
		await page.keyboard.type('http://example.com');
		await assertHTML(page, htmlWithLink);

		// Add non-url text after the link
		await page.keyboard.type('!');
		await assertHTML(
			page,
			paragraph('http://example.com!'),
		);
		await page.keyboard.press('Backspace');
		await assertHTML(page, htmlWithLink);

		// Add non-url text before the link
		await moveToLineBeginning(page);
		await page.keyboard.type('!');
		await assertHTML(
			page,
			paragraph('!http://example.com'),
		);
		await page.keyboard.press('Backspace');
		await assertHTML(page, htmlWithLink);

		// Add newline after link
		await moveToLineEnd(page);
		await page.keyboard.press('Enter');
		await assertHTML(
			page,
			paragraph(autolink('http://example.com') + br(2)),
		);
		await page.keyboard.press('Backspace');
		await assertHTML(page, htmlWithLink);
	});

	test(
		'Can create links when pasting text with multiple autolinks in a row separated by non-alphanumeric characters, but not whitespaces',
		async ({ page }) => {
			await focusEditor(page);

			await pasteFromClipboard(page, {
				'text/plain': 'https://1.com/,https://2.com/;;;https://3.com',
			});

			await assertHTML(
				page,
				paragraph(autolink('https://1.com/') + text(',') + autolink('https://2.com/') + text(';;;') + autolink('https://3.com')),
			);
		},
	);

	test('Handles multiple autolinks in a row', async ({ page }) => {
		await focusEditor(page);
		await pasteFromClipboard(page, { 'text/plain': 'https://1.com/ https://2.com/ https://3.com/ https://4.com/' });

		await assertHTML(
			page,
			paragraph(
				autolink('https://1.com/')
				+ text(' ')
				+ autolink('https://2.com/')
				+ text(' ')
				+ autolink('https://3.com/')
				+ text(' ')
				+ autolink('https://4.com/'),
			),
		);
	});

	test('Handles autolink following an invalid autolink', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hellohttps://example.com https://example.com');

		await assertHTML(
			page,
			paragraph(text('Hellohttps://example.com ') + autolink('https://example.com')),
		);
	});

	// test('Can convert url-like text with formatting into links', async ({ page }) => {
	// 	await focusEditor(page);
	// 	await page.keyboard.type('Hellohttp://example.com and more');
	//
	// 	// Add bold formatting to com
	// 	await moveToLineBeginning(page);
	// 	await moveRight(page, 20);
	// 	await selectCharacters(page, 'right', 3);
	// 	await toggleBold(page);
	//
	// 	await assertHTML(
	// 		page,
	// 		`
	// 			<p class="ui-text-editor__paragraph">
	// 				<span data-lexical-text="true">Hellohttp://example.</span>
	// 				<strong class="ui-text-editor__text-bold" data-lexical-text="true">com</strong>
	// 				<span data-lexical-text="true"> and more</span>
	// 			</p>
	// 		`,
	// 	);
	//
	// 	// Add space before formatted link text
	// 	await moveToLineBeginning(page);
	// 	await moveRight(page, 5);
	// 	await page.keyboard.type(' ');
	//
	// 	await assertHTML(
	// 		page,
	// 		`
	// 			<p class="ui-text-editor__paragraph">
	// 				<span data-lexical-text="true">Hello </span>
	// 				<a href="http://example.com" target="_blank" class="ui-text-editor__link">
	// 					<span data-lexical-text="true">http://example.</span>
	// 					<strong class="ui-text-editor__text-bold" data-lexical-text="true">com</strong>
	// 				</a>
	// 				<span data-lexical-text="true"> and more</span>
	// 			</p>
	// 		`,
	// 	);
	// });

	test('Handles autolink after space typing', async ({ page }) => {
		await focusEditor(page);

		await page.keyboard.type('Hellohttp://example.com and more');

		await assertHTML(
			page,
			paragraph('Hellohttp://example.com and more'),
		);

		// Add space before link text
		await moveToLineBeginning(page);
		await moveRight(page, 5);
		await page.keyboard.type(' ');

		await assertHTML(
			page,
			paragraph(text('Hello ') + autolink('http://example.com') + text(' and more')),
		);
	});

	test('Can convert URLs into links', async ({ page }) => {
		const testUrls = [
			// Basic URLs
			'http://example.com', // Standard HTTP URL
			'https://example.com', // Standard HTTPS URL
			'http://www.example.com', // HTTP URL with www
			'https://www.example.com', // HTTPS URL with www
			'www.example.com', // Missing HTTPS Protocol

			// With Different TLDs
			'http://example.org', // URL with .org TLD
			'https://example.net', // URL with .net TLD
			'http://example.co.uk', // URL with country code TLD
			'https://example.xyz', // URL with generic TLD

			// With Paths
			'http://example.com/path/to/resource', // URL with path
			'https://www.example.com/path/to/resource', // URL with www and path

			// With Query Parameters
			'http://example.com/path?name=value', // URL with query parameters
			'https://www.example.com/path?name=value&another=value2', // URL with multiple query parameters

			// With Fragments
			'http://example.com/path#section', // URL with fragment
			'https://www.example.com/path/to/resource#fragment', // URL with path and fragment

			// With Port Numbers
			'http://example.com:8080', // URL with port number
			'https://www.example.com:443/path', // URL with port number and path

			// IP Addresses
			'http://192.168.0.1', // URL with IPv4 address
			'https://127.0.0.1', // URL with localhost IPv4 address

			// With Special Characters in Path and Query
			'http://example.com/path/to/res+ource', // URL with plus in path
			'https://example.com/path/to/res%20ource', // URL with encoded space in path
			'http://example.com/path?name=va@lue', // URL with special character in query
			'https://example.com/path?name=value&another=val%20ue', // URL with encoded space in query

			// Subdomains and Uncommon TLDs
			'http://subdomain.example.com', // URL with subdomain
			'https://sub.subdomain.example.com', // URL with multiple subdomains
			'http://example.museum', // URL with uncommon TLD
			'https://example.travel', // URL with uncommon TLD

			// Edge Cases
			'http://foo.bar', // Minimal URL with uncommon TLD
			'https://foo.bar', // HTTPS minimal URL with uncommon TLD
		];

		await focusEditor(page);
		await page.keyboard.type(`${testUrls.join(' ')} `);

		let expectedHTML = '';
		for (let url of testUrls)
		{
			url = url.replaceAll('&', '&amp;');
			const rawUrl = url;

			if (!url.startsWith('http'))
			{
				url = `https://${url}`;
			}

			expectedHTML += link(rawUrl, url) + text(' ');
		}

		await assertHTML(
			page,
			paragraph(expectedHTML),
		);
	});

	test('Can not convert bad URLs into links', async ({ page }) => {
		const testUrls = [
			// Missing Protocol
			'example.com', // Missing HTTPS and www

			// Invalid Protocol
			'htp://example.com', // Typo in protocol
			'htps://example.com', // Typo in protocol

			// Invalid TLDs
			'http://example.abcdefg', // TLD too long

			// Spaces and Invalid Characters
			'http://exa mple.com', // Space in domain
			'https://example .com', // Space in domain
			'http://example!.com', // Invalid character in domain

			// Missing Domain
			'http://.com', // Missing domain name
			'https://.org', // Missing domain name

			// Incomplete URLs
			'http://', // Incomplete URL
			'https://', // Incomplete URL

			// Just Text
			'not_a_url', // Plain text
			'this is not a url', // Sentence
			'example', // Single word
			'ftp://example.com', // Unsupported protocol (assuming only HTTP/HTTPS is supported)
		];

		await focusEditor(page);

		await page.keyboard.type(testUrls.join(' '));

		await assertHTML(
			page,
			paragraph(testUrls.join(' ')),
		);
	});

	// test('Can unlink the autolink and then make it link again', async ({ page }) => {
	// 	await focusEditor(page);
	//
	// 	await page.keyboard.type('Hello http://www.example.com test');
	// 	await assertHTML(
	// 		page,
	// 		`
	// 			<p class="ui-text-editor__paragraph">
	// 				<span data-lexical-text="true">Hello </span>
	// 				<a href="http://www.example.com" target="_blank" class="ui-text-editor__link">
	// 					<span data-lexical-text="true">http://www.example.com</span>
	// 				</a>
	// 				<span data-lexical-text="true"> test</span>
	// 			</p>
	// 		`,
	// 	);
	//
	// 	await focusEditor(page);
	// 	await click(page, 'a[href="http://www.example.com"]');
	// 	await click(page, 'button[data-testid="unlink-btn"]');
	//
	// 	await assertHTML(
	// 		page,
	// 		`
	// 			<p class="ui-text-editor__paragraph">
	// 				<span data-lexical-text="true">Hello http://www.example.com test</span>
	// 			</p>
	// 		`,
	// 	);
	// });
});
