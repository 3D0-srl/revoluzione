<?php

require __DIR__ . '/../../vendor/autoload.php';

use Sample\AuthorizeIntentExamples\CreateOrder;
use Sample\AuthorizeIntentExamples\AuthorizeOrder;
use Sample\AuthorizeIntentExamples\CaptureOrder;
use Sample\RefundOrder;

$order = CreateOrder::createOrder();

echo "<pre>";
print "Creating Order...\n";
$orderId = "";
if ($order->statusCode == 201)
{
    $orderId = $order->result->id;
    print "Links:\n";
    for ($i = 0; $i < count($order->result->links); ++$i)
    {
        $link = $order->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
    print "Created Successfully\n";
    print "Copy approve link and paste it in browser. Login with buyer account and follow the instructions.\nOnce approved hit enter...\n";
}
else {
    exit(1);
}

$handle = fopen ("php://stdin","r");
$line = fgets($handle);
fclose($handle);

print "Authorizing Order...\n";
$response = AuthorizeOrder::authorizeOrder($orderId);
$authId = "";

if ($response->statusCode == 201)
{
    print "Authorized Successfully\n";
    $authId = $response->result->purchase_units[0]->payments->authorizations[0]->id;
}
else {
    exit(1);
}

print "\nCapturing Order...\n";
$response = CaptureOrder::captureOrder($authId);
if ($response->statusCode == 201)
{
    print "Captured Successfully\n";
    print "Status Code: {$response->statusCode}\n";
    print "Status: {$response->result->status}\n";
    $captureId = $response->result->id;
    print "Capture ID: {$captureId}\n";
    print "Links:\n";
    for ($i = 0; $i < count($response->result->links); ++$i){
        $link = $response->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
}
else {
    exit(1);
}

print "\nRefunding Order...\n";
$response = RefundOrder::refundOrder($captureId);
if ($response->statusCode == 201)
{
    print "Refunded Successfully\n";
    print "Status Code: {$response->statusCode}\n";
    print "Status: {$response->result->status}\n";
    print "Refund ID: {$response->result->id}\n";
    print "Links:\n";
    for ($i = 0; $i < count($response->result->links); ++$i){
        $link = $response->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
}
else {
    exit(1);
}
