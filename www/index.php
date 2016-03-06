<?php

require_once 'planning.php';

?>
<html>
<head>
	<link rel="stylesheet" href="css/styles.css"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body ng-app="X2D">
<div style="position: absolute;">
<div ng-controller="TogglerCtrl" style="float: left;position: relative;" id="container">
		<div id="mode"> Mode :
			<button ng-click="toggleMode()" style="font-size: large;">{{mode}}</button>
		</div>

		<div id="zone1" class="zone">
			<div my-zone zone-id="zone1" mode="mode" detail="Gestion chauffage Zone 1">
			</div>
		</div>
		
		<div id="zone2" class="zone">
			<div my-zone zone-id="zone2" mode="mode" detail="Gestion chauffage Zone 2">
			</div>
		</div>
<!--		
		<div id="zone3" class="zone">
			<div my-zone zone-id="zone3" mode="mode" detail="Gestion chauffage Zone 3">
			</div>
		</div>
-->
</div>

<div id="planning">
	<a href="#" ng-model="collapsed" ng-click="collapsed=!collapsed">Afficher/Cacher Planning</a>
	
	<div id="holder" ng-show="collapsed">
		<?php
		echo $table_html;
		?>
	</div>

	<div ng-show="collapsed">
	  <p id="upload" class="hidden"><label>Drag &amp; drop not supported, but you can still upload via this input field:<br><input type="file"></label></p>
	  <p id="filereader">File API &amp; FileReader API not supported</p>
	  <p id="formdata">XHR2's FormData is not supported</p>
	  <p id="progress">XHR2's upload progress isn't supported</p>
	  <p>Upload progress: <progress id="uploadprogress" max="100" value="0">0</progress></p>
	  <p>Drag an openoffice .ods file from your desktop on to the drop zone above to see the browser upload automatically to this server. then refresh page to view new planning.</p>
	</div>
</div>
</div>

<script src="js/dragndrop.js"></script>

<script src="js/angular.min.js"></script>
<script>
var app = angular.module('X2D', []);
</script>
<script src="js/getDataService.js"></script>
<script src="js/TogglerCtrl.js"></script>
<script src="js/ZoneDirective.js"></script>
<script src="js/ZoneCtrl.js"></script>


</body>
</html>