<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180321.003
 */

$PAGE_TITLE = "Messaggi";

$FIELDS = Objects::getMessaggiFields();
$TABLE = "messaggi";

$PAGE_CONTENT = "<h1>Gestione messaggi</h1>\n";

switch($_REQUEST["cmd2"]) {
  case "show":
  case "edit":
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "idValue" => $_GET["id"],
      "table" => $TABLE,
    );
    $old = BasicTable::getOldData($config);
    if(!is_array($old)) {
      $_REQUEST["cmd2"] = "add";
    }
    //continue...

  case "add":
    $config = array(
      "jsCheckForm" => "ADMIN.validateObject('Messaggi', '" . $_REQUEST["cmd2"] . "')",
      "action" => $_REQUEST["cmd2"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $old,
    );
    $PAGE_CONTENT .= BasicTable::getForm($config);
    break;

  case "save":
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $_POST,
      "table" => $TABLE,
    );
    $saveRecord = BasicTable::saveRecord($config);
    $PAGE_CONTENT .= $saveRecord["htmlCode"];
    break;

  case "del":
    $config = array(
      "idValue" => $_GET["id"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "table" => $TABLE,
    );
    $deleteRecord = BasicTable::deleteRecord($config);
    $PAGE_CONTENT .= $deleteRecord["htmlCode"];
    break;

  default:
    $config = array(
      "title" => "Messaggi",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"field", "hideColumn"=>true),
        "dataMessaggio" => array("name"=>"Data", "type"=>"field"),
        "destinatario" => array("name"=>"Destinatario", "type"=>"field"),
        "requireLogin" => array("name"=>"Login", "type"=>"arrayIndex", "values"=>BasicTable::$basicStatus),
        "oggetto" => array("name"=>"Oggetto", "type"=>"field"),
      ),
      "table" => $TABLE,
      "defaultOrder" => array("col"=>"dataMessaggio", "dir"=>"DESC"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco dei messaggi</a></div>\n";
?>