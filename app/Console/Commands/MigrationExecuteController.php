<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrationExecuteController extends Command
{
    // bamboo hr details
    protected $companySubDomain = "scopicsoftware";
    protected $bambooBaseUrl="https://api.bamboohr.com/api/gateway.php/";
    protected $postParams;
    protected $bambooApiKey = "beb339373b935137c05562b68190377f5328c600";

    // zoho details
    private $zohoToken = "a3ee87edae89a0d3e343148e33c11e03"; //need change to real
    private $zohoBaseUrl = "https://people.zoho.com/people/api/";


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bamboo_to_zoho:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command synchronizes data from bamboo api to zoho people api';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo PHP_EOL . '***********       STARTED EXECUTING BAMBOO TO ZOHO PEOPLE MIGRATION       *************' . PHP_EOL . PHP_EOL;

        // get all employees list
        $response = $this->createBambooRequest($this->bambooBaseUrl . $this->companySubDomain . '/v1/employees/directory', "GET");
        $response = json_decode($response, true);
        $employees =  $response['employees'];

        // loop through employees object

        foreach($employees as $employee){

            // go get other details

            // id 41169, Alban Afmeti




        }


        echo json_encode($employees[0]);die;
    }

    /**
     * method to perform curl request to Bamboo API
     *
     * @param $url
     * @param $method
     * @return mixed|string
     */
    private function createBambooRequest($url, $method)
    {
        $headers = [
            'Accept: application/json',
            'Content-type: application/json'
        ];
        $http=curl_init();
        curl_setopt($http, CURLOPT_URL, $url);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, $method );
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($http, CURLOPT_POSTFIELDS, $this->postParams);
        curl_setopt($http, CURLOPT_HEADER, false );
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
        curl_setopt($http, CURLOPT_USERPWD, $this->bambooApiKey . ':x');

        $response=curl_exec($http);
        $info = curl_getinfo($http);

        if (curl_errno($http)) {
            $response = '<?xml version="1.0" encoding="UTF-8"?><response><errorResponse>'.'Request Error: ' . curl_error($http) . '</errorResponse></response>';
            curl_close($http);
            return $response;
        } else {
            if($info['http_code'] == 401){
                echo 'unauthorized';
                //unauthorized BambooHR request, send notification to admin about invalid API key
//                $notifier = new Notifier();
//                $notifier->unauthorizedBamboohr();
            }
            curl_close($http);
            return trim($response);
        }
    }

    /**
     * method to perform curl request to Zoho People API
     *
     * @param $url
     * @param $method
     * @return mixed|string
     */
    private function createZohoRequest($url, $method, $params)
    {
        $header = array(
            'Accept: application/json',
            'X-Scopic-Tool-ID: Scopic_RMT',
        );

        $ch = curl_init();

        if ($method == 'POST' || $method == 'PUT') {
            $url = $this->createRequestUrl($url, null);
            $data = $this->createPostData($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $url = $this->createRequestUrl($url, $params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            $response = array(
                'errors' => 'Request Error: ' . curl_error($ch),
            );

            curl_close($ch);
            return $response;
        }
        $response = json_decode($output, true);
        curl_close($ch);

        return $response;
    }

    private function createRequestUrl($url, $params)
    {
        if ($params === null) {
            return $url = $this->zohoBaseUrl . trim($url);
        }
        $url = $this->zohoBaseUrl . trim($url) . '?';
        $url .= ('authtoken=' . $this->zohoToken);
        if ($params && is_array($params)) {
            foreach ($params as $key => $value) {
                $url .= ('&' . $key . '=' . $value);
            }
        }

        return $url;
    }

    private function createPostData($params)
    {
        $params['authtoken'] = $this->zohoToken;
        $data = '';
        foreach ($params as $key => $value) {
            $data .= ($key . '=' . $value .'&');
        }
        $data = trim($data, '&');

        return $data;
    }
}
