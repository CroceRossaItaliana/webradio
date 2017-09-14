<?php
//20110410.163 -- STATIC

/*===========================================================================
- Class: BasicTable
===========================================================================*/
class BasicTable {
  private static $RPP = 30;
  public static $actions = array(
    "show" => "visualizza",
    "add" => "aggiungi",
    "edit" => "modifica",
  );
  public static $basicStatus = array(
    "0" => "No",
    "1" => "Si",
  );

  /*---------------------------------------------------------------------------
  - Method: getOldData
  ---------------------------------------------------------------------------*/
  public static function getOldData($config) {
    $old = null;
    if($config["idValue"]) {
      $queryFilter = array($config["idField"] . "='" . $GLOBALS["DBL"]->real_escape_string(stripslashes($config["idValue"])) . "'");
      if($config["where"]) $queryFilter[] = $config["where"];
      $old = $GLOBALS["DBL"]->query("SELECT * FROM " . $config["table"] . " WHERE " . implode(" AND ", $queryFilter) . " LIMIT 1")->fetch_assoc();
      if($old[$config["idField"]] != $config["idValue"]) unset($old);
    }
    return $old;
  }

  /*---------------------------------------------------------------------------
  - Method: getForm
  ---------------------------------------------------------------------------*/
  public static function getForm($config) {
    if(!array_key_exists($config["action"], BasicTable::$actions)) $config["action"] = "show";
    if(($config["action"] != "show") && ($GLOBALS["LOGIN"]->isGuest())) die("Err.: hueggruevgure");
    $linkParams = BasicTable::buildLinkParamsString($config["linkParams"], array_keys($config["fields"]));
    $htmlCode = "<h3>" . (($config["title"]) ? $config["title"] : (BasicTable::getLocale(ucwords(BasicTable::$actions[$_REQUEST["cmd2"]]))) . " " . BasicTable::getLocale("elemento")) . "</h3>\n";
    if($config["action"] != "show") {
      $htmlCode .= "<form method='post' action='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . (($config["customCmd2Action"]) ? $config["customCmd2Action"] : "save") . "" . $linkParams . "' " . (($config["jsCheckForm"]) ? ("onsubmit=\"return " . $config["jsCheckForm"] . ";\"") : "") . " enctype='multipart/form-data'>
        <div><input type='hidden' name='actionCodeXvfewergwgtrwgrgrewfv' value='" . $config["action"] . "' /></div>\n";
    }
      $htmlCode .= "<table border='0' cellspacing='5' width='95%'>\n";
        foreach($config["fields"] as $fieldId=>$fieldData) {
          if(!is_array($fieldData)) continue;
          if(($config["action"] == "show") && ($fieldData["hideOnShow"])) continue;
          if((in_array($config["action"], array("edit", "add"))) && ($fieldData["manualQuery"])) continue;
          if(!$config["data"][$fieldId] && is_array($config["linkParams"]) && in_array($fieldId, $config["linkParams"])) $config["data"][$fieldId] = $_GET[$fieldId];
          if($fieldData["type"] == "hidden") {
            if($config["action"] != "show") {
              $htmlCode .= "<tr style='display:none;'>
                <td colspan='2'>" . Fields::renderField($fieldId, $fieldData, $config["data"][$fieldId], $config["action"]) . "</td>
              </tr>\n";
            }
          } else {
            $htmlCode .= "<tr>
              <th style='width:20%;'>
                " . htmlentities($fieldData["name"], ENT_QUOTES, "utf-8") . ":
                " . (($fieldData["tip"]) ? "<br /><span class='tip'>" . $fieldData["tip"] . "</span>" : "") . "
              </th>
              <td style='width:80%;'>" . Fields::renderField($fieldId, $fieldData, $config["data"][$fieldId], $config["action"]) . "</td>
            </tr>\n";
          }
        }
        if($config["action"] != "show") {
          $htmlCode .= "<tr>
            <td style='width:20%;'>&nbsp;</td>
            <td style='width:80%;'><input type='submit' value='" . BasicTable::getLocale("Salva") . "' style='font-size:20px; font-weight:bold;' id='addEditSaveButton' /></td>
          </tr>\n";
        }
      $htmlCode .= "</table>\n";
    if($config["action"] != "show") $htmlCode .= "</form>\n";
    return $htmlCode;
  }

  /*---------------------------------------------------------------------------
  - Method: saveRecord
  ---------------------------------------------------------------------------*/
  public static function saveRecord($config, $debug=false) {
    if($GLOBALS["LOGIN"]->isGuest()) die("Err.: hueggruevgure");
    $htmlCode = "<h3>" . (($config["title"]) ? $config["title"] : (BasicTable::getLocale("Salva") . " " . BasicTable::getLocale("elemento"))) . "</h3>\n";
    $validateObject = Fields::validateObject($config["fields"], $config["data"], $config["data"]["actionCodeXvfewergwgtrwgrgrewfv"]);
    if($validateObject["valid"]) {
      $querySet = Fields::buildQuerySet($config["fields"], $config["data"], $config["data"]["actionCodeXvfewergwgtrwgrgrewfv"]);
      if($config["extraQuerySet"]) $querySet[] = $config["extraQuerySet"];
      if($config["data"]["actionCodeXvfewergwgtrwgrgrewfv"] == "edit") {
        $queryFilter = array($config["idField"] . "='" . $config["data"][$config["idField"]] . "'");
        if($config["where"]) $queryFilter[] = $config["where"];
        $query = "UPDATE " . $config["table"] . " SET " . implode(", ", $querySet) . " WHERE " . implode(" AND ", $queryFilter) . " LIMIT 1";
        if($debug) die($query);
        $GLOBALS["DBL"]->query($query);
        $validateObject["action"] = "edit";
      } else {
        $query = "INSERT INTO " . $config["table"] . " SET " . implode(", ", $querySet);
        if($debug) die($query);
        $GLOBALS["DBL"]->query($query);
        $validateObject["action"] = "add";
      }
      $validateObject["recordId"] = $GLOBALS["DBL"]->insert_id;
      if($validateObject["recordId"] === 0) $validateObject["recordId"] = $config["data"][$config["idField"]];
      $validateObject["valid"] = ($GLOBALS["DBL"]->errno === 0);
    }
    if($validateObject["valid"]) {
      if(is_array($_FILES)) {
        foreach($_FILES as $fileId=>$fileData) {
          switch($config["fields"][$fileId]["type"]) {
            case "fileImage":
              $filename = $_SERVER["DOCUMENT_ROOT"] . $config["fields"][$fileId]["path"] . $validateObject["recordId"];
              if($config["data"][$fileId . "Del"]) {
                @unlink($filename . ".s.jpg");
                @unlink($filename . ".b.jpg");
              }
              if($fileData["name"]) {
                BasicTable::resizeAndSaveImage($fileData["tmp_name"], $filename, $config["fields"][$fileId]["widthBig"], $config["fields"][$fileId]["widthSmall"]);
              }
              break;
            case "fileGeneric":
              $filename = $_SERVER["DOCUMENT_ROOT"] . $config["fields"][$fileId]["path"] . $validateObject["recordId"] . "." . $config["fields"][$fileId]["extension"];
              if($config["data"][$fileId . "Del"]) @unlink($filename);
              if($fileData["name"]) {
                @copy($fileData["tmp_name"], $filename);
                //@chmod($filename, 0660);
              }
              break;
          }
        }
      }
      $htmlCode .= BasicTable::showMessageBox(BasicTable::getLocale("Elemento salvato"));
    } else {
      $htmlCode .= BasicTable::showMessageBox(BasicTable::getLocale("Errore durante la scrittura dei dati") . (($validateObject["id"]) ? ("\n" . BasicTable::getLocale("Errore nel campo") . ": " . $config["fields"][$validateObject["id"]]["name"]) : ""), true);
    }
    return array_merge($validateObject, array("htmlCode"=>$htmlCode));
  }

