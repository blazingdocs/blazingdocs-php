<?php
namespace BlazingDocs;

use BlazingDocs\Exceptions\BlazingException;
use BlazingDocs\Models\AccountModel;
use BlazingDocs\Models\ErrorModel;
use BlazingDocs\Models\OperationModel;
use BlazingDocs\Models\TemplateModel;
use BlazingDocs\Models\UsageModel;
use BlazingDocs\Parameters\MergeParameters;
use BlazingDocs\Utils\Constants;
use BlazingDocs\Utils\FormFile;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

interface IBlazingClient {

  public function getAccount();

  public function getTemplates(string $path = null);

  public function getUsage();

  public function mergeWithRelativePath(string $data, string $fileName, MergeParameters $parameters, string $relativePath);

  public function mergeWithGuid(string $data, string $fileName, MergeParameters $parameters, string $guid);

  public function mergeWithFile(string $data, string $fileName, MergeParameters $parameters, FormFile $formFile);

}

class BlazingClient implements IBlazingClient {

  private \Http\Client\Common\HttpMethodsClientInterface $httpClient;
  private $jsonOptions;
  private string $apiKey;
  private Serializer $serializer;

  private string $_baseUrl = "https://api.blazingdocs.com";

  /**
   * @param string $data
   * @param string $fileName
   * @param MergeParameters $parameters
   * @param FormFile $formFile
   * @return OperationModel
   * @throws BlazingException
   * @throws \Http\Client\Exception
   */
  public function mergeWithFile(string $data, string $fileName, MergeParameters $parameters, FormFile $formFile): OperationModel {
    return $this->merge($data, $fileName, $parameters, $formFile);
  }

  /**
   * @param string $data
   * @param string $fileName
   * @param MergeParameters $parameters
   * @param string $guid
   * @return OperationModel
   * @throws BlazingException
   * @throws \Http\Client\Exception
   */
  public function mergeWithGuid(string $data, string $fileName, MergeParameters $parameters, string $guid): OperationModel {
    return $this->merge($data, $fileName, $parameters, $guid);
  }

  /**
   * @param string $data
   * @param string $fileName
   * @param MergeParameters $parameters
   * @param string $relativePath
   * @return OperationModel
   * @throws BlazingException
   * @throws \Http\Client\Exception
   */
  public function mergeWithRelativePath(string $data, string $fileName, MergeParameters $parameters, string $relativePath): OperationModel {
    return $this->merge($data, $fileName, $parameters, $relativePath);
  }

  /**
   * @param string $data
   * @param string $filename
   * @param MergeParameters $parameters
   * @param string|FormFile $template
   * @return OperationModel
   * @throws BlazingException
   * @throws \Http\Client\Exception
   */
  private function merge(string $data, string $filename, MergeParameters $parameters, $template): OperationModel {

    $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    $builder = new MultipartStreamBuilder($streamFactory);

    if (!isset($data) || empty($data)) {
      throw new \InvalidArgumentException('Data is not provided $data');
    }
    $builder->addResource('Data', $data);

    if (!isset($filename) || empty($filename)) {
      throw new \InvalidArgumentException('Output filename is not provided $filename');
    }

    $builder->addResource('OutputName', $filename);

    if ($parameters == null) {
      throw new \InvalidArgumentException('Merge parameters are not provided $parameters');
    }

    if (!in_array($parameters->dataSourceType, [Constants::JSON_TYPE, Constants::CSV_TYPE, Constants::XML_TYPE])) {
      throw new \InvalidArgumentException('Incorrect MergeParameters->dataSourceType');
    }
    $builder->addResource('MergeParameters', json_encode($parameters));

    if ($template === null) {
      throw new \InvalidArgumentException('Template is not provided $template');
    }

    $isGuid = is_string($template) && preg_match(Constants::GUID_REGEX, $template);

    if ($isGuid) {
      $builder->addResource('Template', $template);
    } elseif (is_string($template)) {
      $builder->addResource('Template', trim(str_replace('\\', '/', $template)));
    } else {
      /** @var FormFile $template */
      $builder->addResource('Template', $template->content, ['filename' => $template->name]);
    }

    $endpoint = "{$this->_baseUrl}/operation/merge";

    $multipartStream = $builder->build();
    $boundary = $builder->getBoundary();

    $response = $this->httpClient->post(
      $endpoint,
      ['Content-Type' => 'multipart/form-data; boundary="'.$boundary.'"'],
      $multipartStream
    );

    $raw = $response->getBody()->getContents();
    if ($response->getStatusCode() !== 200) {
      /** @var ErrorModel $error */
      $error = $this->deserializeToJson($raw, ErrorModel::class);
      throw new BlazingException($error->message, $response->getStatusCode());
    }

    return $this->deserializeToJson($raw, OperationModel::class);
  }

