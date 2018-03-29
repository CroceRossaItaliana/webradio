<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180329.051
 */

/*===========================================================================
- CLASS PDF
===========================================================================*/
require("fpdf16/fpdf.php");
class PDF extends FPDF {
  private $PDF_CONST = array(
    "raggioMasters" => 150,
    "raggioSlaves" => 95,
    "incrementoRaggioCollisione" => 5,
    "labelFontSize" => 8,
  );
  private $magliaId;
  private $magliaDati;
  private $ripetitori;
  private $numeroRripetitori;
  private $hasUHF = false;

  /*---------------------------------------------------------------------------
  - Constructor
  ---------------------------------------------------------------------------*/
  function __construct($orientation, $unit, $format, $magliaId, $magliaDati) {
    $this->magliaId = $magliaId;
    $this->magliaDati = $magliaDati;

    $this->ripetitori = array();
    $rsRipetitori = $GLOBALS["DBL"]->query("SELECT id, tipo, numero, localitaCollegata FROM ripetitori WHERE maglia=" . $magliaId . " AND tipo IN(1,2,3,4,5,6,7)");
    $this->numeroRripetitori = $rsRipetitori->num_rows;
    while($rcRipetitori = $rsRipetitori->fetch_assoc()) {
      $this->ripetitori[$rcRipetitori["id"]] = $rcRipetitori;
      $rsSezioni = $GLOBALS["DBL"]->query("SELECT tipo, canale, frequenzaRicezione, frequenzaTrasmissione FROM ripetitoriSezioni WHERE idRipetitore=" . $rcRipetitori["id"]);
      while($rcSezioni = $rsSezioni->fetch_object()) {
        if($rcSezioni->canale) {
          $this->ripetitori["canali"][$rcSezioni->canale] = $rcSezioni->canale;
        }
        if(in_array($rcSezioni->tipo, array(2, 3))) {
          if($rcSezioni->frequenzaTrasmissione > 0) $this->ripetitori[$rcRipetitori["id"]]["frqTx"][] = $rcSezioni->frequenzaTrasmissione;
          if($rcSezioni->frequenzaRicezione > 0) $this->ripetitori[$rcRipetitori["id"]]["frqRx"][] = $rcSezioni->frequenzaRicezione;
        }
        if($rcSezioni->tipo == 2) $this->hasUHF = true;
      }
    }

    parent::__construct($orientation, $unit, $format);
  }

  /*---------------------------------------------------------------------------
  - Function: Header()
  ---------------------------------------------------------------------------*/
  function Header() {
    $this->Image("img/logo.jpg", 20, 15, 50);
    $this->SetXY(77, 25);
    $this->SetFont("Arial", "B", 28);
    $this->MultiCell(600, 14, utf8_decode("Maglia: " . $this->magliaDati->provincia));
    $this->SetFont("Arial", "B", 12);
    $this->SetXY(77, 48);
    $this->Cell(600, 14, utf8_decode("Pratica n° " . $this->magliaDati->numeroAutorizzazione));
    $this->SetXY(15, 80);
  }

  /*---------------------------------------------------------------------------
  - Function: Footer()
  ---------------------------------------------------------------------------*/
  function Footer() {
    $this->SetXY(727, 15);
    $this->SetFont("Times", "I", 8);
    $this->Cell(100, 10, utf8_decode("Pag. " . $this->PageNo() . "/{nb}"), 0, 0, "R");
  }

