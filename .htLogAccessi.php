<?php
//20110110.004
$PAGE_TITLE = "Log";

$PAGE_CONTENT = "<h1>Log</h1>\n";

$config = array(
  "title" => "Log",
  "idField" => "username",
  "columns" => array(
    "accessData" => array("name"=>"Data", "type"=>"field"),
    "username" => array("name"=>"Utente", "type"=>"field"),
    "ipAddress" => array("name"=>"Indirizzo IP", "type"=>"field"),
    "message" => array("name"=>"Messaggio", "type"=>"field"),
  ),
  "table" => "accessLog",
  "filters" => array(
    "username" => array("name"=>"Utente", "type"=>"keyword", "query"=>"username='%%value%%'"),
  ),
  "defaultOrder" => array("col"=>"accessData", "dir"=>"DESC"),
  "disableCreate" => true,
  "disableShow" => true,
  "disableEdit" => true,
  "disableDelete" => true,
);
$PAGE_CONTENT .= BasicTable::getTable($config);
?>