const bbcodeTree = {
	name: 'root',
	children: [
		{
			name: 'p',
			children: [
				{
					name: 'b',
					children: [
						{
							name: 'text',
							content: 'bold',
						},
					],
				},
			],
		},
		{
			name: 'text',
			content: 'test',
		},
	],
};

const leftTree = {
	name: 'root',
	children: [
		{
			name: 'p',
			children: [
				{
					name: 'b',
					children: [
						{
							name: 'text',
							content: 'bo',
						},
					],
				},
			],
		},
	],
};

const rightTree = {
	name: 'root',
	children: [
		{
			name: 'p',
			children: [
				{
					name: 'b',
					children: [
						{
							name: 'text',
							content: 'ld',
						},
					],
				},
			],
		},
		{
			name: 'text',
			content: 'test',
		},
	],
};