  /*---------------------------------------------------------------------------
  - Method: deleteRecord
  ---------------------------------------------------------------------------*/
  public static function deleteRecord($config) {
    if($GLOBALS["LOGIN"]->isGuest()) die("Err.: hueggruevgure");
    $htmlCode = "<h3>" . (($config["title"]) ? $config["title"] : (BasicTable::getLocale("Elimina") . " " . BasicTable::getLocale("elemento"))) . "</h3>\n";
    $queryFilter = array($config["idField"] . "='" . $GLOBALS["DBL"]->real_escape_string(stripslashes($config["idValue"])) . "'");
    if($config["where"]) $queryFilter[] = $config["where"];
    $result = $config["idValue"] && $GLOBALS["DBL"]->query("DELETE FROM " . $config["table"] . " WHERE " . implode(" AND ", $queryFilter) . " LIMIT 1");
    if($result && is_array($config["fields"])) {
      foreach($config["fields"] as $fieldId=>$fieldData) {
        switch($fieldData["type"]) {
          case "fileImage":
            @unlink($_SERVER["DOCUMENT_ROOT"] . $fieldData["path"] . $config["idValue"] . ".s.jpg");
            @unlink($_SERVER["DOCUMENT_ROOT"] . $fieldData["path"] . $config["idValue"] . ".b.jpg");
            break;
          case "fileGeneric":
            @unlink($_SERVER["DOCUMENT_ROOT"] . $fieldData["path"] . $config["idValue"] . "." . $fieldData["extension"]);
            break;
        }
      }
    }
    $htmlCode .= BasicTable::showMessageBox((($result) ? BasicTable::getLocale("Elemento eliminato") : BasicTable::getLocale("Errore durante la cancellazione dell'elemento")));
    return array("result"=>$result, "htmlCode"=>$htmlCode);
  }

  /*---------------------------------------------------------------------------
  - Method: deleteGallery
  ---------------------------------------------------------------------------*/
  public static function deleteGallery($path) {
    if($GLOBALS["LOGIN"]->isGuest()) die("Err.: hueggruevgure");
    $absPath = $_SERVER["DOCUMENT_ROOT"] . $path;
    if(is_dir($absPath)) {
      $itemGalleryImages = BasicTable::getItemGalleryImages($path);
      foreach($itemGalleryImages["images"] as $image) {
        @unlink($absPath . $image . ".s.jpg");
        @unlink($absPath . $image . ".b.jpg");
      }
      @rmdir($absPath);
    }
  }

