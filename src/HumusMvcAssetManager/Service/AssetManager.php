<?php

namespace HumusMvcAssetManager\Service;

use Assetic\Asset\AssetInterface;
use AssetManager\Service\AssetManager as BaseAssetManager;
use HumusMvcAssetManager\Exception;
use Zend_Controller_Request_Abstract as Request;
use Zend_Controller_Request_Http as HttpRequest;
use Zend_Controller_Response_Abstract as Response;

/**
 * @category    Humus
 * @package     HumusMvcAssetManager
 * @subpackage  Service
 */
class AssetManager extends BaseAssetManager
{

    /**
     * Set the asset on the response, including headers and content.
     *
     * @param    Response $response
     * @return   Response
     * @throws   Exception\RuntimeException
     */
    public function setAssetOnResponse(Response $response)
    {
        if (!$this->asset instanceof AssetInterface) {
            throw new Exception\RuntimeException(
                'Unable to set asset on response. Request has not been resolved to an asset.'
            );
        }

        // @todo: Create Asset wrapper for mimetypes
        if (empty($this->asset->mimetype)) {
            throw new Exception\RuntimeException('Expected property "mimetype" on asset.');
        }

        $this->getAssetFilterManager()->setFilters($this->path, $this->asset);

        $this->asset    = $this->getAssetCacheManager()->setCache($this->path, $this->asset);
        $mimeType       = $this->asset->mimetype;
        $assetContents  = $this->asset->dump();

        // @codeCoverageIgnoreStart
        if (function_exists('mb_strlen')) {
            $contentLength = mb_strlen($assetContents, '8bit');
        } else {
            $contentLength = strlen($assetContents);
        }
        // @codeCoverageIgnoreEnd

        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Type', $mimeType);
        $response->setHeader('Content-Length', $contentLength);

        $response->setBody($assetContents);

        return $response;
    }

    /**
     * Resolve the request to a file.
     *
     * @param Request $request
     *
     * @return mixed false when not found, AssetInterface when resolved.
     */
    protected function resolve(Request $request)
    {
        if (!$request instanceof HttpRequest) {
            return false;
        }

        $path = substr($request->getRequestUri(), strlen($request->getBasePath()) + 1);
        $this->path = $path;

        $asset      = $this->getResolver()->resolve($path);

        if (!$asset instanceof AssetInterface) {
            return false;
        }

        return $asset;
    }

    /**
     * Check if the request resolves to an asset.
     *
     * @param    Request $request
     * @return   boolean
     */
    public function resolvesToAsset(Request $request)
    {
        if (null === $this->asset) {
            $this->asset = $this->resolve($request);
        }

        return (bool)$this->asset;
    }
}
