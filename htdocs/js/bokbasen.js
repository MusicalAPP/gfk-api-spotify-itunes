 var tabApp = angular.module('tabbedApp', []);

  tabApp.controller('tabController', ['$scope', function($scope) {
  	
  	  //constant var
  	  $scope.display_none='none';
  	  $scope.display_block='block';

  	  $scope.error_status='has-error';
      $scope.succes_status = 'has-success';

  	  //$scope.ftpbutton='active';
  	  //$scope.localbutton ='';

  	  /*
  	  //variable
      $scope.spotifyTab = 'active';
      $scope.itunesTab = ''; 
      $scope.sevenDigitalTab = '';

      $scope.varCountry = 'de';*/
      var currentTime = new Date();
      $scope.curYear = currentTime.getFullYear();
      $scope.curMonth = (currentTime.getMonth() + 1 < 10)? '0'+(currentTime.getMonth() + 1).toString() : (currentTime.getMonth() + 1).toString();
      $scope.curDay = (currentTime.getDate()<10)? '0'+ currentTime.getDate().toString() : currentTime.getDate().toString();
      $scope.curHour = (currentTime.getHours()<10)? '0'+ currentTime.getHours().toString() : currentTime.getHours().toString();
      $scope.curMinute = (currentTime.getMinutes()<10)? '0'+ currentTime.getMinutes().toString() : currentTime.getMinutes().toString();
      $scope.curSecond = (currentTime.getSeconds()<10)? '0'+ currentTime.getSeconds().toString() : currentTime.getSeconds().toString();
     /* $scope.multiMode = false;
      
      $scope.downloadLists = [];
      $scope.dwnString='';

      $scope.ftpPfad = '/NFS/ASBAD04/FTP/MediaControl/GER/DigRetailer/Spotify_API';
      */      
      
  }]);



