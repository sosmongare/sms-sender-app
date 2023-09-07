<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SmsController extends Controller
{
    public function index()
    {
          // Get the API key and partner ID from your configuration or .env file
        $apiKey = config('api.api_key');
        $partnerID = config('api.partner_id');

        // API endpoint URL
        $url = 'https://quicksms.advantasms.com/api/services/getbalance/';

        // Create a Guzzle HTTP client
        $client = new Client();

        try {
            // Send a GET request to the API with the API key and partner ID
            $response = $client->get($url, [
                'query' => [
                    'apikey' => $apiKey,
                    'partnerID' => $partnerID,
                ],
            ]);

            // Check the response status code
            if ($response->getStatusCode() == 200) {
                // Parse the JSON response
                $data = json_decode($response->getBody());

                // Display the SMS balance in the view
                return view('index', ['balance' => $data->credit]);
            } else {
                return view('index')->with('error', 'Failed to fetch SMS balance');
            }
        } catch (\Exception $e) {
            // Handle exceptions, such as network issues or invalid JSON response
            return view('index')->with('error', 'An error occurred: ' . $e->getMessage());
        }

    }

    public function sendSms(Request $request)
    {
        $client = new Client();
        $url = 'https://quicksms.advantasms.com/api/services/sendsms/';
    
        $payload = [
            'apikey' => Config::get('api.api_key'),
            'partnerID' => Config::get('api.partner_id'),
            'message' => $request->message,
            'shortcode' => Config::get('api.shortcode'),
            'mobile' => $request->mobilenumber,
        ];
    
        $headers = [
            'Content-Type' => 'application/json',
        ];
    
        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $payload,
            ]);
    
            $statusCode = $response->getStatusCode();
    
            if ($statusCode == 200) {
                // Handle successful response
                return redirect()->back()->with('success', 'SMS sent successfully!');
            } else {
                // Handle error response
                return redirect()->back()->with('error', 'Failed to send SMS');
            }
        } catch (\Exception $e) {
            // Handle exceptions, such as network issues or invalid JSON response
            return redirect()->back()->with('error', 'An error occurred while sending the SMS: ' . $e->getMessage());
        }
    }
    

}
