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
namespace Magento\Catalog\Controller\Adminhtml\Category;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Widget
     */
    protected $controller;

    /**
     * @var \Magento\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Backend\Model\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $chooserBlockMock;

    /**
     * @var \Magento\Core\Model\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    public function setUp()
    {
        $this->responseMock = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->viewMock = $this->getMock('Magento\Backend\Model\View', array('getLayout'), array(), '', false);
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            array(),
            array(),
            '',
            false
        );
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            array('getRequest', 'getResponse', 'getMessageManager', 'getSession'),
            $helper->getConstructArguments(
                'Magento\Backend\App\Action\Context',
                array(
                    'response' => $this->responseMock,
                    'request' => $this->requestMock,
                    'view' => $this->viewMock,
                    'objectManager' => $this->objectManagerMock
                )
            )
        );
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($this->responseMock));
        $this->registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->controller = new \Magento\Catalog\Controller\Adminhtml\Category\Widget($context, $this->registryMock);
    }

    protected function _getTreeBlock()
    {
        $this->chooserBlockMock = $this->getMock(
            'Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser', array(), array(), '', false
        );
        $this->layoutMock = $this->getMock('Magento\Core\Model\Layout', array('createBlock'), array(), '', false);
        $this->layoutMock->expects($this->once())->method('createBlock')->will(
            $this->returnValue($this->chooserBlockMock)
        );
        $this->viewMock->expects($this->once())->method('getLayout')->will($this->returnValue($this->layoutMock));
    }

    public function testChooserAction()
    {
        $this->_getTreeBlock();
        $testHtml = '<div>Some test html</div>';
        $this->chooserBlockMock->expects($this->once())->method('toHtml')->will($this->returnValue($testHtml));
        $this->responseMock->expects($this->once())->method('setBody')->with($this->equalTo($testHtml));
        $this->controller->chooserAction();
    }

    public function testCategoriesJsonAction()
    {
        $this->_getTreeBlock();
        $testCategoryId = 1;

        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValue($testCategoryId));
        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', array(), array(), '', false);
        $categoryMock->expects($this->once())->method('load')->will($this->returnValue($categoryMock));
        $categoryMock->expects($this->once())->method('getId')->will($this->returnValue($testCategoryId));
        $this->objectManagerMock->expects($this->once())->method('create')
            ->with($this->equalTo('Magento\Catalog\Model\Category'))->will($this->returnValue($categoryMock));

        $this->chooserBlockMock->expects($this->once())->method('setSelectedCategories')->will(
            $this->returnValue($this->chooserBlockMock)
        );
        $testHtml = '<div>Some test html</div>';
        $this->chooserBlockMock->expects($this->once())->method('getTreeJson')->will($this->returnValue($testHtml));
        $this->responseMock->expects($this->once())->method('representJson')->with($this->equalTo($testHtml));
        $this->controller->categoriesJsonAction();
    }
}
