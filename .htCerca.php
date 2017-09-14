<?php
//20110201.005
$PAGE_TITLE = "Cerca";

$PAGE_CONTENT = "
  <h1>Risultato ricerca</h1>
  <h2>Ricerca per termine &quot;" . htmlentities($_POST["searchKeyword"], ENT_QUOTES, "utf-8") . "&quot;</h2>
";

$kwd = $DBL->real_escape_string(stripslashes(trim($_POST["searchKeyword"])));
if($kwd) {
  $hasResult = false;

  $rs = $DBL->query("SELECT id, unitaCri, maglia, matricola, tipo FROM radio WHERE matricola LIKE '%" . $kwd . "%' OR localita LIKE '%" . $kwd . "%' OR siglaRadio LIKE '%" . $kwd . "%' OR targaAutomezzo LIKE '%" . $kwd . "%' OR utilizzatore LIKE '%" . $kwd . "%' OR note LIKE '%" . $kwd . "%'");
  if($rs->num_rows) {
    $PAGE_CONTENT .= "<div style='font-size:larger; font-weight:bold;'>Radio</div>\n";
    $hasResult = true;
  }
  while($rc = $rs->fetch_object()) {
    $PAGE_CONTENT .= "<div>
      &bull; " . $RADIO_TIPO[$rc->tipo] . "
      mat. <b>" . $rc->matricola . "</b>,
      maglia <b>" . getMagliaById($rc->maglia) . "</b>,
      proprietario <b>" . $rc->unitaCri . "</b>
      [<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Radio&cmd2=show&id=" . $rc->id . "'>Visualizza</a>]
      [<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Radio&cmd2=edit&id=" . $rc->id . "'>Modifica</a>]
    </div>\n";
  }

  $rs = $DBL->query("SELECT id, unitaCri, maglia, tipo, numero, localitaCollegata FROM ripetitori WHERE localitaCollegata LIKE '%" . $kwd . "%' OR ospitante LIKE '%" . $kwd . "%' OR note LIKE '%" . $kwd . "%'");
  if($rs->num_rows) {
    $PAGE_CONTENT .= "<div style='font-size:larger; font-weight:bold;'>Ripetitori</div>\n";
    $hasResult = true;
  }
  while($rc = $rs->fetch_object()) {
    $PAGE_CONTENT .= "<div>
      &bull; " . $RIPETITORE_TIPO[$rc->tipo] . "
      di <b>" . $rc->localitaCollegata . "</b>
      numero <b>" . $rc->numero . "</b>,
      maglia <b>" . getMagliaById($rc->maglia) . "</b>,
      proprietario <b>" . $rc->unitaCri . "</b>
      [<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Ripetitori&cmd2=show&id=" . $rc->id . "'>Visualizza</a>]
      [<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Ripetitori&cmd2=edit&id=" . $rc->id . "'>Modifica</a>]
    </div>\n";
  }

  $rs = $DBL->query("SELECT id, idRipetitore, matricola, tipo, localitaCollegata, funzione, canale, identita FROM ripetitoriSezioni WHERE localitaCollegata LIKE '%" . $kwd . "%' OR matricola LIKE '%" . $kwd . "%' OR funzione LIKE '%" . $kwd . "%' OR identita LIKE '%" . $kwd . "%' OR note LIKE '%" . $kwd . "%'");
  if($rs->num_rows) {
    $PAGE_CONTENT .= "<div style='font-size:larger; font-weight:bold;'>Sezioni di ripetitori</div>\n";
    $hasResult = true;
  }
  while($rc = $rs->fetch_object()) {
    $PAGE_CONTENT .= "<div>
      &bull; " . $RIPETITORE_SEZIONE_TIPO[$rc->tipo] . "
      matricola <b>" . $rc->matricola . "</b>,
      " . (($rc->funzione) ? "funzione <b>" . $rc->funzione . "</b>," : "") . "
      " . (($rc->canale) ? "canale <b>" . $rc->canale . "</b>," : "") . "
      " . (($rc->identita) ? "identità <b>" . $rc->identita . "</b>" : "") . "
      [<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=RipetitoriSezioni&cmd2=show&id=" . $rc->id . "&idRipetitore=" . $rc->idRipetitore . "'>Visualizza</a>]
      [<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=RipetitoriSezioni&cmd2=edit&id=" . $rc->id . "&idRipetitore=" . $rc->idRipetitore . "'>Modifica</a>]
    </div>\n";
  }

  if(!$hasResult) {
    $PAGE_CONTENT .= "<div><i>Nessun oggetto corrisponde ai parametri di ricerca impostati.</i></div>\n";
  }
} else {
  $PAGE_CONTENT .= "<div><i>Specificare un termine di ricerca.</i></div>\n";
}
?>