  /*---------------------------------------------------------------------------
  - Method: getTable
  ---------------------------------------------------------------------------*/
  public static function getTable($config) {
    $htmlCode = "<h3>" . (($config["title"]) ? htmlentities($config["title"], ENT_QUOTES, "utf-8") : BasicTable::getLocale("Elenco elementi")) . "</h3>\n";
    $linkParams = BasicTable::buildLinkParamsString($config["linkParams"]);

    switch($_GET["filter"]) {
      case "changed":
        if(is_array($config["filters"])) {
          foreach($config["filters"] as $filterId=>$filterData) {
            if(isset($_REQUEST[$filterId])) {
              $_SESSION["filter"][$_REQUEST["cmd"]][$filterId] = trim($_REQUEST[$filterId]);
            }
          }
        }
        $_SESSION["filter"][$_REQUEST["cmd"]]["limit"] = 1;
        break;
      case "clear":
        $_SESSION["filter"][$_REQUEST["cmd"]] = array();
        break;
      case "order":
        if(!is_array($config["fixedOrder"]) && array_key_exists($_GET["col"], $config["columns"])) {
          $_SESSION["filter"][$_REQUEST["cmd"]]["order"] = array("col"=>$_GET["col"], "dir"=>(($_GET["dir"] == "DESC") ? "DESC" : "ASC"));
        }
        break;
      case "limit":
        if(is_numeric($_GET["limit"])) $_SESSION["filter"][$_REQUEST["cmd"]]["limit"] = $_GET["limit"];
    }
    if(!is_numeric($_SESSION["filter"][$_REQUEST["cmd"]]["limit"])) $_SESSION["filter"][$_REQUEST["cmd"]]["limit"] = 1;
    if(is_array($config["fixedOrder"])) {
      $_SESSION["filter"][$_REQUEST["cmd"]]["order"] = array("col"=>$config["fixedOrder"]["col"], "dir"=>$config["fixedOrder"]["dir"]);
    } else {
      if(!is_array($_SESSION["filter"][$_REQUEST["cmd"]]["order"])) {
        if(is_array($config["defaultOrder"])) {
          $_SESSION["filter"][$_REQUEST["cmd"]]["order"] = array("col"=>$config["defaultOrder"]["col"], "dir"=>$config["defaultOrder"]["dir"]);
        } else {
          $_SESSION["filter"][$_REQUEST["cmd"]]["order"] = array("col"=>$config["idField"], "dir"=>"ASC");
        }
      }
    }

    $queryFilter = array("1");
    if($config["where"]) $queryFilter[] = $config["where"];

    if(is_array($config["filters"]) && (count($_SESSION["filter"][$_REQUEST["cmd"]]) > 0)) {
      foreach($config["filters"] as $filterId=>$filterData) {
        if(strlen($_SESSION["filter"][$_REQUEST["cmd"]][$filterId])) {
          $queryFilter[] = str_replace("%%value%%", $GLOBALS["DBL"]->real_escape_string(stripslashes($_SESSION["filter"][$_REQUEST["cmd"]][$filterId])), $filterData["query"]);
        }
      }
    }

    $filterCode = "";
    if(is_array($config["filters"]) || ($config["disablePagination"] !== true)) {
      $filterCode .= "<form class='filter' action='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . $_REQUEST["cmd2"] . "&amp;filter=changed" . $linkParams . "' method='post'>";
        if($config["disablePagination"] !== true) {
          $rsNum = $GLOBALS["DBL"]->query("SELECT COUNT(" . $config["idField"] . ") AS numRows FROM " . $config["table"] . " WHERE " . implode(" AND ", $queryFilter))->fetch_object();
          $rsNum = $rsNum->numRows;
          $pageNum = ceil($rsNum / BasicTable::$RPP);
          if($pageNum > 0) {
            $filterCode .= "<div style='float:right;'><select onchange=\"location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . $_REQUEST["cmd2"] . "&amp;filter=limit&amp;limit=' + this.value + '" . $linkParams . "';\">\n";
            for($i=$pageNum; $i>0; $i--) {
              $filterCode .= "<option value='" . $i . "' " . (($_SESSION["filter"][$_REQUEST["cmd"]]["limit"] == $i) ? "selected='selected'" : "") . ">" . BasicTable::getLocale("Pag.") . " " . $i . " (" . ((($i - 1) * BasicTable::$RPP) + 1) . "-" . ((($i * BasicTable::$RPP) < $rsNum) ? ($i * BasicTable::$RPP) : $rsNum) . " " . BasicTable::getLocale("di") . " " . $rsNum . ")</option>\n";
            }
            $filterCode .= "</select></div>\n";
          }
        }
        if(is_array($config["filters"])) {
          $filterCode .= "<b>" . BasicTable::getLocale("Filtro") . "</b>:\n";
          foreach($config["filters"] as $filterId=>$filterData) {
            if(!is_array($filterData) || $filterData["hidden"]) continue;
            $filterCode .= $filterData["name"] . ": ";
            switch($filterData["type"]) {
              case "select":
                $filterCode .= "<select name='" . $filterId . "' style='width:" . (($filterData["width"]) ? $filterData["width"] : "70") . "px;'><option value=''></option>\n";
                  foreach($filterData["values"] as $valueId=>$valueName) {
                    $filterCode .= "<option value='" . $valueId . "' " . ((strlen($_SESSION["filter"][$_REQUEST["cmd"]][$filterId]) && ($valueId == $_SESSION["filter"][$_REQUEST["cmd"]][$filterId])) ? "selected='selected'" : "") . ">" . ((is_array($valueName)) ? $valueName["title"] : $valueName) . "</option>\n";
                  }
                $filterCode .= "</select>";
                break;
              case "keyword":
              default:
                $filterCode .= "<input type='text' name='" . $filterId . "' value=\"" . htmlentities($_SESSION["filter"][$_REQUEST["cmd"]][$filterId], ENT_QUOTES, "utf-8", false) . "\" style='width:" . (($filterData["width"]) ? $filterData["width"] : "70") . "px;' />";
                break;
            }
            $filterCode .= " | ";
          }
          $filterCode .= "<input type='submit' value='" . BasicTable::getLocale("Filtra") . "' />
          <input type='button' value='" . BasicTable::getLocale("Togli filtri") . "' onclick='javascript:window.location=\"" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . $_REQUEST["cmd2"] . "&amp;filter=clear" . $linkParams . "\"' />\n";
        }
        $filterCode .= "
      </form>\n";
    }

    $htmlCode .= $filterCode;

    if(!$GLOBALS["LOGIN"]->isGuest() && !$config["disableCreate"]) $htmlCode .= "<div style='margin:10px 0 -12px 3px; font-weight:bold;'><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=add" . $linkParams . "'>" . BasicTable::getLocale("Crea nuovo elemento") . "</a></div>\n";

    $htmlCode .= "
      <table border='0' cellspacing='2' cellpadding='2' width='100%'>
        <tr>\n";
          $config["columnsForQuery"] = (is_array($config["extraColumns"])) ? $config["extraColumns"] : array();
          foreach($config["columns"] as $colId=>$colData) {
            if(!is_array($colData)) continue;
            if($colData["type"] != "function") {
              $config["columnsForQuery"][] = (($colData["queryTrans"]) ? ($colData["queryTrans"] . " AS ") : "") . $colId;
            } elseif(is_array($colData["fields"])) {
              foreach($colData["fields"] as $field) {
                $config["columnsForQuery"][] = $field;
              }
            }
            if($colData["hideColumn"] !== true) {
              $htmlCode .= "<th>
                " . $colData["name"] . " ";
                if($colData["type"] == "tagList") {
                  $htmlCode .= " <a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;reloadTagList=1' title='Aggiorna elenco'>&#10226;</a>";
                }
                if(!is_array($config["fixedOrder"]) && ($colData["disableSort"] !== true)) {
                  if(($colData["type"] != "function") || (($colData["type"] == "function") && is_array($colData["fields"]) && in_array($colId, $colData["fields"]))) {
                    foreach(array("ASC", "DESC") as $dir) {
                      $active = (($_SESSION["filter"][$_REQUEST["cmd"]]["order"]["col"] == $colId) && ($_SESSION["filter"][$_REQUEST["cmd"]]["order"]["dir"] == $dir)) ? "active" : "";
                      $htmlCode .= "<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;filter=order&amp;col=" . $colId . "&amp;dir=" . $dir . $linkParams . "' title='" . $dir . "' class='" . $active . "' style='font-size:14px;'>" . (($dir == "ASC") ? "&#9650;" : "&#9660;") . "</a>";
                    }
                  }
                }
              $htmlCode .= "</th>\n";
            }
          }
          $htmlCode .= "<th>&nbsp;</th>
        </tr>\n";
        $rs = $GLOBALS["DBL"]->query("SELECT " . implode(", ", $config["columnsForQuery"]) . " FROM " . $config["table"] . " WHERE " . implode(" AND ", $queryFilter) . ((!$config["disableSort"]) ? " ORDER BY " . $_SESSION["filter"][$_REQUEST["cmd"]]["order"]["col"] . " " . $_SESSION["filter"][$_REQUEST["cmd"]]["order"]["dir"] : "") . ((!$config["disablePagination"]) ? (" LIMIT " . (($_SESSION["filter"][$_REQUEST["cmd"]]["limit"] - 1) * BasicTable::$RPP) . ", " . BasicTable::$RPP) : ""));
        if($rs === false) {
          if(!$config["disableSelfClear"] && !$_GET["selfclear"]) {
            die("<script type='text/javascript'>location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&filter=clear&selfclear=1" . str_replace("&amp;", "&", $linkParams) . "';</script>");
          }
          if($GLOBALS["ENABLE_SHOW_QUERY_SELFCLEAR"]) {
            echo("<div>SELECT " . implode(", ", $config["columnsForQuery"]) . " FROM " . $config["table"] . " WHERE " . implode(" AND ", $queryFilter) . " ORDER BY " . $_SESSION["filter"][$_REQUEST["cmd"]]["order"]["col"] . " " . $_SESSION["filter"][$_REQUEST["cmd"]]["order"]["dir"] . ((!$config["disablePagination"]) ? (" LIMIT " . (($_SESSION["filter"][$_REQUEST["cmd"]]["limit"] - 1) * BasicTable::$RPP) . ", " . BasicTable::$RPP) : "") . "</div>\n");
          }
          die("<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;filter=clear" . $linkParams . "'>" . BasicTable::getLocale("Togli filtri") . "</a></div>\n");
        }
        while($rc = $rs->fetch_object()) {
          $extraLinks = array();
          if(is_array($config["extraLinks"])) {
            foreach($config["extraLinks"] as $elId=>$elData) {
              foreach($rc as $fieldName=>$fieldValue) {
                $tokenField = "%%" . $fieldName . "%%";
                if(strpos($elData["url"], $tokenField) !== false) {
                  $elData["url"] = str_replace($tokenField, $fieldValue, $elData["url"]);
                }
                if($elData["test"] && strpos($elData["test"], $tokenField) !== false) {
                  $elData["test"] = str_replace($tokenField, $fieldValue, $elData["test"]);
                }
              }
              $showLink = true;
              if(isset($elData["test"])) {
                eval("\$showLink = " . $elData["test"] . ";");
              }
              if($showLink) {
                $elData["url"] .= $linkParams;
                if($elData["jsconfirm"]) {
                  $elData["url"] = "javascript:if(confirm('" . $elData["jsconfirm"] . "')) location.href='" . $elData["url"] . "';";
                }
                $extraLinks[] = "<a href=\"" . $elData["url"] . "\">" . $elData["name"] . "</a>\n";
              }
            }
          }
          $rowClass = ($rowClass == "B") ? "A" : "B";
          $htmlCode .= "<tr class='row" . $rowClass . "'>\n";
            foreach($config["columns"] as $colId=>$colData) {
              if(!is_array($colData) || (($colData["hideColumn"] === true) && ($colData["type"] != "function"))) continue;
              if($colData["hideColumn"] !== true) {
                $htmlCode .= "<td width='" . $colData["width"] . "'>";
              }
              switch($colData["type"]) {
                case "field":
                  $htmlCode .= htmlentities($rc->$colId, ENT_QUOTES, "utf-8");
                  break;
                case "function":
                  $params = array();
                  if(is_array($colData["params"])) {
                    foreach($colData["params"] as $param) {
                      eval("\$param = " . $param . ";");
                      $params[] = "\"" . str_replace("\"", "\\\"", $param) . "\"";
                    }
                  }
                  $methodOutput = array();
                  eval("\$methodOutput = Methods::" . $colData["function"] . "(" . implode(", ", $params) . ");");
                  if($colData["hideColumn"] !== true) {
                    $htmlCode .= $methodOutput["htmlCode"];
                  }
                  $extraLinks[] =  $methodOutput["extraLink"];
                  break;

                case "arrayIndex":
                case "tagList":
                  $arrayIndexStringArray = array();
                  $arrayIndexValues = explode(";", $rc->$colId);
                  foreach($arrayIndexValues as $aivId) {
                    if(strlen($aivId) && array_key_exists($aivId, $colData["values"])) {
                      $valueToShow = $colData["values"][$aivId];
                      if(is_array($colData["values"][$aivId]) && array_key_exists("title", $colData["values"][$aivId])) $valueToShow = $colData["values"][$aivId]["title"];
                      $arrayIndexStringArray[] = $valueToShow;
                    }
                  }
                  $htmlCode .= htmlentities(implode(", ", $arrayIndexStringArray), ENT_QUOTES, "utf-8");
                  break;
                case "static":
                  $htmlCode .= htmlentities($colData["value"], ENT_QUOTES, "utf-8");
                  break;
              }
              if($colData["hideColumn"] !== true) {
                $htmlCode .= "</td>\n";
              }
            }
            $htmlCode .= "<td style='white-space:nowrap;' class='rowLinks'>\n";
              $disableLinkFromEval = array();
              foreach(array("disableShow", "disableEdit", "disableDelete", "hasGallery") as $disableLink) {
                if(is_string($config[$disableLink])) {
                  $disableLinkFromEval[$disableLink] = false;
                  eval("\$disableLinkFromEval[\"" . $disableLink . "\"] = " . str_replace("%object%", "\$rc", $config[$disableLink]) . ";");
                }
              }
              if(($config["disableShow"] !== true) && !$disableLinkFromEval["disableShow"]) $htmlCode .= "<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=show&amp;id=" . $rc->$config["idField"] . $linkParams . "' title='" . BasicTable::getLocale("Visualizza") . "'><img src='img/icons/show.png' alt='" . BasicTable::getLocale("Visualizza") . "' class='icon' /></a>\n";
              if(!$GLOBALS["LOGIN"]->isGuest() && ($config["disableEdit"] !== true) && !$disableLinkFromEval["disableEdit"]) $htmlCode .= "<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=edit&amp;id=" . $rc->$config["idField"] . $linkParams . "' title='" . BasicTable::getLocale("Modifica") . "'><img src='img/icons/edit.png' alt='" . BasicTable::getLocale("Modifica") . "' class='icon' /></a>\n";
              if(!$GLOBALS["LOGIN"]->isGuest() && ($config["hasGallery"] === true) || $disableLinkFromEval["hasGallery"]) $htmlCode .= "<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=manageItemGallery&amp;id=" . $rc->$config["idField"] . "' title='" . BasicTable::getLocale("Galleria immagini") . "'><img src='img/icons/gallery.png' alt='" . BasicTable::getLocale("Galleria immagini") . "' class='icon' /></a>\n";
              if(!$GLOBALS["LOGIN"]->isGuest() && ($config["disableDelete"] !== true) && !$disableLinkFromEval["disableDelete"]) $htmlCode .= "<a href=\"javascript:if(confirm('" . BasicTable::getLocale("Elimina record") . " #" . htmlentities($rc->$config["idField"], ENT_QUOTES, "utf-8") . "?')) location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=del&amp;id=" . $rc->$config["idField"] . $linkParams . "';\" title='" . BasicTable::getLocale("Cancella") . "'><img src='img/icons/delete.png' alt='" . BasicTable::getLocale("Cancella") . "' class='icon' /></a>\n";
              $htmlCode .= " " . implode(" ", $extraLinks) . "
            </td>
          </tr>\n";
        }
        $htmlCode .= "
      </table>
      " . $filterCode . "
    ";
    return $htmlCode;
  }

