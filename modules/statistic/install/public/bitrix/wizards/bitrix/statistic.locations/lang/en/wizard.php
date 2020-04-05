<?
$MESS["STATWIZ_NO_MODULE_ERROR"] = "Web Analytics module is not installed. The Wizard cannot continue.";
$MESS["STATWIZ_FILES_NOT_FOUND"] = "No files were found. Load files from www.maxmind.com or ip-to-country.webhosting.info sites to the specified folder and run the wizard again. ";
$MESS["STATWIZ_STEP1_TITLE"] = "IP address index wizard";
$MESS["STATWIZ_STEP1_CONTENT"] = "Welcome to IP address index creation Wizard! This wizard will help you to create IP address index for City and Country lookup.<br />Choose  action:";
$MESS["STATWIZ_STEP1_COUNTRY"] = "IP address index for <b>City</b> lookup.";
$MESS["STATWIZ_STEP1_CITY"] = "IP address index for <b>City</b> and <b>Country</b> lookup.";
$MESS["STATWIZ_STEP1_COUNTRY_NOTE_V2"] = "Supported formats:
<ul>
<li><a target=\"_blank\" href=\"#GEOIP_HREF#\">GeoIP Country</a>.</li>
<li><a target=\"_blank\" href=\"#GEOIPLITE_HREF#\">GeoLite Country</a>.</li>
</ul>";
$MESS["STATWIZ_STEP1_CITY_NOTE"] = "Supported formats:
<ul>
<li><a target=\"_blank\" href=\"#GEOIP_HREF#\">GeoIP City</a>.</li>
<li><a target=\"_blank\" href=\"#GEOIPLITE_HREF#\">GeoLite City</a>.</li>
<li><a target=\"_blank\" href=\"#IPGEOBASE_HREF#\">IpGeoBase</a>.</li>
</ul>";
$MESS["STATWIZ_STEP1_COMMON_NOTE"] = "Unpack archives and upload files to #PATH# catalog. Then you can proceed to the the next wizard step.";
$MESS["STATWIZ_STEP2_TITLE"] = "CSV file selection";
$MESS["STATWIZ_STEP2_COUNTRY_CHOOSEN"] = "You have selected to create IP address index for <b>Country</b> lookup.";
$MESS["STATWIZ_STEP2_CITY_CHOOSEN"] = "You have selected to create IP address index for <b>Country</b> and <b>City</b> lookup.";
$MESS["STATWIZ_STEP2_CONTENT"] = "Search has been performed in \"/bitrix/modules/statistic/ip2country\" folder.";
$MESS["STATWIZ_STEP2_FILE_NAME"] = "Filename";
$MESS["STATWIZ_STEP2_FILE_SIZE"] = "Size";
$MESS["STATWIZ_STEP2_DESCRIPTION"] = "Description";
$MESS["STATWIZ_STEP2_FILE_TYPE_MAXMIND_IP_COUNTRY"] = "GeoIP Country or GeoLite Country database.";
$MESS["STATWIZ_STEP2_FILE_TYPE_IP_TO_COUNTRY"] = "ip-to-country database.";
$MESS["STATWIZ_STEP2_FILE_TYPE_MAXMIND_IP_LOCATION"] = "IP ranges part of GeoIP City or GeoLite City database. Should be loaded after the Locations part.";
$MESS["STATWIZ_STEP2_FILE_TYPE_MAXMIND_CITY_LOCATION"] = "Locations part of GeoIP City or GeoLite City database. Should be loaded first.";
$MESS["STATWIZ_STEP2_FILE_TYPE_IPGEOBASE"] = "IP ranges database IpGeoBase (Russia only). Country index should be loaded first.";
$MESS["STATWIZ_STEP2_FILE_TYPE_UNKNOWN"] = "Unknown format.";
$MESS["STATWIZ_STEP2_FILE_ERROR"] = "No file to load.";
$MESS["STATWIZ_STEP3_TITLE"] = "Index is being created.";
$MESS["STATWIZ_STEP3_LOADING"] = "Processing...";
$MESS["STATWIZ_FINALSTEP_TITLE"] = "Wizard completed";
$MESS["STATWIZ_FINALSTEP_BUTTONTITLE"] = "Finish";
$MESS["STATWIZ_FINALSTEP_COUNTRIES"] = "Countries: #COUNT#.";
$MESS["STATWIZ_FINALSTEP_CITIES"] = "Cities: #COUNT#.";
$MESS["STATWIZ_FINALSTEP_CITY_IPS"] = "IP ranges: #COUNT#. ";
$MESS["STATWIZ_CANCELSTEP_TITLE"] = "Wizard has been canceled";
$MESS["STATWIZ_CANCELSTEP_BUTTONTITLE"] = "Close";
$MESS["STATWIZ_CANCELSTEP_CONTENT"] = "Wizard has been canceled. ";
$MESS["STATWIZ_STEP2_FILE_TYPE_IPGEOBASE2"] = "The second part of the IpGeoBase database; matches IP address to locations. This must be loaded after the first part.";
$MESS["STATWIZ_STEP2_FILE_TYPE_IPGEOBASE2_CITY"] = "The second part of the IpGeoBase database; contains locations. Load the country index first to define countries.";
?>