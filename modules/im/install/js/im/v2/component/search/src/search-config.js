export const Config = {
	dialog: {
		entities: [
			{
				'id': 'im-bot',
				'options': {
					'searchableBotTypes': [
						'H',
						'B',
						'S',
						'N'
					],
				},
				'dynamicLoad': true,
				'dynamicSearch': true,
			},
			{
				'id': 'im-chat',
				'options': {
					'searchableChatTypes': [
						'C',
						'O'
					],
				},
				'dynamicLoad': true,
				'dynamicSearch': true,
			},
			{
				'id': 'user',
				'dynamicLoad': true,
				'dynamicSearch': true,
				'filters': [
					{
						'id': 'im.userDataFilter'
					}
				]
			},
			{
				id: 'department',
				dynamicLoad: true,
				dynamicSearch: true,
				options: {
					selectMode: 'usersAndDepartments',
					allowSelectRootDepartment: true,
				}
			},
		],
		preselectedItems: [],
		clearUnavailableItems: false,
		context: 'IM_CHAT_SEARCH'
	}
};