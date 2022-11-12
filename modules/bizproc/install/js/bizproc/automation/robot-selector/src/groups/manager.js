import type { GroupData } from 'ui.entity-catalog';

import { ClientCommunication as ClientCommunicationGroup } from './client-communication';
import { InformingEmployee as InformingEmployeeGroup } from './informing-employee';
import { EmployeeControl as EmployeeControlGroup } from './employee-control';
import { Paperwork as PaperworkGroup } from './paperwork';
import { Payment as PaymentGroup } from './payment';
import { Delivery as DeliveryGroup } from './delivery';
import { RepeatSales as RepeatSalesGroup } from './repeat-sales';
import { Ads as AdsGroup } from './ads';
import { ElementControl as ElementControlGroup } from './element-control';
import { ClientData as ClientDataGroup } from './client-data';
import { Goods as GoodsGroup } from './goods';
import { TaskManagement as TaskManagementGroup } from './task-management';
import { ModificationData as ModificationDataGroup } from './modification-data';
import { DigitalWorkplace as DigitalWorkplaceGroup } from './digital-workplace';
import { OtherGroup } from './other-group';

import { EmployeeCategory } from './employee-category';
import { ClientCategory } from './client-category';
import { AdsCategory } from './ads-category';
import { OtherCategory } from './other-category';
import { TriggerCategory } from './trigger-category';

let instance = null;

export class Manager
{
	#clientCommunicationGroup: ClientCommunicationGroup;
	#informingEmployeeGroup: InformingEmployeeGroup;
	#employeeControlGroup: EmployeeControlGroup;
	#paperworkGroup: PaperworkGroup;
	#paymentGroup: PaymentGroup;
	#deliveryGroup: DeliveryGroup;
	#repeatSalesGroup: RepeatSalesGroup;
	#adsGroup: AdsGroup;
	#elementControlGroup: ElementControlGroup;
	#clientDataGroup: ClientDataGroup;
	#goodsGroup: GoodsGroup;
	#taskManagementGroup: TaskManagementGroup;
	#modificationDataGroup: ModificationDataGroup;
	#digitalWorkplaceGroup: DigitalWorkplaceGroup;
	#otherGroup: OtherGroup;

	#employeeCategory: EmployeeCategory;
	#clientCategory: ClientCategory;
	#adsCategory: AdsCategory;
	#otherCategory: OtherCategory;
	#triggerCategory: TriggerCategory;

	static get Instance(): Manager
	{
		if (instance === null)
		{
			instance = new Manager();
		}

		return instance;
	}

	getAutomationGroupsData(): Array<GroupData>
	{
		return [
			this.clientCommunicationGroup.getData(),
			this.informingEmployeeGroup.getData(),
			this.employeeControlGroup.getData(),
			this.paperworkGroup.getData(),
			this.paymentGroup.getData(),
			this.deliveryGroup.getData(),
			this.repeatSalesGroup.getData(),
			this.adsGroup.getData(),
			this.elementControlGroup.getData(),
			this.clientDataGroup.getData(),
			this.goodsGroup.getData(),
			this.taskManagementGroup.getData(),
			this.modificationDataGroup.getData(),
			this.digitalWorkplaceGroup.getData(),
			this.otherGroup.getData(),
		];
	}

	getAutomationCategoriesData(): Array<GroupData>
	{
		return [
			this.employeeCategory.getData(),
			this.clientCategory.getData(),
			this.adsCategory.getData(),
			this.otherCategory.getData(),
			this.triggerCategory.getData(),
		];
	}

