import {Event, Loc, Type} from 'main.core';
import {Form} from "./form/form";
import {RequestSender} from "./request.sender";
import {ImageField} from "./form/field/image.field";
import {DateField} from "./form/field/date.field";
import {DateRangeField} from "./form/field/date.range.field";
import {UserField} from "./form/field/user.field";
import {SelectField} from "./form/field/select.field";
import {RequireValidator} from "./form/validator/require.validator";
import {Options} from "./form/block/options";
import {Switcher} from "./form/block/switcher";
import {Features} from "./form/block/features";
import {Rights} from "./form/block/rights";
import {Tags} from "./form/block/tags";
import {Types} from "./form/block/types";

export class CopyingManager
{
	constructor(options)
	{
		options = {...{
			signedParameters: "",
			formContainerId: "",
			isProject: "N",
			isExtranet: "N",
			isExtranetGroup: "N",
			isExtranetInstalled: "N",
			isIntranetInstalled: "N",
			groupData: {},
			imageFieldContainerId: "",
			ownerFieldContainerId: "",
			helpersFieldContainerId: "",
			isLandingInstalled: "",
			tagsFieldContainerId: "",
			copyButtonId: "",
			cancelButtonId: ""
		}, ...options};

		this.signedParameters = options.signedParameters;

		this.formContainerId = options.formContainerId;

		this.isProject = (options.isProject === "Y");
		this.isExtranet = (options.isExtranet === "Y");
		this.isExtranetGroup = (options.isExtranetGroup === "Y");
		this.isExtranetInstalled = (options.isExtranetInstalled === "Y");
		this.isIntranetInstalled = (options.isIntranetInstalled === "Y");
		this.isLandingInstalled = (options.isLandingInstalled === "Y");

		this.groupData = options.groupData;

		this.subjects = this.groupData["SUBJECTS"];
		this.features = this.groupData["FEATURES"];
		this.initiatePerms = this.groupData["LIST_INITIATE_PERMS"];

		this.imageFieldContainerId = options.imageFieldContainerId;
		this.ownerFieldContainerId = options.ownerFieldContainerId;
		this.helpersFieldContainerId = options.helpersFieldContainerId;
		this.tagsFieldContainerId = options.tagsFieldContainerId;

		this.requestSender = new RequestSender({
			signedParameters: this.signedParameters,
		});
		this.requestSender.setProjectMarker(this.isProject);

		this.form = new Form({
			requestSender: this.requestSender,
			groupData: options.groupData,
			copyButtonId: options.copyButtonId,
			cancelButtonId: options.cancelButtonId
		});

		this.buildForm();
	}

	buildForm()
	{
		this.checkboxEventName = "BX.Socialnetwork.CheckboxField";

		this.form.addField(new ImageField({
			fieldTitle: Loc.getMessage("SGCG_UPLOAD_IMAGE_TITLE"),
			fieldName: "image_id",
			fieldContainerId: this.imageFieldContainerId
		}));

		this.form.addField(this.createDate());

		if (!this.isProject)
		{
			this.form.addField(this.createRangeDate());
		}

		this.form.addField(this.createOwner());

		this.form.addField(new UserField({
			selectorId: "group-copy-helpers",
			fieldTitle: (this.isProject ?
				Loc.getMessage("SGCG_PROJECT_HELPERS_TITLE") : Loc.getMessage("SGCG_GROUP_HELPERS_TITLE")),
			fieldName: "moderators",
			fieldContainerId: this.helpersFieldContainerId
		}));
		if (Type.isPlainObject(this.subjects))
		{
			this.form.addField(this.createSubject());
		}

		const switcher = new Switcher({
			title: Loc.getMessage("SGCG_OPTIONS_TITLE")
		});
		const options = new Options({
			switcher: switcher
		});
		const features = new Features({
			fieldTitle: Loc.getMessage("SGCG_OPTIONS_FEATURES_TITLE"),
			data: this.features,
			switcher: switcher
		});
		options.addOption(features);

		//todo SPAM_PERMS
		options.addOption(this.createRights(switcher));

		options.addOption(this.createTags(switcher));

		options.addOption(this.createTypes(switcher));

		this.form.addBlock(options);

		this.form.renderTo(document.getElementById(this.formContainerId));
	}

