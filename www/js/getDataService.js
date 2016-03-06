app.service('zoneService', function($http,$location,$log) {
  var that = this;
  this.zones = [];
  this.mode = '';
  cleanUrl=$location.absUrl().split('/').slice(0,-1).join('/').concat('/');
  urlZonesPlanning = cleanUrl +'zonesStatusFromPlanning.php';
  urlRZones = cleanUrl +'readZones.php';
  urlWZones = cleanUrl +'writeZones.php';
  urlMode = cleanUrl +'modeStatus.php';
  
  this.initZones = function() {
	  	console.log('mode init Zone:'+this.mode);	
		
		if(this.mode=='Auto'){
			$http.get(urlZonesPlanning).success(function(data) {
					that.zones = data;	
					console.log('mode Auto:'+that.zones['zone1']+' '+that.zones['zone2']);
					});
		}
		if(this.mode=='Manual'){
			$http.get(urlRZones).success(function(data) {
					that.zones = data;	
					console.log('mode Manual:'+that.zones['zone1']+' '+that.zones['zone2']);
					});	
		}	
  };  
  
  this.initMode = function() {
		// console.log('mode init Mode:'+that.mode);	
		$http.get(urlMode).success(function(data) {
			that.mode = data.mode;
			
			that.initZones();
		});
		// console.log('mode init Zone:'+that.mode);	
  };

  this.getZone = function() {
	  // console.log('mode getZone:'+that.zones['zone1']);	
      return this.zones;
  };
  
  this.setZone = function(zone, value) {
	  $http.get(urlWZones+'?'+zone+'='+value);
	  this.zones[zone]=value;
	  
	  return this.zones;
  }

  this.getMode = function() {
      return this.mode;
  };
  
  this.setMode = function(mode) {
	  $http.get(urlMode+'?mode='+mode);
	  this.mode=mode;
	  
	  this.initMode();

	  this.setZone('zone1', this.zones['zone1']);
	  this.setZone('zone2', this.zones['zone2']);
	  
	  return this.mode;
  }
});