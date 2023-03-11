import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class EmployeeControl extends Group
{
	getId(): string
	{
		return 'employeeControl';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_EMPLOYEE_CONTROL');
	}

	getIcon(): string
	{
		return GroupIcon.EMPLOYEES;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_EMPLOYEE_CONTROL_1');
	}
}