	get clientCommunicationGroup(): ClientCommunicationGroup
	{
		if (!this.#clientCommunicationGroup)
		{
			this.#clientCommunicationGroup = new ClientCommunicationGroup();
		}

		return this.#clientCommunicationGroup;
	}

	get informingEmployeeGroup(): InformingEmployeeGroup
	{
		if (!this.#informingEmployeeGroup)
		{
			this.#informingEmployeeGroup = new InformingEmployeeGroup();
		}

		return this.#informingEmployeeGroup;
	}

	get employeeControlGroup(): EmployeeControlGroup
	{
		if (!this.#employeeControlGroup)
		{
			this.#employeeControlGroup = new EmployeeControlGroup();
		}

		return this.#employeeControlGroup;
	}

	get paperworkGroup(): PaperworkGroup
	{
		if (!this.#paperworkGroup)
		{
			this.#paperworkGroup = new PaperworkGroup();
		}

		return this.#paperworkGroup;
	}

	get paymentGroup(): PaymentGroup
	{
		if (!this.#paymentGroup)
		{
			this.#paymentGroup = new PaymentGroup();
		}

		return this.#paymentGroup;
	}

	get deliveryGroup(): DeliveryGroup
	{
		if (!this.#deliveryGroup)
		{
			this.#deliveryGroup = new DeliveryGroup();
		}

		return this.#deliveryGroup;
	}

	get repeatSalesGroup(): RepeatSalesGroup
	{
		if (!this.#repeatSalesGroup)
		{
			this.#repeatSalesGroup = new RepeatSalesGroup();
		}

		return this.#repeatSalesGroup;
	}

	get adsGroup(): AdsGroup
	{
		if (!this.#adsGroup)
		{
			this.#adsGroup = new AdsGroup();
		}

		return this.#adsGroup;
	}

	get elementControlGroup(): ElementControlGroup
	{
		if (!this.#elementControlGroup)
		{
			this.#elementControlGroup = new ElementControlGroup();
		}

		return this.#elementControlGroup;
	}

	get clientDataGroup(): ClientDataGroup
	{
		if (!this.#clientDataGroup)
		{
			this.#clientDataGroup = new ClientDataGroup();
		}

		return this.#clientDataGroup;
	}

	get goodsGroup(): GoodsGroup
	{
		if (!this.#goodsGroup)
		{
			this.#goodsGroup = new GoodsGroup();
		}

		return this.#goodsGroup;
	}

	get taskManagementGroup(): TaskManagementGroup
	{
		if (!this.#taskManagementGroup)
		{
			this.#taskManagementGroup = new TaskManagementGroup();
		}

		return this.#taskManagementGroup;
	}

	get modificationDataGroup(): ModificationDataGroup
	{
		if (!this.#modificationDataGroup)
		{
			this.#modificationDataGroup = new ModificationDataGroup();
		}

		return this.#modificationDataGroup;
	}

	get digitalWorkplaceGroup(): DigitalWorkplaceGroup
	{
		if (!this.#digitalWorkplaceGroup)
		{
			this.#digitalWorkplaceGroup = new DigitalWorkplaceGroup();
		}

		return this.#digitalWorkplaceGroup;
	}

	get otherGroup(): OtherGroup
	{
		if (!this.#otherGroup)
		{
			this.#otherGroup = new OtherGroup();
		}

		return this.#otherGroup;
	}

	get employeeCategory(): EmployeeCategory
	{
		if (!this.#employeeCategory)
		{
			this.#employeeCategory = new EmployeeCategory();
		}

		return this.#employeeCategory;
	}

	get clientCategory(): ClientCategory
	{
		if (!this.#clientCategory)
		{
			this.#clientCategory = new ClientCategory();
		}

		return this.#clientCategory;
	}

	get adsCategory(): AdsCategory
	{
		if (!this.#adsCategory)
		{
			this.#adsCategory = new AdsCategory();
		}

		return this.#adsCategory;
	}

	get otherCategory(): OtherCategory
	{
		if (!this.#otherCategory)
		{
			this.#otherCategory = new OtherCategory();
		}

		return this.#otherCategory;
	}

	get triggerCategory(): TriggerCategory
	{
		if (!this.#triggerCategory)
		{
			this.#triggerCategory = new TriggerCategory();
		}

		return this.#triggerCategory;
	}
}