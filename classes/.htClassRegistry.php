<?php
//20110224.013 -- STATIC

/*===========================================================================
- Class: Registry
- This class manage the registry.
===========================================================================*/
class Registry {
  private $data = array();
  private $VERSION = "20110224.013";

  /*---------------------------------------------------------------------------
  - Constructor
  ---------------------------------------------------------------------------*/
  function __construct() {
    $rs = $GLOBALS["DBL"]->query("SELECT * FROM registry");
    while($rc = $rs->fetch_object()) {
      $this->data[$rc->regKey] = $rc->regValue;
    }
  }

  /*---------------------------------------------------------------------------
  - Method: setValue
  ---------------------------------------------------------------------------*/
  public function setValue($key, $value) {
    $key = stripslashes($key);
    $value = stripslashes($value);
    $queryKey = "regKey='" . $GLOBALS["DBL"]->real_escape_string($key) . "'";
    $queryValue = "regValue='" . $GLOBALS["DBL"]->real_escape_string($value) . "'";
    if($GLOBALS["DBL"]->query("INSERT INTO registry SET " . $queryKey . ", " . $queryValue . " ON DUPLICATE KEY UPDATE " . $queryValue)) {
      $this->data[$key] = $value;
      return true;
    }
    return false;
  }

  /*---------------------------------------------------------------------------
  - Method: getValue
  ---------------------------------------------------------------------------*/
  public function getValue($key) {
    return stripslashes($this->data[$key]);
  }

  /*---------------------------------------------------------------------------
  - Method: removeKey
  ---------------------------------------------------------------------------*/
  public function removeKey($key) {
    $key = stripslashes($key);
    if($key) {
      $queryKey = "regKey='" . $GLOBALS["DBL"]->real_escape_string($key) . "'";
      if($GLOBALS["DBL"]->query("DELETE FROM registry WHERE " . $queryKey)) {
        $this->data[$key] = "";
        return true;
      }
    }
    return false;
  }

  /*---------------------------------------------------------------------------
  - Method: getSettingsTable
  ---------------------------------------------------------------------------*/
  public function getSettingsTable($params, $extraUrlParams, $localization=array()) {
    if(!is_array($params)) die("Err. 248658356");
    if(!is_array($localization)) $localization = array();
    $extraUrlParams = (is_array($extraUrlParams)) ? implode("&amp;", $extraUrlParams) : "";
    $htmlCode = "<form method='post' action='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . $_REQUEST["cmd2"] . "&amp;" . $extraUrlParams . "'>
      <table border='0' cellspacing='2' cellpadding='2'>
        <tr>
          <th>" . (($localization["titleCol1"]) ? $localization["titleCol1"] : "Parametro") . "</th>
          <th>" . (($localization["titleCol2"]) ? $localization["titleCol2"] : "Valore corrente") . "</th>
          <th>" . (($localization["titleCol3"]) ? $localization["titleCol3"] : "Nuovo valore") . "</th>
        </tr>\n";
        if($_GET["cmdST"] == "removeParam") {
          $this->removeKey($_GET["stKey"]);
        }
        foreach($params as $paramId=>$paramData) {
          if(strlen($_POST["stKey"][$paramId])) {
            if($paramData["isNumeric"] && !is_numeric($_POST["stKey"][$paramId])) $_POST["stKey"][$paramId] = "0";
            $this->setValue($paramId, $_POST["stKey"][$paramId]);
          }
          $rowClass = ($rowClass == "B") ? "A" : "B";
          $htmlCode .= "<tr class='row" . $rowClass . "'>
            <th width='20%'>" . $paramData["title"] . "</th>
            <td width='50%'>\n";
              switch($paramData["type"]) {
                case "array":
                  $paramValues = BasicTable::arraizeTags($this->getValue($paramId));
                  foreach($paramValues as $paramValue) {
                    $htmlCode .= "&bull; *" . $paramValue . "*<br />\n";
                  }
                  break;
                case "select":
                  $htmlCode .= $paramData["values"][$this->getValue($paramId)];
                  break;
                default:
                  $htmlCode .= $this->getValue($paramId);
                  break;
              }
              $htmlCode .= "
            </td>
            <td width='30%' style='white-space:nowrap;'>\n";
              switch($paramData["type"]) {
                case "textarea":
                  $htmlCode .= "<textarea name='stKey[" . $paramId . "]' cols='40' rows='5'>" . $this->getValue($paramId) . "</textarea>\n";
                  break;
                case "select":
                  $htmlCode .= "<select name='stKey[" . $paramId . "]'>\n";
                    foreach($paramData["values"] as $valueId=>$valueName) {
                      $htmlCode .= "<option value='" . $valueId . "' " . (($this->getValue($paramId) == $valueId) ? "selected='selected'" : "") . ">" . $valueName . "</option>\n";
                    }
                  $htmlCode .= "</select>\n";
                  break;
                default:
                  $htmlCode .= "<input type='text' name='stKey[" . $paramId . "]' value='" . $this->getValue($paramId) . "' size='40' />\n";
                  break;
              }
              $htmlCode .= "<a href=\"javascript:if(confirm('" . (($localization["clearValueQuestion"]) ? $localization["clearValueQuestion"] : "Eliminare il valore?") . "')) location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . $_REQUEST["cmd2"] . "&amp;cmdST=removeParam&amp;stKey=" . $paramId . "&amp;" . $extraUrlParams . "';\" title='" . (($localization["clearValueLabel"]) ? $localization["clearValueLabel"] : "Elimina valore") . "'><img src='/img/icons/delete.png' class='icon' alt='" . (($localization["clearValueLabel"]) ? $localization["clearValueLabel"] : "Elimina valore") . "' /></a>
              " . (($paramData["tip"]) ? ("<div class='tip'>" . $paramData["tip"] . "</div>") : "") . "
            </td>
          </tr>\n";
        }
        $htmlCode .= "<tr>
          <td>&nbsp;</td>
          <td colspan='2'><input type='submit' value='" . (($localization["save"]) ? $localization["save"] : "Salva modifiche") . "' /></td>
        </tr>
      </table>
    </form>\n";
    return $htmlCode;
  }

  /*---------------------------------------------------------------------------
  - Method: getVersion
  ---------------------------------------------------------------------------*/
  public function getVersion() {
    return $this->VERSION;
  }
}
?>