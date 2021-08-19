<?php
namespace BlazingDocs\Utils;

use Psr\Http\Message\StreamInterface;

class FormFile {
  public string $name;
  public StreamInterface $contentType;
  /**
   * @var resource
   */
  public $content;

  public function __construct() {
    $args = func_get_args();
    $name = $args[0];
    if ($name) {
      $this->name = $name;
    }
    $content = $args[1];
    if ($content) {
      $this->content = $content;
    }
  }
}
