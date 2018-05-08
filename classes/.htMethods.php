<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180508.017
 */

/*===========================================================================
- Class: Methods
===========================================================================*/
class Methods {
  /*---------------------------------------------------------------------------
  - Method: getPosizioneFormat
  ---------------------------------------------------------------------------*/
  public static function getPosizioneFormat($posizione, $altitudine) {
    if($posizione) {
      $posizione = $GLOBALS["DBL"]->query("SELECT X(0x" . bin2hex($posizione) . ") AS x, Y(0x" . bin2hex($posizione) . ") AS y");
      if($posizione) {
        $decimalPosizione = $posizione->fetch_assoc();
        if($decimalPosizione) {
          $posizione = array();
          $posizione["x"] = Fields::decimalToSexagesimal($decimalPosizione["x"]);
          $posizione["y"] = Fields::decimalToSexagesimal($decimalPosizione["y"]);
        }
      }
    }
    return array(
      "htmlCode" => "Lat: " . $posizione["y"]["d"] . "° " . $posizione["y"]["m"] . "' " . sprintf("%0.1f", $posizione["y"]["s"]) . "&quot;; Long: " . $posizione["x"]["d"] . "° " . $posizione["x"]["m"] . "' " . sprintf("%0.1f", $posizione["x"]["s"]) . "&quot;; Alt: " . $altitudine . "m",
      "posizione" => array("lat"=>$decimalPosizione["y"], "long"=>$decimalPosizione["x"]),
      "extraLink" => "",
    );
  }

  /*---------------------------------------------------------------------------
  - Method: getRipetitoreSezioni
  ---------------------------------------------------------------------------*/
  public static function getRipetitoreSezioni($id) {
    if(is_numeric($id)) $numeroSezioni = $GLOBALS["DBL"]->query("SELECT id FROM ripetitoriSezioni WHERE idRipetitore=" . $id)->num_rows;
    return array(
      "htmlCode" => (int)$numeroSezioni,
      "extraLink" => "<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=RipetitoriSezioni&amp;idRipetitore=" . $id . "' title='Gestione sezioni'><img src='img/icons/ripetitoreSezione.png' alt='Gestione sezioni' class='icon' /></a>",
    );
  }

  /*---------------------------------------------------------------------------
  - Method: getRadioRipetitoreIdWithDocsLink
  ---------------------------------------------------------------------------*/
  public static function getRadioRipetitoreIdWithDocsLink($objType, $id) {
    return array(
      "htmlCode" => $id,
      "extraLink" => "<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Docs&amp;type=" . $objType . "&amp;id=" . $id . "' title='Gestione documenti'><img src='img/icons/folder.png' alt='Gestione documenti' class='icon' /></a>",
    );
  }

  /*---------------------------------------------------------------------------
  - Method: getNumeroUnitaCRIPerMaglia
  ---------------------------------------------------------------------------*/
  public static function getNumeroUnitaCRIPerMaglia($id) {
    return $GLOBALS["DBL"]->query("SELECT maglia FROM users WHERE maglia LIKE '%;" . $GLOBALS["DBL"]->real_escape_string(stripslashes($id)) . ";%'")->num_rows;
  }

  /*---------------------------------------------------------------------------
  - Method: disbaleDeleteCanale
  ---------------------------------------------------------------------------*/
  public static function disbaleDeleteCanale($id) {
    $disableDeleteCanale = false;
    if(is_numeric($id)) {
      $disableDeleteCanale = $GLOBALS["DBL"]->query("SELECT canale FROM ripetitoriSezioni WHERE canale=" . $id)->num_rows + $GLOBALS["DBL"]->query("SELECT canale FROM maglie WHERE canale=" . $id)->num_rows;
    }
    return $disableDeleteCanale;
  }
}
?>