  /**
   * @throws \Http\Client\Exception
   * @return AccountModel
   */
  public function getAccount(): AccountModel {
    $endpoint = "{$this->_baseUrl}/account";
    $response = $this->httpClient->get($endpoint);
    $raw = $response->getBody()->getContents();
    if ($response->getStatusCode() !== 200) {
      /** @var ErrorModel $error */
      $error = $this->deserializeToJson($raw, ErrorModel::class);
      throw new BlazingException($error->message, $response->getStatusCode());
    }

    return $this->deserializeToJson($raw, AccountModel::class);
  }

  /**
   * @param string|null $path
   * @return TemplateModel[]
   */
  public function getTemplates(string $path = null) {
    $endpoint = "{$this->_baseUrl}/templates/{$path}";
    $response = $this->httpClient->get($endpoint);
    $raw = $response->getBody()->getContents();
    if ($response->getStatusCode() !== 200) {
      /** @var ErrorModel $error */
      $error = $this->deserializeToJson($raw, ErrorModel::class);
      throw new BlazingException($error->message, $response->getStatusCode());
    }

    return $this->deserializeToJson($raw, TemplateModel::class);
  }

  /**
   * @return UsageModel
   */
  public function getUsage(): UsageModel {
    $endpoint = "{$this->_baseUrl}/usage";
    $response = $this->httpClient->get($endpoint);
    $raw = $response->getBody()->getContents();
    if ($response->getStatusCode() !== 200) {
      /** @var ErrorModel $error */
      $error = $this->deserializeToJson($raw, ErrorModel::class);
      throw new BlazingException($error->message, $response->getStatusCode());
    }

    return $this->deserializeToJson($raw, UsageModel::class);
  }

  private function deserializeToJson($data, $class) {
    return $this->serializer->deserialize($data, $class, Constants::JSON_TYPE);
  }

  public function __construct(string $apiKey) {
    $this->serializer = $this->getSerializeHelper();
    $clientBuilder = new ClientBuilder();
    $clientBuilder->addPlugin(
      new \Http\Client\Common\Plugin\HeaderDefaultsPlugin(["X-API-Key" => $apiKey])
    );
    $this->httpClient = $clientBuilder->getHttpClient();
    $this->apiKey = $apiKey;
  }

  private function getSerializeHelper(): Serializer {
    $reflectionExtractor = new ReflectionExtractor();
    $phpDocExtractor = new PhpDocExtractor();
    $propertyTypeExtractor = new PropertyInfoExtractor(
      [$reflectionExtractor],
      [$phpDocExtractor, $reflectionExtractor],
      [$phpDocExtractor],
      [$reflectionExtractor],
      [$reflectionExtractor]
    );

    $normalizer = new ObjectNormalizer(null, null, null, $propertyTypeExtractor);
    $arrayNormalizer = new ArrayDenormalizer();
    $dateTimeNormalizer = new DateTimeNormalizer();
    $encoders = [new XmlEncoder(), new JsonEncoder()];
    return new Serializer([$arrayNormalizer, $normalizer, $dateTimeNormalizer], $encoders);
  }
}
