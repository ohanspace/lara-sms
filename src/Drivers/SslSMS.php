<?php
namespace Ohanspace\Sms\Drivers;


use Ohanspace\Sms\OutgoingMessage;
use GuzzleHttp\Client;

class SslSMS extends AbstractSMS implements DriverInterface
{


    /**
     * The API's URL.
     *
     * @var string
     */
    protected $apiBase = 'http://sms.sslwireless.com';
    /**
     * Constructs a new instance.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        //echo 'constructing sslSMS';
        $this->client = $client;
    }

    /**
     * Sends a SMS message.
     *
     * @param \ohanspace\sms\OutgoingMessage $message
     * @return void
     */
    public function send(OutgoingMessage $message)
    {

        $composedMessage = $message->composeMessage();
        $data = [
            'phones' => $message->getTo(),
            'message' => $composedMessage
        ];

        $this->buildCall('/pushapi/dynamic/server.php');
        $this->buildBody($data);
        //dd($this->getBody());
        $this->postRequest();
        $this->processDeliveryReport();

        return $this;
    }


    /**
     * Creates and sends a POST request to the requested URL.
     *
     * @return mixed
     * @throws \Exception
     */
    public function postRequest()
    {
        $data = $this->getBody();
        $sms = [];
        foreach ($data['phones'] as $index => $phone) {
            $sms[$index] = [
                '0' => $phone,
                '1' => $data['message'],
                '2' => rand(10000, 99999)
            ];
        }

        $form_params = [
            'user' => $data['user'],
            'pass' => $data['pass'],
            'sid' => $data['sid'],
            'sms' => $sms
        ];
        //dd($form_params);

        $response = $this->client->request('POST', $this->buildUrl(), [
            'form_params' => $form_params
        ]);

        //dd($response->getBody()->getContents());

        if ($response->getStatusCode() != 201 && $response->getStatusCode() != 200) {
            throw new \Exception('Unable to request from API.');
        }

        $this->setResponse($response) ;
    }

    /*
     * Set the response
     *
     */
    public function setResponse($responseData){

        return $this->response = $responseData;
    }
    /*
     *   get content from ressponse
     */
    public function getResponseContent(){
        $response = $this->response;
        $body = $response->getBody();
        //dump($body);
        $content = $body->getContents();
        //dump($content);
        return $content;
    }
    /*
     *  check if a sms is sent successfully
     */
    public function processDeliveryReport(){

        $xml = simplexml_load_string($this->getResponseContent(), "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        //dd($array);
        if(isset($array['SMSINFO'])){
            //dump($array['SMSINFO']);
            if(isset($array['SMSINFO']['REFERENCEID']) OR isset($array['SMSINFO'][0]['REFERENCEID'])){
                //dump('delivered');
                $this->setDeliveryReport(true);
            }
        }



    }

    /*
     *  set delivery status
     */
    public function setDeliveryReport($status = false){

        $this->delivered = $status;
    }

    /*
     *  get delivery report
     */
    public function delivered(){
        return $this->delivered;
    }



}