  /*---------------------------------------------------------------------------
  - Function: drawCover()
  ---------------------------------------------------------------------------*/
  function drawCover() {
    $this->SetY(90);
    $this->SetFont("Times", "B", 20);
    $this->Cell(0, 20, utf8_decode("CARATTERISTICHE GENERALI"), 0, 0, "C");
    $this->Rect(80, 140, 682, 400);
    $this->SetFont("Times", "", 16);
    $this->SetY(180);
    $this->SetLeftMargin(100);
    $col1 = 310;
    $col2 = 330;

    $this->Cell($col1, 24, utf8_decode("Banda di frequenza"));
    $this->Cell($col2, 24, utf8_decode(($this->hasUHF) ? "VHF-UHF" : "VHF"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Tipo collegamento"));
    $this->Cell($col2, 24, utf8_decode("TF"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Tipo comunicazione"));
    $this->Cell($col2, 24, utf8_decode("S"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° Canali"));
    $this->Cell($col2, 24, utf8_decode(sprintf("%02d", count($this->ripetitori["canali"]))), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Tono subaudio"));
    $this->Cell($col2, 24, utf8_decode("156.7 Hz"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Chiamata selettiva"));
    $this->Cell($col2, 24, utf8_decode($this->magliaDati->selettive), 0, 1);

    $totaleStazioniFisse = $GLOBALS["DBL"]->query("SELECT id FROM radio WHERE maglia=" . $this->magliaId . " AND tipo=1 AND escludiDaSchedaTecnica=0")->num_rows;
    $this->Cell($col1, 24, utf8_decode("N° totale stazioni terminali"));
    $this->Cell($col2, 24, utf8_decode($totaleStazioniFisse), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni terminali multiple destinazioni"));
    $this->Cell($col2, 24, utf8_decode("0"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni fisse"));
    $this->Cell($col2, 24, utf8_decode($totaleStazioniFisse), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni ripetitrici"));
    $this->Cell($col2, 24, utf8_decode($this->numeroRripetitori), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Lunghezza massima del collegamento"));
    $this->Cell($col2, 24, utf8_decode($GLOBALS["MAGLIA_LUNGHEZZA_COLLEGAMENTO"][$this->magliaDati->lunghezzaCollegamento]), 0, 1);
  }

  /*---------------------------------------------------------------------------
  - Function: drawMap()
  ---------------------------------------------------------------------------*/
  function drawMap() {
    $mapConfig = array(
      "x" => 15,
      "y" => 70,
      "width" => 812,
      "height" => 510,
    );
    $mapConfig["center"] = array("x"=>(($mapConfig["width"] / 2) + $mapConfig["x"]), "y"=>(($mapConfig["height"] / 2) + $mapConfig["y"] - 20));

    //Disegna area della mappa
    $this->Rect($mapConfig["x"], $mapConfig["y"], $mapConfig["width"], $mapConfig["height"]);

    //Cerca masters
    $masters = array();
    foreach($this->ripetitori as $rpId=>$rpData) {
      if(in_array($rpData["tipo"], array(1, 2, 3, 4, 5, 6))) $masters[] = $rpId;
    }

    //Posiziona masters
    if(count($masters) > 0) {
      if(count($masters) == 1) {
        $mst = $masters[0];
        $this->ripetitori[$mst]["pdfX"] = $mapConfig["center"]["x"];
        $this->ripetitori[$mst]["pdfY"] = $mapConfig["center"]["y"];
      } else {
        $masterAngle = 360 / count($masters);
        $currentAngle = 180;
        foreach($masters as $mst) {
          $mstY = round($this->PDF_CONST["raggioMasters"] * sin(deg2rad($currentAngle)));
          $mstX = round(sqrt(pow($this->PDF_CONST["raggioMasters"], 2) - pow($mstY, 2)));
          $mstX *= (cos(deg2rad($currentAngle)) > 0) ? 1 : -1;
          $this->ripetitori[$mst]["pdfX"] = $mstX + $mapConfig["center"]["x"];
          $this->ripetitori[$mst]["pdfY"] = $mstY + $mapConfig["center"]["y"];
          $currentAngle += $masterAngle;
        }

        //Collega masters
        foreach($masters as $mstConn1) {
          foreach($masters as $mstConn2) {
            if(($mstConn1 != $mstConn2) && $this->isRepeatersConnected($this->ripetitori[$mstConn1], $this->ripetitori[$mstConn2])) {
              $this->ripetitori[$mstConn1]["connectedMasters"][$mstConn2] = $mstConn2;
              $this->ripetitori[$mstConn2]["connectedMasters"][$mstConn1] = $mstConn1;
              $this->connectRepeaters($this->ripetitori[$mstConn1], $this->ripetitori[$mstConn2]);
            }
          }
        }
      }
    }

    //Cerca slaves
    $slaves = array();
    foreach($this->ripetitori as $rpId=>$rpData) {
      if(in_array($rpData["tipo"], array(7))) {
        $slaves[] = $rpId;
      }
    }

    //Collega slaves ai master
    foreach($slaves as $sl) {
      foreach($masters as $mst) {
        if($this->isRepeatersConnected($this->ripetitori[$sl], $this->ripetitori[$mst])) {
          $this->ripetitori[$mst]["slaves"][] = $sl;
        }
      }
    }

    //Posiziona slaves
    foreach($masters as $mst) {
      if(is_array($this->ripetitori[$mst]["slaves"])) {
        $currentAngle = ($this->ripetitori[$mst]["pdfX"] > $mapConfig["center"]["x"]) ? 275 : 45;
        foreach($this->ripetitori[$mst]["slaves"] as $sl) {
          $loops = 0;
          $raggioSlavesMoltiplicatore = 1;
          $currentAngleIncrement = 360 / (count($this->ripetitori[$mst]["slaves"]) + 4);
          do {
            $currentAngle += $currentAngleIncrement;
            $slY = round($this->PDF_CONST["raggioSlaves"] * $raggioSlavesMoltiplicatore * sin(deg2rad($currentAngle)));
            $slX = round(sqrt(pow(($this->PDF_CONST["raggioSlaves"] * $raggioSlavesMoltiplicatore), 2) - pow($slY, 2)));
            $slX *= (cos(deg2rad($currentAngle)) > 0) ? 1 : -1;
            $slX += $this->ripetitori[$mst]["pdfX"];
            $slY += $this->ripetitori[$mst]["pdfY"];
            if($loops > 80) $raggioSlavesMoltiplicatore = 2;
            if($loops > 120) break;
          } while($this->isPositionCollide($this->ripetitori, $slX, $slY, $currentAngle));
          $this->ripetitori[$sl]["pdfX"] = $slX;
          $this->ripetitori[$sl]["pdfY"] = $slY;
          $currentAngle += $this->PDF_CONST["incrementoRaggioCollisione"];
          $this->connectRepeaters($this->ripetitori[$mst], $this->ripetitori[$sl]);
        }
      }
    }

    //Disegna ripetitori
    foreach($this->ripetitori as $rpt) {
      if(isset($rpt["pdfX"])) {
        $this->drawRepeater($rpt["pdfX"], $rpt["pdfY"], $rpt["tipo"], $rpt["localitaCollegata"], $rpt["numero"]);
      }
    }

    //Disegna stazioni fisse
    $numeroPostazioniFisse = $GLOBALS["DBL"]->query("SELECT id FROM radio WHERE maglia=" . $this->magliaId . " AND tipo=1 AND escludiDaSchedaTecnica=0")->num_rows;
    $stazioniFisseImageSize = getimagesize("img/pdf/stazioniFisse.png");
    $this->Image("img/pdf/stazioniFisse.png", 720, 450);
    $this->SetFont("Arial", "", 7);
    $this->SetFillColor(255, 255, 255);
    $this->SetXY((720 + ($stazioniFisseImageSize[0] / 2) - (120 / 2)), (450 + $stazioniFisseImageSize[1] + 2));
    $this->MultiCell(120, 7, utf8_decode($numeroPostazioniFisse . " Stazioni Fisse" . (($this->magliaDati->visualizzaScrittaComunicazioneNegataTraStazioniFisse) ? "\n\nLe stazioni fisse non comunicano tra loro e sono autorizzate all'utilizzo dell'isofrequenza solo in caso di avaria della stazione ripetitrice." : "")), 0, "C", true);
  }

  /*---------------------------------------------------------------------------
  - Function: drawRepeater()
  ---------------------------------------------------------------------------*/
  private function drawRepeater($x, $y, $tipo, $label, $number) {
    $ripetitoreImageSize = getimagesize("img/pdf/ripetitore_" . $tipo . ".png");
    $this->Image("img/pdf/ripetitore_" . $tipo . ".png", ($x - ($ripetitoreImageSize[0] / 2)), ($y - ($ripetitoreImageSize[1] / 2)));
    $this->SetFont("Arial", "", $this->PDF_CONST["labelFontSize"]);
    $this->SetFillColor(255, 255, 255);
    $this->SetXY(($x - ($ripetitoreImageSize[0] * (($tipo == 2) ? 1.5 : 2) / 2)), ($y + ($ripetitoreImageSize[1] / 2)));
    $this->MultiCell($ripetitoreImageSize[0] * (($tipo == 2) ? 1.5 : 2), $this->PDF_CONST["labelFontSize"], utf8_decode($label), 0, "C", true);
    $this->SetXY(($x - ($ripetitoreImageSize[0] / 2)), ($y + ($ripetitoreImageSize[1] / 2) - ($this->PDF_CONST["labelFontSize"] * (($tipo == 2) ? 3.5 : 2.5))));
    $this->MultiCell($ripetitoreImageSize[0], $this->PDF_CONST["labelFontSize"], utf8_decode($number . "\nPR"), 0, "C");
  }

  /*---------------------------------------------------------------------------
  - Function: connectRepeaters()
  ---------------------------------------------------------------------------*/
  private function connectRepeaters($r1, $r2) {
    $this->Line($r1["pdfX"], $r1["pdfY"], $r2["pdfX"], $r2["pdfY"]);
  }

  /*---------------------------------------------------------------------------
  - Function: isRepeatersConnected()
  ---------------------------------------------------------------------------*/
  private function isRepeatersConnected($r1, $r2) {
    foreach($r1["frqTx"] as $f) {
      if(in_array($f, $r2["frqRx"])) return true;
    }
    foreach($r1["frqRx"] as $f) {
      if(in_array($f, $r2["frqTx"])) return true;
    }
    return false;
  }

  /*---------------------------------------------------------------------------
  - Function: isPositionCollide()
  ---------------------------------------------------------------------------*/
  private function isPositionCollide($ripetitori, $x, $y, $angle) {
    foreach($ripetitori as $r) {
      if(isset($r["pdfX"]) && $this->isPointInArea($this->calculateAreaByOriginAndSize($r["pdfX"], $r["pdfY"], $_SESSION["CACHE_IMAGE_SIZES"]["RIPETITORE"]), array("x"=>$x, "y"=>$y))) return true;
      if(is_array($r["connectedMasters"])) {
        foreach($r["connectedMasters"] as $cm) {
          $cm = $ripetitori[$cm];
          $y = $cm["pdfY"] - $r["pdfY"];
          $rptAngle = rad2deg(asin($y));
          $rtpAngle += ($cm["pdfX"] > $r["pdfX"]) ? 0 : 180;
          if(((($cm["pdfX"] > $r["pdfX"]) && ($x < $cm["pdfX"])) || (($cm["pdfX"] < $r["pdfX"]) && ($x > $cm["pdfX"]))) && ($angle > ($rtpAngle - $this->PDF_CONST["incrementoRaggioCollisione"])) && ($angle < ($rtpAngle + $this->PDF_CONST["incrementoRaggioCollisione"]))) return true;
        }
      }
    }
    return false;
  }

  /*---------------------------------------------------------------------------
  - Function: isPointInArea()
  ---------------------------------------------------------------------------*/
  private function isPointInArea($area, $point) {
    return (($point["x"] > $area["x1"]) && ($point["x"] < $area["x2"]) && ($point["y"] > $area["y1"]) && ($point["y"] < $area["y2"]));
  }

  /*---------------------------------------------------------------------------
  - Function: calculateAreaByOriginAndSize()
  ---------------------------------------------------------------------------*/
  private function calculateAreaByOriginAndSize($x, $y, $size) {
    return array(
      "x1" => $x - $size[0] - 20,
      "y1" => $y - $size[1] - 20,
      "x2" => $x + $size[0] + 20,
      "y2" => $y + $size[1] + 20,
    );
  }

  /*---------------------------------------------------------------------------
  - Function: drawStazioniFisse()
  ---------------------------------------------------------------------------*/
  function drawStazioniFisse() {
    $columns = array(
      "1" => array("title"=>"Ubicazione Stazioni Fisse", "width"=>260),
      "2" => array("title"=>"Apparati", "width"=>170),
      "3" => array("title"=>"Antenne", "width"=>170),
      "4" => array("title"=>"Emissione (MHz)", "width"=>200),
    );

    $i = $row = 0;
    $rs = $GLOBALS["DBL"]->query("SELECT *, X(posizione) AS x, Y(posizione) AS y FROM radio WHERE maglia=" . $this->magliaId . " AND tipo=1 AND escludiDaSchedaTecnica=0 ORDER BY localita");
    while($rc = $rs->fetch_object()) {
      $this->SetLeftMargin(15);
      if(($i == 0) || ($row > 11)) {
        $row = 0;
        $this->AddPage();
        $this->SetFont("Times", "BI", 14);
        foreach($columns as $col) {
          $this->Cell($col["width"], 15, utf8_decode($col["title"]), "B");
        }
        $this->Ln();
      }
      $this->SetFont("Times", "", 10);
      $rowY = 83 + 15 + (12 * 3 * $row);

      //Colonna 1
      $this->SetXY(15, $rowY);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Località: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rc->localita));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Ponte N°: "));
      $this->SetFont("", "");
      $rc->ripetitoreId = $GLOBALS["DBL"]->query("SELECT id, numero FROM ripetitori WHERE id=" . $rc->ripetitoreId)->fetch_object();
      $this->Write(12, utf8_decode($rc->ripetitoreId->numero));
      $this->SetFont("", "I");
      $this->SetX(15 + 90);
      $this->Write(12, utf8_decode("Alt. slm: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rc->altitudine));
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("m"));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Lat. N: "));
      $this->SetFont("", "");
      $lat = Fields::decimalToSexagesimal($rc->y);
      $this->Write(12, utf8_decode(sprintf("%02d", $lat["d"]) . sprintf("%02d", $lat["m"]) . sprintf("%02d", $lat["s"])));
      $this->SetFont("", "I");
      $this->SetX(15 + 90);
      $this->Write(12, utf8_decode("Lon. E: "));
      $this->SetFont("", "");
      $long = Fields::decimalToSexagesimal($rc->x);
      $this->Write(12, utf8_decode(sprintf("%02d", $long["d"]) . sprintf("%02d", $long["m"]) . sprintf("%02d", $long["s"])));

      //Colonna 2
      $radio = getRadioById($rc->modelloRadio);
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"]);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Ditta: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($radio["produttore"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Mod.: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($radio["modello"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Pot.: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode("10"));
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("W"));

      //Colonna 3
      $antenna = getAntennaById($rc->modelloAntenna);
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"]);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Mod.: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($antenna["modello"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Guad.: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($antenna["guadagno"]));
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("dB"));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("ERP: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode("3"));
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("W"));

      //Colonna 4
      $frequenze = getCanali("datiCanale", $this->magliaDati->canale);
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"]);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Tx VHF: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode(sprintf("%0.4f", $frequenze["tx"])));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Rx VHF: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode(sprintf("%0.4f", $frequenze["rx"])));

      $lineY = $rowY + (12 * 3);
      $this->Line(15, $lineY, (15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"] + $columns["4"]["width"]), $lineY);

      $i++;
      $row++;
    }
  }

  /*---------------------------------------------------------------------------
  - Function: drawRipetitori()
  ---------------------------------------------------------------------------*/
  function drawRipetitori() {
    $columns = array(
      "1" => array("title"=>"Ubicazione Ripetitori", "width"=>260),
      "2" => array("title"=>"Apparati", "width"=>170),
      "3" => array("title"=>"Antenne", "width"=>190),
      "4" => array("title"=>"Frequenze MHz", "width"=>180),
    );
    $rowY = $this->drawRipetitoriHeader($columns);

    $ripetitoriRowsDataId = 0;
    $ripetitoriRowsData = array();
    $rs = $GLOBALS["DBL"]->query("SELECT *, X(posizione) AS x, Y(posizione) AS y FROM ripetitori WHERE maglia=" . $this->magliaId . " AND tipo IN(1,2,3,4,5,6,7) ORDER BY numero");
    while($rc = $rs->fetch_object()) {
      $rc->y = Fields::decimalToSexagesimal($rc->y);
      $rc->x = Fields::decimalToSexagesimal($rc->x);

      //Colonna 1
      $ripetitoriRowsData[$ripetitoriRowsDataId]["col1"] = array(
        "localita" => $rc->localitaCollegata,
        "numeroPonte" => $rc->numero,
        "altitudine" => $rc->altitudine,
        "latitudine" => sprintf("%02d", $rc->y["d"]) . sprintf("%02d", $rc->y["m"]) . sprintf("%02d", $rc->y["s"]),
        "longitudine" => sprintf("%02d", $rc->x["d"]) . sprintf("%02d", $rc->x["m"]) . sprintf("%02d", $rc->x["s"]),
      );

      //Colonna 2
      $sezioni = array();
      $rsSezioni = $GLOBALS["DBL"]->query("SELECT * FROM ripetitoriSezioni WHERE idRipetitore=" . $rc->id . " AND tipo IN(1, 2) ORDER BY tipo DESC, frequenzaTrasmissione DESC, frequenzaRicezione DESC");
      while($rcSezioni = $rsSezioni->fetch_object()) $sezioni[] = $rcSezioni;
      $hasSHF = ($rc->tipo == 7) && $GLOBALS["DBL"]->query("SELECT id FROM ripetitoriSezioni WHERE idRipetitore=" . $rc->id . " AND tipo=3")->num_rows;
      foreach($sezioni as $sezione) {
        $sezione->modelloRipetitore = getRipetitoreById($sezione->modelloRipetitore);
        $ripetitoriRowsData[$ripetitoriRowsDataId]["col2"] = array(
          "produttore" => $sezione->modelloRipetitore["produttore"],
          "modello" => $sezione->modelloRipetitore["modello"],
        );

        //Colonna 3
        $antenne = array($sezione->modelloAntenna1, $sezione->modelloAntenna2);
        $col3RowsDataIdOffset = 0;
        foreach($antenne as $antenna) {
          if(!$antenna) continue;
          $antenna = getAntennaById($antenna);
          $ripetitoriRowsData[$ripetitoriRowsDataId + $col3RowsDataIdOffset]["col3"] = array(
            "modello" => $antenna["modello"],
            "guadagno" => $antenna["guadagno"],
          );
          $col3RowsDataIdOffset++;
        }

        //Colonna 4
        if($sezione->tipo == 1) {
          $frequenze = getCanali("datiCanale", $sezione->canale);
          $sezione->frequenzaTrasmissione = sprintf("%0.4f", $frequenze["rx"]);
          $sezione->frequenzaRicezione = sprintf("%0.4f", $frequenze["tx"]);
        }
        $ripetitoriRowsData[$ripetitoriRowsDataId]["col4"] = array(
          "tipoSezione" => $sezione->tipo,
          "frqTx" => $sezione->frequenzaTrasmissione,
          "frqRx" => $sezione->frequenzaRicezione,
          "hasSHF" => $hasSHF,
        );

        $ripetitoriRowsDataId += $col3RowsDataIdOffset;
      }
      $ripetitoriRowsDataId++;
    }

    foreach($ripetitoriRowsData as $rowData) {
      if(is_array($rowData["col1"])) {
        $this->Line(15, $rowY, (15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"] + $columns["4"]["width"]), $rowY);
      }
      $rowY = $this->drawRipetitoriRow($columns, $rowData, $rowY);
    }
  }

  /*---------------------------------------------------------------------------
  - Function: drawRipetitoriHeader()
  ---------------------------------------------------------------------------*/
  function drawRipetitoriHeader($columns) {
    $rowY = 83 + 15 + 10;
    $this->AddPage();
    $this->SetLeftMargin(15);
    $this->SetFont("Times", "BI", 14);
    foreach($columns as $col) {
      $this->Cell($col["width"], 15, utf8_decode($col["title"]));
    }
    $this->Ln();
    $this->SetFontSize(9);
    $this->Cell(($columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"]), 10, " ", "B");
    $this->Cell(($columns["4"]["width"] / 2), 10, "VHF", "B");
    $this->Cell(($columns["4"]["width"] / 2), 10, "UHF", "B");
    $this->Ln();
    $this->SetFont("Times", "", 10);
    return $rowY;
  }

  /*---------------------------------------------------------------------------
  - Function: drawRipetitoriRow()
  ---------------------------------------------------------------------------*/
  function drawRipetitoriRow($columns, $rowData, $rowY) {
    $this->SetLeftMargin(15);
    if($this->GetY() > 450) {
      $rowY = $this->drawRipetitoriHeader($columns);
    }
    $maxYRow = $rowY;

    //Colonna 1
    if(is_array($rowData["col1"])) {
      $this->SetFont("Times", "", 10);
      $this->SetXY(15, $rowY);
      $this->SetLeftMargin(15);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Località: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col1"]["localita"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Ponte N°: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col1"]["numeroPonte"]));
      $this->SetFont("", "I");
      $this->SetX(15 + 90);
      $this->Write(12, utf8_decode("Alt. slm: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col1"]["altitudine"]));
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("m"));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Lat. N: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col1"]["latitudine"]));
      $this->SetFont("", "I");
      $this->SetX(15 + 90);
      $this->Write(12, utf8_decode("Lon. E: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col1"]["longitudine"]));
      $this->Ln();
      if($maxYRow < $this->GetY()) $maxYRow = $this->GetY();
    }

    //Colonna 2
    if(is_array($rowData["col2"])) {
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"]);
      $this->SetX(15 + $columns["1"]["width"]);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Ditta: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col2"]["produttore"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Mod.: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col2"]["modello"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Pot.: "));
      $this->SetFont("", "");
      if($rowData["col4"]["frqTx"] < 1) {
        $this->Write(12, utf8_decode("-"));
      } else {
        $this->Write(12, utf8_decode("10"));
        $this->SetFont("", "I");
        $this->Write(12, utf8_decode("W"));
      }
      $this->Ln();
      if($maxYRow < $this->GetY()) $maxYRow = $this->GetY();
    }

    //Colonna 3
    if(is_array($rowData["col3"]) && ((float)$rowData["col4"]["frqTx"])) {
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"]);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Mod.: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col3"]["modello"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Guad.: "));
      $this->SetFont("", "");
      $this->Write(12, utf8_decode($rowData["col3"]["guadagno"]));
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("dB"));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("ERP: "));
      $this->SetFont("", "");
      if($rowData["col4"]["frqTx"] < 1) {
        $this->Write(12, utf8_decode("-"));
      } else {
        $this->Write(12, utf8_decode("3"));
        $this->SetFont("", "I");
        $this->Write(12, utf8_decode("W"));
      }
      $this->Ln();
      if($maxYRow < $this->GetY()) $maxYRow = $this->GetY();
    }

    //Colonna 4
    if(is_array($rowData["col4"])) {
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"]);
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Tx: "));
      $this->SetFont("", "");
      if($rowData["col4"]["tipoSezione"] == 2) $this->SetX(15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"] + ($columns["4"]["width"] / 2));
      $this->Write(12, utf8_decode($rowData["col4"]["frqTx"]));
      $this->Ln();
      $this->SetFont("", "I");
      $this->Write(12, utf8_decode("Rx: "));
      $this->SetFont("", "");
      if($rowData["col4"]["tipoSezione"] == 2) $this->SetX(15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"] + ($columns["4"]["width"] / 2));
      $this->Write(12, utf8_decode($rowData["col4"]["frqRx"]));
      if($rowData["col4"]["hasSHF"]) {
        $this->Ln();
        $this->Write(12, utf8_decode("Ponte collegato al master via LAN/IP"));
      }
      $this->Ln();
      if($maxYRow < $this->GetY()) $maxYRow = $this->GetY();
    }

    return $maxYRow;
  }
}
?>