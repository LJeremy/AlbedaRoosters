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

function getKlassen() {
    $.ajax({
        type: "GET",
        dataType: "json",
        url: 'temp/classes_albedashw_2015.json',
        success: function(data) {
            $(data.classes).each(function(i, val) {
                $.each(val, function(key, val) {
                    $('#klassen').append('<option value="' + val + '">' + key + '</option>')
                    //console.log(key + " : " + val);
                });
            });
        }
    });
}

getKlassen();

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
          //console.log(id);
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
    url: 'https://lars.ninja/albeda/albedashw/' + week + '/' + klas + '.json/',
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
