import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class EmployeeCategory extends Group
{
	getId(): string
	{
		return 'employee_category';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_EMPLOYEE_CATEGORY');
	}

	getIcon(): string
	{
		return GroupIcon.EMPLOYEES;
	}
}