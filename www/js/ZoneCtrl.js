app.controller('zoneCtrl', function($scope,zoneService) {
  $scope.imgs = [{imgUrl: "picto/moon3.svg", name:"Moon"}, 
		{imgUrl: "picto/sun3.svg", name: "Sun"}];
  $scope.service = zoneService;

  zoneService.initZones();


  $scope.$watch('service.getZone()', function(newVal) {
    $scope.zones = newVal;
						
	$scope.val = 0;
	for (var i = 0; i < $scope.imgs.length; i++) {
							
		if($scope.imgs[i].name == $scope.zones[$scope.zoneId])
		{
			$scope.val=i;
			break;
		}
	}
						
	$scope.myImgUrl = $scope.imgs[$scope.val].imgUrl;				
												
  });

  	$scope.toggleImage = function() {
		$scope.val = 1 - $scope.val;
		$scope.myImgUrl = $scope.imgs[$scope.val].imgUrl;
		zoneService.setZone($scope.zoneId, $scope.imgs[$scope.val].name);
	};	

});