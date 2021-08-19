<?php

namespace BlazingDocs\Models;

use Http\Discovery\Psr17FactoryDiscovery;

class OperationModel {
  public string $id;
  public $type;
  public int $pageCount;
  public int $elapsedMilliseconds;
  public string $remoteIpAddress;

  /**
   * @var FileModel[]
   */
  public array $files;

  public function saveTo(&$stream) {
    if (!$this->files) {
      throw new \Exception('File not found');
    }
    $file = $this->files[0];
    $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    $stream = $streamFactory->createStreamFromFile($file->downloadUrl);
  }
}
