$(function() {
  init();
});

var cac40 = new MapData({
  file : './data/cac40.json',
  sourceCoords : ["48.8566","2.35097"],
});
var ftse100 = new MapData({
  file : './data/ftse100.json',
  sourceCoords : ["51.51121","-0.11982"],
});

var mapView = new MapView({
  model : ftse100
});

function init()
{
  loadJson();

  //Set hover effect on side menu
  $("#right-panel").hover(function() {
    $( this ).stop().animate({
      right: 0
    }, 500);
  },
  function() {
    $( this ).stop().animate({
      right: -150
    }, 500, function() {    
    });
  });

  //set callback for the map switcher
  $("#map-chooser input[name='options']").change(function(){
    mapView.model = (parseInt($(this).val()) == 0) ? ftse100 : cac40;
    loadJson();
  });
}

function loadJson()
{
  $('#map').html("");
  $('#loader').show();
  if(mapView.model.data == null)
  {
    $.getJSON(mapView.model.file, function(d){
      mapView.model.data = d;
      mapView.model.minValue = 100;
      mapView.model.maxValue = 0;
      for (var key in mapView.model.data) {
        mapView.model.menuEl +='<a href="#" id="val-'+key+'" onclick="setCompany(\''+key+'\')" class="list-group-item">'+mapView.model.data[key].name+"</a>";
        $.each( mapView.model.data[key].values, function(i, val){
          if(val<mapView.model.minValue) mapView.model.minValue = parseInt(val);
          if(val > mapView.model.maxValue) mapView.model.maxValue = parseInt(val);
        });
      }      
      $("#right-panel>div").html(mapView.model.menuEl);
      setCompany(0);
    });
  }
  else
  {
    $("#right-panel>div").html(mapView.model.menuEl);
    setCompany(0);
  }
}

function setCompany(val)
{
  $('#loader').show();
  mapView.model.currentCompany = val;
  mapView.drawMap();

  //update the title
  $('#title').html(mapView.model.data[mapView.model.currentCompany].name);

  //update the menu view
  $('a').removeClass("active");
  $('#val-'+mapView.model.currentCompany).addClass("active");

  //scale 1
  mapView.drawLinks(1);
  $('#loader').hide();
}