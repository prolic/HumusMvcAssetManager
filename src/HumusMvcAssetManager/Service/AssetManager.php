<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace HumusMvcAssetManager\Service;

use Assetic\Asset\AssetInterface;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetCacheManagerAwareInterface;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\AssetFilterManagerAwareInterface;
use HumusMvcAssetManager\Exception;
use Zend_Controller_Request_Abstract as Request;
use Zend_Controller_Request_Http as HttpRequest;
use Zend_Controller_Response_Abstract as Response;

/**
 * @category    Humus
 * @package     HumusMvcAssetManager
 * @subpackage  Service
 */
class AssetManager implements
    AssetFilterManagerAwareInterface,
    AssetCacheManagerAwareInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var AssetFilterManager The AssetFilterManager service.
     */
    protected $filterManager;

    /**
     * @var AssetCacheManager The AssetCacheManager service.
     */
    protected $cacheManager;

    /**
     * @var AssetInterface The asset
     */
    protected $asset;

    /**
     * @var string The requested path
     */
    protected $path;

    /**
     * @var array The asset_manager configuration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param ResolverInterface $resolver
     * @param array             $config
     *
     * @return AssetManager
     */
    public function __construct($resolver, $config = array())
    {
        $this->setResolver($resolver);
        $this->setConfig($config);
    }

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

    /**
     * Set the config
     *
     * @param array $config
     */
    protected function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Set the resolver to use in the asset manager
     *
     * @param ResolverInterface $resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the resolver used by the asset manager
     *
     * @return ResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Set the AssetFilterManager.
     *
     * @param AssetFilterManager $filterManager
     */
    public function setAssetFilterManager(AssetFilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * Get the AssetFilterManager
     *
     * @return AssetFilterManager
     */
    public function getAssetFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * Set the AssetCacheManager.
     *
     * @param AssetCacheManager $filterManager
     */
    public function setAssetCacheManager(AssetCacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Get the AssetCacheManager
     *
     * @return AssetCacheManager
     */
    public function getAssetCacheManager()
    {
        return $this->cacheManager;
    }

}