  /*---------------------------------------------------------------------------
  - Method: getIdField
  ---------------------------------------------------------------------------*/
  public static function getIdField($fields) {
    foreach($fields as $fieldId=>$fieldData) {
      if($fieldData["idField"] === true) return $fieldId;
    }
    return "";
  }

  /*---------------------------------------------------------------------------
  - Method: showMessageBox
  ---------------------------------------------------------------------------*/
  public static function showMessageBox($message, $isError=false, $doNotHtmlentitize=false) {
    if(!$doNotHtmlentitize) $message = htmlentities($message, ENT_QUOTES, "utf-8", false);
    return "<div class='messageBox messageBox" . (($isError) ? "Ko" : "Ok") . "'>" . nl2br($message) . "</div>\n";
  }

  /*---------------------------------------------------------------------------
  - Method: buildLinkParamsString
  ---------------------------------------------------------------------------*/
  public static function buildLinkParamsString($linkParams, $excludeParams=array()) {
    $linkParamsString = "";
    if(is_array($linkParams)) {
      foreach($linkParams as $lp) {
        if(isset($_REQUEST[$lp]) && !in_array($lp, $excludeParams)) $linkParamsString .= "&amp;" . $lp . "=" . rawurlencode($_REQUEST[$lp]);
      }
    }
    return $linkParamsString;
  }

  /*---------------------------------------------------------------------------
  - Method: dieUrl
  ---------------------------------------------------------------------------*/
  public static function dieUrl($url) {
    die("<script type='text/javascript'>location.href='" . $url . "';</script>");
  }

  /*---------------------------------------------------------------------------
  - Method: resizeAndSaveImage
  ---------------------------------------------------------------------------*/
  public static function resizeAndSaveImage($imageSrc, $imageDst, $widthBig, $widthSmall) {
    $imageBase = imagecreatefromjpeg($imageSrc);
    list($imageWidth, $imageHeight) = getimagesize($imageSrc);
    if(is_numeric($imageWidth) && is_numeric($imageHeight)) {
      $widthDst = array("s"=>$widthSmall, "b"=>$widthBig);
      foreach(array("s", "b") as $size) {
        if($widthDst[$size] === 0) continue;
        if(is_numeric($widthDst[$size]) && ($imageWidth > $widthDst[$size])) {
          $imageWidthResized = $widthDst[$size];
          $imageHeightResized = $widthDst[$size] * $imageHeight / $imageWidth;
          $imageResized = imagecreatetruecolor($imageWidthResized, $imageHeightResized);
          imagecopyresampled($imageResized, $imageBase, 0, 0, 0, 0, $imageWidthResized, $imageHeightResized, $imageWidth, $imageHeight);
          imagejpeg($imageResized, ($imageDst . "." . $size . ".jpg"), 85);
        } else {
          @copy($imageSrc, $imageDst . "." . $size . ".jpg");
        }
        //@chmod(($imageDst . "." . $size . ".jpg"), 0660);
      }
    }
  }

  /*---------------------------------------------------------------------------
  - Method: forceFileDownload
  ---------------------------------------------------------------------------*/
  public static function forceFileDownload($file, $crc, $mimeType) {
    if((sha1(basename($file) . $GLOBALS["SECRET"]) == $crc) && file_exists($_SERVER["DOCUMENT_ROOT"] . $file)) {
      header("Pragma: ");
      header("Cache-control: ");
      header("Content-type: " . $mimeType);
      header("Content-Disposition: inline; filename=\"" . basename($file) . "\"");
      readfile($_SERVER["DOCUMENT_ROOT"] . $file);
      die();
    } else {
      header("HTTP/1.0 404 Not Found");
      die("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested file was not found on this server.</p>\n</body></html>");
    }
  }

  /*---------------------------------------------------------------------------
  - Method: buildShowFileLink
  ---------------------------------------------------------------------------*/
  public static function buildShowFileUrl($filename, $extension="jpg", $mimeType="image/jpeg", $scriptName="") {
    if(!$scriptName) $scriptName = $_SERVER["SCRIPT_NAME"];
    return $scriptName . "?cmd=Ajax&amp;cmd2=showFile&amp;file=" . base64_encode($filename . "." . $extension) . "&amp;mime=" . $mimeType . "&amp;crc=" . sha1(basename($filename . "." . $extension) . $GLOBALS["SECRET"]);
  }

