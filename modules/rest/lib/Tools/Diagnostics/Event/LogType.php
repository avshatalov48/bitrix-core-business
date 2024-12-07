<?php

namespace Bitrix\Rest\Tools\Diagnostics\Event;

enum LogType: string
{
	case EVENT_START = 'Event start';
	case EVENT_HANDLER_FOUND = 'Event handler found';
	case SKIP_BY_APP_INACTIVE = 'Event handler skipped because inactive application';
	case SKIP_BY_PAYMENT_EXPIRED = 'Event handler skipped because payment expired';
	case EVENT_EXCEPTION = 'Event handler skipped due to exception';
	case SEND_SQS = 'Send to SQS';
	case FAILED_SEND_TO_SQS = 'Failed to send events';
	case OAUTH_ERROR = 'OAuth connection error';
	case SENDER_CALL_START = 'Sender::call start';
	case SENDER_SEND_START = 'Sender::send start';
	case OFFLINE_EVENT_SKIPPED = 'Event skipped because initializer is current application';
	case CYCLIC_CALL_LIMIT = 'Cyclic call limit';
	case READY_OFFLINE_EVENT_LIST = 'Ready offline event list';
	case READY_ONLINE_EVENT_LIST = 'Ready online event list';
}
