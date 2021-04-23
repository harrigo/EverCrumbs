<?php

namespace Harrigo\EverCrumbs\Block;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Evercrumbs extends Template
{
    protected Data $catalogData = null;
    private Registry $registry;

    public function __construct(
        Context $context,
        Data $catalogData,
        Registry $registry,
        array $data = []
    ) {
        $this->catalogData = $catalogData;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    private function getRootCategoryId(): int
    {
        return (int) $this->_storeManager->getStore()->getRootCategoryId();
    }

    public function getCrumbs(): array
    {
        $crumbs = [];
        $crumbs[] = [
            'label' => __('Home'),
            'title' => __('Go to Home Page'),
            'link' => $this->_storeManager->getStore()->getBaseUrl()
        ];

        $product = $this->registry->registry('current_product');
        $categoryCollection = clone $product->getCategoryCollection();
        $categoryCollection->clear()
            ->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC)
            ->addAttributeToFilter('path', ['like' => "1/" . $this->getRootCategoryId() . "/%"])
            ->setPageSize(1);

        $breadcrumbCategories = $categoryCollection->getFirstItem()->getParentCategories();
        usort($breadcrumbCategories, function ($a, $b) {
            return strcmp($a->getLevel(), $b->getLevel());
        });
        foreach ($breadcrumbCategories as $category) {
            $crumbs[] = [
                'label' => $category->getName(),
                'title' => $category->getName(),
                'link'  => $category->getUrl()
            ];
        }

        $crumbs[] = [
            'label' => $product->getName(),
            'title' => $product->getName(),
            'link' => ''
        ];

        return $crumbs;
    }
}