  /*---------------------------------------------------------------------------
  - Method: manageItemGallery
  ---------------------------------------------------------------------------*/
  public static function manageItemGallery($path, $id, $widthBig, $widthSmall, $pathIndex="", $publicShowFileBasename="") {
    if($GLOBALS["LOGIN"]->isGuest()) die("Err.: hueggruevgure");
    if(mt_rand(1, 50) == 1) {
      $rsGalleryCaptions = $GLOBALS["DBL"]->query("SELECT id FROM galleriesData");
      while($rcGalleryCaptions = $rsGalleryCaptions->fetch_object()) {
        if(!file_exists($_SERVER["DOCUMENT_ROOT"] . $rcGalleryCaptions->id . ".s.jpg")) {
          $GLOBALS["DBL"]->query("DELETE FROM galleriesData WHERE id='" . $rcGalleryCaptions->id . "' LIMIT 1");
        }
      }
    }
    $htmlCode = "<div>&nbsp;</div>\n";
    $path .= $id . "/";
    $pathWithDocumentRoot = $_SERVER["DOCUMENT_ROOT"] . $path;
    $fileDidaTrans = array(".jpg"=>"", ".jpeg"=>"", "_"=>" ");
    if(!is_dir($pathWithDocumentRoot)) mkdir($pathWithDocumentRoot, 0755, true);
    if(is_dir($pathWithDocumentRoot)) {
      if($_FILES["immagine"]["name"]) {
        $legalFilenameChars = "abcdefghijklmnopqrstuvwxyz0123456789_-.";
        $originalFilenameLen = strlen($_FILES["immagine"]["name"]) - 4;
        $newImageName = $pathWithDocumentRoot;
        for($i=0; $i<$originalFilenameLen; $i++) {
          $char = $_FILES["immagine"]["name"][$i];
          $newImageName .= (stripos($legalFilenameChars, $char) !== false) ? $char : "_";
        }
        $newImageName .= "-" . date("YmdHis");
        BasicTable::resizeAndSaveImage($_FILES["immagine"]["tmp_name"], $newImageName, $widthBig, $widthSmall);
        $fileNamePlain = $GLOBALS["DBL"]->real_escape_string(stripslashes(strtr(basename($_FILES["immagine"]["name"]), $fileDidaTrans)));
        $GLOBALS["DBL"]->query("INSERT INTO galleriesData (id, caption) VALUE ('" . $path . basename($newImageName) . "', '" . $fileNamePlain . "') ON DUPLICATE KEY UPDATE caption='" . $fileNamePlain . "'");
      }
      if($_FILES["immagineZIP"]["name"]) {
        if($zip = zip_open($_FILES["immagineZIP"]["tmp_name"])) {
          while($zipEntry = zip_read($zip)) {
            $fileName = basename(zip_entry_name($zipEntry));
            if(stripos($fileName, ".jpg")) {
              if(zip_entry_open($zip, $zipEntry, "r")) {
                $tmpjpgname = tempnam(ini_get("upload_tmp_dir"), "zipjpg");
                $tmpjpg = fopen($tmpjpgname, "w");
                fwrite($tmpjpg, zip_entry_read($zipEntry, zip_entry_filesize($zipEntry)));
                fclose($tmpjpg);
                $newImageName = $path . base64_encode($fileName);
                BasicTable::resizeAndSaveImage($tmpjpgname, $newImageName, $widthBig, $widthSmall);
                $fileNamePlain = $GLOBALS["DBL"]->real_escape_string(stripslashes(strtr($fileName, $fileDidaTrans)));
                $GLOBALS["DBL"]->query("INSERT INTO galleriesData (id, caption) VALUE ('" . $path . basename($newImageName) . "', '" . $fileNamePlain . "') ON DUPLICATE KEY UPDATE caption='" . $fileNamePlain . "'");
                unlink($tmpjpgname);
              }
            }
            zip_entry_close($zipEntry);
          }
          zip_close($zip);
        }
      }
      if($_GET["delImage"]) {
        @unlink($pathWithDocumentRoot . $_GET["delImage"] . ".b.jpg");
        @unlink($pathWithDocumentRoot . $_GET["delImage"] . ".s.jpg");
      }
      $itemGalleryImages = BasicTable::getItemGalleryImages($path);
      if(count($itemGalleryImages["unknown"])) {
        $htmlCode .= "<div style='padding:15px; background-color:#FFB3B3; border:1px solid #F00;'>
          La galleria contiene errori.<br />
          Contattare l'assistenza tecnica specificando il codice <b>" . $_REQUEST["cmd"] . "/" . $rc->id . "</b>.
        </div>\n";
      }
      $htmlCode .= "<div style='background-color:#F0F0F0; border:1px solid #AAA; margin-bottom:15px;'>
        <div style='float:left;'>\n";
          if(count($itemGalleryImages["images"])) {
            foreach($itemGalleryImages["images"] as $imageId) {
              $imagePath = $path . $imageId;
              if($pathIndex) {
                $imageUrlBig = $GLOBALS["PATHS"][$pathIndex] . $id . "/" . $imageId . ".b.jpg";
                $imageUrlSmall = $GLOBALS["PATHS"][$pathIndex] . $id . "/" . $imageId . ".s.jpg";
              } else {
                $imageUrlBig = BasicTable::buildShowFileUrl($imagePath . ".b", "jpg", "image/jpeg", $publicShowFileBasename);
                $imageUrlSmall = BasicTable::buildShowFileUrl($imagePath . ".s", "jpg", "image/jpeg", $publicShowFileBasename);
              }
              $htmlCode .= "<div style='float:left; text-align:center; border:1px solid #AAA; background-color:#DDD; padding:10px; margin:6px; height:300px;'>
                <a href='" . $imageUrlBig . "' target='_blank' style='text-decoration:none;'><img src='" . $imageUrlSmall . "' width='120' border='0' alt='ID: " . $imageId . "' style='margin-bottom:5px; border:2px solid #000;' /></a><br />
                <abbr title='Immagine piccola'>S</abbr>: <input type='text' value='" . $imageUrlSmall . "' readonly='readonly' style='width:100px;' /><br />
                <abbr title='Immagine grande'>B</abbr>: <input type='text' value='" . $imageUrlBig . "' readonly='readonly' style='width:100px;' /><br />
                <textarea id='dida-" . $path . basename($imagePath) . "' cols='20' rows='2' style='width:120px;'>";
                  $rcImageDida = $GLOBALS["DBL"]->query("SELECT caption FROM galleriesData WHERE id='" . $path . basename($imagePath) . "' LIMIT 1")->fetch_object();
                $htmlCode .= htmlentities($rcImageDida->caption, ENT_QUOTES, "utf-8", false) . "</textarea><br />
                <a href=\"javascript:ADMIN.saveGalleryCaption('" . $path . basename($imagePath) . "');\">Salva nuova dida</a><br />
                <a href=\"javascript:if(confirm('Eliminare immagine?')) location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . $_REQUEST["cmd2"] . "&amp;id=" . $id . "&amp;delImage=" . $imageId . "';\">Cancella immagine</a>
              </div>\n";
            }
          } else {
            $htmlCode .= "L'oggetto non contiene immagini";
          }
        $htmlCode .= "</div>
        <div style='clear:left;'></div>
      </div>
      <div style='border:1px solid #C9D1D5; padding:3px; margin-bottom:15px;'>
        <form method='post' action='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=" . $_REQUEST["cmd2"] . "&amp;id=" . $id . "' enctype='multipart/form-data'>
          <div>
            <b>Carica nuova immagine singola</b><br />
            <span style='font-size:smaller; font-style:italic; font-weight:normal;'>Immagine in formato <b>JPG</b></span>
          </div>
          <div style='border-bottom:1px solid #C9D1D5; padding-bottom:8px;'>
            <input type='file' name='immagine' />
            <input type='submit' value='Carica immagine' />
          </div>
          <div>
            <b>Carica immagini da file ZIP</b><br />
            <span style='font-size:smaller; font-style:italic; font-weight:normal;'>Immagini contenute in un file <b>ZIP</b></span>
          </div>
          <div>
            <input type='file' name='immagineZIP' />
            <input type='submit' value='Carica immagini da file ZIP' />
          </div>
        </form>
      </div>\n";
    }
    return $htmlCode;
  }

  /*---------------------------------------------------------------------------
  - Method: getItemGalleryImages
  ---------------------------------------------------------------------------*/
  public static function getItemGalleryImages($path) {
    $path = $_SERVER["DOCUMENT_ROOT"] . $path;
    $itemGalleryImages = array("images"=>array(), "unknown"=>array());
    if(is_dir($path)) {
      if($dh = opendir($path)) {
        while(($file = readdir($dh)) !== false) {
          if(is_file($path . $file)) {
            switch(substr($file, -6)) {
              case ".s.jpg": $itemGalleryImages["images"][] = substr($file, 0, -6); break;
              case ".b.jpg": break;
              default: $itemGalleryImages["unknown"][] = $file; break;
            }
          } else {
            if(($file != ".") && ($file != "..")) {
              $itemGalleryImages["unknown"][] = $file;
            }
          }
        }
        closedir($dh);
      }
    }
    return $itemGalleryImages;
  }

  /*-------------------------------------------------------------------------
   - Method: reloadTagList
   ---------------------------------------------------------------------------*/
  public static function reloadTagList($tagVariables) {
    foreach($tagVariables as $tagId=>$tagData) {
      if(!is_array($_SESSION[$tagId]) || $_GET["reloadTagList"]) {
        $_SESSION[$tagId] = array();
        $rs = $GLOBALS["DBL"]->query("SELECT " . $tagData["field"] . " AS tagField FROM " . $tagData["table"]);
        while($rc = $rs->fetch_object()) {
          $rc->tagField = explode(";", $rc->tagField);
          foreach($rc->tagField as $tag) {
            $tag = trim($tag);
            if($tag) $_SESSION[$tagId][$tag] = $tag;
          }
        }
        asort($_SESSION[$tagId]);
      }
    }
    return true;
  }

  /*-------------------------------------------------------------------------
   - Method: arraizeTags
   ---------------------------------------------------------------------------*/
  public static function arraizeTags($csvTags) {
    $arrayTags = array();
    $csvTags = explode(";", $csvTags);
    foreach($csvTags as $tag) {
      $tag = trim($tag);
      if($tag) $arrayTags[] = $tag;
    }
    return $arrayTags;
  }

  /*-------------------------------------------------------------------------
   - Method: getLocale
   ---------------------------------------------------------------------------*/
  public static function getLocale($string) {
    if(is_array($GLOBALS["WIZ_LOCALE"]) && array_key_exists($string, $GLOBALS["WIZ_LOCALE"])) {
      return $GLOBALS["WIZ_LOCALE"][$string];
    }
    return $string;
  }

  /*-------------------------------------------------------------------------
   - Method: showVersion
   ---------------------------------------------------------------------------*/
  public static function showVersion() {
    return $GLOBALS["PHP_VERSION"] . ".<script type='text/javascript'>document.write(ADMIN.JS_VERSION);</script>";
  }
}


/*===========================================================================
- Class: Fields
===========================================================================*/
class Fields {

