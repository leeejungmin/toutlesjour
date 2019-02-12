<?php
/*
Warning : it is important to separate the curl connection in 3 differents functions to call API, otherwise the curl connection will not work
*/

$key1 			= '380982263505659';
$key2 			= 'SVMNUTESNEZTBYT';

$apiId 			= 'E3P1H-4KNE7-12HT7-GL6T9-K51DA';
$apiPassword 	= 'GOVZGQYTAGFWZAD';

//$fileName 		= '20180315_200113.jpg';		//=> KO
//$fileName 		= '20180315_200113_cropped.jpg'; 	//=> OK
//$fileName 		= '20180319_173805.jpg';
$fileName 		= '20180319_173805_cropped.jpg';

$userAgent 		= "ocrmobile.cloud";

$serviceUrl 	= 'https://www.ocrmobile.cloud/public/api/';

// get valid token to send treatment / valid during 3600 seconds
$urlAuthenticate 	= $serviceUrl.'authenticate/'.$key1.'::'.$key2;
// post scan or crop
$urlTreat 			= $serviceUrl.'process/{token}';
// get result
$urlResult 			= $serviceUrl.'getResult/{processId}/{token}';

// Get path to file that we are going to recognize
$filePath = "/home/zakaria/Documents/dev/ocrmobile/photos/".$fileName;

// 1. first step authenticate to get valid token for use api
function authenticate(){
global $apiId,$apiPassword,$urlAuthenticate,$urlTreat,$urlResult,$userAgent,$filePath,$time_start;

	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curlHandle, CURLOPT_URL, $urlAuthenticate);
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlHandle, CURLOPT_USERPWD, "$apiId:$apiPassword");
	curl_setopt($curlHandle, CURLOPT_POST, 0);
	curl_setopt($curlHandle, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
	$time_start= microtime(true);
	$response = curl_exec($curlHandle);

	$time_end = microtime(true);
	$execution_time = round(($time_end - $time_start)*1000,0);


	echo $execution_time." ".$urlAuthenticate;

		if($response == FALSE) {
			$errorText = curl_error($curlHandle);
			curl_close($curlHandle);
			die("authenticate error ".$errorText);
		}

	$httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
	curl_close($curlHandle);

		// reponse type JSON {"access":true,"token":"12346578","expires":3600}
		$jsonAuthenticate = json_decode($response,true);

		if($jsonAuthenticate["access"] === true){

				$token 		= $jsonAuthenticate["token"];
				$urlTreat 	= str_replace("{token}",$token,$urlTreat);
				$urlResult 	= str_replace("{token}",$token,$urlResult);

		}else{

			die($jsonAuthenticate["error"]);
		}

}

// 2. Send HTTP POST request and ret JSON response
function sendProcess($processJson){
global $apiId,$apiPassword,$urlTreat,$userAgent,$filePath,$urlResult,$time_start;
// echo " processJson : $processJson <br/>";
	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curlHandle, CURLOPT_URL, $urlTreat);
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlHandle, CURLOPT_USERPWD, "$apiId:$apiPassword");
	curl_setopt($curlHandle, CURLOPT_POST, 1);
	curl_setopt($curlHandle, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);

	$post_array = array();

	  if((version_compare(PHP_VERSION, '5.5') >= 0)) {
		$post_array["my_file"] = new CURLFile($filePath);
	  } else {
		$post_array["my_file"] = "@".$filePath;
	  }

	// send params JSON see documentation datas.pdf
	$post_array["processJson"] = $processJson;// '{"supportId":2,"taxes":[2.1,5.5,10,20],"task":"'.$task.'","country":"FR","fileName":"'.$fileName.'","type":"img","srcCount":1,"useCrop":2,"saveBinary":true}';

	curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $post_array);
	$response = curl_exec($curlHandle);

	  if($response == FALSE) {

		$errorText = curl_error($curlHandle);
		curl_close($curlHandle);
		echo "response false ".$errorText;
		exit;
	  }
	// var_dump($response);
	curl_close($curlHandle);

	$time_start = microtime(true);

	$jsonProcessResponse  = json_decode($response,true);

		if($jsonProcessResponse["access"]){

			if($jsonProcessResponse["access"] === false){
				var_dump($jsonProcessResponse);
				exit;
			}else if(isset($jsonProcessResponse["error"])){

				echo $jsonProcessResponse["error"];
				exit;

			}

			else{
				// get processId for request result
				$processId  = $jsonProcessResponse["processId"];
				$urlResult 	= str_replace("{processId}",$processId,$urlResult);

			}

		}else{

			echo $jsonProcessResponse["error"];
			exit;
		}



}

// 3. get JSON process result
function getResult(){
global $urlResult,$apiId,$apiPassword,$userAgent,$jsonResult;
while(true)
  {
    sleep(1);
    $curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curlHandle, CURLOPT_URL, $urlResult );
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlHandle, CURLOPT_USERPWD, "$apiId:$apiPassword");
    curl_setopt($curlHandle, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
    $response = curl_exec($curlHandle);
    $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
	curl_close($curlHandle);

    $jsonResult = json_decode($response, true);

    if($httpCode != 200) {

		echo "Unexpected response getResult ".$response;
		$nbFalseResponse++;

		if($nbFalseResponse == 2){
			echo $urlTreat."<br/>".$urlResult ;
			// exit;
			break;
		}else{

			sleep(1);

		}


    }

	if($jsonResult["access"]){
echo('<br />******<br />');
		if($jsonResult["status"] == "ok"){
echo('<br />***OK***<br />');
var_dump($response);
			// result treatment
			echo "<pre>".htmlentities($response)."</pre>";

			break;

		}else if($jsonResult["status"] == "failed" || $jsonResult["status"] == "failed_"){

			 die("treatment fail ".implode("<br/>",$jsonResult));

		}else if($jsonResult["status"] == "inProgress"){

			 continue;

		}

	}else{

		die("No access ".$jsonResult["error"]);

	}


  }


}


// we call the 3 functions
echo('<br />Start<br />');
authenticate();
echo('<br />Second<br />');
sendProcess('{"supportId":1,"taxes":[2.1,5.5,10,20],"task":"scan", "language":"FR", "country":"FR", "fileName":"'.$fileName.'", "type":"img", "useCrop":true, "searchAddress":true}');
echo('<br />Third<br />');
$result = getResult();
var_dump($result);

?>
