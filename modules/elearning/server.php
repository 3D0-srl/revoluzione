<?php
require 'vendor/autoload.php';

use React\Http\Server;
use React\Http\Message\Response;
use React\EventLoop\Factory;
use Psr\Http\Message\ServerRequestInterface;

// init the event loop
$loop = Factory::create();


$filesystem = \React\Filesystem\Filesystem::create($loop);


$loggingMiddleware2 = function(ServerRequestInterface $request, callable $next) {
    echo date('Y-m-d H:i:s') . ' ' . $request->getMethod() . ' ' . $request->getUri() . PHP_EOL;
    return $next($request);
};

$loggingMiddleware = function(ServerRequestInterface $request, callable $next) {
    echo date('Y-m-d H:i:s') . ' ' . $request->getMethod() . ' ' . $request->getUri() . PHP_EOL;
    return $next($request);
};


$streamingMiddleware = function (ServerRequestInterface $request) use ($filesystem) {
    $params = $request->getQueryParams();
    $file = $params['video'] ?? '';
    $token = $params['token'] ?? '';
    if (empty($token)) {
        return new Response(200, ['Content-Type' => 'text/plain'], 'Video streaming server');
    }
    $filePathtoken = __DIR__ . DIRECTORY_SEPARATOR . 'tokens' . DIRECTORY_SEPARATOR . basename($token);
  
    if (!file_exists($filePathtoken)) {
        echo date('Y-m-d H:i:s') . ' token expired' . PHP_EOL;
        return new Response(404, ['Content-Type' => 'text/plain'], "Token expired");
    }
    //unlink($filePathtoken);

    if (empty($file)) {
        return new Response(200, ['Content-Type' => 'text/plain'], 'Video streaming server');
    }

    $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($file);
    @$fileStream = fopen($filePath, 'r');
    if (!$fileStream) {
        return new Response(404, ['Content-Type' => 'text/plain'], "Video $file doesn't exist on server.");
    }

    $file = $filesystem->file($filePath);

    return $file->open('r')->then(
        function (\React\Filesystem\Stream\ReadableStream $stream) {
            return new Response(200, ['Content-Type' => 'video/mp4'], $stream);
        },
        function (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    );
};


$server = new Server($loggingMiddleware,$streamingMiddleware);

$socket = new \React\Socket\Server('0.0.0.0:8000', $loop);
$server->listen($socket);
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . "\n";

// run the application
$loop->run();

?>