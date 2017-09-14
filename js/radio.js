//20110518.034
var ADMIN = {
  scriptName: "index.php",
  JS_VERSION: "029",

  /*---------------------------------------------------------------------------
  - Method: createXHR
  ---------------------------------------------------------------------------*/
  createXHR: function() {
    try {
      var XHR;
      if(window.XMLHttpRequest) XHR = new XMLHttpRequest();
      else if(window.ActiveXObject) XHR = new ActiveXObject("Microsoft.XMLHTTP");
      return XHR;
    } catch(e) { }
  },

  /*---------------------------------------------------------------------------
  - Method: addEvent
  ---------------------------------------------------------------------------*/
  addEvent: function(elementTarget, eventType, functionHandler) {
    try {
      if(elementTarget.addEventListener) elementTarget.addEventListener(eventType, functionHandler, false);
      else if(elementTarget.attachEvent) elementTarget.attachEvent("on" + eventType, functionHandler);
      else elementTarget["on" + eventType] = functionHandler;
    } catch(e) { }
  },
  
  /*---------------------------------------------------------------------------
  - Method: checkFormOnSubmit
  ---------------------------------------------------------------------------*/
  checkFormOnSubmit: function() {
    for(i=0; i<arguments.length; i++) {
      if(document.getElementById(arguments[i]).value == "") {
        alert("Compilare tutti i campi indicati con un asterisco");
        document.getElementById(arguments[i]).focus();
        return false;
      }
    }
    return true;
  },

  /*---------------------------------------------------------------------------
  - Method: validateObject
  ---------------------------------------------------------------------------*/
  validateObject: function(type, action) {
    try {
      var saveButton = document.getElementById("addEditSaveButton");
      saveButton.value = "Attendere...";
      saveButton.disabled = "disabled";
    } catch(e) { }

    var params = "";
    var fieldTagNames = new Array("input", "textarea", "select");
    for(i in fieldTagNames) {
      fields = document.getElementsByTagName(fieldTagNames[i]);
      for(j in fields) {
        if(fields[j].name && fields[j].value) {
          if((fields[j].type == "checkbox") && (!fields[j].checked)) continue;
          params += fields[j].name + "=" + encodeURIComponent(fields[j].value) + "&";
        }
      }
    }

    try {
      type = type.split(",");
      var voXHR = ADMIN.createXHR();
      for(i in type) {
        voXHR.open("POST", ADMIN.scriptName + "?cmd=Ajax&cmd2=validateObject&type=" + encodeURIComponent(type[i]) + "&action=" + action, false);
        voXHR.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        voXHR.send(params);
        var responseText = voXHR.responseText.split(":");
        if(responseText[0] != "true") {
          saveButton.value = "Salva";
          saveButton.disabled = "";
          switch(responseText[1]) {
            case "username": responseText[2] += "\nNota: lo username deve essere specificato in lettere minuscole."; break;
          }
          alert("Alcuni campi contengono errori" + ((responseText[2]) ? (": " + responseText[2]) : ""));
          var errorField = document.getElementById(responseText[1]);
          if(errorField) {
            errorField.focus();
            errorField.select();
          }
          return false;
        }
      }
      return true;
    } catch(e) { }

    return false;
  },

  /*---------------------------------------------------------------------------
  - Method: checkIfUsernameExists
  ---------------------------------------------------------------------------*/
  checkIfUsernameExists: function(usernameFieldId, doNotAlertIfUsernameExists) {
    var usernameExists = true;
    try {
      var usernameField = document.getElementById(usernameFieldId);
      if(usernameField.value != "") {
        var userXHR = ADMIN.createXHR();
        userXHR.open("GET", ADMIN.scriptName + "?cmd=Ajax&cmd2=checkIfUsernameExists&username=" + usernameField.value, false);
        userXHR.send(null);
        if(userXHR.responseText == "false") usernameExists = false;
      }
      if(!doNotAlertIfUsernameExists && usernameExists) {
        alert("Username non valido");
        usernameField.focus();
        usernameField.select();
      }
    } catch(e) { }
    return usernameExists;
  },

  /*---------------------------------------------------------------------------
  - Method: refreshTagList
  ---------------------------------------------------------------------------*/
  refreshTagList: function(id, valueSessionVar, value, singleSelection) {
    try {
      var rtlXHR = ADMIN.createXHR();
      rtlXHR.open("GET", ADMIN.scriptName + "?cmd=Ajax&cmd2=refreshTagList&id=" + id + "&value=" + value + "&valueSessionVar=" + valueSessionVar + "&singleSelection=" + singleSelection + "&reloadTagList=1", false);
      rtlXHR.send(null);
      document.getElementById("tagList-" + id).innerHTML = rtlXHR.responseText;
    } catch(e) { }
  },
  
  /*---------------------------------------------------------------------------
  - Method: radioAddEditMagliaChanged
  ---------------------------------------------------------------------------*/
  radioAddEditMagliaChanged: function() {
    var magliaObj = document.getElementById("maglia");
    var npObj = document.getElementById("ripetitoreId");
    npObj.length = 0;
    try {
      npObj.add(document.createElement("option"), null);
      var mXHR = ADMIN.createXHR();
      mXHR.open("GET", ADMIN.scriptName + "?cmd=Ajax&cmd2=getRipetitoriByMaglia&maglia=" + magliaObj.value, false);
      mXHR.send(null);
      var options = eval("(" + mXHR.responseText + ")");
      for(i in options) {
        maglia = document.createElement("option");
        maglia.value = i;
        maglia.text = options[i];
        npObj.add(maglia, null);
      }
    } catch(e) { }
  },

  /*---------------------------------------------------------------------------
  - Method: initMappa
  ---------------------------------------------------------------------------*/
  initMappa: function(oggetti) {
    var mappaOpt = {
      zoom: 6,
      center: new google.maps.LatLng(42.0, 12.0),
      mapTypeId: google.maps.MapTypeId.HYBRID,
      streetViewControl: false
    };
    var mappa = new google.maps.Map(document.getElementById("mappa"), mappaOpt);
    var imgRipetitore = new google.maps.MarkerImage('img/mappaRipetitore.png', new google.maps.Size(25, 36), new google.maps.Point(0, 0), new google.maps.Point(12, 36));
    var imgRipetitoreOff = new google.maps.MarkerImage('img/mappaRipetitoreOff.png', new google.maps.Size(25, 36), new google.maps.Point(0, 0), new google.maps.Point(12, 36));
    var imgFissa = new google.maps.MarkerImage('img/mappaFissa.png', new google.maps.Size(19, 19), new google.maps.Point(0, 0), new google.maps.Point(9, 12));

    google.maps.event.addListener(mappa, 'click', function(event) {
      var latLong = "" + event.latLng;
      try {
        var d2sXHR = ADMIN.createXHR();
        d2sXHR.open("GET", ADMIN.scriptName + "?cmd=Ajax&cmd2=decimalToSexagesimal&decimal=" + latLong, false);
        d2sXHR.send(null);
        latLong = d2sXHR.responseText.split(",");
      } catch(e) { }
      document.getElementById("mapCoords").innerHTML = "Lat.: " + latLong[0] + " - Long.: " + latLong[1];
    });

    for (var i=0; i<oggetti.length; i++) {
      var oggetto = oggetti[i];
      var icon = null;
      switch(oggetto[3]) {
        case "rOn": icon = imgRipetitore; break;
        case "rOff": icon = imgRipetitoreOff; break;
        case "f": icon = imgFissa; break;
      }
      var marker = new google.maps.Marker({
        position: new google.maps.LatLng(oggetto[1], oggetto[2]),
        map: mappa,
        icon: icon,
        title: oggetto[0],
        id: oggetto[4],
        type: oggetto[3]
      });
      google.maps.event.addListener(marker, 'click', function() {
        var messaggio = "No data available.";
        try {
          var iwXHR = ADMIN.createXHR();
          iwXHR.open("GET", ADMIN.scriptName + "?cmd=Ajax&cmd2=getMappaInfoWindowsText&type=" + this.type + "&id=" + this.id, false);
          iwXHR.send(null);
          messaggio = iwXHR.responseText;
        } catch(e) { }
        new google.maps.InfoWindow({
          content: messaggio
        }).open(mappa, this);
      });
    }
  },

  /*---------------------------------------------------------------------------
  - Method: radioCalcolaSelettiva
  ---------------------------------------------------------------------------*/
  radioCalcolaSelettiva: function(siglaBase) {
    ADMIN.closeDynamicPopup();
    var unitaCri = document.getElementById("unitaCri").value;
    var tipo = document.getElementById("tipo").value;
    try {
      var rcsXHR = ADMIN.createXHR();
      rcsXHR.open("GET", ADMIN.scriptName + "?cmd=Ajax&cmd2=radioCalcolaSelettiva&unitaCri=" + encodeURIComponent(unitaCri) + "&tipo=" + tipo + "&siglaBase=" + siglaBase, false);
      rcsXHR.send(null);
      response = rcsXHR.responseText.split("#");
      if(response[0] == "1") {
        document.getElementById("siglaRadio").value = response[1];
      } else if(response[0] == "2") {
        ADMIN.openDynamicPopup("Seleziona l'utilizzatore", response[1]);
      } else {
        alert("ATTENZIONE!!\n\n" + response[1]);
      }
    } catch(e) { }
  },

  /*---------------------------------------------------------------------------
  - Method: openDynamicPopup
  ---------------------------------------------------------------------------*/
  openDynamicPopup: function(title, message) {
    var popupWidth = 500;
    var innerWidth = parseInt((window.innerWidth) ? window.innerWidth : document.documentElement.clientWidth);
    var innerHeight = parseInt((window.innerHeight) ? window.innerHeight : document.documentElement.clientHeight);
    var pageYOffset = parseInt((window.innerWidth) ? window.pageYOffset : document.documentElement.scrollTop);

    var popupGnd = document.createElement("div");
    popupGnd.id = "popupDynamicGnd";
    popupGnd.style.width = "100%";
    popupGnd.style.height = document.documentElement.scrollHeight + "px";
    popupGnd.style.top = "0px";
    document.getElementById("main").appendChild(popupGnd);

    var popup = document.createElement("div");
    popup.id = "popupDynamic";
    popup.style.top = (pageYOffset + ((innerHeight - 350) / 2)) + "px";
    popup.style.left = (((innerWidth - popupWidth) / 2) + 50) + "px";
    popup.style.width = popupWidth + "px";
    popup.innerHTML = "<div class='popupTitle'>" + title + "</div><div class='popupMessage'>" + message + "</div>";
    document.getElementById("main").appendChild(popup);
  },

  /*---------------------------------------------------------------------------
  - Method: closeDynamicPopup
  ---------------------------------------------------------------------------*/
  closeDynamicPopup: function() {
    var gndObjs = new Array("popupDynamic", "popupDynamicGnd");
    for(i in gndObjs) {
      var popupObj = document.getElementById(gndObjs[i]);
      if(popupObj) {
        popupObj.parentNode.removeChild(popupObj);
      }
    }
  },

  /*---------------------------------------------------------------------------
  - Method: init
  ---------------------------------------------------------------------------*/
  init: function() {
    if(!document.getElementById) return;
  }
}

ADMIN.addEvent(window, "load", ADMIN.init);