  /*---------------------------------------------------------------------------
  - Method: renderField
  ---------------------------------------------------------------------------*/
  public static function renderField($id, $data, $value="", $action="show") {
    if(!array_key_exists($action, BasicTable::$actions)) $action = "show";
    $htmlCode = "";

    if($data["readonlyOnEdit"] && ($action == "edit")) $readonlyOnEdit = "readonly='readonly'";
    if($data["maxlen"]) $maxlen = "maxlength='" . $data["maxlen"] . "'";

    switch($data["type"]) {
      case "textarea":
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<textarea name='" . $id . "' id='" . $id . "' cols='" . $data["cols"] . "' rows='" . $data["rows"] . "' " . $readonlyOnEdit . ">" . htmlentities($value, ENT_QUOTES, "utf-8") . "</textarea>";
            break;
          case "show":
          default:
            if(!$data["doNotHtmlentitize"]) $value = nl2br(htmlentities($value, ENT_QUOTES, "utf-8"));
            $htmlCode .= $value;
            break;
        }
        break;

      case "textareaAdv":
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "
              <textarea name='" . $id . "' id='" . $id . "' cols='" . $data["cols"] . "' rows='" . $data["rows"] . "' " . $readonlyOnEdit . ">" . htmlentities($value, ENT_QUOTES, "utf-8") . "</textarea>
              <script type='text/javascript'>CKEDITOR.replace('" . $id . "', { height:" . (($data["height"]) ? $data["height"] : 300) . " });</script>
            ";
            break;
          case "show":
          default:
            $htmlCode .= $value;
            break;
        }
        break;

      case "checkbox":
        $value = explode(";", $value);
        switch($action) {
          case "add":
          case "edit":
            foreach($data["values"] as $valueId=>$valueName) {
              $htmlCode .= "<input type='checkbox' name=\"" . $id . "['" . md5($valueId) . "']\" id=\"" . htmlentities($id . "-" . $valueId, ENT_QUOTES, "utf-8") . "\" value=\"" . htmlentities($valueId, ENT_QUOTES, "utf-8") . "\" " . ((is_array($value) && in_array($valueId, $value)) ? "checked='checked'" : "") . " " . $readonlyOnEdit . " style='vertical-align:text-bottom;' /> <label for=\"" . htmlentities($id . "-" . $valueId, ENT_QUOTES, "utf-8") . "\">" . htmlentities($valueName, ENT_QUOTES, "utf-8") . "</label><br />\n";
            }
            break;
          case "show":
          default:
            $valuesStringArray = array();
            foreach($data["values"] as $valueId=>$valueName) {
              if(is_array($value) && in_array($valueId, $value)) $valuesStringArray[] = htmlentities($valueName, ENT_QUOTES, "utf-8");
            }
            $htmlCode .= implode(", ", $valuesStringArray);
            break;
        }
        break;

      case "tagList":
        $value = explode(";", $value);
        switch($action) {
          case "add":
          case "edit":
            $type = ($data["singleSelection"]) ? "radio" : "checkbox";
            $htmlCode .= "<div id='tagList-" . $id . "'>\n";
              foreach($_SESSION[$data["valuesSessionVar"]] as $valueId=>$valueName) {
                $name = $id . "['" . (($data["singleSelection"]) ? "single" : md5($valueId)) . "']";
                $htmlCode .= "<input type='" . $type . "' name=\"" . $name . "\" id=\"" . htmlentities($id . "-" . $valueId, ENT_QUOTES, "utf-8") . "\" value=\"" . htmlentities($valueId, ENT_QUOTES, "utf-8") . "\" " . ((is_array($value) && in_array($valueId, $value)) ? "checked='checked'" : "") . " " . $readonlyOnEdit . " /> <label for=\"" . htmlentities($id . "-" . $valueId, ENT_QUOTES, "utf-8") . "\">" . htmlentities($valueName, ENT_QUOTES, "utf-8") . "</label><br />\n";
              }
            $htmlCode .= "</div>
              <span style='font-size:smaller; font-style:italic;'>" . BasicTable::getLocale("Se l'elenco non è aggiornato") . " <a onclick=\"ADMIN.refreshTagList('" . $id . "', '" . $data["valuesSessionVar"] . "', '" . base64_encode(serialize($value)) . "', " . ((int)$data["singleSelection"]) . ");\" style='cursor:pointer;'>" . BasicTable::getLocale("clicca qui") . "</a></span><br />
              " . BasicTable::getLocale("Altri valori") . ": <input type='text' name='" . $id . "[tagListNew]' id='" . $id . "-tagListNew' size='20' /> <span style='font-size:smaller; font-style:italic;'>(" . BasicTable::getLocale("Valori separati da punto e virgola") . ")</span>
            ";
            break;
          case "show":
          default:
            $valuesStringArray = array();
            foreach($_SESSION[$data["valuesSessionVar"]] as $valueId=>$valueName) {
              if(is_array($value) && in_array($valueId, $value)) $valuesStringArray[] = htmlentities($valueName, ENT_QUOTES, "utf-8");
            }
            $htmlCode .= implode(", ", $valuesStringArray);
            break;
        }
        break;

