<?php

IncludeModuleLangFile(__FILE__);

define("BLOG_PERMS_DENY", "D");	/* CAN'T EVEN LOOK AT ANY OBJECTS*/
define("BLOG_PERMS_READ", "I");	/* CAN ONLY READ ALL OBJECTS */
define("BLOG_PERMS_PREMODERATE", "K");	/* CAN READ ALL OBJECTS, CAN WRITE OWN HIDDEN OBJECTS */
define("BLOG_PERMS_WRITE", "P");	/* CAN READ ALL OBJECTS, CAN WRITE OWN OBJECTS */
define("BLOG_PERMS_MODERATE", "T");	/* CAN READ ALL OBJECT, CAN WRITE OWN OBJECTS, CAN HIDE AND SHOW ALL OBLECT */
define("BLOG_PERMS_FULL", "W");	/* CAN READ ALL OBJECT, CAN WRITE ALL OBJECTS */

$GLOBALS["AR_BLOG_PERMS"] = array(
	BLOG_PERMS_DENY => GetMessage("BLI_P_D"),
	BLOG_PERMS_READ => GetMessage("BLI_P_I"),
	BLOG_PERMS_PREMODERATE => GetMessage("BLI_P_K"),
	BLOG_PERMS_WRITE => GetMessage("BLI_P_P"),
	BLOG_PERMS_MODERATE => GetMessage("BLI_P_T"),
	BLOG_PERMS_FULL => GetMessage("BLI_P_W")
);
$GLOBALS["AR_BLOG_PERMS_EVERYONE"] = array(
	"D" => GetMessage("BLI_P_D"),
	"I" => GetMessage("BLI_P_I"),
);

$GLOBALS["AR_BLOG_POST_PERMS"] = array(
	BLOG_PERMS_DENY, 
	BLOG_PERMS_READ,
	BLOG_PERMS_PREMODERATE,
	BLOG_PERMS_WRITE, 
	BLOG_PERMS_MODERATE,
	BLOG_PERMS_FULL
	);
$GLOBALS["AR_BLOG_COMMENT_PERMS"] = array(
	BLOG_PERMS_DENY, 
	BLOG_PERMS_READ, 
	BLOG_PERMS_PREMODERATE,
	BLOG_PERMS_WRITE, 
	BLOG_PERMS_MODERATE,
	BLOG_PERMS_FULL
	);

define("BLOG_PERMS_POST", "P");
define("BLOG_PERMS_COMMENT", "C");

define("BLOG_PUBLISH_STATUS_DRAFT", "D");
define("BLOG_PUBLISH_STATUS_READY", "K");
define("BLOG_PUBLISH_STATUS_PUBLISH", "P");

$GLOBALS["AR_BLOG_PUBLISH_STATUS"] = array(
	"D" => GetMessage("BLI_PS_D"),
	"K" => GetMessage("BLI_PS_K"),
	"P" => GetMessage("BLI_PS_P")
);

define("BLOG_BY_USER_ID", 1);
define("BLOG_BY_BLOG_USER_ID", 2);

define("BLOG_ADD", 1);
define("BLOG_CHANGE", 2);
define("BLOG_RESET", 3);

$GLOBALS["AR_BLOG_RESERVED_NAMES"] = array("admin", "users", "group", "rss", "new", "user", "user_friends", "search", "user_settings", "user_settings_edit", "group_edit", "blog_edit", "category_edit", "post_edit", "draft", "moderation", "trackback", "post", "post_rss", "rss", "rss_all", "index");
$GLOBALS["AR_BLOG_POST_RESERVED_CODES"] = Array("admin", "users", "index", "group", "blog", "user", "user_friends", "search", "user_settings", "user_settings_edit", "group_edit", "blog_edit", "category_edit", "post_edit", "draft", "moderation", "trackback", "post", "post_rss", "rss", "rss_all", "new");

CModule::AddAutoloadClasses(
	"blog",
	array(
		"CBlog" => "mysql/blog.php",
		"CBlogCandidate" => "mysql/blog_candid.php",
		"CBlogGroup" => "mysql/blog_group.php",
		"CBlogImage" => "mysql/blog_image.php",
		"CBlogPost" => "mysql/blog_post.php",
		"CBlogCategory" => "mysql/blog_category.php",
		"CBlogComment" => "mysql/blog_comment.php",
		"CBlogUser" => "mysql/blog_user.php",
		"CBlogUserGroup" => "mysql/blog_user_group.php",
		"CBlogTrackback" => "mysql/blog_trackback.php",
		"CBlogUserGroupPerms" => "mysql/blog_user_group_perms.php",
		"CBlogSitePath" => "mysql/blog_site_path.php",
		"CBlogSmile" => "general/smile.php",
		"CBlogPostCategory" => "mysql/blog_post_category.php",

		"CBlogSearch" => "general/blog_search.php",
		"CBlogSoNetPost" => "general/sonet.php",
		"blogTextParser" => "general/functions.php",
		"CBlogTools" => "general/functions.php",
		"CBlogMetaWeblog" => "general/blog_metaweblog.php",
		
		"CRatingsComponentsBlog" => "mysql/ratings_components.php",
		"CBlogNotifySchema" => "general/blog_notify_schema.php",
		"CBlogUserOptions" => "general/blog_post_param.php",
	)
);
