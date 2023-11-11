export const Config = {
	get: () => {
		return {
			dialog: {
				entities: [
					{
						id: 'im-bot',
						options: {
							searchableBotTypes: [
								'H',
								'B',
								'S',
								'N',
							],
							fillDialogWithDefaultValues: false,
						},
						dynamicLoad: true,
						dynamicSearch: true,
					},
					{
						id: 'user',
						dynamicLoad: true,
						dynamicSearch: true,
						filters: [
							{
								id: 'im.userDataFilter'
							}
						]
					},
					/* Disable - chat with users section
					{
						id: 'im-chat-user',
						options: {
							searchableChatTypes: [
								'C',
								'O'
							],
							fillDialogWithDefaultValues: false,
						},
						dynamicLoad: true,
						dynamicSearch: true,
					},
					*/
				],
				preselectedItems: [],
				clearUnavailableItems: false,
				context: 'IM_CHAT_SEARCH',
				id: 'im-search',
			}
		};
	},
	getNetworkEntity: () => {
		return {
			id: 'imbot-network',
			dynamicSearch: true,
			options: {
				'filterExistingLines': true,
			}
		};
	},
	getDepartmentEntity: () => {
		return {
			id: 'department',
			dynamicLoad: true,
			dynamicSearch: true,
			options: {
				selectMode: 'usersAndDepartments',
				allowSelectRootDepartment: true,
			},
			filters: [
				{
					id: 'im.departmentDataFilter'
				}
			]
		};
	},
	getChatEntity: () => {
		return {
			id: 'im-chat',
			options: {
				searchableChatTypes: [
					'C',
					'O',
					'L'
				],
				fillDialogWithDefaultValues: false,
			},
			dynamicLoad: true,
			dynamicSearch: true,
		};
	},
	getLinesEntity: () => {
		return {
			id: 'imol-chat',
			dynamicLoad: true,
			dynamicSearch: true,
		};
	},
};
