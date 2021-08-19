<?php

use BlazingDocs\BlazingClient;
use BlazingDocs\Parameters\MergeParameters;
use BlazingDocs\Utils\Constants;
use BlazingDocs\Utils\FormFile;
use Http\Discovery\Psr17FactoryDiscovery;

require_once __DIR__ . './../vendor/autoload.php';

$blazingClient = new BlazingClient('YOUR-API-KEY');
$mergeParam = new MergeParameters(Constants::JSON_TYPE);

mergeSingle($blazingClient, $mergeParam);
mergeSingleXml($blazingClient, $mergeParam);
mergeArray($blazingClient, $mergeParam);
mergeRelativePath($blazingClient, $mergeParam);
mergeGuid($blazingClient, $mergeParam);
getAccount($blazingClient);
getTemplates($blazingClient);
getUsage($blazingClient);

function mergeRelativePath(BlazingClient $blazingClient, MergeParameters $mergeParam) {
  $jsonFile = file_get_contents('PO-Template.json');
  $relativePath = 'YOUR-RELATIVE-PATH';

  $data = $blazingClient->mergeWithGuid($jsonFile, 'output.pdf', $mergeParam, $relativePath);
  var_dump($data);
}

function mergeGuid(BlazingClient $blazingClient, MergeParameters $mergeParam) {
  $jsonFile = file_get_contents('PO-Template.json');
  $guid = 'YOUR_GUID';

  $data = $blazingClient->mergeWithGuid($jsonFile, 'output.pdf', $mergeParam, $guid);
  var_dump($data);
}

function mergeSingle(BlazingClient $blazingClient, MergeParameters $mergeParam) {
  $jsonFile = file_get_contents('PO-Template.json');
  $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

  $content = $streamFactory->createStreamFromFile('PO-Template.docx', 'r');
  $template = new FormFile('PO-Template.docx', $content);

  $data = $blazingClient->mergeWithFile($jsonFile, 'output.pdf', $mergeParam, $template);
  var_dump($data);
}

function mergeSingleXml(BlazingClient $blazingClient, MergeParameters $mergeParam) {
  $xmlFile = file_get_contents('PO-Template.xml');
  $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

  $mergeParam->dataSourceType = Constants::XML_TYPE;

  $content = $streamFactory->createStreamFromFile('PO-Template.docx', 'r');
  $template = new FormFile('PO-Template.docx', $content);

  $data = $blazingClient->mergeWithFile($xmlFile, 'output.pdf', $mergeParam, $template);
  var_dump($data);
}

function mergeArray(BlazingClient $blazingClient, MergeParameters $mergeParam) {
  $jsonFile = file_get_contents('PO-Template-2.json');
  $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

  $mergeParam->strict = true;
  $mergeParam->sequence = true;

  $content = $streamFactory->createStreamFromFile('PO-Template-2.docx', 'r');
  $template = new FormFile('PO-Template-2.docx', $content);

  $data = $blazingClient->mergeWithFile($jsonFile, 'output.pdf', $mergeParam, $template);
  var_dump($data);
}

function getAccount(BlazingClient $blazingClient) {
  $data = $blazingClient->getAccount();
  var_dump($data);
}

function getTemplates(BlazingClient $blazingClient) {
  $data = $blazingClient->getTemplates();
  var_dump($data);
}

function getUsage(BlazingClient $blazingClient) {
  $data = $blazingClient->getUsage();
  var_dump($data);
}

