<?
$MESS ['VIRUS_DETECTED_NAME'] = "Virus detected";
$MESS ['VIRUS_DETECTED_DESC'] = "#EMAIL# - Site administrator's e-mail address (from the Kernel module settings)";
$MESS ['VIRUS_DETECTED_SUBJECT'] = "#SITE_NAME#:  Virus detected";
$MESS ['VIRUS_DETECTED_MESSAGE'] = "Informational message from #SITE_NAME#

------------------------------------------

You have received this message as a result of the detection of potentially dangerous code by the proactive protection system of #SERVER_NAME#.

1.  The potentially dangerous code has been cut from the html. 
2.  Check the event log and make sure that the code is indeed harmful, and is not simply a counter or framework.
	(link: http://#SERVER_NAME#/bitrix/admin/event_log.php?lang=en&set_filter=Y&find_type=audit_type_id&find_audit_type[]=SECURITY_VIRUS )
3.  If the code is not harmful, add it to the 'exceptions' list on the antivirus settings page. 
	(link: http://#SERVER_NAME#/bitrix/admin/security_antivirus.php?lang=en&tabControl_active_tab=exceptions )
4.  If the code is a virus, then complete the following steps:

	a) Change the login password for the administrator and other responsible users to the site.
	b) Change the login password for ssh and ftp. 
	c) Test and remove viruses from computers of administrators who have access to the site through ssh or ftp. 
	d) Turn off password saving in programs which provide access to the site through ssh or ftp. 
	e) Delete the harmful code from the infected files.  For example, re-install the infected files using the most recent backup.  

---------------------------------------------------------------------
This message has been automatically generated.";
?>