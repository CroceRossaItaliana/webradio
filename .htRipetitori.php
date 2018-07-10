<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180708.045
 */

$PAGE_TITLE = "Ripetitori";

$FIELDS = Objects::getRipetitoriFields();
$TABLE = "ripetitori";
$WHERE = "";

list($filtroUnitaCri, $unitaCriValues, $WHERE, $extraQuerySet) = setObjectFilter();
if(is_array($unitaCriValues)) $FIELDS["unitaCri"]["values"] = $unitaCriValues;

$PAGE_CONTENT = "<h1>Gestione ripetitori</h1>\n";

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
    if(!isActionAllowed("ripetitori")) jsRedirect("/");
    $config = array(
      "title" => ucwords(BasicTable::$actions[$_REQUEST["cmd2"]]) . " Ripetitore",
      "jsCheckForm" => "ADMIN.validateObject('Ripetitori', '" . $_REQUEST["cmd2"] . "')",
      "action" => $_REQUEST["cmd2"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $old,
    );
    $PAGE_CONTENT .= BasicTable::getForm($config);
    if($_REQUEST["cmd2"] == "show") {
      $PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=RipetitoriSezioni&amp;idRipetitore=" . $_GET["id"] . "' title='Gestione sezioni'>Visualizza sezioni del ripetitore</a></div>\n";
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
    logMessage((($saveRecord["action"] == "add") ? "Inserito" : "Modificato") . " ripetitore codice #" . $saveRecord["recordId"]);
    mail("antonio.oliveri@cri.it", "TLCensus - Modifica ripetitore", "L'utente " . $LOGIN->getUserData("username") . " ha aggiunto/modificato il ripetitore #" . $_POST[BasicTable::getIdField($FIELDS)] . " il " . date("d/m/Y") . " alle " . date("H:i:s"));
    break;

  case "del":
    if(!isActionAllowed("ripetitori")) jsRedirect("/");
    $config = array(
      "idValue" => $_GET["id"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "table" => $TABLE,
      "where" => $WHERE,
    );
    $deleteRecord = BasicTable::deleteRecord($config);
    $PAGE_CONTENT .= $deleteRecord["htmlCode"];
    logMessage("Cancellato ripetitore codice #" . $_GET["id"]);
    break;

  case "download":
    header("Pragma: ");
    header("Cache-control: ");
    header("Content-type: text/csv");
    header("Content-Disposition: inline; filename=\"ripetitori.csv\"");
    echo(utf8_decode("Maglia\tTipo\tNumero\tLocalita Collegata\tPosizione e altitudine\tOspitante\tContratto Locazione\tCanone Affitto\tQuota Canone\tFonte Alimentazione 1\tFonte Alimentazione 2\tContatore Enel A Carico Cri\tContratto Assistenza\tRiferimento Assistenza\tNote\n"));
    echo(utf8_decode("\t\tMatricola\tTipo\tLocalita Collegata\tFunzione\tModello Ripetitore\tModello Antenna 1\tPolarizzazione Antenna 1\tModello Antenna 2\tPolarizzazione Antenna 2\tModello Antenna 3\tPolarizzazione Antenna 3\tCanale\tIdentita\tTono Sub Audio\tFrequenza Ricezione\tFrequenza Trasmissione\tMmodalita Sincronizzazione\tNote\n\n"));
    $rs = $DBL->query("SELECT * FROM " . $TABLE . " WHERE " . $WHERE);
    while($rc = $rs->fetch_object()) {
      $posizioneAltitudine = Methods::getPosizioneFormat($rc->posizione, $rc->altitudine);
      echo(utf8_decode(escapeCsv(getMagliaById($rc->maglia)) . "\t" . escapeCsv($RIPETITORE_TIPO[$rc->tipo]) . "\t" . escapeCsv($rc->numero) . "\t" . escapeCsv($rc->localitaCollegata) . "\t" . escapeCsv(html_entity_decode($posizioneAltitudine["htmlCode"])) . "\t" . escapeCsv($rc->ospitante) . "\t" . escapeCsv($rc->contrattoLocazione) . "\t" . escapeCsv($rc->canoneAffitto) . "\t" . escapeCsv($rc->quotaCanone) . "\t" . escapeCsv($RIPETITORE_ALIMENTAZIONE_1[$rc->fonteAlimentazione1]) . "\t" . escapeCsv($RIPETITORE_ALIMENTAZIONE_2[$rc->fonteAlimentazione2]) . "\t" . escapeCsv($rc->contatoreEnelACaricoCri) . "\t" . escapeCsv($rc->contrattoAssistenza) . "\t" . escapeCsv($rc->riferimentoAssistenza) . "\t" . escapeCsv($rc->note) . "\n"));
      $rsS = $DBL->query("SELECT * FROM ripetitoriSezioni WHERE idRipetitore=" . $rc->id);
      while($rcS = $rsS->fetch_object()) {
        $ripetitore = getRipetitoreById($rc->modelloRipetitore);
        $ripetitore = $ripetitore["produttore"] . " - " . $ripetitore["modello"];
        $antenna1 = getAntennaById($rc->modelloAntenna1);
        $antenna1 = $antenna1["produttore"] . " - " . $antenna1["modello"];
        $antenna2 = getAntennaById($rc->modelloAntenna2);
        $antenna2 = $antenna2["produttore"] . " - " . $antenna2["modello"];
        $antenna3 = getAntennaById($rc->modelloAntenna3);
        $antenna3 = $antenna3["produttore"] . " - " . $antenna3["modello"];
        $tonoSubAudio = getTonoSubAudioById($rc->tonoSubAudio);
        $modalitaSincronizzazione = getModalitaSincronizzazioneById($rc->modalitaSincronizzazione);
        echo(utf8_decode("\tSezione:\t" . escapeCsv($rc->matricola) . "\t" . escapeCsv($RIPETITORE_SEZIONE_TIPO[$rc->tipo]) . "\t" . escapeCsv($rc->localitaCollegata) . "\t" . escapeCsv($rc->funzione) . "\t" . escapeCsv($ripetitore) . "\t" . escapeCsv($antenna1) . "\t" . escapeCsv($ANTENNE_POLARIZZAZIONI[$rc->polarizzazioneAntenna1]) . "\t" . escapeCsv($antenna2) . "\t" . escapeCsv($ANTENNE_POLARIZZAZIONI[$rc->polarizzazioneAntenna2]) . "\t" . escapeCsv($antenna3) . "\t" . escapeCsv($ANTENNE_POLARIZZAZIONI[$rc->polarizzazioneAntenna3]) . "\t" . escapeCsv($rc->canale) . "\t" . escapeCsv($rc->identita) . "\t" . escapeCsv($tonoSubAudio) . "\t" . escapeCsv($rc->frequenzaRicezione) . "MHz\t" . escapeCsv($rc->frequenzaTrasmissione) . "MHz\t" . escapeCsv($modalitaSincronizzazione) . "\t" . escapeCsv($rc->note) . "\n"));
      }
      echo("\n");
    }
    die();
    break;

  default:
    $config = array(
      "title" => "Ripetitori",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"function", "function"=>"getRadioRipetitoreIdWithDocsLink", "fields"=>array("id"), "params"=>array("Ripetitori", "\$rc->id"), "disableSort"=>true, "hideColumn"=>true),
        "numero" => array("name"=>"Numero", "type"=>"field"),
        "unitaCri" => ($LOGIN->getUserData("type") == "5") ? null : array("name"=>"Unità", "type"=>"field"),
        "maglia" => array("name"=>"Maglia", "type"=>"arrayIndex", "values"=>getElencoMaglie()),
        "tipo" => array("name"=>"Tipo", "type"=>"arrayIndex", "values"=>$RIPETITORE_TIPO),
        "localitaCollegata" => array("name"=>"Località", "type"=>"field"),
        "posizione" => array("name"=>"Posizione", "type"=>"function", "function"=>"getPosizioneFormat", "fields"=>array("posizione", "altitudine"), "params"=>array("\$rc->posizione", "\$rc->altitudine"), "disableSort"=>true),
        "sezioni" => array("name"=>"Sezioni", "type"=>"function", "function"=>"getRipetitoreSezioni", "fields"=>array("id"), "params"=>array("\$rc->id"), "disableSort"=>true),
      ),
      "table" => $TABLE,
      "where" => $WHERE,
      "filters" => array(
        "key" => array("name"=>"Key", "type"=>"keyword", "query"=>"id='%%value%%' OR localitaCollegata LIKE '%%%value%%%'"),
        "maglia" => ($LOGIN->getUserData("type") == "5") ? null : array("name"=>"Maglia", "type"=>"select", "values"=>getElencoMaglie(), "query"=>"maglia=%%value%%", "width"=>"250"),
        "unitaCri" => ($LOGIN->getUserData("type") == "5") ? null : array("name"=>"Unità CRI", "type"=>"select", "values"=>$FIELDS["unitaCri"]["values"], "query"=>"unitaCri='%%value%%'", "width"=>"200"),
        "tipo" => array("name"=>"Tipo", "type"=>"select", "values"=>$RIPETITORE_TIPO, "query"=>"tipo='%%value%%'", "width"=>250),
      ),
      "defaultOrder" => array("col"=>"id", "dir"=>"DESC"),
      "disableCreate" => !isActionAllowed("ripetitori"),
      "disableEdit" => !isActionAllowed("ripetitori"),
      "disableDelete" => !isActionAllowed("ripetitori"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div>
  <a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco dei ripetitori</a><br />
  <a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=download' target='_blank'>Scarica l'elenco dei ripetitori</a><br />
</div>\n";
?>