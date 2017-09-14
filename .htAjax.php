<?php
//20110518.027
switch($_GET["cmd2"]) {
  case "checkIfUsernameExists":
    header("Content-type: text/plain; charset=utf-8;");
    die(($LOGIN->isLoginValid($_GET["username"])) ? "true" : "false");
    break;

  case "validateObject":
    if($_GET["type"]) {
      $getFieldsMethod = "get" . $_GET["type"] . "Fields";
      if(method_exists(Objects, $getFieldsMethod)) {
        $fields = Objects::$getFieldsMethod();
        $validateObject = Fields::validateObject($fields, $_POST, $_GET["action"]);
        if($validateObject["valid"]) {
          die("true:");
        } else {
          die("false:" . $validateObject["fieldId"] . ":" . $fields[$validateObject["id"]]["name"]);
        }
      }
    }
    die("false:");
    break;

  case "refreshTagList":
    $tagListHtmlCode = "";
    $_GET["value"] = unserialize(base64_decode($_GET["value"]));
    $type = ($_GET["singleSelection"]) ? "radio" : "checkbox";
    foreach($_SESSION[$_GET["valueSessionVar"]] as $valueId=>$valueName) {
      $name = $_GET["id"] . "['" . (($data["singleSelection"]) ? "single" : md5($valueId)) . "']";
      $tagListHtmlCode .= "<input type='" . $type . "' name=\"" . $name . "\" id=\"" . htmlentities($_GET["id"] . "-" . $valueId, ENT_QUOTES, "utf-8") . "\" value=\"" . htmlentities($valueId, ENT_QUOTES, "utf-8") . "\" " . ((is_array($_GET["value"]) && in_array($valueId, $_GET["value"])) ? "checked='checked'" : "") . " /> <label for=\"" . htmlentities($_GET["id"] . "-" . $valueId, ENT_QUOTES, "utf-8") . "\">" . htmlentities($valueName, ENT_QUOTES, "utf-8") . "</label><br />\n";
    }
    die($tagListHtmlCode);
    break;

  case "showFile":
    BasicTable::forceFileDownload(base64_decode($_GET["file"]), $_GET["crc"], $_GET["mime"]);
    die();
    break;

  case "getRipetitoriByMaglia":
    $ripetitori = array();
    if(is_numeric($_GET["maglia"])) {
      $rsRipetitori = $DBL->query("SELECT id, numero, localitaCollegata FROM ripetitori WHERE maglia=" . $_GET["maglia"] . " ORDER BY numero");
      while($rcRipetitori = $rsRipetitori->fetch_object()) {
        $ripetitori[$rcRipetitori->id] = $rcRipetitori->numero . ": " . $rcRipetitori->localitaCollegata;
      }
    }
    die(json_encode($ripetitori));
    break;

  case "decimalToSexagesimal":
    $_GET["decimal"] = explode(",", str_replace(array("(", ")"), array("", ""), $_GET["decimal"]));
    die(Fields::decimalToSexagesimal(trim($_GET["decimal"][0]), true) . "," . Fields::decimalToSexagesimal(trim($_GET["decimal"][1]), true));
    break;

  case "getMappaInfoWindowsText":
    if(is_numeric($_GET["id"])) {
      switch(substr($_GET["type"], 0, 1)) {
        case "r":
          $rcRipetitore = $DBL->query("SELECT localitaCollegata, X(posizione) as xPos, Y(posizione) as yPos, tipo, maglia FROM ripetitori WHERE id=" . $_GET["id"])->fetch_object();
          $rcRipetitore->maglia = getMagliaById($rcRipetitore->maglia);
          die("<div><b>" . htmlentities($rcRipetitore->localitaCollegata, ENT_QUOTES, "utf-8") . "</b></div><div>&bull; Latitudine: " . htmlentities(Fields::decimalToSexagesimal($rcRipetitore->yPos, true), ENT_QUOTES, "utf-8") . "<br />&bull; Longitudine: " . htmlentities(Fields::decimalToSexagesimal($rcRipetitore->xPos, true), ENT_QUOTES, "utf-8") . "<br />&bull; Maglia: " . $rcRipetitore->maglia . "<br />&bull; Tipo: " . $RIPETITORE_TIPO[$rcRipetitore->tipo] . "</div>");
          break;
        case "f":
          $rsFissa = $DBL->query("SELECT localita, X(posizione) as xPos, Y(posizione) as yPos, maglia, tipo, siglaRadio FROM radio WHERE id=" . $_GET["id"])->fetch_object();
          $rsFissa->maglia = getMagliaById($rsFissa->maglia);
          die("<div><b>" . htmlentities($rsFissa->localita, ENT_QUOTES, "utf-8") . "</b></div><div>&bull; Latitudine: " . htmlentities(Fields::decimalToSexagesimal($rsFissa->yPos, true), ENT_QUOTES, "utf-8") . "<br />&bull; Longitudine: " . htmlentities(Fields::decimalToSexagesimal($rsFissa->xPos, true), ENT_QUOTES, "utf-8") . "<br />&bull; Maglia: " . $rsFissa->maglia . "<br />&bull; Tipo: " . $RADIO_TIPO[$rsFissa->tipo] . "<br />&bull; Selettiva: " . $rsFissa->siglaRadio . "</div>");
          break;
      }
    }
    die("No data.");
    break;

  case "radioCalcolaSelettiva":
    if(!$_GET["unitaCri"]) {
      die("0#Specificare Unità CRI");
    }
    if(!array_key_exists($_GET["tipo"], $RADIO_TIPO)) {
      die("0#Specificare il tipo di radio");
    }
    if($_GET["tipo"] == "1") {
      die("0#Il calcolo automatico è attivo solo per le radio veicolari e portatili");
    }
    $rcUnita = $DBL->query("SELECT username, type FROM users WHERE username='" . $DBL->real_escape_string(stripslashes($_GET["unitaCri"])) . "' LIMIT 1")->fetch_object();
    if($rcUnita->username != $_GET["unitaCri"]) {
      die("0#Unità CRI non trovata");
    }
    $elencoFisse = array();
    $rsFisse = $DBL->query("SELECT siglaRadio, localita, utilizzatore FROM radio WHERE unitaCri='" . $DBL->real_escape_string(stripslashes($rcUnita->username)) . "' AND tipo=1 " . ((is_numeric($_GET["siglaBase"])) ? "AND siglaRadio=" . $_GET["siglaBase"] : "") . " GROUP BY siglaRadio ORDER BY siglaRadio");
    while($rcFisse = $rsFisse->fetch_object()) {
      $elencoFisse[] = array("sigla"=>$rcFisse->siglaRadio, "utilizzatore"=>$rcFisse->localita . (($rcFisse->utilizzatore) ? ", " . $rcFisse->utilizzatore : ""));
    }
    switch(count($elencoFisse)) {
      case 0:
        die("0#Calcolo automatico della selettiva non riuscito");
        break;
      case 1:
        if(strlen($elencoFisse[0]["sigla"]) < 6) {
          die("0#Il calcolo automatico della selettiva è attivo solo per le Unità con selettiva a 6 cifre");
        }
        switch($_GET["tipo"]) {
          case "2": //Radio veicolare
            $ultimaSelettiva = $elencoFisse[0]["sigla"]; $ultimaSelettiva[2] = 6; $ultimaSelettiva[5] = 9;
            for($i=($elencoFisse[0]["sigla"]+1001); $i<=$ultimaSelettiva; $i++) {
              if(($i % 10) == 0) $i += 991;
              $nuovaSiglaRadio = str_pad($i, 6, "0", STR_PAD_LEFT);
              if($DBL->query("SELECT id FROM radio WHERE siglaRadio='" . $DBL->real_escape_string(stripslashes($nuovaSiglaRadio)) . "'")->num_rows == 0) {
                die("1#" . $nuovaSiglaRadio);
                break;
              }
            }
            break;

          case "3": //Radio portatile
            $i = substr($elencoFisse[0]["sigla"], 0, 2) . "7000";
            $ultimaSelettiva = substr($elencoFisse[0]["sigla"], 0, 2) . "8999";
            for($i; $i<=$ultimaSelettiva; $i++) {
              if(($i % 1000) == 0) $i++;
              $nuovaSiglaRadio = str_pad($i, 6, "0", STR_PAD_LEFT);
              if($DBL->query("SELECT id FROM radio WHERE siglaRadio='" . $DBL->real_escape_string(stripslashes($nuovaSiglaRadio)) . "'")->num_rows == 0) {
                die("1#" . $nuovaSiglaRadio);
                break;
              }
            }
            break;
        }
        die("0#Calcolo automatico della selettiva non riuscito");
        break;
      default:
        $selezionePostazioneHtml = "";
        foreach($elencoFisse as $fs) {
          $selezionePostazioneHtml .= "&bull; <a href=\"javascript:ADMIN.radioCalcolaSelettiva('" . $fs["sigla"] . "');\">" . $fs["utilizzatore"] . "</a> (Fissa: " . $fs["sigla"] . ")<br />";
        }
        die("2#" . $selezionePostazioneHtml);
    }
    die("0#Calcolo automatico della selettiva non riuscito");
    break;
}

die("Err: command unknown.");
?>