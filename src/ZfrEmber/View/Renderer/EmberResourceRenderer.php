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

namespace ZfrEmber\View\Renderer;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use Zend\View\HelperPluginManager as ViewHelperPluginManager;

/**
 * Renderer that output Ember-Data compliant data
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class EmberResourceRenderer
{
    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * @var HydratorPluginManager
     */
    protected $hydratorManager;

    /**
     * @var ViewHelperPluginManager
     */
    protected $viewHelperManager;

    /**
     * @param ClassMetadataFactory    $classMetadataFactory
     * @param HydratorPluginManager   $hydratorManager
     * @param ViewHelperPluginManager $viewHelperManager
     */
    public function __construct(
        ClassMetadataFactory $classMetadataFactory,
        HydratorPluginManager $hydratorManager,
        ViewHelperPluginManager $viewHelperManager
    ) {
        $this->classMetadataFactory = $classMetadataFactory;
        $this->hydratorManager      = $hydratorManager;
        $this->viewHelperManager    = $viewHelperManager;
    }

    /**
     * {@inheritDoc}
     */
    public function render($nameOrModel, $values = null)
    {
        if (!$nameOrModel instanceof ResourceModel) {
            return;
        }

        $resource = $nameOrModel->getResource();

        if ($resource->isCollection()) {
            $payload = $this->renderCollection($resource);
        } else {
            $payload = $this->renderItem($resource);
        }

        // EmberJS accepts a "meta" section where data like pagination is inserted
        $payload = array_merge($payload, $this->renderMeta($resource));

        return json_encode($payload);
    }
}
