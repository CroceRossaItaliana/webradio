<?php
//20101005.007
$PAGE_TITLE = "Modalità di sincronizzazione";

$FIELDS = Objects::getModalitaSincronizzazioneFields();
$TABLE = "modalitaSincronizzazione";

$PAGE_CONTENT = "<h1>Gestione modalità di sincronizzazione</h1>\n";

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
      "jsCheckForm" => "ADMIN.validateObject('ModalitaSincronizzazione', '" . $_REQUEST["cmd2"] . "')",
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
    $REGISTRY->setValue("lastModalitaSincronizzazioneChange", time());
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
    $REGISTRY->setValue("lastModalitaSincronizzazioneChange", time());
    break;

  default:
    $config = array(
      "title" => "Modalità di sincronizzazione",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"field", "hideColumn"=>true),
        "modalitaSincronizzazione" => array("name"=>"Modalità di sincronizzazione", "type"=>"field"),
      ),
      "table" => $TABLE,
      "disablePagination" => true,
      "defaultOrder" => array("col"=>"modalitaSincronizzazione", "dir"=>"ASC"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco delle modalità di sincronizzazione</a></div>\n";
?>