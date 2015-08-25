 var tabApp = angular.module('tabbedApp', []);

  tabApp.controller('tabController', ['$scope', function($scope) {
  	
  	  //constant var
  	  $scope.display_none='none';
  	  $scope.display_block='block';
  	  $scope.multiModechkbox_checked = 'checked';

  	  $scope.error_status='has-error';

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
     
     /* $scope.multiMode = false;
      
      $scope.downloadLists = [];
      $scope.dwnString='';

      $scope.ftpPfad = '/NFS/ASBAD04/FTP/MediaControl/GER/DigRetailer/Spotify_API';
      */
      $scope.multiModeClick = function(){      	
      	if($scope.multiMode){
      		jQuery('#multiModeCheckBox').prop('checked', false);
      	}else{
      		jQuery('#multiModeCheckBox').prop('checked', true);
      	}      	
      }

      $scope.itunesmultiModeClick = function(){
        if($scope.itunesmultiMode){
          jQuery('#itunesmultiModeCheckBox').prop('checked', false);
        }else{
          jQuery('#itunesmultiModeCheckBox').prop('checked', true);
        } 
      }

      $scope.add_dwnitem = function(){
      	var tmpvar = $scope.varCountry + $scope.varYear.toString() + $scope.varMonth.toString() + $scope.varDay.toString();
      	if(jQuery.inArray(tmpvar, $scope.downloadLists) != -1){
      		alert('item exists!!!');
      	}else{
	      	$scope.downloadLists.push(tmpvar);
	    }
	    $scope.dwnString=$scope.downloadLists.join(',');
      }

      $scope.itunes_add_dwnitem = function(){
        var tmpvar = $scope.getDwlFilename($scope.varitunesGeo) + $scope.varitunesYear.toString() + $scope.varitunesMonth.toString() + $scope.varitunesDay.toString();
        if(jQuery.inArray(tmpvar, $scope.itunesdownloadLists) != -1){
          alert('item exists!!!');
        }else{
          $scope.itunesdownloadLists.push(tmpvar);
        }
        $scope.itunesdwnString=$scope.itunesdownloadLists.join(',');
      }

      $scope.remove_dwnitem = function(){
      	$scope.downloadLists = [];
      	$scope.dwnString='';
      }

      $scope.itunesremove_dwnitem = function(){
        $scope.itunesdownloadLists = [];
        $scope.itunesdwnString='';
      }
      $scope.getDwlFilename = function(val){
        switch(val){
          case 'EUR':
            return 'eur_master_trans_';
            break;
          case 'APAC':
            return 'apac_master_trans_';
            break;
          case 'eur-applemusic':
            return 'streaming_eur_master_';
            break;
          case 'apac-applemusic':
            return 'streaming_apac_master_';
            break;
          default:
            return val.toLowerCase();
          break;
        }
      }
      
  }]);



