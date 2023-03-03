<?php declare(strict_types=1);

namespace Swkweb\SortNestedOrderLineItems\Core\Checkout\Cart\Order;

use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderConverterSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CartConvertedEvent::class => ['onConvertedCart'],
        ];
    }

    public function onConvertedCart(CartConvertedEvent $event): void
    {
        if (!$this->systemConfigService->getBool('SwkwebSortNestedOrderLineItems.config.shouldAssignPositionFlat')) {
            return;
        }

        $cart = $event->getConvertedCart();
        if (isset($cart['lineItems']) && is_array($cart['lineItems'])) {
            $cart['lineItems'] = $this->assignLineItemPositionFlat($cart['lineItems']);
            $event->setConvertedCart($cart);
        }
    }

    /**
     * @param list<array{position: int}> $lineItems
     *
     * @return list<array{position: int}>
     */
    private function assignLineItemPositionFlat(array $lineItems): array
    {
        $position = 1;
        foreach ($lineItems as &$lineItem) {
            $lineItem['position'] = $position++;
        }

        return $lineItems;
    }
}
