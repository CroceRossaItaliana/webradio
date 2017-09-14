<?php
//20101015.007
$PAGE_TITLE = "Maglie";

$FIELDS = Objects::getMaglieFields();
$TABLE = "maglie";

$PAGE_CONTENT = "<h1>Gestione maglie</h1>\n";

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
      "jsCheckForm" => "ADMIN.validateObject('Maglie', '" . $_REQUEST["cmd2"] . "')",
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
    $REGISTRY->setValue("lastMaglieChange", time());
    break;

  case "del":
    if(!Methods::getNumeroUnitaCRIPerMaglia($_GET["id"])) {
      $config = array(
        "idValue" => $_GET["id"],
        "idField" => BasicTable::getIdField($FIELDS),
        "fields" => $FIELDS,
        "table" => $TABLE,
      );
      $deleteRecord = BasicTable::deleteRecord($config);
      $PAGE_CONTENT .= $deleteRecord["htmlCode"];
      $REGISTRY->setValue("lastMaglieChange", time());
    }
    break;

  default:
    $config = array(
      "title" => "Maglie",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"field", "hideColumn"=>true),
        "codice" => array("name"=>"Codice", "type"=>"field"),
        "provincia" => array("name"=>"Provincia", "type"=>"field"),
        "canale" => array("name"=>"Canale", "type"=>"field"),
        "selettive" => array("name"=>"Selettive", "type"=>"field"),
        "lunghezzaCollegamento" => array("name"=>"Lunghezza Collegamento", "type"=>"arrayIndex", "values"=>$MAGLIA_LUNGHEZZA_COLLEGAMENTO),
      ),
      "table" => $TABLE,
      "disableDelete" => "Methods::getNumeroUnitaCRIPerMaglia(%object%->id)",
      "defaultOrder" => array("col"=>"codice", "dir"=>"ASC"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco delle maglie</a></div>\n";
?>