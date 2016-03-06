app.controller('TogglerCtrl', function (zoneService, $scope) {
    $scope.modes = [{name:"Auto"}, 
                         {name: "Manual"}];
						 
	$scope.service = zoneService;
	zoneService.initMode();
	
	$scope.$watch('service.getMode()', function(newVal) {
			$scope.mode = newVal;
			
			$scope.val = 0;
			for (var i = 0; i < $scope.modes.length; i++) {
				// console.log('modes:'+$scope.modes[i].name);	
				// console.log('mode:'+$scope.mode);	
		
				if($scope.modes[i].name == $scope.mode)
				{
					$scope.val=i;
					break;
				}
			}
			$scope.mode = $scope.modes[$scope.val].name;

	  });
	

	  function sleep(milliseconds) {
  var start = new Date().getTime();
	for (var i = 0; i < 1e7; i++) {
			if ((new Date().getTime() - start) > milliseconds){
				break;
			}
		}
	}
	
    $scope.toggleMode = function() {

		if ($scope.lastClick && new Date() - $scope.lastAnswerClick < 300) {
           return;
       }
       $scope.lastClick = new Date();
	   $scope.lastAnswerClick = new Date();

		
		$scope.val = 1 - $scope.val;
		$scope.mode = $scope.modes[$scope.val].name;

		zoneService.setMode($scope.mode);
    };
});
