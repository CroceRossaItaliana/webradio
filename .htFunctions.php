<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180706.076
 */

function dbEsc($string) {
  return $GLOBALS["DBL"]->real_escape_string($string);
}

function jsRedirect($url) {
  die("<script type='text/javascript'>location.href='" . $url . "'</script>");
}

function getModelliRadio() {
  if(($_SESSION["CACHE_TIMERS"]["ModelliRadio"] < $GLOBALS["REGISTRY"]->getValue("lastModelliRadioChange")) || !isset($_SESSION["CACHE_MODELLI_RADIO"]) || !is_array($_SESSION["CACHE_MODELLI_RADIO"])) {
    $_SESSION["CACHE_MODELLI_RADIO"] = array();
    $rsModelliRadio = $GLOBALS["DBL"]->query("SELECT id, produttore, modello, omologazione, fuoriUso FROM modelliRadio WHERE status=true ORDER BY produttore, modello");
    while($rcModelliRadio = $rsModelliRadio->fetch_object()) {
      $_SESSION["CACHE_MODELLI_RADIO"][$rcModelliRadio->id] = array(
        "title" => substr($rcModelliRadio->produttore, 1, -1) . " - " . $rcModelliRadio->modello,
        "style" => "background:transparent url(" . $GLOBALS["PATHS"]["modelliRadioJpg"] . $rcModelliRadio->id . ".s.jpg) no-repeat 0 0; height:90px; padding:0 0 0 95px;",
        "produttore" => substr($rcModelliRadio->produttore, 1, -1),
        "modello" => $rcModelliRadio->modello,
        "omologazione" => $rcModelliRadio->omologazione,
        "fuoriUso" => $rcModelliRadio->fuoriUso,
      );
    }
    $_SESSION["CACHE_TIMERS"]["ModelliRadio"] = time();
  }
  return $_SESSION["CACHE_MODELLI_RADIO"];
}

function getRadioById($id) {
  $radio = getModelliRadio();
  return $radio[$id];
}

function getModelliRipetitore() {
  if(($_SESSION["CACHE_TIMERS"]["ModelliRipetitore"] < $GLOBALS["REGISTRY"]->getValue("lastModelliRipetitoreChange")) || !isset($_SESSION["CACHE_MODELLI_RIPETITORE"]) || !is_array($_SESSION["CACHE_MODELLI_RIPETITORE"])) {
    $_SESSION["CACHE_MODELLI_RIPETITORE"] = array();
    $rsModelliRipetitore = $GLOBALS["DBL"]->query("SELECT id, produttore, modello, omologazione FROM modelliRipetitore WHERE status=true ORDER BY produttore, modello");
    while($rcModelliRipetitore = $rsModelliRipetitore->fetch_object()) {
      $_SESSION["CACHE_MODELLI_RIPETITORE"][$rcModelliRipetitore->id] = array(
        "title" => substr($rcModelliRipetitore->produttore, 1, -1) . " - " . $rcModelliRipetitore->modello,
        "style" => "background:transparent url(" . $GLOBALS["PATHS"]["modelliRipetitoriJpg"] . $rcModelliRipetitore->id . ".s.jpg) no-repeat 0 0; height:90px; padding:0 0 0 95px;",
        "produttore" => substr($rcModelliRipetitore->produttore, 1, -1),
        "modello" => $rcModelliRipetitore->modello,
        "omologazione" => $rcModelliRipetitore->omologazione,
      );
    }
    $_SESSION["CACHE_TIMERS"]["ModelliRipetitore"] = time();
  }
  return $_SESSION["CACHE_MODELLI_RIPETITORE"];
}

function getRipetitoreById($id) {
  $ripetitore = getModelliRipetitore();
  return $ripetitore[$id];
}

function getModelliAntenne() {
  if(($_SESSION["CACHE_TIMERS"]["ModelliAntenne"] < $GLOBALS["REGISTRY"]->getValue("lastModelliAntenneChange")) || !isset($_SESSION["CACHE_MODELLI_ANTENNE"]) || !is_array($_SESSION["CACHE_MODELLI_ANTENNE"])) {
    $_SESSION["CACHE_MODELLI_ANTENNE"] = array();
    $rsModelliAntenne = $GLOBALS["DBL"]->query("SELECT id, produttore, modello, guadagno FROM modelliAntenne WHERE status=true ORDER BY produttore, modello");
    while($rcModelliAntenne = $rsModelliAntenne->fetch_object()) {
      $_SESSION["CACHE_MODELLI_ANTENNE"][$rcModelliAntenne->id] = array(
        "title" => substr($rcModelliAntenne->produttore, 1, -1) . " - " . $rcModelliAntenne->modello,
        "style" => "background:transparent url(" . $GLOBALS["PATHS"]["modelliAntenneJpg"] . $rcModelliAntenne->id . ".s.jpg) no-repeat 0 0; height:90px; padding:0 0 0 95px;",
        "produttore" => substr($rcModelliAntenne->produttore, 1, -1),
        "modello" => $rcModelliAntenne->modello,
        "guadagno" => $rcModelliAntenne->guadagno,
      );
    }
    $_SESSION["CACHE_TIMERS"]["ModelliAntenne"] = time();
  }
  return $_SESSION["CACHE_MODELLI_ANTENNE"];
}

