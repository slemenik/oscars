var myApp=angular.module("myApp",[]);
myApp.controller("MainCtrl", function ($scope) {
	
    $scope.name = 'World';
  
  $scope.formData = [
  {label:'First Name', type:'text', required:'true'},
  {label:'Last Name', type:'text', required:'true'},
  {label:'Coffee Preference', type:'dropdown', options: ["HiTest", "Dunkin", "Decaf"]},
  {label: 'Address', type:'group', "Fields":[
      {label:'Street1', type:'text', required:'true'},
      {label:'Street2', type:'text', required:'true'},
      {label:'State', type:'dropdown',  options: ["California", "New York", "Florida"],
        "Fields" : [
          {label:'LOLCat1', type:'text', required:'true'},
          {label:'LOLCat2', type:'text', required:'true'}]
      }
    ]},
  ];
  
	var stolpci = [{"ID_stolpca":'1', "Ime": "Stolpec 1", "podstolpci": [{"ID_stolpca":'1.1', "Ime": "Podstolpec 1", "stolpci": []},{"ID_stolpca":'1.2', "Ime": "Podstolpec 2", "podstolpci": []}]},
					  {"ID_stolpca":'2',"Ime": "Stolpec 2", "podstolpci": []},
					  {"ID_stolpca":'3', "Ime": "Stolpec 3", "podstolpci": []}];
	var l = 6;
	var addLeft = function(seznam, sosedID) {
		var len = seznam.length;
		for (var i=0; i<len; i++) {
			if (seznam[i].ID_stolpca==sosedID) {
				var nov = {"ID_stolpca":''+l+'', "Ime":'Stolpec '+l+'', 'podstolpci': []};
				seznam.splice(i,0,nov);
				l+=1;
				break;
			}
			else {
				addLeft(seznam[i].podstolpci,sosedID);
			}
		}
	}
	addLeft(stolpci,'1.1');
	console.log(stolpci);
		
});

