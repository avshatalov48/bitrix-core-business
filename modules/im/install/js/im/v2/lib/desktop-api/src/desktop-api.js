import { lifecycleFunctions } from './functions/lifecycle';
import { versionFunctions } from './functions/version';
import { eventFunctions } from './functions/event';
import { windowFunctions } from './functions/window';
import { iconFunctions } from './functions/icon';
import { settingsFunctions } from './functions/settings';
import { commonFunctions } from './functions/common';
import { legacyFunctions } from './functions/legacy';
import { notificationFunctions } from './functions/notifications';
import { loggerFunctions } from './functions/logger';
import { callMaskFunctions } from './functions/call/mask';
import { callBackgroundFunctions } from './functions/call/background';
import { accountFunctions } from './functions/account';
import { diskFunctions } from './functions/disk';
import { debugFunctions } from './functions/debug';

export { DesktopFeature } from './features';
export { DesktopSettingsKey } from './functions/settings';

export const DesktopApi = {
	...lifecycleFunctions,
	...commonFunctions,
	...versionFunctions,
	...eventFunctions,
	...windowFunctions,
	...iconFunctions,
	...notificationFunctions,
	...settingsFunctions,
	...legacyFunctions,
	...callBackgroundFunctions,
	...callMaskFunctions,
	...loggerFunctions,
	...accountFunctions,
	...diskFunctions,
	...debugFunctions,
};

export type { DesktopAccount } from './types/account';
