<?
if($updater->CanUpdateDatabase() && $updater->TableExists("b_sonet_group"))
{
	$updater->Query(array(
		"MySQL" => "update b_sonet_log SET EVENT_ID='blog_post' where EVENT_ID='blog_post_micro'",
		"MSSQL" => "update b_sonet_log SET EVENT_ID='blog_post' where EVENT_ID='blog_post_micro'",		
		"Oracle" => "update b_sonet_log SET EVENT_ID='blog_post' where EVENT_ID='blog_post_micro'",
	));
}
?>