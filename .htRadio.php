<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180508.058
 */

$PAGE_TITLE = "Radio";

$FIELDS = Objects::getRadioFields();
$TABLE = "radio";
$WHERE = "";

list($filtroUnitaCri, $unitaCriValues, $WHERE, $extraQuerySet) = setObjectFilter();
if(is_array($unitaCriValues)) $FIELDS["unitaCri"]["values"] = $unitaCriValues;

$PAGE_CONTENT = "<h1>Gestione radio</h1>\n";

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
    if(!isActionAllowed("radio")) jsRedirect("/");
    if(in_array($_REQUEST["cmd2"], array("add", "edit"))) {
      $FIELDS["ripetitoreId"]["type"] = "select";
    }
    if($_REQUEST["cmd2"] == "edit") {
      $FIELDS["ripetitoreId"]["values"] = array();
      $rsRip = $DBL->query("SELECT id, numero, localitaCollegata FROM ripetitori WHERE maglia=" . $old["maglia"]);
      while($rcRip = $rsRip->fetch_object()) {
        $FIELDS["ripetitoreId"]["values"][$rcRip->id] = $rcRip->numero . ": " . $rcRip->localitaCollegata;
      }
    }
    if($_REQUEST["cmd2"] == "show") {
      if(is_numeric($old["ripetitoreId"])) {
        $old["ripetitoreId"] = $DBL->query("SELECT id, numero, localitaCollegata FROM ripetitori WHERE id=" . $old["ripetitoreId"])->fetch_object();
        $old["ripetitoreId"] = $old["ripetitoreId"]->numero . ": " . $old["ripetitoreId"]->localitaCollegata;
      }
    }
    $config = array(
      "title" => ucwords(BasicTable::$actions[$_REQUEST["cmd2"]]) . " Radio",
      "jsCheckForm" => "ADMIN.validateObject('Radio', '" . $_REQUEST["cmd2"] . "')",
      "action" => $_REQUEST["cmd2"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $old,
    );
    $PAGE_CONTENT .= BasicTable::getForm($config);
    if($LOGIN->getUserData("type") == "5") {
      $PAGE_CONTENT .= "<input type='hidden' id='unitaCri' value=\"" . $LOGIN->getUserData("username") . "\" />";
    }
    if(in_array($_REQUEST["cmd2"], array("add", "edit"))) {
      $PAGE_CONTENT .= "<script type='text/javascript'>ADMIN.addEvent(document.getElementById('maglia'), 'change', ADMIN.radioAddEditMagliaChanged);</script>\n";
    }
    break;

  case "save":
    $extraQuerySet = "ultimaModificaData=NOW(), ultimaModificaUser='" . $DBL->real_escape_string(stripslashes($LOGIN->getUserData("username"))) . "'" . (($extraQuerySet) ? (", " . $extraQuerySet) : "");
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $_POST,
      "table" => $TABLE,
      "where" => $WHERE,
      "extraQuerySet" => $extraQuerySet,
    );
    $saveRecord = BasicTable::saveRecord($config);
    $PAGE_CONTENT .= $saveRecord["htmlCode"];
    $logMessage = (($saveRecord["action"] == "add") ? "Inserita" : "Modificata") . " radio codice #" . $saveRecord["recordId"];
    logMessage($logMessage);
    // Applicazione webradio - durante il salvataggio di una modifica, l'applicazione si blocca
    // Ticket 21955 - A.S. - 06/05/2015
    // mail($ADMIN_EMAIL, "Nuova radio", $logMessage);
    break;

  case "del":
    if(!isActionAllowed("radio")) jsRedirect("/");
    $config = array(
      "idValue" => $_GET["id"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "table" => $TABLE,
      "where" => $WHERE,
    );
    $deleteRecord = BasicTable::deleteRecord($config);
    $PAGE_CONTENT .= $deleteRecord["htmlCode"];
    logMessage("Cancellata radio codice #" . $_GET["id"]);
    break;

  case "download":
    header("Pragma: ");
    header("Cache-control: ");
    header("Content-type: text/csv");
    header("Content-Disposition: inline; filename=\"radio.csv\"");
    echo(utf8_decode("Maglia\tUnità di appartenenza\tMatricola\tTipo\tLocalità\tPosizione e altitudine\tID Ripetitore\tModello Radio\tModello Antenna\tSigla Radio\tNumero Inventario\tTarga Automezzo\tUtilizzatore\tContratto Assistenza\tRiferimento Assistenza\tNote\n"));
    $rs = $DBL->query("SELECT * FROM " . $TABLE . " WHERE " . $WHERE);
    while($rc = $rs->fetch_object()) {
      $posizioneAltitudine = Methods::getPosizioneFormat($rc->posizione, $rc->altitudine);
      $ripetitore = $DBL->query("SELECT id, numero, localitaCollegata FROM ripetitori WHERE id=" . $rc->ripetitoreId)->fetch_object();
      $ripetitore = $ripetitore->numero . ": " . $ripetitore->localitaCollegata;
      $rc->modelloRadio = getRadioById($rc->modelloRadio);
      $rc->modelloRadio = $rc->modelloRadio["produttore"] . " - " . $rc->modelloRadio["modello"];
      $rc->modelloAntenna = getAntennaById($rc->modelloAntenna);
      $rc->modelloAntenna = $rc->modelloAntenna["produttore"] . " - " . $rc->modelloAntenna["modello"];
      echo(utf8_decode(escapeCsv(getMagliaById($rc->maglia)) . "\t" . escapeCsv($rc->unitaCri) . "\t" . escapeCsv($rc->matricola) . "\t" . escapeCsv($RADIO_TIPO[$rc->tipo]) . "\t" . escapeCsv($rc->localita) . "\t" . escapeCsv(html_entity_decode($posizioneAltitudine["htmlCode"])) . "\t" . escapeCsv($ripetitore) . "\t" . escapeCsv($rc->modelloRadio) . "\t" . escapeCsv($rc->modelloAntenna) . "\t" . escapeCsv($rc->siglaRadio) . "\t" . escapeCsv($rc->numeroInventario) . "\t" . escapeCsv($rc->targaAutomezzo) . "\t" . escapeCsv($rc->utilizzatore) . "\t" . escapeCsv($rc->contrattoAssistenza) . "\t" . escapeCsv($rc->riferimentoAssistenza) . "\t" . escapeCsv($rc->note) . "\n"));
    }
    die();
    break;

  case "pdf":
    require(".htPDFElencoRadioClass.php");
    $pdf = new PDF("L", "pt", "A4");
    $pdf->AliasNbPages();
    $pdf->SetAutoPageBreak(false);
    $pdf->SetAuthor("Croce Rossa Italiana", true);
    $pdf->SetCreator("Croce Rossa Italiana", true);
    $pdf->SetSubject("Elenco Radio", true);
    $pdf->SetTitle("Elenco Radio", true);

    //Genera elenco radio
    $pdf->drawRadio($WHERE);

    //Butta fuori PDF
    $pdf->Output();

    logMessage("Generato elenco radio PDF");
    die();
    break;

  default:
    $config = array(
      "title" => "Radio",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"function", "function"=>"getRadioRipetitoreIdWithDocsLink", "fields"=>array("id"), "params"=>array("Radio", "\$rc->id"), "disableSort"=>true, "hideColumn"=>true),
        "unitaCri" => ($LOGIN->getUserData("type") == "5") ? null : array("name"=>"Unità", "type"=>"field"),
        "maglia" => array("name"=>"Maglia", "type"=>"arrayIndex", "values"=>getElencoMaglie()),
        "matricola" => array("name"=>"Matricola", "type"=>"field"),
        "tipo" => array("name"=>"Tipo", "type"=>"arrayIndex", "values"=>$RADIO_TIPO),
        "modelloRadio" => array("name"=>"Modello", "type"=>"arrayIndex", "values"=>$_SESSION["CACHE_MODELLI_RADIO"]),
        "siglaRadio" => array("name"=>"Selettiva", "type"=>"field"),
      ),
      "table" => $TABLE,
      "where" => $WHERE,
      "filters" => array(
        "key" => array("name"=>"Key", "type"=>"keyword", "query"=>"id='%%value%%' OR matricola LIKE '%%%value%%%' OR siglaRadio='%%value%%' OR targaAutomezzo LIKE '%%%value%%%'"),
        "maglia" => ($LOGIN->getUserData("type") == "5") ? null : array("name"=>"Maglia", "type"=>"select", "values"=>getElencoMaglie(), "query"=>"maglia=%%value%%", "width"=>"250"),
        "unitaCri" => ($LOGIN->getUserData("type") == "5") ? null : array("name"=>"Unità CRI", "type"=>"select", "values"=>$FIELDS["unitaCri"]["values"], "query"=>"unitaCri='%%value%%'", "width"=>200),
        "modelloRadio" => array("name"=>"Modello", "type"=>"select", "values"=>$_SESSION["CACHE_MODELLI_RADIO"], "query"=>"modelloRadio='%%value%%'", "width"=>200),
        "tipo" => array("name"=>"Tipo", "type"=>"select", "values"=>$RADIO_TIPO, "query"=>"tipo='%%value%%'", "width"=>150),
      ),
      "defaultOrder" => array("col"=>"id", "dir"=>"DESC"),
      "disableCreate" => !isActionAllowed("radio"),
      "disableEdit" => !isActionAllowed("radio"),
      "disableDelete" => !isActionAllowed("radio"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div>
  <a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco delle radio</a><br />
  <a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=download' target='_blank'>Scarica l'elenco delle radio in formato CSV</a><br />
  <a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=pdf' target='_blank'>Scarica l'elenco delle radio in formato PDF</a><br />
</div>\n";
?>