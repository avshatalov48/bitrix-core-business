export const defaultTheme = {
	blockCursor: 'ui-text-editor__block-cursor',
	indent: 'ui-text-editor__indent',
	ltr: 'ui-text-editor__ltr',
	rtl: 'ui-text-editor__rtl',

	heading: {
		h1: 'ui-typography-heading-h1',
		h2: 'ui-typography-heading-h2',
		h3: 'ui-typography-heading-h3',
		h4: 'ui-typography-heading-h4',
		h5: 'ui-typography-heading-h5',
		h6: 'ui-typography-heading-h6',
	},
	hashtag: 'ui-typography-hashtag',
	link: 'ui-typography-link',
	list: {
		listitem: 'ui-typography-li',
		nested: {
			listitem: 'ui-text-editor__nestedListItem',
		},
		olDepth: [
			'ui-typography-ol ui-text-editor__ol1',
			'ui-typography-ol ui-text-editor__ol2',
			'ui-typography-ol ui-text-editor__ol3',
			'ui-typography-ol ui-text-editor__ol4',
			'ui-typography-ol ui-text-editor__ol5',
		],
		ul: 'ui-typography-ul',
	},
	paragraph: 'ui-typography-paragraph ui-text-editor__paragraph',
	text: {
		bold: 'ui-typography-text-bold',
		code: 'ui-typography-text-code',
		italic: 'ui-typography-text-italic',
		strikethrough: 'ui-typography-text-strikethrough',
		subscript: 'ui-typography-text-subscript',
		superscript: 'ui-typography-text-superscript',
		underline: 'ui-typography-text-underline',
		underlineStrikethrough: 'ui-typography-text-underline-strikethrough',
	},
	mention: 'ui-typography-mention',
	quote: 'ui-typography-quote',
	spoiler: {
		container: 'ui-typography-spoiler',
		title: 'ui-typography-spoiler-title ui-icon-set__scope',
		content: 'ui-typography-spoiler-content',
	},
	smiley: 'ui-typography-smiley',
	code: 'ui-typography-code',
	codeHighlight: {
		operator: 'ui-typography-token-operator',
		punctuation: 'ui-typography-token-punctuation',
		comment: 'ui-typography-token-comment',
		word: 'ui-typography-token-word',
		keyword: 'ui-typography-token-keyword',
		boolean: 'ui-typography-token-boolean',
		regex: 'ui-typography-token-regex',
		string: 'ui-typography-token-string',
		number: 'ui-typography-token-number',
		semicolon: 'ui-typography-token-semicolon',
		bracket: 'ui-typography-token-bracket',
		brace: 'ui-typography-token-brace',
		parentheses: 'ui-typography-token-parentheses',
	},

	table: 'ui-typography-table',
	tableRow: 'ui-typography-table-row',
	tableCell: 'ui-typography-table-cell',
	tableCellHeader: 'ui-typography-table-cell-header',
	tableSelection: 'ui-typography-table-selection',

	image: {
		container: 'ui-typography-image-container ui-text-editor__image-container',
		img: 'ui-typography-image',
	},

	video: {
		container: 'ui-typography-video-container ui-text-editor__video-container',
		object: 'ui-typography-video-object ui-text-editor__video-object',
	},

	file: 'ui-text-editor__file',
};
