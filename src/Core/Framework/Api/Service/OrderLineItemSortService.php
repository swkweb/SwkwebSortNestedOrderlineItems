<?php declare(strict_types=1);

namespace Swkweb\SortNestedOrderLineItems\Core\Framework\Api\Service;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class OrderLineItemSortService
{
    public function sort(OrderEntity $order): void
    {
        // We need to clone the order, as `getNestedLineItems` removes
        // the child associations
        $clonedOrder = clone $order;
        $nestedLineItems = $clonedOrder->getNestedLineItems();

        $lineItems = $order->getLineItems();

        if ($nestedLineItems === null || $lineItems === null) {
            return;
        }

        $i = 1;
        $positions = [];
        foreach ($this->buildFlat($nestedLineItems) as $lineItem) {
            $positions[$lineItem->getId()] = $i;
            $i++;
        }

        foreach ($this->iterateNestedLineItems($lineItems) as $lineItem) {
            $lineItem->setPosition($positions[$lineItem->getId()]);
        }

        $lineItems->sortByPosition();
    }

    /**
     * @return OrderLineItemEntity[]
     */
    private function buildFlat(OrderLineItemCollection $nestedLineItems): array
    {
        $flat = [];
        foreach ($nestedLineItems as $lineItem) {
            $flat[] = $lineItem;

            $children = $lineItem->getChildren();
            if ($children !== null) {
                foreach ($this->buildFlat($children) as $nest) {
                    $flat[] = $nest;
                }
            }
        }

        return $flat;
    }

    /**
     * @return \Generator<OrderLineItemEntity>
     */
    private function iterateNestedLineItems(OrderLineItemCollection $lineItems): \Generator
    {
        foreach ($lineItems as $lineItem) {
            yield $lineItem;

            $children = $lineItem->getChildren();
            if ($children !== null) {
                return $this->iterateNestedLineItems($children);
            }
        }
    }
}
