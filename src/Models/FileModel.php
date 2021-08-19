<?php

namespace BlazingDocs\Models;

use Http\Discovery\Psr17FactoryDiscovery;

class FileModel {
  public string $id;
  public string $name;
  public string $contentType;
  public string $downloadUrl;
  public $createdAt;
  public $lastModifiedAt;
  public $lastAccessedAt;
  public int $length;

  public function saveTo(&$stream) {
    $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    $stream = $streamFactory->createStreamFromFile($this->downloadUrl);
  }
}
