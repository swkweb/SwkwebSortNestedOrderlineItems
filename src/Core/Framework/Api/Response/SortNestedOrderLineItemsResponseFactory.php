<?php declare(strict_types=1);

namespace Swkweb\SortNestedOrderLineItems\Core\Framework\Api\Response;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Api\Context\ContextSource;
use Shopware\Core\Framework\Api\Response\ResponseFactoryInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Swkweb\SortNestedOrderLineItems\Core\Framework\Api\Service\OrderLineItemSortService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SortNestedOrderLineItemsResponseFactory implements ResponseFactoryInterface
{
    private ResponseFactoryInterface $factory;
    private OrderLineItemSortService $orderLineItemSortService;

    public function __construct(
        ResponseFactoryInterface $factory,
        OrderLineItemSortService $orderLineItemSortService
    ) {
        $this->factory = $factory;
        $this->orderLineItemSortService = $orderLineItemSortService;
    }

    public function supports(string $contentType, ContextSource $origin): bool
    {
        return $this->factory->supports($contentType, $origin);
    }

    public function createDetailResponse(
        Criteria $criteria,
        Entity $entity,
        EntityDefinition $definition,
        Request $request,
        Context $context,
        bool $setLocationHeader = false
    ): Response {
        if ($entity instanceof OrderEntity) {
            $this->orderLineItemSortService->sort($entity);
        }

        return $this->factory->createDetailResponse($criteria, $entity, $definition, $request, $context, $setLocationHeader);
    }

    public function createListingResponse(
        Criteria $criteria,
        EntitySearchResult $searchResult,
        EntityDefinition $definition,
        Request $request,
        Context $context
    ): Response {
        array_map(
            [$this->orderLineItemSortService, 'sort'],
            $searchResult->getEntities()->getElements()
        );

        return $this->factory->createListingResponse($criteria, $searchResult, $definition, $request, $context);
    }

    public function createRedirectResponse(
        EntityDefinition $definition,
        string $id,
        Request $request,
        Context $context
    ): Response {
        return $this->factory->createRedirectResponse($definition, $id, $request, $context);
    }
}
