<?php
namespace BlazingDocs\Parameters;

use BlazingDocs\Utils\Constants;

class MergeParameters {
  public string $dataSourceName = 'data';
  public string $dataSourceType = Constants::JSON_TYPE;
  public bool $sequence = false;
  public bool $parseColumns = false;
  public bool $strict = false;

  public function __construct() {
    $args = func_get_args();
    if (!$args) {
      return;
    }
    $dataSourceType = $args[0];
    if ($dataSourceType) {
      $this->dataSourceType = $dataSourceType;
    }
  }
}
