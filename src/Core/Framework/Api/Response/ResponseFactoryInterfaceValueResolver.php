<?php declare(strict_types=1);

namespace Swkweb\SortNestedOrderLineItems\Core\Framework\Api\Response;

use Shopware\Core\Framework\Api\Response\ResponseFactoryInterface;
use Swkweb\SortNestedOrderLineItems\Core\Framework\Api\Service\OrderLineItemSortService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ResponseFactoryInterfaceValueResolver implements ValueResolverInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ValueResolverInterface $resolver,
        private readonly OrderLineItemSortService $orderLineItemSortService,
    ) {
    }

    /**
     * @return iterable<ResponseFactoryInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $factories = [...$this->resolver->resolve($request, $argument)];

        if (!$this->isFilteredRequest($request)) {
            return $factories;
        }

        return array_map(
            fn (ResponseFactoryInterface $factory): ResponseFactoryInterface => $this->decorateResponseFactory($factory),
            $factories,
        );
    }

    private function isFilteredRequest(Request $request): bool
    {
        return $request->attributes->get('_route') === 'api.order.search';
    }

    private function decorateResponseFactory(ResponseFactoryInterface $factory): ResponseFactoryInterface
    {
        return new SortNestedOrderLineItemsResponseFactory(
            $factory,
            $this->orderLineItemSortService,
        );
    }
}
