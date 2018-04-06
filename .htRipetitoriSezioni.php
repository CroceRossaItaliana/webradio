<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180406.032
 */

list($filtroUnitaCri, $unitaCriValues, $WHERE, $extraQuerySet) = setObjectFilter();
if($_REQUEST["idRipetitore"] && is_numeric($_REQUEST["idRipetitore"])) {
  $rsRipetitore = $DBL->query("SELECT id, tipo, localitaCollegata FROM ripetitori WHERE id=" . $_REQUEST["idRipetitore"] . " AND " . $WHERE);
  if($rsRipetitore->num_rows == 1) {
    $rcRipetitore = $rsRipetitore->fetch_object();
  }
}
if(!$rcRipetitore) die(header("Location: " . $_SERVER["SCRIPT_NAME"] . "?cmd=Ripetitori"));

$PAGE_TITLE = "Sezioni del Ripetitore";

$FIELDS = Objects::getRipetitoriSezioniFields();
$TABLE = "ripetitoriSezioni";
$WHERE = "idRipetitore=" . $_REQUEST["idRipetitore"];

$PAGE_CONTENT = "<h1>Sezioni del ripetitore " . $RIPETITORE_TIPO[$rcRipetitore->tipo] . " sito di " . $rcRipetitore->localitaCollegata . "</h1>\n";

switch($_REQUEST["cmd2"]) {
  case "show":
  case "edit":
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "idValue" => $_GET["id"],
      "table" => $TABLE,
      "where" => $WHERE,
    );
    $old = BasicTable::getOldData($config);
    if(!is_array($old)) jsRedirect("/");
    //continue...

  case "add":
    if(!isActionAllowed("ripetitoriSezioni")) jsRedirect("/");
    $config = array(
      "title" => ucwords(BasicTable::$actions[$_REQUEST["cmd2"]]) . " Sezione del Ripetitore",
      "jsCheckForm" => "ADMIN.validateObject('RipetitoriSezioni', '" . $_REQUEST["cmd2"] . "')",
      "action" => $_REQUEST["cmd2"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $old,
      "linkParams" => array("idRipetitore"),
    );
    $PAGE_CONTENT .= BasicTable::getForm($config);
    break;

  case "save":
    $FIELDS["idRipetitore"]["manualQuery"] = true;
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $_POST,
      "table" => $TABLE,
      "where" => $WHERE,
      "extraQuerySet" => "idRipetitore=" . $_REQUEST["idRipetitore"] . ", ultimaModificaData=NOW(), ultimaModificaUser='" . $DBL->real_escape_string(stripslashes($LOGIN->getUserData("username"))) . "'",
      "linkParams" => array("idRipetitore"),
    );
    $saveRecord = BasicTable::saveRecord($config);
    $PAGE_CONTENT .= $saveRecord["htmlCode"];
    logMessage((($saveRecord["action"] == "add") ? "Inserita" : "Modificata") . " sezione del ripetitore codice #" . $saveRecord["recordId"]);
    break;

  case "del":
    if(!isActionAllowed("ripetitoriSezioni")) jsRedirect("/");
    $config = array(
      "idValue" => $_GET["id"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "table" => $TABLE,
      "where" => $WHERE,
      "linkParams" => array("idRipetitore"),
    );
    $deleteRecord = BasicTable::deleteRecord($config);
    $PAGE_CONTENT .= $deleteRecord["htmlCode"];
    logMessage("Cancellata sezione del ripetitore codice #" . $_GET["id"]);
    break;

  default:
    $config = array(
      "title" => "Sezioni del Ripetitore",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"field", "hideColumn"=>true),
        "localitaCollegata" => array("name"=>"Località collegata", "type"=>"field"),
        "funzione" => array("name"=>"Funzione", "type"=>"field"),
        "tipo" => array("name"=>"Tipo", "type"=>"arrayIndex", "values"=>$RIPETITORE_SEZIONE_TIPO),
        "modelloRipetitore" => array("name"=>"Modello", "type"=>"arrayIndex", "values"=>$_SESSION["CACHE_MODELLI_RIPETITORE"]),
        "frequenzaTrasmissione" => array("name"=>"Freq. TX", "type"=>"field"),
        "frequenzaRicezione" => array("name"=>"Freq. RX", "type"=>"field"),
        "canale" => array("name"=>"Canale", "type"=>"field"),
      ),
      "table" => $TABLE,
      "where" => $WHERE,
      "defaultOrder" => array("col"=>"id", "dir"=>"DESC"),
      "disableCreate" => !isActionAllowed("ripetitoriSezioni"),
      "disableEdit" => !isActionAllowed("ripetitoriSezioni"),
      "disableDelete" => !isActionAllowed("ripetitoriSezioni"),
      "disablePagination" => true,
      "linkParams" => array("idRipetitore"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "
  <div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;idRipetitore=" . $_REQUEST["idRipetitore"] . "'>Torna all'elenco delle sezioni del ripetitore</a></div>
  <div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Ripetitori'>Torna all'elenco dei ripetitori</a></div>
";
?>