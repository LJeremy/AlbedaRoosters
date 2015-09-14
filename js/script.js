$(document).ready(function() {

if(localStorage.getItem('klas') != null){
  var klas = 'c000' + localStorage.getItem('klas');
      $("li a").click(function(event) {
          var id = event.target.id;
          console.log(id);
          getRooster(id, klas);
      });
      getRooster(1, klas);
} else {
  $('#settings').openModal();
}

// Array met alle Klassen (SHW)
var classes = ["42IB4-2A_K","42IB4-2B_K","42ICT2-1A_K","42ICT2-2A_K","42ICT3-1A_K","42ICT3-1B_K","42ICT3-1C_K","42ICT3-2A_K","42ICT3-2B_K","42ICT4-1A_K","42ICT4-1B_K","42ICT4-1C_K","42NB4-2A_K","42NB4-2B_K","42_TE41TMO","42DAUT3A_K","42DAUT4A_K","42DCT1A_K","42DCT2A_K","42DCT3A_K","42DENE3A_K","42DENE4A_K","42DMIT1A_K","42DMIT2A_K","42DMIT2HAVO_K","42DMIT3A_K","42DMIT4A_K","42DTDE1A_K","42DTDE2A_K","42DTDE2HAVO_K","42DTDW1A_K","42DTDW2A_K","42DTDW3A_K","42DTDW4A_K","42DMKB1A_K","42DMKB2A_K","42DMKB3A_K","42DMKB3B_k","42DMKB4A_K","42DMKB4B_K","42DMKB4C_K","42DMKB1B_K","42ICT4-2A_K","42ICT4-2B_K","42ICT4-2C_K","42NB4-2C_K","42ICT3-1D_K","42DW2HAVO_K","42CT2HAVO_K"];
var option = '';
  // Geef alle Klassen een value
  for (var i=0;i<classes.length;i++){
    var j = i + 1;
    // Als de value minder is dan 10
    if(j < 10){
      // Doe er 1 bij
      var p = i + 1;
      // Zet een 0 voor de value zodat het 01 is in plaats van 1
      var j = "0" + p;
      option += '<option value="'+ j + '">' + classes[i] + '</option>';
    }
    else {
     option += '<option value="'+ j + '">' + classes[i] + '</option>';
    }
  }
$('#klassen').append(option);

$('#klassen').on('change', function (e) {
  var klasnaam = $("#klassen option:selected").text();
  var klasid = $("#klassen option:selected").val();
    $('#saveKlas').on('click', function (e) {
      localStorage.setItem('klas', klasid);
      console.log('Saved');
      var klas = 'c000' + localStorage.getItem('klas');
      // Krijg het rooster van de Dag 
      $("li a").click(function(event) {
          var id = event.target.id;
          console.log(id);
          getRooster(id, klas);
      });
      getRooster(1, klas);
  });
});

}); // End document.ready


// Kleine hack om de week van het jaar te krijgen
Date.prototype.getWeek = function() {
    var onejan = new Date(this.getFullYear(),0,1);
    var today = new Date(this.getFullYear(),this.getMonth(),this.getDate());
    var dayOfYear = ((today - onejan +1)/86400000);
    return Math.ceil(dayOfYear/7)
};

var today = new Date();
var n = today.getDay();
// Als het weekend is, ga naar de volgende week
if (n => 6 && n <= 0){
    var w = today.getWeek();
    var week = w + 1;
} else {
  var week = today.getWeek();
}
console.log('Week: ' + week);

function getRooster(id, klas) {
  NProgress.start();
  $.ajax({
    type: "GET",
    dataType: "json",
    url: 'http://localhost/albeda/albedashw/' + week + '/' + klas + '.json/',
    success: function (data) {
      NProgress.done();
      //Sort of displays the schedule
      $('tbody').empty();
      $.each(data.rooster.weekdag[id].uur, function(h, obj) {
        $('.tabel').append('<tbody><tr><td>' + h + '</td><td>' + obj.teacher + '</td><td>' + obj.subject + '</td><td>' + obj.room+ '</td></tbody>')
      });
      
    }
  });
}
