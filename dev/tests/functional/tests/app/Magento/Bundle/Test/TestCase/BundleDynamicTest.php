<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Test\TestCase;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Bundle\Test\Fixture\Bundle;

/**
 * Class BundleDynamicTest
 * Bundle product dynamic test
 */
class BundleDynamicTest extends Functional
{
    /**
     * Login into backend area before test
     *
     * @return void
     */
    protected function setUp()
    {
        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * Creating bundle (dynamic) product and assigning it to the category
     *
     * @ZephyrId MAGETWO-12702
     * @return void
     */
    public function testCreate()
    {
        //Data
        $bundle = Factory::getFixtureFactory()->getMagentoBundleBundleDynamic();
        $bundle->switchData('bundle');
        //Pages & Blocks
        $manageProductsGrid = Factory::getPageFactory()->getCatalogProductIndex();
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productForm = $createProductPage->getForm();
        //Steps
        $manageProductsGrid->open();
        $manageProductsGrid->getProductBlock()->addProduct('bundle');
        $productForm->fillProduct($bundle);
        $createProductPage->getFormAction()->save();
        //Verification
        $createProductPage->getMessagesBlock()->assertSuccessMessage();
        // Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        $cachePage->getMessagesBlock()->assertSuccessMessage();
        //Verification
        $this->assertOnGrid($bundle);
        $this->assertOnCategory($bundle);
    }

    /**
     * Assert existing product on admin product grid
     *
     * @param Bundle $product
     * @return void
     */
    protected function assertOnGrid($product)
    {
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        $gridBlock = $productGridPage->getProductGrid();
        $this->assertTrue($gridBlock->isRowVisible(array('sku' => $product->getProductSku())));
    }

    /**
     * Checking the product on the category page
     *
     * @param Bundle $product
     * @return void
     */
    protected function assertOnCategory($product)
    {
        //Pages
        $frontendHomePage = Factory::getPageFactory()->getCmsIndexIndex();
        $categoryPage = Factory::getPageFactory()->getCatalogCategoryView();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        //Steps
        $frontendHomePage->open();
        $frontendHomePage->getTopmenu()->selectCategoryByName($product->getCategoryName());
        //Verification on category product list
        $productListBlock = $categoryPage->getListProductBlock();
        $this->assertTrue($productListBlock->isProductVisible($product->getName()));
        $productListBlock->openProductViewPage($product->getName());
        //Verification on product detail page
        $productViewBlock = $productPage->getViewBlock();
        $this->assertSame($product->getName(), $productViewBlock->getProductName());
        $this->assertEquals($product->getProductPrice(), $productViewBlock->getProductPrice());

        // @TODO: add click on "Customize and Add To Cart" button and assert options count
        $productOptionsBlock = $productPage->getCustomOptionsBlock();
        $actualOptions = $productOptionsBlock->getOptions();
        $expectedOptions = $product->getBundleOptions();
        foreach ($actualOptions as $optionType => $actualOption) {
            $this->assertContains($expectedOptions[$optionType], $actualOption);
        }
    }
}