function getAntennaById($id) {
  $antenna = getModelliAntenne();
  return $antenna[$id];
}

function getUnitaCri($where="type>1", $onlyUsernames=false) {
  $unitaCri = array();
  $rs = $GLOBALS["DBL"]->query("SELECT username, realName FROM users WHERE " . $where . " ORDER BY realName");
  while($rc = $rs->fetch_object()) {
    $unitaCri[$rc->username] = ($onlyUsernames) ? ("'" . $GLOBALS["DBL"]->real_escape_string(stripslashes($rc->username)) . "'") : $rc->realName;
  }
  return $unitaCri;
}

function getModalitaSincronizzazione() {
  if(($_SESSION["CACHE_TIMERS"]["ModalitaSincronizzazione"] < $GLOBALS["REGISTRY"]->getValue("lastModalitaSincronizzazioneChange")) || !isset($_SESSION["CACHE_MODALITA_SINCRONIZZAZIONE"]) || !is_array($_SESSION["CACHE_MODALITA_SINCRONIZZAZIONE"])) {
    $_SESSION["CACHE_MODALITA_SINCRONIZZAZIONE"] = array();
    $rsModalitaSincronizzazione = $GLOBALS["DBL"]->query("SELECT id, modalitaSincronizzazione FROM modalitaSincronizzazione ORDER BY modalitaSincronizzazione");
    while($rcModalitaSincronizzazione = $rsModalitaSincronizzazione->fetch_object()) {
      $_SESSION["CACHE_MODALITA_SINCRONIZZAZIONE"][$rcModalitaSincronizzazione->id] = $rcModalitaSincronizzazione->modalitaSincronizzazione;
    }
    $_SESSION["CACHE_TIMERS"]["ModalitaSincronizzazione"] = time();
  }
  return $_SESSION["CACHE_MODALITA_SINCRONIZZAZIONE"];
}

function getModalitaSincronizzazioneById($id) {
  $tono = getModalitaSincronizzazione();
  return $tono[$id];
}

function getToniSubAudio() {
  if(!isset($_SESSION["CACHE_TONI_SUB_AUDIO"]) || !is_array($_SESSION["CACHE_TONI_SUB_AUDIO"])) {
    $_SESSION["CACHE_TONI_SUB_AUDIO"] = array();
    $rsToniSubAudio = $GLOBALS["DBL"]->query("SELECT id, tono FROM toniSubAudio ORDER BY tono");
    while($rcToniSubAudio = $rsToniSubAudio->fetch_object()) {
      $_SESSION["CACHE_TONI_SUB_AUDIO"][$rcToniSubAudio->id] = $rcToniSubAudio->tono;
    }
  }
  return $_SESSION["CACHE_TONI_SUB_AUDIO"];
}

function getTonoSubAudioById($id) {
  $tono = getToniSubAudio();
  return $tono[$id];
}

function getElencoMaglie($filter="") {
  if(($_SESSION["CACHE_TIMERS"]["Maglie"] < $GLOBALS["REGISTRY"]->getValue("lastMaglieChange")) || !isset($_SESSION["CACHE_ELENCO_MAGLIE"]) || !is_array($_SESSION["CACHE_ELENCO_MAGLIE"])) {
    $_SESSION["CACHE_ELENCO_MAGLIE"] = array();
    $rsElencoMaglie = $GLOBALS["DBL"]->query("SELECT id, codice, provincia, canale FROM maglie ORDER BY codice, provincia");
    while($rcElencoMaglie = $rsElencoMaglie->fetch_object()) {
      $_SESSION["CACHE_ELENCO_MAGLIE"][$rcElencoMaglie->id] = $rcElencoMaglie->codice . " - " . $rcElencoMaglie->provincia . " (" . $rcElencoMaglie->canale . ")";
    }
    $_SESSION["CACHE_TIMERS"]["Maglie"] = time();
  }
  if(is_numeric($filter)) {
    return $_SESSION["CACHE_ELENCO_MAGLIE"][$filter];
  }
  if($filter) {
    $maglie = array();
    $filter = explode(";", $filter);
    foreach($filter as $flt) {
      if($flt && is_numeric($flt)) {
        $maglie[$flt] = $_SESSION["CACHE_ELENCO_MAGLIE"][$flt];
      }
    }
    return $maglie;
  }
  return $_SESSION["CACHE_ELENCO_MAGLIE"];
}

function getMagliaById($id) {
  return getElencoMaglie($id);
}

