<?php

include_once("_auth.php");

    //function to make cURL request
    function call($method, $parameters, $url)
    {
        ob_start();
        $curl_request = curl_init();

        curl_setopt($curl_request, CURLOPT_URL, $url);
        curl_setopt($curl_request, CURLOPT_POST, 1);
        curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl_request, CURLOPT_HEADER, 1);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

        $jsonEncodedData = json_encode($parameters);

        $post = array(
             "method" => $method,
             "input_type" => "JSON",
             "response_type" => "JSON",
             "rest_data" => $jsonEncodedData
        );

        curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($curl_request);
        curl_close($curl_request);

        $result = explode("\r\n\r\n", $result, 2);
        $response = json_decode($result[1]);
        ob_end_flush();

        return $response;
    }

    //login -------------------------------------------------

    $login_parameters = array(
         "user_auth"=>array(
              "user_name"=>$username,
              "password"=>md5($password),
              "version"=>"1"
         ),
         "application_name"=>"RestTest",
         "name_value_list"=>array(),
    );

    $login_result = call("login", $login_parameters, $url);


    echo "<pre>";
    print_r($login_result);
    echo "</pre>";

    //get session id
    $session_id = $login_result->id;

    //retrieve records ----------------------------------------

    $get_entry_list_parameters = array(

         //session id
         'session' => $session_id,

         //The name of the module from which to retrieve records
         'module_name' => "Opportunities",

        'ids' => array(
             'c6665459-d86b-f0b0-8f5b-51d29bd720d4'
         ),


         //The record offset from which to start.
         'offset' => "0",

         //Optional. The list of fields to be returned in the results
         'select_fields' => array(
			 'name_generic_c',
			 'description',
			 'listing_sales_c',
              'name',
              'sales_stage',
              'listing_featured_c',              
         ),

         //A list of link names and the fields to be returned for each link name
         'link_name_to_fields_array' => array(
         ),

         //The maximum number of results to return.
         'max_results' => '1',

         //To exclude deleted records
         'deleted' => 0,

         //If only records marked as favorites should be returned.
         'Favorites' => false,

    );

    $get_entry_list_result = call("get_entries", $get_entry_list_parameters, $url);

    echo "<pre>";
    print_r($get_entry_list_result);
    echo "</pre>";
foreach($get_entry_list_result->entry_list AS $obj){
	echo $obj->name_value_list->name_generic_c->value."<br>";
	echo $obj->name_value_list->sales_stage->value."<br>";
	echo $obj->name_value_list->description->value."<br>";
	echo $obj->name_value_list->listing_sales_c->value."<br>";
	echo $obj->name_value_list->listing_featured_c->value."<br>";
	echo "<br>";
}
?>
