export const MockRestResponse = {
	users: [
		{
			'id': 1,
			'active': true,
			'name': 'Dmitriy Mezhnin',
			'first_name': 'Dmitriy',
			'last_name': 'Mezhnin',
			'work_position': '',
			'color': '#df532d',
			'avatar': '',
			'gender': 'M',
			'birthday': '',
			'extranet': false,
			'network': false,
			'bot': false,
			'connector': false,
			'external_auth_id': 'default',
			'status': 'online',
			'idle': false,
			'last_activity_date': '2022-12-03T22:44:07+02:00',
			'mobile_last_date': '2022-08-24T14:45:40+02:00',
			'absent': false,
			'departments': [
				1
			],
			'phones': false,
			'desktop_last_date': '2022-12-03T22:33:00+02:00'
		},
	],
	tasksList: [
		{
			'id': 36,
			'messageId': null,
			'chatId': 101,
			'authorId': 1,
			'dateCreate': '2022-11-21T10:55:58+02:00',
			'task': {
				'id': 39,
				'title': 'Task from chat...',
				'creatorId': 1,
				'responsibleId': 1,
				'status': 2,
				'statusTitle': 'Waiting status',
				'deadline': '2022-11-21T19:00:00+02:00',
				'state': '&minus; 1 week',
				'color': 'danger',
				'source': 'https://mezhnin.office.bitrix.ru/company/personal/user/1/tasks/task/view/39/'
			}
		}
	],
	meetingsList: [
		{
			'id': 6,
			'messageId': null,
			'chatId': 101,
			'authorId': 1,
			'dateCreate': '2022-11-18T17:00:34+02:00',
			'calendar': {
				'id': 83,
				'title': 'Need to discuss',
				'dateFrom': '2022-11-19T18:10:00+03:00',
				'dateTo': '2022-11-19T19:10:00+03:00',
				'source': 'https://mezhnin.office.bitrix.ru/calendar/?EVENT_ID=83'
			}
		}
	]
};