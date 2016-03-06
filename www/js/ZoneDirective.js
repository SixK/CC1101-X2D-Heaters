
app.directive('myZone', function () {
			return {
				 restrict: "A",
				scope: {
					mode: '=',
					zoneId: '@',
					detail: '@'
				},
				template: '<div class="zone">{{detail}}</div>  \
									<button ng-click="toggleImage()" class="svg" style="background-color: {{color}};">  \
									<img ng-src="{{myImgUrl}}" width="125px" height="125px" /> \
									</button>',
				controller: 'zoneCtrl',
				 link: function(scope, element, attrs, ctrls) {
				 		scope.$watch('mode', function(neww ,old){
						buttons = element.find('button');
							if (neww == "Auto") {
									buttons.attr('disabled', 'disabled');
									scope.color="dimgrey";
							}
							else {
								buttons.removeAttr('disabled');
								scope.color="none";
								}
						}, true)
				 }
			};
		});
