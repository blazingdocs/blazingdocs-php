# BlazingDocs PHP client
High-performance document generation API. Generate documents and reports from СSV, JSON, XML with 99,9% uptime and 24/7 monitoring.

## Installation

Run this line from Terminal:

```
composer require blazingdocs/blazingdocs-php
```

## Integration basics

### Setup

You can get your API Key at https://app.blazingdocs.com

```php
$client = new \BlazingDocs\BlazingClient('API-KEY');
```

### Getting account info

```php
$account = $client->getAccount();
```

### Getting merge templates list

```php
$templates = $client->getTemplates();
```

### Getting usage info

```php
$usage = $client->getUsage();
```

### Executing merge

```php
$client = new \BlazingDocs\BlazingClient('API-KEY');
$parameters = new \BlazingDocs\Parameters\MergeParameters();

$jsonFile = file_get_contents('PO-Template.json');
$streamFactory = Psr17FactoryDiscovery::findStreamFactory();

$content = $streamFactory->createStreamFromFile('PO-Template.docx', 'r');
$template = new \BlazingDocs\Utils\FormFile('PO-Template.docx', $content);

$data = $client->mergeWithFile($jsonFile, 'output.pdf', $parameters, $template);
```

## Documentation

See more details here https://docs.blazingdocs.com
