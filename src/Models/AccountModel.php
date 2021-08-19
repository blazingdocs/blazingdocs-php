<?php
namespace BlazingDocs\Models;

class AccountModel {
  public string $id;
  public string $name;
  public PlanModel $plan;
  public string $apiKey;
  public $obsoleteApiKey;
  public $createdAt;
  public $lastSyncedAt;
  public $updatedAt;
  public bool $isDisabled;
}
