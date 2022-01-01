<?php
namespace PhpGateway;

use PhpGateway\Gateway\Gateway;

class GatewayExecute extends Gateway
{

    protected $default_response = [];

    function __construct($config = '')
    {
        parent::__construct($config);

        if (!empty($config['default_response'])) {
            $this->default_response = $config['default_response'];
        } else {
            $this->default_response = [
                "status" => "ok",
            ];
        }
    }

    function set_default_response($default)
    {
        $this->default_response = $default;
    }

    function run()
    {
        try {

            $method       = $this->get_request_method();
            $headers      = $this->get_request_headers();
            $query_string = $this->get_query_string();
            $request_path = $this->get_request_path();

            foreach($this->middlewares as $middleware) {
                $middleware(headers: $headers);
            }
            
            if ($request_path == '/') {
                return $this->response($this->default_response);
            }
            
            $service       = $this->get_service();
    
            $request_endpoint_path = $service['endpoint'] . $this->request_endpoint_path();

            $client = new \GuzzleHttp\Client(['verify' => false]);

            $options = [];

            $options['headers'] = $headers;
            $options['query'] = $query_string;

            $client = $client->request($method, $request_endpoint_path);

            $response_body    = $client->getBody();

            $this->set_response_headers($client->getHeaders());


            return $this->response($response_body->getContents());
        } catch (\GuzzleHttp\Exception\RequestException $th) {

            return $this->response([
                "code" => $th->getCode(),
                "status" => "error",
                "message" => $th->getMessage()
            ], $th->getCode());
        } catch (\Exception $th) {

            return $this->response([
                "code"   => $th->getCode(),
                "status" => "error",
                "message" => $th->getMessage()
            ], $th->getCode());
        }
    }
}
