<?php
namespace PhpGateway\Gateway;

use Exception;

class Gateway {

    public $path = '';
    public $middlewares = [];

    public function __construct() {
        $this->path = explode('/', $this->get_request_path());
    }

    function get_request_path() {
        if(isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $path = $_SERVER['REQUEST_URI'];
        }
        return $path;
    }
    
    function get_request_headers() {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($key, 5))))] = $value;
            }
        }

        // $headers['Content-Length'] = 0;
        // $headers['Accept'] = 'application/json';
        // $headers['Accept-Encoding'] = 'gzip';
        // dd($headers);
        return $headers;
    }

    function get_request_method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    function get_query_string() {
        return $_SERVER['QUERY_STRING'] ?? null;
    }

    function segment($num) {
        return $this->path[$num+1];
    }

    function get_namespace() {
        return $this->segment(0);
    }

    function request_endpoint_path() {
        return str_replace('/'.$this->get_namespace(), '', $this->get_request_path());
    }

    function get_services() {
        $services = config('services');

        if($services == null) {
            throw new Exception('No services found in config/services.php');
        } else {
            return $services;
        }
    }

    function get_service() {
        try {
            $services  = $this->get_services();
            $path      = $this->path;
            $namespace = $this->get_namespace();
    
    
            foreach($services['endpoints'] as $service) {
                if($service['namespace'] == $namespace) {
                    return $service;
                }
            }
    
            throw new Exception('service not found', 404);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function set_response_headers($headers) {
        foreach($headers as $key => $value) {
            $value = is_array($value) ? implode(',', $value) : $value;
            header($key.': '.$value);
        }
    }

    function response($body, $status = 200) {

        http_response_code($status);
    
        $toArray   = is_array($body) ? $body : json_decode($body, true);
    
        if($toArray == null) {
            header('Content-Length: ' . strlen($body));
            echo $body;
        } else {
            $json = json_encode($toArray, JSON_PRETTY_PRINT);
    
            header('Content-Length: ' . strlen($json));
            header('Content-Type: application/json');
            // $output = str_replace('\\/', '/', $json);

            echo $json;
        }
        die;
    }

    function middleware(callable $middleware) {
        $this->middlewares[] = $middleware;

    }

}