<?php
//20110303.017
$PAGE_TITLE = "Mappa";

$PAGE_CONTENT = "
  <h1>Mappa</h1>
  <h3>Mappa</h3>
  <div id='mappa' style='border:1px solid #000; height:700px;'></div>
  <script type='text/javascript' src='https://maps.google.com/maps/api/js?sensor=false&language=it'></script>
  <script type='text/javascript'>
    var oggetti = [\n";
      $rsRipetitori = $DBL->query("SELECT id, localitaCollegata, X(posizione) as xPos, Y(posizione) as yPos, ripetitoreAttivo FROM ripetitori WHERE tipo NOT IN(8, 9, 10) HAVING xPos <> 0 OR yPos <> 0");
      while($rcRipetitori = $rsRipetitori->fetch_object()) {
        $PAGE_CONTENT .= "['" . htmlentities($rcRipetitori->localitaCollegata, ENT_QUOTES, "utf-8") . "', $rcRipetitori->yPos, $rcRipetitori->xPos, '" . (($rcRipetitori->ripetitoreAttivo) ? "rOn" : "rOff") . "', " . $rcRipetitori->id . "],\n";
      }
      $rsFisse = $DBL->query("SELECT id, localita, X(posizione) as xPos, Y(posizione) as yPos FROM radio WHERE tipo=1 HAVING xPos <> 0 OR yPos <> 0");
      while($rcFisse = $rsFisse->fetch_object()) {
        $PAGE_CONTENT .= "['" . htmlentities($rcFisse->localita, ENT_QUOTES, "utf-8") . "', $rcFisse->yPos, $rcFisse->xPos, 'f', " . $rcFisse->id . "],\n";
      }
    $PAGE_CONTENT .= "];
    ADMIN.initMappa(oggetti);
  </script>
  <div id='mapCoords' style='color:#666; font-style:italic;'>Fare click per ottenere le coordinate del puntatore</div>
  <div style='color:#666; font-style:italic;'><b>" . $rsRipetitori->num_rows . "</b> ripetitori, <b>" . $rsFisse->num_rows . "</b> stazioni fisse.</div>
";
?>