	subscribeToField(eventName, callback)
	{
		Event.EventEmitter.subscribe(eventName, callback);
	}

	createTypes(switcher)
	{
		const types = new Types({
			fieldTitle: (this.isProject ? Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_LABEL") :
				Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_LABEL")),
			data: this.groupData,
			switcher: switcher,
			isProject: this.isProject,
			isExtranetGroup: this.isExtranetGroup,
			isExtranetInstalled: this.isExtranetInstalled,
			isIntranetInstalled: this.isIntranetInstalled,
			isLandingInstalled: this.isLandingInstalled
		});
		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			types.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_LABEL") : Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_LABEL"));
		});
		return types;
	}

	createTags(switcher)
	{
		return new Tags({
			fieldTitle: Loc.getMessage("SGCG_OPTIONS_KEYWORDS_TITLE"),
			tagsFieldContainerId: this.tagsFieldContainerId,
			switcher: switcher
		});
	}

	createRights(switcher)
	{
		const rights = new Rights({
			fieldTitle: (this.isProject ? Loc.getMessage("SGCG_OPTIONS_PROJECT_PERMS_LABEL") :
				Loc.getMessage("SGCG_OPTIONS_GROUP_PERMS_LABEL")),
			value: this.groupData["INITIATE_PERMS"],
			data: (this.isProject ? this.initiatePerms.project : this.initiatePerms.group),
			switcher: switcher
		});
		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			rights.changeSelectOptions((baseEvent.data.checked ? this.initiatePerms.project : this.initiatePerms.group));
			rights.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_OPTIONS_PROJECT_PERMS_LABEL") : Loc.getMessage("SGCG_OPTIONS_GROUP_PERMS_LABEL"));
		});
		return rights;
	}

	createSubject()
	{
		const subject = new SelectField({
			fieldTitle: (this.isProject ?
				Loc.getMessage("SGCG_PROJECT_SUBJECT") : Loc.getMessage("SGCG_GROUP_SUBJECT")),
			fieldName: "subject_id",
			value: this.groupData["SUBJECT_ID"],
			list: this.subjects
		});
		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			subject.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_PROJECT_SUBJECT") : Loc.getMessage("SGCG_GROUP_SUBJECT"));
		});
		return subject;
	}

	createOwner()
	{
		const owner = new UserField({
			selectorId: "group-copy-owner",
			multiple: false,
			fieldTitle: (this.isProject ?
				Loc.getMessage("SGCG_PROJECT_OWNER_TITLE") : Loc.getMessage("SGCG_GROUP_OWNER_TITLE")),
			fieldName: "owner_id",
			validators: [RequireValidator],
			fieldContainerId: this.ownerFieldContainerId
		});
		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			owner.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_PROJECT_OWNER_TITLE") : Loc.getMessage("SGCG_GROUP_OWNER_TITLE"));
		});
		return owner;
	}

	createDate()
	{
		const date = new DateField({
			fieldTitle: (this.isProject ?
				Loc.getMessage("SGCG_PROJECT_START_POINT_TITLE") : Loc.getMessage("SGCG_GROUP_START_POINT_TITLE")),
			fieldName: "start_point",
			validators: [RequireValidator],
		});
		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			if (this.isProject)
			{
				date.changeTitle(baseEvent.data.checked ?
					Loc.getMessage("SGCG_PROJECT_START_POINT_TITLE") : Loc.getMessage("SGCG_GROUP_START_POINT_TITLE"));
			}
			else
			{
				date.toggleVisible(!baseEvent.data.checked);
				date.changeTitle(baseEvent.data.checked ?
					Loc.getMessage("SGCG_PROJECT_RANGE_TITLE") : Loc.getMessage("SGCG_GROUP_START_POINT_TITLE"));
			}
		});
		return date;
	}

	createRangeDate()
	{
		const dateRange = new DateRangeField({
			fieldTitle: Loc.getMessage("SGCG_PROJECT_RANGE_TITLE"),
			fieldName: "project_term",
			visible: false
		});
		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			if (!this.isProject)
			{
				dateRange.toggleVisible(baseEvent.data.checked);
			}
		});
		return dateRange;
	}
}