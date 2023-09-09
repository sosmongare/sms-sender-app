<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Models\SmsReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SmsController extends Controller
{
    private function checkSmsBalance()
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

                // Return the SMS balance
                return $data->credit;
            } else {
                return false; // Return false to indicate an error
            }
        } catch (\Exception $e) {
            // Handle exceptions, such as network issues or invalid JSON response
            return false; // Return false to indicate an error
        }
    }

    public function index()
    {
        // Call the function to check SMS balance
        $balance = $this->checkSmsBalance();

        if ($balance === false) {
            return view('index')->with('error', 'Failed to fetch SMS balance');
        }

        return view('index', compact('balance'));

    }


    public function sendSms(Request $request)
    {
        $client = new Client();
        $sendSmsUrl = 'https://quicksms.advantasms.com/api/services/sendsms/?';

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
            // Send the SMS
            $response = $client->post($sendSmsUrl, [
                'headers' => $headers,
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                // Handle successful response
                $decodedResponse = json_decode($response->getBody(), true);
                  // Check if the 'responses' array exists and is not empty
                  if (isset($decodedResponse['responses']) && is_array($decodedResponse['responses']) && !empty($decodedResponse['responses'])) {
                    // Access the first element in the 'responses' array
                    $firstResponse = $decodedResponse['responses'][0];

                    // Check if the correct keys exist in the first response element
                    if (isset($firstResponse['messageid']) && isset($firstResponse['mobile'])) {
                        $messageId = $firstResponse['messageid'];
                        $mobileNumber = $firstResponse['mobile'];

                        // Fetch and store the delivery receipt
                        $this->getDeliveryReport($messageId, $mobileNumber);

                        return redirect()->back()->with('success', 'SMS sent successfully!');
                    }
                }
                    // Handle the case where keys are not found in the response
                return redirect()->back()->with('error', 'Failed to retrieve message ID and mobile from the SMS response');
                
            } else {
                // Handle error response
                return redirect()->back()->with('error', 'Failed to send SMS');
            }
        } catch (\Exception $e) {
            // Handle exceptions, such as network issues or invalid JSON response
            return redirect()->back()->with('error', 'An error occurred while sending the SMS: ' . $e->getMessage());
        }
    }

    private function getDeliveryReport($messageId, $mobileNumber)
    {
        // Make a request to the getdlr API to fetch the receipt details
        $client = new Client();
        $getDlrUrl = 'https://quicksms.advantasms.com/api/services/getdlr/';
        $headers = [
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $client->post($getDlrUrl, [
                'headers' => $headers,
                'json' => [
                    'apikey' => Config::get('api.api_key'),
                    'partnerID' => Config::get('api.partner_id'),
                    'messageID' => $messageId,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                // Handle successful getdlr response
                $getDlrData = json_decode($response->getBody(), true);

                // Store the delivery receipt details in the database
                SmsReceipt::create([
                  'mobilenumber' => $mobileNumber,
                  'response_code' => $getDlrData['response-code'],
                  'response_description' => $getDlrData['response-description'],
                  'message_id' => $messageId,
                  'delivery_status' => $getDlrData['delivery-status'],
                  'delivery_description' => $getDlrData['delivery-description'],
                  'delivery_tat' => $getDlrData['delivery-tat'],
                  'delivery_networkid' => $getDlrData['delivery-networkid'],
                  'delivery_time' => $getDlrData['delivery-time'],
              ]);
            }  else {
              // Handle error response from the getdlr API
              // Log the error or perform other error-handling actions
              Log::error('Failed to fetch delivery receipt for message ID: ' . $messageId);
              // Return an error response to the caller with an appropriate HTTP status code and message
              return response()->json(['error' => 'Failed to fetch delivery receipt'], 500);
          }
        } catch (\Exception $e) {
          // Handle exceptions, such as network issues or invalid JSON response
          // Log the exception or perform appropriate error-handling actions
          Log::error('Exception occurred while fetching delivery receipt: ' . $e->getMessage());
          // Return an error response to the caller with an appropriate HTTP status code and message
          return response()->json(['error' => 'An error occurred while fetching the delivery receipt'], 500);
    
        }
    }
    

}