function getCanali($idLista="datiCanale", $canale=0) {
  if(($_SESSION["CACHE_TIMERS"]["Canali"] < $GLOBALS["REGISTRY"]->getValue("lastCanaliChange")) || !isset($_SESSION["CACHE_CANALI"]) || !is_array($_SESSION["CACHE_CANALI"])) {
    $_SESSION["CACHE_CANALI"] = array("elencoCanali"=>array());
    $rsCanali = $GLOBALS["DBL"]->query("SELECT id, canale, frequenzaTx, frequenzaRx FROM canali ORDER BY canale");
    while($rcCanali = $rsCanali->fetch_object()) {
      $_SESSION["CACHE_CANALI"]["elencoCanali"][$rcCanali->canale] = $rcCanali->canale;
      $_SESSION["CACHE_CANALI"]["datiCanale"][$rcCanali->canale] = array("canale"=>$rcCanali->canale, "tx"=>$rcCanali->frequenzaTx, "rx"=>$rcCanali->frequenzaRx);
    }
    $_SESSION["CACHE_TIMERS"]["Canali"] = time();
  }
  switch($idLista) {
    case "elencoCanali":
      $canaliData = $_SESSION["CACHE_CANALI"]["elencoCanali"];
      break;
    case "datiCanale":
    default:
      if(is_numeric($canale) && ($canale > 0) && array_key_exists($canale, $_SESSION["CACHE_CANALI"]["datiCanale"])) {
        $canaliData = $_SESSION["CACHE_CANALI"]["datiCanale"][$canale];
      } else {
        $canaliData = $_SESSION["CACHE_CANALI"]["datiCanale"];
      }
      break;
  }
  return $canaliData;
}

function setObjectFilter() {
  $escapedUsername = $GLOBALS["DBL"]->real_escape_string(stripslashes($GLOBALS["LOGIN"]->getUserData("username")));
  switch($GLOBALS["LOGIN"]->getUserData("type")) {
    case "0": //Guest (solo visualizzazione)
    case "1": //Amministratore
    case "2": //Comitato centrale
      $where = "1";
      break;
    case "3": //Comitato regionale
    case "6": //S.I.E.
      $filtroUnitaCri = "username='" . $escapedUsername . "'";
      $comProvs = getUnitaCri("username='" . $escapedUsername . "' OR referenceUser='" . $escapedUsername . "'", true);
      foreach($comProvs as $cp) {
        $filtroUnitaCri .= " OR username=" . $cp . " OR referenceUser=" . $cp;
      }
      $unitaCriValues = getUnitaCri($filtroUnitaCri);
      $where = "unitaCri IN(" . implode(",", getUnitaCri($filtroUnitaCri, true)) . ")";
      break;
    case "4": //Comitato provinciale
    case "7": //C.I.E.
    case "8": //Centro Mob.
    case "9": //N.O.P.I.
    case "10": //Centro di Mobilitazione
      $filtroUnitaCri = "username='" . $escapedUsername . "' OR referenceUser='" . $escapedUsername . "'";
      $unitaCriValues = getUnitaCri($filtroUnitaCri);
      $where = "unitaCri IN(" . implode(",", getUnitaCri($filtroUnitaCri, true)) . ")";
      break;
    case "5": //Unità CRI
    case "11": //Commissione TLC
    default:
      $where = "unitaCri='" . $escapedUsername . "'";
      $extraQuerySet = "unitaCri='" . $escapedUsername . "'";
      break;
  }
  return array($filtroUnitaCri, $unitaCriValues, $where, $extraQuerySet);
}

function getProvince() {
  if(!isset($_SESSION["CACHE_PROVINCE"]) || !is_array($_SESSION["CACHE_PROVINCE"])) {
    $_SESSION["CACHE_PROVINCE"] = array();
    $rsProvince = $GLOBALS["DBL"]->query("SELECT id, nome FROM province ORDER BY nome");
    while($rcProvince = $rsProvince->fetch_object()) {
      $_SESSION["CACHE_PROVINCE"][$rcProvince->id] = $rcProvince->nome;
    }
  }
  return $_SESSION["CACHE_PROVINCE"];
}

function logMessage($message, $username="") {
  $username = ($username) ? $username : $GLOBALS["LOGIN"]->getUserData("username");
  $GLOBALS["DBL"]->query("INSERT INTO accessLog SET accessData=NOW(), username='" . $GLOBALS["DBL"]->real_escape_string($username) . "', ipAddress='" . $_SERVER["REMOTE_ADDR"] . "', message='" . $GLOBALS["DBL"]->real_escape_string(stripslashes($message)) . "'");
}

function escapeCsv($string) {
  return "\"" . str_replace("\"", "\"\"", trim($string)) . "\"";
}

function arraizeCsv($csv, $separator=";") {
  $csv = explode($separator, $csv);
  $array = array();
  foreach($csv as $item) {
    $item = trim($item);
    if(strlen($item) > 0) $array[] = $item;
  }
  return $array;
}

function isActionAllowed($type) {
  $userType = $GLOBALS["LOGIN"]->getUserData("type");
  if($userType == "1") return true; //Amministratore

  switch($type) {
    case "ripetitori":
    case "ripetitoriSezioni":
      if($userType == 2) return true; //Comitato nazionale
      if($userType == 10) return true; //Isp. Corpo Militare Volontario
      if($GLOBALS["LOGIN"]->getUserData("gestioneRipetitori") == 1) return true;
      break;
    case "radio":
      return true;
      break;
  }
  return false;
}
?>