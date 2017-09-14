<?php
//20110508.006
$PAGE_TITLE = "Strumenti";

$PAGE_CONTENT = "<h1>Strumenti</h1>\n";

switch($_REQUEST["cmd2"]) {
  case "elencoRadio5toni":
    header("Pragma: ");
    header("Cache-control: ");
    header("Content-type: text/csv");
    header("Content-Disposition: inline; filename=\"elencoRadio5Toni.csv\"");
    echo(utf8_decode("Maglia\tUnita CRI\tMatricola\tTipo\tLocalità\tPosizione e altitudine\tModello Radio\tModello Antenna\tSigla Radio\tNumero Inventario\tTarga Automezzo\tUtilizzatore\tCcontratto Assistenza\tRiferimento Assistenza\tNote\n"));
    $rs = $DBL->query("SELECT * FROM radio WHERE CHAR_LENGTH(siglaRadio)<6");
    while($rc = $rs->fetch_object()) {
      $posizioneAltitudine = Methods::getPosizioneFormat($rc->posizione, $rc->altitudine);
      $rc->modelloRadio = getRadioById($rc->modelloRadio);
      if($rc->modelloRadio["omologazione"] == 0) continue;
      $rc->modelloRadio = $rc->modelloRadio["produttore"] . " - " . $rc->modelloRadio["modello"];
      $rc->modelloAntenna = getAntennaById($rc->modelloAntenna);
      $rc->modelloAntenna = $rc->modelloAntenna["produttore"] . " - " . $rc->modelloAntenna["modello"];
      echo(utf8_decode(escapeCsv(getMagliaById($rc->maglia)) . "\t" . escapeCsv($rc->unitaCri) . "\t" . escapeCsv($rc->matricola) . "\t" . escapeCsv($RADIO_TIPO[$rc->tipo]) . "\t" . escapeCsv($rc->localita) . "\t" . escapeCsv(html_entity_decode($posizioneAltitudine["htmlCode"])) . "\t" . escapeCsv($rc->modelloRadio) . "\t" . escapeCsv($rc->modelloAntenna) . "\t" . escapeCsv($rc->siglaRadio) . "\t" . escapeCsv($rc->numeroInventario) . "\t" . escapeCsv($rc->targaAutomezzo) . "\t" . escapeCsv($rc->utilizzatore) . "\t" . escapeCsv($rc->contrattoAssistenza) . "\t" . escapeCsv($rc->riferimentoAssistenza) . "\t" . escapeCsv($rc->note) . "\n"));
    }
    die();
    break;

  case "elencoRadioNonOmologate":
    header("Pragma: ");
    header("Cache-control: ");
    header("Content-type: text/csv");
    header("Content-Disposition: inline; filename=\"elencoRadioNonOmologate.csv\"");
    echo(utf8_decode("Maglia\tUnita CRI\tMatricola\tTipo\tLocalità\tPosizione e altitudine\tModello Radio\tModello Antenna\tSigla Radio\tNumero Inventario\tTarga Automezzo\tUtilizzatore\tCcontratto Assistenza\tRiferimento Assistenza\tNote\n"));
    $rs = $DBL->query("SELECT * FROM radio");
    while($rc = $rs->fetch_object()) {
      $posizioneAltitudine = Methods::getPosizioneFormat($rc->posizione, $rc->altitudine);
      $rc->modelloRadio = getRadioById($rc->modelloRadio);
      if($rc->modelloRadio["omologazione"] == 1) continue;
      $rc->modelloRadio = $rc->modelloRadio["produttore"] . " - " . $rc->modelloRadio["modello"];
      $rc->modelloAntenna = getAntennaById($rc->modelloAntenna);
      $rc->modelloAntenna = $rc->modelloAntenna["produttore"] . " - " . $rc->modelloAntenna["modello"];
      echo(utf8_decode(escapeCsv(getMagliaById($rc->maglia)) . "\t" . escapeCsv($rc->unitaCri) . "\t" . escapeCsv($rc->matricola) . "\t" . escapeCsv($RADIO_TIPO[$rc->tipo]) . "\t" . escapeCsv($rc->localita) . "\t" . escapeCsv(html_entity_decode($posizioneAltitudine["htmlCode"])) . "\t" . escapeCsv($rc->modelloRadio) . "\t" . escapeCsv($rc->modelloAntenna) . "\t" . escapeCsv($rc->siglaRadio) . "\t" . escapeCsv($rc->numeroInventario) . "\t" . escapeCsv($rc->targaAutomezzo) . "\t" . escapeCsv($rc->utilizzatore) . "\t" . escapeCsv($rc->contrattoAssistenza) . "\t" . escapeCsv($rc->riferimentoAssistenza) . "\t" . escapeCsv($rc->note) . "\n"));
    }
    die();
    break;

  case "elencoRipetitoriNonOmologati":
    header("Pragma: ");
    header("Cache-control: ");
    header("Content-type: text/csv");
    header("Content-Disposition: inline; filename=\"elencoRipetitoriNonOmologati.csv\"");
    echo(utf8_decode("Maglia\tTipo\tNumero\tLocalita Collegata\tPosizione e altitudine\tOspitante\tContratto Locazione\tCanone Affitto\tQuota Canone\tFonte Alimentazione 1\tFonte Alimentazione 2\tContatore Enel A Carico Cri\tContratto Assistenza\tRiferimento Assistenza\tNote\n"));
    $rs = $DBL->query("SELECT * FROM ripetitori");
    while($rc = $rs->fetch_object()) {
      $hasSezioniNonOmologate = false;
      $posizioneAltitudine = Methods::getPosizioneFormat($rc->posizione, $rc->altitudine);
      $headRipetitore = utf8_decode(escapeCsv(getMagliaById($rc->maglia)) . "\t" . escapeCsv($RIPETITORE_TIPO[$rc->tipo]) . "\t" . escapeCsv($rc->numero) . "\t" . escapeCsv($rc->localitaCollegata) . "\t" . escapeCsv(html_entity_decode($posizioneAltitudine["htmlCode"])) . "\t" . escapeCsv($rc->ospitante) . "\t" . escapeCsv($rc->contrattoLocazione) . "\t" . escapeCsv($rc->canoneAffitto) . "\t" . escapeCsv($rc->quotaCanone) . "\t" . escapeCsv($RIPETITORE_ALIMENTAZIONE_1[$rc->fonteAlimentazione1]) . "\t" . escapeCsv($RIPETITORE_ALIMENTAZIONE_2[$rc->fonteAlimentazione2]) . "\t" . escapeCsv($rc->contatoreEnelACaricoCri) . "\t" . escapeCsv($rc->contrattoAssistenza) . "\t" . escapeCsv($rc->riferimentoAssistenza) . "\t" . escapeCsv($rc->note) . "\n");
      $rsS = $DBL->query("SELECT * FROM ripetitoriSezioni WHERE idRipetitore=" . $rc->id);
      while($rcS = $rsS->fetch_object()) {
        $ripetitore = getRipetitoreById($rcS->modelloRipetitore);
        if($ripetitore["omologazione"] == 1) continue;
        $hasSezioniNonOmologate = true;
        $ripetitore = $ripetitore["produttore"] . " - " . $ripetitore["modello"];
        $antenna1 = getAntennaById($rc->modelloAntenna1);
        $antenna1 = $antenna1["produttore"] . " - " . $antenna1["modello"];
        $antenna2 = getAntennaById($rc->modelloAntenna2);
        $antenna2 = $antenna2["produttore"] . " - " . $antenna2["modello"];
        $antenna3 = getAntennaById($rc->modelloAntenna3);
        $antenna3 = $antenna3["produttore"] . " - " . $antenna3["modello"];
        $tonoSubAudio = getTonoSubAudioById($rc->tonoSubAudio);
        $modalitaSincronizzazione = getModalitaSincronizzazioneById($rc->modalitaSincronizzazione);
        $headRipetitore .= utf8_decode("\tSezione:\t" . escapeCsv($rc->matricola) . "\t" . escapeCsv($RIPETITORE_SEZIONE_TIPO[$rc->tipo]) . "\t" . escapeCsv($rc->localitaCollegata) . "\t" . escapeCsv($rc->funzione) . "\t" . escapeCsv($ripetitore) . "\t" . escapeCsv($antenna1) . "\t" . escapeCsv($ANTENNE_POLARIZZAZIONI[$rc->polarizzazioneAntenna1]) . "\t" . escapeCsv($antenna2) . "\t" . escapeCsv($ANTENNE_POLARIZZAZIONI[$rc->polarizzazioneAntenna2]) . "\t" . escapeCsv($antenna3) . "\t" . escapeCsv($ANTENNE_POLARIZZAZIONI[$rc->polarizzazioneAntenna3]) . "\t" . escapeCsv($rc->canale) . "\t" . escapeCsv($rc->identita) . "\t" . escapeCsv($tonoSubAudio) . "\t" . escapeCsv($rc->frequenzaRicezione) . "MHz\t" . escapeCsv($rc->frequenzaTrasmissione) . "MHz\t" . escapeCsv($modalitaSincronizzazione) . "\t" . escapeCsv($rc->note) . "\n");
      }
      if($hasSezioniNonOmologate) {
        echo($headRipetitore . "\n");
      }
    }
    die();
    break;

  case "elencoUtenti":
    header("Pragma: ");
    header("Cache-control: ");
    header("Content-type: text/csv");
    header("Content-Disposition: inline; filename=\"elencoUtenti.csv\"");
    echo(utf8_decode("Nome utente\tTipo\tNome\tProvincia\tNome Responsabile\tTelefono Responsabile\tEmail Responsabile\n"));
    $userTypes = $LOGIN->getUserTypes();
    $rs = $DBL->query("SELECT * FROM users WHERE type>1 ORDER BY username");
    while($rc = $rs->fetch_object()) {
      $rc->type = $userTypes[$rc->type];
      echo(utf8_decode(escapeCsv($rc->username) . "\t" . escapeCsv($rc->type) . "\t" . escapeCsv($rc->realName) . "\t" . escapeCsv($rc->provincia) . "\t" . escapeCsv($rc->nomeResponsabile) . "\t" . escapeCsv($rc->telefonoResponsabile) . "\t" . escapeCsv($rc->emailResponsabile) . "\n"));
    }
    die();
    break;

  default:
    $PAGE_CONTENT .= "
      <h3>Strumenti</h3>
      <ul>
        <li><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=elencoRadio5toni'>Elenco radio a 5 toni</a></li>
        <li><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=elencoRadioNonOmologate'>Elenco radio non omologate</a></li>
        <li><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=elencoRipetitoriNonOmologati'>Elenco ripetitori non omologati</a></li>
        <li><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=elencoUtenti'>Elenco utenti</a></li>
      </ul>
    ";
    break;
}
$PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco degli strumenti</a></div>\n";
?>