      case "select":
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<select name='" . $id . "' id='" . $id . "' " . ((($action == "edit") && $data["readonlyOnEdit"]) ? "onfocus='this.blur();'" : "") . "><option value=''></option>\n";
            foreach($data["values"] as $valueId=>$valueName) {
              if(is_array($valueName)) {
                if(array_key_exists("title", $valueName)) {
                  $htmlCode .= "<option value='" . $valueId . "' " . ((($value !== null) && ($valueId == $value)) ? "selected='selected'" : "") . " style='" . $valueName["style"] . "'>" . htmlentities($valueName["title"], ENT_QUOTES, "utf-8") . "</option>\n";
                } else {
                  $htmlCode .= "<optgroup label=\"" . htmlentities($valueId, ENT_QUOTES, "utf-8") . "\">\n";
                  foreach($valueName as $valueNameId=>$valueNameName) {
                    $htmlCode .= "<option value='" . $valueNameId . "' " . ((($value !== null) && ($valueNameId == $value)) ? "selected='selected'" : "") . ">" . htmlentities($valueNameName, ENT_QUOTES, "utf-8") . "</option>\n";
                  }
                  $htmlCode .= "</optgroup>\n";
                }
              } else {
                $htmlCode .= "<option value='" . $valueId . "' " . ((($value !== null) && ($valueId == $value)) ? "selected='selected'" : "") . ">" . htmlentities($valueName, ENT_QUOTES, "utf-8") . "</option>\n";
              }
            }
            $htmlCode .= "</select>\n";
            break;
          case "show":
          default:
            foreach($data["values"] as $valueId=>$valueName) {
              if(($value !== null) && ($valueId == $value)) {
                if(is_array($valueName) && array_key_exists("title", $valueName)) $valueName = $valueName["title"];
                $htmlCode .= htmlentities($valueName, ENT_QUOTES, "utf-8");
              }
            }
            break;
        }
        break;

      case "datetime":
      case "date":
        if($data["nowIfEmpty"] && !$value) {
          $value = date("Y-m-d H:i:s");
        }
        $value = explode(" ", $value);
        $value[0] = explode("-", $value[0]);
        $value[1] = explode(":", $value[1]);
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "
              <input type='text' name='" . $id . "[D]' id='" . $id . "D' value=\"" . (($value[0][2] > 0) ? $value[0][2] : "") . "\" size='2' maxlength='2' " . $readonlyOnEdit . " /> /
              <input type='text' name='" . $id . "[M]' id='" . $id . "M' value=\"" . (($value[0][1] > 0) ? $value[0][1] : "") . "\" size='2' maxlength='2' " . $readonlyOnEdit . " /> /
              <input type='text' name='" . $id . "[Y]' id='" . $id . "Y' value=\"" . (($value[0][0] > 0) ? $value[0][0] : "") . "\" size='4' maxlength='4' " . $readonlyOnEdit . " />
            ";
            if($data["type"] == "datetime") {
              $htmlCode .= ", &nbsp;
                <input type='text' name='" . $id . "[H]' id='" . $id . "H' value=\"" . (($value[0][2] > 0) ? $value[1][0] : "") . "\" size='2' maxlength='2' " . $readonlyOnEdit . " /> :
                <input type='text' name='" . $id . "[I]' id='" . $id . "I' value=\"" . (($value[0][2] > 0) ? $value[1][1] : "") . "\" size='2' maxlength='2' " . $readonlyOnEdit . " /> :
                <input type='text' name='" . $id . "[S]' id='" . $id . "S' value=\"" . (($value[0][2] > 0) ? $value[1][2] : "") . "\" size='2' maxlength='2' " . $readonlyOnEdit . " />
              ";
            }
            break;
          case "show":
          default:
            $htmlCode .= ($data["type"] == "datetime") ? sprintf("%02d/%02d/%04d, %02d:%02d:%02d", $value[0][2], $value[0][1], $value[0][0], $value[1][0], $value[1][1], $value[1][2]) : sprintf("%02d/%02d/%04d", $value[0][2], $value[0][1], $value[0][0]);
            break;
        }
        break;

      case "password":
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<input type='password' name='" . $id . "' id='" . $id . "' size='" . $data["size"] . "' " . $maxlen . " />";
            break;
          case "show":
          default:
            $htmlCode .= "********";
            break;
        }
        break;

      case "hidden":
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<input type='hidden' name='" . $id . "' id='" . $id . "' value=\"" . htmlentities($value, ENT_QUOTES, "utf-8") . "\" />";
            break;
          case "show":
          default:
            break;
        }
        break;

      case "text":
      case "numeric":
      default:
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<input type='text' name='" . $id . "' id='" . $id . "' value=\"" . htmlentities($value, ENT_QUOTES, "utf-8") . "\" size='" . $data["size"] . "' " . $maxlen . " " . $readonlyOnEdit . " />";
            break;
          case "show":
          default:
            if(!$data["doNotHtmlentitize"]) $value = htmlentities($value, ENT_QUOTES, "utf-8");
            $htmlCode .= $value;
            break;
        }
        break;

      case "email":
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<input type='text' name='" . $id . "' id='" . $id . "' value=\"" . htmlentities($value, ENT_QUOTES, "utf-8") . "\" size='" . $data["size"] . "' " . $maxlen . " " . $readonlyOnEdit . " />";
            break;
          case "show":
          default:
            $htmlCode .= "<a href='mailto:" . htmlentities($value, ENT_QUOTES, "utf-8") . "'>" . htmlentities($value, ENT_QUOTES, "utf-8") . "</a>";
            break;
        }
        break;

      case "fileImage":
        $fileBasename = $data["path"] . $_GET["id"];
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<input type='file' name='" . $id . "' />";
            if(file_exists($_SERVER["DOCUMENT_ROOT"] . $fileBasename . ".s.jpg")) $htmlCode .= "<br /><input type='checkbox' name='" . $id . "Del' id='" . $id . "Del' value='1' /> <label for='" . $id . "Del'>Cancella immagine</label>";
            break;
          case "show":
          default:
            $htmlCode .= "
              Immagine piccola: " . ((file_exists($_SERVER["DOCUMENT_ROOT"] . $fileBasename . ".s.jpg")) ? "<a href='" . BasicTable::buildShowFileUrl($fileBasename . ".s") . "' target='_blank'>visualizza</a>" : "<i>non presente</i>") . "<br />
              Immagine grande: " . ((file_exists($_SERVER["DOCUMENT_ROOT"] . $fileBasename . ".b.jpg")) ? "<a href='" . BasicTable::buildShowFileUrl($fileBasename . ".b") . "' target='_blank'>visualizza</a>" : "<i>non presente</i>") . "<br />
            ";
            break;
        }
        break;

      case "fileGeneric":
        $fileBasename = $data["path"] . $_GET["id"];
        switch($action) {
          case "add":
          case "edit":
            $htmlCode .= "<input type='file' name='" . $id . "' />";
            if(file_exists($_SERVER["DOCUMENT_ROOT"] . $fileBasename . "." . $data["extension"])) $htmlCode .= "<br /><input type='checkbox' name='" . $id . "Del' id='" . $id . "Del' value='1' /> <label for='" . $id . "Del'>Cancella file</label>";
            break;
          case "show":
          default:
            $htmlCode .= "File: " . ((file_exists($_SERVER["DOCUMENT_ROOT"] . $fileBasename . "." . $data["extension"])) ? "<a href='" . BasicTable::buildShowFileUrl($fileBasename, $data["extension"], $data["mimeType"]) . "' target='_blank'>visualizza</a>" : "<i>non presente</i>") . "\n";
            break;
        }
        break;

      case "point":
        if($value) $value = $GLOBALS["DBL"]->query("SELECT X(0x" . bin2hex($value) . ") AS x, Y(0x" . bin2hex($value) . ") AS y")->fetch_assoc();
        if($data["useDMS"]) {
          $value["x"] = Fields::decimalToSexagesimal($value["x"]);
          $value["y"] = Fields::decimalToSexagesimal($value["y"]);
        }
        switch($action) {
          case "add":
          case "edit":
            if($data["useDMS"]) {
              $htmlCode .= "
                " . $data["yLabel"] . ": <input type='text' name='" . $id . "[y][d]' id='" . $id . "-y-d' value=\"" . $value["y"]["d"] . "\" size='4' maxlength='4' " . $readonlyOnEdit . " />° <input type='text' name='" . $id . "[y][m]' id='" . $id . "-y-m' value=\"" . $value["y"]["m"] . "\" size='2' maxlength='2' " . $readonlyOnEdit . " />' <input type='text' name='" . $id . "[y][s]' id='" . $id . "-y-s' value=\"" . $value["y"]["s"] . "\" size='7' maxlength='7' " . $readonlyOnEdit . " />&quot;<br />
                " . $data["xLabel"] . ": <input type='text' name='" . $id . "[x][d]' id='" . $id . "-x-d' value=\"" . $value["x"]["d"] . "\" size='4' maxlength='4' " . $readonlyOnEdit . " />° <input type='text' name='" . $id . "[x][m]' id='" . $id . "-x-m' value=\"" . $value["x"]["m"] . "\" size='2' maxlength='2' " . $readonlyOnEdit . " />' <input type='text' name='" . $id . "[x][s]' id='" . $id . "-x-s' value=\"" . $value["x"]["s"] . "\" size='7' maxlength='7' " . $readonlyOnEdit . " />&quot;
              ";
            } else {
              $htmlCode .= "
                " . $data["yLabel"] . ": <input type='text' name='" . $id . "[y]' id='" . $id . "-y' value=\"" . $value["y"] . "\" size='10' maxlength='10' " . $readonlyOnEdit . " /><br />
                " . $data["xLabel"] . ": <input type='text' name='" . $id . "[x]' id='" . $id . "-x' value=\"" . $value["x"] . "\" size='10' maxlength='10' " . $readonlyOnEdit . " />
              ";
            }
            break;
          case "show":
            if($data["useDMS"]) {
              $value["x"] = $value["x"]["d"] . "° " . $value["x"]["m"] . "' " . $value["x"]["s"] . "&quot;";
              $value["y"] = $value["y"]["d"] . "° " . $value["y"]["m"] . "' " . $value["y"]["s"] . "&quot;";
            }
            $htmlCode .= "
              " . $data["yLabel"] . ": " . $value["y"] . "<br />
              " . $data["xLabel"] . ": " . $value["x"] . "
            ";
          default:
            break;
        }
        break;
    }

    if(($action != "show") && ($data["mandatory"] || ($data["mandatoryOnCreate"] && ($action == "add")))) $htmlCode .= " (*)";
    return $htmlCode;
  }

  /*---------------------------------------------------------------------------
  - Method: buildQueryField
  ---------------------------------------------------------------------------*/
  public static function buildQueryField($id, $data, $value, $action) {
    if($data == null) return false;
    $query = "";
    switch($data["type"]) {
      case "checkbox":
      case "tagList":
        $checkboxValues = array();
        if(is_array($value)) {
          foreach($value as $val) {
            $val = trim($val);
            if($val) $checkboxValues[] = $val;
          }
        }
        $value = (count($checkboxValues)) ? (";" . implode(";", $checkboxValues) . ";") : "";
        $query = $id . "='" . $GLOBALS["DBL"]->real_escape_string(stripslashes($value)) . "'";
        break;

      case "date":
        $query = $id . "='" . $GLOBALS["DBL"]->real_escape_string($value["Y"] . "-" . $value["M"] . "-" . $value["D"]) . "'";
        break;

      case "datetime":
        $query = $id . "='" . $GLOBALS["DBL"]->real_escape_string($value["Y"] . "-" . $value["M"] . "-" . $value["D"] . " " . $value["H"] . "-" . $value["I"] . "-" . $value["S"]) . "'";
        break;

      case "password":
        if($value != "") $query = $id . "='" . sha1(stripslashes($value)) . "'";
        break;

      case "fileImage":
      case "fileGeneric":
        break;

      case "point":
        if($data["useDMS"]) {
          $value["x"] = Fields::sexagesimalToDecimal($value["x"]["d"], $value["x"]["m"], $value["x"]["s"]);
          $value["y"] = Fields::sexagesimalToDecimal($value["y"]["d"], $value["y"]["m"], $value["y"]["s"]);
        }
        $query = $id . "=GEOMFROMTEXT('POINT(" . (double)$GLOBALS["DBL"]->real_escape_string($value["x"]) . " " . (double)$GLOBALS["DBL"]->real_escape_string($value["y"]) . ")')";
        break;

      case "text":
      case "textarea":
      case "textareaAdv":
      case "numeric":
      case "select":
      case "hidden":
      case "email":
      default:
        $query = $id . "='" . $GLOBALS["DBL"]->real_escape_string(stripslashes($value)) . "'";
        break;
    }
    return $query;
  }

  /*---------------------------------------------------------------------------
  - Method: buildQuerySet
  ---------------------------------------------------------------------------*/
  public static function buildQuerySet($fields, $values, $action) {
    $querySet = array();
    foreach($fields as $id=>$data) {
      if($data["manualQuery"] || (($action == "edit") && ($data["idField"] === true))) continue;
      if($data["type"] == "tagList") {
        if($data["singleSelection"] && $values[$id]["tagListNew"]) {
          $values[$id] = array($values[$id]["tagListNew"]);
        } else {
          $values[$id]["tagListNew"] = explode(";", $values[$id]["tagListNew"]);
          foreach($values[$id]["tagListNew"] as $newTag) {
            $newTag = trim($newTag);
            if($newTag) $values[$id][] = $newTag;
          }
          $values[$id]["tagListNew"] = "";
        }
      }
      $queryField = Fields::buildQueryField($id, $data, $values[$id], $action);
      if($queryField) $querySet[] = $queryField;
    }
    return $querySet;
  }

  /*---------------------------------------------------------------------------
  - Method: validateField
  ---------------------------------------------------------------------------*/
  public static function validateField($id, $data, $values, $action="add") {
    $value = $values[$id];
    if(!array_key_exists($action, BasicTable::$actions)) $action = "add";
    switch($data["type"]) {
      case "checkbox":
      case "tagList":
        if(
          ($data["mandatory"] && (count($value) < 1))
        ) return array("valid"=>false, "id"=>$id, "fieldName"=>($id . "[]"), "fieldId"=>($id . "-0"));
        break;

      case "datetime":
        $idxs = array("D", "M", "Y", "H", "I", "S");
      case "date":
        if(!$idxs) {
          $idxs = array("D", "M", "Y");
          $value["H"] = $value["I"] = $value["S"] = 0;
        }
        foreach($idxs as $idx) {
          if(
            ($data["mandatory"] && ($value[$idx] == "")) ||
            ($data["mandatoryOnCreate"] && ($action == "add") && ($value[$idx] == "")) ||
            ($value[$idx] && !is_numeric($value[$idx]))
          ) return array("valid"=>false, "id"=>$id, "fieldName"=>($id . "[" . $idx . "]"), "fieldId"=>($id . $idx));
        }
        if(!Fields::checkDatetime($value["M"], $value["D"], $value["Y"], $value["H"], $value["I"], $value["S"])) return array("valid"=>false, "id"=>$id, "fieldName"=>($id . "[D]"), "fieldId"=>($id . "D"));
        break;

      case "numeric":
        $lowerGreaterThenFields = false;
        if(is_array($data["lowerthanfields"])) {
          foreach($data["lowerthanfields"] as $ltField) {
            if($value >= $values[$ltField]) $lowerGreaterThenFields = true;
          }
        }
        if(is_array($data["greaterthanfields"])) {
          foreach($data["greaterthanfields"] as $gtField) {
            if($value <= $values[$gtField]) $lowerGreaterThenFields = true;
          }
        }
        if(
          ($data["mandatory"] && ($value == "")) ||
          ($data["mandatoryOnCreate"] && ($action == "add") && ($value == "")) ||
          (($value != "") && !is_numeric($value)) ||
          ($data["mandatory"] && isset($data["minvalue"]) && ($value < $data["minvalue"])) ||
          (isset($data["minvalue"]) && isset($value) && ($value < $data["minvalue"])) ||
          ($data["mandatory"] && isset($data["maxvalue"]) && ($value > $data["maxvalue"])) ||
          (isset($data["maxvalue"]) && isset($value) && ($value > $data["maxvalue"])) ||
          $lowerGreaterThenFields
        ) return array("valid"=>false, "id"=>$id, "fieldName"=>$id, "fieldId"=>$id);
        break;

      case "point":
        if($data["useDMS"]) {
          $hasEmptyValues = (($value["x"]["d"] == "") || ($value["x"]["m"] == "") || ($value["x"]["s"] == "") || ($value["y"]["d"] == "") || ($value["y"]["m"] == "") || ($value["y"]["s"] == ""));
          if(
            ($data["mandatory"] && $hasEmptyValues) ||
            ($data["mandatoryOnCreate"] && ($action == "add") && $hasEmptyValues) ||
            (!is_numeric($value["x"]["d"]) || !is_numeric($value["x"]["m"]) || !is_numeric($value["x"]["s"]) || !is_numeric($value["y"]["d"]) || !is_numeric($value["y"]["m"]) || !is_numeric($value["y"]["s"]))
          ) return array("valid"=>false, "id"=>$id, "fieldName"=>$id, "fieldId"=>($id . "-x-d"));
        } else {
          if(
            ($data["mandatory"] && (($value["x"] == "") || ($value["y"] == ""))) ||
            ($data["mandatoryOnCreate"] && ($action == "add") && (($value["x"] == "") || ($value["y"] == ""))) ||
            (($value["x"] != "") && (($value["y"] == "") || !is_numeric($value["x"]))) ||
            (($value["y"] != "") && (($value["x"] == "") || !is_numeric($value["y"])))
          ) return array("valid"=>false, "id"=>$id, "fieldName"=>$id, "fieldId"=>($id . "-x"));
        }
        break;

      case "textareaAdv":
        break;

      case "email":
        if(
          ($data["mandatory"] && ($value == "")) ||
          ($value && !filter_var($value, FILTER_VALIDATE_EMAIL))
        ) return array("valid"=>false, "id"=>$id, "fieldName"=>$id, "fieldId"=>$id);
        break;

      case "text":
      case "textarea":
      case "password":
      case "select":
      case "hidden":
      default:
        if(
          ($data["mandatory"] && ($value == "")) ||
          ($data["mandatoryOnCreate"] && ($action == "add") && ($value == "")) ||
          ($data["regexp"] && !preg_match($data["regexp"], $value)) ||
          (is_array($data["values"]) && $value && !array_key_exists($value, $data["values"]))
        ) return array("valid"=>false, "id"=>$id, "fieldName"=>$id, "fieldId"=>$id);
        break;
    }
    return array("valid"=>true);
  }

  /*---------------------------------------------------------------------------
  - Method: validateObject
  ---------------------------------------------------------------------------*/
  public static function validateObject($fields, $values, $action="add") {
    if(!array_key_exists($action, BasicTable::$actions)) $action = "add";
    foreach($fields as $id=>$data) {
      $validateField = Fields::validateField($id, $data, $values, $action);
      if($validateField["valid"] !== true) return $validateField;
    }
    return array("valid"=>true);
  }

  /*---------------------------------------------------------------------------
  - Method: checkDatetime
  ---------------------------------------------------------------------------*/
  public static function checkDatetime($month, $day, $year, $hour=0, $minute=0, $second=0) {
    if($month || $day || $year || $hour || $minute || $second) {
      if(!checkdate($month, $day, $year)) return false;
      if(!is_numeric($hour) || ($hour < 0) || ($hour > 23)) return false;
      if(!is_numeric($minute) || ($minute < 0) || ($minute > 59)) return false;
      if(!is_numeric($second) || ($second < 0) || ($second > 59)) return false;
    }
    return true;
  }

  /*---------------------------------------------------------------------------
  - Method: decimalToDegree
  ---------------------------------------------------------------------------*/
  public static function decimalToSexagesimal($decimal, $asString=false) {
    $DMS = array("d"=>0, "m"=>0, "s"=>0);
    $isNegative = ($decimal < 0);
    $decimal = abs($decimal);
    $DMS["d"] = floor($decimal);
    $decimal = ($decimal - $DMS["d"]) * 60;
    $DMS["m"] = floor($decimal);
    $decimal = ($decimal - $DMS["m"]) * 60;
    $DMS["s"] = sprintf("%0.4f", $decimal);
    if($DMS["s"] == 60) {
      $DMS["s"] = sprintf("%0.4f", 0);
      $DMS["m"]++;
    }
    if($DMS["m"] == 60) {
      $DMS["m"] = 0;
      $DMS["d"]++;
    }
    if($isNegative) $DMS["d"] *= -1;
    if($asString) {
      $DMS = $DMS["d"] . "° " . $DMS["m"] . "' " . $DMS["s"] . "\"";
    }
    return $DMS;
  }

  /*---------------------------------------------------------------------------
  - Method: degreeToDecimal
  ---------------------------------------------------------------------------*/
  public static function sexagesimalToDecimal($degrees, $minutes, $seconds) {
    $decimal = abs($degrees) + ($minutes / 60) + ($seconds / 3600);
    if($degrees < 0) $decimal *= -1;
    return $decimal;
  }
}
?>