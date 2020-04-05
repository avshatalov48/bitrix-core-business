<?
$MESS["CLUWIZ_NO_MODULE_ERROR"] = "The Web Cluster module is not installed. The wizard will now abort.";
$MESS["CLUWIZ_NO_NODE_ERROR"] = "The module database is not specified. The wizard will not abort.";
$MESS["CLUWIZ_NO_CONN_ERROR"] = "Error connecting to the database. The wizard will not abort.";
$MESS["CLUWIZ_STEP1_TITLE1"] = "Database Usage Wizard";
$MESS["CLUWIZ_STEP1_CONTENT1"] = "<p>To start using the database#database#, one of the modules must be migrated to it .</p><p>Select a module to transfer:</p>#module_select_list#<p>This module will be disabled while processing it for migration. The website will remain up and running.</p>";
$MESS["CLUWIZ_STEP1_CONTENT2"] = "<p>To stop using the database, the module must be migrated to another database.</p><p>Select the destination database:</p>#module_select_list#<p>This module will be disabled while processing it for migration. The website will remain up and running.</p>";
$MESS["CLUWIZ_STEP1_TITLE2"] = "Module Migration Wizard";
$MESS["CLUWIZ_STEP2_TITLE"] = "Checking For The Availability Of The Module Tables";
$MESS["CLUWIZ_STEP2_NO_TABLES"] = "No tables have been found; data transfer is safe to start.";
$MESS["CLUWIZ_STEP2_TABLES_EXIST"] = "The specified database contains tables. Those tables must be deleted to continue.";
$MESS["CLUWIZ_STEP2_TABLES_LIST"] = "view tables";
$MESS["CLUWIZ_STEP2_DELETE_TABLES"] = "Allow tables in the database #database# to be deleted";
$MESS["CLUWIZ_STEP3_TITLE"] = "Deleting Tables";
$MESS["CLUWIZ_STEP4_TITLE"] = "Moving Tables";
$MESS["CLUWIZ_FINALSTEP_BUTTONTITLE"] = "Ready";
$MESS["CLUWIZ_CANCELSTEP_TITLE"] = "The wizard has been canceled.";
$MESS["CLUWIZ_CANCELSTEP_BUTTONTITLE"] = "Close";
$MESS["CLUWIZ_CANCELSTEP_CONTENT"] = "The wizard has been canceled.";
$MESS["CLUWIZ_DATABASE_NOT_SUPPORTED"] = "The wizard does not support the specified database type.";
?>