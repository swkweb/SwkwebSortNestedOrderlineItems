<?php declare(strict_types=1);

namespace Swkweb\SortNestedOrderLineItems\Core\Framework\Api\Response;

use Shopware\Core\Framework\Api\Response\ResponseFactoryInterface;
use Swkweb\SortNestedOrderLineItems\Core\Framework\Api\Service\OrderLineItemSortService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ResponseFactoryInterfaceValueResolver implements ArgumentValueResolverInterface
{
    private ArgumentValueResolverInterface $resolver;
    private OrderLineItemSortService $orderLineItemSortService;

    /**
     * @internal
     */
    public function __construct(
        ArgumentValueResolverInterface $coreResponseInterfaceResolver,
        OrderLineItemSortService $orderLineItemSortService,
    ) {
        $this->resolver = $coreResponseInterfaceResolver;
        $this->orderLineItemSortService = $orderLineItemSortService;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $this->resolver->supports($request, $argument);
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

        return array_map([$this, 'decorateResponseFactory'], $factories);
    }

    private function isFilteredRequest(Request $request): bool
    {
        if ($request->attributes->get('_route') !== 'api.order.search') {
            return false;
        }

        return true;
    }

    private function decorateResponseFactory(ResponseFactoryInterface $factory): ResponseFactoryInterface
    {
        return new SortNestedOrderLineItemsResponseFactory(
            $factory,
            $this->orderLineItemSortService
        );
    }
}
